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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logout-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .nav-tabs {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-tabs a {
            padding: 12px 25px;
            margin-right: 15px;
            text-decoration: none;
            color: #666;
            border-radius: 10px;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-tabs a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .nav-tabs a.active {
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .nav-tabs a.active::before {
            left: 0;
        }

        .content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .content h3 {
            color: #333;
            margin: 40px 0 20px 0;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:active {
            transform: translateY(0);
        }

        .message {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-box {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-box p {
            margin: 10px 0;
            color: #0d47a1;
            font-size: 16px;
            font-weight: 500;
        }

        hr {
            margin: 40px 0;
            border: none;
            border-top: 2px solid #e1e5e9;
            position: relative;
        }

        hr::after {
            content: '';
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .nav-tabs a {
                margin-bottom: 10px;
                margin-right: 0;
                width: 100%;
                text-align: center;
            }

            .content {
                padding: 20px;
            }

            .info-box {
                padding: 20px;
            }
        }
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