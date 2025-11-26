<!DOCTYPE html>
<html>

<head>
    <title>Insert Data Dosen</title>

    <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
</head>

<body>
    <?php
    session_start();
    $con = new mysqli("localhost", "root", "", "fullstack");

    if ($con->connect_errno) {
        die("Failed: " . $con->connect_error);
    }

    //assign variable
    $npk = $_POST['npk_dosen'];
    $nama = strtolower($_POST['nama']);
    $foto = $_FILES['foto'];
    $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    //PENGECEKAN NPK
    $sql2 = "SELECT npk FROM dosen WHERE npk=?";
    $stmt2 = $con->prepare($sql2);
    $stmt2->bind_param("s", $npk);
    $stmt2->execute();
    $stmt2->store_result();
    if ($stmt2->num_rows > 0) { //cek npk duplikat
        $_SESSION['error_message'] = "NPK $npk sudah terdaftar. NPK tidak boleh duplikat, silahkan menggunakan NPK lain.";
        header('location: insertdosen.php');
        exit();
    }
    if ($npk == '') { //cek input kosong
        $_SESSION['error_message'] = "NPK wajib diisi.";
        header("Location: insertdosen.php");
        exit();
    }
    if (strlen($npk) != 6) { //cek digit npk
        $_SESSION['error_message'] = "NPK harus berupa 6 digit angka.";
        header("Location: insertdosen.php");
        exit();
    }
    $stmt2->close();

    //PENGECEKAN USERNAME
    $sql3 = "SELECT username FROM akun WHERE username=?";
    $stmt3 = $con->prepare($sql3);
    $stmt3->bind_param("s", $username);
    $stmt3->execute();
    $stmt3->store_result();
    if ($stmt3->num_rows > 0) {
        $_SESSION['error_message'] = "Username $username sudah terdaftar. Username tidak boleh duplikat, silahkan menggunakan Username lain.";
        header('location: insertdosen.php');
        exit();
    }
    if ($username == '') {
        $_SESSION['error_message'] = "Username wajib diisi.";
        header("Location: insertdosen.php");
        exit();
    }
    $stmt3->close();

    $sql = "INSERT INTO dosen(npk, nama, foto_extension) VALUES(?,?,?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('sss', $npk, $nama, $ext);
    $stmt->execute();

    $sqlAkun = "INSERT INTO akun (npk_dosen, username, password) VALUES (?, ?, ?)";
    $stmtAkun = $con->prepare($sqlAkun);
    $stmtAkun->bind_param("sss", $npk, $username, $password);
    $stmtAkun->execute();

    if ($stmt && $stmtAkun) {
        echo "Insert Sukses.";
        if (in_array($ext, $allowed)) {
            $dst = "foto_dosen/$nama" . "_" . "$npk.$ext";
            move_uploaded_file($foto['tmp_name'], $dst);

        } else {
            die("Format foto hanya JPG, JPEG, PNG, GIF.");
        }
        echo '<a href="admin_tabeldosen.php">Kembali ke Halaman Mengelola Data Dosen</a>';
    } else {
        echo "Insert Gagal.";
    }

    $con->close();
    ?>
</body>

</html>