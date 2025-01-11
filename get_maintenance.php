<?php
include 'dbconfig.php';
$id = $_GET['id'];
$record = $database->getReference("Admin/maintenance/{$id}")->getValue();
echo json_encode($record);
?>