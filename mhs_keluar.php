<?php
session_start();
$username = $_SESSION['username'];

$conn = new mysqli("localhost", "root", "", "fullstack");

$id = $_GET['id'];

$conn->query("DELETE FROM member_grup WHERE idgrup=$id AND username='$username'");

echo "<script>alert('Anda berhasil keluar dari grup.');location.href='mahasiswa_home.php';</script>";
?>
