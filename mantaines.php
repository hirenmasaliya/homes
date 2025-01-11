<?php
require 'vendor/autoload.php';
include 'dbconfig.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_COOKIE["login"]) || $_COOKIE["login"] !== "true") {
    echo "Cookie not set or invalid.";
    exit();
}

$currentYear = isset($_POST['year']) ? $_POST['year'] : date("Y");
$currentMonth = isset($_POST['month']) ? $_POST['month'] : date("m");

$flatsRef = $database->getReference('Admin/home');
$flats = $flatsRef->getValue();

$buildingRef = $database->getReference("Admin/buildings");
$building = $buildingRef->getValue();

$floorRef = $database->getReference("Admin/floor");
$floor = $floorRef->getValue();

$maintenanceRef = $database->getReference("Admin/maintenance/{$currentYear}/{$currentMonth}");
$maintenance = $maintenanceRef->getValue() ?? [];

$entriesPerPage = 10;
$totalEntries = count($flats);
$totalPages = ceil($totalEntries / $entriesPerPage);
$currentPage = isset($_GET['page']) ? max(1, min((int) $_GET['page'], $totalPages)) : 1;
$startIndex = ($currentPage - 1) * $entriesPerPage;

$flatsToShow = array_slice($flats, $startIndex, $entriesPerPage, true);

$totalAllFlatsAmount = 0;
$pageTotalAmount = 0;
foreach ($flatsToShow as $key => $flat) {
    if (isset($maintenance[$key]['amount'])) {
        $pageTotalAmount += $maintenance[$key]['amount'];
    }
}
foreach ($maintenance as $data) {
    if (isset($data['amount'])) {
        $totalAllFlatsAmount += $data['amount'];
    }
}

if (isset($_POST['update_payment'])) {
    $updatedPayments = $_POST['payment_received'] ?? [];
    $defaultAmount = 2000;

    $newTotalIncome = 0;

    foreach ($flats as $flatId => $flat) {
        $paymentReceived = isset($updatedPayments[$flatId]) && $updatedPayments[$flatId] === 'on';

        $existingPayment = $maintenance[$flatId]['payment_received'] ?? false;
        $existingAmount = $maintenance[$flatId]['amount'] ?? $defaultAmount;

        $buildingName = $building[$flat['building']]['building_name'] ?? '';
        $floorName = $floor[$flat['floor']]['floor_name'] ?? '';
        $flatNumber = $flat['homeNumber'] ?? '';
        $ownerName = $flat['ownerDetails']['name'] ?? '';
        $ownerEmail = $flat['ownerDetails']['email'] ?? '';
        $ownerMobile = $flat['ownerDetails']['mobileNo'] ?? '';
        $ownerAddress = $flat['ownerDetails']['address'] ?? '';
        $paymentDate = date("Y-m-d");
        $paymentAmount = $existingAmount;
        // $paymentType = $_POST['payment_type'][$flatId] ?? '';
        // $description = $_POST['description'][$flatId] ?? '';
        // $status = $_POST['status'][$flatId] ?? '';

        if ($paymentReceived !== $existingPayment) {
            $maintenanceRef->getChild($flatId)->update([
                'payment_received' => $paymentReceived,
                'amount' => $existingAmount,
                'building_name' => $buildingName,
                'floor_name' => $floorName,
                'flat_number' => $flatNumber,
                'owner_name' => $ownerName,
                'owner_email' => $ownerEmail,
                'owner_mobile' => $ownerMobile,
                'owner_address' => $ownerAddress,
                'payment_date' => $paymentDate,
                'payment_amount' => $paymentAmount,
                // 'payment_type' => $paymentType,
                // 'description' => $description,
                // 'status' => $status
            ]);
        }

        if ($paymentReceived) {
            $newTotalIncome += $existingAmount;
        }
    }

    $database->getReference("Admin/income/{$currentYear}/{$currentMonth}")
        ->set(['totalIncome' => $newTotalIncome]);

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

if (isset($_POST['send_reminder'])) {
    $mailer = new PHPMailer(true);
    try {
        $mailer->isSMTP();
        $mailer->Host = 'smtp.gmail.com';
        $mailer->SMTPAuth = true;
        $mailer->Username = 'hirenmasaliya14@gmail.com';
        $mailer->Password = 'jaopevmdwvprosfo';
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = 587;

        $mailer->setFrom('hirenmasaliya14@gmail.com', 'Maintenance Management');

        foreach ($flats as $key => $flat) {
            $maintenanceData = $maintenance[$key] ?? null;
            $paymentAmount = $maintenanceData['amount'] ?? 2000;
            $paymentReceived = $maintenanceData['payment_received'] ?? false;

            if (!$paymentReceived) {
                // $mailer->addAddress('hirenmasaliya456@gmail.com', $flat['ownerDetails']['name']);
                $mailer->addAddress($flat['ownerDetails']['email'], $flat['ownerDetails']['name']);

                $mailer->Subject = 'Reminder: Unpaid Maintenance Fee';
                $mailer->Body = "Dear {$flat['ownerDetails']['name']},\n\n" .
                    "This is a reminder that your maintenance payment of ₹{$paymentAmount} for the month of {$currentMonth}/{$currentYear} is still pending.\n" .
                    "Please make the payment at your earliest convenience.\n\n" .
                    "Thank you,\nMaintenance Management";

                $mailer->send();
                $mailer->clearAddresses();
            }
        }


    } catch (Exception $e) {
        echo "Mailer Error: {$mailer->ErrorInfo}";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Management - <?php echo "$currentMonth/$currentYear"; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    h1 {
        margin-top: 0;
        padding: 0;
    }

    table thead {
        background-color: #006241;
        color: white;
    }

    td,
    th {
        font-size: 14px;
    }

    .form-group label {
        font-size: 14px;
    }

    select {
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 20%;
    }

    .action-buttons {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        justify-content: space-evenly;
    }

    .btn {
        padding: 8px 12px;
        text-decoration: none;
        color: white;
        border-radius: 4px;
        font-size: 14px;
        text-align: center;
        margin-top: 10px;
        justify-self: end;
    }

    .btn-export {
        background-color: #006241;
    }

    .form-group {
        margin: 15px 0;
    }

    .status-pending {
        color: red;
    }

    .status-done {
        color: green;
    }

    .pagination {
        display: flex;
        justify-content: center;
        list-style-type: none;
        padding: 0;
        margin: 20px 0;
    }

    .pagination li {
        margin: 0 5px;
    }

    .pagination a {
        text-decoration: none;
        color: #006241;
        border: 1px solid #006241;
        /* background-color: #006241; */
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        margin-right: 10px;
    }

    .pagination a.active {
        font-weight: bold;
        color: white;
        background-color: #004d30;
        pointer-events: none;
    }
</style>

<body>
    <h1>Maintenance Management - <?php echo "$currentMonth/$currentYear"; ?></h1>
    <form method="POST" style="margin-bottom: 20px;">
        <!-- <label for="month">Select Month:</label> -->
        <select name="month" id="month">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo str_pad($m, 2, "0", STR_PAD_LEFT); ?>" <?php echo ($m == $currentMonth) ? "selected" : ""; ?>>
                    <?php echo date("F", mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>

        <!-- <label for="year">Select Year:</label> -->
        <select name="year" id="year">
            <?php for ($y = 2020; $y <= date("Y"); $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo ($y == $currentYear) ? "selected" : ""; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>

        <button type="submit" class="btn">Fetch Data</button>
    </form>

    <div style="display: flex; justify-content: space-between">
        <form action="getFile.php" method="POST" style="margin: 0;">
            <input type="hidden" name="month" value="<?php echo $currentMonth; ?>">
            <input type="hidden" name="year" value="<?php echo $currentYear; ?>">
            <button type="submit" name="export" class="btn btn-export">Export to Excel</button>
        </form>
        <form method="POST" style="">
            <button type="submit" name="send_reminder" class="btn">Reminder Emails</button>
        </form>
    </div>


    <form method="POST">
        <button style="display: flex; justify-self: end;" type="submit" name="update_payment" class="btn">Update
            Payments</button>
        <table>
            <thead>
                <tr>
                    <th>Building</th>
                    <th>Floor</th>
                    <th>Flat</th>
                    <th>Owner</th>
                    <th>Amount</th>
                    <th>Payment Received</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($flatsToShow)): ?>
                    <?php foreach ($flatsToShow as $key => $flat): ?>
                        <?php
                        $maintenanceData = $maintenance[$key] ?? null;
                        $paymentAmount = $maintenanceData['amount'] ?? 0;
                        $paymentReceived = $maintenanceData['payment_received'] ?? false;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($building[$flat['building']]['building_name']); ?></td>
                            <td><?php echo htmlspecialchars($floor[$flat['floor']]['floor_name']); ?></td>
                            <td><?php echo htmlspecialchars($flat['homeNumber']); ?></td>
                            <td><?php echo htmlspecialchars($flat['ownerDetails']['name']); ?></td>
                            <td>₹<?php echo number_format($paymentAmount, 2); ?></td>
                            <td>
                                <input type="checkbox" name="payment_received[<?php echo $key; ?>]" <?php echo $paymentReceived ? 'checked' : ''; ?>>
                                <?php
                                if ($paymentReceived) {
                                    echo "<span class='status-done'>Payment Received</span>";
                                } else {
                                    echo "<span class='status-pending'>Pending</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $key; ?>" class="btn btn-edit">Edit</a>
                                <a href="paymentRecipt.php?flat_id=<?php echo $key; ?>" class="btn btn-edit">Download Receipt</a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No data available for the selected month and year.</td>
                    </tr>
                <?php endif; ?>

                <tr style="background-color: #006241;color: white">
                    <td colspan="4"><strong>Total Amount for This Page:</strong></td>
                    <td><strong>₹ <?php echo number_format($pageTotalAmount, 0, '', ','); ?></strong></td>
                    <td colspan="2"></td>
                </tr>

                <tr style="background-color: #006241;color: white">
                    <td colspan="4"><strong>Total Amount for All Flats:</strong></td>
                    <td><strong>₹ <?php echo number_format($totalAllFlatsAmount, 0, '', ','); ?></strong></td>
                    <td colspan="2"></td>
                </tr>

            </tbody>
        </table>


    </form>

    <div class="pagination">
        <?php if ($totalPages > 1): ?>
            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                <a href="?page=<?php echo $page; ?>" class="<?php echo ($page == $currentPage) ? 'active' : ''; ?>">
                    <?php echo $page; ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>

</body>

</html>