<?php
include('config.php');
include('dbconfig.php');
$notificationMessage = "";

$buildingId = isset($_GET['edit']) ? $_GET['edit'] : null;
$building = null;

if ($buildingId) {
    $reference = $database->getReference("Admin/buildings/{$buildingId}");
    $building = $reference->getValue();
}

if (isset($_POST['submit'])) {
    $building_name = trim($_POST['building_name']);

    if ($buildingId) {
        $database->getReference("Admin/buildings/{$buildingId}")
            ->update(['building_name' => $building_name]);
        $notificationMessage = "Building updated successfully!";
    } else {
        $key = $database->getReference('Admin/buildings')->push()->getKey();
        $database->getReference("Admin/buildings/{$key}")
            ->set([
                'id' => $key,
                'building_name' => $building_name,
            ]);
        $notificationMessage = "Building added successfully!";
    }

    header("Location: index.php?page=add-building");
    exit;
}

$reference = $database->getReference('Admin/buildings');
$value = $reference->getValue();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Building</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 40%;
            position: relative;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-button {
            padding: 12px;
            background-color: #006241;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            margin: 10px 0;
        }

        .modal-button:hover {
            background-color: #004c1a;
        }

        .container {
            background-color: #fff;

            border-radius: 8px;
            width: 400px;
        }

        h2 {
            color: #006241;
        }

        .notification {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #006241;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: none;
            font-size: 16px;
            z-index: 1000;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            text-align: start;
        }

        label {
            font-size: 16px;
            color: #333;
        }

        input[type="text"],
        input[type="number"] {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
        }

        button {
            padding: 12px;
            background-color: #006241;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #006241;
        }

        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
        }

        table thead {
            background-color: #006241;
            color: white;
        }

        table th,
        table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 8px 12px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }

        .btn-edit {
            background-color: #006241;
        }

        .btn-delete {
            background-color: #f44336;
        }

        .btn:hover {
            opacity: 0.8;
        }
    </style>
    <script>
        function showNotification(message) {
            var notification = document.getElementById("notification");
            notification.innerHTML = message;
            notification.style.display = "block";

            setTimeout(function () {
                notification.style.display = "none";
            }, 3000);
        }

        window.onload = function () {
            <?php if ($notificationMessage != ""): ?>
                showNotification("<?php echo $notificationMessage; ?>");
            <?php endif; ?>
        };

        function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }
    </script>
</head>

<body>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>

            <h2><?php echo $buildingId ? 'Edit Building Details' : 'Add Building Details'; ?></h2>
            <form action="" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($buildingId); ?>">
                <label for="building_name">Building Name:</label>
                <input type="text" name="building_name" id="building_name" required
                    value="<?php echo $building ? htmlspecialchars($building['building_name']) : ''; ?>"
                    placeholder="Enter Building Name">
                <button type="submit" name="submit"><?php echo $buildingId ? 'Update' : 'Save'; ?></button>
            </form>

        </div>
    </div>

    <div id="notification" class="notification"></div>

    <div class="containers">
        <h2>Building List</h2>
        <button class="modal-button" onclick="openModal()">Add Building</button>
        <?php if (!empty($value)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Building ID</th>
                        <th>Building Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($value as $key => $building): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($building['id']); ?></td>
                            <td><?php echo htmlspecialchars($building['building_name']); ?></td>
                            <td>
                                <a href="index.php?page=add-building&edit=<?php echo $building['id']; ?>" class="btn btn-edit"
                                    onclick="openModal()">Edit</a>
                                <a href="delete_building.php?id=<?php echo $building['id']; ?>" class="btn btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this building?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No buildings found.</p>
        <?php endif; ?>
    </div>

</body>

</html>