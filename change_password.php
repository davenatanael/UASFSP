<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "fullstack");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $user = $_SESSION['username'];

    $sql = "SELECT * FROM akun WHERE username=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $old_pass);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $sqlUpdate = "UPDATE akun SET password=? WHERE username=?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ss", $new_pass, $user);
        $stmtUpdate->execute();
        echo "<script>alert('Password berhasil diubah'); window.location='" . $_SESSION['role'] . "_home.php';</script>";
    } else {
        echo "<script>alert('Password lama salah');</script>";
    }
}
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>Ganti Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E0D9D9;
            margin: 0;
            padding: 0;
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background-color: white;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 0 15px 0 rgba(0, 0, 0, 0.3);
            width: 350px;
            text-align: center;
        }

        h2 {
            color: #2F5755;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            background-color: #2F5755;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            width: 100%;
        }

        .back-btn {
            background-color: #005a9e;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <header>
        <h2>Ganti Password</h2>
        <nav>
            <a href="<?php echo $_SESSION['role']; ?>_home.php">Kembali</a>
        </nav>
    </header>

    <main>
        <div class="form-container">
            <h2>Ubah Password</h2>
            <form method="post">
                <input type="password" name="old_password" placeholder="Password Lama" required><br>
                <input type="password" name="new_password" placeholder="Password Baru" required><br>
                <button type="submit">Simpan</button>
            </form>
        </div>
    </main>

</body>

</html>