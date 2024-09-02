<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != "") {
        if ($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
            // Allow access
        } else {
            echo "<script>alert('You do not have permission to access this page!')</script>";
            echo "<script>window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Please login first!')</script>";
        echo "<script>window.location.href='login.php';</script>";
    }
    require '../php/connection.php';
    require_once '../php/config.php';
    require 'navigation-bar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inbox.css">
    <title>Document</title>
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
</head>
<body>
    <div class="inbox-container" id="inbox-container">
        <div class="settings-container">
            <input type="text" name="search" id="search">
        </div>
        <div class="inbox-table">
            <table id="history-table-for-search">
                <tr class="sticky-row">
                    <th>Requested By</th>
                    <th>Requested On</th>
                    <th>Received On</th>
                    <th>Department Charged</th>
                    <th>Request Status</th>
                    <th>School Year</th>
                    <th>Actions</th>
                </tr>
                <?php 
                    require '../php/get-requisitions.php';
                ?>
            </table>
        </div>

        <div class="floating-editRequest-container" id="floating-editRequest-container">
            <div class="bar">
                <span>Request Details</span>
                <span class="close-icon" onclick="hideFloatingContainerEditRequest()">&#10006;</span>
            </div>
            <form action="" method="POST">
                <div class="request-details" id="request-details">

                </div>
            </form>
        </div>

    </div>
</body>
<script>
    function hideFloatingContainerEditRequest() {
        document.getElementById('floating-editRequest-container').style.display = 'none';
    }
    function showFloatingContainerAddDepartment() {
        document.querySelector('.floating-addCategory-container').style.display = 'block';
    }

    function showFloatingContainerEditRequest(requestId) {
        $(document).ready(function() {
            document.getElementById('floating-editRequest-container').style.display = 'block';
            console.log(requestId);
            if (requestId) {
                $('#request-details').load('../php/view-request.php', {
                    request_id: requestId
                });
            }
        });
    }

    function openPDF(requestId) {
        $(document).ready(function() {
            console.log(requestId);
            if (requestId) {
                window.open('../php/generatePDF.php?request_id=' + requestId, '_blank');

            }
        });
    }
    changeNavBarTitle('Requisitions');


    document.getElementById('search').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#history-table-for-search tr:not(.sticky-row):not(.lend-item-row)');

        rows.forEach(function(row) {
            let rowText = row.textContent.toLowerCase();
            if (rowText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

</html>