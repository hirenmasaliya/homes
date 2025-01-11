<?php

require 'vendor/autoload.php';
include 'dbconfig.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_POST['export-file']) || isset($_GET['flat_id'])) {
    $flatId = $_POST['flat_id'] ?? $_GET['flat_id'];

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

    if ($flatId) {
        $flat = $flats[$flatId];
        $maintenanceData = $maintenance[$flatId] ?? [];

        $paymentAmount = $maintenanceData['amount'] ?? 0;
        $paymentReceived = $maintenanceData['payment_received'] ?? false;
        $buildingName = $maintenanceData['building_name'] ?? 'Unknown';
        $floorName = $maintenanceData['floor_name'] ?? 'Unknown';
        $homeNumber = $maintenanceData['flat_number'] ?? 'Unknown';
        $paymentType = $maintenanceData['payment_type'] ?? 'Pending';
        $description = $maintenanceData['description'] ?? 'No details';
        $paymentDate = $maintenanceData['payment_date'] ?? '-';
        $status = $maintenanceData['payment_received'] ? "Payment Done Successfully" : "Pending";
        $receiptNumber = "RCPT" . date("YmdHis");

        $ownerName = $maintenanceData['owner_name'] ?? 'Unknown Owner';
        $ownerEmail = $maintenanceData['owner_email'] ?? 'Unknown Email';
        $ownerMobile = $maintenanceData['owner_mobile'] ?? 'Unknown Mobile';
        $ownerAddress = $maintenanceData['owner_address'] ?? 'Unknown Address';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells("A1:D1");
        $sheet->setCellValue("A1", "Maintenance Payment Receipt");
        $sheet->getStyle("A1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $details = [
            ["Receipt Number", $receiptNumber],
            ["Building Name", $buildingName],
            ["Floor Name", $floorName],
            ["Flat Number", $homeNumber],
            ["Owner Name", $ownerName],
            ["Owner Email", $ownerEmail],
            ["Owner Mobile", $ownerMobile],
            ["Owner Address", $ownerAddress],
            ["Payment Date", $paymentDate],
            ["Payment Amount", "₹" . number_format($paymentAmount, 2)],
            ["Payment Type", $paymentType],
            ["Description", $description],
            ["Status", $status],
        ];


        $row = 2;
        foreach ($details as $detail) {
            $sheet->setCellValue("A$row", $detail[0]);
            $sheet->setCellValue("B$row", $detail[1]);
            $sheet->getStyle("A$row")->getFont()->setBold(true);
            $sheet->getStyle("B$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $row++;
        }

        $sheet->mergeCells("A$row:B$row");
        $sheet->setCellValue("A$row", "Thank you for your payment!");
        $sheet->getStyle("A$row")->applyFromArray([
            'font' => ['italic' => true, 'color' => ['argb' => 'FF008000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(50);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Maintenance_Receipt_'. $maintenanceData['building_name'] .'_'. $maintenanceData['floor_name'] .'_' . $maintenanceData['flat_number'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        echo "Flat not found.";
    }
} else {
    echo "No flat ID provided.";
}


?>