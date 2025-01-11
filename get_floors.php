<?php
include 'dbconfig.php';

if (isset($_POST['buildingId'])) {
    $buildingId = $_POST['buildingId'];
    
    $reference = $database->getReference('Admin/floor');
    $floors = $reference->getValue();

    $filteredFloors = [];
    
    foreach ($floors as $key => $row) {
        if ($row['buildingId'] == $buildingId) {
            $filteredFloors[] = [
                'key' => $key,
                'floor_name' => $row['floor_name']
            ];
        }
    }
    
    echo json_encode($filteredFloors);
}
?>
