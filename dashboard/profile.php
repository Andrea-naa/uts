<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/conn_db.php';

$message = "";

if (isset($_POST['update'])) {
    $user_id = $_SESSION['user_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    
    $sql = "UPDATE users SET nama='$nama' WHERE id=$user_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $nama;
        $message = "Profil berhasil diupdate!";
    }
}

if (isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
    
    if (!password_verify($current, $user['password'])) {
        $message = "Password lama salah!";
    } elseif ($new !== $confirm) {
        $message = "Password baru tidak cocok!";
    } elseif (strlen($new) < 6) {
        $message = "Password minimal 6 karakter!";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id=$user_id");
        $message = "Password berhasil diubah!";
    }
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=" . $_SESSION['user_id']));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Pengguna</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        .header {
            background: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { color: #333; font-size: 24px; }
        .logout-btn {
            padding: 8px 16px; background: #f44336; color: white;
            border: none; border-radius: 5px; cursor: pointer; text-decoration: none;
        }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .nav-tabs {
            background: white; padding: 15px; border-radius: 8px;
            margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .nav-tabs a {
            padding: 10px 20px; margin-right: 10px; text-decoration: none;
            color: #333; border-radius: 5px; display: inline-block;
        }
        .nav-tabs a.active { background: #2196F3; color: white; }
        .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; font-weight: 500; }
        .form-group input {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;
        }
        .btn {
            padding: 12px 24px; background: #4CAF50; color: white;
            border: none; border-radius: 5px; cursor: pointer; font-size: 16px;
        }
        .btn:hover { background: #45a049; }
        .message {
            padding: 15px; background: #d4edda; color: #155724;
            border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;
        }
        .info-box {
            background: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 30px;
        }
        .info-box p { margin: 8px 0; color: #0d47a1; }
        hr { margin: 30px 0; border: none; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Dashboard Admin Gudang</h1>
        </div>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <div class="nav-tabs">
            <a href="index.php">Data Produk</a>
            <a href="profile.php" class="active">Profil & Password</a>
        </div>

        <div class="content">
            <?php if ($message): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <h2>Informasi Akun</h2>
            <div class="info-box">
                <p><strong>Email:</strong> <?= $user['email'] ?></p>
                <p><strong>Role:</strong> <?= $user['role'] ?></p>
                <p><strong>Status:</strong> <span style="color: #4CAF50;"><?= $user['status'] ?></span></p>
            </div>

            <h3>Update Profil</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>
                <button type="submit" name="update" class="btn">Update Profil</button>
            </form>

            <hr>

            <h3>Ubah Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Password Lama:</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>Password Baru:</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru:</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" name="change_password" class="btn">Ubah Password</button>
            </form>
        </div>
    </div>
</body>
</html>