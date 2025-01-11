<?php
include 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $buildingId = $_POST['buildingId'];
    $floorId = $_POST['floorId'];

    $flatRef = $database->getReference("Admin/home");
    $flats = $flatRef->getValue();

    $filteredFloors = [];
    
    foreach ($flats as $key => $flat) {
        if ($flat['building'] == $buildingId && $flat['floor'] == $floorId) {
            $filteredFloors[] = [
                'key' => $key,
                'homeNumber' => $flat['homeNumber'],
            ];
        }
    }
    echo json_encode($filteredFloors);
}
?>
