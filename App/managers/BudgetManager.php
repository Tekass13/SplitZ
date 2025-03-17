<?php
class BudgetManager extends AbstractManager {

    public function __construct() {
        parent::__construct();
    }

    public function createBudget(string $title, float $totalPrice): int {
        // Vérifier les formats de données
        if (empty($title) || strlen($title) > 50 || $totalPrice < 0) {
            throw new Exception("Données invalides.");
        }

        try {
            $query = $this->db->prepare("INSERT INTO budgets (user_id, title, total_price, created_at) VALUES (?, ?, ?, NOW())");      
            $userId = $_SESSION['user'] ?? null;

            if (!$userId) {
                throw new Exception("Utilisateur non authentifié.");
            }

            $query->execute([$userId, $title, $totalPrice]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de créer le budget.");
        }
    }

    public function addCategory(int $budgetId, string $type, string $name, float $price, ?int $contactId = null): int {
        // Vérifier les formats de données
        if ($budgetId <= 0 || empty($type) || empty($name) || strlen($name) > 50 || $price < 0) {
            throw new Exception("Données invalides.");
        }

        try {
            $query = $this->db->prepare("INSERT INTO budget_categories (budget_id, type, name, price, contact_id) VALUES (?, ?, ?, ?, ?)");
            $query->execute([$budgetId, $type, $name, $price, $contactId]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible d'ajouter la catégorie.");
        }
    }

    public function getUserBudgets(int $userId): array {
        // Vérifier les formats de données
        if ($userId <= 0 || empty($userId)) {
            throw new Exception("Données invalides.");
        }

        try {
            $query = $this->db->prepare("SELECT id, title, total_price, created_at FROM budgets WHERE user_id = ? ORDER BY created_at DESC");
            $query->execute([$userId]);
            $budgets = $query->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($budgets as &$budget) {
                $budget['categories'] = $this->getBudgetCategories($budget['id']);
            }

            return $budgets;
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de récupérer les budgets.");
        }
    }

    public function getBudgetCategories(int $budgetId): array {
        // Vérifier les formats de données
        if ($budgetId <= 0) {
            throw new Exception("ID budget invalide.");
        }

        try {
            $query = $this->db->prepare("SELECT id, type, name, price, contact_id FROM budget_categories WHERE budget_id = ?");
            $query->execute([$budgetId]);
            $categories = $query->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $userId = $_SESSION['user'] ?? null;
            if (!$userId) {
                throw new Exception("Utilisateur non authentifié.");
            }

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
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de récupérer les catégories.");
        }
    }

    public function getBudgetById(int $budgetId, int $userId): ?array {
        // Vérifier les formats de données
        if ($budgetId <= 0 || $userId <= 0) {
            throw new Exception("Données invalides.");
        }

        try {
            $query = $this->db->prepare("SELECT id, title, total_price, created_at FROM budgets WHERE id = ? AND user_id = ?");
            $query->execute([$budgetId, $userId]);
            $budget = $query->fetch(PDO::FETCH_ASSOC);

            if ($budget) {
                $budget['categories'] = $this->getBudgetCategories($budget['id']);
                return $budget;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de récupérer le budget.");
        }
    }

    public function updateBudget(int $budgetId, string $title, float $totalPrice, int $userId): bool {
        // Vérifier les formats de données
        if ($budgetId <= 0 || empty($title) || strlen($title) > 50 || $totalPrice < 0 || $userId <= 0) {
            throw new Exception("Données invalides.");
        }

        try {
            $query = $this->db->prepare("UPDATE budgets SET title = ?, total_price = ? WHERE id = ? AND user_id = ?");
            $query->execute([$title, $totalPrice, $budgetId, $userId]);
            return $query->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de mettre à jour le budget.");
        }
    }

    public function deleteBudget(int $budgetId, int $userId): bool {
        // Vérifier les formats de données
        if ($budgetId <= 0 || $userId <= 0) {
            throw new Exception("Données invalides.");
        }

        try {
            $query = $this->db->prepare("SELECT id FROM budgets WHERE id = ? AND user_id = ?");
            $query->execute([$budgetId, $userId]);

            if (!$query->fetch()) {
                return false;
            }

            $this->db->prepare("DELETE FROM budget_categories WHERE budget_id = ?")->execute([$budgetId]);
            $this->db->prepare("DELETE FROM budgets WHERE id = ?")->execute([$budgetId]);

            return true;
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de supprimer le budget.");
        }
    }
}
?>
