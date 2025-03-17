<?php
class BudgetController extends AbstractController {

    public function __construct() {
        parent::__construct();
    }

    public function listBudgets() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }
        
        $userId = $_SESSION['user'];
        $budgetManager = new BudgetManager();
        $budgets = $budgetManager->getUserBudgets($userId);

        $this->render('budget', ['budgets' => $budgets]);
    }

    public function addBudget() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }
        
        // Récupérer les contacts de l'utilisateur connecté
        $userId = $_SESSION['user'];
        $contactManager = new ContactManager();
        $contacts = $contactManager->getAllContacts($userId);
        
        // Afficher le formulaire avec les contacts
        $this->render('add-budget', ['contacts' => $contacts]);
    }

    public function saveBudget() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['add-budget-title'] ?? '';
            $totalPrice = (float) ($_POST['add-budget-price'] ?? 0);
            $categories = $_POST['categories'];

            // Validation simple
            if (empty($title)) {
                $this->render('add-budget', [
                    'error' => 'Le titre du budget est requis',
                    'contacts' => (new ContactManager())->getAllContacts($_SESSION['user'])
                ]);
                return;
            }
            
            // Créer le budget
            $budgetManager = new BudgetManager();
            $budgetId = $budgetManager->createBudget($title, $totalPrice);
            
            // Ajouter les catégories
            foreach ($categories as $categoryData) {
                $budgetManager->addCategory(
                    $budgetId,
                    $categoryData['type'] ?? '',
                    $categoryData['name'] ?? '', 
                    (float) ($categoryData['price'] ?? 0),
                    !empty($categoryData['contact_id']) ? (int) $categoryData['contact_id'] : null
                );
            }
            
            // Rediriger vers la liste des budgets
            header('Location: index.php?route=budgets');
            exit;
        }
        
        // Si ce n'est pas une requête POST, rediriger vers le formulaire
        header('Location: index.php?route=add-budget');
        exit;
    }

    public function editBudget() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }
        
        $budgetId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user'];
        
        if ($budgetId <= 0) {
            header('Location: index.php?route=budgets');
            exit;
        }
        
        $budgetManager = new BudgetManager();
        $budget = $budgetManager->getBudgetById($budgetId, $userId);
        
        if (!$budget) {
            header('Location: index.php?route=budgets');
            exit;
        }
        
        // Récupérer les contacts de l'utilisateur
        $contactManager = new ContactManager();
        $contacts = $contactManager->getAllContacts($userId);
        
        $this->render('edit-budget', [
            'budget' => $budget,
            'contacts' => $contacts
        ]);
    }

    public function updateBudget() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $budgetId = (int) ($_POST['budget_id'] ?? 0);
            $title = $_POST['edit-budget-title'] ?? '';
            $totalPrice = (float) ($_POST['edit-budget-price'] ?? 0);
            $categories = json_decode($_POST['categories_data'] ?? '[]', true);
            $userId = $_SESSION['user'];
            
            if ($budgetId <= 0 || empty($title)) {
                header('Location: index.php?route=budgets');
                exit;
            }
            
            $budgetManager = new BudgetManager();
            
            // Mettre à jour le budget
            $success = $budgetManager->updateBudget($budgetId, $title, $totalPrice, $userId);
            
            if ($success) {
                // Supprimer les anciennes catégories et ajouter les nouvelles
                $this->db->prepare("DELETE FROM budget_categories WHERE budget_id = ?")->execute([$budgetId]);
                
                foreach ($categories as $categoryData) {
                    $budgetManager->addCategory(
                        $budgetId,
                        $categoryData['type'] ?? '',
                        $categoryData['name'] ?? '', 
                        (float) ($categoryData['price'] ?? 0),
                        !empty($categoryData['contact_id']) ? (int) $categoryData['contact_id'] : null
                    );
                }
            }
            
            // Rediriger vers la liste des budgets
            header('Location: index.php?route=budgets');
            exit;
        }
        
        // Si ce n'est pas une requête POST, rediriger vers la liste des budgets
        header('Location: index.php?route=budgets');
        exit;
    }

    public function deleteBudget() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }
        
        $budgetId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user'];
        
        if ($budgetId > 0) {
            $budgetManager = new BudgetManager();
            $budgetManager->deleteBudget($budgetId, $userId);
        }
        
        // Rediriger vers la liste des budgets
        header('Location: index.php?route=budgets');
        exit;
    }

    public function updateCategoriePrice() : void
    {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer le nouveau prix de la catégorie depuis le formulaire
            $price = (int)($_POST['add-categorie-price'] ?? 0);
            
            // Stocker le prix dans la session
            $_SESSION['categorie_price'] = $price;
            
            // Recalculer les montants par participant
            $amount = $this->splitPrice($price);
            
            // Rediriger vers la page d'ajout de catégorie
            $this->render("add-categorie", [
                'participants' => $_SESSION['selected_participants'] ?? [],
                'amount' => $amount
            ]);
        } else {
            // Rediriger vers la page d'ajout de catégorie en cas de méthode non valide
            header('Location: index.php?route=add-categorie');
            exit;
        }
    }

    public function getCategoryIcon(string $type): string {
        $icons = [
            'food' => 'fa-utensils',
            'transport' => 'fa-car',
            'housing' => 'fa-home',
            'entertainment' => 'fa-film',
            'shopping' => 'fa-shopping-cart',
            'health' => 'fa-medkit',
            'travel' => 'fa-plane',
            'education' => 'fa-graduation-cap',
            // Valeur par défaut
            'default' => 'fa-tag'
        ];
        
        return $icons[$type] ?? $icons['default'];
    }
}