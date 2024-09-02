<link rel="stylesheet" type="text/css" href="../css/add-category.css">
<?php 
    require '../php/connection.php';
    if(isset($_POST['department_id'])) {
        $department_id = $_POST['department_id'];
        $sql_get_department = "SELECT * FROM departments WHERE department_id = " . $department_id;
        $result = $conn->query($sql_get_department);
        $row = $result->fetch_assoc();
        $department_name = $row['department_name'];
    }
    else{
        echo "<script>alert('No department selected.')</script>";
        echo "<script>window.location.href = 'inventory.php'</script>";
    }
?>
<span class="close-icon" onclick="hideFloatingContainerEditCategory()">&#10006;</span>
<h2 class="form-title">Edit Department</h2>
<form class="edit-category-from" action="" method="POST">
    <label for="department">Department: <?php echo $department_name ?></label>
    <input type="text" name="new-department" id="new-department" value="<?php echo $department_name ?>" required>
    <input type="hidden" name="edit-department" value="<?php echo $department_id ?>">
    <button type="submit">Save Changes</button>
</form>