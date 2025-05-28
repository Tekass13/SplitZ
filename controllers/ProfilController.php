<?php
/**
 * Contrôleur pour la gestion du profil utilisateur
 */
class ProfilController extends AbstractController
{
    private ProfilManager $profilManager;
    
    public function __construct()
    {
        $this->profilManager = new ProfilManager();
    }
    
    /**
     * Affiche la page de profil avec les informations de l'utilisateur
     */
    public function showProfil(): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?route=login');
            return;
        }

        $userId = (int) $_SESSION['user'];
        $user = $this->profilManager->getUserById($userId);

        if ($user) {
            $this->render('profil', ['user' => $user]);
        }
    }

    /**
     * Mise à jour du profil utilisateur
     */
    public function updateProfil(): void
    {  
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?route=login');
            return;
        }

        $userId = (int) $_SESSION['user'];
        $user = $this->profilManager->getUserById($userId);

        if (empty($user)) {
            $this->redirect('index.php?route=login');
            return;
        }

        // Récupérer les nouvelles valeurs ou garder les anciennes
        $username = !empty($_POST['username']) ? $_POST['username'] : $user->getUserName();
        $email = !empty($_POST['email']) ? $_POST['email'] : $user->getEmail();

        // Validation de l'email AVANT de faire quoi que ce soit
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format d\'e-mail invalide !';
            $this->redirect('index.php?route=profil');
            return;
        }

        // Mise à jour du nom d'utilisateur
        $result = $this->profilManager->updateUsername($userId, $username);
        if ($result) {
            $_SESSION['username'] = $username;
        }

        // Mise à jour de l'email
        $this->profilManager->updateEmail($userId, $email);

        // Message de succès et redirection
        $this->render('profil', ['user' => $user]);
    }
}