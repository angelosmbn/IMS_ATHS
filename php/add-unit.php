<?php 
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add-unit'])) {
        $unit = trim($_POST['unit']);

        $sql_check_duplicate = "SELECT * FROM items_unit WHERE unit_name = '$cunit'";
        $result = $conn->query($sql_check_duplicate);
        if($result->num_rows > 0) {
            echo "<script>alert('Unit already exists!')</script>";
            echo "<script>window.location.href = 'inventory.php?act=CH-UNIT';</script>";
        }else{
            $sql_add_unit = "INSERT INTO items_unit (unit_name) VALUES ('$unit')";
            if($conn->query($sql_add_unit) === TRUE) {
                echo "<script>alert('Unit added successfully!')</script>";
                echo "<script>window.location.href = 'inventory.php?act=CH-UNIT';</script>";
            } else {
                echo "<script>alert('Error adding unit: ".$conn->error."')</script>";
            }
        }
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit-unit'])) {
        $new_unit = $_POST['new-unit'];
        $unit_id = $_POST['edit-unit'];
        
        $sql_check_changes = "SELECT * FROM items_unit WHERE unit_id = '$unit_id'";
        $result = $conn->query($sql_check_changes);
        $row = $result->fetch_assoc();
        if($new_unit == $row['unit_name']) {
            echo "<script>alert('No changes made.')</script>";
            echo "<script>window.location.href = 'inventory.php?act=CH-UNIT';</script>";
        } else {
            $sql_check_duplicate = "SELECT * FROM items_unit WHERE unit_name = '$new_unit'";
            $result = $conn->query($sql_check_duplicate);
            if($result->num_rows > 0) {
                echo "<script>alert('Unit already exists!')</script>";
                //echo "<script>window.location.href = 'inventory.php'</script>";
                echo "<script>window.location.href = 'inventory.php?act=CH-UNIT';</script>";
            } else {
                // Prepare the SQL statement
                $sql_edit_unit = "UPDATE items_unit SET unit_name = ? WHERE unit_id = ?";
                $stmt = $conn->prepare($sql_edit_unit);

                // Bind parameters
                $stmt->bind_param("si", $new_unit, $unit_id);

                // Execute the statement
                if($stmt->execute()) {
                    echo "<script>alert('Unit updated successfully! $new_unit $unit_id')</script>";
                    echo "<script>window.location.href = 'inventory.php?act=CH-UNIT';</script>";
                } else {
                    echo "<script>alert('Error updating unit: ".$conn->error."')</script>";
                }
            }
        }


        
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/add-unit.css">
    <link rel="stylesheet" href="../fontawesome-free-6.5.1-web/css/all.min.css">
    
    <title>Document</title>
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <div class="floating-addUnit-container" id="floating-addUnit-container">
        <div class="bar">
            <span>Add Unit</span>
            <span class="close-icon" onclick="hideFloatingContainerAddUnit()">&#10006;</span>
        </div>
        <form class="add-unit-from" action="" method="POST">
            <label for="unit">Unit:</label>
            <input type="text" name="unit" id="unit" required>
            <button type="submit" name="add-unit">Add Unit</button>
            <?php 
                echo "<span id='response'></span>"
            ?>
        </form>

        <div class="unit-table">
            <table>
                <tr>
                    <th>Unit</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <?php 
                        $sql_get_categories = "SELECT * FROM items_unit";
                        $result = $conn->query($sql_get_categories);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>".$row['unit_name']."</td>";
                            echo '<td><button class="edit-unit-btn" onclick="showFloatingContainerEditUnit(' . $row['unit_id'] . ')"><i class="fa-solid fa-pencil"></i></button></td>';
                            echo "</tr>";
                        }
                    ?>
                </tr>
            </table>
        </div>
    </div>

    <div class="floating-editUnit-container" id="floating-editUnit-container">
        
    </div>
</body>
<script>
    function hideFloatingContainerAddUnit() {
        document.getElementById('floating-addUnit-container').style.display = 'none';
        window.history.pushState({}, '', 'inventory.php');
    }
    function hideFloatingContainerEditUnit() {
        document.getElementById('floating-editUnit-container').style.display = 'none';
    }

    function showFloatingContainerEditUnit(unitId) {
        $(document).ready(function() {
            console.log(unitId);
            if (unitId) {
                $('#floating-editUnit-container').load('../php/edit-unit.php', {
                    unit_id: unitId
                });
                document.getElementById('floating-editUnit-container').style.display = 'block';
            }
        });
    }
</script>
</html>