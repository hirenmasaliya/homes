<?php
include 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $timestamp = time();

    $newNotice = [
        'title' => $title,
        'message' => $message,
        'timestamp' => $timestamp
    ];

    $database->getReference('Admin/notices')->push($newNotice);
    header('Location: index.php?page=notiesboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notice'])) {
    $noticeId = $_POST['notice_id'];
    $updatedTitle = $_POST['title'];
    $updatedMessage = $_POST['message'];

    $database->getReference('Admin/notices/' . $noticeId)->update([
        'title' => $updatedTitle,
        'message' => $updatedMessage
    ]);
    header('Location: index.php?page=notiesboard');
    exit;
}

if (isset($_GET['delete_notice'])) {
    $noticeId = $_GET['delete_notice'];
    if (!$noticeId) {
        die("Notice ID is not set or invalid.");
    }

    echo "Attempting to delete notice with ID: $noticeId";

    $database->getReference('Admin/notices/' . $noticeId)->remove();

    header('Location: index.php?page=notiesboard');
    exit;
}



$noticesRef = $database->getReference('Admin/notices');
$notices = $noticesRef->getValue();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Board</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
        }

        h1 {
            text-align: center;
        }

        .notice {
            background-color: #eaf4e9;
            padding: 10px;
            margin: 10px 0;
            border-left: 5px solid #4CAF50;
        }

        .notice h3 {
            margin: 0;
        }

        .notice p {
            margin: 5px 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input,
        textarea {
            width: 96%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .action-icons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .action-icons a,
        .action-icons button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .action-icons a {
            color: #ff4d4d;
            /* Delete icon color */
        }

        .action-icons a:hover {
            background-color: rgba(255, 77, 77, 0.1);
        }

        .action-icons button {
            color: #4CAF50;
            /* Edit icon color */
        }

        .action-icons button:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .action-icons i {
            font-size: 20px;
        }

        .action-icons a:focus,
        .action-icons button:focus {
            outline: 2px solid rgba(0, 0, 0, 0.2);
            outline-offset: 2px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Notice Board</h1>

        <button id="addNoticeBtn" class="btn">Add Notice</button>

        <div id="noticeModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New Notice</h2>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="title">Notice Title:</label>
                        <input type="text" name="title" id="title" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Notice Message:</label>
                        <textarea name="message" id="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="add_notice" class="btn">Add Notice</button>
                </form>
            </div>
        </div>

        <h2>All Notices</h2>
        <?php
        if (!empty($notices)) {
            foreach ($notices as $key => $notice) {
                echo "<div class='notice'>";
                echo "<h3>" . htmlspecialchars($notice['title']) . "</h3>";
                echo "<p>" . nl2br(htmlspecialchars($notice['message'])) . "</p>";
                echo "<p><small>Posted on: " . date("Y-m-d H:i:s", $notice['timestamp']) . "</small></p>";
                echo "<div class='action-icons'>";
                echo "<a href='delete_notice.php?notice_id=" . $key . "' onclick=\"return confirm('Are you sure you want to delete this notice?');\"><i class='fas fa-trash-alt'></i></a>";
                echo "<button class='updateBtn' data-id='" . $key . "' data-title='" . htmlspecialchars($notice['title']) . "' data-message='" . htmlspecialchars($notice['message']) . "'><i class='fas fa-edit'></i></button>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No notices available.</p>";
        }
        ?>
    </div>

    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Notice</h2>
            <form action="" method="POST">
                <input type="hidden" name="notice_id" id="notice_id">
                <div class="form-group">
                    <label for="update_title">Notice Title:</label>
                    <input type="text" name="title" id="update_title" required>
                </div>
                <div class="form-group">
                    <label for="update_message">Notice Message:</label>
                    <textarea name="message" id="update_message" rows="5" required></textarea>
                </div>
                <button type="submit" name="update_notice" class="btn">Update Notice</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("noticeModal");
        var updateModal = document.getElementById("updateModal");

        var btn = document.getElementById("addNoticeBtn");

        var closeBtns = document.getElementsByClassName("close");

        btn.onclick = function () {
            modal.style.display = "block";
        }

        for (var i = 0; i < closeBtns.length; i++) {
            closeBtns[i].onclick = function () {
                modal.style.display = "none";
                updateModal.style.display = "none";
            }
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
            if (event.target == updateModal) {
                updateModal.style.display = "none";
            }
        }

        var updateButtons = document.querySelectorAll(".updateBtn");
        updateButtons.forEach(function (btn) {
            btn.onclick = function () {
                var noticeId = this.getAttribute("data-id");
                var title = this.getAttribute("data-title");
                var message = this.getAttribute("data-message");

                document.getElementById("notice_id").value = noticeId;
                document.getElementById("update_title").value = title;
                document.getElementById("update_message").value = message;

                updateModal.style.display = "block";
            }
        });
    </script>

</body>

</html>