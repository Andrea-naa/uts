<?php
include '../config/conn_db.php';
include '../config/email_config.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if (isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek email aktif
    $sql = "SELECT * FROM users WHERE email='$email' AND status='ACTIVE'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(16));
        $update = "UPDATE users SET reset_token='$token' WHERE email='$email'";
        
        if (mysqli_query($conn, $update)) {
            $link = "http://localhost/USERMGMT/auth/reset_password.php?token=$token";

            // Kirim email menggunakan PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = 'tls';
                $mail->Port = SMTP_PORT;

                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Password - Sistem Gudang';
                $mail->Body = "
                    <h3>Hai!</h3>
                    <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
                    <p>Klik tautan berikut untuk membuat password baru:</p>
                    <a href='$link'>$link</a>
                    <br><br>
                    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                    <p>Salam,<br><b>Sistem Gudang</b></p>
                ";

                $mail->send();
                $message = "<span style='color:green;'>Link reset password telah dikirim ke email Anda.</span>";
            } catch (Exception $e) {
                $message = "Gagal mengirim email. Error: {$mail->ErrorInfo}";
            }
        }
    } else {
        $message = "Email tidak terdaftar atau akun belum aktif.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password</title>
    <style>
        body { font-family: Arial; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); height: 100vh; margin: 0; }
        .form-container {
            width: 350px; margin: 100px auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        h3 { text-align: center; color: #333; }
        input[type=email] {
            width: 93%; padding: 12px; margin: 8px 0;
            border: 1px solid #ddd; border-radius: 5px;
        }
        button { 
            width: 100%; padding: 12px; background: #f5576c; color: white; 
            border: none; cursor: pointer; border-radius: 5px; font-size: 16px;
        }
        button:hover { background: #e04454; }
        .msg { margin-top: 15px; color: blue; font-size: 14px; text-align:center; }
        .links { margin-top: 15px; text-align: center; }
        .links a { color: #f5576c; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>🔒 Lupa Password</h3>
        <p style="text-align: center; color: #666; font-size: 14px;">
            Masukkan email Anda untuk reset password
        </p>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            <button type="submit" name="reset">Kirim Link Reset</button>
        </form>
        <div class="msg"><?= $message ?></div>
        <div class="links">
            <a href="login.php">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
