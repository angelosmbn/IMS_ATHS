<?php 
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add-item'])) {
        $item_name = ucwords($_POST['item-name']);
        $item_category = $_POST['item-category'];
        $item_brand = ucwords($_POST['item-brand']);
        $item_description = ucfirst($_POST['item-description']);
        $item_stocks = $_POST['item-stocks'];
        $item_unit = $_POST['item-unit'];
        $item_price = $_POST['item-price'];
        $item_indicator = $_POST['item-indicator'];
        $borrowable = $_POST['borrowable'];
        $date_added = date('Y-m-d H:i:s');

        $sql = "INSERT INTO items (item_name, item_category, item_brand, item_description, beginning_inventory, item_stocks, unit, item_price, restock_indicator, borrowable, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $item_name, $item_category, $item_brand, $item_description, $item_stocks, $item_stocks, $item_unit, $item_price, $item_indicator, $borrowable, $date_added);
        $stmt->execute();
        $stmt->close();

        if($stmt) {
            $item_id = $conn->insert_id;
            $school_year = $_SESSION['school_year'];
            addAccumulable($item_id, $school_year, $conn);
            echo "<script>
            alert('Item added successfully!');
            window.location.href = 'inventory.php';
            </script>";

        } else {
            echo "<script>alert('Error')</script>";
        }
    }

    function addAccumulable($item_id, $school_year, $conn) {
        $sql_get_departments = "SELECT * FROM departments";
        $result = $conn->query($sql_get_departments);
        while ($row = $result->fetch_assoc()) {
            $department_id = $row['department_id'];
            $sql_insert = "INSERT INTO accumulable (department_id_fk, item_id_fk, accumulable_quantity, consumed, school_year) VALUES (?, ?, 0, 0, ?)";
            $stmt = $conn->prepare($sql_insert);
            $stmt->bind_param("iis", $department_id, $item_id, $school_year);
            $stmt->execute();
            $stmt->close();
        }
    }



    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/add-item.css">
    <link rel="stylesheet" href="../fontawesome-free-6.5.1-web/css/all.min.css">
    
    <title>Document</title>
</head>
<body>
    <div class="floating-addItem-container" id="floating-addItem-container">
        <div class="bar">
            <span>Add Item</span>
            <span class="close-icon" onclick="hideFloatingContainerAddItem()">&#10006;</span>
        </div>
        <form class="add-item-form" method="POST" action="">
            
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
                                    echo "<option value='".$row['category_name']."'>".$row['category_name']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                    <td><input type="text" name="item-name" id="item-name" required></td>
                </tr>

                <tr>
                    <td><label for="item-brand">Item Brand</label></td>
                    <td><label for="item-description">Item Description</label></td>
                </tr>
                <tr>
                    <td><input type="text" name="item-brand" id="item-brand" required></td>
                    <td><input type="text" name="item-description" id="item-description" required></td>
                </tr>

                <tr>
                    <td><label for="item-stocks">Beginning Inventory</label></td>
                    <td><label for="item-unit">Item Unit</label></td>
                </tr>
                <tr>
                    <td><input type="number" name="item-stocks" id="item-stocks" pattern="[0-9]+" title="Please enter a positive integer" required></td>
                    <td>
                        <select name="item-unit" id="item-unit" required>
                            <option value="">Select Unit</option>
                            <?php 
                                $sql_get_categories = "SELECT * FROM items_unit";
                                $result = $conn->query($sql_get_categories);
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='".$row['unit_name']."'>".$row['unit_name']."</option>";
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
                    <td><input type="number" step="0.01" name="item-price" id="item-price" required></td>
                    <td><input type="text" name="item-indicator" id="item-indicator" pattern="[0-9]+" title="Please enter a positive integer"></td>
                </tr>
                
                <tr>
                    <td><label for="borrowable">Borrowable</label></td>
                </tr>
                <tr>
                    <td>
                    <select name="borrowable" id="borrowable">
                        <option value="yes">Yes</option>
                        <option value="no" selected>No</option>
                    </select>
                    <input type="hidden" name="add-item">
                    </td>
                </tr>
                
            </table>

            <div class="form-button">
                <button type="submit">Add</button>
            </div>
        </form>
        
    </div>
</body>
<script>
    function hideFloatingContainerAddItem() {
        document.getElementById('floating-addItem-container').style.display = 'none';
    }
</script>
</html>