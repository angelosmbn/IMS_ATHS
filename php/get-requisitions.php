<link rel="stylesheet" href="../css/inbox.css">
<?php 
    $access_level = $_SESSION['access_level'];
    $user_id = $_SESSION['user_id'];
    


    $sql_get_request = "SELECT * FROM requests r
                            JOIN users u ON r.requestor_id = u.user_id
                            JOIN departments d ON r.charged_department = d.department_id
                            WHERE r.request_status = 'completed' OR r.request_status = 'confirmation'";


    $result_get_request = $conn->query($sql_get_request);
    while ($row_get_request = $result_get_request->fetch_assoc()) {
        
        $requestId = $row_get_request['request_id'];
        //$encryptedRequestId = $row_get_request['request_id'];
        $requestor_name = $row_get_request['first_name'];

        if (!empty($row_get_request['middle_name'])) {
            $requestor_name .= ' ' . strtoupper(substr($row_get_request['middle_name'], 0, 1)) . '.';
        }

        $requestor_name .= ' ' . $row_get_request['last_name'];

        echo '<tr>';
        echo '<td>' . $requestor_name . '</td>';
        echo '<td>' . $row_get_request['requested_date'] . '</td>';
        echo '<td>' . $row_get_request['received_date'] . '</td>';
        echo '<td>' . $row_get_request['department_name'] . '</td>';
        echo '<td>' . $row_get_request['request_status'] . '</td>';
        echo '<td>' . $row_get_request['school_year'] . '</td>';
        echo '<td class="actions">';
        echo '<button class="btn1" data-user-id="' . $row_get_request['request_id'] . '" onclick="showFloatingContainerEditRequest(' . $row_get_request['request_id'] . ')"><i class="fa-solid fa-eye"></i></button>';
        if ($row_get_request['request_status'] == 'completed' OR $row_get_request['request_status'] == 'confirmation') {
            echo '<button class="btn2" data-request-id="' . $requestId . '" onclick="openPDF(this)"><i class="fa-regular fa-file-pdf"></i></button>';
        }
        
        echo '</td>';
        echo '</tr>';


    }
?>