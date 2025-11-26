<?php
session_start();

//isset mengecek utk security apkah akun tersebut benar admin, kalau bukan jangan boleh masuk page ini
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

//paging
if (isset($_GET['cboPage'])) {
    $perpage = (int) $_GET['cboPage'];
} else {
    $perpage = 5;
}

if (isset($_GET['p'])) {
    $p = (int) $_GET['p'];
    if ($p < 1)
        $p = 1;
} else {
    $p = 1;
}

$con = new mysqli("localhost", "root", "", "fullstack");

if ($con->connect_errno) {
    die("Failed: " . $con->connect_error);
}

//query untuk paging
$sql_total = "SELECT COUNT(npk) AS total FROM dosen";
$result_total = $con->query($sql_total);
$row_total = $result_total->fetch_assoc();
$totaldata = (int) $row_total['total'];
$totalpage = ceil($totaldata / $perpage);

if ($p > $totalpage && $totaldata > 0) {
    $p = $totalpage;
}

$start = ($p - 1) * $perpage;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Dosen</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 30px;
            background-color: #217773ff;
        }

        h2 {
            color: #E0D9D9;
            text-align: center;
            margin-bottom: 20px;
        }

        .btnTambah {
            display: inline-block;
            padding: 20px;
            margin: 30px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            background-color: #28a745;
            font-weight: bold;
        }

        .btnBack {
            display: inline-block;
            padding: 20px;
            margin: 30px;
            margin-left: 75px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            background-color: #2F5755;
            font-weight: bold;
            text-align: center;
        }

        .btnEdit {
            background-color: #feda6cff;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
        }

        .btnHapus {
            background-color: #e74c3c;
            color: white;
            width: 60px;
            height: 35px;
            border: none;
            border-radius: 5px;
            margin: 5%;
            padding: 8px 14px;
            text-decoration: none;
        }

        table {
            width: 80%;
            background: #E0D9D9;
            margin-left: auto;
            margin-right: auto;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #2F5755;
            color: white;
        }

        .paging {
            width: 50%;
            margin: auto;
        }
    </style>
</head>

<body>
    <h2>Pengelolaan Data Dosen</h2>

    <div style="text-align:center;">
        <a href="insertdosen.php" class="btn btnTambah">+ Tambah Dosen</a>
    </div>

    <table>
        <tr>
            <th>NPK</th>
            <th>Nama</th>
            <th>Foto</th>
            <th>Kelola Data</th>
        </tr>
        <?php
        // query utk menampilkan data dosen dgn paging
        $sql = "SELECT * FROM dosen ORDER BY npk LIMIT ?, ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ii", $start, $perpage);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['npk'] . "</td>";
            echo "<td>" . $row['nama'] . "</td>";
            echo "<td>";
            if ($row['foto_extension']) {
                $ext = strtolower($row['foto_extension']);
                $path_foto = "foto_dosen/" . $row['nama'] . "_" . $row['npk'] . "." . $ext;

                if (file_exists($path_foto)) {
                    echo "<img src='$path_foto?" . time() . "' width='170'>";
                } else {
                    echo "(foto tidak ditemukan)";
                }
            }
            echo "</td>";
            echo "<td>";
            echo "<a href='edit_dosen.php?npk=" . $row['npk'] . "' class='btnEdit'>Edit</a> ";
            echo "<a href='delete_dosen.php?npk=" . $row['npk'] . "' class='btnHapus' onclick='return confirm(\"Yakin hapus dosen?\")'>Hapus</a>";
            echo "</td>";
            echo "</tr>";
        }

        $con->close();
        ?>
    </table>
    <br>
    <a href="admin_home.php" class="btnBack" style="text-align: center">Kembali ke Halaman Utama</a>
    <br>
    <div style="width: 15%; margin: auto;">
        <?php
        echo "<a href='?p=1&cboPage=$perpage'>First</a> ";

        if ($p == 1) {
            echo "<span style='padding: 8px 12px; margin: 0 2px; color: #999; border: 1px solid #ccc; border-radius: 5px;'>Prev</span> ";
        } else {
            $x = $p - 1;
            echo "<a href='?p=$x&cboPage=$perpage'>Prev</a> ";
        }

        for ($i = 1; $i <= $totalpage; $i++) {
            if ($i == $p) {
                echo "<strong>$i</strong> ";
            } else {
                echo "<a href='?p=$i&cboPage=$perpage'>$i</a> ";
            }
        }
        if ($p == $totalpage || $totalpage == 0) {
            echo "<span style='padding: 8px 12px; margin: 0 2px; color: #999; border: 1px solid #ccc; border-radius: 5px;'>Next</span> ";
        } else {
            $x = $p + 1;
            echo "<a href='?p=$x&cboPage=$perpage'>Next</a> ";
        }
        echo "<a href='?p=$totalpage&cboPage=$perpage'>Last</a>";
        ?>
    </div>
</body>

</html>