<?php
session_start();
$con = new mysqli("localhost", "root", "", "fullstack");

if ($con->connect_errno) {
    die("Failed: " . $con->connect_error);
}

if (!isset($_GET['nrp'])) {
    die("NRP tidak ditemukan.");
}

//mendapatkan nrp dari halaman tabel
$original_nrp = $_GET['nrp'];
//query mendapatkan data mahasiswa dan akunntya
$sql = "SELECT m.*, a.username, a.password FROM mahasiswa m LEFT JOIN akun a ON m.nrp = a.nrp_mahasiswa WHERE m.nrp = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $original_nrp);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_assoc();

if (!$mahasiswa) {
    die("Data mahasiswa tidak ditemukan.");
}

//untuk keperluan testing debug dan error, dapat diabaikan saja
$errors = [];
$success = false;
//

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //variable menangkap hasil input
    $nrp = $_POST['nrp'];
    $nama = strtolower($_POST['nama']);
    $gender = $_POST['gender'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $angkatan = $_POST['angkatan'];
    $username = $_POST['username'];   
    $password = $_POST['password'];   
    $foto_baru = $_FILES['foto'];
    $ext_lama = $mahasiswa['foto_extention'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    //keperluan debug
    if ($nama == '') $errors[] = "Nama wajib diisi.";
    if ($gender == '') $errors[] = "Gender wajib dipilih.";
    if ($tanggal_lahir == '') $errors[] = "Tanggal lahir wajib diisi.";
    if ($angkatan == '') $errors[] = "Angkatan wajib diisi.";
    if ($username == '') $errors[] = "Username wajib diisi."; 
    if ($password == '') $errors[] = "Password wajib diisi."; 

    if (count($errors) == 0) {
        //update untuk picture
        $ext_baru = '';
        $foto_ext_to_store = $ext_lama;
        $old_path = "foto_mahasiswa/" . $mahasiswa['nrp'] . "." . $ext_lama;
        $new_path = "foto_mahasiswa/" . $nrp . "." . $ext_lama;

        //jalankan kalau ada foto baru
        if (!empty($foto_baru['name'])) {
            $ext_baru = strtolower(pathinfo($foto_baru['name'], PATHINFO_EXTENSION));
            if (!in_array($ext_baru, $allowed)) {
                $errors[] = "Format foto tidak valid. Gunakan JPG, JPEG, PNG, atau GIF.";
            } else {
                if (file_exists($old_path)) unlink($old_path);
                move_uploaded_file($foto_baru['tmp_name'], "foto_mahasiswa/" . $nrp . "." . $ext_baru);
                $foto_ext_to_store = $ext_baru;
            }
        } else {
            if ($nrp !== $original_nrp && file_exists($old_path)) {
                rename($old_path, $new_path);
            }
        }
        
        // update data mahasiswa
        $sql_upd = "UPDATE mahasiswa SET nrp=?, nama=?, gender=?, tanggal_lahir=?, angkatan=?, foto_extention=? 
                    WHERE nrp=?";
        $stmt_upd = $con->prepare($sql_upd);
        $stmt_upd->bind_param("sssssss", $nrp, $nama, $gender, $tanggal_lahir, $angkatan, $foto_ext_to_store, $original_nrp);
        $ok1 = $stmt_upd->execute();

        $cekAkun = $con->prepare("SELECT nrp_mahasiswa FROM akun WHERE nrp_mahasiswa=?");
        $cekAkun->bind_param("s", $original_nrp);
        $cekAkun->execute();
        $resAkun = $cekAkun->get_result();

        //update data akun
        if ($resAkun->num_rows > 0) {
            $sql_acc = "UPDATE akun SET username=?, password=?, nrp_mahasiswa=? WHERE nrp_mahasiswa=?";
            $stmt_acc = $con->prepare($sql_acc);
            $stmt_acc->bind_param("ssss", $username, $password, $nrp, $original_nrp);
            $ok2 = $stmt_acc->execute();
        } else {
            $sql_ins = "INSERT INTO akun (username, password, nrp_mahasiswa, role) VALUES (?, ?, ?, 'mahasiswa')";
            $stmt_ins = $con->prepare($sql_ins);
            $stmt_ins->bind_param("sss", $username, $password, $nrp);
            $ok2 = $stmt_ins->execute();
        }

        if ($ok1 && $ok2) {
            $success = true;
            header("Location: admin_tabelmahasiswa.php");
            exit();
        } else {
            $errors[] = "Gagal update data mahasiswa atau akun.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Mahasiswa</title>
    <style>
        body { font-family: sans-serif; margin: 30px; background-color: #E0D9D9; }
        h2 { text-align: center; margin-bottom: 20px; }
        form { width: 50%; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        .btnSimpan { margin-top: 20px; padding: 10px 20px; background-color: #3271d6; border: none; color: white; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btnKembali { margin-top: 10px; display: inline-block; text-decoration: none; color: #2F5755; }
        .errors { color: #b71c1c; background: #ffdede; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        img.preview { margin-top: 8px; border: 1px solid #ddd; padding: 5px; border-radius: 6px; }
    </style>
</head>
<body>
    <h2>Edit Data Mahasiswa</h2>

    <form method="POST" enctype="multipart/form-data">
        <?php
        if (count($errors) > 0) {
            echo "<div class='errors'><ul>";
            foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>";
            echo "</ul></div>";
        }
        if ($success) {
            echo "<div class='success'>Data berhasil diperbarui.</div>";
        }
        ?>

        <label>NRP (9 digit)</label>
        <input type="text" name="nrp" value="<?php echo htmlspecialchars($mahasiswa['nrp']); ?>" maxlength="9" readonly>

        <label>Nama</label>
        <input type="text" name="nama" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" required>

        <label>Gender</label>
        <select name="gender" required>
            <option value="">-- Pilih --</option>
            <option value="Pria" <?php if ($mahasiswa['gender'] == 'Pria') echo 'selected'; ?>>Pria</option>
            <option value="Wanita" <?php if ($mahasiswa['gender'] == 'Wanita') echo 'selected'; ?>>Wanita</option>
        </select>

        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($mahasiswa['tanggal_lahir']); ?>" required>

        <label>Angkatan</label>
        <input type="text" name="angkatan" value="<?php echo htmlspecialchars($mahasiswa['angkatan']); ?>" required>

        <label>Username Akun</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($mahasiswa['username']); ?>" required>

        <label>Password Akun</label>
        <input type="text" name="password" value="<?php echo htmlspecialchars($mahasiswa['password']); ?>" required>

        <label>Foto</label>
        <?php
        if (!empty($mahasiswa['foto_extention'])) {
            $foto_url = "foto_mahasiswa/" . $mahasiswa['nrp'] . "." . $mahasiswa['foto_extention'];
            echo "<br><img src='$foto_url' width='150' class='preview'><br>";
        }
        ?>
        <input type="file" name="foto" accept="image/*">

        <button type="submit" class="btnSimpan">Simpan</button>
        <br>
        <a href="admin_tabelmahasiswa.php" class="btnKembali">← Kembali</a>
    </form>
</body>
</html>
