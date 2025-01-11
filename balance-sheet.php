<?php
require 'vendor/autoload.php';
include 'dbconfig.php';

$year = date('Y');
$month = date('m');

$balanceRef = $database->getReference('Admin/income');
$expensesRef = $database->getReference('Admin/expenses');
$maintenanceRef = $database->getReference("Admin/maintenance/{$year}/{$month}");

$incomeData = $balanceRef->getValue();
$totalBalance = array_sum(is_array($incomeData) ? $incomeData : []);

$expenses = $expensesRef->getValue() ?? [];
$totalexpenses = 0;
foreach ($expenses as $key => $exp) {
    $totalexpenses += $exp['amount'];
    $expenses[$key]['key'] = $key; // Add key for update/delete operations
}

$maintenanceIncome = $maintenanceRef->getValue() ?? [];

$totalIncome = 0;
foreach ($maintenanceIncome as $income) {
    $totalIncome += $income['amount'];
}

$currentIncome = $totalIncome;
$currentBalance = $totalBalance + $currentIncome - $totalexpenses;

// Add Expense
if (isset($_POST['add_expense'])) {
    $expenseName = $_POST['expense_name'];
    $expenseAmount = $_POST['expense_amount'];

    $newExpense = [
        'name' => $expenseName,
        'amount' => $expenseAmount,
        'timestamp' => time()
    ];

    $expensesRef->push($newExpense);

    header("Location: index.php?page=balance-sheet");
    exit;
}

// Update Expense
if (isset($_POST['update_expense'])) {
    $expenseKey = $_POST['expense_key'];
    $expenseName = $_POST['expense_name'];
    $expenseAmount = $_POST['expense_amount'];

    $updatedExpense = [
        'name' => $expenseName,
        'amount' => $expenseAmount,
        'timestamp' => time()
    ];

    $expensesRef->getChild($expenseKey)->set($updatedExpense);

    header("Location: index.php?page=balance-sheet");
    exit;
}

if (isset($_GET['delete_expense'])) {
    $expenseKey = $_GET['delete_expense'];

    $expensesRef->getChild($expenseKey)->remove();

    header("Location: index.php?page=balance-sheet");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Balance Sheet</title>
</head>
<style>
    div.box {
        display: flex;
        flex-direction: row;
        justify-content: center;
        gap: 10px;
    }

    p.card {
        width: 25%;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        background-color: #fff;
        border-radius: 8px;
        background-image: linear-gradient(120deg, #d4fc79 0%, #96e6a1 100%);
    }

    #expenseModal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 50%;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        padding: 20px;
        z-index: 1000;
    }

    #expenseModal form {
        display: flex;
        flex-direction: column;
    }

    #expenseModal form div {
        margin-bottom: 15px;
    }

    #expenseModal label {
        font-size: 1rem;
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    #expenseModal input[type="text"],
    #expenseModal input[type="number"] {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    #expenseModal button {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 15px;
        font-size: 1rem;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    #expenseModal button:hover {
        background-color: #45a049;
    }

    #expenseModal button.close {
        background-color: red;
        margin-top: 10px;
    }

    #expenseModal button.close:hover {
        background-color: darkred;
    }

    #expenseModalOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 999;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        text-align: left;
    }

    table th,
    table td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    table th {
        background-color: #006241;
        font-weight: bold;
    }

    table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    table a {
        text-decoration: none;
        color: #007BFF;
        transition: color 0.3s;
    }

    table a:hover {
        color: #0056b3;
    }

    button {
        background-color: #006241;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #006241;
    }

    a.delete-button {
        display: inline-block;
        padding: 10px 15px;
        color: white;
        background-color: #f44336;
        /* Red color for warning */
        text-decoration: none;
        border-radius: 4px;
        font-size: 1rem;
        transition: background-color 0.3s, transform 0.2s;
    }

    a.delete-button:hover {
        background-color: #d32f2f;
        /* Darker red on hover */
        transform: scale(1.05);
        /* Slightly enlarge on hover */
    }
</style>


<body>
    <h1>Society Balance Sheet</h1>

    <div class="box">
        <p class="card"><strong>Account Balance:</strong> ₹<?php echo number_format($currentBalance, 2); ?></p>
        <p class="card"><strong>Income from Maintenance (Month):</strong> ₹<?php echo number_format($totalIncome, 2); ?>
        </p>
        <p class="card"><strong>Expenses:</strong> ₹<?php echo number_format($totalexpenses, 2); ?></p>
    </div>

    <div style="display: flex; flex-direction: row; align-items: center; gap: 10px; justify-content: space-between;">
        <h2>Expenses</h2>
        <button onclick="showAddForm()">Add Expense</button>
    </div>


    <table border="1">
        <thead>
            <tr>
                <th>Name</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $key => $expense): ?>
                <tr>
                    <td><?php echo $expense['name']; ?></td>
                    <td>₹<?php echo number_format($expense['amount'], 2); ?></td>
                    <td>
                        <button
                            onclick="showUpdateForm('<?php echo $expense['key']; ?>', '<?php echo $expense['name']; ?>', '<?php echo $expense['amount']; ?>')">Update</button>
                        <a href="delete_expense.php?id=<?php echo $expense['key']; ?>" class="delete-button"
                            onclick="return confirm('Are you sure?')">
                            Delete
                        </a>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    <div id="expenseModal" style="display:none;">
        <form method="POST" id="expenseForm">
            <input type="hidden" id="expense_key" name="expense_key">
            <div>
                <label for="expense_name">Expense Name:</label>
                <input type="text" id="expense_name" name="expense_name" required>
            </div>
            <div>
                <label for="expense_amount">Expense Amount:</label>
                <input type="number" id="expense_amount" name="expense_amount" required>
            </div>
            <button type="submit" id="submitButton" name="add_expense">Add Expense</button>
        </form>
    </div>

    <script>
        function showAddForm() {
            document.getElementById('expenseModal').style.display = 'block';
            document.getElementById('expenseForm').reset();
            document.getElementById('submitButton').name = 'add_expense';
        }

        function showUpdateForm(key, name, amount) {
            document.getElementById('expenseModal').style.display = 'block';
            document.getElementById('expense_key').value = key;
            document.getElementById('expense_name').value = name;
            document.getElementById('expense_amount').value = amount;
            document.getElementById('submitButton').name = 'update_expense';
        }
    </script>
</body>

</html>