<?php
    $con = new mysqli("localhost", "root", "", "fullstack");

    if ($con->connect_errno) {
        die("Failed: " . $con->connect_error);
    }


if (isset($_GET['npk'])) {
    $npk = $_GET['npk'];

    //query mendapatkan data utk nama dan extension file supaya dapat di hapus di folder
    $sql = "SELECT nama, foto_extension FROM dosen WHERE npk = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $result = $stmt->get_result();
    $dosen = $result->fetch_assoc();

    if ($dosen) {
        $nama_file =  $dosen['nama'];
        $foto_path = "foto_dosen/" . $nama_file . "_" . $npk . "." . $dosen['foto_extension'];

        if (file_exists($foto_path)) {
            unlink($foto_path); //hapus di folder
        }

        // hapus di database dosen
        $sql_delete = "DELETE FROM dosen WHERE npk = ?";
        $stmt_delete = $con->prepare($sql_delete);
        $stmt_delete->bind_param("s", $npk);
        $stmt_delete->execute();
    }
}

$con->close();

header("Location: admin_tabeldosen.php");
exit();
