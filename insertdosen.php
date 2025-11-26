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
    <title>Tambah Akun Dosen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            text-align: center;
            background-color: #E0D9D9;
        }

        form {
            display: inline-block;
            text-align: left;
            background-color: #fff;
            padding: 25px 40px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0 16px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btnSubmit {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btnSubmit:hover {
            background-color: #218838;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            color: #2F5755;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h2>Tambah Akun Dosen Baru</h2>

    <form method="post" enctype="multipart/form-data" action="insertdosen_proses.php">
        NPK Dosen : <input type="text" name="npk_dosen" required><br>
        Username : <input type="text" name="username" required><br>
        Password : <input type="password" name="password" required><br>
        Nama : <input type="text" name="nama" required><br>
        Foto : <input type="file" name="foto" accept="image/jpeg, image/png, image/gif" required><br>
        <input type="submit" name="submit" value="Insert Data Dosen" class="btnSubmit">
    </form>
    <br>
    <a href="admin_tabeldosen.php">Kembali ke Halaman Dosen</a>
</body>

</html>