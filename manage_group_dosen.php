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

$username_dosen = $_SESSION['username'];

$sql = "SELECT * FROM grup WHERE username_pembuat = ? ORDER BY tanggal_pembentukan DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username_dosen);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Kelola Grup</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #E0D9D9; margin: 0; }
        header { background-color: #2F5755; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h2 { margin: 0; }
        nav a { color: white; text-decoration: none; background-color: #2F5755; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        main { padding: 40px; }
        .menu-button { background-color: #2F5755; color: white; padding: 10px 25px; margin: 10px 0; border: none; border-radius: 8px; font-size: 15px; text-decoration: none; display: inline-block;}
        .logout { background-color: crimson; }
        .table-container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-top: 20px;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .action-link { color: #2F5755; text-decoration: none; font-weight: bold; margin-right: 10px;}
        .action-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<header>
    <h2>Kelola Grup Saya</h2>
    <nav>
        <a href="dosen_home.php">Home</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <h2>Daftar Grup yang Anda Kelola</h2>
    <a class="menu-button" href="add_group.php">➕ Tambah Grup Baru</a>

    <div class="table-container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nama Grup</th>
                    <th>Deskripsi</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['tanggal_pembentukan'])); ?></td>
                        <td>
                            <a class="action-link" href="detail_group.php?idgrup=<?php echo $row['idgrup']; ?>">Detail & Kelola</a>
                            |
                            <a class="action-link" href="edit_group.php?idgrup=<?php echo $row['idgrup']; ?>">Edit Grup</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Anda belum membuat Grup apa pun.</p>
    <?php endif; ?>
    </div>
</main>

<?php
mysqli_close($conn);
?>
</body>
</html>