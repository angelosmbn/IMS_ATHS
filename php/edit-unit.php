<link rel="stylesheet" type="text/css" href="../css/add-unit.css">
<?php 
    require '../php/connection.php';
    if(isset($_POST['unit_id'])) {
        $unit_id = $_POST['unit_id'];
        $sql_get_unit = "SELECT * FROM items_unit WHERE unit_id = " . $unit_id;
        $result = $conn->query($sql_get_unit);
        $row = $result->fetch_assoc();
        $unit_name = $row['unit_name'];
    }
    else{
        echo "<script>alert('No unit selected.')</script>";
        echo "<script>window.location.href = 'inventory.php'</script>";
    }
?>
<div class="bar">
    <span>Edit Unit</span>
    <span class="close-icon" onclick="hideFloatingContainerEditUnit()">&#10006;</span>
</div>
<form class="edit-unit-from" action="" method="POST">
    <label for="unit">Unit: <?php echo $unit_name ?></label>
    <input type="text" name="new-unit" id="new-unit" value="<?php echo $unit_name ?>" required>
    <input type="hidden" name="edit-unit" value="<?php echo $unit_id ?>">
    <button type="submit">Save Changes</button>
</form>