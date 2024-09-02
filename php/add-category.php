<?php 
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add-category'])) {
        $category = trim($_POST['category']);

        $sql_check_duplicate = "SELECT * FROM items_category WHERE category_name = '$category'";
        $result = $conn->query($sql_check_duplicate);
        if($result->num_rows > 0) {
            echo "<script>alert('Category already exists!')</script>";
            echo "<script>window.location.href = 'inventory.php?act=CH-CATEGORY';</script>";
        }else{

            $sql_add_category = "INSERT INTO items_category (category_name) VALUES ('$category')";
            if($conn->query($sql_add_category) === TRUE) {
                echo "<script>alert('Category added successfully!')</script>";
                echo "<script>window.location.href = 'inventory.php?act=CH-CATEGORY';</script>";
            } else {
                echo "<script>alert('Error adding category: ".$conn->error."')</script>";
            }
        }
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit-category'])) {
        $new_category = $_POST['new-category'];
        $category_id = $_POST['edit-category'];
        
        $sql_check_changes = "SELECT * FROM items_category WHERE category_id = '$category_id'";
        $result = $conn->query($sql_check_changes);
        $row = $result->fetch_assoc();
        if($new_category == $row['category_name']) {
            echo "<script>alert('No changes made.')</script>";
            echo "<script>window.location.href = 'inventory.php?act=CH-CATEGORY';</script>";
        } else {
            $sql_check_duplicate = "SELECT * FROM items_category WHERE category_name = '$new_category'";
            $result = $conn->query($sql_check_duplicate);
            if($result->num_rows > 0) {
                echo "<script>alert('Category already exists!')</script>";
                //echo "<script>window.location.href = 'inventory.php'</script>";
                echo "<script>window.location.href = 'inventory.php?act=CH-CATEGORY';</script>";
            } else {
                // Prepare the SQL statement
                $sql_edit_category = "UPDATE items_category SET category_name = ? WHERE category_id = ?";
                $stmt = $conn->prepare($sql_edit_category);

                // Bind parameters
                $stmt->bind_param("si", $new_category, $category_id);

                // Execute the statement
                if($stmt->execute()) {
                    echo "<script>alert('Category updated successfully! $new_category $category_id')</script>";
                    echo "<script>window.location.href = 'inventory.php?act=CH-CATEGORY';</script>";
                } else {
                    echo "<script>alert('Error updating category: ".$conn->error."')</script>";
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

    <link rel="stylesheet" href="../css/add-category.css">
    <link rel="stylesheet" href="../fontawesome-free-6.5.1-web/css/all.min.css">
    
    <title>Document</title>
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <div class="floating-addCategory-container" id="floating-addCategory-container">
        <div class="bar">
            <span>Add Category</span>
            <span class="close-icon" onclick="hideFloatingContainerAddCategory()">&#10006;</span>
        </div>
        <form class="add-category-from" action="" method="POST">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" required>
            <button type="submit" name="add-category">Add Category</button>
            <?php 
                echo "<span id='response'></span>"
            ?>
        </form>

        <div class="category-table">
            <table>
                <tr>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <?php 
                        $sql_get_categories = "SELECT * FROM items_category";
                        $result = $conn->query($sql_get_categories);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>".$row['category_name']."</td>";
                            echo '<td><button class="edit-category-btn" onclick="showFloatingContainerEditCategory(' . $row['category_id'] . ')"><i class="fa-solid fa-pencil"></i></button></td>';
                            echo "</tr>";
                        }
                    ?>
                </tr>
            </table>
        </div>
    </div>

    <div class="floating-editCategory-container" id="floating-editCategory-container">
        
    </div>
</body>
<script>
    function hideFloatingContainerAddCategory() {
        document.getElementById('floating-addCategory-container').style.display = 'none';
        window.history.pushState({}, '', 'inventory.php');
    }
    function hideFloatingContainerEditCategory() {
        document.getElementById('floating-editCategory-container').style.display = 'none';
    }

    function showFloatingContainerEditCategory(categoryId) {
        $(document).ready(function() {
            console.log(categoryId);
            if (categoryId) {
                $('#floating-editCategory-container').load('../php/edit-category.php', {
                    category_id: categoryId
                });
                document.getElementById('floating-editCategory-container').style.display = 'block';
            }
        });
    }
</script>
</html>