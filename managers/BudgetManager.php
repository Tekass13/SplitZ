<?php
class BudgetManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createBudget(string $title, float $price, $userId): int
    {
        $query = $this->db->prepare("INSERT INTO budgets (title, price, created_by, created_at) VALUES (:title, :price, :created_by, NOW())");
        $parameters = [
            ':title' => $title,
            ':price' => $price,
            ':created_by' => $userId
        ];
        $query->execute($parameters);
        return $this->db->lastInsertId();
    }

    public function deleteBudget(int $budgetId, int $userId): bool
    {
        $checkQuery = $this->db->prepare("
        SELECT id FROM budgets 
        WHERE id = :budget_id AND created_by = :user_id
    ");

        $checkQuery->execute([
            ':budget_id' => $budgetId,
            ':user_id' => $userId
        ]);

        if (!$checkQuery->fetchColumn()) {
            return false;
        }

        $deleteParticipantsQuery = $this->db->prepare("
            DELETE FROM category_participants 
            WHERE category_id IN (
                SELECT id FROM categories WHERE budget_id = :budget_id
            )
        ");
        $deleteParticipantsQuery->execute([':budget_id' => $budgetId]);

        $deleteCategoriesQuery = $this->db->prepare("
            DELETE FROM categories 
            WHERE budget_id = :budget_id
        ");
        $deleteCategoriesQuery->execute([':budget_id' => $budgetId]);

        $deleteBudgetQuery = $this->db->prepare("
            DELETE FROM budgets 
            WHERE id = :budget_id AND created_by = :user_id
        ");
        $deleteBudgetQuery->execute([
            ':budget_id' => $budgetId,
            ':user_id' => $userId
        ]);

        return true;
    }

    public function getUserBudgets(int $userId): array
    {
        $query = $this->db->prepare("
        SELECT 
            budgets.id AS budget_id,
            budgets.title AS budget_title,
            budgets.price AS budget_price,
            budgets.created_at AS budget_created_at,
            budgets.created_by AS budget_created_by,
            categories.id AS category_id,
            categories.type AS category_type,
            categories.name AS category_name,
            categories.price AS category_price,
            category_participants.participant_id AS participant_id,
            category_participants.participant_amount AS participant_amount,
            category_participants.invitation AS participant_invitation,
            category_participants.payment AS participant_payment,
            users.username AS participant_username,
            users.unique_id AS participant_unique_id,  /* Ajout de cette ligne */
            creator.username AS created_by_username
        FROM budgets
        LEFT JOIN categories ON categories.budget_id = budgets.id
        LEFT JOIN category_participants ON category_participants.category_id = categories.id
        LEFT JOIN users ON users.id = category_participants.participant_id
        LEFT JOIN users AS creator ON creator.id = budgets.created_by
        WHERE budgets.created_by = :created_by
        ORDER BY budgets.id, categories.id, users.username
    ");

        $parameters = ['created_by' => $userId];
        $query->execute($parameters);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        return $this->processBudgetResults($results);
    }

    public function getBudgetById(int $budgetId, int $userId): ?Budget
    {
        $query = $this->db->prepare("
            SELECT 
                budgets.id AS budget_id,
                budgets.title AS budget_title,
                budgets.price AS budget_price,
                budgets.created_at AS budget_created_at,
                budgets.created_by AS budget_created_by,
                categories.id AS category_id,
                categories.type AS category_type,
                categories.name AS category_name,
                categories.price AS category_price,
                category_participants.participant_id AS participant_id,
                category_participants.participant_amount AS participant_amount,
                category_participants.invitation AS participant_invitation,
                category_participants.payment AS participant_payment,
                users.username AS participant_username,
                users.unique_id AS participant_unique_id,
                creator.username AS created_by_username
            FROM budgets
            LEFT JOIN categories ON categories.budget_id = budgets.id
            LEFT JOIN category_participants ON category_participants.category_id = categories.id
            LEFT JOIN users ON users.id = category_participants.participant_id
            LEFT JOIN users AS creator ON creator.id = budgets.created_by
            WHERE budgets.id = :budget_id AND budgets.created_by = :user_id
            ORDER BY categories.id, users.username
        ");

        $parameters = [
            ':budget_id' => $budgetId,
            ':user_id' => $userId
        ];

        $query->execute($parameters);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            return null;
        }

        $processedBudgets = $this->processBudgetResults($results);
        return !empty($processedBudgets) ? $processedBudgets[0] : null;
    }

    public function updateBudget(int $budgetId, string $title, float $price, int $userId): bool
    {
        $query = $this->db->prepare("
            UPDATE budgets 
            SET title = :title, price = :price 
            WHERE id = :budget_id AND created_by = :user_id
        ");

        $parameters = [
            ':title' => $title,
            ':price' => $price,
            ':budget_id' => $budgetId,
            ':user_id' => $userId
        ];

        return $query->execute($parameters);
    }

    public function deleteBudgetCategories(int $budgetId): bool
    {
        $deleteParticipantsQuery = $this->db->prepare("
            DELETE FROM category_participants 
            WHERE category_id IN (
                SELECT id FROM categories WHERE budget_id = :budget_id
            )
        ");
        $deleteParticipantsQuery->execute([':budget_id' => $budgetId]);

        $deleteCategoriesQuery = $this->db->prepare("
            DELETE FROM categories 
            WHERE budget_id = :budget_id
        ");

        return $deleteCategoriesQuery->execute([':budget_id' => $budgetId]);
    }

    private function processBudgetResults(array $results): array
    {
        $budgets = [];

        foreach ($results as $row) {
            $budgetId = $row['budget_id'];
            if (!isset($budgets[$budgetId])) {
                $budget = new Budget();
                $budget->id = $row['budget_id'];
                $budget->title = $row['budget_title'];
                $budget->price = $row['budget_price'];
                $budget->created_at = $row['budget_created_at'];
                $budget->created_by = $row['budget_created_by'];
                $budget->created_by_username = $row['created_by_username'] ?? '';
                $budget->categories = [];
                $budgets[$budgetId] = $budget;
            }

            $currentBudget = $budgets[$budgetId];
            $categoryId = $row['category_id'];

            if ($categoryId && !$this->categoryExists($currentBudget, $categoryId)) {
                $category = new Category();
                $category->id = $categoryId;
                $category->type = $row['category_type'];
                $category->name = $row['category_name'];
                $category->price = $row['category_price'];
                $category->participants = [];
                $currentBudget->categories[] = $category;
            }

            if ($row['participant_username'] && $categoryId) {
                foreach ($currentBudget->categories as $category) {
                    if ($category->id === $categoryId) {
                        $participant = new Participant();
                        $participant->id = $row['participant_id'];
                        $participant->username = $row['participant_username'];
                        $participant->amount = $row['participant_amount'];
                        $participant->invitation = $row['participant_invitation'] ?? 'En attente';
                        $participant->payment = $row['participant_payment'] ?? 'En attente';
                        $participant->unique_id = $row['participant_unique_id'] ?? '';
                        $category->participants[] = $participant;
                        break;
                    }
                }
            }
        }

        return array_values($budgets);
    }

    public function addBudgetCategory(int $budgetId, string $type, string $name, float $price): int
    {
        $query = $this->db->prepare("INSERT INTO categories (type, name, price, budget_id) VALUES (:type, :name, :price, :budget_id)");
        $parameters = [
            ':type' => $type,
            ':name' => $name,
            ':price' => $price,
            ':budget_id' => $budgetId
        ];
        $query->execute($parameters);
        return $this->db->lastInsertId();
    }

    public function addCategoryParticipant(int $categoryId, array $participants): array
    {
        $addedParticipants = [];
        $budgetInfo = $this->getBudgetInfoFromCategoryId($categoryId);

        foreach ($participants as $participant) {
            $contactId = $participant['id'];

            $isCreator = ($contactId == $_SESSION['user']);

            if (!$isCreator) {
                $query = $this->db->prepare("
                    SELECT contact_id FROM contacts_list 
                    WHERE contact_id = :contact_id
                ");
                $query->execute([':contact_id' => $contactId]);
                $contactExists = $query->fetchColumn();

                if (!$contactExists) {
                    continue;
                }
            }

            $checkQuery = $this->db->prepare("
                SELECT id FROM category_participants 
                WHERE category_id = :category_id AND participant_id = :participant_id
            ");
            $checkQuery->execute([
                ':category_id' => $categoryId,
                ':participant_id' => $contactId
            ]);

            if (!$checkQuery->fetchColumn()) {
                $insertQuery = $this->db->prepare("
                INSERT INTO category_participants (category_id, participant_id, participant_amount, invitation, payment) 
                VALUES (:category_id, :participant_id, :participant_amount, 'En attente', 'En attente')
            ");

                $insertQuery->execute([
                    ':category_id' => $categoryId,
                    ':participant_id' => $contactId,
                    ':participant_amount' => $participant['amount']
                ]);

                $participantId = $contactId;
                $addedParticipants[] = $participantId;

                if ($budgetInfo) {
                    $this->sendParticipationRequest($participantId, $categoryId, $budgetInfo);
                }
            }
        }

        return $addedParticipants;
    }

    private function getBudgetInfoFromCategoryId(int $categoryId): ?array
    {
        $query = $this->db->prepare("
            SELECT 
                budgets.id AS budget_id, 
                budgets.title AS budget_title,
                categories.id AS category_id, 
                categories.name AS category_name,
                users.id AS creator_id,
                users.username AS creator_name
            FROM categories
            JOIN budgets ON categories.budget_id = budgets.id
            JOIN users ON budgets.created_by = users.id
            WHERE categories.id = :category_id
        ");

        $query->execute([':category_id' => $categoryId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function sendParticipationRequest(int $participantId, int $categoryId, array $budgetInfo): void
    {
        $query = $this->db->prepare("
            SELECT id FROM users WHERE id = :user_id
        ");
        $query->execute([':user_id' => $participantId]);
        $userId = $query->fetchColumn();

        if (!$userId) {
            return;
        }

        $confirmationUrl = "index.php?route=confirm-participation&category_id=" . $categoryId . "&user_id=" . $userId;

        $messageManager = new MessageManager();
        $subject = "Invitation à participer au budget: " . htmlspecialchars($budgetInfo['budget_title']);

        $content = "Vous êtes invité à participer à la catégorie \"" . htmlspecialchars($budgetInfo['category_name']) . "\" ";
        $content .= "du budget \"" . htmlspecialchars($budgetInfo['budget_title']) . "\".<br><br>";
        $content .= "Pour confirmer votre participation, veuillez cliquer sur le lien suivant :<br>";
        $content .= "<a href=\"" . $confirmationUrl . "\">Confirmer ma participation</a><br><br>";
        $content .= "Ou copiez-collez ce lien dans votre navigateur :<br>";
        $content .= $confirmationUrl . "<br><br>";
        $content .= "Créer par " . htmlspecialchars($budgetInfo['creator_name']);

        $message = new Message(
            $budgetInfo['creator_id'],
            $userId,
            $subject,
            $content
        );

        $messageManager->createMessage($message);
    }

    public function confirmParticipation(int $categoryId, int $userId): bool
    {
        $query = $this->db->prepare("
            UPDATE category_participants 
            SET invitation = 'Confirmé' 
            WHERE category_id = :category_id AND participant_id = :participant_id
        ");

        $parameters = [
            ':category_id' => $categoryId,
            ':participant_id' => $userId
        ];

        return $query->execute($parameters);
    }

    public function confirmPayment(int $categoryId, int $userId): bool
    {
        $query = $this->db->prepare("
            UPDATE category_participants 
            SET payment = 'Confirmé' 
            WHERE category_id = :category_id AND participant_id = :participant_id
        ");

        $parameters = [
            ':category_id' => $categoryId,
            ':participant_id' => $userId
        ];

        return $query->execute($parameters);
    }

    private function categoryExists(Budget $budget, int $categoryId): bool
    {
        foreach ($budget->categories as $category) {
            if ($category->id === $categoryId) {
                return true;
            }
        }
        return false;
    }

    public function getCategoryParticipations(int $userId): array
    {
        $query = $this->db->prepare("
            SELECT 
                category_participants.id AS participation_id,
                category_participants.category_id,
                category_participants.participant_amount,
                category_participants.invitation,
                category_participants.payment,
                categories.name AS category_name,
                categories.type AS category_type,
                budgets.id AS budget_id,
                budgets.title AS budget_title,
                users.username AS creator_name
            FROM category_participants
            JOIN categories ON category_participants.category_id = categories.id
            JOIN budgets ON categories.budget_id = budgets.id
            JOIN users ON budgets.created_by = users.id
            WHERE category_participants.participant_id = :user_id
            ORDER BY category_participants.invitation, category_participants.payment, budgets.created_at DESC
        ");

        $parameters = [':user_id' => $userId];
        $query->execute($parameters);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
