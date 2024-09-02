<?php 
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add-department'])) {
        $department= $_POST['department'];

        $sql_check_duplicate = "SELECT * FROM departments WHERE department_name = '$department'";
        $result = $conn->query($sql_check_duplicate);
        if($result->num_rows > 0) {
            echo "<script>alert('Department already exists!')</script>";
            echo "<script>
                var currentUrl = window.location.href;
                var separator = currentUrl.includes('?') ? '&' : '?';
                window.location.href = currentUrl + separator + 'act=CH-DEPARTMENT';
            </script>";
        }else{
            $sql_add_department = "INSERT INTO departments (department_name) VALUES ('$department')";
            if($conn->query($sql_add_department) === TRUE) {
                $department_id = $conn->insert_id;
                $sql_get_items = "SELECT * FROM items";
                $result_items = $conn->query($sql_get_items);
                while($row_items = $result_items->fetch_assoc()) {
                    $sql_add_accumulable = "INSERT INTO accumulable (item_id_fk, department_id_fk, school_year) VALUES ('".$row_items['item_id']."', '".$department_id."', '".$_SESSION['school_year']."')";
                    $conn->query($sql_add_accumulable);
                }
                
                echo "<script>alert('Department added successfully!')</script>";
                echo "<script>
                    var currentUrl = window.location.href;
                    var separator = currentUrl.includes('?') ? '&' : '?';
                    window.location.href = currentUrl + separator + 'act=CH-DEPARTMENT';
                </script>";
            } else {
                echo "<script>alert('Error adding department: ".$conn->error."')</script>";
            }
        }
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit-department'])) {
        $new_department = $_POST['new-department'];
        $department_id = $_POST['edit-department'];
        
        $sql_check_changes = "SELECT * FROM departments WHERE department_id = '$department_id'";
        $result = $conn->query($sql_check_changes);
        $row = $result->fetch_assoc();
        if($new_department == $row['department_name']) {
            echo "<script>alert('No changes made.')</script>";
            echo "<script>
                    var currentUrl = window.location.href;
                    var separator = currentUrl.includes('?') ? '&' : '?';
                    window.location.href = currentUrl + separator + 'act=CH-DEPARTMENT';
                </script>";
        } else {
            $sql_check_duplicate = "SELECT * FROM departments WHERE department_name = '$new_department'";
            $result = $conn->query($sql_check_duplicate);
            if($result->num_rows > 0) {
                echo "<script>alert('Department already exists!')</script>";
                //echo "<script>window.location.href = 'inventory.php'</script>";
                echo "<script>
                    var currentUrl = window.location.href;
                    var separator = currentUrl.includes('?') ? '&' : '?';
                    window.location.href = currentUrl + separator + 'act=CH-DEPARTMENT';
                </script>";
            } else {
                // Prepare the SQL statement
                $sql_edit_department = "UPDATE departments SET department_name = ? WHERE department_id = ?";
                $stmt = $conn->prepare($sql_edit_department);

                // Bind parameters
                $stmt->bind_param("si", $new_department, $department_id);

                // Execute the statement
                if($stmt->execute()) {
                    echo "<script>alert('Department updated successfully! $new_department $department_id')</script>";
                    echo "<script>
                        var currentUrl = window.location.href;
                        var separator = currentUrl.includes('?') ? '&' : '?';
                        window.location.href = currentUrl + separator + 'act=CH-DEPARTMENT';
                    </script>";
                } else {
                    echo "<script>alert('Error updating department: ".$conn->error."')</script>";
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
            <span>Add Department</span>
            <span class="close-icon" onclick="hideFloatingContainerAddCategory()">&#10006;</span>
        </div>
        <form class="add-category-from" action="" method="POST">
            <label for="department">Department</label>
            <input type="text" name="department" id="department" required>
            <button type="submit" name="add-department">Add Department</button>
            <?php 
                echo "<span id='response'></span>"
            ?>
        </form>

        <div class="category-table">
            <table>
                <tr>
                    <th>Department</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <?php 
                        $sql_get_categories = "SELECT * FROM departments WHERE department_id != 0";
                        $result = $conn->query($sql_get_categories);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>".$row['department_name']."</td>";
                            echo '<td><button class="edit-category-btn" onclick="showFloatingContainerEditCategory(' . $row['department_id'] . ')"><i class="fa-solid fa-pencil"></i></button></td>';
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
    <?php if(isset($_GET['act'])): ?>
        // Get the PHP flag value and embed it into JavaScript
        var actValue = "<?php echo $_GET['act']; ?>";

        // Check if the flag value equals 'CH-CATEGORY' and set display accordingly
        if (actValue === 'CH-DEPARTMENT') {
            document.querySelector('.floating-addCategory-container').style.display = 'block';
        }
    <?php endif; ?>

    
    function hideFloatingContainerAddCategory() {
        document.getElementById('floating-addCategory-container').style.display = 'none';
    }
    function hideFloatingContainerEditCategory() {
        document.getElementById('floating-editCategory-container').style.display = 'none';
    }

    function showFloatingContainerEditCategory(departmentId) {
        $(document).ready(function() {
            console.log(departmentId);
            if (departmentId) {
                $('#floating-editCategory-container').load('../php/edit-department.php', {
                    department_id: departmentId
                });
                document.getElementById('floating-editCategory-container').style.display = 'block';
            }
        });
    }
</script>
</html>