<?php
include('dbconfig.php');


if (isset($_GET['id'])) {
    $building_id = $_GET['id'];

    $toBeDeleted = $database->getReference("Admin/floor/{$building_id}");
    
    $toBeDeleted->remove();

    header('Location: index.php?page=add-floor');
    exit;
} else {
    echo "Invalid request.";
}
?>