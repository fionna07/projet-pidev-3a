<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationEmail(string $recipientEmail, string $confirmationLink)
    {
        $emailContent = "
            Bonjour,
            <br><br>
            Nous avons bien reçu votre inscription. Veuillez cliquer sur le lien ci-dessous pour confirmer votre inscription :
            <br><br>
            <a href=\"$confirmationLink\">Confirmer mon inscription</a>
            <br><br>
            Cordialement,<br>
            L'équipe de votre application
        ";

        $email = (new Email())
            ->from('wefarmapplication@gmail.com')
            ->to($recipientEmail)
            ->subject('Confirmation de votre inscription')
            ->html($emailContent);

            try {
                $this->mailer->send($email);
                echo 'Email envoyé avec succès !';
            } catch (\Exception $e) {
                echo 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage();
            }
    }


    public function sendTransactionNotification(string $clientEmail, string $terrainAdresse, string $transactionType, float $transactionAmount)
    {
        $emailContent = "
            <html>
                <body>
                    <h2>Notification de Transaction</h2>
                    <p>Nous vous informons que vous avez effectué une transaction pour le terrain situé à : <strong>{$terrainAdresse}</strong>.</p>
                    <p>Type de la transaction : <strong>{$transactionType}</strong></p>
                    <p>Montant : <strong>{$transactionAmount} TND</strong></p>
                    <p>Merci pour votre confiance !</p>
                </body>
            </html>
        ";

        $email = (new Email())
            ->from('wefarmapplication@gmail.com')
            ->to($clientEmail)
            ->subject('Notification de Transaction pour Terrain: ' . $terrainAdresse)
            ->html($emailContent);

        try {
            $this->mailer->send($email);
            echo 'Email de notification de transaction envoyé avec succès !';
        } catch (\Exception $e) {
            echo 'Erreur lors de l\'envoi de l\'email de notification de transaction: ' . $e->getMessage();
        }
    }
}
