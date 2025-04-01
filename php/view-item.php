
<div class="bar">
    <span>View Item</span>
    <span class="close-icon" onclick="hideFloatingContainerViewItem()">&#10006;</span>
</div>
<div class="item-details">
    <table>
        <?php 
            require '../php/connection.php';
            $item_id = $_POST['item_id'];
            $sql_get_item = "SELECT * FROM items WHERE item_id = " . $item_id;
            $result = $conn->query($sql_get_item);
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><span>Category: </span>' . $row['item_category'] . '</td>';
                echo '<td><span>Name: </span>' . $row['item_name'] . '</td>';
                echo '<td><span>Brand: </span>' . $row['item_brand'] . '</td>';
                echo '<td><span>Description: </span>' . $row['item_description'] . '</td>';
                echo '</tr>';
            }
        ?>
    </table>
</div>
<div class="view-item">
    <table id="view-item-table">
        <tr class="sticky-row">
            <th>Beginning Inventory</th>
            <th>Purchases</th>
            <th>Cost</th>
            <th>Request</th>
            <th>Date of Request</th>
            <th>Date of Release</th>
            <th>Ending Balance</th>
            <th>Requesting Department</th>
            <th>Charged Department</th>
            <th>Purpose</th>
        </tr>
        <?php 
            $sql_get_monitoring = "SELECT * FROM stock_monitoring st
                                JOIN departments d ON st.requesting_department_id  = d.department_id
                                JOIN accumulable a ON st.item_id = a.item_id_fk AND st.requesting_department_id = a.department_id_fk
                                WHERE item_id = " . $item_id . " 
                                ORDER BY monitoring_id ASC";
            $result = $conn->query($sql_get_monitoring);
            $prev_ReqID = 0;
            $prev_ReqDeptID = 0;
            $prev_ChargedDeptID = 0;
            $prev_Qty = 0;
            $prev_ItemID = 0;
            $num_rows = $result->num_rows;
            $prev_QtyFlag = true;
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                if ($prev_ReqID == $row['request_id'] && $prev_ReqID != 0 && $row['requesting_department_id'] == $prev_ReqDeptID && $row['charged_department_id'] == $prev_ChargedDeptID && $row['purpose'] != 'Returned' && $num_rows != 1 && $prev_ItemID == $row['item_id']) {
                    echo '<tr>';
                    echo '<td>' . $row['beginning_inventory_general'] + $prev_Qty . '</td>';
    
                    echo '<td>' . $row['item_purchases'] . 'asd'. '</td>';
                    echo '<td>' . $row['item_cost'] . '</td>';
                    echo '<td>' . $row['requested_quantity_general'] + $prev_Qty . '</td>';
                    echo '<td>' . date('F j, Y', strtotime($row['requested_date'])) . '</td>';
                    if ($row['release_date'] == NULL) {
                        echo '<td></td>';
                    } else {
                        echo '<td>' . date('F j, Y', strtotime($row['release_date'])) . '</td>';
                    }
                    echo '<td>' . $row['ending_inventory_general'] . '</td>';
                    $sql_get_department_charged_name = "SELECT * FROM departments WHERE department_id = " . $row['charged_department_id'];
                    $result_charged = $conn->query($sql_get_department_charged_name);
                    $row_charged = $result_charged->fetch_assoc();
                    if ($row['purpose'] == 'Add Stocks') {
                        echo '<td></td>';
                        echo '<td></td>';
                    } else {
                        echo '<td>' . $row['department_name'] . '</td>';
                        echo '<td>' . $row_charged['department_name'] . '</td>';
                    }
                    echo '<td>' . $row['purpose'] . '</td>';
                    echo '</tr>';
                
                    $prev_QtyFlag = true;
                } elseif($prev_ReqID == 0 && $num_rows != 1 && ($row['purpose'] == 'Returned' || $row['purpose'] == 'Add Stocks') && $i != 0) {

                }else{
    
                    echo '<tr>';
                    echo '<td>' . $row['beginning_inventory_general'] . '</td>';
                    echo '<td>' . $row['item_purchases'] . '</td>';
                    echo '<td>' . $row['item_cost'] . '</td>';
                    echo '<td>' . $row['requested_quantity_general'] . '</td>';
                    echo '<td>' . date('F j, Y', strtotime($row['requested_date'])) . '</td>';
                    if ($row['release_date'] == NULL) {
                        echo '<td></td>';
                    } else {
                        echo '<td>' . date('F j, Y', strtotime($row['release_date'])) . '</td>';
                    }
                    echo '<td>' . $row['ending_inventory_general'] . '</td>';
                    $sql_get_department_charged_name = "SELECT * FROM departments WHERE department_id = " . $row['charged_department_id'];
                    $result_charged = $conn->query($sql_get_department_charged_name);
                    $row_charged = $result_charged->fetch_assoc();
                    if ($row['purpose'] == 'Add Stocks') {
                        echo '<td></td>';
                        echo '<td></td>';
                    } else {
                        echo '<td>' . $row['department_name'] . '</td>';
                        echo '<td>' . $row_charged['department_name'] . '</td>';
                    }
                    echo '<td>' . $row['purpose'] . '</td>';
                    echo '</tr>';
                    $prev_QtyFlag = false;
                }
                if ($prev_QtyFlag) {
                    $prev_Qty = $row['requested_quantity_general'];
                } else {
                    $prev_Qty = 0;
                }
                $i = 1;
                $prev_ReqID = $row['request_id'];
                $prev_ReqDeptID = $row['requesting_department_id'];
                $prev_ChargedDeptID = $row['charged_department_id'];
                $prev_ItemID = $row['item_id'];

                echo '<script>console.log('. $prev_Qty .')</script>';
                echo '<script>console.log('. $prev_Qty .')</script>';
            }
        
        ?>
    </table>
</div>