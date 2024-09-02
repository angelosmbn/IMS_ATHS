<?php 
    require '../php/connection.php';
    $selected_items = $_POST['selected_items'];
    $selected_items_string = implode(',', $selected_items);

    $department_id = $_POST['department_id'];
?>
<link rel="stylesheet" href="../css/add-request.css">
<div class="selected-items">
    <table id="selectedItemsTable">
        <tr class="sticky-row">
            <th rowspan="2">Name</th>
            <th rowspan="2">Brand</th>
            <th rowspan="2">Description</th>
            <th colspan="2">Stocks</th>
            <th rowspan="2">Price</th>
            <th rowspan="2">Quantity</th>
            <th rowspan="2">Unit</th>
        </tr> 
        <tr class="sticky-row-1">
            <th>General</th>
            <th>Department</th>
        </tr>
        <?php 
            $sql_get_selected_items = "SELECT * FROM items WHERE item_id IN (" . $selected_items_string . ") AND borrowable = 'no'";
            $result = $conn->query($sql_get_selected_items);
            while ($row = $result->fetch_assoc()) {
                $sql_get_department_accumulable = "SELECT * FROM accumulable WHERE item_id_fk = ? AND department_id_fk = ?";
                $stmt = $conn->prepare($sql_get_department_accumulable);
                $stmt->bind_param("ii", $row['item_id'], $department_id);
                $stmt->execute();
                $result2 = $stmt->get_result();

                if ($result2) {
                    $row2 = $result2->fetch_assoc();
                    if ($row2['consumed'] > $row2['accumulable_quantity']) {
                        $available_department = 0;
                    } else {
                        $available_department = $row2['accumulable_quantity'] - $row2['consumed'];
                    }
                } else {
                    // Handle case when there are no results from the query
                    $row['item_stocks_department'] = 0;
                    $consumed = 0;
                    $consumable = 0;
                }

                echo '<tr>';
                echo '<td>' . $row['item_name'] . '</td>';
                echo '<td>' . $row['item_brand'] . '</td>';
                echo '<td>' . $row['item_description'] . '</td>';
                echo '<td>' . $row['item_stocks'] . '</td>';
                echo '<td>' . $available_department . '</td>';
                echo '<td>' . $row['item_price'] . '</td>';
                // Add visible input field with initial quantity of 1
                echo '<td><input type="number" name="quantity[' . $row['item_id'] . ']" min="1" max="' . $row['item_stocks'] + $available_department . '" value="1"></td>';  // Set initial value to 1
                echo '<td>' . $row['unit'] . '</td>';
                echo '</tr>';
            }


            $sql_get_selected_items = "SELECT * FROM items WHERE item_id IN (" . $selected_items_string . ") AND borrowable = 'yes'";
            $result = $conn->query($sql_get_selected_items);
            if ($result->num_rows > 0) {
                echo '<tr>';
                echo '<td colspan="8">BORROWABLE ITEMS</td>';
                echo '</tr>';
                while ($row = $result->fetch_assoc()) {
                    $sql_get_department_accumulable = "SELECT * FROM accumulable WHERE item_id_fk = ? AND department_id_fk = ?";
                    $stmt = $conn->prepare($sql_get_department_accumulable);
                    $stmt->bind_param("ii", $row['item_id'], $department_id);
                    $stmt->execute();
                    $result2 = $stmt->get_result();

                    if ($result2) {
                        $row2 = $result2->fetch_assoc();
                        if ($row2['consumed'] > $row2['accumulable_quantity']) {
                            $available_department = 0;
                        } else {
                            $available_department = $row2['accumulable_quantity'] - $row2['consumed'];
                        }
                    } else {
                        // Handle case when there are no results from the query
                        $row['item_stocks_department'] = 0;
                        $consumed = 0;
                        $consumable = 0;
                    }

                    echo '<tr>';
                    echo '<td>' . $row['item_name'] . '</td>';
                    echo '<td>' . $row['item_brand'] . '</td>';
                    echo '<td>' . $row['item_description'] . '</td>';
                    echo '<td>' . $row['item_stocks'] . '</td>';
                    echo '<td>' . $available_department . '</td>';
                    echo '<td>' . $row['item_price'] . '</td>';
                    // Add visible input field with initial quantity of 1
                    echo '<td><input type="number" name="quantity[' . $row['item_id'] . ']" min="1" max="' . $row['item_stocks'] + $available_department . '" value="1"></td>';  // Set initial value to 1
                    echo '<td>' . $row['unit'] . '</td>';
                    echo '</tr>';
                }
            }

        ?>
    </table>
</div>
