<link rel="stylesheet" href="../css/inbox.css">

<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    require '../php/connection.php';
    $requestId = $_POST['request_id'];
    $requestGroupId = $_POST['request_group_id'];


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
    $requestingDepartment = $row_get_request['department_name'];
    $requestor_id = $row_get_request['user_id'];
    $request_date = date("Y-m-d", strtotime($row_get_request['requested_date']));
    $request_status = $row_get_request['request_status'];
    $access_level = $_SESSION['access_level'];
?>

<table>
    <tr class="request-details-tr">
        
        <?php 
            $colspan3 = 1;
            if ($access_level == 'finance officer' && $request_status != 'confirmation') {
                $colspan = 3;
                $colspan2 = 4;
            } else {
                $colspan = 3;
                $colspan2 = 2;
            }
            if ($requestGroupId  == NULL) {
                $colspan2 = 2;
                $colspan3 = 1;
            
        ?>
        <td colspan="<?php echo $colspan ?>" id="request-name-td">
            <b>Name:</b> <?php echo $requestor_name?>
        </td>
        <?php }?>
        <td colspan="<?php echo $colspan2 ?>" id="request-name-td">
            <b>Requesting Department:</b> <?php echo $requestingDepartment?>
        </td>
        <td colspan="<?php echo $colspan3 ?>" id="request-date-td">
            <b>Request Date:</b> <?php echo $request_date?>
        </td>
    </tr>
    <?php 
        if ($requestGroupId != NULL && ($access_level == 'finance officer' && $request_status != 'confirmation')) {
            echo '<tr>';
            echo '<th rowspan="2"><input type="checkbox" id="checkAll"></th>';
            echo '<th rowspan="2">Requestor</th>';
            echo '<th rowspan="2">Item</th>';
            echo '<th rowspan="2">Description</th>';
            echo '<th rowspan="2">Quantity</th>';
            echo '<th colspan="2">Inventory</th>';
            echo '<th rowspan="2">Approximate Price</th>';
            echo '<th rowspan="2">Date Needed</th>';
            echo '<th rowspan="2">Charged Account</th>';
            echo '</tr>';
        } elseif ($requestGroupId == NULL && ($access_level == 'finance officer' && $request_status != 'confirmation')) {
            echo '<tr>';
            echo '<th rowspan="2"><input type="checkbox" id="checkAll"></th>';
            echo '<th rowspan="2">Item</th>';
            echo '<th rowspan="2">Description</th>';
            echo '<th rowspan="2">Quantity</th>';
            echo '<th colspan="2">Inventory</th>';
            echo '<th rowspan="2">Approximate Price</th>';
            echo '<th rowspan="2">Date Needed</th>';
            echo '<th rowspan="2">Charged Account</th>';
            echo '</tr>';
        } elseif ($requestGroupId != NULL && ($access_level == 'coordinator' || $access_level == 'finance officer')) {
            echo '<tr>';
            echo '<th><input type="checkbox" id="checkAll"></th>';
            echo '<th>Requestor</th>';
            echo '<th>Item</th>';
            echo '<th>Description</th>';
            echo '<th>Quantity</th>';
            echo '<th>Approximate Price</th>';
            echo '<th>Date Needed</th>';
            echo '<th>Charged Account</th>';
            echo '</tr>';
        } elseif ($requestGroupId != NULL && ($access_level == 'inventory manager' && $request_status != 'confirmation')) {
            echo '<tr>';
            echo '<th>Requestor</th>';
            echo '<th>Item</th>';
            echo '<th>Description</th>';
            echo '<th>Quantity</th>';
            echo '<th>Approximate Price</th>';
            echo '<th>Date Needed</th>';
            echo '<th>Charged Account</th>';
            echo '</tr>';
        }
        elseif ($requestGroupId == NULL && ($access_level == 'inventory manager' && $request_status != 'confirmation')) {
            echo '<tr>';
            echo '<th>Item</th>';
            echo '<th>Description</th>';
            echo '<th>Quantity</th>';
            echo '<th>Approximate Price</th>';
            echo '<th>Date Needed</th>';
            echo '<th>Charged Account</th>';
            echo '</tr>';
        }
        else {
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
    if ($requestGroupId == NULL) { // for single request
        $sql_get_requested_items = "SELECT * FROM requests r 
        JOIN requested_items ri ON r.request_id = ri.request_id_fk
        JOIN items i ON ri.item_id_fk = i.item_id
        JOIN departments d ON ri.requesting_department_id = d.department_id
        WHERE r.request_id = $requestId AND ri.is_rejected = 'no'
        ORDER BY CASE WHEN i.borrowable = 'yes' THEN 1 ELSE 0 END, i.borrowable";

        $result_get_requested_items = $conn->query($sql_get_requested_items);
        $num_rows = $result_get_requested_items->num_rows;

        $grand_total = 0;
        $borrowableDisplayed = false; // Track whether borrowable items have been displayed
        
        $i = 0;
        while ($row_get_requested_items = $result_get_requested_items->fetch_assoc()) {
            $item_id_fk = $row_get_requested_items['item_id_fk'];
            $description = $row_get_requested_items['request_description'];
            if ($row_get_requested_items['borrowable'] == 'yes' && !$borrowableDisplayed) {
                echo '<tr>';
                $colspan = (($access_level == 'finance officer' && $request_status != 'confirmation') || ($access_level == 'coordinator' && $request_status != 'confirmation')) ? 9 : 7;
                echo '<td colspan="'.$colspan.'"><b>Borrowable Items</b></td>';
                echo '</tr>';
                $borrowableDisplayed = true; // Set to true once borrowable items are displayed
            }
            echo '<tr>';
            if (($access_level == 'finance officer' && $request_status != 'confirmation') || ($access_level == 'coordinator' && $request_status != 'confirmation')) {
                echo '<td><input type="checkbox" class="checkbox" name="requested_items_ids[]" value="' . $row_get_requested_items['requested_items_id'] . '" data-row-id="' . $row_get_requested_items['requested_items_id'] . '_' . $row_get_requested_items['item_id_fk'] . '_' . $row_get_requested_items['requesting_department_id'] . '"></td>';
            }
            //echo '<td><input type="checkbox" class="checkbox" name="requested_items_ids[]" value="' . $row_get_requested_items['requested_items_id'] . '" data-row-id="' . $row_get_requested_items['requested_items_id'] . '_' . $row_get_requested_items['item_id_fk'] . '_' . $row_get_requested_items['requesting_department_id'] . '"></td>';
            echo '<td>' . $row_get_requested_items['item_name'] . '</td>';
            echo '<td>' . $row_get_requested_items['item_description'] . '</td>';
            echo '<td class="default_quantity"><span id="default_quantity_input_'. $row_get_requested_items['requested_items_id'] .'">' . $row_get_requested_items['request_quantity'] . '</span>';
            echo '<input type="number" name="request_quantity[]" id="quantity_input_'. $row_get_requested_items['requested_items_id'] .'" value="' . $row_get_requested_items['request_quantity'] . '" min="1" max="'. $row_get_requested_items['item_stocks'] .' disabled"/></td>';

            if ($access_level == 'finance officer' && $request_status != 'confirmation') {

                    $sql_get_stocks = "SELECT * FROM items i
                    JOIN accumulable a ON i.item_id = a.item_id_fk AND department_id_fk = " . $row_get_requested_items['requesting_department_id'] . "
                    WHERE item_id = " . $row_get_requested_items['item_id_fk'];
                    $result_get_stocks = $conn->query($sql_get_stocks);
                    $row_get_stocks = $result_get_stocks->fetch_assoc();
                    echo '<td>' . $row_get_stocks['item_stocks'] . '</td>';
                    
                    echo '<td id="accumulable-stocks_' . $row_get_requested_items['requested_items_id'] .'">' . $row_get_stocks['accumulable_quantity'] - $row_get_stocks['consumed'] . '</td>';
            }



            echo '<td id="item-price-td-' . $row_get_requested_items['requested_items_id'] . '">
                    ' . $row_get_requested_items['item_price'] . '
                </td>';
            echo '<td>' . $row_get_requested_items['needed_date'] . '</td>';

            
            if ($access_level == 'finance officer' && $request_status != 'confirmation') {
                ?>
                <td id="select-department-td-<?php echo $row_get_requested_items['requested_items_id']; ?>" class="select-department-td">
                    
                <select name="department_charged[]" class="department_charged" 
                id="department_charged_input_<?php echo $row_get_requested_items['requested_items_id']; ?>" 
                data-row-id="<?php echo $row_get_requested_items['requested_items_id']; ?>_<?php echo $row_get_requested_items['item_id_fk']; ?>" 
                required disabled>

                        <?php
                        echo '<script>console.log("department_charged_input_'.$row_get_requested_items['requested_items_id'].'")</script>'; 
                            $requested_items_id = $row_get_requested_items['requested_items_id'];
                            $item_id_fk = $row_get_requested_items['item_id_fk'];
                            $selected = $row_get_requested_items['requesting_department_id'];
                            $itemID = $row_get_requested_items['item_id_fk'];
                            if (isset($_POST['department_ids'])) {
                                $selected = $_POST['department_ids'][$i - 1];
                            }
                            $request_quantity = $row_get_requested_items['request_quantity'];

                            $sql_get_departments = "SELECT * FROM departments ORDER BY (department_id != 0), department_name";
                            $result_get_departments = $conn->query($sql_get_departments);
                            //GENERAL NOT ALLOWED
                            while ($row_get_departments = $result_get_departments->fetch_assoc()) {
                                $department_id = $row_get_departments['department_id'];
                                $department_name = $row_get_departments['department_name'];
                                $selected_attr = ($department_id == $selected) ? 'selected' : '';
                                if ($department_id == 0) {
                                    echo '<option value="">Select Department</option>';
                                }else{
                                    echo '<option value="' . $department_id . '" ' . $selected_attr . '>' . $department_name . '</option>';
                                }
                            }
                          
                        ?>
                    </select>

                    <span id="default_department_<?php echo $row_get_requested_items['requested_items_id']; ?>">
                        <?php echo $row_get_requested_items['department_name']; ?>
                    </span>


                    <input type="hidden" name="item_id" id="item_id_<?php echo $item_id_fk; ?>"  data-row-id="<?php echo $item_id_fk; ?>"> 
                    <input type="hidden" name="requested_items_id[]" value="<?php echo $requested_items_id; ?>">
                    
                
                </td>
                <?php
            } else {
                echo '<td>' . $row_get_requested_items['department_name'] . '</td>';
            }
            echo '<input type="hidden" name="items_id_fk[]" id="item_id_fk_' . $row_get_requested_items['requested_items_id'] . '" value="' . $item_id_fk . '" disabled>';
            echo '<input type="hidden" name="requests_ids[]" value="' . $row_get_requested_items['request_id'] . '">';
            

            echo '</tr>';
            $grand_total += $row_get_requested_items['request_quantity'] * $row_get_requested_items['item_price'];
        }


    } else { // For group requests
        $sql_get_requested_items = "SELECT * FROM requests r 
        JOIN requested_items ri ON r.request_id = ri.request_id_fk
        JOIN items i ON ri.item_id_fk = i.item_id
        JOIN departments d ON ri.requesting_department_id = d.department_id
        WHERE r.request_group_id = $requestGroupId AND ri.is_rejected = 'no'
        ORDER BY CASE WHEN i.borrowable = 'yes' THEN 1 ELSE 0 END, i.borrowable";

        $result_get_requested_items = $conn->query($sql_get_requested_items);

        $grand_total = 0;
        $borrowableDisplayed = false; // Track whether borrowable items have been displayed
        
        $i = 0;
        while ($row_get_requested_items = $result_get_requested_items->fetch_assoc()) {
            //get name
            $sql_get_requestor_name = "SELECT * FROM users WHERE user_id = " . $row_get_requested_items['requestor_id'];
            $result_get_requestor_name = $conn->query($sql_get_requestor_name);
            $row_get_requestor_name = $result_get_requestor_name->fetch_assoc();
            $requestor_name = $row_get_requestor_name['last_name'] . ', ' . $row_get_requestor_name['first_name'];
            if (!empty($row_get_requestor_name['middle_name'])) {
                $requestor_name .= ' ' . strtoupper(substr($row_get_requestor_name['middle_name'], 0, 1)) . '.';
            }

            $item_id_fk = $row_get_requested_items['item_id_fk'];
            $description = $row_get_requested_items['request_description'];
            if ($row_get_requested_items['borrowable'] == 'yes' && !$borrowableDisplayed) {
                echo '<tr>';
                $colspan = (($access_level == 'finance officer' && $request_status != 'confirmation') || ($access_level == 'coordinator' && $request_status != 'confirmation')) ? 10 : 7;
                echo '<td colspan="'.$colspan.'"><b>Borrowable Items</b></td>';
                echo '</tr>';
                $borrowableDisplayed = true; // Set to true once borrowable items are displayed
            }
            echo '<tr>';
            if (($access_level == 'finance officer' && $request_status != 'confirmation') || $access_level == 'coordinator') {
                echo '<td><input type="checkbox" class="checkbox" name="requested_items_ids[]" value="' . $row_get_requested_items['requested_items_id'] . '" data-row-id="' . $row_get_requested_items['requested_items_id'] . '_' . $row_get_requested_items['item_id_fk'] . '_' . $row_get_requested_items['requesting_department_id'] . '"></td>';
            }
            //echo '<td><input type="checkbox" class="checkbox" name="requested_items_ids[]" value="' . $row_get_requested_items['requested_items_id'] . '" data-row-id="' . $row_get_requested_items['requested_items_id'] . '_' . $row_get_requested_items['item_id_fk'] . '_' . $row_get_requested_items['requesting_department_id'] . '"></td>';
            echo '<td>' . $requestor_name . '</td>';
            echo '<td>' . $row_get_requested_items['item_name'] . '</td>';
            echo '<td>' . $row_get_requested_items['item_description'] . '</td>';
            echo '<td class="default_quantity"><span id="default_quantity_input_'. $row_get_requested_items['requested_items_id'] .'">' . $row_get_requested_items['request_quantity'] . '</span>';
            echo '<input type="number" name="request_quantity[]" id="quantity_input_'. $row_get_requested_items['requested_items_id'] .'" value="' . $row_get_requested_items['request_quantity'] . '" min="1" max="'. $row_get_requested_items['item_stocks'] .' disabled"/></td>';

            if ($access_level == 'finance officer' && $request_status != 'confirmation') {

                    $sql_get_stocks = "SELECT * FROM items i
                    JOIN accumulable a ON i.item_id = a.item_id_fk AND department_id_fk = " . $row_get_requested_items['requesting_department_id'] . "
                    WHERE item_id = " . $row_get_requested_items['item_id_fk'];
                    $result_get_stocks = $conn->query($sql_get_stocks);
                    $row_get_stocks = $result_get_stocks->fetch_assoc();
                    echo '<td>' . $row_get_stocks['item_stocks'] . '</td>';
                    
                    echo '<td id="accumulable-stocks_' . $row_get_requested_items['requested_items_id'] .'">' . $row_get_stocks['accumulable_quantity'] - $row_get_stocks['consumed'] . '</td>';
            }



            echo '<td id="item-price-td-' . $row_get_requested_items['requested_items_id'] . '">
                    ' . $row_get_requested_items['item_price'] . '
                </td>';
            echo '<td>' . $row_get_requested_items['needed_date'] . '</td>';

            
            if ($access_level == 'finance officer' && $request_status != 'confirmation') {
                ?>
                <td id="select-department-td-<?php echo $row_get_requested_items['requested_items_id']; ?>" class="select-department-td">
                    
                <select name="department_charged[]" class="department_charged" 
                id="department_charged_input_<?php echo $row_get_requested_items['requested_items_id']; ?>" 
                data-row-id="<?php echo $row_get_requested_items['requested_items_id']; ?>_<?php echo $row_get_requested_items['item_id_fk']; ?>" 
                required disabled>

                        <?php
                        echo '<script>console.log("department_charged_input_'.$row_get_requested_items['requested_items_id'].'")</script>'; 
                            $requested_items_id = $row_get_requested_items['requested_items_id'];
                            $item_id_fk = $row_get_requested_items['item_id_fk'];
                            $selected = $row_get_requested_items['requesting_department_id'];
                            $itemID = $row_get_requested_items['item_id_fk'];
                            if (isset($_POST['department_ids'])) {
                                $selected = $_POST['department_ids'][$i - 1];
                            }
                            $request_quantity = $row_get_requested_items['request_quantity'];

                            $sql_get_departments = "SELECT * FROM departments ORDER BY (department_id != 0), department_name";
                            $result_get_departments = $conn->query($sql_get_departments);
                            //GENERAL NOT ALLOWED
                            while ($row_get_departments = $result_get_departments->fetch_assoc()) {
                                $department_id = $row_get_departments['department_id'];
                                $department_name = $row_get_departments['department_name'];
                                $selected_attr = ($department_id == $selected) ? 'selected' : '';
                                if ($department_id == 0) {
                                    echo '<option value="">Select Department</option>';
                                }else{
                                    echo '<option value="' . $department_id . '" ' . $selected_attr . '>' . $department_name . '</option>';
                                }
                            }
                          
                        ?>
                    </select>

                    <span id="default_department_<?php echo $row_get_requested_items['requested_items_id']; ?>">
                        <?php echo $row_get_requested_items['department_name']; ?>
                    </span>


                    <input type="hidden" name="item_id" id="item_id_<?php echo $item_id_fk; ?>"  data-row-id="<?php echo $item_id_fk; ?>"> 
                    <input type="hidden" name="requested_items_id[]" value="<?php echo $requested_items_id; ?>">
                    
                
                </td>
                <?php
            } else {
                echo '<td>' . $row_get_requested_items['department_name'] . '</td>';
            }
            echo '<input type="hidden" name="items_id_fk[]" id="item_id_fk_' . $row_get_requested_items['requested_items_id'] . '" value="' . $item_id_fk . '" disabled>';
            echo '<input type="hidden" name="requests_ids[]" value="' . $row_get_requested_items['request_id'] . '">';
            

            echo '</tr>';
            $grand_total += $row_get_requested_items['request_quantity'] * $row_get_requested_items['item_price'];
        }

    }

    ?>
    <tr>
        <?php 
            $colspan = ($access_level == 'finance officer' && $request_status != 'confirmation') ? 8 : (($access_level == 'coordinator' && $request_status != 'confirmation') ? 6 : (($access_level == 'inventory manager' && $request_status != 'confirmation') ? 5 : 4));
            $colspan = ($requestGroupId == NULL && $request_status != 'confirmation') ? $colspan - 1 : $colspan;
        ?>  
        <td colspan="<?php echo $colspan ?>" id="request-name-td"><b>Request Status: </b><?php echo $request_status?></td>
        <td><b>Grand Total:</b></td>
        <td id="grand-total-td"><?php echo $grand_total; ?></td>
    </tr>
    
<table>

<table id="request-inputs">
    <tr>
    <?php if ($requestGroupId == NULL) { ?>
        <td><b>Description</b></td>
        <?php 
        }
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
        <?php if ($requestGroupId == NULL) { ?>
        <td><textarea name="description" id="description" cols="30" rows="10" placeholder="Request Description." readonly><?php echo $row_get_request['request_description']; ?></textarea></td>
        <?php 
        }
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
        <input type="hidden" name="request_group_id" id="request_group_id" value="<?php echo $requestGroupId; ?>">
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
        echo '</div>';
    }
?>

<script>


    document.querySelectorAll('.checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                console.log('Checkbox changed');
                var rowId = this.getAttribute('data-row-id');
                var splitRowId = rowId.split('_');
                var requestedItemsId = splitRowId[0];
                var itemId = splitRowId[1];
                var departmentId = splitRowId[2];
                console.log('Row ID:', requestedItemsId);

                var departmentSelect = document.getElementById('department_charged_input_' + requestedItemsId);

                var quantityInput = document.getElementById('quantity_input_' + requestedItemsId);

                var defaultQuantity = document.getElementById('default_quantity_input_' + requestedItemsId);

                var itemIdFk = document.getElementById('item_id_fk_' + requestedItemsId);

                var defaultDepartment = document.getElementById('default_department_' + requestedItemsId);

                

                if (departmentSelect) { 
                    if (this.checked) {
                        defaultQuantity.style.display = 'none';
                        departmentSelect.style.display = 'block';
                        quantityInput.style.display = 'block';

                        departmentSelect.setAttribute('required', 'required');
                        departmentSelect.disabled = false;

                        quantityInput.removeAttribute('disabled');

                        itemIdFk.removeAttribute('disabled');

                        defaultDepartment.style.display = 'none';
                        

                    } else {
                        // Make AJAX request
                        $.ajax({
                            url: '../php/fetch-stocks.php',
                            type: 'POST',
                            data: {
                                department_id: departmentId,
                                item_id: itemId
                            },
                            dataType: 'json',
                            success: function(response) {
                                var stockElement = document.getElementById('accumulable-stocks_' + requestedItemsId);
                                var defaultAccumulable = document.getElementById('default_accumulable_' + requestedItemsId);//hgkjhgjhkghjgkjhghjkgjkhgjkhgjhkgkjhghjghjkgjhh
                                if (stockElement) {
                                    var stocks = response.stocks;//hgkjhgjhkghjgkjhghjkgjkhgjkhgjhkgkjhghjghjkgjhh
                                    //efaultAccumulable.innerHTML = stocks;//hgkjhgjhkghjgkjhghjkgjkhgjkhgjhkgkjhghjghjkgjhh
                                    stockElement.innerHTML = response.stocks;
                                } else {
                                    console.error('Element with ID accumulable-stocks_' + requestedItemsId + ' not found.');
                                }
                            },

                            error: function() {
                                console.error('Failed to fetch stock data.');
                            }
                        });
                        departmentSelect.style.display = 'none';
                        departmentSelect.removeAttribute('required');
                        departmentSelect.disabled = true;

                        document.getElementById('checkAll').checked = false;
                        var stockElement = document.getElementById('accumulable-stocks_' + requestedItemsId);
                
                        /*if (stockElement) {
                            stockElement.innerHTML = '';
                        } else {
                            console.error('Element with ID accumulable-stocks_' + requestedItemsId + ' not found.');
                        }*/

                        quantityInput.style.display = 'none';
                        defaultQuantity.style.display = 'block';

                        quantityInput.disabled = true;

                        itemIdFk.disabled = true;

                        defaultDepartment.style.display = 'block';
                        //defaultAccumulable.style.display = 'block';


                        console.log('Checkbox unchecked');

                        
                    }
                } else {
                    // for coordinator
                    if (this.checked) {
                        defaultQuantity.style.display = 'none';
                        quantityInput.style.display = 'block';

                        quantityInput.removeAttribute('disabled');
                        itemIdFk.removeAttribute('disabled');

                    } else {

                        quantityInput.style.display = 'none';
                        defaultQuantity.style.display = 'block';

                        quantityInput.disabled = true;
                        itemIdFk.disabled = true;
                        console.log('Checkbox unchecked');

                        
                    }
                }
            });
        });

    (function() {
        const checkAll = document.getElementById('checkAll');
        
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                let checkboxes = document.querySelectorAll('.checkbox');
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });
        }
    })();

    


    (function() {
        const approveButton = document.getElementById('approve-button');
        
        // Function to check if at least one checkbox is checked
        if (approveButton) {
            approveButton.addEventListener('click', function(event) {
                let checkboxes = document.querySelectorAll('.checkbox');
                
                // Check if at least one checkbox is checked
                let isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                
                // If no checkbox is checked, prevent form submission and show an alert
                if (!isChecked) {
                    event.preventDefault(); // Prevent form submission
                    alert('You must select at least one item to approve.');
                } else {
                    // If checkboxes are selected, ask for confirmation
                    let confirmApproval = confirm('Are you sure you want to approve the selected items? The others will be rejected?');
                    
                    // If the user cancels the confirmation, prevent form submission
                    if (!confirmApproval) {
                        event.preventDefault();
                    }
                }
            });
        }
    })();


    document.addEventListener('change', function(event) {
    if (event.target.classList.contains('department_charged')) {
        var requestId = $('#request_id').val();
        var requestGroupId = $('#request_group_id').val();
        // Retrieve data-row-id from the changed <select> element
        var rowIdd = event.target.getAttribute('data-row-id');
        
        // Split rowIdd into requested_items_id and item_id
        var splitRowId = rowIdd.split('_');
        var requestedItemsId = splitRowId[0];  // requested_items_id
        var itemId = splitRowId[1];  // item_id
        console.log('Department charged:', event.target.value);
        console.log('Requested Item ID:', requestedItemsId);
        console.log('Item ID:', itemId);

        var departmentId = event.target.value; // Adjust this if needed
        console.log('Department ID:', departmentId);
        // Make AJAX request
        $.ajax({
            url: '../php/fetch-stocks.php',
            type: 'POST',
            data: {
                department_id: departmentId,
                item_id: itemId
            },
            dataType: 'json',
            success: function(response) {
                var stockElement = document.getElementById('accumulable-stocks_' + requestedItemsId);
                //var defaultAccumulable = document.getElementById('default_accumulable_' + requestedItemsId);//hgkjhgjhkghjgkjhghjkgjkhgjkhgjhkgkjhghjghjkgjhh
                if (stockElement) {
                    var stocks = response.stocks;//hgkjhgjhkghjgkjhghjkgjkhgjkhgjhkgkjhghjghjkgjhh
                    //defaultAccumulable.innerHTML = stocks;//hgkjhgjhkghjgkjhghjkgjkhgjkhgjhkgkjhghjghjkgjhh
                    stockElement.innerHTML = response.stocks;
                } else {
                    console.error('Element with ID accumulable-stocks_' + requestedItemsId + ' not found.');
                }
            },

            error: function() {
                console.error('Failed to fetch stock data.');
            }
        });
        
        //defaultAccumulable.style.display = 'none';
    }
});


    $(document).on('click', '#reject-button', function() {
        // Display a confirmation dialog
        var userConfirmed = confirm('Are you sure you want to reject all selected items? This action cannot be undone.');

        // Proceed only if the user clicks "OK"
        if (userConfirmed) {
            // Iterate over each <select> element with the class 'department_charged'
            $('.department_charged').each(function() {
                if (!$(this).is(':disabled')) {
                    // Remove the 'required' attribute and disable the <select> element if it is currently enabled
                    $(this).prop('required', false).prop('disabled', true);
                }
            });
        }
    });






    function initQuantityAndCheckboxListeners() {
        // Listen for changes in the quantity inputs
        $('input[type="number"]').on('input', function() {
            // Get the current quantity and row ID
            var quantity = $(this).val();
            var rowId = $(this).attr('id').split('_')[2]; // Extract row ID from element's ID

            // Get the price from the same row
            var price = $('#item-price-td-' + rowId).text();

            // Calculate the total for this row
            var total = quantity * price;

            console.log('Total for row ' + rowId + ': ' + total);
            
            // Update totals for all checked rows
            updateTotalForCheckedRows();
        });

        // Also update totals when checkboxes are clicked
        $('.checkbox').on('change', function() {
            updateTotalForCheckedRows();
        });
    }

    // Function to update totals for checked rows
    function updateTotalForCheckedRows() {
        var grandTotal = 0;
        
        $('.checkbox:checked').each(function() {
            var rowId = $(this).data('row-id').split('_')[0]; // Extract row ID
            var quantity = $('#quantity_input_' + rowId).val();
            var price = $('#item-price-td-' + rowId).text();

            var rowTotal = quantity * price;
            grandTotal += parseFloat(rowTotal);
        });

        console.log('Grand Total for checked rows: ' + grandTotal);
        // Optionally, display the grand total somewhere in the UI
        $('#grand-total-td').text(grandTotal.toFixed(2)); // Display with two decimal points
    }

    // Trigger the function when needed
    $(document).ready(function() {
        initQuantityAndCheckboxListeners(); // Initialize listeners when the document is ready
    });




</script>