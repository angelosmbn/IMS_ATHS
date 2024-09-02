<link rel="stylesheet" type="text/css" href="../css/add-category.css">
<?php 
    require '../php/connection.php';
    if(isset($_POST['category_id'])) {
        $category_id = $_POST['category_id'];
        $sql_get_category = "SELECT * FROM items_category WHERE category_id = " . $category_id;
        $result = $conn->query($sql_get_category);
        $row = $result->fetch_assoc();
        $category_name = $row['category_name'];
    }
    else{
        echo "<script>alert('No category selected.')</script>";
        echo "<script>window.location.href = 'inventory.php'</script>";
    }
?>
<div class="bar">
    <span>Edit Category</span>
    <span class="close-icon" onclick="hideFloatingContainerEditCategory()">&#10006;</span>
</div>
<form class="edit-category-from" action="" method="POST">
    <label for="category">Category: <?php echo $category_name ?></label>
    <input type="text" name="new-category" id="new-category" value="<?php echo $category_name ?>" required>
    <input type="hidden" name="edit-category" value="<?php echo $category_id ?>">
    <button type="submit">Save Changes</button>
</form>