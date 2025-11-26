<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'dosen') {
    header("Location: login.php");
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "fullstack");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (!isset($_GET['idgrup']) || !is_numeric($_GET['idgrup'])) {
    header("Location: manage_group_dosen.php");
    exit;
}

$idgrup = (int)$_GET['idgrup'];
$username_dosen = $_SESSION['username'];
$group = null;
$message = "";

// 1. Ambil data grup
$sql_group = "SELECT * FROM grup WHERE idgrup = ? AND username_pembuat = ?";
$stmt_group = mysqli_prepare($conn, $sql_group);
mysqli_stmt_bind_param($stmt_group, "is", $idgrup, $username_dosen);
mysqli_stmt_execute($stmt_group);
$result_group = mysqli_stmt_get_result($stmt_group);

if (mysqli_num_rows($result_group) > 0) {
    $group = mysqli_fetch_assoc($result_group);
} else {
    // Grup tidak ditemukan atau bukan milik dosen ini
    header("Location: manage_group_dosen.php");
    exit;
}

// 2. Proses Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $jenis = $_POST['jenis'];

    $sql_update = "UPDATE grup SET nama = ?, deskripsi = ?, jenis = ? WHERE idgrup = ? AND username_pembuat = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "sssis", $nama, $deskripsi, $jenis, $idgrup, $username_dosen);

    if (mysqli_stmt_execute($stmt_update)) {
        $message = "✅ Grup **" . htmlspecialchars($nama) . "** berhasil diperbarui!";
        // Muat ulang data grup yang baru
        $group['nama'] = $nama;
        $group['deskripsi'] = $deskripsi;
        $group['jenis'] = $jenis;
    } else {
        $message = "❌ Gagal memperbarui grup: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Edit Grup: <?php echo htmlspecialchars($group['nama']); ?></title>
    <style>
        /* Gunakan Style yang Konsisten */
        body { font-family: Arial, sans-serif; background-color: #E0D9D9; margin: 0; }
        header { background-color: #2F5755; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h2 { margin: 0; }
        nav a { color: white; text-decoration: none; background-color: #2F5755; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        main { padding: 40px; display: flex; justify-content: center; align-items: flex-start; }
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2); width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #2F5755; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-button { background-color: #2F5755; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 10px; }
        .submit-button:hover { background-color: #1a3837; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2F5755; text-decoration: none; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<header>
    <h2>Edit Grup: <?php echo htmlspecialchars($group['nama']); ?></h2>
    <nav>
        <a href="manage_group_dosen.php">Kelola Grup</a>
        <a href="detail_group.php?idgrup=<?php echo $idgrup; ?>">Detail Grup</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <div class="form-container">
        <a href="manage_group_dosen.php" class="back-link">&leftarrow; Kembali ke Daftar Grup</a>
        <h3>Ubah Data Grup</h3>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="edit_group.php?idgrup=<?php echo $idgrup; ?>">
            <div class="form-group">
                <label for="nama">Nama Grup</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($group['nama']); ?>" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($group['deskripsi']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="jenis">Jenis Grup</label>
                <select id="jenis" name="jenis" required>
                    <option value="Kelas" <?php echo $group['jenis'] == 'Kelas' ? 'selected' : ''; ?>>Kelas</option>
                    <option value="Proyek" <?php echo $group['jenis'] == 'Proyek' ? 'selected' : ''; ?>>Proyek</option>
                    <option value="Diskusi" <?php echo $group['jenis'] == 'Diskusi' ? 'selected' : ''; ?>>Diskusi</option>
                    <option value="Lainnya" <?php echo $group['jenis'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>
            <button type="submit" class="submit-button">Simpan Perubahan</button>
        </form>
    </div>
</main>

</body>
</html>