<link rel="stylesheet" href="../css/inbox.css">
<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    require '../php/connection.php';
    $requestId = $_POST['request_id'];

    $sql_get_request = "SELECT * FROM requests r
                        JOIN users u ON r.requestor_id = u.user_id
                        WHERE r.request_id = " . $requestId;
    $result_get_request = $conn->query($sql_get_request);
    $row_get_request = $result_get_request->fetch_assoc();

    $requestor_name = $row_get_request['last_name'] . ', ' . $row_get_request['first_name'];

    // Check if middle initial exists and is not empty
    if (!empty($row_get_request['middle_name'])) {
        $requestor_name .= ' ' . strtoupper(substr($row_get_request['middle_name'], 0, 1)) . '.';
    }

    $request_date = date("Y-m-d", strtotime($row_get_request['requested_date']));
    $request_status = $row_get_request['request_status'];
?>

<table>
    <tr class="request-details-tr">
        <td colspan="3" id="request-name-td">
            <b>Name:</b> <?php echo $requestor_name?>
        </td>
        <td colspan="4" id="request-date-td">
            <b>Request Date:</b> <?php echo $request_date?>
        </td>
    </tr>
    <tr>
        <th colspan="2">Item</th>
        <th>Description</th>
        <th>Quantity</th>
        <th>Approximate Price</th>
        <th>Date Needed</th>
        <th>Charged Account</th>
    </tr>

    <?php 
        $sql_get_requested_items = "SELECT * FROM requests r 
        JOIN requested_items ri ON r.request_id = ri.request_id_fk
        JOIN items i ON ri.item_id_fk = i.item_id
        JOIN departments d ON ri.requesting_department_id = d.department_id
        WHERE r.request_id = $requestId
        ORDER BY CASE WHEN i.borrowable = 'yes' THEN 1 ELSE 0 END, i.borrowable";

        $result_get_requested_items = $conn->query($sql_get_requested_items);

        $grand_total = 0;
        $borrowableDisplayed = false; // Track whether borrowable items have been displayed
        
        while ($row_get_requested_items = $result_get_requested_items->fetch_assoc()) {
            $description = $row_get_requested_items['request_description'];
            if ($row_get_requested_items['borrowable'] == 'yes' && !$borrowableDisplayed) {
                echo '<tr>';
                echo '<td colspan="7"><b>Borrowable Items</b></td>';
                echo '</tr>';
                $borrowableDisplayed = true; // Set to true once borrowable items are displayed
            }
            echo '<tr>';
            /*if ($borrowableDisplayed && $row_get_requested_items['return_status'] == 'no') {
                echo '<td style="background-color: red;">' . $row_get_requested_items['item_name'] . '</td>';
            } else if ($borrowableDisplayed && $row_get_requested_items['return_status'] == 'marked') {
                echo '<td style="background-color: yellow;">' . $row_get_requested_items['item_name'] . '</td>';
            } else if ($borrowableDisplayed && $row_get_requested_items['return_status'] == 'returned') {
                echo '<td style="background-color: green;">' . $row_get_requested_items['item_name'] . '</td>';
            }else {
                echo '<td>' . $row_get_requested_items['item_name'] . '</td>';
            }*/
            if (!$borrowableDisplayed ) {
                echo '<td colspan="2">' . $row_get_requested_items['item_name'] . '</td>';
            } else {
                if ($row_get_requested_items['return_status'] == 'no') {
                    echo '<td id="return-status" style="background-color: red;"></td>';
                } else if ($row_get_requested_items['return_status'] == 'marked') {
                    echo '<td id="return-status" style="background-color: yellow;"></td>';
                } else if ($row_get_requested_items['return_status'] == 'returned') {
                    echo '<td id="return-status" style="background-color: green;"></td>';
                } else {
                    echo '<td id="return-status"></td>';
                    
                }
                echo '<td>' . $row_get_requested_items['item_name'] . '</td>';
            }
            
            //echo '<td>' . $row_get_requested_items['item_name'] . '</td>';
            echo '<td>' . $row_get_requested_items['item_description'] . '</td>';
            echo '<td>' . $row_get_requested_items['request_quantity'] . '</td>';
            echo '<td>' . $row_get_requested_items['item_price'] . '</td>';
            echo '<td>' . $row_get_requested_items['needed_date'] . '</td>';
            echo '<td>' . $row_get_requested_items['department_name'] . '</td>';
            echo '</tr>';
            $grand_total += $row_get_requested_items['request_quantity'] * $row_get_requested_items['item_price'];
        }
    ?>
    <tr>
        <td colspan="5" id="request-name-td"><b>Request Status: </b><?php echo $request_status?></td>
        <td><b>Grand Total:</b></td>
        <td><?php echo $grand_total; ?></td>
    </tr>

<table>

<table id="request-inputs">
    <tr>
        <td><b>Description</b></td>
        <td>
            <b>Coordinator Comment</b><br>
            <b>
                <?php
                    if ($row_get_request['coordinator_approval'] == "APPROVED") {
                        echo '<span style="color: green;">APPROVED</span>';
                    } else if ($row_get_request['coordinator_approval'] == "REJECTED") {
                        echo '<span style="color: red;">REJECTED</span>';
                    } else {
                        echo $row_get_request['coordinator_approval'];
                    }
                ?>
            </b>
        </td>
        <td>
            <b>Finance Comment</b><br>
            <b>
                <?php
                    if ($row_get_request['finance_approval'] == "APPROVED") {
                        echo '<span style="color: green;">APPROVED</span>';
                    } else if ($row_get_request['finance_approval'] == "REJECTED") {
                        echo '<span style="color: red;">REJECTED</span>';
                    } else {
                        echo $row_get_request['finance_approval'];
                    }
                ?>
            </b>
        </td>
    </tr>

    <tr>
        <td><textarea name="description" id="description" cols="30" rows="10" placeholder="Request Description." readonly><?php echo $row_get_request['request_description']; ?></textarea></td>
        <td><textarea name="coordinator_comment" id="coordinator_comment" cols="30" rows="10" placeholder="Coordinator can comment here." readonly><?php echo $row_get_request['coordinator_comment']; ?></textarea></td>
        <td><textarea name="finance_comment" id="finance_comment" cols="30" rows="10" placeholder="Finance Officer can comment here." readonly><?php echo $row_get_request['finance_comment']; ?></textarea></td>
    </tr>

</table>

