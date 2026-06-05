<?php
/**
 * Mailer — wrapper sobre PHPMailer 6.x para Valle Verde
 *
 * Uso básico:
 *   $mailer = new \VV\Mail\Mailer();
 *   $mailer->send('destino@email.com', 'Asunto', '<p>HTML...</p>');
 */

namespace VV\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

// Autoload de PHPMailer (instalado en utilities/includes/PHPMailer/src/)
require_once dirname(__DIR__) . '/includes/PHPMailer/src/Exception.php';
require_once dirname(__DIR__) . '/includes/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/includes/PHPMailer/src/SMTP.php';

class Mailer
{
    private string $host     = 'smtp.gmail.com';
    private int    $port     = 587;
    private string $username = 'comiteasesorvalleverde@gmail.com';
    private string $password = 'utic oobq nydk dyih';
    private string $fromName = 'Valle Verde';

    /**
     * Envía un correo HTML a un destinatario.
     *
     * @param  string $to       Dirección del destinatario.
     * @param  string $subject  Asunto del correo.
     * @param  string $htmlBody Cuerpo HTML.
     * @param  string $toName  Nombre del destinatario (opcional).
     * @return bool   true si fue enviado, false si hubo error.
     * @throws MailerException  (silenciado internamente; usar el valor de retorno).
     */
    public function send(string $to, string $subject, string $htmlBody, string $toName = ''): bool
    {
        $mail = new PHPMailer(true); // true = excepciones activadas

        try {
            // ── Servidor SMTP ────────────────────────────────────────────────
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->Port       = $this->port;
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->CharSet    = 'UTF-8';

            // ── Remitente y destinatario ──────────────────────────────────────
            $mail->setFrom($this->username, $this->fromName);
            $mail->addAddress($to, $toName);

            // ── Contenido ────────────────────────────────────────────────────
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
            return true;

        } catch (MailerException $e) {
            // El error queda en $mail->ErrorInfo; lo relanzamos como RuntimeException
            // para que el llamador lo capture si quiere detalle.
            throw new \RuntimeException('Mailer error: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    /**
     * Envía correos a múltiples destinatarios reutilizando una sola conexión SMTP.
     * Cada elemento de $recipients: ['email' => '...', 'name' => '...', 'html' => '...']
     * Devuelve ['sent' => int, 'errors' => string[]].
     */
    public function sendBatch(string $subject, array $recipients): array
    {
        $sent   = 0;
        $errors = [];

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host          = $this->host;
        $mail->Port          = $this->port;
        $mail->SMTPAuth      = true;
        $mail->SMTPSecure    = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username      = $this->username;
        $mail->Password      = $this->password;
        $mail->CharSet       = 'UTF-8';
        $mail->SMTPKeepAlive = true; // una sola conexión para todo el batch
        $mail->Timeout       = 15;  // seg. por operación SMTP — evita colgar indefinidamente

        foreach ($recipients as $r) {
            $email = trim($r['email'] ?? '');
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email inválido omitido: '{$email}'";
                continue;
            }
            try {
                $mail->clearAddresses();
                $mail->setFrom($this->username, $this->fromName);
                $mail->addAddress($email, $r['name'] ?? '');
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $r['html'];
                $mail->AltBody = strip_tags($r['html']);
                $mail->send();
                $sent++;
            } catch (MailerException $e) {
                $errors[] = "Error enviando a {$email}: " . $mail->ErrorInfo;
            }
        }

        $mail->smtpClose();
        return ['sent' => $sent, 'errors' => $errors];
    }
}
