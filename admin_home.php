<?php
session_start();

//isset pengecekan security apkah akun tersebut benar admin, kalau bukan jangan boleh masuk page ini
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Home - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E0D9D9;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2F5755;
            color: #E0D9D9;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h2 {
            margin: 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            background-color: #2F5755;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
        }

        nav a:hover {
            background-color: #003f73;
        }

        main {
            padding: 40px;
            text-align: center;
        }

        .menu-button {
            display: inline-block;
            background-color: #2F5755;
            color: white;
            padding: 15px 30px;
            margin: 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .menu-button:hover {
            background-color: #005a9e;
        }

        .logout {
            background-color: crimson;
            margin-left: 10px;
        }

        .logout:hover {
            background-color: darkred;
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: #0078d7;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<header>
    <h2>Dashboard Admin</h2>
    <nav>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <h1>Selamat datang, <?php echo($_SESSION['username']); ?>!</h1>
    <p>Anda login sebagai <b>ADMIN</b>.</p>
    <p>Pilih menu di bawah untuk mengelola data dosen dan mahasiswa.</p>
    <!-- buttons untuk ke page admin masing" -->
    <a href="admin_tabeldosen.php" class="menu-button">Kelola Data Dosen</a>
    <a href="admin_tabelmahasiswa.php" class="menu-button">Kelola Data Mahasiswa</a>
</main>
</body>
</html>
