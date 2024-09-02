
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
                                WHERE item_id = " . $item_id . " ORDER BY timestamp_added ASC";
            $result = $conn->query($sql_get_monitoring);

            while ($row = $result->fetch_assoc()) {
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



                echo '<td>' . $row['department_name'] . '</td>';
                $sql_get_department_charged_name = "SELECT * FROM departments WHERE department_id = " . $row['charged_department_id'];
                $result_charged = $conn->query($sql_get_department_charged_name);
                $row_charged = $result_charged->fetch_assoc();
                echo '<td>' . $row_charged['department_name'] . '</td>';
                echo '<td>' . $row['purpose'] . '</td>';
                echo '</tr>';
            
            }
        
        ?>
    </table>
</div>