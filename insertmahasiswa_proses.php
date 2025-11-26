<!DOCTYPE html>
<html>

<head>
    <title>Insert Data Mahasiswa</title>

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
    $nrp = $_POST['nrp'];
    $nama = strtolower($_POST['nama']);
    $gender = $_POST['gender'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $angkatan = $_POST['angkatan'];
    $foto = $_FILES['foto'];
    $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    //PENGECEKAN NRP
    $sql2 = "SELECT nrp FROM mahasiswa WHERE nrp=?";
    $stmt2 = $con->prepare($sql2);
    $stmt2->bind_param("s", $nrp);
    $stmt2->execute();
    $stmt2->store_result();
    if ($stmt2->num_rows > 0) { //cek duplikat
        $_SESSION['error_message'] = "NRP $nrp sudah terdaftar. NRP tidak boleh duplikat, silahkan menggunakan NRP lain.";
        header('location: insertmahasiswa.php');
        exit();
    } else if ($nrp == '') { //cek kosong
        $_SESSION['error_message'] = "NRP wajib diisi.";
        header("Location: insertmahasiswa.php");
        exit();
    } elseif (strlen($nrp) != 9) { //cek digit
        $_SESSION['error_message'] = "NRP harus berupa 9 digit angka.";
        header("Location: insertmahasiswa.php");
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
    //INSERT
    $sql = "INSERT INTO mahasiswa(nrp, nama, gender, tanggal_lahir, angkatan, foto_extention) VALUES(?,?,?,?,?,?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('ssssis', $nrp, $nama, $gender, $tanggal_lahir, $angkatan, $ext);
    $stmt->execute();

    $sql_akun = "INSERT INTO akun (username, password, nrp_mahasiswa)
                 VALUES (?, ?, ?)";
    $stmt_akun = $con->prepare($sql_akun);
    $stmt_akun->bind_param("sss", $username, $password, $nrp);
    $stmt_akun->execute();

    if ($stmt && $stmt_akun) {
        echo "Insert Sukses.";
        if (in_array($ext, $allowed)) {
            $dst = "foto_mahasiswa/$nrp.$ext";
            move_uploaded_file($foto['tmp_name'], $dst);

        } else {
            die("Format foto hanya JPG, JPEG, PNG, GIF.");
        }
        echo '<a href="admin_tabelmahasiswa.php">Kembali ke Halaman Mengelola Data Mahasiswa</a>';
    } else {
        echo "Insert Gagal.";
    }

    $con->close();
    ?>
</body>

</html>