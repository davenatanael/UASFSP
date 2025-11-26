<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'dosen') {
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

$message = "";
$group_name = "Grup Tidak Ditemukan";

$sql_check = "SELECT nama FROM grup WHERE idgrup = ? AND username_pembuat = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "is", $idgrup, $username_dosen);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) === 0) {
    header("Location: manage_group_dosen.php?msg=" . urlencode("❌ Akses ditolak"));
    exit;
}

$group_name = mysqli_fetch_assoc($result_check)['nama'];

// ---------- INSERT EVENT ----------
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['action'] === 'insert') {
    $judul = $_POST['judul'];
    $tanggal = $_POST['tanggal'];
    $jenis = $_POST['jenis'];
    $keterangan = $_POST['keterangan'];

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul)));

    $sql_insert = "INSERT INTO event (idgrup, judul, `judul-slug`, tanggal, keterangan, jenis)
    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt, "isssss", $idgrup, $judul, $slug, $tanggal, $keterangan, $jenis);

    if (mysqli_stmt_execute($stmt)) {
    header("Location: manage_event.php?idgrup=$idgrup&msg=" . urlencode("✅ Event berhasil ditambahkan"));
exit;
} else {
     $message = "❌ Gagal menambahkan event: " . mysqli_error($conn); 
      }
}

// ---------- DELETE EVENT ----------
if (isset($_GET['action'], $_GET['idevent']) && $_GET['action'] === 'delete') {
    $idevent = (int)$_GET['idevent'];

    $sql_del = "DELETE FROM event WHERE idevent = ? AND idgrup = ?";
    $stmt_del = mysqli_prepare($conn, $sql_del);
    mysqli_stmt_bind_param($stmt_del, "ii", $idevent, $idgrup);
    mysqli_stmt_execute($stmt_del);

    header("Location: manage_event.php?idgrup=$idgrup&msg=" . urlencode("✅ Event dihapus"));
    exit;
}

// ---------- LIST EVENT ----------
$event_list = [];
$sql_events = "SELECT * FROM event WHERE idgrup = ? ORDER BY tanggal DESC";
$stmt_events = mysqli_prepare($conn, $sql_events);
mysqli_stmt_bind_param($stmt_events, "i", $idgrup);
mysqli_stmt_execute($stmt_events);
$result_events = mysqli_stmt_get_result($stmt_events);

while ($row = mysqli_fetch_assoc($result_events)) {
    $event_list[] = $row;
}

if (isset($_GET['msg'])) {
    $message = urldecode($_GET['msg']);
}

mysqli_close($conn);
?>


<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Kelola Event Grup: <?php echo htmlspecialchars($group_name); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #E0D9D9; margin: 0; }
        header { background-color: #2F5755; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h2 { margin: 0; }
        nav a { color: white; text-decoration: none; background-color: #2F5755; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        main { padding: 40px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2F5755; text-decoration: none; font-weight: bold; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .content-container { display: flex; gap: 30px; margin-top: 20px; }
        .form-box, .list-box { flex: 1; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-box h3, .list-box h3 { color: #2F5755; margin-top: 0; }
        
        input[type="text"], input[type="date"], textarea, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 10px; }
        .submit-button { background-color: #5cb85c; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .action-link { color: #2F5755; text-decoration: none; font-weight: bold; margin-right: 5px;}
    </style>
</head>
<body>

<header>
    <h2>Kelola Event Grup: <?php echo htmlspecialchars($group_name); ?></h2>
    <nav>
        <a href="detail_group.php?idgrup=<?php echo $idgrup; ?>">Detail Grup</a> 
        <a href="manage_group_dosen.php">Kelola Grup</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <a href="detail_group.php?idgrup=<?php echo $idgrup; ?>" class="back-link">&leftarrow; Kembali ke Detail Grup</a>
    
    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="content-container">
        <div class="form-box">
            <h3>➕ Tambah Event Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="insert">
                <label for="judul">Judul Event</label>
                <input type="text" id="judul" name="judul" required>
                
                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" required>
                
                <label for="jenis">Jenis Event</label>
                <select id="jenis" name="jenis" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="Rapat">Rapat</option>
                    <option value="Kuliah">Kuliah</option>
                    <option value="Deadline">Deadline</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
                
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3" required></textarea>

                <button type="submit" class="submit-button">Simpan Event</button>
            </form>
        </div>

        <div class="list-box">
            <h3>🗓️ Daftar Event Grup</h3>
            <?php if (!empty($event_list)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Jenis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($event_list as $event): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($event['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($event['judul']); ?></td>
                                <td><?php echo htmlspecialchars($event['jenis']); ?></td>
                                <td>
                                    <a class="action-link" href="edit_event.php?idgrup=<?php echo $idgrup; ?>&idevent=<?php echo $event['idevent']; ?>">Edit</a>
                                    
                                    <a class="action-link" href="manage_event.php?idgrup=<?php echo $idgrup; ?>&action=delete&idevent=<?php echo $event['idevent']; ?>" onclick="return confirm('Yakin ingin menghapus event ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada Event yang terdaftar untuk Grup ini. Silakan gunakan formulir di samping untuk menambahkan.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

</body>
</html>