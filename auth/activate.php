<?php
include '../config/conn_db.php';

$message = "";
$success = false;

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    $sql = "SELECT * FROM users WHERE activation_token='$token' AND status='PENDING'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $update = "UPDATE users SET status='ACTIVE', activation_token=NULL WHERE activation_token='$token'";
        if (mysqli_query($conn, $update)) {
            $message = "Akun berhasil diaktivasi! Silakan login.";
            $success = true;
        } else {
            $message = "Terjadi kesalahan saat aktivasi.";
        }
    } else {
        $message = "Token tidak valid atau akun sudah aktif.";
    }
} else {
    $message = "Token aktivasi tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aktivasi Akun</title>
    <style>
        body { font-family: Arial; background-color: #f0f0f0; }
        .container {
            width: 400px; margin: 100px auto; background: white; padding: 30px;
            border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success { color: #4CAF50; font-size: 18px; }
        .error { color: #f44336; font-size: 18px; }
        .btn { 
            display: inline-block; margin-top: 20px; padding: 10px 20px; 
            background: #2196F3; color: white; text-decoration: none; 
            border-radius: 5px;
        }
        .btn:hover { background: #0b7dda; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Aktivasi Akun</h2>
        <p class="<?= $success ? 'success' : 'error' ?>">
            <?= $message ?>
        </p>
        <?php if ($success): ?>
            <a href="login.php" class="btn">Login Sekarang</a>
        <?php else: ?>
            <a href="register.php" class="btn">Kembali ke Registrasi</a>
        <?php endif; ?>
    </div>
</body>
</html>