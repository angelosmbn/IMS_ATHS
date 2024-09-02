<link rel="stylesheet" href="../css/users.css">

<table>
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Access Level</th>
        <th>Department</th>
        <th>Handled Department</th>
        <th>Email</th>
        <th>Contact</th>
        <th>Account Status</th>
    </tr>
<?php 
    require '../php/connection.php';
    $userId = $_POST['user_id'];
    $sql = "SELECT * FROM users WHERE user_id = " . $userId;
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $department_names = ""; // Initialize $department_names variable

    if (!empty($row['department'])) { // Check if department is not empty
        $department_array = explode(',', $row['department']);
        $department_names = '';
        foreach ($department_array as $department_id) {
            $sql_department = "SELECT department_name FROM departments WHERE department_id = " . $department_id;
            $result_department = $conn->query($sql_department);
            $row_department = $result_department->fetch_assoc();
            $department_names .= $row_department['department_name'] . ', ';
        }
        $department_names = rtrim($department_names, ', ');
    }

    echo '<tr>';
    echo '<td>' . $row['user_id'] . '</td>';
    echo '<td>' . $row['first_name'] . '</td>';
    echo '<td>' . $row['middle_name'] . '</td>';
    echo '<td>' . $row['last_name'] . '</td>';
    
    echo '<td><select name="access_level" id="access_level" onchange="checkCoordinator(this)">';

    $access_levels = array("employee", "admin", "coordinator", "finance officer", "inventory manager");

    foreach ($access_levels as $level) {
        $selected = ($level == $row['access_level']) ? 'selected' : '';
        echo '<option value="' . $level . '" ' . $selected . '>' . ucfirst($level) . '</option>';
    }

    echo '</select></td>';
    
    echo '<td id="department_selects">';
    if (!empty($department_array)) {
        foreach ($department_array as $department_id) {
            $sql_department = "SELECT department_name FROM departments WHERE department_id = " . $department_id;
            $result_department = $conn->query($sql_department);
            $row_department = $result_department->fetch_assoc();
            echo '<select name="department[]" id="department" required>';
            $sql = "SELECT * FROM departments WHERE department_id != 0";
            $result2 = $conn->query($sql);
            while ($row2 = $result2->fetch_assoc()) {
                $selected = ($row2['department_id'] == $department_id) ? 'selected' : '';
                echo '<option value="' . $row2['department_id'] . '" ' . $selected . '>' . $row2['department_name'] . '</option>';
            }
            echo '</select><br>';
        }
    } else {
        echo '<select name="department[]" id="department" required>';
        echo '<option value="" disabled selected>Select Department</option>';
        $sql = "SELECT * FROM departments WHERE department_id != 0";
        $result2 = $conn->query($sql);
        while ($row2 = $result2->fetch_assoc()) {
            echo '<option value="' . $row2['department_id'] . '">' . $row2['department_name'] . '</option>';
        }
        echo '</select>';
    }
    
    echo '<input type="hidden" name="user_id" value="' . $row['user_id'] . '">';
    echo '</td>';

    echo '<td id="handled_department_input">';
    if ($row['access_level'] == 'coordinator' || $row['access_level'] == 'finance officer') {
        echo '<select name="handled_department" id="handled_department">';
        $sql_get_departments = "SELECT * FROM departments WHERE department_id != 0";
        $result_get_departments = $conn->query($sql_get_departments);
        while ($row_get_departments = $result_get_departments->fetch_assoc()) {
            $selected = ($row_get_departments['department_id'] == $row['handled_department']) ? 'selected' : '';
            echo '<option value="' . $row_get_departments['department_id'] . '" ' . $selected . '>' . $row_get_departments['department_name'] . '</option>';
        }
        echo '</select>';
    }
    echo '</td>';

    echo '<td>' . $row['email'] . '</td>';
    echo '<td>' . $row['contact'] . '</td>';
    echo '<td><select name="account_status" id="account_status">';
    echo '<option value="active" ' . ($row['account_status'] == 'active' ? 'selected' : '') . '>Active</option>';
    echo '<option value="disabled" ' . ($row['account_status'] == 'disabled' ? 'selected' : '') . '>Disabled</option>';
    echo '<option value="pending" ' . ($row['account_status'] == 'pending' ? 'selected' : '') . '>Pending</option>';
    echo '</select></td>';
    echo '</tr>';

?>
<tr class="add-minus-btn">
    <td colspan="5">

    </td>
    <td>
        <button type="button" id="addButton" onclick="addDepartmentSelect()">+</button>
        <button type="button" id="removeButton" onclick="removeDepartmentSelect()">-</button>
    </td>
    <td colspan="4">

    </td>
</tr>
</table>

