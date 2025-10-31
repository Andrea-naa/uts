<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/conn_db.php';

if (isset($_POST['add_product'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stok = intval($_POST['stok']);
    $harga = floatval($_POST['harga']);
    $created_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO products (nama_produk, sku, stok, harga, created_by) 
            VALUES ('$nama', '$sku', $stok, $harga, $created_by)";
    mysqli_query($conn, $sql);
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: index.php");
    exit();
}

// Get products
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        .header {
            background: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { color: #333; font-size: 24px; }
        .header .user-info { color: #666; font-size: 14px; }
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
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; color: #555; font-weight: 500; }
        .form-group input {
            padding: 10px; border: 1px solid #ddd; border-radius: 5px;
        }
        .btn {
            padding: 12px 24px; background: #114E17FF; color: white;
            border: none; border-radius: 5px; cursor: pointer; font-size: 16px;
        }
        .btn:hover { background: #08FFFFFF; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th { background: #f5f5f5; padding: 12px; text-align: left; font-weight: 600; }
        table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .action-btns a {
            padding: 5px 10px; margin-right: 5px; text-decoration: none;
            border-radius: 3px; font-size: 13px;
        }
        .edit-btn { background: #EEFF00FF; color: white; }
        .delete-btn { background: #F22421FF; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Dashboard Admin Gudang</h1>
            <div class="user-info">
                <?= $_SESSION['user_name'] ?> (<?= $_SESSION['user_role'] ?>) - <?= $_SESSION['user_email'] ?>
            </div>
        </div>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <div class="nav-tabs">
            <a href="index.php" class="active">Data Produk Gudang</a>
            <a href="profile.php">Profil & Password</a>
        </div>

        <div class="content">
            <h2>Manajemen Produk</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Produk:</label>
                        <input type="text" name="nama_produk" required>
                    </div>
                    <div class="form-group">
                        <label>SKU:</label>
                        <input type="text" name="sku" required>
                    </div>
                    <div class="form-group">
                        <label>Stok:</label>
                        <input type="number" name="stok" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp):</label>
                        <input type="number" name="harga" required min="0" step="0.01">
                    </div>
                </div>
                <button type="submit" name="add_product" class="btn">Tambah Produk</button>
            </form>

            <h3 style="margin-top: 30px;">Daftar Produk</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>SKU</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($products)): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($row['sku']) ?></td>
                        <td><?= $row['stok'] ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td class="action-btns">
                            <a href="?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>