<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fullstack");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM akun WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $_SESSION['username'] = $row['username'];

    //assign session role sesuai dengan login nya
    if ($row['isadmin'] == 1) {
        $_SESSION['role'] = "admin";
        header("Location: admin_home.php");
    } elseif (!empty($row['npk_dosen'])) {
        $_SESSION['role'] = "dosen";
        header("Location: dosen_home.php");
    } elseif (!empty($row['nrp_mahasiswa'])) {
        $_SESSION['role'] = "mahasiswa";
        header("Location: mahasiswa_home.php");
    } else {
        echo "Role tidak dikenali.";
    }
} else {
    $_SESSION['error_message'] = "Username atau password salah!";
    header("Location: login.php");
}

$conn->close();
?>