<?php
session_start();
//isset pengecekan apakah user benar dosen
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'dosen') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Home Dosen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E0D9D9;
            margin: 0;
        }

        header {
            background-color: #2F5755;
            color: white;
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

        main {
            padding: 40px;
            text-align: center;
        }

        .menu-button {
            background-color: #2F5755;
            color: white;
            padding: 10px 25px;
            margin: 10px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
        }

        .logout {
            background-color: crimson;
        }
    </style>
</head>
<body>

<header>
    <h2>Dashboard Dosen</h2>
    <nav>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <h1>Selamat datang, <?php echo $_SESSION['username']; ?>!</h1>
    <p>Anda login sebagai <b>Dosen</b>.</p>
    <a class="menu-button" href="change_password.php">Change Password</a>
    <a class="menu-button" href="manage_group_dosen.php">Kelola Grup yang Saya Buat</a>
</main>

</body>
</html>
