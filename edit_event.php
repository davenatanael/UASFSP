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

if (!isset($_GET['idgrup']) || !is_numeric($_GET['idgrup']) || !isset($_GET['idevent']) || !is_numeric($_GET['idevent'])) {
    header("Location: manage_group_dosen.php");
    exit;
}

$idgrup = (int)$_GET['idgrup'];
$idevent = (int)$_GET['idevent'];
$username_dosen = $_SESSION['username'];
$message = "";

// Cek kepemilikan Event & Grup
$sql_event_check = "SELECT e.*, g.nama AS group_name FROM event e JOIN grup g ON e.idgrup = g.idgrup WHERE e.idevent = ? AND e.idgrup = ? AND g.username_pembuat = ?";
$stmt_event_check = mysqli_prepare($conn, $sql_event_check);
mysqli_stmt_bind_param($stmt_event_check, "iis", $idevent, $idgrup, $username_dosen);
mysqli_stmt_execute($stmt_event_check);
$result_event_check = mysqli_stmt_get_result($stmt_event_check);

if (mysqli_num_rows($result_event_check) == 0) {
    $group_name = "Akses Ditolak";
    $message = "❌ Event tidak ditemukan atau Anda tidak berhak mengeditnya.";
    // Jika event tidak ditemukan, langsung redirect
    header("Location: manage_event.php?idgrup=$idgrup&msg=" . urlencode("❌ Event tidak ditemukan atau akses ditolak."));
    exit;
}
$event = mysqli_fetch_assoc($result_event_check);
$group_name = $event['group_name'];

// 2. Proses Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = trim($_POST['judul']);
    $tanggal = $_POST['tanggal'];
    $keterangan = trim($_POST['keterangan']);
    $jenis = $_POST['jenis'];

    $sql_update = "UPDATE event SET judul = ?, tanggal = ?, keterangan = ?, jenis = ? WHERE idevent = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssssi", $judul, $tanggal, $keterangan, $jenis, $idevent);

    if (mysqli_stmt_execute($stmt_update)) {
        $message = "✅ Event **" . htmlspecialchars($judul) . "** berhasil diperbarui!";
        // Muat ulang data event yang baru
        $event['judul'] = $judul;
        $event['tanggal'] = $tanggal;
        $event['keterangan'] = $keterangan;
        $event['jenis'] = $jenis;
    } else {
        $message = "❌ Gagal memperbarui event: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Edit Event: <?php echo htmlspecialchars($event['judul']); ?></title>
    <style>
        /* CSS Konsisten dari manage_event.php */
        body { font-family: Arial, sans-serif; background-color: #E0D9D9; margin: 0; }
        header { background-color: #2F5755; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h2 { margin: 0; }
        nav a { color: white; text-decoration: none; background-color: #2F5755; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        main { padding: 40px; display: flex; justify-content: center; align-items: flex-start; }
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2); width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #2F5755; }
        input[type="text"], input[type="date"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-button { background-color: #5cb85c; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 10px; }
        .submit-button:hover { background-color: #4cae4c; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2F5755; text-decoration: none; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<header>
    <h2>Edit Event: <?php echo htmlspecialchars($event['judul']); ?> (Grup: <?php echo htmlspecialchars($group_name); ?>)</h2>
    <nav>
        <a href="manage_event.php?idgrup=<?php echo $idgrup; ?>">Kelola Event</a>
        <a href="detail_group.php?idgrup=<?php echo $idgrup; ?>">Detail Grup</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <div class="form-container">
        <a href="manage_event.php?idgrup=<?php echo $idgrup; ?>" class="back-link">&leftarrow; Kembali ke Daftar Event</a>
        <h3>Ubah Event</h3>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="edit_event.php?idgrup=<?php echo $idgrup; ?>&idevent=<?php echo $idevent; ?>">
            <div class="form-group">
                <label for="judul">Judul Event</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($event['judul']); ?>" required>
            </div>
            <div class="form-group">
                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d', strtotime($event['tanggal'])); ?>" required>
            </div>
            <div class="form-group">
                <label for="jenis">Jenis Event</label>
                <select id="jenis" name="jenis" required>
                    <option value="Rapat" <?php echo $event['jenis'] == 'Rapat' ? 'selected' : ''; ?>>Rapat</option>
                    <option value="Kuliah" <?php echo $event['jenis'] == 'Kuliah' ? 'selected' : ''; ?>>Kuliah</option>
                    <option value="Deadline" <?php echo $event['jenis'] == 'Deadline' ? 'selected' : ''; ?>>Deadline</option>
                    <option value="Lainnya" <?php echo $event['jenis'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="4" required><?php echo htmlspecialchars($event['keterangan']); ?></textarea>
            </div>
            <button type="submit" class="submit-button">Simpan Perubahan Event</button>
        </form>
    </div>
</main>

</body>
</html>