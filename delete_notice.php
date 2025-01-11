<?php
include 'dbconfig.php';

if (isset($_GET['notice_id'])) {
    $noticeId = $_GET['notice_id'];

    if (!$noticeId) {
        die("Notice ID is not set or invalid.");
    }

    $database->getReference('Admin/notices/' . $noticeId)->remove();

    header('Location: index.php?page=notiesboard');
    exit;
} else {
    die("Notice ID not provided.");
}
?>
