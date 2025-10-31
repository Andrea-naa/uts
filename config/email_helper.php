<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Import PHPMailer manual (bukan composer)
require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

require __DIR__ . '/email_config.php';

function sendActivationEmail($to_email, $to_name, $token)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        // Content
        $activation_link = "http://localhost/usermgmt/auth/activate.php?token=$token";

        $mail->isHTML(true);
        $mail->Subject = 'Aktivasi Akun Admin Gudang';
        $mail->Body    = "
            <h2>Halo, $to_name!</h2>
            <p>Terima kasih telah mendaftar sebagai Admin Gudang.</p>
            <p>Klik link berikut untuk aktivasi akun:</p>
            <p><a href='$activation_link' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none;'>Aktivasi Akun</a></p>
            <p>Atau copy link: <strong>$activation_link</strong></p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
