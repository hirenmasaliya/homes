<?php
include 'dbconfig.php';

$editId = $_GET['id'] ?? '';

$buildingRef = $database->getReference("Admin/buildings");
$building = $buildingRef->getValue();

$floorRef = $database->getReference("Admin/floor");
$floor = $floorRef->getValue();

$flatRef = $database->getReference("Admin/home/{$editId}");
$flatDetails = $flatRef->getValue();

$maintenanceRef = $database->getReference("Admin/maintenance");
$maintenanceDetails = $maintenanceRef->getValue();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $paymentType = $_POST['payment_type'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $paymentDate = new DateTime($date);
    $year = $paymentDate->format('Y');
    $month = $paymentDate->format('m');

    $maintenancePath = "Admin/maintenance/{$year}/{$month}/{$editId}";

    $maintenanceData = [
        'payment_type' => $paymentType,
        'description' => $description,
        'amount' => $amount,
        'date' => $date,
        'payment_status' => 'Pending',
        'building_name' => $building[$flatDetails['building']]['building_name'],
        'floor_name' => $floor[$flatDetails['floor']]['floor_name'],
        'flat_name' => $flatDetails['homeNumber']
    ];

    $database->getReference($maintenancePath)->update($maintenanceData);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Edit Maintenance</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
    }

    form {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        width: 100%;
        max-width: 600px;
        margin: 20px;
        justify-self: center;
    }

    h1 {
        text-align: center;
        font-size: 28px;
        color: #333;
        margin-bottom: 10px;
    }

    label {
        display: block;
        font-size: 16px;
        color: #333;
        margin: 5px 0 5px;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"],
    select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background-color: #fafafa;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    input[type="date"]:focus,
    select:focus {
        outline: none;
        border-color: #006241;
        background-color: #fff;
    }

    button[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #006241;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
        background-color: #004f32;
    }

    /* Info text (building, floor, flat) */
    p {
        font-size: 16px;
        color: #333;
        margin-bottom: 20px;
    }
</style>

<body>



    <form method="POST" action="">
        <h1>Edit Maintenance</h1>
        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($editId); ?>">

        <label>Building:</label>
        <input type="text" name=""
            value="<?php echo htmlspecialchars($building[$flatDetails['building']]['building_name']); ?>" required readonly>

        <label>Floor:</label>
        <input type="text" name="" value="<?php echo htmlspecialchars($floor[$flatDetails['floor']]['floor_name']); ?>"required readonly>

        <label>Flat:</label>
        <input type="text" name="" value="<?php echo htmlspecialchars($flatDetails['homeNumber']); ?>" required readonly>

        <label for="payment_type">Payment Type</label>
        <select name="payment_type" required>
            <option value="Cash" <?php echo ($maintenanceDetails['payment_type'] == 'Cash') ? 'selected' : ''; ?>>Cash
            </option>
            <option value="Card" <?php echo ($maintenanceDetails['payment_type'] == 'Card') ? 'selected' : ''; ?>>Card
            </option>
            <option value="Online" <?php echo ($maintenanceDetails['payment_type'] == 'Online') ? 'selected' : ''; ?>>
                Online</option>
        </select>

        <label for="description">Description</label>
        <input type="text" name="description"
            value="<?php echo htmlspecialchars($maintenanceDetails['description']); ?>" required>

        <label for="amount">Amount</label>
        <input type="number" name="amount" value="<?php echo htmlspecialchars($maintenanceDetails['amount']); ?>"
            required>

        <label for="date">Date</label>
        <input type="date" name="date" value="<?php echo htmlspecialchars($maintenanceDetails['date']); ?>" required>

        <button type="submit">Update Maintenance</button>
    </form>

</body>

</html>