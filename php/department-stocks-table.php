<tr class="sticky-row">
                    <th>Item</th>
                    <th>Brand</th>
                    <th>Description</th>
                    <th class="small-width">Accumulable</th>
                    <th class="small-width">Consumed</th>
                    <th class="small-width">Remaining</th>
                </tr>
<?php 
    require '../php/connection.php';
    $departmentId = $_POST['department_id'];
    $sql = "SELECT * FROM items";
    $result = $conn->query($sql);
    if($result->num_rows > 0) {
    
        while($row = $result->fetch_assoc()) {
            $get_accumulable = "SELECT * FROM accumulable WHERE department_id_fk = " . $departmentId . " AND item_id_fk = " . $row['item_id'];
            $result_accumulable = $conn->query($get_accumulable);
            $row_accumulable = $result_accumulable->fetch_assoc();
            $accumulable = 0;
            $consumed = 0;
            if ($result_accumulable) {
                $accumulable = $row_accumulable['accumulable_quantity'];
                $consumed = $row_accumulable['consumed'];
            }

            $accumulableInputId = 'accumulable-quantity-' . $row['item_id'];
            echo "<tr>";
            echo "<td>".$row['item_name']."</td>";
            echo "<td>".$row['item_brand']."</td>";
            echo "<td>".$row['item_description']."</td>";
            echo "<td><input type='number' id='".$accumulableInputId."' name='accumulable-quantity[]' step='1' value='".$accumulable."' readonly></td>";
            echo "<td>".$consumed."</td>";
            echo "<td style='color: " . (($accumulable - $consumed) < 0 ? "red" : "black") . ";'>" . ($accumulable - $consumed) . "</td>";
            echo "<input type='hidden' name='item-id[]' value='".$row['item_id']."'>";
            echo "<input type='hidden' name='department-id' id='department-id' value='".$departmentId."'>";
            echo "</tr>";
        }
    }
?>