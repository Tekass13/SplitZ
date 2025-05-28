<?php

class ParticipationController extends AbstractController
{
    private BudgetManager $budgetManager;

    public function __construct()
    {
        $this->budgetManager = new BudgetManager();
    }

    public function listParticipations(): void
    {
        $userId = $_SESSION['user'];
        $participations = $this->budgetManager->getCategoryParticipations($userId);

        $this->render("participations", [
            'participations' => $participations
        ]);
    }

    public function confirmParticipation(): void
    {
        if (!isset($_GET['category_id']) || !isset($_GET['user_id'])) {
            $this->redirect("index.php?route=participations");
            return;
        }

        $categoryId = (int)$_GET['category_id'];
        $userId = (int)$_GET['user_id'];
        $currentUser = $_SESSION['user'];

        if ($userId !== $currentUser) {
            $this->redirect("index.php?route=participations&error=unauthorized");
            return;
        }

        $result = $this->budgetManager->confirmParticipation($categoryId, $userId);

        if ($result) {
            $this->redirect("index.php?route=participations&success=confirmed");
        } else {
            $this->redirect("index.php?route=participations&error=confirmation_failed");
        }
    }

    public function confirmPayment(): void
    {
        if (!isset($_GET['category_id']) || !isset($_GET['user_id'])) {
            $this->redirect("index.php?route=payments");
            return;
        }

        $categoryId = (int)$_GET['category_id'];
        $userId = (int)$_GET['user_id'];
        $currentUser = $_SESSION['user'];

        if ($userId !== $currentUser) {
            $this->redirect("index.php?route=payments&error=unauthorized");
            return;
        }

        $result = $this->budgetManager->confirmPayment($categoryId, $userId);

        if ($result) {
            $this->redirect("index.php?route=payments&success=confirmed");
        } else {
            $this->redirect("index.php?route=payments&error=confirmation_failed");
        }
    }

    function displayAlerts()
    {
        $alertTypes = [
            'success' => ['confirmed' => 'Participation confirmée avec succès!'],
            'error' => [
                'confirmation_failed' => 'La confirmation de la participation a échoué.',
                'unauthorized' => 'Vous n\'êtes pas autorisé à confirmer cette participation.'
            ]
        ];

        foreach ($alertTypes as $type => $messages) {
            if (isset($_GET[$type])) {
                $key = $_GET[$type];
                if (isset($messages[$key])) {
                    echo "<p class=\"alert alert-{$type}\">{$messages[$key]}</p>";
                }
            }
        }
    }

    function renderTable(array $participations, string $status_field, string $status_value, string $title, bool $include_actions = false)
    {
        $found = false;
        $status_class = '';
        $empty_text = strtolower(str_replace(['Participations ', 'Paiements '], '', $title));
        $colspan = $include_actions ? 7 : 6;
        $route = $status_field === 'invitation' ? 'confirm-participation' : 'confirm-payment';

        if (strtolower($status_value) == 'confirmé') {
            $status_class = 'confirmed';
        } else {
            $status_class = 'pending';
        }

        echo "<h2>{$title}</h2>";
        echo "<table class=\"table-responsive\">";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Budget</th>";
        echo "<th class=\"hide-sm\">Catégorie</th>";
        echo "<th>Type</th>";
        echo "<th>Montant</th>";
        echo "<th class=\"hide-sm\">Créateur</th>";
        echo "<th>Statut</th>";
        if ($include_actions) echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($participations as $participation) {
            if ($participation[$status_field] === $status_value) {
                $found = true;
                echo "<tr>";
                echo "<td data-label=\"Budget\">" . htmlspecialchars($participation['budget_title']) . "</td>";
                echo "<td data-label=\"Catégorie\" class=\"hide-sm\">" . htmlspecialchars($participation['category_name']) . "</td>";
                echo "<td data-label=\"Type\">" . htmlspecialchars($participation['category_type']) . "</td>";
                echo "<td data-label=\"Montant\">" . number_format($participation['participant_amount'], 2) . " €</td>";
                echo "<td data-label=\"Créateur\" class=\"hide-sm\">" . htmlspecialchars($participation['creator_name']) . "</td>";
                echo "<td data-label=\"Statut\"><span class=\"status {$status_class}\">{$status_value}</span></td>";

                if ($include_actions) {
                    echo "<td data-label=\"Actions\">";
                    echo "<a href=\"index.php?route={$route}&category_id={$participation['category_id']}&user_id={$_SESSION['user']}\" class=\"btn btn-primary\">Confirmer</a>";
                    echo "</td>";
                }

                echo "</tr>";
            }
        }

        if (!$found) {
            echo "<tr><td colspan='{$colspan}'>Aucune {$empty_text}</td></tr>";
        }

        echo "</tbody>";
        echo "</table>";

        return $found;
    }

    function displayPaymentAlerts()
    {
        $alertTypes = [
            'success' => ['confirmed' => 'Paiement confirmé avec succès!'],
            'error' => [
                'confirmation_failed' => 'La confirmation du paiement a échoué.',
                'unauthorized' => 'Vous n\'êtes pas autorisé à confirmer ce paiement.'
            ]
        ];

        foreach ($alertTypes as $type => $messages) {
            if (isset($_GET[$type])) {
                $key = $_GET[$type];
                if (isset($messages[$key])) {
                    echo "<p class=\"alert alert-{$type}\">{$messages[$key]}</p>";
                }
            }
        }
    }
}
