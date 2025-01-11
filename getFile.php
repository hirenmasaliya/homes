<?php
require 'vendor/autoload.php';
include 'dbconfig.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (isset($_POST['export'])) {

    // Validate input
    $selectedYear = isset($_GET['year']) && is_numeric($_GET['year']) ? intval($_GET['year']) : date("Y");
    $selectedMonth = isset($_GET['month']) && is_numeric($_GET['month']) ? str_pad(intval($_GET['month']), 2, "0", STR_PAD_LEFT) : date("m");

    $flatsRef = $database->getReference('Admin/home');
    $flats = $flatsRef->getValue();

    $buildingRef = $database->getReference("Admin/buildings");
    $building = $buildingRef->getValue();

    $floorRef = $database->getReference("Admin/floor");
    $floor = $floorRef->getValue();

    $maintenanceRef = $database->getReference("Admin/maintenance/{$selectedYear}/{$selectedMonth}");
    $maintenance = $maintenanceRef->getValue();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add headers
    $headers = ['Building', 'Floor', 'Flat', 'Payment Type', 'Description', 'Amount', 'Date', 'Status'];
    $sheet->fromArray($headers, null, 'A1');

    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FFFFFFFF'],
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF4CAF50'],
        ],
    ];
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(25);

    $row = 2;
    $totalAmount = 0;

    foreach ($flats as $key => $flat) {
        $maintenanceData = isset($maintenance[$key]) ? $maintenance[$key] : null;
        $paymentAmount = $maintenanceData['amount'] ?? 0;

        $sheet->setCellValue('A' . $row, $building[$flat['building']]['building_name']);
        $sheet->setCellValue('B' . $row, $floor[$flat['floor']]['floor_name']);
        $sheet->setCellValue('C' . $row, $flat['homeNumber']);
        $sheet->setCellValue('D' . $row, $maintenanceData['payment_type'] ?? '-');
        $sheet->setCellValue('E' . $row, $maintenanceData['description'] ?? '-');
        $sheet->setCellValue('F' . $row, $paymentAmount);
        $sheet->setCellValue('G' . $row, $maintenanceData['date'] ?? '-');
        $sheet->setCellValue('H' . $row, ($paymentAmount == 0) ? 'Pending' : 'Payment Done Successfully');

        $totalAmount += $paymentAmount;
        $row++;
    }

    $sheet->setCellValue('A' . $row, 'Total');
    $sheet->mergeCells("A{$row}:E{$row}");
    $sheet->setCellValue('F' . $row, $totalAmount);

    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ];
    $sheet->getStyle("A1:H$row")->applyFromArray($styleArray);
    $sheet->getStyle("A2:H$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFDDDDDD'],
        ],
    ]);

    $writer = new Xlsx($spreadsheet);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Maintenance_Data_' . $selectedMonth . '_' . $selectedYear . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    // header("Location: index.php?page=mantaines");
    exit;
}
?>