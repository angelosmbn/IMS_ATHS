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
    require '../php/email.php';


    if (isset($_POST['approve-button'])) {
        $request_id = $_POST['request_id'];
        $request_status = $_POST['request_status'];


        if ($request_status == 'coordinator approval') {
            $coordinator_comment = $_POST['coordinator_comment'];
            $sql_update_request = "UPDATE requests SET request_status = 'finance approval', coordinator_approval = 'APPROVED', coordinator_comment = '$coordinator_comment' WHERE request_id = $request_id";
        } 
        else if ($request_status == 'finance approval') {
            $finance_comment = $_POST['finance_comment'];

            $department_charged = $_POST['department_charged'];
            $requested_items_ids = $_POST['requested_items_id'];
            $item_id_fks = $_POST['items_id_fk'];
            for ($i = 0; $i < count($department_charged); $i++) {
                $sql_update_request = "UPDATE requested_items SET requesting_department_id = '{$department_charged[$i]}' WHERE request_id_fk = $request_id AND item_id_fk = '{$item_id_fks[$i]}' AND requested_items_id = '{$requested_items_ids[$i]}'";
                $conn->query($sql_update_request);
            }

            $sql_update_request = "UPDATE requests SET request_status = 'releasing', finance_approval = 'APPROVED', finance_comment = '$finance_comment' WHERE request_id = $request_id";
        } 
        else if ($request_status == 'releasing') {
            $sql_update_request = "UPDATE requests SET request_status = 'confirmation' WHERE request_id = $request_id";
        }

        if ($conn->query($sql_update_request) === TRUE) {
            echo "<script>alert('Request successfully approved!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } else {
            echo "<script>alert('Error approving request!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        }
        
        
    }

    if (isset($_POST['reject-button'])) {
        $request_id = $_POST['request_id'];
        $request_status = $_POST['request_status'];

        if ($request_status == 'coordinator approval') {
            $coordinator_comment = $_POST['coordinator_comment'];
            $sql_update_request = "UPDATE requests SET request_status = 'rejected', coordinator_approval = 'REJECTED', coordinator_comment = '$coordinator_comment' WHERE request_id = $request_id";
        } else if ($request_status == 'finance approval') {
            $finance_comment = $_POST['finance_comment'];
            $sql_update_request = "UPDATE requests SET request_status = 'rejected', finance_approval = 'REJECTED', finance_comment = '$finance_comment' WHERE request_id = $request_id";
        }

        if ($conn->query($sql_update_request) === TRUE) {
            echo "<script>alert('Request successfully rejected!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } else {
            echo "<script>alert('Error rejecting request!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        }
    }

    if (isset($_POST['release-button'])) {
        $request_id = $_POST['request_id'];
        $request_status = $_POST['request_status'];
        $release_date = date('Y-m-d H:i:s');
        $sql_update_request = "UPDATE requests SET request_status = 'confirmation', released_date = '$release_date' WHERE request_id = $request_id";

        if ($conn->query($sql_update_request) === TRUE) {
            $sql_get_request_items = "SELECT ri.*, i.*, d.*, r.*
            FROM requested_items ri
            JOIN items i ON ri.item_id_fk = i.item_id
            JOIN departments d ON ri.requesting_department_id = d.department_id
            JOIN requests r ON ri.request_id_fk = r.request_id
            WHERE ri.request_id_fk = $request_id";

            //DO NOT CHANGE THE ORDER.

            $result_get_request_items = $conn->query($sql_get_request_items);
            $prevItemId = ""; 
            $prevDepartmentId = "";
            while ($row_get_request_items = $result_get_request_items->fetch_assoc()) {
                $requestor_id = $row_get_request_items['requestor_id'];
                $school_year = $row_get_request_items['school_year'];
                $item_stocks = $row_get_request_items['item_stocks'];

                

                if ($row_get_request_items['requesting_department_id'] != 0) {
                    $sql_update_department_stocks = "UPDATE accumulable SET consumed = consumed + {$row_get_request_items['request_quantity']} WHERE item_id_fk = '{$row_get_request_items['item_id_fk']}' AND department_id_fk = '{$row_get_request_items['requesting_department_id']}'";
                    $conn->query($sql_update_department_stocks);
                }

                $sql_get_stocks = "SELECT * FROM items WHERE item_id = '{$row_get_request_items['item_id_fk']}'";
                $result_get_stocks = $conn->query($sql_get_stocks);
                $row_get_stocks = $result_get_stocks->fetch_assoc();

                if ($prevItemId == $row_get_request_items['item_id_fk'] && $prevDepartmentId == $row_get_request_items['requesting_department_id']) {
                    $sql_update_stock_monitoring = "UPDATE stock_monitoring SET requested_quantity_general = requested_quantity_general + {$row_get_request_items['request_quantity']}, ending_inventory_general = ending_inventory_general - {$row_get_request_items['request_quantity']} WHERE request_id = $request_id AND item_id = '{$row_get_request_items['item_id_fk']}' AND requesting_department_id = '{$row_get_request_items['requesting_department_id']}'";
                    $conn->query($sql_update_stock_monitoring);
                }
                else {
                    $ending_inventory_general = $row_get_stocks['item_stocks'] - $row_get_request_items['request_quantity'];
                    $sql_insert_stock_monitoring = "INSERT INTO stock_monitoring (request_id, item_id, requesting_department_id, beginning_inventory_general, item_cost, requested_quantity_general, requested_date, release_date, ending_inventory_general, school_year, charged_department_id) VALUES 
                                        ('$request_id', '{$row_get_request_items['item_id_fk']}', '{$row_get_request_items['charged_department']}', '{$row_get_stocks['item_stocks']}', '{$row_get_request_items['item_price']}', '{$row_get_request_items['request_quantity']}', '{$row_get_request_items['requested_date']}', '$release_date', '$ending_inventory_general', '$school_year', '{$row_get_request_items['requesting_department_id']}')";
                    $conn->query($sql_insert_stock_monitoring);
                }
                $sql_update_item_stocks = "UPDATE items SET item_stocks = item_stocks - {$row_get_request_items['request_quantity']} WHERE item_id = '{$row_get_request_items['item_id_fk']}'";
                $conn->query($sql_update_item_stocks);

                $prevItemId = $row_get_request_items['item_id_fk'];
                $prevDepartmentId = $row_get_request_items['requesting_department_id'];
            }
            $sql_get_requestor_email = "SELECT * FROM users WHERE user_id = $requestor_id";
            $result_get_requestor_email = $conn->query($sql_get_requestor_email);
            $row_get_requestor_email = $result_get_requestor_email->fetch_assoc();
            $recipientEmail = $row_get_requestor_email['email'];
            sendEmail($recipientEmail, $request_id, $conn);
            echo "<script>alert('Items successfully released!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } else {
            echo "<script>alert('Error releasing items!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        }
    }

    function updateDepartmentStocks($item_id, $department_id, $consumed, $conn) {
        $sql_update_department_stocks = "UPDATE accumulable SET consumed = consumed + $consumed WHERE item_id_fk = $item_id AND department_id_fk = $department_id";
        $conn->query($sql_update_department_stocks);
    }





    if (isset($_POST['receive-button'])) {
        $request_id = $_POST['request_id'];
        $request_status = $_POST['request_status'];
        $receive_date = date('Y-m-d H:i:s');
        $sql_update_request = "UPDATE requests SET request_status = 'completed', requestor_approval = 'APPROVED', received_date = '$receive_date' WHERE request_id = $request_id";

        if ($conn->query($sql_update_request) === TRUE) {
            echo "<script>alert('Items successfully received!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } else {
            echo "<script>alert('Error receiving items!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        }
    }


    function updateStocks($item_id, $quantity, $conn) {
        $sql_update_stocks = "UPDATE items SET item_stocks = item_stocks - $quantity WHERE item_id = $item_id";
        $conn->query($sql_update_stocks);
    }












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
            <div>
                <?php 
                    if($_SESSION['access_level'] == 'finance officer') {
                        echo "<button onclick=\"showFloatingContainerAddDepartment()\">+ Add Department</button>";
                    }
                ?>
            </div>
        </div>
        <div class="inbox-table">
            <table id="inbox-table-for-search">
                <tr class="sticky-row">
                    <th>Requested By</th>
                    <th>Requesting Department</th>
                    <th>Requested On</th>
                    <th>Needed On</th>
                    <th>Request Status</th>
                    <th>Actions</th>
                </tr>
                <?php 
                    require '../php/get_inbox.php';
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
                $('#request-details').load('../php/edit-request.php', {
                    request_id: requestId
                });
            }
        });
    }

    function resetEditRequest(requestId) {
        $(document).ready(function() {
            console.log(requestId);
            if (requestId) {
                $('#request-details').load('../php/edit-request.php', {
                    request_id: requestId
                });
            }
        });
    }



    document.addEventListener('change', function(event) {
        // Check if the changed element is a select element with the class 'department_charged'
        if (event.target.classList.contains('department_charged')) {
            var selectedDepartments = [];
            var requestId = $('#request_id').val();

            // Iterate over each selected option and add its value to the array
            $('select[name="department_charged[]"] option:selected').each(function() {
                selectedDepartments.push($(this).val());
            });

            console.log(selectedDepartments);

            if (selectedDepartments) {
                $('#request-details').load('../php/edit-request.php', {
                    request_id: requestId,
                    department_ids: selectedDepartments
                });
            }



            // You can perform any actions here based on the selected value
        }
    })

    changeNavBarTitle('Inbox');


    document.getElementById('search').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#inbox-table-for-search tr:not(.sticky-row):not(.lend-item-row)');

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