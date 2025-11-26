<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>Login Akun</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E0D9D9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-container {
            background-color: white;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 0 15px 0 rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
        }

        h2 {
            color: #5A9690;
            margin-bottom: 25px;
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
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login Akun</h2>
        <?php
        session_start();

        if (isset($_SESSION["error_message"])) {
            echo "<script>alert('{$_SESSION['error_message']}')</script>";
            unset($_SESSION['error_message']);
        }
        ?>
        <form method="post" action="login_process.php">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>