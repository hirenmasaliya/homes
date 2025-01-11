<?php
require 'vendor/autoload.php';
include 'dbconfig.php';

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $expenseKey = $_GET['id'];

    // Access the Firebase database reference
    $expensesRef = $database->getReference('Admin/expenses');

    try {
        // Delete the expense by key
        $expensesRef->getChild($expenseKey)->remove();

        // Redirect back to the balance sheet page
        header("Location: index.php?page=balance-sheet&status=deleted");
        exit;
    } catch (Exception $e) {
        echo "Error deleting expense: " . $e->getMessage();
        exit;
    }
} else {
    // If no ID is provided, redirect to the balance sheet page
    header("Location: index.php?page=balance-sheet&status=error");
    exit;
}
