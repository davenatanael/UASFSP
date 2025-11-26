<?php
session_start();
$con = new mysqli("localhost", "root", "", "fullstack");

if ($con->connect_errno) {
    die("Failed: " . $con->connect_error);
}

if (!isset($_GET['npk'])) {
    die("NPK tidak ditemukan.");
}

//mendapatkan npk dari halaman tabel
$npk = $_GET['npk'];
//query mendapatkan data dosen
$sql = "SELECT * FROM dosen WHERE npk = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $npk);
$stmt->execute();
$result = $stmt->get_result();
$dosen = $result->fetch_assoc();

if (!$dosen) {
    die("Data dosen tidak ditemukan.");
}

//query mendapatkan data akun dosen tersebut
$sqlAkun = "SELECT username, password FROM akun WHERE npk_dosen = ?";
$stmtAkun = $con->prepare($sqlAkun);
$stmtAkun->bind_param("s", $npk);
$stmtAkun->execute();
$resAkun = $stmtAkun->get_result();
$akun = $resAkun->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //variable menangkap dari input
    $nama = $_POST['nama'];
    $npk = $_POST['npk'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    //variable utk foto
    $nama_file_baru = strtolower($nama);
    $ext_lama = strtolower($dosen['foto_extension']);
    $foto_baru = $_FILES['foto'];
    $ext = pathinfo($foto_baru['name'], PATHINFO_EXTENSION);
    $nama_file_lama = strtolower($dosen['nama']);
    $path_lama = "foto_dosen/" . $nama_file_lama . "_" . $dosen['npk'] . "." . $ext_lama;
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    //jalankan code kalau ada foto baru
    if (!empty($foto_baru['name']) && in_array($ext, $allowed)) {
        $path_baru = "foto_dosen/" . $nama_file_baru . "_" . $npk . "." . $ext;

        if (file_exists($path_lama)) {
            unlink($path_lama);
        }

        move_uploaded_file($foto_baru['tmp_name'], $path_baru);

        //update dosen
        $sql = "UPDATE dosen SET nama=?, foto_extension=? WHERE npk=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sss", $nama, $ext, $npk);
    } else {
        if (file_exists($path_lama)) {
            $path_rename = "foto_dosen/" . $nama_file_baru . "_" . $npk . "." . $ext_lama;
            rename($path_lama, $path_rename);
        }

        $sql = "UPDATE dosen SET nama=? WHERE npk=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $nama, $npk);
    }

    $stmt->execute();

    // update akun
    if ($akun) {
        if (!empty($password)) {
            $stmtAkun = $con->prepare("UPDATE akun SET username=?, password=? WHERE npk_dosen=?");
            $stmtAkun->bind_param("sss", $username, $password, $npk);
        } else {
            $stmtAkun = $con->prepare("UPDATE akun SET username=? WHERE npk_dosen=?");
            $stmtAkun->bind_param("ss", $username, $npk);
        }
        $stmtAkun->execute();
    }

    header("Location: admin_tabeldosen.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Dosen</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 30px;
            background-color: #E0D9D9;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: 50%;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input {
            width: 95%;
            padding: 8px;
            margin-top: 5px;
        }

        .btnSimpan {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2F5755;
            border: none;
            color: white;
            border-radius: 5px;
            font-weight: bold;
        }

        .btnKembali {
            margin-top: 10px;
            display: inline-block;
            text-decoration: none;
            color: #2F5755;
        }
    </style>
</head>

<body>
    <h2>Edit Data Dosen</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>NPK</label>
        <input type="text" name="npk" value="<?php echo $dosen['npk']; ?>" readonly>

        <label>Nama</label>
        <input type="text" name="nama" value="<?php echo $dosen['nama']; ?>" required>

        <label>Username Akun</label>
        <input type="text" name="username" value="<?php echo $akun['username']; ?>" required>

        <label>Password Akun</label>
        <input type="text" name="password" placeholder="Kosongkan jika tidak ingin ubah password"
            value="<?php echo isset($akun['password']) ? $akun['password'] : ''; ?>" required>

        <label>Foto</label>
        <?php
        if (!empty($dosen['foto_extension'])) {
            $foto_path = "foto_dosen/" . $dosen['nama'] . "_" . $dosen['npk'] . "." . $dosen['foto_extension'];
            if (file_exists($foto_path)) {
                echo '<br><img src="' . $foto_path . '" width="150" alt="Foto Dosen"><br>';
                echo '<small>Path: ' . htmlspecialchars($foto_path) . '</small><br>';
            } else {
                echo '<p style="color:red;">Foto tidak ditemukan di ' . htmlspecialchars($foto_path) . '</p>';
            }
        } else {
            echo "<p>Tidak ada foto yang tersimpan.</p>";
        }
        ?>
        <input type="file" name="foto">

        <button type="submit" class="btnSimpan">Simpan</button>
        <br>
        <a href="admin_tabeldosen.php" class="btnKembali">← Kembali</a>
    </form>
</body>

</html>