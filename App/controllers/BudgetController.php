<?php
class BudgetController extends AbstractController
{
    private ContactManager $cm;
    private BudgetManager $bm;

    public function __construct()
    {
        $this->cm = new ContactManager();
        $this->bm = new BudgetManager();

        if (!isset($_SESSION['user'])) {
            return;
        }
    }

    public function listBudgets()
    {
        $userId = $_SESSION['user'];
        $budgets = $this->bm->getUserBudgets($userId);

        $this->render('budget', ['budgets' => $budgets]);
    }

    public function addBudget()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user'];
        $contacts = $this->cm->getAllContacts($userId);

        $this->render('add-budget', ['contacts' => $contacts]);
    }

    public function saveBudget()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['add-budget-title'] ?? '';
            $totalPrice = (float) ($_POST['add-budget-price'] ?? 0);
            $categories = $_POST['categories'];

            $userId = $_SESSION['user'];
            $contacts = $this->cm->getAllContacts($userId);

            if (empty($title)) {
                $this->render('add-budget', [
                    'error' => 'Le titre du budget est requis',
                    'contacts' => $contacts
                ]);
                return;
            }

            $budgetId = $this->bm->createBudget($title, $totalPrice);

            foreach ($categories as $categoryData) {
                $this->bm->addCategory(
                    $budgetId,
                    $categoryData['type'] ?? '',
                    $categoryData['name'] ?? '',
                    (float) ($categoryData['price'] ?? 0),
                    !empty($categoryData['contact_id']) ? (int) $categoryData['contact_id'] : null
                );
            }

            header('Location: index.php?route=budgets');
            exit;
        }

        header('Location: index.php?route=add-budget');
        exit;
    }

    public function deleteBudget()
    {
        $budgetId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user'];

        if ($budgetId > 0) {
            $this->bm->deleteBudget($budgetId, $userId);
        }

        header('Location: index.php?route=budgets');
        exit;
    }

    public function getCategoryIcon(string $type): string
    {
        $icons = [
            'food' => 'fa-utensils',
            'transport' => 'fa-car',
            'housing' => 'fa-home',
            'entertainment' => 'fa-film',
            'shopping' => 'fa-shopping-cart',
            'health' => 'fa-medkit',
            'travel' => 'fa-plane',
            'education' => 'fa-graduation-cap',
            'default' => 'fa-tag'
        ];

        return $icons[$type] ?? $icons['default'];
    }
}
