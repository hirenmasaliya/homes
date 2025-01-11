<?php
include('dbconfig.php');

if (isset($_GET['id'])) {
    $house_id = $_GET['id'];

    $toBeDeleted = $database->getReference("Admin/home/{$house_id}");
    
    $toBeDeleted->remove();

    header('Location: index.php?page=view-houses');
    exit;
} else {
    echo "Invalid request.";
}
?>