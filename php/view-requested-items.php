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
        echo "<script>
                alert('Department ID not set.');
                window.location.href='department-request.php';
             </script>";
    }
    if (isset($_POST['school_year'])) {
        $school_year = $_POST['school_year'];
    } else {
        $school_year = $_SESSION['school_year'];
    }

    $department_id = intval($department_id); // Assuming $department_id is defined earlier in the script
    $school_year = $conn->real_escape_string($school_year);
    $total = 0;
    $totalprice = 0;

    echo '<table>';
    
    if($department_id == 0) {
        echo '<tr class="sticky-row">
                    <th>Department</th>
                    <th>Sub Total</th>
                </tr>';
        $sql_get_departments = "SELECT * FROM departments WHERE department_id != 0 AND department_id != 1 ORDER BY department_name";
        $result_get_departments = $conn->query($sql_get_departments);
        $grandtotal = 0;
        while ($row = $result_get_departments->fetch_assoc()) {
            $department_id = $row['department_id'];
            $department_name = $row['department_name'];

            $sql = "SELECT SUM(item_cost * requested_quantity_general) AS total FROM stock_monitoring
            WHERE charged_department_id = $department_id
            AND school_year = '$school_year'
            AND is_shown = 'yes'
            AND purpose != 'Returned'";

            $result = $conn->query($sql);
            $total = $result->fetch_assoc()['total'];
            $grandtotal += $total;
            echo '<tr>';
            echo '<td>' . $department_name . '</td>';
            echo '<td>' . number_format($total, 2, '.', ',') . '</td>';
            echo '</tr>';
        }
        echo '<tr >';
        echo '<td style="background-color: yellow;"><strong>Grand Total:</strong></td>';
        echo '<td style="background-color: yellow;"><strong>' . number_format($grandtotal, 2, '.', ',') . '</strong></td>';
        echo '</tr>';


    }else{
        echo '<tr class="sticky-row">
                    <th>Date</th>
                    <th>Items</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th colspan="2">Total</th>
                </tr>';
    

        $sql = "SELECT * FROM stock_monitoring st
                JOIN departments d ON st.requesting_department_id  = d.department_id
                JOIN items i ON st.item_id = i.item_id
                WHERE st.charged_department_id = $department_id
                AND st.school_year = '$school_year'
                AND st.purpose != 'Add Stocks'
                AND st.is_shown = 'yes'
                ORDER BY st.monitoring_id ASC";   
        $result = $conn->query($sql);
        
        
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
                echo '<td>' . number_format($row['item_cost'], 2, '.', ',') . '</td>';
                echo '<td>' . $row['requested_quantity_general'] . '</td>';
                echo '<td>' . $row['unit'] . '</td>';

                if ($row['is_borrowable'] == 'yes') {
                    $borrowable = true;
                    if ($row['purpose'] == 'Returned') {
                    $totalprice = $row['item_cost'] * $row['requested_quantity_general'];
                    echo '<td>- ' . number_format($totalprice, 2, '.', ',') . '</td>';
                    echo '<td id="return-indicator" style="background-color: green;"></td>';
                    } else {
                        $totalprice = $row['item_cost'] * $row['requested_quantity_general'];
                        echo '<td>' . number_format($totalprice, 2, '.', ',') . '</td>';
                        echo '<td id="return-indicator" style="background-color: red;"></td>';
                        $total = $total + ($row['item_cost'] * $row['requested_quantity_general']);
                    }
                } else {
                    $totalprice = $row['item_cost'] * $row['requested_quantity_general'];
                    echo '<td colspan="2">' . number_format($totalprice, 2, '.', ',') . '</td>';
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
            echo '<td colspan="2">' . number_format($total, 2, '.', ',') . '</td>';
            echo '</tr>';

        }
    }
    echo '</table>';

?>