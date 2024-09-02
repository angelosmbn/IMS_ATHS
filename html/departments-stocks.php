<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != "") {

    } else {
        echo "<script>alert('Please login first!')</script>";
        echo "<script>window.location.href='login.php';</script>";
    }
    require '../php/connection.php';
    require 'navigation-bar.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['accumulable-quantity']) && isset($_POST['item-id'])) {
            $accumulableQuantities = $_POST['accumulable-quantity'];
            $itemIds = $_POST['item-id'];
            $departmentId = $_POST['department-id'];
            $numChanges = 0; // Variable to track the number of changes
        
            if (is_array($accumulableQuantities)) {
                for ($i = 0; $i < count($accumulableQuantities); $i++) {
                    $accumulableQuantity = $accumulableQuantities[$i];
                    $itemId = $itemIds[$i];

                    $sql_get_accumulable = "SELECT * FROM accumulable WHERE department_id_fk = " . $departmentId . " AND item_id_fk = " . $itemId;
                    $result_accumulable = $conn->query($sql_get_accumulable);
                    $row_accumulable = $result_accumulable->fetch_assoc();
                    $prevAccumulable = $row_accumulable['accumulable_quantity'];


                    if ($prevAccumulable != $accumulableQuantity) {
                        $sql_update = "UPDATE accumulable SET accumulable_quantity = " . $accumulableQuantity . " WHERE department_id_fk = " . $departmentId . " AND item_id_fk = " . $itemId;
                        $result_insert = $conn->query($sql_update);
                        $numChanges += $conn->affected_rows;
                    }
                    
                    
                }

            }
        
            if ($numChanges > 0) {
                echo "<script>alert('Changes saved.');</script>";
            } else {
                echo "<script>alert('No changes made.');</script>";
            }
            echo "<script>window.location.href='departments-stocks.php?departmentId=" . $departmentId . "';</script>";
        }
        echo "<script>window.location.href='departments-stocks.php';</script>";
    }


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/departments-stocks.css">
    <title>Document</title>
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
</head>
<body>
    <div class="departments-stocks-container" id="departments-stocks-container">
        <div class="settings-container">
            <input type="text" name="search" id="search">
            <select name="department" id="department" onchange="departmentChanged()">
                <option value="">Select Department</option>
                <?php 
                    $sql = "SELECT * FROM departments WHERE department_id != 0";
                    $result = $conn->query($sql);
                    if($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $selected = ''; // Initialize the selected attribute
                            if(isset($_GET['departmentId']) && $_GET['departmentId'] == $row['department_id']) {
                                $selected = 'selected'; // Set selected attribute if departmentId matches
                            }
                            echo "<option value='".$row['department_id']."' ".$selected.">".$row['department_name']."</option>";
                        }
                    }
                ?>

            </select>
            
            <div class="buttons">
                <button id="edit-button" onclick="activateEdit()">Edit</button>
                <button id="cancel-button" onclick="deactivateEdit()">Cancel</button>
                <button id="save-button" type="submit">Save</button>
            </div>
        </div>
        <form action="" method="POST">
        <div class="departments-items-stocks">
            
                <table id="department-stocks">
                    <tr class="sticky-row">
                        <th>Item</th>
                        <th>Brand</th>
                        <th>Description</th>
                        <th class="small-width">Accumulable</th>
                        <th class="small-width">Consumed</th>
                        <th class="small-width">Remaining</th>
                    </tr>
                    <tr id="select-a-department">
                        <td colspan="6">Select a department view record.</td>
                    </tr>
                </table>
            
        </div>
        </form>
    </div>
</body>
<script>
    


    function activateEdit() {
        document.getElementById('edit-button').style.display = 'none';
        document.getElementById('cancel-button').style.display = 'block';

        // Select all input elements with name 'accumulable-quantity[]'
        var accumulableInputs = document.querySelectorAll('input[name="accumulable-quantity[]"]');
        // Loop through each input and set readOnly to false
        accumulableInputs.forEach(function(input) {
            input.readOnly = false;
        });

        document.getElementById('department').disabled = true;
    }

    function deactivateEdit() {
        var departmentInput = document.getElementById('department');
        var departmentId = departmentInput.value;

        document.getElementById('cancel-button').style.display = 'none';
        document.getElementById('edit-button').style.display = 'block';

        // Select all input elements with name 'accumulable-quantity[]'
        var accumulableInputs = document.querySelectorAll('input[name="accumulable-quantity[]"]');
        // Loop through each input and set readOnly to true
        accumulableInputs.forEach(function(input) {
            input.readOnly = true;
        });

        document.getElementById('department').disabled = false;
        
        $(document).ready(function() {
            console.log(departmentId); // Make sure departmentId is not undefined
            if (departmentId) {
                $('#department-stocks').load('../php/department-stocks-table.php', {
                    department_id: departmentId
                });
            }
        });
    }


    <?php if(isset($_GET['departmentId'])) { ?>
            departmentChanged(); // Call departmentChanged() function
    <?php } ?>


    function departmentChanged() {
        var departmentInput = document.getElementById('department');
        var departmentId = departmentInput.value;
        var editButton = document.getElementById('edit-button');
        var saveButton = document.getElementById('save-button');
        var departmentStocksTable = document.getElementById('department-stocks');
        var selectDepartmentMsg = document.getElementById('select-a-department');

        // Check if departmentId is 'none'
        if (departmentId === 'none') {
            editButton.style.display = 'none';
            saveButton.style.display = 'none';
            
            // Clear existing rows in department-stocks table
            departmentStocksTable.innerHTML = '';

            // Insert the rows for selecting a department
            departmentStocksTable.innerHTML += `
                <tr class="sticky-row">
                    <th>Item</th>
                    <th>Brand</th>
                    <th>Description</th>
                    <th class="small-width">Accumulable</th>
                    <th class="small-width">Consumed</th>
                </tr>
                <tr id="select-a-department">
                    <td colspan="5">Select a department to view records.</td>
                </tr>
            `;
            return; // Exit the function early
        }

        // If departmentId is not 'none', display buttons and load department stocks
        editButton.style.display = 'block';
        saveButton.style.display = 'block';

        $(document).ready(function() {
            console.log(departmentId); // Make sure departmentId is not undefined
            if (departmentId) {
                $('#department-stocks').load('../php/department-stocks-table.php', {
                    department_id: departmentId
                });
            }
        });
    }



    $(document).ready(function() {
        $('#save-button').click(function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Validation flag
            var isValid = true;

            // Check each input field for validation
            $('input[type="number"]').each(function() {
                // Get the input value
                var inputValue = parseInt($(this).val());
                //var minValue = parseInt($(this).attr('min')); // Get the minimum value
                //var maxValue = parseInt($(this).attr('max')); // Get the maximum value

                // Check if the input is not within the specified range
                if (isNaN(inputValue))// || inputValue < minValue || inputValue > maxValue) 
                {
                    isValid = false;
                    // Highlight the invalid input field (you can add your own styling)
                    $(this).css('border', '1px solid red');
                }
            });

            // If all inputs are valid, submit the form
            if (isValid) {
                $('form').submit(); // Submit the form
            } else {
                // Optionally, display an error message or take other actions
                alert('Please enter valid numbers within the specified range in all input fields.');
            }
        });
    });


    changeNavBarTitle('Department Stocks');

    document.getElementById('search').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#department-stocks tr:not(.sticky-row):not(.lend-item-row)');

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