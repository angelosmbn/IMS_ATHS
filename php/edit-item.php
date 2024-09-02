<link rel="stylesheet" type="text/css" href="../css/edit-item.css">
<?php 
    require '../php/connection.php';
    if(isset($_POST['item_id'])) {
        $item_id = $_POST['item_id'];
        $sql_get_item = "SELECT * FROM items WHERE item_id = " . $item_id;
        $result = $conn->query($sql_get_item);
        $row = $result->fetch_assoc();
        $item_category = $row['item_category'];
        $item_name = $row['item_name'];
        $item_brand = $row['item_brand'];
        $item_description = $row['item_description'];
        $item_stocks = $row['item_stocks'];
        $item_unit = $row['unit'];
        $item_price = $row['item_price'];
        $item_indicator = $row['restock_indicator'];
        $borrowable = $row['borrowable'];
        $hide = $row['hide_status'];
    } else {
        echo "<script>alert('No item selected.')</script>";
        echo "<script>window.location.href = 'inventory.php'</script>";
    }

    

?>
<div class="bar">
    <span>Edit Item</span>
    <span class="close-icon" onclick="hideFloatingContainerEditItem()">&#10006;</span>
</div>
        <form class="edit-item-form" id="edit-item-form" method="POST" action="">
            <table>
                <tr class="input-labels">
                    <td><label for="item-category">Item Category</label></td>
                    <td><label for="item-name">Item Name</label></td>
                </tr>
                <tr>
                    <td>
                    <select name="item-category" id="item-category" required>
                        <option value="">Select Category</option>
                        <?php 
                            $sql_get_categories = "SELECT * FROM items_category";
                            $result = $conn->query($sql_get_categories);
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='".$row['category_name']."' ";
                                if ($item_category == $row['category_name']) {
                                    echo "selected";
                                }
                                echo ">".$row['category_name']."</option>";
                            }
                        ?>
                    </select>
                    </td>
                    <td><input type="text" name="item-name" id="item-name" value="<?php echo htmlspecialchars($item_name); ?>" required></td>
                </tr>

                <tr>
                    <td><label for="item-brand">Item Brand</label></td>
                    <td><label for="item-description">Item Description</label></td>
                </tr>
                <tr>
                    <td><input type="text" name="item-brand" id="item-brand" value="<?php echo htmlspecialchars($item_brand); ?>" required></td>
                    <td><input type="text" name="item-description" id="item-description" value="<?php echo htmlspecialchars($item_description); ?>" required></td>
                </tr>

                <tr>
                    <td><label for="item-stocks">Beginning Inventory</label></td>
                    <td><label for="item-unit">Item Unit</label></td>
                </tr>
                <tr>
                    <td><input type="number" name="item-stocks" id="item-stocks" pattern="[0-9]+" title="Please enter a positive integer" value="<?php echo htmlspecialchars($item_stocks); ?>" readonly></td>
                    <td>
                        <select name="item-unit" id="item-unit" required>
                            <option value="">Select Unit</option>
                            <?php 
                                $sql_get_categories = "SELECT * FROM items_unit";
                                $result = $conn->query($sql_get_categories);
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='".$row['unit_name']."' ";
                                    if ($item_unit == $row['unit_name']) {
                                        echo "selected";
                                    }
                                    echo ">".$row['unit_name']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="item-price">Item Price</label></td>
                    <td><label for="item-indicator">Restock Indicator</label></td>
                </tr>
                <tr>
                    <td><input type="number" step="0.01" name="item-price" id="item-price" value="<?php echo htmlspecialchars($item_price); ?>" required></td>
                    <td><input type="text" name="item-indicator" id="item-indicator" value="<?php echo htmlspecialchars($item_indicator); ?>" pattern="[0-9]+" title="Please enter a positive integer"></td>

                </tr>
                
                <tr>
                    <td><label for="borrowable">Borrowable</label></td>
                    <td><label for="hide">Hide</label></td>
                </tr>
                <tr>
                    <td>
                        <select name="borrowable" id="borrowable">
                            <option value="yes" <?php if ($borrowable == 'yes') echo 'selected'; ?>>Yes</option>
                            <option value="no" <?php if ($borrowable == 'no') echo 'selected'; ?>>No</option>
                        </select>
                        <input type="hidden" name="edit-item" value="<?php echo $item_id ?>">
                    </td>
                    <td>
                        <select name="hide" id="hide">
                            <option value="yes"  <?php if ($hide == 'yes') echo 'selected'; ?>>Yes</option>
                            <option value="no" <?php if ($hide == 'no') echo 'selected'; ?>>No</option>
                        </select>
                    </td>
                </tr>
                
            </table>
            <div class="form-button">
                <button type="submit">Save</button>
            </div>
        </form>