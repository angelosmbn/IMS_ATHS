<link rel="stylesheet" href="../css/returns.css">
<?php 
    $access_level = $_SESSION['access_level'];
    $user_id = $_SESSION['user_id'];
    

    if ($access_level == 'inventory manager') {
        $sql_get_request = "SELECT * FROM requested_items ri
                            JOIN items i ON ri.item_id_fk = i.item_id
                            JOIN departments d ON ri.requesting_department_id = d.department_id
                            JOIN requests r ON ri.request_id_fk = r.request_id
                            JOIN users u ON r.requestor_id = u.user_id
                            WHERE i.borrowable = 'yes' AND 
                            (r.request_status = 'completed' OR r.request_status = 'confirmation')
                            ORDER BY 
                                CASE
                                    WHEN ri.return_status = 'marked' THEN 1 
                                    WHEN ri.return_status = 'returned' THEN 3
                                    ELSE 2
                                END,
                                r.released_date ASC";
    }else{
        $sql_get_request = "SELECT * FROM requested_items ri
                            JOIN items i ON ri.item_id_fk = i.item_id
                            JOIN departments d ON ri.requesting_department_id = d.department_id
                            JOIN requests r ON ri.request_id_fk = r.request_id
                            JOIN users u ON r.requestor_id = u.user_id
                            WHERE r.requestor_id = '$user_id' AND 
                            i.borrowable = 'yes' AND 
                            r.request_status = 'completed'
                            ORDER BY 
                                CASE
                                    WHEN ri.return_status = 'marked' THEN 1 
                                    WHEN ri.return_status = 'returned' THEN 3
                                    ELSE 2
                                END,
                                r.released_date ASC";
    }
    

    $result_get_request = $conn->query($sql_get_request);
    while ($row_get_request = $result_get_request->fetch_assoc()) {
        $requestor_name = $row_get_request['last_name'] . ', ' . $row_get_request['first_name'];

        // Check if middle initial exists and is not empty
        if (!empty($row_get_request['middle_name'])) {
            $requestor_name .= ' ' . strtoupper(substr($row_get_request['middle_name'], 0, 1)) . '.';
        }

        echo '<tr>';
        if($row_get_request['return_status'] == 'no'){
            echo '<td style="background-color: red;"></td>';
        } elseif($row_get_request['return_status'] == 'marked'){
            echo '<td style="background-color: yellow;"></td>';
        } elseif($row_get_request['return_status'] == 'returned'){
            echo '<td style="background-color: green;"></td>';
        }else {
            echo '<td></td>';
        }
        echo '<td>' . $requestor_name . '</td>';
        echo '<td>' . $row_get_request['item_name'] . '</td>';
        echo '<td>' . $row_get_request['requested_date'] . '</td>';
        echo '<td>' . $row_get_request['released_date'] . '</td>';
        echo '<td>' . $row_get_request['returned_date'] . '</td>';
        echo '<td>' . $row_get_request['department_name'] . '</td>';
        echo '<td class="actions">';
        echo '<button class="btn1" data-user-id="' . $row_get_request['request_id'] . '" onclick="showFloatingContainerEditRequest(' . $row_get_request['request_id'] . ', ' . $row_get_request['requested_items_id'] . ')"><i class="fa-solid fa-eye"></i></button>';

        echo '</td>';
        echo '</tr>';

    }
?>