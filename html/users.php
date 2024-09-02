<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != "" ) {
        if ($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer' || $_SESSION['access_level'] == 'admin') {
            // Allow access
        } else {
            echo "<script>alert('You do not have permission to access this page!')</script>";
            echo "<script>window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Please login first!')</script>";
        echo "<script>window.location.href='login.php';</script>";
    }
    require '../php/connection.php';
    require 'navigation-bar.php';
    require '../php/add-department.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        $accessLevel = $_POST['access_level'];
        $unique_departments = array_unique($_POST['department']); // Remove duplicates from the array
$unique_departments_string = implode(', ', $unique_departments); // Implode unique values back into a string


        if ($accessLevel == "coordinator" || $accessLevel == "finance officer") {
            $handledDepartment = $_POST['handled_department'];
        } else {
            $handledDepartment = "";
        }
        $account_status = $_POST['account_status'];
    
        // Fetch existing user details from the database
        $sql_fetch_user = "SELECT * FROM users WHERE user_id = $userId";
        $result_fetch_user = $conn->query($sql_fetch_user);
        $row_fetch_user = $result_fetch_user->fetch_assoc();
    
        // Compare the submitted values with the existing values
        if ($row_fetch_user['access_level'] == $accessLevel &&
            $row_fetch_user['department'] == $unique_departments_string &&
            $row_fetch_user['handled_department'] == $handledDepartment &&
            $row_fetch_user['account_status'] == $account_status) {
            // If there are no changes, alert and redirect
            echo "<script>alert('No changes were made.');</script>";
            echo "<script>window.location.href='users.php';</script>";
        } else {
            // If there are changes, update the user details
            $sql = "UPDATE users SET access_level = '$accessLevel', department = '$unique_departments_string', handled_department = '$handledDepartment', account_status = '$account_status' WHERE user_id = $userId";
            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('User details updated successfully!')</script>";
                echo "<script>window.location.href='users.php';</script>";
            } else {
                echo "<script>alert('Error updating user details: " . $conn->error . "')</script>";
            }
        }
    }
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/users.css">
    <title>Document</title>
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
</head>
<body>
    <div class="users-container" id="users-container">
        <div class="settings-container">
            <input type="text" name="search" id="search">
            <div>
                <?php 
                    if($_SESSION['access_level'] == 'admin') {
                        echo '<button onclick="showFloatingContainerAddDepartment()">+ Add Department</button>';
                    }
                ?>
                
            </div>
        </div>
        <div class="users-table">
            <table id="user-table-search">
                <tr class="sticky-row">
                    <th>#</th>
                    <th>Name</th>
                    <th>User Type</th>
                    <th>Department</th>
                    <th>Handled Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php 
                    require '../php/get_users.php';
                ?>
            </table>
        </div>

        <div class="floating-viewUser-container" id="floating-viewUser-container">
            <div class="bar">
                <span>User Details</span>
                <span class="close-icon" onclick="hideFloatingContainerViewUser()">&#10006;</span>
            </div>
            <div class="user-details" id="user-details">
            </div>
        </div>

        <div class="floating-editUser-container" id="floating-editUser-container">
            <div class="bar">
                <span>Edit User Details</span>
                <span class="close-icon" onclick="hideFloatingContainerEditUser()">&#10006;</span>
            </div>
            <form action="" method="POST">
                <div class="user-details" id="user-details2">

                </div>
                <button type="submit" id="save-button">Save</button>
            </form>
        </div>

    </div>
</body>
<script>
    function hideFloatingContainerViewUser() {
        document.getElementById('floating-viewUser-container').style.display = 'none';
    }

    function showFloatingContainerViewUser(userId) {
        $(document).ready(function() {
            document.getElementById('floating-viewUser-container').style.display = 'block';
            console.log(userId);
            if (userId) {
                $('#user-details').load('../php/view-user.php', {
                    user_id: userId
                });
            }
        });
    }

    function hideFloatingContainerEditUser() {
        document.getElementById('floating-editUser-container').style.display = 'none';
    }

    function showFloatingContainerEditUser(userId) {
        $(document).ready(function() {
            document.getElementById('floating-editUser-container').style.display = 'block';
            console.log(userId);
            if (userId) {
                $('#user-details2').load('../php/edit-user.php', {
                    user_id: userId
                });
            }
        });
    }


</script>
<script>
    function checkCoordinator(select) {
        if (select.value === "coordinator" || select.value === "finance officer") {
            $('#handled_department_input').html('');
            addHandledDepartmentInput();
        } else {
            // If the selected value is not "coordinator", clear the content of handled_department_input
            $('#handled_department_input').html('');
        }
    }

    

    <?php 
        $sql_get_departments = "SELECT department_id, department_name FROM departments WHERE department_id != 0";
        $result_get_departments = $conn->query($sql_get_departments);
        
        $departments = array();
        if ($result_get_departments->num_rows > 0) {
            while ($row = $result_get_departments->fetch_assoc()) {
                $departments[] = $row;
            }
        }
    ?>
    function addHandledDepartmentInput() {
        var departmentSelect = document.createElement("select");
        departmentSelect.name = "handled_department";
        departmentSelect.className = "handled_department";
        departmentSelect.required = true;

        // Create the default option
        var defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "Select Department";
        defaultOption.disabled = true;
        defaultOption.selected = true;
        departmentSelect.appendChild(defaultOption);

        // Department options (retrieved from PHP)
        var departmentOptions = <?php echo json_encode($departments); ?>;

        // Adding options to the select element
        departmentOptions.forEach(function(option) {
            var optionElement = document.createElement("option");
            optionElement.value = option.department_id;
            optionElement.textContent = option.department_name;
            departmentSelect.appendChild(optionElement);
        });

        document.getElementById("handled_department_input").appendChild(departmentSelect);
    }

    // Function to add a new department select
    function addDepartmentSelect() {
        var departmentSelect = document.createElement("select");
        departmentSelect.name = "department[]";
        departmentSelect.className = "department";
        departmentSelect.required = true; // Set required attribute to true

        // Create the default option
        var defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "Select Department";
        defaultOption.disabled = true;
        defaultOption.selected = true;
        departmentSelect.appendChild(defaultOption);

        // Department options (retrieved from PHP)
        var departmentOptions = <?php echo json_encode($departments); ?>;

        // Adding options to the select element
        departmentOptions.forEach(function(option) {
            var optionElement = document.createElement("option");
            optionElement.value = option.department_id;
            optionElement.textContent = option.department_name;
            departmentSelect.appendChild(optionElement);
        });

        document.getElementById("department_selects").appendChild(departmentSelect);
        document.getElementById("department_selects").appendChild(document.createElement("br"));
    }



    // Function to remove the last department select
    function removeDepartmentSelect() {
        var departmentSelects = document.querySelectorAll("#department_selects select");
        if (departmentSelects.length > 1) {
            var lastSelect = departmentSelects[departmentSelects.length - 1];
            lastSelect.parentNode.removeChild(lastSelect);
            var brElements = document.querySelectorAll("#department_selects br");
            var lastBr = brElements[brElements.length - 1];
            lastBr.parentNode.removeChild(lastBr);
        }
    }

    // Check if the element with ID "floating-editUser-container" is visible
    if (document.getElementById('floating-editUser-container').style.display == "block") {
        // Add event listener for the "Add" button
        document.querySelector("#addButton").addEventListener("click", function() {
            addDepartmentSelect();
        });

        // Add event listener for the "Remove" button
        document.querySelector("#removeButton").addEventListener("click", function() {
            removeDepartmentSelect();
        });
    }

    function showFloatingContainerAddDepartment() {
        document.querySelector('.floating-addCategory-container').style.display = 'block';
    }

    changeNavBarTitle('Users');

    document.getElementById('search').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#user-table-search tr:not(.sticky-row):not(.lend-item-row)');

        rows.forEach(function(row) {
            let rowText = row.textContent.toLowerCase();
            if (rowText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

</html>