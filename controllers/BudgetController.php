<?php
class BudgetController extends AbstractController
{
    private BudgetManager $budgetManager;
    private ContactManager $contactManager;

    public function __construct()
    {
        $this->budgetManager = new BudgetManager();
        $this->contactManager = new ContactManager();
    }

    public function listBudgets()
    {
        $userId = $_SESSION['user'];
        if (!$userId) {
            $this->redirect('index.php?route=login');
        }

        $budgets = $this->budgetManager->getUserBudgets($userId);

        $this->render('budget', ['budgets' => $budgets]);
    }

    public function addBudget()
    {
        $userId = $_SESSION['user'];
        if (!$userId) {
            $this->redirect('index.php?route=login');
        }
        $contacts = $this->contactManager->getAllContacts($userId);

        $this->render('add-budget', ['contacts' => $contacts]);
    }

    public function saveBudget()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = filter_input(INPUT_POST, 'add-budget-title', FILTER_DEFAULT) ?: '';
            $defaultPrice = filter_input(INPUT_POST, 'add-budget-price', FILTER_DEFAULT) ?: '0';
            $priceInput = str_replace(',', '.', $defaultPrice);
            $price = filter_var($priceInput, FILTER_VALIDATE_FLOAT) ?: 0.0;
            $categoriesData = filter_input(INPUT_POST, 'categories_data', FILTER_DEFAULT) ?: '[]';
            $categoriesInput = json_decode($categoriesData, true) ?: [];

            if (empty($title) || $price <= 0) {
                $_SESSION['error'] = 'Titre invalide ou prix incorrect';
                $this->redirect('index.php?route=add-budget');
                exit;
            }

            $userId = $_SESSION['user'];
            if (!$userId) {
                $this->redirect('index.php?route=login');
            }
            $budgetId = $this->budgetManager->createBudget($title, $price, $userId);

            if ($budgetId && !empty($categoriesInput)) {
                foreach ($categoriesInput as $categoryData) {

                    $category = new Category(
                        $categoryData['type'] ?? '',
                        $categoryData['name'] ?? '',
                        $categoryData['price'] ?? 0.0
                    );

                    $categoryId = $this->budgetManager->addBudgetCategory(
                        $budgetId,
                        $category->type,
                        $category->name,
                        $category->price
                    );
                    if ($categoryId && !empty($categoryData['participants'])) {
                        $participants = array_map(function ($participantData) {
                            return [
                                'id' => $participantData['id'] ?? 0,
                                'amount' => $participantData['amount'] ?? 0.0
                            ];
                        }, $categoryData['participants']);

                        $this->budgetManager->addCategoryParticipant(
                            $categoryId,
                            $participants
                        );
                    }
                }
            }

            $this->redirect('index.php?route=budgets');
        }
    }

    public function editBudget()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = 'ID de budget invalide';
            $this->redirect('index.php?route=budget');
        }

        $budgetId = (int)$_GET['id'];
        $userId = $_SESSION['user'];

        // Récupérer le budget spécifique
        $budget = $this->budgetManager->getBudgetById($budgetId, $userId);

        if (!$budget) {
            $_SESSION['error'] = 'Budget introuvable ou vous n\'avez pas les permissions nécessaires';
            $this->redirect('index.php?route=budget');
        }
        $contacts = $this->contactManager->getAllContacts($userId);

        $this->render('edit-budget', ['budget' => $budget, 'contacts' => $contacts]);
    }

    public function updateBudget()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['budget_id']) || !is_numeric($_POST['budget_id'])) {
                $_SESSION['error'] = 'ID de budget invalide';
                $this->redirect('index.php?route=budgets');
                return;
            }

            $budgetId = (int)$_POST['budget_id'];
            $title = filter_input(INPUT_POST, 'edit-budget-title', FILTER_DEFAULT) ?: '';

            // CORRECTION : Utiliser 'edit-budget-price' au lieu de 'add-budget-price'
            $defaultPrice = filter_input(INPUT_POST, 'edit-budget-price', FILTER_DEFAULT) ?: '0';
            $priceInput = str_replace(',', '.', $defaultPrice);
            $price = filter_var($priceInput, FILTER_VALIDATE_FLOAT) ?: 0.0;
            $categoriesData = filter_input(INPUT_POST, 'categories_data', FILTER_DEFAULT) ?: '[]';

            $categoriesInput = json_decode($categoriesData, true) ?: [];

            // Debug : Afficher les données reçues (à supprimer en production)
            error_log("Title: " . $title);
            error_log("Price: " . $price);
            error_log("Categories data: " . $categoriesData);

            if (empty($title)) {
                $_SESSION['error'] = 'Titre invalide';
                $this->redirect('index.php?route=edit-budget&id=' . $budgetId);
                return;
            }

            // Permettre un prix à 0 si calculé automatiquement
            if ($price < 0) {
                $_SESSION['error'] = 'Prix incorrect';
                $this->redirect('index.php?route=edit-budget&id=' . $budgetId);
                return;
            }

            $userId = $_SESSION['user'];
            $success = $this->budgetManager->updateBudget($budgetId, $title, $price, $userId);

            if ($success) {
                // Supprimer les anciennes catégories
                $this->budgetManager->deleteBudgetCategories($budgetId);

                // Ajouter les nouvelles catégories
                if (!empty($categoriesInput)) {
                    foreach ($categoriesInput as $categoryData) {
                        $category = new Category(
                            $categoryData['type'] ?? '',
                            $categoryData['name'] ?? '',
                            $categoryData['price'] ?? 0.0
                        );

                        $categoryId = $this->budgetManager->addBudgetCategory(
                            $budgetId,
                            $category->type,
                            $category->name,
                            $category->price
                        );

                        if ($categoryId && !empty($categoryData['participants'])) {
                            $participants = array_map(function ($participantData) {
                                return [
                                    'id' => $participantData['id'] ?? 0,
                                    'amount' => $participantData['amount'] ?? 0.0
                                ];
                            }, $categoryData['participants']);

                            $this->budgetManager->addCategoryParticipant(
                                $categoryId,
                                $participants
                            );
                        }
                    }
                }

                $_SESSION['message'] = 'Budget mis à jour avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour du budget';
            }

            $this->redirect('index.php?route=budgets');
        }
    }

    public function deleteBudget()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['error'] = 'ID de budget invalide';
            $this->redirect('index.php?route=budgets');
        }

        $budgetId = (int)$_GET['id'];
        $userId = $_SESSION['user'];

        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
            $tokenManager = new CSRFTokenManager();

            if (!$tokenManager->validateCSRFToken($token)) {
                $_SESSION['error'] = 'Erreur de sécurité, veuillez réessayer';
                $this->redirect('index.php?route=budgets');
            }
        }

        $success = $this->budgetManager->deleteBudget($budgetId, $userId);

        if ($success) {
            $_SESSION['message'] = 'Budget supprimé avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du budget';
        }

        $this->redirect('index.php?route=budgets');
    }
}
