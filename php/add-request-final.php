<?php 
    require '../php/connection.php';
    
    // Decode selected items from JSON
    $selectedItems = $_POST['selected_items'];
    
    // Access department ID, date needed, and purpose from POST data
    $departmentId = $_POST['department_id'];
    $date_needed = $_POST['date_needed'];
    $purpose = $_POST['purpose'];

    $sql_get_department = "SELECT * FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($sql_get_department);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $departmentName = $row['department_name'];
    } else {
        // Handle case when department is not found
        $departmentName = "Unknown Department";
    }

?>

<link rel="stylesheet" href="../css/add-request.css">
<table id="final-request-information">
<tr>
    <th>Item</th>
    <th>Description</th>
    <th>Quantity</th>
    <th>Approximate Price</th>
    <th>Date Needed</th>
    <th>Charge Account</th>
    <input type="hidden" name="selected_department" value="<?php echo $departmentId; ?>">
    <input type="hidden" name="date_needed" value="<?php echo $date_needed; ?>">
    <input type="hidden" name="purpose" value="<?php echo $purpose; ?>">
</tr>

<?php 
    if (is_array($selectedItems)) {
        $totalPrice = 0;
        $borrow_flag = 0;
        $borrowable = 'no';
        for ($i = 0; $i < 2; $i++) {
            foreach ($selectedItems as $itemId => $quantity) {
                $sql_get_item = "SELECT * FROM items WHERE item_id = $itemId AND borrowable = '$borrowable'";
                $result = $conn->query($sql_get_item);
                while ($row = $result->fetch_assoc()) {

                    if ($borrow_flag == 1){
                        echo '<tr>';
                        echo '<td colspan="6">BORROWABLE ITEMS</td>';
                        echo '</tr>';
                        $borrow_flag = 0;
                    }

                    // Fetch accumulable data for the current item
                    $sql_get_accumulable = "SELECT * FROM accumulable WHERE item_id_fk = ? AND department_id_fk = ?";
                    $stmt = $conn->prepare($sql_get_accumulable);
                    $stmt->bind_param("ii", $itemId, $departmentId);
                    $stmt->execute();
                    $result2 = $stmt->get_result();
                    $row2 = $result2->fetch_assoc();

                    $accumulable_quantity = $row2['accumulable_quantity'];
                    $accumulable_quantity -= $row2['consumed'];


                    $department_quantity = $accumulable_quantity < $quantity ? $accumulable_quantity : $quantity;
                    if ($department_quantity != 0 && $department_quantity > 0) {
                        echo '<tr>';
                        echo '<td>' . $row['item_name'] . '</td>';
                        echo '<td>' . $row['item_description'] . '</td>';
                        echo '<td>' . $department_quantity . '</td>';
                        echo '<td>' . $row['item_price'] . '</td>';
                        echo '<td>' . $date_needed . '</td>';
                        echo '<td>' . $departmentName . '</td>';
                        echo '</tr>';

                        // Hidden input for item ID, quantity, and department charged
                        echo '<input type="hidden" name="item_id[]" value="' . $itemId . '">';
                        echo '<input type="hidden" name="quantity_requested[]" value="' . $department_quantity . '">';
                        echo '<input type="hidden" name="department_charged[]" value="' . $departmentId . '">';

                        $totalPrice += $row['item_price'] * $department_quantity;
                    }


                    if ($accumulable_quantity < $quantity) {
                        $general_quantity = $accumulable_quantity < 0 ? $quantity : $quantity - $accumulable_quantity;
                        echo '<tr>';
                        echo '<td>' . $row['item_name'] . '</td>';
                        echo '<td>' . $row['item_description'] . '</td>';
                        echo '<td>' . $general_quantity . '</td>';
                        echo '<td>' . $row['item_price'] . '</td>';
                        echo '<td>' . $date_needed . '</td>';
                        echo '<td>General</td>';
                        echo '</tr>';

                        // Hidden input for item ID, quantity, and department charged
                        echo '<input type="hidden" name="item_id[]" value="' . $itemId . '">';
                        echo '<input type="hidden" name="quantity_requested[]" value="' . $general_quantity . '">';
                        echo '<input type="hidden" name="department_charged[]" value="0">';

                        $totalPrice += $row['item_price'] * $general_quantity;
                    }
                }
            }
            $borrow_flag = 1;
            $borrowable = 'yes';
        }
    
        echo '<tr>';
        echo '<td colspan="5" style="text-align: right;">Grand Total:</td>';
        echo '<td>' . $totalPrice . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>Purpose:</td>';
        echo '<td colspan="5">' . $purpose . '</td>';
        echo '</tr>';
    } else {
        echo "<tr><td colspan='6'>No selected items found.</td></tr>";
    }
?>
</table>
