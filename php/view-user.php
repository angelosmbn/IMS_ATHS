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

    $department_array = explode(',', $row['department']);
    $department_names = ''; // Changed variable name to avoid conflict
    foreach ($department_array as $department_id) { // Changed variable name here too
        $sql_department = "SELECT department_name FROM departments WHERE department_id = " . $department_id;
        $result_department = $conn->query($sql_department);
        $row_department = $result_department->fetch_assoc();
        $department_names .= $row_department['department_name'] . ', '; // Append department names
    }

    // Remove the last comma and any trailing whitespace
    $department_names = rtrim($department_names, ', ');

    if ($row['handled_department'] == '') {
        $handled_department_name = "";
    } else {
        $sql_get_handled_department = "SELECT department_name FROM departments WHERE department_id = " . $row['handled_department'];
        $result_get_handled_department = $conn->query($sql_get_handled_department);
        $row_get_handled_department = $result_get_handled_department->fetch_assoc();
        $handled_department_name = $row_get_handled_department['department_name'];
    }


    echo '<tr>';
    echo '<td>' . $row['user_id'] . '</td>';
    echo '<td>' . $row['first_name'] . '</td>';
    echo '<td>' . $row['middle_name'] . '</td>';
    echo '<td>' . $row['last_name'] . '</td>';
    echo '<td>' . $row['access_level'] . '</td>';
    echo '<td>' . $department_names . '</td>';
    echo '<td>' . $handled_department_name . '</td>';
    echo '<td>' . $row['email'] . '</td>';
    echo '<td>' . $row['contact'] . '</td>';
    echo '<td>' . $row['account_status'] . '</td>';
    echo '</tr>';
?>
</table>