<?php
include '../config/conn_db.php';

$message = "";

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $query = "SELECT * FROM users WHERE reset_token='$token'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        die("Token tidak valid atau sudah digunakan.");
    }
} else {
    die("Token tidak ditemukan.");
}

if (isset($_POST['change'])) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $message = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = "UPDATE users SET password='$hashed', reset_token=NULL WHERE reset_token='$token'";
        if (mysqli_query($conn, $update)) {
            $message = "<span style='color:green;'>Password berhasil diubah. <a href='login.php'>Login sekarang</a>.</span>";
        } else {
            $message = "Gagal memperbarui password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: Arial; background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); height: 100vh; margin: 0; }
        .form-container {
            width: 350px; margin: 100px auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        h3 { text-align: center; color: #333; }
        input[type=password] {
            width: 93%; padding: 12px; margin: 8px 0;
            border: 1px solid #ddd; border-radius: 5px;
        }
        button { 
            width: 100%; padding: 12px; background: #185a9d; color: white; 
            border: none; cursor: pointer; border-radius: 5px; font-size: 16px;
        }
        button:hover { background: #0f4c81; }
        .msg { margin-top: 15px; color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>🔑 Reset Password</h3>
        <form method="POST">
            <label>Password Baru:</label>
            <input type="password" name="password" minlength="6" required>

            <label>Konfirmasi Password:</label>
            <input type="password" name="confirm_password" minlength="6" required>

            <button type="submit" name="change">Ubah Password</button>
        </form>
        <div class="msg"><?= $message ?></div>
    </div>
</body>
</html>
