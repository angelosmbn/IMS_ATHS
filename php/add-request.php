<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {
        // Check if all required fields are set
        if (isset($_POST['item_id']) && isset($_POST['quantity_requested']) && isset($_POST['department_charged'])) {
            // Get the submitted data
            $itemIds = $_POST['item_id'];
            $quantities = $_POST['quantity_requested'];
            $departments = $_POST['department_charged'];
            $selected_department = $_POST['selected_department'];
            $date_needed = $_POST['date_needed'];
            $purpose = $_POST['purpose'];

            // Get the user ID from the session
            $requestor_id = $_SESSION['user_id'];
            $requestor_level = $_SESSION['access_level'];
            $coordinator_id = 1; // Temporary coordinator ID use sql to get it.
            $finance_id = 1; // Temporary finance ID use sql to get it.
            $school_year = $_SESSION['school_year']; // Temporary school year use sql to get it. get the latest school year
            $requested_date = date('Y-m-d H:i:s'); // Get the current date and time
            //$request_status = "coordinator approval"; // Set the request status to pending

            // Get the coordinator ID
            $sql_get_coordinator_id = "SELECT * FROM users WHERE (access_level = 'coordinator' OR access_level = 'finance officer') AND FIND_IN_SET('$selected_department', handled_department)";
            $result = $conn->query($sql_get_coordinator_id);

            // Check if there are any coordinators found
            if ($result->num_rows > 0) {
                // If there are multiple coordinators for the selected department, you might want to handle that
                // For now, let's just fetch the first coordinator found
                $row = $result->fetch_assoc();
                $coordinator_id = $row['user_id'];
            } else {
                // No coordinator found for the selected department
                $coordinator_id = null;
            }


            // Get the finance ID
            $sql_get_finance_id = "SELECT * FROM users WHERE access_level = 'finance officer'";
            $result = $conn->query($sql_get_finance_id);

            // Check if there are any finance officers found
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $finance_id = $row['user_id'];
            } elseif ($result->num_rows > 1) {
                echo "<script>alert('Multiple finance officers found.');</script>";
                return;
            } else {
                echo "<script>alert('Finance officer not found.');</script>";
                return;
            }

            //6 finance
            //3 coordinator

            if ($requestor_id === $coordinator_id || $coordinator_id === $finance_id) {
                $request_status = "finance approval";
            } else if ($coordinator_id !== null) {
                $request_status = "coordinator approval";
            } else {
                $request_status = "finance approval";
            }



                $sql_insert_request = "INSERT INTO requests (requestor_id, coordinator_id, finance_id, school_year, requested_date, request_status, needed_date, request_description, charged_department) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_insert_request);
                $stmt->bind_param("iiisssssi", $requestor_id, $coordinator_id, $finance_id, $school_year, $requested_date, $request_status, $date_needed, $purpose, $selected_department);
                //echo "<script>alert('Coordinator ID: $coordinator_id');</script>";


            $stmt->execute();

            if($conn->affected_rows > 0) {
                // Get the last inserted request ID
                $request_id = $conn->insert_id;

                // Process each item
                for ($i = 0; $i < count($itemIds); $i++) {
                    // Get the data for the current item
                    $itemId = $itemIds[$i];
                    $quantity = $quantities[$i];
                    $department = ($departments[$i] != 0) ? $selected_department : $departments[$i];

                    //sql
                    $sql_insert_request_items = "INSERT INTO requested_items (request_id_fk, item_id_fk, request_quantity, requesting_department_id) VALUES 
                    ('$request_id', '$itemId', '$quantity', '$department')";
                    $conn->query($sql_insert_request_items);
                    //echo "<script>alert('$itemId - $quantity - $department');</script>";
                }
                if($conn->affected_rows > 0) {
                    echo "<script>alert('Request submitted successfully.');</script>";
                    echo "<script>window.location.href = 'inventory.php';</script>";
                } else {
                    echo "<script>alert('Request was not able to be inserted to request table.');</script>";
                } 
            } else {
                echo "script>alert('Request was not able to be inserted to request table.');</script>";
            }
        } else {
            echo "script>alert('Please fill out all required fields.');</script>";
        }
    }
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/add-request.css">
    <link rel="stylesheet" href="../fontawesome-free-6.5.1-web/css/all.min.css">
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
    
    <title>Document</title>
</head>
<body>
    <div class="floating-addRequest-container" id="floating-addRequest-container">
        <div class="bar">
            <span>Request Items</span>
            <span class="close-icon" onclick="hideFloatingContainerAddRequest()">&#10006;</span>
        </div>
        <div class="request-department-input" id="request-department-input">
            Select Department:
            <select name="department" id="department" required>
                <?php 
                    $sql_get_departments = "SELECT * FROM users WHERE user_id = '" . $_SESSION['user_id'] . "'";
                    $result = $conn->query($sql_get_departments);
                    $row = $result->fetch_assoc();
                    $department = $_SESSION['department'];
                    foreach ($department as $dept) {
                        $sql_get_department_name = "SELECT * FROM departments WHERE department_id = '$dept'";
                        $result = $conn->query($sql_get_department_name);
                        $row = $result->fetch_assoc();
                        $dept_name = $row['department_name'];
                        echo '<option value="' . $dept . '">' . $dept_name . '</option>';
                    }
                ?>
            </select>
        </div>
        <div class="form-container">
            <div>
                <button class="next-button" type="submit" id="next-button" name="next-button" disabled>Next</button>
                <button class="next-button1" id="next-button1" name="next-button1">Next</button>
                <button class="back-button" id="back-button">Back</button>
                <button class="back-button1" id="back-button1">Back</button>
                <input type="text" name="search" id="search" placeholder="Search here">
                <button class="submit-button" type="submit" name="submit-button" id="submit-button">Submit</button>
            </div>
            
            <div class="add-request-form" method="POST" action="">
                
            
                <div class="item-select" id="item-select">
                        <table class="show-items">
                            <tr>
                                <th colSpan="6">SELECT THE ITEMS YOU WANT TO REQUEST</th>
                            </tr>
                            <tr class="sticky-row">
                                <th>✓</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Description</th>
                                <th>Stocks</th>
                                <th>Price</th>
                            </tr>
                                <?php 
                                    $sql_get_items = "SELECT * FROM items WHERE item_status = 'available' AND borrowable = 'no'";
                                    $result = $conn->query($sql_get_items);
                                    
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<tr>';
                                        echo '<td class="checkbox-td"><input type="checkbox" name="selected_items[]" value="' . $row['item_id'] . '"></td>';
                                        echo '<td>' . $row['item_name'] . '</td>';
                                        echo '<td>' . $row['item_brand'] . '</td>';
                                        echo '<td>' . $row['item_description'] . '</td>';
                                        echo '<td>' . $row['item_stocks'] . '</td>';
                                        echo '<td>' . $row['item_price'] . '</td>';
                                        echo '</tr>';
                                    }

                                    echo '<tr class="sticky-row-indication">';
                                    echo '<th colspan="6">LEND ITEM ONLY</th>';
                                    echo '</tr>';
                                    //for ($i = 0; $i < 10; $i++) {

                                    
                                    $sql_get_items = "SELECT * FROM items WHERE item_status = 'available' AND borrowable = 'yes'";
                                    $result = $conn->query($sql_get_items);
                                    

                                    
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<tr class="borrowable-item">';
                                        echo '<td class="checkbox-td"><input type="checkbox" class="borrowable-checkbox" name="selected_items[]" value="' . $row['item_id'] . '"></td>';
                                        echo '<td>' . $row['item_name'] . '</td>';
                                        echo '<td>' . $row['item_brand'] . '</td>';
                                        echo '<td>' . $row['item_description'] . '</td>';
                                        echo '<td>' . $row['item_stocks'] . '</td>';
                                        echo '<td>' . $row['item_price'] . '</td>';
                                        echo '</tr>';
                                    }
                                //} 
                                ?>
                        </table>      
                </div>

                <form class="request-form" id="request-form" method="POST" action="" >
                    <div class="request-form-inputs" id="request-form-inputs">
                        <table>
                            <tr>
                                <td id="date-needed-td">
                                    Date needed: <input type="date" name="date_needed" required>
                                </td>
                                <td>
                                    Purpose: <input type="text" name="purpose" placeholder="Purpose" required>
                                </td>
                                <input type="hidden" id="selectedItems" name="selectedItems">
                            <input type="hidden" id="quantities" name="quantities">
                            </tr>
                        </table>
                    </div>
                    
                    <div class="request-information" id="request-information">
                    </div>
                    
                    <div class="final-request" id="final-request">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
<?php 
        // Your existing PHP code...
        $department_ids = $_SESSION['department']; //value of this is an array
        $department_ids_str = implode(', ', $department_ids);
        $sql_get_accumulable = "SELECT * FROM accumulable WHERE department_id IN ($department_ids_str)";
        // Fetch item details from the database
        $sql_get_items = "SELECT * FROM items WHERE item_status = 'available'";
        $result = $conn->query($sql_get_items);

        // Initialize an array to hold item details
        $itemDetails = array();

        // Fetch item details from the result set and store them in the array
        while ($row = $result->fetch_assoc()) {
            $itemId = $row['item_id'];
            $itemDetails[$itemId] = array(
                'name' => $row['item_name'],
                'brand' => $row['item_brand'],
                'description' => $row['item_description'],
                'stocks' => $row['item_stocks'],
                'stocks1' => $row['item_stocks'],
                'price' => $row['item_price'],
                'borrowable' => $row['borrowable'],
                'unit' => $row['unit'],
                'department_id' => $department_ids_str,
                // Add more details as needed
            );
        }

        // Convert the PHP array to JSON
        $itemDetailsJson = json_encode($itemDetails);
        // Output the JSON data into a JavaScript variable
        echo "var itemDetails = " . $itemDetailsJson . ";";
    ?>
    // Retrieve selected items from checkboxes
    function getSelectedItems() {
        var selectedItems = [];
        var checkboxes = document.querySelectorAll('input[name="selected_items[]"], .borrowable-checkbox');
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                selectedItems.push(checkbox.value);
            }
        });
        return selectedItems;
    }


    function nextBtnClickEvent() {
        document.addEventListener('DOMContentLoaded', function() {
            const btn2Elements = document.querySelectorAll('.next-button');
            btn2Elements.forEach(btn => {
                btn.addEventListener('click', function() {
                    var selectedItems = [];
                    var checkboxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
                    // Iterate over each checked checkbox
                    checkboxes.forEach(function(checkbox) {
                        // Push the value of the checked checkbox into the selectedItems array
                        selectedItems.push(checkbox.value);
                    });
                    console.log(selectedItems);
                    // Now you have the value of item_id in the itemId variable
                    showSelectedItems(selectedItems); // Pass itemId to the function
                });
            });
        });
    }

    function showSelectedItems(selectedItems) {
        $(document).ready(function() {
            console.log(selectedItems); // Make sure itemId is not undefined
            if (selectedItems) {
                // Get the select element by its id
                var selectElement = document.getElementById('department');
                
                // Get the selected option from the select element
                var selectedOption = selectElement.options[selectElement.selectedIndex];
                
                // Get the value of the selected option
                var departmentId = selectedOption.value;

                $('#request-information').load('../php/add-request-quantity.php', {
                    selected_items: selectedItems,
                    department_id: departmentId
                });
            }
        });
    }


    function nextBtn1ClickEvent() {
        document.addEventListener('DOMContentLoaded', function() {
            const btn2Elements = document.querySelectorAll('.next-button1');

            btn2Elements.forEach(btn => {
                btn.addEventListener('click', function() {
                    

                    //console.log(selectedItems); // Log selected items for debugging (optional)
                    showRequestInformation(); // Pass selected items object
                });
            });
        });
    }

    function showRequestInformation() {
        $(document).ready(function() {
            //console.log(selectedItems); // Make sure itemId is not undefined
            //if (selectedItems) {
                // Get the select element by its id
                var selectElement = document.getElementById('department');
                
                // Get the selected option from the select element
                var selectedOption = selectElement.options[selectElement.selectedIndex];
                
                // Get the value of the selected option
                var departmentId = selectedOption.value;

                // Get the date needed value
                var dateValue = document.querySelector('input[name="date_needed"]').value;
                var purposeValue = document.querySelector('input[name="purpose"]').value;

                const quantityInputs = document.querySelectorAll('input[name^="quantity"]');
                    const selectedItems = {}; // Object to store item_id and quantity pairs

                    quantityInputs.forEach(input => {
                        const itemId = input.name.split('[')[1].split(']')[0];
                        const quantity = parseInt(input.value);

                        // Check if itemId is not an empty string and quantity is valid
                        if (itemId !== "" && quantity > 0) {
                            selectedItems[itemId] = quantity;
                        }
                    });
                    console.log(selectedItems);
                $('#final-request').load('../php/add-request-final.php', {
                    selected_items: selectedItems,
                    department_id: departmentId,
                    date_needed: dateValue,
                    purpose: purposeValue
                });
            //}
        });
    }

    nextBtnClickEvent();
    nextBtn1ClickEvent();
    const backButton = document.getElementById('back-button');

    backButton.addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('item-select').style.transform = 'translateX(0)';
        document.getElementById('item-select').style.opacity = '1';
        document.getElementById('request-information').style.transform = 'translateX(100%)';
        document.getElementById('request-information').style.opacity = '0';
        document.getElementById('search').style.display = 'block';
        document.getElementById('back-button').style.display = 'none';
        document.getElementById('next-button').style.display = 'block';
        document.getElementById('next-button1').style.display = 'none';
        document.getElementById('request-form-inputs').style.opacity = '0';
        document.getElementById('request-form-inputs').style.transform = 'translateX(100%)';
        document.getElementById('request-department-input').style.display = 'block';
    });


    const nextButton = document.getElementById('next-button');

    nextButton.addEventListener('click', function(event) {
        event.preventDefault();
        
        // Hide elements and perform other actions
        document.getElementById('item-select').style.transform = 'translateX(-100%)';
        document.getElementById('item-select').style.opacity = '0';
        document.getElementById('request-information').style.transform = 'translateX(-100%)';
        document.getElementById('request-information').style.opacity = '1';
        document.getElementById('search').style.display = 'none';
        document.getElementById('back-button').style.display = 'block';
        document.getElementById('next-button').style.display = 'none';
        document.getElementById('next-button1').style.display = 'block';
        document.getElementById('request-form-inputs').style.opacity = '1';
        document.getElementById('request-form-inputs').style.transform = 'translateX(-100%)';
        document.getElementById('request-department-input').style.display = 'none';
        // Retrieve selected items and append rows to the table
        //var selectedItems = getSelectedItems();
        //appendRowsToTable(selectedItems);
    });


    function moveToFinalStep() {
        document.getElementById('request-information').style.transform = 'translateX(0)';
        document.getElementById('request-information').style.opacity = '0';
        document.getElementById('back-button').style.display = 'none';
        document.getElementById('next-button1').style.display = 'none';
        document.getElementById('back-button1').style.display = 'block';
        document.getElementById('request-form-inputs').style.opacity = '0';
        document.getElementById('request-form-inputs').style.transform = 'translateX(100%)';
        document.getElementById('final-request').style.opacity = '1';
        document.getElementById('final-request').style.transform = 'translateX(-100%)';
        document.getElementById('submit-button').style.display = 'block';
        //populateFinalRequestTable();
    }

    const backButton1 = document.getElementById('back-button1');

    backButton1.addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('request-information').style.transform = 'translateX(-100%)';
        document.getElementById('request-information').style.opacity = '1';
        document.getElementById('back-button').style.display = 'block';
        document.getElementById('next-button1').style.display = 'block';
        document.getElementById('back-button1').style.display = 'none';
        document.getElementById('request-form-inputs').style.opacity = '1';
        document.getElementById('request-form-inputs').style.transform = 'translateX(-100%)';
        document.getElementById('final-request').style.opacity = '0';
        document.getElementById('final-request').style.transform = 'translateX(100%)';
        document.getElementById('submit-button').style.display = 'none';
    });

    const submitButton = document.getElementById('submit-button');

    submitButton.addEventListener('click', function(event) {
        event.preventDefault();
        // Find the form element
        const form = document.getElementById('request-form'); // Replace 'your-form-id' with the actual ID of your form
        // Submit the form
        form.submit();
    });




    function toggleSubmitButton() {
        var selectedItems = document.querySelectorAll('input[name="selected_items[]"]:checked');
        var submitButton = document.querySelector('[name="next-button"]');
        submitButton.disabled = selectedItems.length === 0;
        submitButton.style.backgroundColor = submitButton.disabled ? 'gray' : '';
    }

    // Add event listener to each checkbox to call toggleSubmitButton() on change
    var checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', toggleSubmitButton);
    });

    // Call toggleSubmitButton() initially to set initial state of submit button
    toggleSubmitButton();

    function hideFloatingContainerAddRequest() {
        document.getElementById('floating-addRequest-container').style.display = 'none';
    }

    function performSearch() {
        var search = document.getElementById('search').value.toLowerCase();
        var rows = document.querySelectorAll('.show-items tr');
        rows.forEach(row => {
            const itemColumn = row.querySelector('td:nth-child(2)');
            const itemBrand = row.querySelector('td:nth-child(3)');
            const itemDescription = row.querySelector('td:nth-child(4)');
            if (itemColumn) {
                const itemName = itemColumn.textContent.toLowerCase();
                const itemBrandName = itemBrand.textContent.toLowerCase();
                const itemDescriptionName = itemDescription.textContent.toLowerCase();
                if (itemName.includes(search) || itemBrandName.includes(search) || itemDescriptionName.includes(search)) {
                    row.style.display = '';
                    row.style.backgroundColor = 'white';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    document.getElementById('search').addEventListener('input', performSearch);

    
    // Function to validate inputs before proceeding to the next step
    function validateInputs() {
        var dateNeeded = document.querySelector('input[name="date_needed"]').value;
        var inputs = document.querySelectorAll('input[type="number"]');
        var valid = true;
        
        // Check if date_needed is filled
        if (dateNeeded.trim() === '') {
            alert("Please enter the date needed.");
            return false;
        }
        
        // Check if all quantity inputs are within range
        inputs.forEach(function(input) {
            var max = parseInt(input.getAttribute('max'));
            var value = parseInt(input.value);
            
            if (value > max) {
                alert("The input value exceeds the maximum allowed.");
                valid = false;
            }
        });
        
        return valid;
    }

// Add event listener to the "Next1" button to validate inputs before proceeding
document.getElementById('next-button1').addEventListener('click', function(event) {
    // Prevent the default behavior of the button
    event.preventDefault();
    
    // Validate inputs before proceeding
    if (validateInputs()) {
        console.log("All inputs are valid. Proceeding to the next step.");
        moveToFinalStep();
    }
});




</script>
</html>