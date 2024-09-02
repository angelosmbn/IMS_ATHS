<link rel="stylesheet" href="../css/department-request.css">
<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    require '../php/connection.php';
    $borrowable = false;
    if (isset($_POST['department_id'])) {
        $department_id = $_POST['department_id'];
    } else {
        $department_id = 1;
    }
    if (isset($_POST['school_year'])) {
        $school_year = $_POST['school_year'];
    } else {
        $school_year = $_SESSION['school_year'];
    }

    $department_id = intval($department_id); // Assuming $department_id is defined earlier in the script
    $school_year = $conn->real_escape_string($school_year);
    $total = 0;
    $sql = "SELECT * FROM stock_monitoring st
            JOIN departments d ON st.requesting_department_id  = d.department_id
            JOIN items i ON st.item_id = i.item_id
            WHERE st.charged_department_id = $department_id
            AND st.school_year = '$school_year'";
    $result = $conn->query($sql);
    
    echo '<table>';
    echo '<tr class="sticky-row">
                    <th>Date</th>
                    <th>Items</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th colspan="2">Total</th>
                </tr>';
    if ($result->num_rows == 0) {
        echo '<tr>';
        echo '<td colspan="7">No results found</td>';
        echo '</tr>';
    }
    else{
        while ($row = $result->fetch_assoc()) {
            echo '<tr>'; 
            echo '<td>' . $row['release_date'] . '</td>';
            echo '<td>' . $row['item_name'] . '</td>';
            echo '<td>' . $row['item_cost'] . '</td>';
            echo '<td>' . $row['requested_quantity_general'] . '</td>';
            echo '<td>' . $row['unit'] . '</td>';

            if ($row['borrowable'] == 'yes') {
                $borrowable = true;
                if ($row['purpose'] == 'Item Returned') {
                echo '<td>' . $row['item_cost'] * $row['requested_quantity_general'] . '</td>';
                echo '<td id="return-indicator" style="background-color: green;"></td>';
                } else {
                    echo '<td>' . $row['item_cost'] * $row['requested_quantity_general'] . '</td>';
                    echo '<td id="return-indicator" style="background-color: red;"></td>';
                    $total = $total + ($row['item_cost'] * $row['requested_quantity_general']);
                }
            } else {
                echo '<td colspan="2">' . $row['item_cost'] * $row['requested_quantity_general'] . '</td>';
                $total = $total + ($row['item_cost'] * $row['requested_quantity_general']);
            }

            echo '</tr>';
            
        }
        echo '<tr>';
        if ($borrowable) {
            echo '<td colspan="4"></td>';
        } else {
            echo '<td colspan="3"></td>';
        }
        echo '<td>Total:</td>';
        echo '<td colspan="2">' . $total . '</td>';
        echo '</tr>';

    }
    echo '</table>';

?>