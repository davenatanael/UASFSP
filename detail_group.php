<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'dosen') {
    header("Location: login.php");
    exit;
}

// Koneksi Database
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
$success_message = "";

// Cek apakah ada notifikasi sukses setelah pembuatan grup
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "✅ Grup berhasil dibuat dan Anda telah ditambahkan sebagai member! Kode pendaftaran grup ini adalah: ";
}

// 1. Ambil Detail Grup dan Pastikan Dosen adalah Pembuatnya
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
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Detail Grup: <?php echo htmlspecialchars($group['nama']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #E0D9D9; margin: 0; }
        header { background-color: #2F5755; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h2 { margin: 0; }
        nav a { color: white; text-decoration: none; background-color: #2F5755; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        main { padding: 40px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2F5755; text-decoration: none; font-weight: bold; }
        .group-header { background-color: #fff; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .group-header h1 { margin-top: 0; color: #2F5755; }
        .code-info { background-color: #e6f7ff; border: 1px solid #91d5ff; padding: 15px; border-radius: 5px; font-size: 1.1em; margin-top: 15px; }
        .code-info strong { color: crimson; font-size: 1.2em; }
        .success-alert { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .management-section { display: flex; justify-content: space-between; gap: 20px; margin-top: 20px; }
        .box { flex: 1; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .box h3 { color: #2F5755; margin-top: 0; border-bottom: 2px solid #2F5755; padding-bottom: 10px; }
        .menu-button { background-color: #2F5755; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px 0; }
        .menu-button.add-event { background-color: #5cb85c; }
    </style>
</head>
<body>

<header>
    <h2>Detail Grup</h2>
    <nav>
        <a href="manage_group_dosen.php">Kelola Grup</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <a href="manage_group_dosen.php" class="back-link">&leftarrow; Kembali ke Daftar Grup</a>
    
    <?php if ($success_message): ?>
        <div class="success-alert">
            <?php echo $success_message; ?>
            <strong style="font-size: 1.3em;"><?php echo htmlspecialchars($group['kode_pendaftaran']); ?></strong>
        </div>
    <?php endif; ?>

    <div class="group-header">
        <h1><?php echo htmlspecialchars($group['nama']); ?> (<?php echo htmlspecialchars($group['jenis']); ?>)</h1>
        <p>Dibuat: <?php echo date('d M Y', strtotime($group['tanggal_pembentukan'])); ?></p>
        <p>Deskripsi: <em><?php echo nl2br(htmlspecialchars($group['deskripsi'])); ?></em></p>
        
        <div class="code-info">
            Kode Pendaftaran Grup: <strong><?php echo htmlspecialchars($group['kode_pendaftaran']); ?></strong>
            <small>(Berikan kode ini kepada Mahasiswa untuk bergabung)</small>
        </div>
    </div>

    <div class="management-section">
        <div class="box" id="event-management">
            <h3>🗓️ Kelola Event Grup</h3>
            <p>Di sini Anda dapat menambahkan, mengubah, atau menghapus kegiatan/pertemuan Grup.</p>
            <a class="menu-button add-event" href="manage_event.php?idgrup=<?php echo $idgrup; ?>">Kelola Event</a>
            
            <h4>Daftar Event (3 Terbaru)</h4>
            <?php
            // Query untuk mengambil event
            $sql_event = "SELECT * FROM event WHERE idgrup = ? ORDER BY tanggal DESC LIMIT 3";
            $stmt_event = mysqli_prepare($conn, $sql_event);
            mysqli_stmt_bind_param($stmt_event, "i", $idgrup);
            mysqli_stmt_execute($stmt_event);
            $result_event = mysqli_stmt_get_result($stmt_event);
            
            if (mysqli_num_rows($result_event) > 0) {
                echo "<ul>";
                while($event = mysqli_fetch_assoc($result_event)) {
                    echo "<li>" . htmlspecialchars($event['judul']) . " - Tgl: " . date('d/m/Y', strtotime($event['tanggal'])) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Belum ada Event yang ditambahkan.</p>";
            }
            ?>
        </div>

        <div class="box" id="member-management">
            <h3>👥 Kelola Anggota Grup</h3>
            <p>Tambahkan atau hapus Mahasiswa/Dosen dari Grup ini.</p>
            <a class="menu-button" href="manage_member.php?idgrup=<?php echo $idgrup; ?>">Kelola Anggota</a>
            
            <h4>Jumlah Anggota</h4>
            <?php
            $sql_count = "SELECT COUNT(*) AS total FROM member_grup WHERE idgrup = ?";
            $stmt_count = mysqli_prepare($conn, $sql_count);
            mysqli_stmt_bind_param($stmt_count, "i", $idgrup);
            mysqli_stmt_execute($stmt_count);
            $result_count = mysqli_stmt_get_result($stmt_count);
            $total_members = mysqli_fetch_assoc($result_count)['total'];
            ?>
            <p style="font-size: 2em; font-weight: bold; color: #333;"><?php echo $total_members; ?> Anggota</p>
        </div>
    </div>
</main>

<?php
mysqli_close($conn);
?>
</body>
</html>