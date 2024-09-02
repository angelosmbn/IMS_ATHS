<?php
// Include your database connection file

// Function to render items
function renderItems($conn, $conditions, $sessionCheck = false) {
    $sql = "SELECT * FROM items WHERE $conditions";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $requested_stocks = 0;
        
        $sql_get_requests = "
            SELECT SUM(ri.request_quantity) AS total_requested 
            FROM requested_items ri 
            JOIN requests r ON ri.request_id_fk = r.request_id 
            WHERE item_id_fk = {$row['item_id']} AND 
            r.request_status NOT IN ('confirmation', 'rejected', 'completed')";
        
        $result_requests = $conn->query($sql_get_requests);
        
        if ($result_requests) {
            $requested_stocks = $result_requests->fetch_assoc()['total_requested'];
        }

        echo '<tr>';
        echo '<td>' . $row['item_name'] . '</td>';
        echo '<td>' . $row['item_brand'] . '</td>';
        echo '<td>' . $row['item_description'] . '</td>';
        if ($row['item_stocks'] <= $row['restock_indicator'] && $_SESSION['access_level'] == 'inventory manager') {
            echo '<td>' . $row['item_stocks'] . ' <span style="display:inline-block; width:10px; height:10px; background-color:red; border-radius:50%;"></span></td>';
        }
        else {
            echo '<td>' . $row['item_stocks'] . '</td>';
        }
        echo '<td>' . $requested_stocks . '</td>';
        echo '<td>' . $row['item_price'] . '</td>';
        
        if ($sessionCheck && $_SESSION['access_level'] == 'inventory manager') {
            echo '<td class="actions">';
            echo '<button class="btn1" data-item-id="' . $row['item_id'] . '" onclick="showFloatingContainerViewItem()"><i class="fa-solid fa-eye"></i></button>';
            echo '<button class="btn2" data-item-id="' . $row['item_id'] . '" onclick="showFloatingContainerEditItem()"><i class="fa-solid fa-pencil"></i></button>';
            echo '<button class="btn3" data-item-id="' . $row['item_id'] . '"><i class="fa-solid fa-plus"></i></button>';  
            echo '</td>';
        }
        echo '</tr>';
    }
}

// Render available and non-borrowable items
renderItems($conn, "item_status = 'available' AND borrowable = 'no' AND hide_status = 'no'", true);

// Render the "LEND ITEM ONLY" row
echo '<tr class="lend-item-row"><td class="indication" colspan="7">LEND ITEM ONLY</td></tr>';

// Render available and borrowable items
renderItems($conn, "item_status = 'available' AND borrowable = 'yes' AND hide_status = 'no'", true);

// Render hidden items if the user has appropriate access
if (in_array($_SESSION['access_level'], ['inventory manager', 'finance officer'])) {
    echo '<tr class="hidden-item-row"><td class="indication" colspan="7">HIDDEN ITEMS</td></tr>';
    renderItems($conn, "hide_status = 'yes'", true);
}

// Close the database connection
closeConnection();
?>
