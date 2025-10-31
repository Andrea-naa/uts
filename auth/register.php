<?php
include '../config/conn_db.php';
include '../config/email_config.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Variabel untuk menampung pesan ke user
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi password
    if ($password !== $confirm) {
        $message = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter!";
    } else {
        // Cek apakah email sudah digunakan
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (!$cek) {
            $message = "Terjadi kesalahan: " . mysqli_error($conn);
        } elseif (mysqli_num_rows($cek) > 0) {
            $message = "Email sudah terdaftar!";
        } else {
            // Simpan ke database
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16));

            $sql = "INSERT INTO users (nama, email, password, activation_token, role, status)
                    VALUES ('$nama', '$email', '$hashed', '$token', 'Admin Gudang', 'PENDING')";

            if (mysqli_query($conn, $sql)) {
                $mail = new PHPMailer(true);

                try {
                    // Pengaturan server SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'lulumudhiah1905@gmail.com';      // Email Gmail Anda
                    $mail->Password   = 'etsa wuxj oeyg usss';            // App Password Gmail (16 digit)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    
                    // Tambahan untuk debugging (hapus setelah berhasil)
                    $mail->SMTPDebug  = 0; // 0 = off, 2 = detail debug
                    $mail->Debugoutput = function($str, $level) {
                        error_log("PHPMailer: $str");
                    };
                    
                    // Pengaturan timeout
                    $mail->Timeout = 30;
                    $mail->SMTPKeepAlive = true;
                    
                    // Pengaturan email
                    $mail->setFrom('lulumudhiah1905@gmail.com', 'Admin Gudang');
                    $mail->addAddress($email, $nama);
                    $mail->addReplyTo('lulumudhiah1905@gmail.com', 'Admin Gudang');
                    
                    // Konten email
                    $mail->isHTML(true);
                    $mail->Subject = 'Aktivasi Akun Admin Gudang';
                    
                    // Dapatkan URL base secara dinamis
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $baseUrl = $protocol . "://" . $host . "/usermgmt/auth";
                    
                    $mail->Body = "
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset='UTF-8'>
                        </head>
                        <body style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                            <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px;'>
                                <h2 style='color: #667eea; text-align: center;'>Aktivasi Akun Admin Gudang</h2>
                                <p>Halo <strong>$nama</strong>,</p>
                                <p>Terima kasih telah mendaftar. Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda:</p>
                                <div style='text-align: center; margin: 30px 0;'>
                                    <a href='$baseUrl/activate.php?token=$token' 
                                       style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                        Aktivasi Akun
                                    </a>
                                </div>
                                <p style='color: #666; font-size: 14px;'>Atau copy link berikut ke browser Anda:</p>
                                <p style='background: #f0f0f0; padding: 10px; word-break: break-all; font-size: 12px;'>
                                    $baseUrl/activate.php?token=$token
                                </p>
                                <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                                <p style='color: #999; font-size: 12px; text-align: center;'>
                                    Jika Anda tidak merasa mendaftar, abaikan email ini.
                                </p>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    // Alternative plain text untuk email client yang tidak mendukung HTML
                    $mail->AltBody = "Halo $nama,\n\n" .
                                    "Silakan klik link berikut untuk aktivasi akun:\n" .
                                    "$baseUrl/activate.php?token=$token\n\n" .
                                    "Terima kasih!";

                    $mail->send();
                    $message = "<span style='color:green;'>✓ Registrasi berhasil! Silakan cek email Anda untuk aktivasi akun.</span>";
                    
                    // Clear form data setelah berhasil
                    unset($nama, $email);
                    
                } catch (Exception $e) {
                    // Hapus data dari database jika email gagal dikirim
                    mysqli_query($conn, "DELETE FROM users WHERE email='$email' AND activation_token='$token'");
                    
                    $message = "<span style='color:red;'>Email gagal dikirim. Error: {$mail->ErrorInfo}</span>";
                    
                    // Log error untuk debugging
                    error_log("PHPMailer Error: " . $mail->ErrorInfo);
                }
            } else {
                $message = "Gagal menyimpan data. Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin Gudang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #FFB700FF 0%, #FFFB00FF 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        h3 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        input[type=text],
        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type=text]:focus,
        input[type=email]:focus,
        input[type=password]:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: #5568d3;
        }

        button:active {
            transform: scale(0.98);
        }

        .msg {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: -10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h3>Registrasi Admin Gudang</h3>
        <form method="POST" action="">
            <label>Nama Lengkap:</label>
            <input type="text" name="nama" value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>

            <label>Password:</label>
            <input type="password" name="password" minlength="6" required>
            <div class="password-hint">Minimal 6 karakter</div>

            <label>Konfirmasi Password:</label>
            <input type="password" name="confirm_password" minlength="6" required>

            <button type="submit" name="register">Daftar Sekarang</button>
        </form>
        
        <?php if (!empty($message)): ?>
            <div class="msg"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="links">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>

</html>