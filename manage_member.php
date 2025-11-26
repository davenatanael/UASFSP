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
$message = "";


if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Cek kepemilikan Grup, karna kalau selain pembuat tidak boleh akses kelola grub
$sql_check = "SELECT nama FROM grup WHERE idgrup = ? AND username_pembuat = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "is", $idgrup, $username_dosen);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    $message = "❌ Anda tidak berhak mengelola grup ini.";
    $group_name = "Akses Ditolak";
} else {
    $group_name = mysqli_fetch_assoc($result_check)['nama'];
}

// ---  TAMBAH MEMBER ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_member') {
    $input_id = trim($_POST['new_username']); 
    
    $sql_user_check = "SELECT username FROM akun WHERE username = ? OR nrp_mahasiswa = ? OR npk_dosen = ?";
    
    $stmt_user_check = mysqli_prepare($conn, $sql_user_check);
    mysqli_stmt_bind_param($stmt_user_check, "sss", $input_id, $input_id, $input_id);
    mysqli_stmt_execute($stmt_user_check);
    $result_user_check = mysqli_stmt_get_result($stmt_user_check);

    if (mysqli_num_rows($result_user_check) > 0) {
        $user_data = mysqli_fetch_assoc($result_user_check);
        $new_username = $user_data['username']; 

        // Pengcekan apakah pengguna sudah menjadi anggota grup ini atau belum
        $sql_exist = "SELECT username FROM member_grup WHERE idgrup = ? AND username = ?";
        $stmt_exist = mysqli_prepare($conn, $sql_exist);
        mysqli_stmt_bind_param($stmt_exist, "is", $idgrup, $new_username);
        mysqli_stmt_execute($stmt_exist);
        $result_exist = mysqli_stmt_get_result($stmt_exist);

        if (mysqli_num_rows($result_exist) == 0) {
            $sql_insert = "INSERT INTO member_grup (idgrup, username) VALUES (?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "is", $idgrup, $new_username);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                header("Location: manage_member.php?idgrup=$idgrup&msg=" . urlencode("✅ Member **" . htmlspecialchars($new_username) . "** berhasil ditambahkan!"));
                exit;
            } else {
                $message = "❌ Gagal menambahkan member: " . mysqli_error($conn);
            }
        } else {
            $message = "⚠️ Pengguna **" . htmlspecialchars($new_username) . "** sudah menjadi anggota grup ini.";
        }
    } else {
        $message = "❌ Pengguna dengan username/NRP/NPK tersebut tidak ditemukan.";
    }
}

// ---  HAPUS MEMBER ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'remove_member') {
    $member_to_remove = trim($_POST['member_username']);

    // Pastikan dosen tidak menghapus dirinya sendiri (pembuat grup)
    if ($member_to_remove == $username_dosen) {
        $message = "❌ Anda tidak bisa menghapus diri sendiri dari grup yang Anda buat.";
    } else {
        $sql_delete = "DELETE FROM member_grup WHERE idgrup = ? AND username = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "is", $idgrup, $member_to_remove);

        if (mysqli_stmt_execute($stmt_delete)) {
             header("Location: manage_member.php?idgrup=$idgrup&msg=" . urlencode("✅ Member **" . htmlspecialchars($member_to_remove) . "** berhasil dikeluarkan."));
            exit;
        } else {
            $message = "❌ Gagal mengeluarkan member: " . mysqli_error($conn);
        }
    }
}

// --- DATA  MEMBER ---
$member_list = [];
$sql_members = "SELECT mg.username,
                CASE
                    WHEN a.npk_dosen IS NOT NULL THEN 'Dosen'
                    WHEN a.nrp_mahasiswa IS NOT NULL THEN 'Mahasiswa'
                    ELSE 'Admin'
                END AS role
                FROM member_grup mg
                JOIN akun a ON mg.username = a.username
                WHERE mg.idgrup = ?";
$stmt_members = mysqli_prepare($conn, $sql_members);
mysqli_stmt_bind_param($stmt_members, "i", $idgrup);
mysqli_stmt_execute($stmt_members);
$result_members = mysqli_stmt_get_result($stmt_members);
while ($row = mysqli_fetch_assoc($result_members)) {
    $member_list[] = $row;
}

// --- DATA  MAHASISWA UNTUK PENAMBAHAN ---
$search_query = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";
$mahasiswa_list = [];
if (isset($_GET['search']) && trim($_GET['search']) != '') {
    $sql_mahasiswa = "SELECT m.nrp, m.nama
                      FROM mahasiswa m
                      WHERE m.nrp LIKE ? OR m.nama LIKE ?
                      ";
    $stmt_mhs = mysqli_prepare($conn, $sql_mahasiswa);
    mysqli_stmt_bind_param($stmt_mhs, "ss", $search_query, $search_query);
    mysqli_stmt_execute($stmt_mhs);
    $result_mhs = mysqli_stmt_get_result($stmt_mhs);
    while ($row = mysqli_fetch_assoc($result_mhs)) {
        $mahasiswa_list[] = $row;
    }
}


mysqli_close($conn);
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Kelola Member Grup: <?php echo htmlspecialchars($group_name); ?></title>
    <style>
        /* CSS Konsisten */
        body { font-family: Arial, sans-serif; background-color: #E0D9D9; margin: 0; }
        header { background-color: #2F5755; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h2 { margin: 0; }
        nav a { color: white; text-decoration: none; background-color: #2F5755; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        main { padding: 40px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2F5755; text-decoration: none; font-weight: bold; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        
        .member-container { display: flex; gap: 30px; margin-top: 20px; }
        .member-list-box, .add-member-box { flex: 1; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .add-member-box h3, .member-list-box h3 { color: #2F5755; margin-top: 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .remove-button { background-color: crimson; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        
        .search-form input[type="text"] { width: 70%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .search-form button { background-color: #5cb85c; color: white; border: none; padding: 8px 10px; border-radius: 4px; cursor: pointer; }
        .add-button { background-color: #2F5755; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<header>
    <h2>Kelola Member Grup: <?php echo htmlspecialchars($group_name); ?></h2>
    <nav>
        <a href="detail_group.php?idgrup=<?php echo $idgrup; ?>">Detail Grup</a>
        <a href="manage_group_dosen.php">Kelola Grup</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <a href="detail_group.php?idgrup=<?php echo $idgrup; ?>" class="back-link">&leftarrow; Kembali ke Detail Grup</a>
    
    <?php if ($message): ?>
        <div class="alert <?php
            if (strpos($message, '✅') !== false) echo 'alert-success';
            elseif (strpos($message, '❌') !== false) echo 'alert-error';
            else echo 'alert-warning';
        ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="member-container">
        <div class="member-list-box">
            <h3>👥 Daftar Anggota Grup</h3>
            <table>
                <thead>
                    <tr>
                        <th>Username (NRP/NPK)</th>
                        <th>Peran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($member_list as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['username']); ?></td>
                            <td><?php echo htmlspecialchars($member['role']); ?></td>
                            <td>
                                <?php if ($member['username'] != $username_dosen): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin mengeluarkan member ini?');">
                                        <input type="hidden" name="action" value="remove_member">
                                        <input type="hidden" name="member_username" value="<?php echo htmlspecialchars($member['username']); ?>">
                                        <button type="submit" class="remove-button">Keluarkan</button>
                                    </form>
                                <?php else: ?>
                                    (Pembuat Grup)
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="add-member-box">
            <h3>➕ Tambah Mahasiswa/Dosen</h3>
            <p>Cari Mahasiswa/Dosen berdasarkan **NRP/NPK** atau **Nama**:</p>
            
            <form method="GET" class="search-form">
                <input type="hidden" name="idgrup" value="<?php echo $idgrup; ?>">
                <input type="text" name="search" placeholder="Cari NRP/Nama..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Cari</button>
            </form>

            <?php if (isset($_GET['search']) && trim($_GET['search']) != '' && !empty($mahasiswa_list)): ?>
                <h4>Hasil Pencarian Mahasiswa:</h4>
                <table>
                    <thead>
                        <tr>
                            <th>NRP</th>
                            <th>Nama</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswa_list as $mhs): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mhs['nrp']); ?></td>
                                <td><?php echo htmlspecialchars($mhs['nama']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="add_member">
                                        <input type="hidden" name="new_username" value="<?php echo htmlspecialchars($mhs['nrp']); ?>">
                                        <button type="submit" class="add-button">Tambah</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            <?php elseif (isset($_GET['search']) && trim($_GET['search']) != ''): ?>
                <p>Tidak ditemukan Mahasiswa dengan kata kunci "<?php echo htmlspecialchars($_GET['search']); ?>".</p>
            <?php endif; ?>
            
            <h4 style="margin-top: 30px;">Tambah Dosen Lain:</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_member">
                <input type="text" name="new_username" placeholder="Masukkan NPK Dosen" required>
                <button type="submit" class="add-button" style="width: 100%; margin-top: 10px;">Tambah Dosen</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>