<?php
session_start();
include '../config/conn_db.php';

$message = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if ($user['status'] != 'ACTIVE') {
            $message = "Akun belum diaktivasi. Silakan cek email Anda.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            $message = "Password salah.";
        }
    } else {
        $message = "Email tidak terdaftar.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial; background: linear-gradient(135deg, #FFFF00FF 0%, #FFAB03FF 100%); height: 100vh; margin: 0; }
        .form-container {
            width: 350px; margin: 80px auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        h3 { text-align: center; color: #333; }
        input[type=email], input[type=password] {
            width: 93%; padding: 12px; margin: 8px 0;
            border: 1px solid #ddd; border-radius: 5px;
        }
        button { 
            width: 100%; padding: 12px; background: #667eea; color: white; 
            border: none; cursor: pointer; border-radius: 5px; font-size: 16px;
        }
        button:hover { background: #5568d3; }
        .msg { margin-top: 10px; color: red; text-align: center; }
        .links { margin-top: 15px; text-align: center; font-size: 14px; }
        .links a { color: #667eea; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>Login Admin Gudang</h3>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit" name="login">Login</button>
        </form>
        <div class="msg"><?= $message ?></div>
        <div class="links">
            <a href="forgot_password.php">Lupa Password?</a> | 
            <a href="register.php">Belum punya akun?</a>
        </div>
    </div>
</body>
</html>