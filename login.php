<?php
    include 'config.php';

    if (isset($_COOKIE["login"]) && $_COOKIE["login"] === "true") {
        header("Location: index.php");
        exit;
    }

    if (isset($_POST["submit"])){

        $email = $_POST['email'];
        $password = $_POST['password'];

        if($email == "admin@gmail.com" && $password == "1234"){
            $_SESSION['login'] = true;
            setcookie("login", "true", time() + (86400 * 7), "/");
            header("Location: index.php");
            echo "User Login Successfully..";
        } else {
            echo "User Not Login...";
        }

    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('green-city.jpg');
            background-size: 100% auto;
            background-position: bottom center;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: self-start;
            height: 100vh;
            color: #333;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.10);
            width: 100%;
            max-width: 400px;
            padding: 10px;
            border-radius: 8px;
            margin-top: 120px;
            text-align: center;
        }

        h1 {
            font-size: 30px;
            margin-bottom: 5px;
            color: #006241;
        }

        h2{
            margin-bottom: 5px;
            text-align: start;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 18px;
            margin-bottom: 8px;
        }

        input[type="email"],
        input[type="password"] {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #006241;
        }

        button {
            padding: 12px;
            background-color: #006241;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #006241;
        }
    </style>
</head>

<body>
    <div class="container">
        <form action="" method="post">
            <h1>Welcome to My Society App</h1>
            <h2>Login</h2>
            <input type="email" name="email" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>

            <button type="submit" name="submit">Login</button>
        </form>
    </div>
</body>

</html>
