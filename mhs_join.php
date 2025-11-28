<?php
session_start();
$username = $_SESSION['username'];

$conn = new mysqli("localhost", "root", "", "fullstack");

$idgrup = $_POST['idgrup'];
$kode = $_POST['kode'];

$cek = $conn->query("SELECT * FROM grup WHERE idgrup=$idgrup AND kode_pendaftaran='$kode'");

if ($cek->num_rows == 1) {
    $conn->query("INSERT INTO member_grup VALUES ($idgrup, '$username')");
    echo "<script>alert('Berhasil join grup!');location.href='mahasiswa_home.php';</script>";
} else {
    echo "<script>alert('Kode salah! Tidak bisa join.');history.back();</script>";
}
?>
