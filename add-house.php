<?php
include 'dbconfig.php';

$buildingId = isset($_GET['edit']) ? $_GET['edit'] : null;
$building = null;

if ($buildingId) {
    $reference = $database->getReference("Admin/home/{$buildingId}");
    $building = $reference->getValue();
}

$buildingRef = $database->getReference('Admin/buildings');
$buildingList = $buildingRef->getValue();

$floorRef = $database->getReference('Admin/floor');
$floors = $floorRef->getValue();

$homeRef = $database->getReference("Admin/home");
$homes = $homeRef->getValue();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $building = $_POST['building'];
    $floor = $_POST['floor'];
    $homeNumber = $_POST['homeNumber'];
    $ownerName = $_POST['OwnerName'];
    $ownerMobileNo = $_POST['OwnerMobileNo'];
    $ownerEmail = $_POST['OwnerEmail'];
    $ownerAddress = $_POST['OwnerAddress'];
    $permanentAddress = $_POST['PermanentAddress'];
    $isRental = isset($_POST['isRental']) ? true : false;
    $tenantName = isset($_POST['TenantName']) ? $_POST['TenantName'] : null;
    $tenantMobileNo = isset($_POST['TenantMobileNo']) ? $_POST['TenantMobileNo'] : null;
    $tenantEmail = isset($_POST['TenantEmail']) ? $_POST['TenantEmail'] : null;
    $tenantAddress = isset($_POST['TenantAddress']) ? $_POST['TenantAddress'] : null;
    $idProof = isset($_FILES['idProof']) ? $_FILES['idProof'] : null;


    if ($buildingId) {
        $database->getReference("Admin/home/{$buildingId}")
            ->update([
                'building' => $building,
                'floor' => $floor,
                'homeNumber' => $homeNumber,
                'ownerDetails' => [
                    'name' => $ownerName,
                    'mobileNo' => $ownerMobileNo,
                    'email' => $ownerEmail,
                    'address' => $ownerAddress
                ],
                'permanentAddress' => $permanentAddress,
                'isRental' => $isRental,
                'tenantDetails' => [
                    'name' => $tenantName,
                    'mobileNo' => $tenantMobileNo,
                    'email' => $tenantEmail,
                    'address' => $tenantAddress
                ]
            ]);
        header("Location: index.php?page=view-houses");
        exit;
    } else {
        $key = $database->getReference('Admin/home')->push()->getKey();
        $database->getReference("Admin/home/{$key}")
            ->set([
                'building' => $building,
                'floor' => $floor,
                'homeNumber' => $homeNumber,
                'ownerDetails' => [
                    'name' => $ownerName,
                    'mobileNo' => $ownerMobileNo,
                    'email' => $ownerEmail,
                    'address' => $ownerAddress
                ],
                'permanentAddress' => $permanentAddress,
                'isRental' => $isRental,
                'tenantDetails' => [
                    'name' => $tenantName,
                    'mobileNo' => $tenantMobileNo,
                    'email' => $tenantEmail,
                    'address' => $tenantAddress
                ]
            ]);
        header("Location: index.php?page=add-house");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Edit House</title>
    <style>
        h1 {
            color: #333;
        }

        form {
            max-width: 600px;
        }

        form h3 {
            color: #555;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="file"],
        select {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="checkbox"] {
            margin: 10px 0;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #006241;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #006241;
        }
    </style>
</head>

<body>
    <h1><?php echo $buildingId ? 'Edit' : 'Add New'; ?> House</h1>

    <form action="" method="post" enctype="multipart/form-data">
        <h3>Building and Floor:</h3>
        <select name="building" id="building" required>
            <option value="">Select Building</option>
            <?php foreach ($buildingList as $key => $row): ?>
                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($building && $building['building'] == $key) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($row['building_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="floor" id="floor" required>
            <option value="">Select Floor</option>
        </select>

        <input type="text" name="homeNumber" placeholder="Home Number"
            value="<?php echo htmlspecialchars($building['homeNumber'] ?? ''); ?>" required>

        <h3>Owner Details:</h3>
        <input type="text" name="OwnerName" placeholder="Owner Name"
            value="<?php echo htmlspecialchars($building['ownerDetails']['name'] ?? ''); ?>" required>
        <input type="tel" name="OwnerMobileNo" placeholder="Owner Mobile Number" pattern="[0-9]{10}"
            value="<?php echo htmlspecialchars($building['ownerDetails']['mobileNo'] ?? ''); ?>" required>
        <input type="email" name="OwnerEmail" placeholder="Owner Email Id"
            value="<?php echo htmlspecialchars($building['ownerDetails']['email'] ?? ''); ?>" required>

        <h3>Address:</h3>
        <input type="text" name="OwnerAddress" placeholder="Owner Address"
            value="<?php echo htmlspecialchars($building['ownerDetails']['address'] ?? ''); ?>" required>
        <input type="checkbox" name="sameAddress" id="sameAddress" onchange="togglePermanentAddress()" <?php echo (isset($building['permanentAddress']) && $building['permanentAddress'] == $building['ownerDetails']['address']) ? 'checked' : ''; ?>> Same as House
        Address
        <div id="permanentAddress"
            style="display: <?php echo isset($building['permanentAddress']) && $building['permanentAddress'] != $building['ownerDetails']['address'] ? 'block' : 'none'; ?>;">
            <input type="text" name="PermanentAddress" placeholder="Permanent Address"
                value="<?php echo htmlspecialchars($building['permanentAddress'] ?? ''); ?>">
        </div>

        <h3>Rental Details:</h3>
        <input type="checkbox" name="isRental" id="isRental" onchange="toggleRentalDetails()" <?php echo isset($building['isRental']) && $building['isRental'] ? 'checked' : ''; ?>> Is the house on rent?
        <div id="rentalDetails"
            style="display: <?php echo isset($building['isRental']) && $building['isRental'] ? 'block' : 'none'; ?>;">
            <input type="text" name="TenantName" placeholder="Tenant Name"
                value="<?php echo htmlspecialchars($building['tenantDetails']['name'] ?? ''); ?>">
            <input type="tel" name="TenantMobileNo" placeholder="Tenant Mobile Number" pattern="[0-9]{10}"
                value="<?php echo htmlspecialchars($building['tenantDetails']['mobileNo'] ?? ''); ?>">
            <input type="email" name="TenantEmail" placeholder="Tenant Email Id"
                value="<?php echo htmlspecialchars($building['tenantDetails']['email'] ?? ''); ?>">
            <input type="text" name="TenantAddress" placeholder="Tenant Address"
                value="<?php echo htmlspecialchars($building['tenantDetails']['address'] ?? ''); ?>">
            <input type="file" name="idProof" accept="image/*,.pdf">
        </div>

        <button type="submit" name="submit"><?php echo $buildingId ? 'Update' : 'Submit'; ?></button>
    </form>

    <script>
        function toggleRentalDetails() {
            const rentalDetails = document.getElementById('rentalDetails');
            const isRental = document.getElementById('isRental').checked;
            rentalDetails.style.display = isRental ? 'block' : 'none';
        }

        function togglePermanentAddress() {
            const permanentAddress = document.getElementById('permanentAddress');
            const sameAddress = document.getElementById('sameAddress').checked;
            permanentAddress.style.display = sameAddress ? 'none' : 'block';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const buildingSelect = document.getElementById('building');
            if (buildingSelect.value) {
                loadFloors(buildingSelect.value);
            }

            buildingSelect.addEventListener('change', function () {
                const buildingId = this.value;
                loadFloors(buildingId);
            });

            function loadFloors(buildingId) {
                if (buildingId) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'get_floors.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        if (xhr.status == 200) {
                            var floors = JSON.parse(xhr.responseText);
                            var floorSelect = document.getElementById('floor');
                            floorSelect.innerHTML = '<option value="">Select Floor</option>';

                            floors.forEach(function (floor) {
                                var option = document.createElement('option');
                                option.value = floor.key;
                                option.textContent = floor.floor_name;
                                floorSelect.appendChild(option);
                            });

                            if ('<?php echo $building["floor"] ?? ''; ?>') {
                                floorSelect.value = '<?php echo $building["floor"] ?? ''; ?>';
                            }
                        }
                    };
                    xhr.send('buildingId=' + buildingId);
                } else {
                    document.getElementById('floor').innerHTML = '<option value="">Select Floor</option>';
                }
            }
        });

    </script>
</body>

</html>