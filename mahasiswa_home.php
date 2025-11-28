<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

$conn = new mysqli("localhost", "root", "", "fullstack");

$grup_join = "SELECT g.* FROM member_grup mg JOIN grup g ON mg.idgrup = g.idgrup WHERE mg.username = '$username'";
$joined = $conn->query($grup_join);

$grup_publik = "SELECT g.* FROM grup g WHERE g.jenis = 'Publik' AND g.idgrup NOT IN (SELECT idgrup FROM member_grup WHERE username='$username')";
$public = $conn->query($grup_publik);
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Home Mahasiswa</title>
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
        }
        .box {
            background: white;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;   
            text-align: center;   
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #555;
        }
        .btn {
            padding: 7px 14px;
            background: #2F5755;
            color: white;
            border-radius: 6px;
            text-decoration: none;
        }
        .btn-red {
            background: crimson;
        }
        .btn-green{
            background: green;
        } 
        .logout {
            background-color: crimson;
        }
    </style>
</head>
<body>

<header>
    <h2>Dashboard Mahasiswa</h2>
    <nav>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main>
    <h1>Selamat datang, <?php echo $_SESSION['username']; ?>!</h1>
    <p>Anda login sebagai <b>Mahasiswa</b>.</p>
    <a class="btn" href="change_password.php">Change Password</a>

    <br><br>

    <div class="box">
        <h2>Group yang Anda Ikuti</h2>

        <?php if ($joined->num_rows == 0) { ?>
            <p>Anda belum mengikuti group apapun.</p>
        <?php } else { ?>

        <table>
            <tr>
                <th>Nama Grup</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = $joined->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['nama']; ?></td>
                    <td><?php echo $row['deskripsi']; ?></td>
                    <td>
                        <a class="btn" href="mhs_detail_grup.php?id=<?php echo $row['idgrup']; ?>">Detail</a>
                        <a class="btn btn-red" href="mhs_keluar.php?id=<?php echo $row['idgrup']; ?>"
                           onclick="return confirm('Yakin ingin keluar dari grup ini?')">Keluar</a>
                    </td>
                </tr>
            <?php } ?>

        </table>

        <?php } ?>
    </div>

    <div class="box">
        <h2>Group Publik yang Bisa Anda Join</h2>

        <?php if ($public->num_rows == 0) { ?>
            <p>Tidak ada group publik yang tersedia.</p>
        <?php } else { ?>
        <table>
            <tr>
                <th>Nama Grup</th>
                <th>Pembuat</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = $public->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['nama']; ?></td>
                    <td><?php echo $row['username_pembuat']; ?></td>
                    <td>
                        <form method="POST" action="mhs_join.php" style="display:flex; gap:5px;">
                            <input type="hidden" name="idgrup" value="<?php echo $row['idgrup']; ?>">
                            <input type="text" name="kode" placeholder="Kode Pendaftaran" required>
                            <button class="btn btn-green">Join</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>

        </table>
        <?php } ?>
    </div>

</main>

</body>
</html>
