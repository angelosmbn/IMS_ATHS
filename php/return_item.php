<link rel="stylesheet" href="../css/returns.css">

<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    require '../php/connection.php';
    $requestId = $_POST['request_id'];
    $requestItemsId = $_POST['requested_items_id'];
    $departmentId = $_POST['department_id'];

    $sql_get_request = "SELECT * FROM requests r
                        JOIN users u ON r.requestor_id = u.user_id
                        JOIN departments d ON r.charged_department = d.department_id
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
    $access_level = $_SESSION['access_level'];
?>

<table>
    <tr class="request-details-tr">
        <td colspan="3" id="request-name-td">
            <b>Name:</b> <?php echo $requestor_name?>
        </td>
        <td colspan="2" id="request-name-td">
            <b>Department:</b> <?php echo $row_get_request['department_name']?>
        </td>
        <td id="request-date-td">
            <b>Request Date:</b> <?php echo $request_date?>
        </td>
    </tr>
    <?php 
        echo '<tr>';
        echo '<th>Item</th>';
        echo '<th>Description</th>';
        echo '<th>Quantity</th>';
        echo '<th>Approximate Price</th>';
        echo '<th>Date Needed</th>';
        echo '<th>Charged Account</th>';
        echo '</tr>';
    ?>


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
        $i = 0;
        while ($row_get_requested_items = $result_get_requested_items->fetch_assoc()) {
            $description = $row_get_requested_items['request_description'];
            if ($departmentId == $row_get_requested_items['requesting_department_id'] && $requestItemsId == $row_get_requested_items['requested_items_id']) {
                echo '<tr>';
                echo '<td>' . $row_get_requested_items['item_name'] . '</td>';  
                echo '<td>' . $row_get_requested_items['item_description'] . '</td>';
                echo '<td>' . $row_get_requested_items['request_quantity'] . '</td>';

                echo '<td>' . $row_get_requested_items['item_price'] . '</td>';
                echo '<td>' . $row_get_requested_items['needed_date'] . '</td>';
                echo '<td>' . $row_get_requested_items['department_name'] . '</td>';
                echo '</tr>';
                $grand_total += $row_get_requested_items['request_quantity'] * $row_get_requested_items['item_price'];
            }
        }
    ?>
    <tr>

        <td colspan="4" id="request-name-td"><b>Request Status: </b><?php echo $request_status?></td>
        <td><b>Grand Total:</b></td>
        <td><?php echo $grand_total; ?></td>
        <input type="hidden" name="request_id" value="<?php echo $requestId ?>">
        <input type="hidden" name="requested_items_id" value="<?php echo $requestItemsId ?>">
    </tr>

</table>

<?php 
    $sql_check_return_status = "SELECT * FROM requested_items WHERE requested_items_id = $requestItemsId";
    $result_check_return_status = $conn->query($sql_check_return_status);
    $row_get_return_status = $result_check_return_status->fetch_assoc();
    if ($row_get_return_status['return_status'] != 'returned') {
        
    
        echo '<div class="submit-buttons">';
        if (($request_status == 'confirmation' || $request_status == 'completed') && $row_get_request['requestor_id'] == $_SESSION['user_id'] && $row_get_return_status['return_status'] != 'marked') {
            
            echo '<button type="submit" name="mark-button" id="mark-button">Mark as Returned</button>';
            
        }
        if ($access_level == 'inventory manager') {
            
            echo '<button type="submit" name="return-button" id="return-button">Item Returned</button>';
            
        }
        echo '</div>';
    }

?>