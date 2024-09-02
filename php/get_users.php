<link rel="stylesheet" href="../css/users.css">
<?php 
    $sql = "SELECT * FROM users ORDER BY 
            CASE 
                WHEN account_status = 'pending' THEN creation_date 
                WHEN account_status = 'active' THEN last_name 
                ELSE account_status 
            END ASC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $middle_initial = '';
        if (!empty($row['middle_name'])) {
            $middle_initial = strtoupper($row['middle_name'][0]) . '.'; // Get the first character of the middle name and append a period
        }
        $name = ucwords(strtolower($row['last_name'])) . ', ' . ucwords(strtolower($row['first_name'])) . ' ' . $middle_initial; // Concatenate the first name, middle initial, and last name


        if ($row['department'] == '') {
            $department_names = "";
        } else {
            $department_array = explode(', ', $row['department']);
            $department_names = '';
            foreach ($department_array as $department_id) {
                $sql_department = "SELECT department_name FROM departments WHERE department_id = " . $department_id;
                $result_department = $conn->query($sql_department);
                $row_department = $result_department->fetch_assoc();
                $department_names .= $row_department['department_name'] . ', ';
            }
            $department_names = rtrim($department_names, ', ');
        }
    
        if ($row['handled_department'] == 0) {
            $handled_department_name = "";
        } else {
            $sql_get_handled_department = "SELECT department_name FROM departments WHERE department_id = " . $row['handled_department'];
            $result_get_handled_department = $conn->query($sql_get_handled_department);
            $row_get_handled_department = $result_get_handled_department->fetch_assoc();
            $handled_department_name = $row_get_handled_department['department_name'];
        }

        echo '<tr>';
        echo '<td>' . $row['user_id'] . '</td>';
        echo '<td>' . $name . '</td>';
        echo '<td>' . $row['access_level'] . '</td>';
        echo '<td>' . $department_names . '</td>';
        echo '<td>' . $handled_department_name . '</td>';
        echo '<td>' . $row['account_status'] . '</td>';
        echo '<td class="actions">';
        echo '<button class="btn1" data-user-id="' . $row['user_id'] . '" onclick="showFloatingContainerViewUser(' . $row['user_id'] . ')"><i class="fa-solid fa-eye"></i></button>';
        if ($_SESSION['access_level'] == 'admin') {
            echo '<button class="btn2" data-user-id="' . $row['user_id'] . '" onclick="showFloatingContainerEditUser(' . $row['user_id'] . ')"><i class="fa-solid fa-pencil"></i></button>';
        }
        echo '</td>';
        echo '</tr>';

    }
?>