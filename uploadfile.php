<?php
include 'dbconfig.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
        if ($_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];

            // Verify valid Excel file
            $fileType = mime_content_type($fileTmpPath);
            if (!in_array($fileType, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])) {
                throw new Exception("Invalid file type. Please upload a valid Excel file.");
            }

            // Load the Excel file
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $buildingsSnapshot = $database->getReference('Admin/buildings')->getValue();
            $existingBuildings = [];
            if ($buildingsSnapshot) {
                foreach ($buildingsSnapshot as $id => $building) {
                    $existingBuildings[$building['building_name']] = $id;
                }
            }

            foreach ($rows as $key => $row) {
                if ($key === 1) {
                    continue; // Skip header row
                }

                $buildingName = trim($row['A'] ?? null);
                $floorName = trim($row['B'] ?? null);
                $flatNumber = trim($row['C'] ?? null);

                if (empty($buildingName) || empty($floorName) || empty($flatNumber)) {
                    continue;
                }

                if (!isset($existingBuildings[$buildingName])) {
                    $buildingId = $database->getReference('Admin/buildings')->push()->getKey();
                    $newBuildingRef = $database->getReference("Admin/buildings/{$buildingId}")->set([
                        'id' => $buildingId,
                        'building_name' => $buildingName
                    ]);
                    //$buildingId = $newBuildingRef->getKey();
                    $existingBuildings[$buildingName] = $buildingId;
                } else {
                    $buildingId = $existingBuildings[$buildingName];
                }

                $floorId = $database->getReference('Admin/floor')->push()->getKey();
                $floorsSnapshot = $database->getReference('Admin/floor')->getValue();
                $existingFloors = [];
                if ($floorsSnapshot) {
                    foreach ($floorsSnapshot as $id => $floor) {
                        $existingFloors[$floor['floor_name']] = $id;
                    }
                }

                if (!isset($existingFloors[$floorName])) {
                    $newFloorRef = $database->getReference("Admin/floor/{$floorId}")->set([
                        'id' => $floorId,
                        'floor_name' => $floorName,
                        'buildingId' => $buildingId
                    ]);
                    //$floorId = $newFloorRef->getKey();
                    $existingFloors[$floorName] = $floorId;
                } else {
                    $floorId = $existingFloors[$floorName];
                }

                $flatsId = $database->getReference('Admin/home')->push()->getKey();

                $newFlatRef = $database->getReference("Admin/home/{$flatsId}")->set([
                    'building' => $buildingId,
                    'floor' => $floorId,
                    'homeNumber' => $flatNumber,
                    'isRental' => false,
                    'ownerDetails' => [
                        'address' => '',
                        'email' => '',
                        'mobileNo' => '',
                        'name' => '',
                    ],
                    'permanentAddress' => '',
                    'tenantDetails' => [
                        'address' => '',
                        'email' => '',
                        'mobileNo' => '',
                        'name' => '',
                    ],
                ]);
            }

            echo "Data inserted successfully!";

            header("Location: index.php?page=import");
            exit;
        } else {
            throw new Exception("File upload error: " . $_FILES['excel_file']['error']);
        }
    }
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>