<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/conn_db.php';

$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM products WHERE id=$edit_id");
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_product = mysqli_fetch_assoc($edit_result);
    }
}

if (isset($_POST['add_product'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stok = intval($_POST['stok']);
    $harga = floatval($_POST['harga']);
    $created_by = $_SESSION['user_id'];

    $sql = "INSERT INTO products (nama_produk, sku, stok, harga, created_by)
            VALUES ('$nama', '$sku', $stok, $harga, $created_by)";
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit();
}

if (isset($_POST['update_product'])) {
    $id = intval($_POST['product_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stok = intval($_POST['stok']);
    $harga = floatval($_POST['harga']);

    $sql = "UPDATE products SET nama_produk='$nama', sku='$sku', stok=$stok, harga=$harga WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit();
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

        .header .user-info {
            color: #666;
            font-size: 14px;
            font-weight: 500;
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
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

        .table-container {
            overflow-x: auto;
            margin-top: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f0f2f5;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        table tr:hover td {
            background-color: #f8f9ff;
        }

        .action-btns a {
            padding: 8px 16px;
            margin-right: 8px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .edit-btn {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        }

        .delete-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .content h3 {
            color: #333;
            margin: 40px 0 20px 0;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px;
            }

            .table-container {
                margin: 20px -10px 0;
            }
        }
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
            <?php if ($edit_product): ?>
                <h3>Edit Produk</h3>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Produk:</label>
                            <input type="text" name="nama_produk" value="<?= htmlspecialchars($edit_product['nama_produk']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>SKU:</label>
                            <input type="text" name="sku" value="<?= htmlspecialchars($edit_product['sku']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Stok:</label>
                            <input type="number" name="stok" value="<?= $edit_product['stok'] ?>" required min="0">
                        </div>
                        <div class="form-group">
                            <label>Harga (Rp):</label>
                            <input type="number" name="harga" value="<?= $edit_product['harga'] ?>" required min="0" step="0.01">
                        </div>
                    </div>
                    <button type="submit" name="update_product" class="btn">Update Produk</button>
                    <a href="index.php" class="btn" style="background: linear-gradient(135deg, #95a5a6, #7f8c8d); margin-left: 10px;">Batal</a>
                </form>
            <?php else: ?>
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
            <?php endif; ?>

            <h3>Daftar Produk</h3>
            <div class="table-container">
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
                            <a href="?edit=<?= $row['id'] ?>" class="edit-btn">Edit</a>
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