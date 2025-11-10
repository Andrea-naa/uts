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
                    $mail->Username   = 'noeemeitin@gmail.com';      // Email Gmail Anda
                    $mail->Password   = 'nmaf lcvy xiax ayix';            // App Password Gmail (16 digit)
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
                    $mail->setFrom('noeemeitin@gmail.com', 'Admin Gudang');
                    $mail->addAddress($email, $nama);
                    $mail->addReplyTo('noeemeitin@gmail.com', 'Admin Gudang');
                    
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .form-container {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h3 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        input[type=text],
        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
        }

        input[type=text]:focus,
        input[type=email]:focus,
        input[type=password]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        input[type=text]:focus + label,
        input[type=email]:focus + label,
        input[type=password]:focus + label {
            color: #667eea;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        button:hover::before {
            left: 100%;
        }

        button:active {
            transform: translateY(0);
        }

        .msg {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .links {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .links a:hover::after {
            width: 100%;
        }

        .links a:hover {
            color: #764ba2;
        }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: -15px;
            margin-bottom: 15px;
            padding-left: 5px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h3>Registrasi Admin Gudang</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">Nama Lengkap:</label>
                <input type="text" id="nama" name="nama" value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" minlength="6" required>
                <div class="password-hint">Minimal 6 karakter</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
            </div>

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