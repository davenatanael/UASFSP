<?php
    $con = new mysqli("localhost", "root", "", "fullstack");

    if ($con->connect_errno) {
        die("Failed: " . $con->connect_error);
    }


if (!isset($_GET['nrp'])) {
    die("NRP tidak ditemukan.");
}

$nrp = $_GET['nrp'];

//query
$sql = "SELECT * FROM mahasiswa WHERE nrp=?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $nrp);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_assoc();

if (!$mahasiswa) {
    die("Data mahasiswa tidak ditemukan.");
}

if (!empty($mahasiswa['foto_extention'])) {
    $fotoPath = "foto_mahasiswa/" . $mahasiswa['nrp'] . "." . $mahasiswa['foto_extention'];
    if (file_exists($fotoPath)) {
        unlink($fotoPath); //hapus foto
    }
}

//hapus di database mahasiswa
$sql = "DELETE FROM mahasiswa WHERE nrp=?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $nrp);

if ($stmt->execute()) {
    header("Location: admin_tabelmahasiswa.php");
    exit();
} else {
    echo "Gagal menghapus data.";
}

$con->close();
?>
