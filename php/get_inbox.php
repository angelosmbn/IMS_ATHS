<link rel="stylesheet" href="../css/inbox.css">
<?php 
    $access_level = $_SESSION['access_level'];
    $user_id = $_SESSION['user_id'];
    


    $sql_get_request = "SELECT * FROM requests r
                            JOIN users u ON r.requestor_id = u.user_id
                            JOIN departments d ON r.charged_department = d.department_id
                            WHERE r.request_status = 'confirmation' AND r.requestor_id = '$user_id'";


    $result_get_request = $conn->query($sql_get_request);
    while ($row_get_request = $result_get_request->fetch_assoc()) {
        $requestor_name = $row_get_request['last_name'] . ', ' . $row_get_request['first_name'];

        // Check if middle initial exists and is not empty
        if (!empty($row_get_request['middle_name'])) {
            $requestor_name .= ' ' . strtoupper(substr($row_get_request['middle_name'], 0, 1)) . '.';
        }


        echo '<tr>';
        echo '<td>' . $requestor_name . '</td>';
        echo '<td>' . $row_get_request['department_name'] . '</td>';
        echo '<td>' . $row_get_request['requested_date'] . '</td>';
        echo '<td>' . $row_get_request['needed_date'] . '</td>';
        echo '<td>' . $row_get_request['request_status'] . '</td>';
        echo '<td class="actions">';
        echo '<button class="btn1" data-user-id="' . $row_get_request['request_id'] . '" onclick="showFloatingContainerEditRequest(' . $row_get_request['request_id'] . ')"><i class="fa-solid fa-eye"></i></button>';
        echo '</td>';
        echo '</tr>';

    }

    if ($access_level == 'coordinator' || $access_level == 'finance officer' || $access_level == 'inventory manager') {
        
        if ($access_level == 'coordinator') {
            $handled_department = $_SESSION['handled_department'];
            $sql_get_requests = "SELECT * FROM requests r
                            JOIN users u ON r.requestor_id = u.user_id
                            JOIN departments d ON r.charged_department = d.department_id
                            WHERE r.request_status = 'coordinator approval' AND charged_department = '$handled_department'";
        } else if ($access_level == 'finance officer') {
            $handled_department = $_SESSION['handled_department'];
            $sql_get_requests = "SELECT * FROM requests r
                            JOIN users u ON r.requestor_id = u.user_id
                            JOIN departments d ON r.charged_department = d.department_id
                            WHERE r.request_status = 'finance approval'";
        } else if ($access_level == 'inventory manager') {
            $sql_get_requests = "SELECT * FROM requests r
                            JOIN users u ON r.requestor_id = u.user_id
                            JOIN departments d ON r.charged_department = d.department_id
                            WHERE r.request_status = 'releasing'";
        }

        $result_get_requests = $conn->query($sql_get_requests);
        
        while ($row_get_requests = $result_get_requests->fetch_assoc()) {
            $requestor_id = $row_get_requests['requestor_id'];

            $requestor_name = $row_get_requests['last_name'] . ', ' . $row_get_requests['first_name'];

            // Check if middle initial exists and is not empty
            if (!empty($row_get_requests['middle_name'])) {
                $requestor_name .= ' ' . strtoupper(substr($row_get_requests['middle_name'], 0, 1)) . '.';
            }
            
            echo '<tr>';
            echo '<td>' . $requestor_name . '</td>';
            echo '<td>' . $row_get_requests['department_name'] . '</td>';
            echo '<td>' . $row_get_requests['requested_date'] . '</td>';
            echo '<td>' . $row_get_requests['needed_date'] . '</td>';
            echo '<td>' . $row_get_requests['request_status'] . '</td>';
            echo '<td class="actions">';
            echo '<button class="btn1" data-user-id="' . $row_get_requests['request_id'] . '" onclick="showFloatingContainerEditRequest(' . $row_get_requests['request_id'] . ')"><i class="fa-solid fa-eye"></i></button>';
            echo '</td>';
            echo '</tr>';

        }

    }
?>