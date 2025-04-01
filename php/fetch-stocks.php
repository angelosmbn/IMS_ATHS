<?php
require '../php/connection.php';
if (isset($_POST['department_id']) && isset($_POST['item_id'])) {
    $departmentId = intval($_POST['department_id']);
    $itemId = intval($_POST['item_id']);

    // SQL query
    $sql_get_stocks = "SELECT a.accumulable_quantity, a.consumed 
                       FROM accumulable a 
                       WHERE a.department_id_fk = $departmentId 
                       AND a.item_id_fk = $itemId";
    $result_get_stocks = $conn->query($sql_get_stocks);

    if ($result_get_stocks && $row_get_stocks = $result_get_stocks->fetch_assoc()) {
        $stocks = $row_get_stocks['accumulable_quantity'] - $row_get_stocks['consumed'];
        echo json_encode(['stocks' => $stocks]);
    } else {
        echo json_encode(['stocks' => 'N/A']);
    }

    $conn->close();
} else {
    echo json_encode(['stocks' => 'Invalid request']);
}
?>
