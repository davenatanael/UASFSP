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

// Fungsi generate kode pendaftaran (simple & aman)
function generateUniqueCode() {
    return 'GRP' . date('ymd') . strtoupper(substr(uniqid(), -4));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama       = trim($_POST['nama']);
    $deskripsi  = trim($_POST['deskripsi']);
    $jenis      = $_POST['jenis'];
    $username_pembuat = $_SESSION['username'];
    $tanggal_pembentukan = date('Y-m-d H:i:s');

    // Generate kode
    $kode_pendaftaran = generateUniqueCode();

    // Insert grup
    $sql_grup = "INSERT INTO grup 
        (username_pembuat, nama, deskripsi, tanggal_pembentukan, jenis, kode_pendaftaran)
        VALUES (?, ?, ?, ?, ?, ?)";

    $stmt_grup = mysqli_prepare($conn, $sql_grup);
    mysqli_stmt_bind_param(
        $stmt_grup,
        "ssssss",
        $username_pembuat,
        $nama,
        $deskripsi,
        $tanggal_pembentukan,
        $jenis,
        $kode_pendaftaran
    );

    if (mysqli_stmt_execute($stmt_grup)) {

        $idgrup_baru = mysqli_insert_id($conn);

        // Tambahkan dosen sebagai member pertama
        $sql_member = "INSERT INTO member_grup (idgrup, username) VALUES (?, ?)";
        $stmt_member = mysqli_prepare($conn, $sql_member);
        mysqli_stmt_bind_param($stmt_member, "is", $idgrup_baru, $username_pembuat);

        if (mysqli_stmt_execute($stmt_member)) {
            header("Location: detail_group.php?idgrup=$idgrup_baru&success=1");
            exit;
        } else {
            die("Gagal menambahkan member grup");
        }

    } else {
        die("Gagal membuat grup");
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Tambah Grup Baru</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #E0D9D9; margin: 0; }
        header { background-color: #2F5755; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h2 { margin: 0; }
        nav a { color: white; text-decoration: none; background-color: #2F5755; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        main { padding: 40px; display: flex; justify-content: center; align-items: center; }
        .form-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2); width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #2F5755; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-button { background-color: #2F5755; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 10px; }
        .submit-button:hover { background-color: #1a3837; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2F5755; text-decoration: none; }
    </style>
</head>
<body>

<header>
    <h2>Tambah Grup Baru</h2>
    <nav>
        <a href="manage_group_dosen.php">Kelola Grup</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <div class="form-container">
        <a href="manage_group_dosen.php" class="back-link">&leftarrow; Kembali ke Daftar Grup</a>
        <h3>Form Pembuatan Grup</h3>
        <form method="POST" action="add_group.php">
            <div class="form-group">
                <label for="nama">Nama Grup</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="jenis">Jenis Grup</label>
                <select id="jenis" name="jenis" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="Kelas">Kelas</option>
                    <option value="Proyek">Proyek</option>
                    <option value="Diskusi">Diskusi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <button type="submit" class="submit-button">Buat Grup</button>
        </form>
    </div>
</main>

</body>
</html>