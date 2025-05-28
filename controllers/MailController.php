<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailController extends AbstractController
{
    public function __construct() {}

    public function sendMail()
    {
        $message_erreur = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupération et nettoyage des données
            $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : "";
            $sujet = isset($_POST['sujet']) ? htmlspecialchars(trim($_POST['sujet'])) : "Sans sujet";
            $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : "";

            // Validation des champs
            if (empty($email) || empty($message)) {
                $message_erreur = "Tous les champs marqués d'un * sont obligatoires.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message_erreur = "L'adresse email n'est pas valide.";
            } else {
                // Créer une nouvelle instance de PHPMailer
                $mail = new PHPMailer(true);

                try {
                    // Configuration du serveur
                    $mail->SMTPDebug = SMTP::DEBUG_OFF;          // Activer le debug verbose (0 = off, 1 = client messages, 2 = client et serveur)
                    $mail->isSMTP();                             // Utiliser SMTP
                    $mail->Host       = 'sandbox.smtp.mailtrap.io';        // Serveur SMTP Gmail
                    $mail->SMTPAuth   = true;                    // Activer l'authentification SMTP
                    $mail->Username   = 'd1ccaf4e224a43'; // Adresse email SMTP
                    $mail->Password   = '09f8d25c4406f9'; // Mot de passe SMTP ou mot de passe d'application
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Activer le chiffrement TLS
                    $mail->Port       = 2525;                     // Port TCP pour se connecter

                    // Destinataires
                    $mail->setFrom('test@email.com', 'test');
                    $mail->addAddress('test@email.com');  // Destinataire
                    $mail->addReplyTo($email);                   // Adresse de réponse

                    // Contenu
                    $mail->isHTML(false);                        // Format de l'email (false = texte brut)
                    $mail->Subject = $sujet;
                    $mail->Body    = "Message de : $email\n\n$message";

                    $mail->send();
                    $_SESSION['message_success'] = "Votre message a été envoyé avec succès.";
                    $this->redirect('index.php?route=contact');
                    return;
                } catch (Exception $e) {
                    $message_erreur = "Le message n'a pas pu être envoyé. Erreur : " . $mail->ErrorInfo;
                }
            }

            $_SESSION['message_erreur'] = $message_erreur;
            $this->redirect('index.php?route=contact');
        }
    }
}
