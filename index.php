<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_COOKIE["login"]) && $_COOKIE["login"] !== "true") {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 250px;
            background-color: #006241;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            transition: all 0.3s ease;
        }

        .sidebar h2 {
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #004526;
        }

        .sidebar a.active {
            background-color: #004526;
            font-weight: bold;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
            transition: margin-left 0.3s ease;
        }

        .content h1 {
            color: #34495e;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 40px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .section p {
            color: #7f8c8d;
        }

        .btn {
            background-color: #006241;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #006241;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Devs Greens</h2>
        <a href="?page=add-building"
            class="<?= (isset($_GET['page']) && $_GET['page'] == 'add-building') ? 'active' : ''; ?>">Add Building</a>
        <a href="?page=add-floor"
            class="<?= (isset($_GET['page']) && $_GET['page'] == 'add-floor') ? 'active' : ''; ?>">Add Floor</a>
        <a href="?page=add-house"
            class="<?= (isset($_GET['page']) && $_GET['page'] == 'add-house') ? 'active' : ''; ?>">Add House</a>
        <a href="?page=view-houses"
            class="<?= (isset($_GET['page']) && $_GET['page'] == 'view-houses') ? 'active' : ''; ?>">View Houses</a>
        <a href="?page=mantaines"
            class="<?= (isset($_GET['page']) && ($_GET['page'] == 'mantaines' || ($_GET['page'] >= 1 && $_GET['page'] <= 100))) ? 'active' : ''; ?>">
            Maintenance
        </a>
        <a href="?page=notiesboard"
        class="<?= (isset($_GET['page']) && $_GET['page'] == 'notiesboard') ? 'active' : ''; ?>">Notiesboard</a>
        <a href="?page=balance-sheet"
        class="<?= (isset($_GET['page']) && $_GET['page'] == 'balance-sheet') ? 'active' : ''; ?>">Balancesheet</a>

        <a href="?page=import"
        class="<?= (isset($_GET['page']) && $_GET['page'] == 'import') ? 'active' : ''; ?>">Import Data</a>

        <a href="logout.php" class="<?= (isset($_GET['page']) && $_GET['page'] == 'logout') ? 'active' : ''; ?>"
            onclick="return confirm('Are you sure Logout?')">Logout</a>
    </div>

    <div class="content" id="content">
        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];

            if ($page == 'add-building') {
                include('add-building.php');
            } else if ($page == 'add-house') {
                include('add-house.php');
            } elseif ($page == 'add-floor') {
                include('add-floor.php');
            } elseif ($page == 'view-houses') {
                include('view-houses.php');
            } elseif ($page == 'mantaines' || ($page >= 1 && $page <= 100)) {
                include('mantaines.php');
            } elseif ($page == 'notiesboard'){
                include('notiesboard.php');
            } elseif ($page == 'balance-sheet'){
                include('balance-sheet.php');
            }elseif ($page == 'import'){
                include('upload.html');
            }else {
                include('logout.php');
            }
        } else {
            header("Location: index.php?page=add-building");
            exit;
        }
        ?>
    </div>
</body>

</html>