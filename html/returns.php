<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != "") {

    } else {
        echo "<script>alert('Please login first!')</script>";
        echo "<script>window.location.href='login.php';</script>";
    }
    require '../php/connection.php';
    require 'navigation-bar.php';
    require '../php/add-department.php';

    if (isset($_POST['return-button'])) {
        $request_id = $_POST['request_id'];
        $requested_items_id = $_POST['requested_items_id'];
        $returned_date = date('Y-m-d H:i:s');

        $sql_return_item = "UPDATE requested_items SET return_status = 'returned', returned_date = '$returned_date' WHERE request_id_fk = '$request_id' AND requested_items_id = '$requested_items_id'";
        if ($conn->query($sql_return_item) === TRUE) {
            $sql_get_request_items = "SELECT ri.*, i.*, d.*, r.*
                                    FROM requested_items ri
                                    JOIN items i ON ri.item_id_fk = i.item_id
                                    JOIN departments d ON ri.requesting_department_id = d.department_id
                                    JOIN requests r ON ri.request_id_fk = r.request_id
                                    WHERE ri.request_id_fk = $request_id AND ri.requested_items_id = $requested_items_id";
            $result_get_request_items = $conn->query($sql_get_request_items);
            $row_get_request_items = $result_get_request_items->fetch_assoc();
            $sql_get_stocks = "SELECT * FROM items WHERE item_id = '{$row_get_request_items['item_id_fk']}'";
            $result_get_stocks = $conn->query($sql_get_stocks);
            $row_get_stocks = $result_get_stocks->fetch_assoc();
            $release_date = date('Y-m-d H:i:s');
            $ending_inventory_general = $row_get_stocks['item_stocks'] + $row_get_request_items['request_quantity'];
            $school_year = $row_get_request_items['school_year'];
            
            $sql_insert_stock_monitoring = "INSERT INTO stock_monitoring (request_id, item_id, requesting_department_id, beginning_inventory_general, item_cost, requested_quantity_general, requested_date, release_date, ending_inventory_general, school_year, purpose) VALUES 
                                ('$request_id', '{$row_get_request_items['item_id_fk']}', '{$row_get_request_items['charged_department']}', '{$row_get_stocks['item_stocks']}', '{$row_get_request_items['item_price']}', '{$row_get_request_items['request_quantity']}', '{$row_get_request_items['requested_date']}', '$release_date', '$ending_inventory_general', '$school_year', 'Returned')";
            echo "<script>alert('". $row_get_request_items['item_id_fk'] ."')</script>";
            if ($conn->query($sql_insert_stock_monitoring) === TRUE) {
                $sql_return_stocks = "UPDATE items i
                            JOIN requested_items ri ON i.item_id = ri.item_id_fk
                            SET i.item_stocks = i.item_stocks + ri.request_quantity
                            WHERE ri.request_id_fk = '$request_id' AND ri.requested_items_id = '$requested_items_id'";
                if ($conn->query($sql_return_stocks) === TRUE) {
                    echo "<script>alert('Item successfully returned1!')</script>";
                    echo "<script>window.location.href='returns.php';</script>";
                } else {
                    echo "<script>alert('Error returning item!')</script>";
                    echo "<script>window.location.href='returns.php';</script>";
                }
            } else{
                echo "<script>alert('Error returning item!')</script>";
                echo "<script>window.location.href='returns.php';</script>";
            }
        } else{
            echo "<script>alert('Item successfully returned!')</script>";
            echo "<script>window.location.href='returns.php';</script>";
        }
    }

    if (isset($_POST['mark-button'])) {
        $request_id = $_POST['request_id'];
        $requested_items_id = $_POST['requested_items_id'];

        $sql_return_item = "UPDATE requested_items SET return_status = 'marked' WHERE request_id_fk = '$request_id' AND requested_items_id = '$requested_items_id'";
        if ($conn->query($sql_return_item) === TRUE) {
            echo "<script>alert('Item successfully marked!')</script>";
            echo "<script>window.location.href='returns.php';</script>";
        } else {
            echo "<script>alert('Error marking item!')</script>";
            echo "<script>window.location.href='returns.php';</script>";
        }  
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/returns.css">
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
            <table id="returns-table">
                <tr class="sticky-row">
                    <th></th>
                    <th>Requested By</th>
                    <th>Item</th>
                    <th>Requested On</th>
                    <th>Released On</th>
                    <th>Returned On</th>
                    <th>Department Charged</th>
                    <th>Actions</th>
                </tr>
                <?php 
                    require '../php/view-borrowed.php';
                ?>
            </table>
        </div>

        <div class="floating-editRequest-container" id="floating-editRequest-container">
            <span class="close-icon" onclick="hideFloatingContainerEditRequest()">&#10006;</span>
            <h2 class="form-title">Request Details</h2>
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

    function showFloatingContainerEditRequest(requestId, requestedItemsId) {
        $(document).ready(function() {
            document.getElementById('floating-editRequest-container').style.display = 'block';
            console.log(requestId);
            console.log(requestedItemsId);
            if (requestId && requestedItemsId) {
                $('#request-details').load('../php/return_item.php', {
                    request_id: requestId,
                    requested_items_id: requestedItemsId
                });
            }
        });
    }

    changeNavBarTitle('Borrowed Items');

    document.getElementById('search').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#returns-table tr:not(.sticky-row):not(.lend-item-row)');

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