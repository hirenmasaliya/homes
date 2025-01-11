<?php
include('dbconfig.php');

$references = $database->getReference('Admin/home');
$values = $references->getValue();

$ref = $database->getReference('Admin/floor');
$floors = $ref->getValue();

$no = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building List</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
            font-size: 15px;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            display: inline-block;
            width: 80px;
            padding: 8px 0;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            margin: 0 auto;
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

        div.container {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Flat List</h2>
        <?php if (!empty($values)): ?>
            <table>
                <thead>
                    <tr>
                        <th>SR NO</th>
                        <th>Building Name</th>
                        <th>Floor Name(s)</th>
                        <th>Home Number</th>
                        <th>Owner Details</th>
                        <th>Tenant Details</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($values as $key => $building):
                        $no = $no + 1; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($no); ?></td>

                            <?php
                            $buildingReference = $database->getReference("Admin/buildings/{$building['building']}");
                            $buildingData = $buildingReference->getValue();
                            ?>
                            <td><?php echo htmlspecialchars($buildingData['building_name']); ?></td>

                            <?php
                            $floorReference = $database->getReference("Admin/floor/{$building['floor']}");
                            $floorData = $floorReference->getValue();
                            ?>
                            <td><?php echo htmlspecialchars($floorData['floor_name']); ?></td>

                            <td><?php echo htmlspecialchars($building['homeNumber']); ?></td>

                            <td>
                                <h4><?php echo htmlspecialchars($building['ownerDetails']['name']); ?></h4>
                                <br>
                                <?php echo htmlspecialchars($building['ownerDetails']['address']); ?>
                                <br>
                                <?php echo htmlspecialchars($building['ownerDetails']['mobileNo']); ?>
                                <br>
                                <?php echo htmlspecialchars($building['ownerDetails']['email']); ?>
                            </td>


                            <td>
                                <?php if (!empty($building['tenantDetails'])): ?>
                                    <h4><?php echo htmlspecialchars($building['tenantDetails']['name']); ?></h4>
                                    <br>
                                    <?php echo htmlspecialchars($building['tenantDetails']['address']); ?>
                                    <br>
                                    <?php echo htmlspecialchars($building['tenantDetails']['mobileNo']); ?>
                                    <br>
                                    <?php echo htmlspecialchars($building['tenantDetails']['email']); ?>
                                <?php else: ?>
                                    <p>No tenant details available.</p>
                                <?php endif; ?>
                            </td>

                            <td class="display:flex; flex-direction: column;">
                                <a href="index.php?page=add-house&edit=<?php echo $key; ?>"
                                    class="btn btn-edit">Update</a><br><br>
                                <a href="delete_houses.php?id=<?php echo $key; ?>" class="btn btn-delete"
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