<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "fullstack");

$id = $_GET['id'];

$sql = "SELECT g.*, a.npk_dosen, a.nrp_mahasiswa FROM grup g JOIN akun a ON g.username_pembuat = a.username WHERE g.idgrup = $id";
$grup = $conn->query($sql)->fetch_assoc();

$pembuat = $grup['username_pembuat'];
$nama_pembuat = "";

if ($grup['npk_dosen'] != null) {
    $npk = $grup['npk_dosen'];
    $q = $conn->query("SELECT nama FROM dosen WHERE npk='$npk'")->fetch_assoc();
    $nama_pembuat = $q['nama'] . " (Dosen)";
}
else if ($grup['nrp_mahasiswa'] != null) {
    $nrp = $grup['nrp_mahasiswa'];
    $q = $conn->query("SELECT nama FROM mahasiswa WHERE nrp='$nrp'")->fetch_assoc();
    $nama_pembuat = $q['nama'] . " (Mahasiswa)";
}
else {
    $nama_pembuat = $pembuat;
}

$sql_member = "SELECT mg.username, a.npk_dosen, a.nrp_mahasiswa FROM member_grup mg JOIN akun a ON mg.username = a.username WHERE mg.idgrup = $id";
$members = $conn->query($sql_member);

$sql_event = "SELECT * FROM event WHERE idgrup=$id";
$events = $conn->query($sql_event);

$sql_thread = "SELECT * FROM thread WHERE idgrup=$id ORDER BY tanggal_pembuatan DESC";
$threads = $conn->query($sql_thread);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detail Grup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #E0D9D9;
            margin: 0;
        }
        header {
            background: #2F5755;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            margin-top:10px;
            padding: 30px;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid black;
            padding: 8px;
        }
        a.btn {
            padding: 7px 14px;
            background: white;
            color: #2F5755;
            border-radius: 6px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<header>
    <h2>Detail Grup</h2>
    <nav>
        <a class="btn" href="mahasiswa_home.php">Kembali</a>
    </nav>
</header>

   
<div class="container">
    <div class="box">
        <h2><?php echo $grup['nama']; ?></h2>
        <p><b>Deskripsi:</b> <?php echo $grup['deskripsi']; ?></p>
        <p><b>Pembuat:</b> <?php echo $nama_pembuat; ?></p>
        <p><b>Tanggal dibuat:</b> <?php echo $grup['tanggal_pembentukan']; ?></p>
        <p><b>Jenis:</b> <?php echo $grup['jenis']; ?></p>
    </div>

    <div class="box">
        <h3>Daftar Member</h3>

        <table>
            <tr>
                <th>Username</th>
                <th>Nama</th>
                <th>Gender</th>
            </tr>

            <?php while ($m = $members->fetch_assoc()) {
                $user = $m['username'];
                $nama = "-";
                $gender = "-";

                if ($m['npk_dosen'] != null) {
                    $npk = $m['npk_dosen'];
                    $q = $conn->query("SELECT nama FROM dosen WHERE npk='$npk'")->fetch_assoc();
                    $nama = $q['nama'] . " (Dosen)";
                }
                else if ($m['nrp_mahasiswa'] != null) {
                    $nrp = $m['nrp_mahasiswa'];
                    $q = $conn->query("SELECT nama, gender FROM mahasiswa WHERE nrp='$nrp'")->fetch_assoc();
                    $nama = $q['nama'] . " (Mahasiswa)";
                    $gender = $q['gender'];
                }
            ?>

            <tr>
                <td><?php echo $user; ?></td>
                <td><?php echo $nama; ?></td>
                <td><?php echo $gender; ?></td>
            </tr>

            <?php } ?>
        </table>
    </div>


</div>

</body>
</html>
