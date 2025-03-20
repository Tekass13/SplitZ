<?php
class BudgetManager extends AbstractManager
{

    public function __construct()
    {
        parent::__construct();
    }

    public function createBudget(string $title, float $totalPrice): int
    {
        $query = $this->db->prepare("INSERT INTO budgets (user_id, title, total_price, created_at) VALUES (:user_id, :title, :total_price, NOW())");
        $userId = $_SESSION['user'] ?? null;
        $parameters = [
            ':user_id' => $userId,
            ':title' => $title,
            ':total_price' => $totalPrice
        ];

        $query->execute($parameters);
        return $this->db->lastInsertId();
    }

    public function addCategory(int $budgetId, string $type, string $name, float $price, ?int $contactId = null): int
    {
        $query = $this->db->prepare("INSERT INTO budget_categories (budget_id, type, name, price, contact_id) VALUES (:budget_id, :type, :name, :price, :contact_id)");
        $parameters = [
            ':budget_id' => $budgetId,
            ':type' => $type,
            ':name' => $name,
            ':price' => $price,
            ':contact_id' => $contactId
        ];
        $query->execute($parameters);
        return $this->db->lastInsertId();
    }

    public function getUserBudgets(int $userId): array
    {
        $query = $this->db->prepare("SELECT id, title, total_price, created_at FROM budgets WHERE user_id = :user_id ORDER BY created_at DESC");
        $parameters = [':user_id' => $userId];
        $query->execute($parameters);
        $budgets = $query->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($budgets as &$budget) {
            $budget['categories'] = $this->getBudgetCategories($budget['id']);
        }

        return $budgets;
    }

    public function getBudgetCategories(int $budgetId): array
    {
        $query = $this->db->prepare("SELECT id, type, name, price, contact_id FROM budget_categories WHERE budget_id = :budget_id");
        $parameters = [':budget_id' => $budgetId];
        $query->execute($parameters);
        $categories = $query->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $userId = $_SESSION['user'] ?? null;

        foreach ($categories as &$category) {
            $category['contact'] = null;
            if (!empty($category['contact_id'])) {
                $contactManager = new ContactManager();
                $contacts = $contactManager->getContactById($userId, $category['contact_id']);

                if (!empty($contacts)) {
                    $category['contact'] = $contacts;
                }
            }
        }

        return $categories;
    }

    public function getBudgetById(int $budgetId, int $userId): ?array
    {
        $query = $this->db->prepare("SELECT id, title, total_price, created_at FROM budgets WHERE id = :id AND user_id = :user_id");
        $parameters = [
            ':id' => $budgetId,
            'user_id' => $userId
        ];
        $query->execute($parameters);
        $budget = $query->fetch(PDO::FETCH_ASSOC);

        if ($budget) {
            $budget['categories'] = $this->getBudgetCategories($budget['id']);
            return $budget;
        }

        return null;
    }

    public function updateBudget(int $budgetId, string $title, float $totalPrice, int $userId): bool
    {
        $query = $this->db->prepare("UPDATE budgets SET title = :title, total_price = :total_price WHERE id = :id AND user_id = :user_id");
        $parameters = [
            ':title' => $title,
            ':total_price' => $totalPrice,
            ':id' => $budgetId,
            ':user_id' => $userId
        ];
        $query->execute($parameters);
        return $query->rowCount() > 0;
    }

    public function deleteBudget(int $budgetId, int $userId): bool
    {
        $query = $this->db->prepare("SELECT id FROM budgets WHERE id = :id AND user_id = :user_id");
        $parameters = [
            ':id' => $budgetId,
            ':user_id' => $userId
        ];
        $query->execute($parameters);

        if (!$query->fetch()) {
            return false;
        }

        $parameters = [':budget_id' => $budgetId];

        $this->db->prepare("DELETE FROM budget_categories WHERE budget_id = :budget_id")->execute($parameters);
        $this->db->prepare("DELETE FROM budgets WHERE id = :budget_id")->execute($parameters);

        return true;
    }
}
