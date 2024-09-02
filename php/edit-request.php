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

    $requestor_id = $row_get_request['user_id'];
    $request_date = date("Y-m-d", strtotime($row_get_request['requested_date']));
    $request_status = $row_get_request['request_status'];
    $access_level = $_SESSION['access_level'];
?>

<table>
    <tr class="request-details-tr">
        <td colspan="3" id="request-name-td">
            <b>Name:</b> <?php echo $requestor_name?>
        </td>
        <td colspan="3" id="request-date-td">
            <b>Request Date:</b> <?php echo $request_date?>
        </td>
    </tr>
    <?php 
        if ($access_level == 'finance officer' && $request_status != 'confirmation') {
            echo '<tr>';
            echo '<th rowspan="2">Item</th>';
            echo '<th rowspan="2">Description</th>';
            echo '<th rowspan="2">Quantity</th>';
            echo '<th colspan="2">Inventory</th>';
            echo '<th rowspan="2">Approximate Price</th>';
            echo '<th rowspan="2">Date Needed</th>';
            echo '<th rowspan="2">Charged Account</th>';
            echo '</tr>';
        } else{
            echo '<tr>';
            echo '<th>Item</th>';
            echo '<th>Description</th>';
            echo '<th>Quantity</th>';
            echo '<th>Approximate Price</th>';
            echo '<th>Date Needed</th>';
            echo '<th>Charged Account</th>';
            echo '</tr>';
        }
        if ($access_level == 'finance officer' && $request_status != 'confirmation') {
            echo '<tr>';
            echo '<th>General</th>';
            echo '<th>Department</th>';
            echo '</tr>';
        }
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
            if ($row_get_requested_items['borrowable'] == 'yes' && !$borrowableDisplayed) {
                echo '<tr>';
                $colspan = ($access_level == 'finance officer' && $request_status != 'confirmation') ? 8 : 6;
                echo '<td colspan="'.$colspan.'"><b>Borrowable Items</b></td>';
                echo '</tr>';
                $borrowableDisplayed = true; // Set to true once borrowable items are displayed
            }
            echo '<tr>';
            echo '<td>' . $row_get_requested_items['item_name'] . '</td>';
            echo '<td>' . $row_get_requested_items['item_description'] . '</td>';
            echo '<td>' . $row_get_requested_items['request_quantity'] . '</td>';

            if ($access_level == 'finance officer' && $request_status != 'confirmation') {
                

                if (isset($_POST['department_ids'])) {
                    $departmentIds = $_POST['department_ids'];
                    $sql_get_stocks = "SELECT * FROM items i
                                    JOIN accumulable a ON i.item_id = a.item_id_fk AND department_id_fk = " . $departmentIds[$i] . "
                                    WHERE item_id = " . $row_get_requested_items['item_id_fk'];
                    $result_get_stocks = $conn->query($sql_get_stocks);
                    $row_get_stocks = $result_get_stocks->fetch_assoc();
                    echo '<td>' . $row_get_stocks['item_stocks'] . '</td>';
                    echo '<td id="accumulable-stocks">' . $row_get_stocks['accumulable_quantity'] - $row_get_stocks['consumed'] . '</td>';
                    $i++;
                } else {
                    $sql_get_stocks = "SELECT * FROM items i
                    JOIN accumulable a ON i.item_id = a.item_id_fk AND department_id_fk = " . $row_get_requested_items['requesting_department_id'] . "
                    WHERE item_id = " . $row_get_requested_items['item_id_fk'];
                    $result_get_stocks = $conn->query($sql_get_stocks);
                    $row_get_stocks = $result_get_stocks->fetch_assoc();
                    echo '<td>' . $row_get_stocks['item_stocks'] . '</td>';
                    echo '<td id="accumulable-stocks">' . $row_get_stocks['accumulable_quantity'] - $row_get_stocks['consumed'] . '</td>';
                }
            }

            echo '<td>' . $row_get_requested_items['item_price'] . '</td>';
            echo '<td>' . $row_get_requested_items['needed_date'] . '</td>';
            //echo '<td>' . $row_get_requested_items['department_name'] . '</td>';

            if ($access_level == 'finance officer' && $request_status != 'confirmation') {
                ?>
                <td>
                    <select name="department_charged[]" class="department_charged">
                        <?php 
                            $requested_items_id = $row_get_requested_items['requested_items_id'];
                            $item_id_fk = $row_get_requested_items['item_id_fk'];
                            $selected = $row_get_requested_items['requesting_department_id'];
                            $itemID = $row_get_requested_items['item_id_fk'];
                            if (isset($_POST['department_ids'])) {
                                $selected = $_POST['department_ids'][$i - 1];
                            }
                            $request_quantity = $row_get_requested_items['request_quantity'];
                            /*$sql_get_departments = "SELECT * FROM accumulable a
                                                    JOIN departments d ON d.department_id = a.department_id_fk
                                                    JOIN items i ON i.item_id = a.item_id_fk
                                                    WHERE (a.accumulable_quantity - a.consumed >= $request_quantity
                                                    OR d.department_id = 0) 
                                                    AND a.item_id_fk = $itemID
                                                    GROUP BY d.department_id
                                                    ORDER BY a.id ASC"; // Group by department_id to ensure uniqueness

                            $result_get_departments = $conn->query($sql_get_departments);
                            while ($row_get_departments = $result_get_departments->fetch_assoc()) {
                                $department_id = $row_get_departments['department_id'];
                                $department_name = $row_get_departments['department_name'];
                                $selected_attr = ($department_id == $selected) ? 'selected' : '';

                                $sql = "SELECT * FROM accumulable WHERE department_id_fk = $departmentIds[$i] ";
                                
                                echo '<option value="' . $department_id . '" ' . $selected_attr . '>' . $department_name . '</option>';  
                            }  */
                            $sql_get_departments = "SELECT * FROM departments ORDER BY (department_id != 0), department_name";
                            $result_get_departments = $conn->query($sql_get_departments);
                            while ($row_get_departments = $result_get_departments->fetch_assoc()) {
                                $department_id = $row_get_departments['department_id'];
                                $department_name = $row_get_departments['department_name'];
                                $selected_attr = ($department_id == $selected) ? 'selected' : '';
                                echo '<option value="' . $department_id . '" ' . $selected_attr . '>' . $department_name . '</option>';
                            }
                          
                        ?>
                    </select>
                    
                    <input type="hidden" name="requested_items_id[]" value="<?php echo $requested_items_id; ?>">
                    <input type="hidden" name="items_id_fk[]" value="<?php echo $item_id_fk; ?>">
                </td>
                <?php
            } else {
                echo '<td>' . $row_get_requested_items['department_name'] . '</td>';
            }


            echo '</tr>';
            $grand_total += $row_get_requested_items['request_quantity'] * $row_get_requested_items['item_price'];
        }
    ?>
    <tr>
        <?php 
            $colspan = ($access_level == 'finance officer' && $request_status != 'confirmation') ? 6 : 4;
        ?>
        <td colspan="<?php echo $colspan ?>" id="request-name-td"><b>Request Status: </b><?php echo $request_status?></td>
        <td><b>Grand Total:</b></td>
        <td><?php echo $grand_total; ?></td>
    </tr>

<table>

<table id="request-inputs">
    <tr>
        <td><b>Description</b></td>
        <?php 
            if ($request_status == 'confirmation' || $request_status == 'releasing') {
                echo '<td>
                    <b>
                        Coordinator Comment<br>';
                if ($row_get_request['coordinator_approval'] == "APPROVED") {
                    echo '<span style="color: green;">APPROVED</span>';
                } else if ($row_get_request['coordinator_approval'] == "REJECTED") {
                    echo '<span style="color: red;">REJECTED</span>';
                }
                echo '</b></td>';
                echo '<td><b>Finance Comment<br>';
                    if ($row_get_request['finance_approval'] == "APPROVED") {
                        echo '<span style="color: green;">APPROVED</span>';
                    } else if ($row_get_request['finance_approval'] == "REJECTED") {
                        echo '<span style="color: red;">REJECTED</span>';
                    }
                echo '</b></td>';
            } else {
                if ($access_level == 'coordinator') {
                    echo '<td><b>Coordinator Comment</b></td>';
                } else if ($access_level == 'finance officer') {
                    echo '<td><b>Coordinator Comment<br>';
                    if ($row_get_request['coordinator_approval'] == "APPROVED") {
                        echo '<span style="color: green;">APPROVED</span>';
                    } else if ($row_get_request['coordinator_approval'] == "REJECTED") {
                        echo '<span style="color: red;">REJECTED</span>';
                    }
                    echo '</b></td>';
                    echo '<td><b>Finance Comment</b></td>';
                }
            }
                
        ?>
    </tr>
    <tr>
        <td><textarea name="description" id="description" cols="30" rows="10" placeholder="Request Description." readonly><?php echo $row_get_request['request_description']; ?></textarea></td>
        <?php 
            $access_level = $_SESSION['access_level'];
            if ($request_status == 'confirmation' || $request_status == 'releasing') {
                echo '<td><textarea name="coordinator_comment" id="coordinator_comment" cols="30" rows="10" placeholder="Coordinator can comment here." readonly>' . $row_get_request['coordinator_comment'] . '</textarea></td>';
                echo '<td><textarea name="finance_comment" id="finance_comment" cols="30" rows="10" placeholder="Finance Officer can comment here." readonly>' . $row_get_request['finance_comment'] . '</textarea></td>';
            } else {

                if ($access_level == 'coordinator') {
                    echo '<td><textarea name="coordinator_comment" id="coordinator_comment" cols="30" rows="10" placeholder="Coordinator can comment here." required>' . $row_get_request['coordinator_comment'] . '</textarea></td>';
                } else if ($access_level == 'finance officer') {
                    echo '<td><textarea name="coordinator_comment" id="coordinator_comment" cols="30" rows="10" placeholder="Coordinator can comment here." readonly>' . $row_get_request['coordinator_comment'] . '</textarea></td>';
                    echo '<td><textarea name="finance_comment" id="finance_comment" cols="30" rows="10" placeholder="Finance Officer can comment here." required>' . $row_get_request['finance_comment'] . '</textarea></td>';
                } else {
                    echo '<td><textarea name="coordinator_comment" id="coordinator_comment" cols="30" rows="10" placeholder="Coordinator can comment here." readonly>' . $row_get_request['coordinator_comment'] . '</textarea></td>';
                    echo '<td><textarea name="finance_comment" id="finance_comment" cols="30" rows="10" placeholder="Finance Officer can comment here." readonly>' . $row_get_request['finance_comment'] . '</textarea></td>';
                }

            }
        ?>
        <input type="hidden" name="request_id" id="request_id" value="<?php echo $requestId ?>">
        <input type="hidden" name="request_status" id="request_status" value="<?php echo $request_status ?>">
    </tr>

</table>

<?php 
    if ($request_status == 'releasing') {
        echo '<div class="submit-buttons">';
        echo '<button type="submit" name="release-button" id="release-button">Release</button>';
        echo '</div>';
    } else if ($request_status == 'confirmation') {
        echo '<div class="submit-buttons">';
        echo '<button type="submit" name="receive-button" id="receive-button">Received</button>';
        echo '</div>';
    }
    else {
        echo '<div class="submit-buttons">';
        echo '<button type="submit" name="reject-button" id="reject-button">Reject</button>';
        echo '<button type="submit" name="approve-button" id="approve-button">Approve</button>';
        echo '<button type="button" onclick="resetEditRequest(' . $requestId . ')" name="reset-button" id="cancel-button">Reset</button>';
        echo '</div>';
    }
?>