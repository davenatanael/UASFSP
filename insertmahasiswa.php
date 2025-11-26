<?php
session_start();
//isset cek apakah ada error message
if (isset($_SESSION["error_message"])) {
    echo "<script>alert('{$_SESSION['error_message']}')</script>";
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Insert Data Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E0D9D9;
            margin: 40px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            width: 50%;
            margin: 0 auto;
            background-color: #fff;
            padding: 25px 35px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            text-align: left;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="password"],
        input[type="file"] {
            width: 95%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .gender-group {
            text-align: left;
            margin-top: 10px;
        }

        .btnSubmit {
            display: inline-block;
            margin-top: 25px;
            background-color: #28a745;
            color: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .btnSubmit:hover {
            background-color: #218838;
        }

        a.kembali {
            display: inline-block;
            margin-top: 20px;
            color: #2F5755;
            text-decoration: none;
            font-weight: bold;
        }

        a.kembali:hover {
            text-decoration: underline;
        }
    </style>
    <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
</head>

<body>
    <h2>Form Tambah Data Mahasiswa</h2>
    <form method="post" enctype="multipart/form-data" action="insertmahasiswa_proses.php">
        <label>NRP (9 digit):</label>
        <input type="text" name="nrp" maxlength="9" pattern="\d{9}" required>

        <label>Nama:</label>
        <input type="text" name="nama" required>

        <label>Gender:</label>
        <div class="gender-group">
            <input type="radio" name="gender" value="Pria" required> Pria
            <input type="radio" name="gender" value="Wanita" required> Wanita
        </div>

        <label>Tanggal Lahir:</label>
        <input type="date" name="tanggal_lahir" required>

        <label>Angkatan:</label>
        <input type="number" name="angkatan" required>

        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Foto:</label>
        <input type="file" name="foto" accept="image/jpeg, image/png, image/gif">

        <button type="submit" class="btnSubmit">Simpan Data Mahasiswa</button>
    </form>
    <br>
    <div style="text-align:center;">
        <a href="admin_tabelmahasiswa.php" class="kembali">Kembali ke Halaman Mahasiswa</a>
    </div>
</body>

</html>