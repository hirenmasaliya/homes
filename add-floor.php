<?php

include 'config.php';
include 'dbconfig.php';

// $result = $conn->query("SELECT * FROM buildings");

$reference = $database->getReference('Admin/buildings');
$value = $reference->getValue();

$ref = $database->getReference('Admin/floor');
$floor = $ref->getValue();

$editFloor = null;
if (isset($_GET['id'])) {
    $floorId = $_GET['id'];
    $editFloorRef = $database->getReference("Admin/floor/{$floorId}");
    $editFloor = $editFloorRef->getValue();

    if (!$editFloor) {
        echo "Floor not found.";
        exit;
    }
}


if (isset($_POST['submit'])) {
    $floorName = $_POST['floorName'];
    $buildingId = $_POST['buildingId'];

    if ($editFloor) {
        $database->getReference("Admin/floor/{$editFloor['id']}")
            ->update([
                'buildingId' => $buildingId,
                'floor_name' => $floorName
            ]);
    } else {
        $key = $database->getReference('Admin/floor')->push()->getKey();
        $database->getReference("Admin/floor/{$key}")
            ->set([
                'id' => $key,
                'buildingId' => $buildingId,
                'floor_name' => $floorName
            ]);
    }

    header("Location: index.php?page=add-floor");
    exit;
}


if (isset($_POST['search'])) {
    $searchQuery = trim($_POST['search']);
    $searchTerms = explode(' ', $searchQuery); // Split the search query into multiple terms

    $filteredFloors = array_filter($floor, function ($floors) use ($searchTerms, $database) {
        $buildingRef = $database->getReference("Admin/buildings/{$floors['buildingId']}");
        $building = $buildingRef->getValue();

        foreach ($searchTerms as $term) {
            if (
                stripos((string) $floors['buildingId'], $term) !== false ||  // Convert buildingId to string
                (isset($building['building_name']) && stripos($building['building_name'], $term) !== false) ||
                stripos($floors['floor_name'], $term) !== false
            ) {
                return true;
            }
        }
        return false;
    });
} else {
    $filteredFloors = $floor;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Dashboard</title>
    <style>
        h2 {
            color: #006241;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .add-button-container {
            display: flex;
            justify-content: flex-end;
            margin: 20px;
        }

        .add-button {
            padding: 10px 20px;
            background-color: #006241;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .add-button:hover {
            background-color: #004d33;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close-button {
            float: right;
            font-size: 18px;
            cursor: pointer;
            color: #006241;
        }

        .close-button:hover {
            color: #004d33;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        td,th{
            font-size: 14px;
        }

        label {
            font-size: 16px;
        }

        input[type="text"],
        select {
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 96%;
        }

        select{
            width: 100%;
        }

        button[type="submit"] {
            padding: 10px;
            background-color: #006241;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #004d33;
        }

        .search form {
            display: flex;
            flex-direction: row;
        }

        .search form input{
            width: 250px;
            margin-right: 0;
        }
        .search form button{
            width: 110px;
            margin-right: 0;
        }
    </style>
</head>

<body>
    <h2>Manage Floors</h2>

    <div class="add-button-container">
        <button class="add-button" id="openModalBtn">Add Floor</button>
    </div>

    <div class="add-button-container">
        <div class="search">
            <form method="post" action="">
                <input type="text" name="search" placeholder="Search by Floor or Building Name">
                <button class="btn" type="submit">Search</button>
            </form>
        </div>
    </div>


    <div class="modal" id="floorModal">
        <div class="modal-content">
            <span class="close-button" id="closeModalBtn">&times;</span>
            <h2><?php echo $editFloor ? 'Edit Floor' : 'Add Floor'; ?></h2>
            <form action="" method="post">
                <?php if ($editFloor): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editFloor['id']); ?>">
                <?php endif; ?>
                <!-- <label for="buildingId">Select Building</label> -->
                <select name="buildingId" id="buildingId" required>
                    <option value="">Select a Building</option>
                    <?php foreach ($value as $key => $row): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($editFloor && $editFloor['buildingId'] == $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['building_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- <label for="floorName">Floor Name</label> -->
                <input type="text" name="floorName" id="floorName" placeholder="Enter Floor Name"
                    value="<?php echo htmlspecialchars($editFloor['floor_name'] ?? ''); ?>" required>
                <button type="submit" name="submit"><?php echo $editFloor ? 'Update' : 'Submit'; ?></button>
            </form>
        </div>
    </div>



    <div>
        <?php if (!empty($filteredFloors)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Building ID</th>
                        <th>Building Name</th>
                        <th>Floor Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredFloors as $key => $floors): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($floors['id']); ?></td>
                            <td><?php echo htmlspecialchars($floors['buildingId']); ?></td>
                            <?php
                            $buildingRef = $database->getReference("Admin/buildings/{$floors['buildingId']}");
                            $building = $buildingRef->getValue();
                            ?>
                            <td><?php echo htmlspecialchars($building['building_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($floors['floor_name']); ?></td>
                            <td>
                                <a href="index.php?page=add-floor&id=<?php echo $floors['id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="delete_floor.php?id=<?php echo $floors['id']; ?>" class="btn btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this floor?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No floors match your search criteria.</p>
        <?php endif; ?>
    </div>

    <script>
        const openModalBtn = document.getElementById('openModalBtn');
        const floorModal = document.getElementById('floorModal');
        const closeModalBtn = document.getElementById('closeModalBtn');

        openModalBtn.addEventListener('click', () => {
            floorModal.style.display = 'flex';
        });

        closeModalBtn.addEventListener('click', () => {
            floorModal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === floorModal) {
                floorModal.style.display = 'none';
            }
        });
    </script>
</body>

</html>