<?php
// include('config.php');

// if (isset($_GET['id'])) {
//     $building_id = $_GET['id'];

//     $sql = "DELETE FROM buildings WHERE id = $building_id";

//     if ($conn->query($sql) === TRUE) {
//         header('Location: index.php?page=add-building');
//         exit;
//     } else {
//         echo "Error: " . $conn->error;
//     }
// } else {
//     echo "Invalid request.";
// }

include('dbconfig.php');


if (isset($_GET['id'])) {
    $building_id = $_GET['id'];

    $toBeDeleted = $database->getReference("Admin/buildings/{$building_id}");
    
    $toBeDeleted->remove();

    header('Location: index.php?page=add-building');
    exit;
} else {
    echo "Invalid request.";
}
?>