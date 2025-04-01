<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it hasn't been started already
}
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != "") {

} else {
    echo "<script>alert('Please login first!')</script>";
    echo "<script>window.location.href='login.php';</script>";
}
require '../php/connection.php';
require 'navigation-bar.php';
require '../php/add-department.php';
require '../php/email.php';

function checkRejected($request_id, $request_status, $conn)
{
    // SQL to count all items with the given request_id_fk
    $sql_total_items = "SELECT COUNT(*) AS total_items FROM requested_items WHERE request_id_fk = $request_id";
    $result_total_items = mysqli_query($conn, $sql_total_items);
    $row_total_items = mysqli_fetch_assoc($result_total_items);
    $total_items = $row_total_items['total_items'];

    // SQL to count rejected items with the given request_id_fk
    $sql_rejected_items = "SELECT COUNT(*) AS rejected_items FROM requested_items WHERE request_id_fk = $request_id AND is_rejected = 'yes'";
    $result_rejected_items = mysqli_query($conn, $sql_rejected_items);
    $row_rejected_items = mysqli_fetch_assoc($result_rejected_items);
    $rejected_items = $row_rejected_items['rejected_items'];

    // Condition: Check if the total items are equal to rejected items
    if ($total_items == $rejected_items) {

        if ($request_status == 'coordinator approval') {
            $sql_update_request = "UPDATE requests SET request_status = 'rejected', coordinator_approval = 'REJECTED' WHERE request_id = $request_id";
        } else if ($request_status == 'finance approval') {
            $sql_update_request = "UPDATE requests SET request_status = 'rejected', finance_approval = 'REJECTED' WHERE request_id = $request_id";
        }

        if ($conn->query($sql_update_request) === TRUE) {
            return true;
        } else {
            return false;
        }
    } else {
        return null;
    }
}


if (isset($_POST['approve-button'])) {
    $request_id = $_POST['request_id'];
    $request_status = $_POST['request_status'];
    $request_group_id = $_POST['request_group_id'];
    $requested_items_ids = isset($_POST['requested_items_ids']) ? $_POST['requested_items_ids'] : NULL;//array if selected items

    $request_quantity = $_POST['request_quantity']; //array
    $item_id_fks = $_POST['items_id_fk']; //array
    if ($request_group_id != NULL) {
        $requests_ids = $_POST['requests_ids']; // this are the ids of request that are in the same group
    }

    $conn->begin_transaction();
    if ($request_status == 'coordinator approval') {
        $coordinator_comment = $_POST['coordinator_comment'];

        //Group request
        if ($request_group_id != NULL) {

            $sql_update_request = "UPDATE requests SET request_status = 'finance approval', coordinator_approval = 'APPROVED', coordinator_comment = '$coordinator_comment' WHERE request_group_id = $request_group_id AND request_status = 'coordinator approval'";
            if (!$conn->query($sql_update_request)) {
                $conn->rollback();
                echo "<script>alert('Error approving request!')</script>";
                echo "<script>window.location.href='inbox.php';</script>";
            }

            $sql_update_request = "UPDATE requested_items 
                       SET is_rejected = 'yes' 
                       WHERE requested_items_id NOT IN (" . implode(',', array_map('intval', $requested_items_ids)) . ") 
                       AND request_id_fk IN (" . implode(',', array_map('intval', $requests_ids)) . ")";
            if (!$conn->query($sql_update_request)) {
                $conn->rollback();
                echo "<script>alert('Error approving request!')</script>";
                echo "<script>window.location.href='inbox.php';</script>";
            }

            for ($i = 0; $i < count($item_id_fks); $i++) {
                $sql_update_request = "UPDATE requested_items SET request_quantity = '{$request_quantity[$i]}' WHERE item_id_fk = '{$item_id_fks[$i]}' AND requested_items_id = '{$requested_items_ids[$i]}'";
                //echo '<script>alert("'.$department_charged[$i]. '-' .$request_quantity[$i] .'-'.$requested_items_ids[$i].'")</script>';
                if (!$conn->query($sql_update_request)) {
                    $conn->rollback();
                    echo "<script>alert('Error approving request!')</script>";
                    echo "<script>window.location.href='inbox.php';</script>";
                } else {
                    $conn->commit();
                }
            }
        }
        //Single request 
        else {
            $sql_update_request = "UPDATE requests SET request_status = 'finance approval', coordinator_approval = 'APPROVED', coordinator_comment = '$coordinator_comment' WHERE request_id = $request_id AND request_status = 'coordinator approval'";
            if (!$conn->query($sql_update_request)) {
                $conn->rollback();
                echo "<script>alert('Error approving request!')</script>";
                echo "<script>window.location.href='inbox.php';</script>";
            }
        }

    } else if ($request_status == 'finance approval') {
        $finance_comment = $_POST['finance_comment'];

        $department_charged = $_POST['department_charged']; //array

        //unused$requested_items_id = $_POST['requested_items_id']; //array of all items
        //echo '<script>alert("'.count($department_charged). '-' .count($request_quantity) .'-' .count($requested_items_ids) .'-' .count($item_id_fks) .'")</script>';

        if ($request_group_id != NULL) {
            $sql_update_request = "UPDATE requests SET request_status = 'releasing', finance_approval = 'APPROVED', finance_comment = '$finance_comment' WHERE request_group_id = $request_group_id AND request_status = 'finance approval'";
            if (!$conn->query($sql_update_request)) {
                $conn->rollback();
                echo "<script>alert('Error approving request!')</script>";
                echo "<script>window.location.href='inbox.php';</script>";
            }

            $sql_update_request = "UPDATE requested_items 
                       SET is_rejected = 'yes' 
                       WHERE requested_items_id NOT IN (" . implode(',', array_map('intval', $requested_items_ids)) . ") 
                       AND request_id_fk IN (" . implode(',', array_map('intval', $requests_ids)) . ")";
            if (!$conn->query($sql_update_request)) {
                $conn->rollback();
                echo "<script>alert('Error approving request!')</script>";
                echo "<script>window.location.href='inbox.php';</script>";
            }

            for ($i = 0; $i < count($department_charged); $i++) {
                $sql_update_request = "UPDATE requested_items SET requesting_department_id = '{$department_charged[$i]}', request_quantity = '{$request_quantity[$i]}' WHERE item_id_fk = '{$item_id_fks[$i]}' AND requested_items_id = '{$requested_items_ids[$i]}'";
                //echo '<script>alert("'.$department_charged[$i]. '-' .$request_quantity[$i] .'-'.$requested_items_ids[$i].'")</script>';
                if (!$conn->query($sql_update_request)) {
                    $conn->rollback();
                    echo "<script>alert('Error approving request!')</script>";
                    echo "<script>window.location.href='inbox.php';</script>";
                } else {
                    $conn->commit();
                }
            }

        } else {
            for ($i = 0; $i < count($department_charged); $i++) {
                $sql_update_request = "UPDATE requested_items SET requesting_department_id = '{$department_charged[$i]}' WHERE request_id_fk = $request_id AND item_id_fk = '{$item_id_fks[$i]}' AND requested_items_id = '{$requested_items_ids[$i]}'";

                if (!$conn->query($sql_update_request)) {
                    $conn->rollback();
                    echo "<script>alert('Error approving request!')</script>";
                    echo "<script>window.location.href='inbox.php';</script>";
                } else {
                    $conn->commit();
                }
            }

            $sql_update_request = "UPDATE requests SET request_status = 'releasing', finance_approval = 'APPROVED', finance_comment = '$finance_comment' WHERE request_id = $request_id";
            if (!$conn->query($sql_update_request)) {
                $conn->rollback();
                echo "<script>alert('Error approving request!')</script>";
                echo "<script>window.location.href='inbox.php';</script>";
            } else {
                $conn->commit();
            }
        }

        

    }



    if ($conn->query($sql_update_request) === TRUE) {
        $result = checkRejected($request_id, $request_status, $conn);
        if ($result === false) {
            $conn->rollback();
            echo "<script>alert('Error approving request!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } else {
            $conn->commit();
            echo "<script>alert('Request successfully approved!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        }
    } else {
        $conn->rollback();
        echo "<script>alert('Error approving request!')</script>";
        echo "<script>window.location.href='inbox.php';</script>";
    }
}

if (isset($_POST['reject-button'])) {
    $request_id = $_POST['request_id'];
    $request_status = $_POST['request_status'];
    $request_group_id = $_POST['request_group_id'];
    $requested_items_ids = isset($_POST['requested_items_ids']) ? $_POST['requested_items_ids'] : NULL;

    if ($requested_items_ids != NULL && $request_id != NULL) {
        $sql_update_request = "UPDATE requested_items SET is_rejected = 'yes' WHERE requested_items_id IN (" . implode(',', $requested_items_ids) . ")";
    } else {
        echo "<script>alert('Error rejecting request!')</script>";
        echo "<script>window.location.href='inbox.php';</script>";
    }

    
    


    if ($conn->query($sql_update_request) === TRUE) {
        #$result = checkRejected($request_id, $request_status, $conn);
        if ($result == true) {
            # iterate through the requested_items_ids and update the request status to rejected
            foreach ($requested_items_ids as $requested_item_id) {
                $sql_get_request_id_fk = "SELECT request_id_fk FROM requested_items WHERE requested_items_id = $requested_item_id";
                $result_get_request_id_fk = $conn->query($sql_get_request_id_fk);
                $row_get_request_id_fk = $result_get_request_id_fk->fetch_assoc();
                $request_id_fk = $row_get_request_id_fk['request_id_fk'];

                echo "<script>alert('requested_item_id: " . $requested_item_id . "');</script>";
                $sql_get_number_of_items = "SELECT COUNT(*) AS number_of_items FROM requested_items WHERE request_id_fk = $request_id_fk";
                $result_get_number_of_items = $conn->query($sql_get_number_of_items);
                $row_get_number_of_items = $result_get_number_of_items->fetch_assoc();
                $number_of_items = $row_get_number_of_items['number_of_items'];

                $sql_get_number_of_rejected_items = "SELECT COUNT(*) AS number_of_rejected_items FROM requested_items WHERE request_id_fk = $request_id_fk AND is_rejected = 'yes'";
                $result_get_number_of_rejected_items = $conn->query($sql_get_number_of_rejected_items);
                $row_get_number_of_rejected_items = $result_get_number_of_rejected_items->fetch_assoc();
                $number_of_rejected_items = $row_get_number_of_rejected_items['number_of_rejected_items'];
                
                echo "<script>alert('number_of_items: " . $number_of_items . " number_of_rejected_items: " . $number_of_rejected_items . "');</script>";
                if ($number_of_items == $number_of_rejected_items) {
                    $sql_update_request = "UPDATE requests SET request_status = 'rejected' WHERE request_id = $request_id_fk";
                    if ($conn->query($sql_update_request) !== TRUE) {
                        echo "<script>alert('Error rejecting request!')</script>";
                        echo "<script>window.location.href='inbox.php';</script>";
                    }
                }
            }
            echo "<script>alert('Requests successfully updated!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } elseif ($result === false) {
            echo "<script>alert('Error rejecting request!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } else {
            # iterate through the requested_items_ids and update the request status to rejected
            foreach ($requested_items_ids as $requested_item_id) {
                $sql_get_request_id_fk = "SELECT request_id_fk FROM requested_items WHERE requested_items_id = $requested_item_id";
                $result_get_request_id_fk = $conn->query($sql_get_request_id_fk);
                $row_get_request_id_fk = $result_get_request_id_fk->fetch_assoc();
                $request_id_fk = $row_get_request_id_fk['request_id_fk'];

                echo "<script>alert('requested_item_id: " . $requested_item_id . "');</script>";
                $sql_get_number_of_items = "SELECT COUNT(*) AS number_of_items FROM requested_items WHERE request_id_fk = $request_id_fk";
                $result_get_number_of_items = $conn->query($sql_get_number_of_items);
                $row_get_number_of_items = $result_get_number_of_items->fetch_assoc();
                $number_of_items = $row_get_number_of_items['number_of_items'];

                $sql_get_number_of_rejected_items = "SELECT COUNT(*) AS number_of_rejected_items FROM requested_items WHERE request_id_fk = $request_id_fk AND is_rejected = 'yes'";
                $result_get_number_of_rejected_items = $conn->query($sql_get_number_of_rejected_items);
                $row_get_number_of_rejected_items = $result_get_number_of_rejected_items->fetch_assoc();
                $number_of_rejected_items = $row_get_number_of_rejected_items['number_of_rejected_items'];
                
                echo "<script>alert('number_of_items: " . $number_of_items . " number_of_rejected_items: " . $number_of_rejected_items . "');</script>";
                if ($number_of_items == $number_of_rejected_items) {
                    $sql_update_request = "UPDATE requests SET request_status = 'rejected' WHERE request_id = $request_id_fk";
                    if ($conn->query($sql_update_request) !== TRUE) {
                        echo "<script>alert('Error rejecting request!')</script>";
                        echo "<script>window.location.href='inbox.php';</script>";
                    }
                }
            }

            echo "<script>alert('Request successfully rejected items!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        }
    } else {
        echo "<script>alert('Error rejecting request!')</script>";
        echo "<script>window.location.href='inbox.php';</script>";
    }
}

if (isset($_POST['release-button'])) {
    $request_id = $_POST['request_id'];
    $request_status = $_POST['request_status'];
    $release_date = date('Y-m-d H:i:s');
    $request_group_id = isset($_POST['request_group_id']) ? $_POST['request_group_id'] : NULL;
    //begin transaction
    $conn->begin_transaction();

    $request_ids = [];
    $requestor_ids = [];
    if ($request_group_id != NULL) {
        $sql_update_request = "UPDATE requests SET request_status = 'confirmation', released_date = '$release_date' WHERE request_group_id = $request_group_id AND request_status = 'releasing' AND request_status != 'rejected'";
    } else {
        $sql_update_request = "UPDATE requests SET request_status = 'confirmation', released_date = '$release_date' WHERE request_id = $request_id";
    }

    // Group request
    if (($conn->query($sql_update_request) === TRUE) && $request_group_id != NULL) {
        echo '<script>console.log("group")</script>';
        //GET ALL REQUEST IDS OF THE REQUEST GROUP
        echo "<script>alert('" . $request_group_id . "');</script>";
        $sql_get_request_ids = "SELECT * FROM requests WHERE request_group_id = $request_group_id AND request_status != 'rejected'";
        $result_get_request_ids = $conn->query($sql_get_request_ids);

        while ($row_get_requests_ids = $result_get_request_ids->fetch_assoc()) {
            $request_ids[] = $row_get_requests_ids['request_id']; // Append each request_id to the array
            $requestor_ids[] = $row_get_requests_ids['requestor_id'];
        }

        //SELECT ALL REQUEST ITEMS IN REQUESTED ITEMS
        if (count($request_ids) > 0) {
            $recipientEmails = [];
            $requestIdsToSend = [];
            for ($i = 0; $i < count($request_ids); $i++) {
                echo "<script>alert('here');</script>";
                echo "<script>alert('" . $request_ids[$i] . "');</script>";
                $request_id = (int) $request_ids[$i];
                $sql_get_request_items = "SELECT ri.*, i.*, d.*, r.*
                            FROM requested_items ri
                            JOIN items i ON ri.item_id_fk = i.item_id
                            JOIN departments d ON ri.requesting_department_id = d.department_id
                            JOIN requests r ON ri.request_id_fk = r.request_id
                            WHERE ri.request_id_fk = ?
                            AND TRIM(ri.is_rejected) = 'no'";
                //                             AND r.request_status NOT IN ('coordinator approval', 'finance approval', 'confirmation', 'completed', 'rejected')

                $stmt = $conn->prepare($sql_get_request_items); // Replace $your_database_connection
                $stmt->bind_param("i", $request_id); // "i" indicates an integer
                $stmt->execute();
                $result = $stmt->get_result();

                // echo "<script>alert(`$sql_get_request_items`);</script>";
                // $result_get_request_items = $conn->query($sql_get_request_items);
                if (!$result) {
                    echo "<script>alert('Error retrieving request items!');</script>";
                    echo "<script>window.location.href='inbox.php';</script>";
                    return;
                }
                if ($result->num_rows == 0) {
                    
                    
                    echo "<script>alert('No rows found');</script>";
                    echo "<script>window.location.href='inbox.php';</script>";
                    return;
                } else {
                    echo "<script>alert('Successfully retrieved request items!');</script>";
                }
                while ($row_get_request_items = $result->fetch_assoc()) {
                    echo "<script>alert('here2');</script>";
                    $requestor_id = $row_get_request_items['requestor_id'];
                    $school_year = $row_get_request_items['school_year'];
                    $item_stocks = $row_get_request_items['item_stocks'];

                    if ($row_get_request_items['requesting_department_id'] != 0) {
                        echo "<script>alert('here3');</script>";
                        $sql_update_department_stocks = "UPDATE accumulable SET consumed = consumed + {$row_get_request_items['request_quantity']} WHERE item_id_fk = '{$row_get_request_items['item_id_fk']}' AND department_id_fk = '{$row_get_request_items['requesting_department_id']}'";
                        if ($conn->query($sql_update_department_stocks) !== TRUE) {
                            echo "<script>alert('here4');</script>";
                            // Use addslashes() to escape any special characters in the error message
                            $error_message = addslashes($conn->error);
                            echo "<script>alert('Error updating stocks: $error_message');</script>";
                            return;
                        }
                    }

                    $sql_get_stocks = "SELECT * FROM items WHERE item_id = '{$row_get_request_items['item_id_fk']}'";
                    $result_get_stocks = $conn->query($sql_get_stocks);
                    if ($result_get_stocks !== FALSE) {
                        echo "<script>alert('here5');</script>";
                        $row_get_stocks = $result_get_stocks->fetch_assoc();

                        $ending_inventory_general = $row_get_stocks['item_stocks'] - $row_get_request_items['request_quantity'];
                        if ($ending_inventory_general < 0) {
                            echo "<script>alert('here6');</script>";
                            $conn->rollback();
                            echo "<script>alert('Error releasing items: Insufficient stocks for " . $row_get_stocks['item_name'] . "')</script>";
                            echo "<script>window.location.href='inbox.php';</script>";
                            return;
                        }
                        $sql_insert_stock_monitoring = "INSERT INTO stock_monitoring (request_id, item_id, requesting_department_id, beginning_inventory_general, item_cost, requested_quantity_general, requested_date, release_date, ending_inventory_general, school_year, charged_department_id, is_borrowable) VALUES 
                                                ('{$request_ids[$i]}', '{$row_get_request_items['item_id_fk']}', '{$row_get_request_items['charged_department']}', '{$row_get_stocks['item_stocks']}', '{$row_get_request_items['item_price']}', '{$row_get_request_items['request_quantity']}', '{$row_get_request_items['requested_date']}', '$release_date', '$ending_inventory_general', '$school_year', '{$row_get_request_items['requesting_department_id']}', '{$row_get_request_items['borrowable']}')";
                        echo "<script>alert('" . $i . " : " . $request_ids[$i] . "')</script>";
                        if ($conn->query($sql_insert_stock_monitoring) === TRUE) {
                            echo "<script>alert('here7');</script>";
                            $monitoring_id = $conn->insert_id;
                            $update_requested_items = "UPDATE requested_items SET stock_monitoring_id = $monitoring_id WHERE request_id_fk = '{$request_ids[$i]}' AND item_id_fk = '{$row_get_request_items['item_id_fk']}' AND requested_items_id = '{$row_get_request_items['requested_items_id']}'";
                            if ($conn->query($update_requested_items) === TRUE) {
                                $sql_update_item_stocks = "UPDATE items SET item_stocks = item_stocks - {$row_get_request_items['request_quantity']} WHERE item_id = '{$row_get_request_items['item_id_fk']}'";
                                if ($conn->query($sql_update_item_stocks)) {
                                    $prevItemId = $row_get_request_items['item_id_fk'];
                                    $prevDepartmentId = $row_get_request_items['requesting_department_id'];
                                } else {
                                    $conn->rollback();
                                    // Use addslashes() to escape any special characters in the error message
                                    $error_message = addslashes($conn->error);
                                    echo "<script>alert('Error updating stocks: $error_message');</script>";
                                    return;
                                }
                            } else {
                                echo "<script>alert('here8');</script>";
                                $conn->rollback();
                                // Use addslashes() to escape any special characters in the error message
                                $error_message = addslashes($conn->error);
                                echo "<script>alert('Error updating stocks monitoring: $error_message');</script>";
                                return;
                            }
                        } else {
                            echo "<script>alert('here9');</script>";
                            $conn->rollback();
                            // Use addslashes() to escape any special characters in the error message
                            $error_message = addslashes($conn->error);
                            echo "<script>alert('Error updating stocks monitoring: $error_message');</script>";
                            return;
                        }
                    } else {
                        echo "<script>alert('here10');</script>";
                        $conn->rollback();
                        // Use addslashes() to escape any special characters in the error message
                        $error_message = addslashes($conn->error);
                        echo "<script>alert('Error retrieving stocks: $error_message');</script>";
                        return;
                    }
                }
                

            }
            $conn->commit();

            // send email
            for ($j = 0; $j < count($request_ids); $j++) {
                $sql_get_requestor_email = "SELECT * FROM users WHERE user_id = {$requestor_ids[$j]}";
                $result_get_requestor_email = $conn->query($sql_get_requestor_email);
                $row_get_requestor_email = $result_get_requestor_email->fetch_assoc();
                $sentEmail = sendEmail($row_get_requestor_email['email'], $request_ids[$j], $conn);
                if (!($sentEmail)) {
                    echo "<script>alert('Failed sending email to " . $row_get_requestor_email['email'] . "');</script>";
                } else {
                    echo "<script>alert('Sent email to " . $row_get_requestor_email['first_name'] . "');</script>";
                }
            }
            echo "<script>alert('Items successfully released!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        } else {
            $conn->rollback();
            echo "<script>alert('Error releasing items!')</script>";
            echo "<script>window.location.href='inbox.php';</script>";
        }

        //UPDATE ACCUMULABLE

        //INSERT STOCK MONITORING GET THE ID AND UPDATE REQUESTED ITEMS

        //UPDATE ITEMS STOCKS

        //SEND EMAIL TO ALL REQUESTORS
    }
    // Single request
    else if ($conn->query($sql_update_request) === TRUE && $request_group_id == NULL) {
        echo '<script>alert("single")</script>';
        $sql_get_request_items = "SELECT ri.*, i.*, d.*, r.*
            FROM requested_items ri
            JOIN items i ON ri.item_id_fk = i.item_id
            JOIN departments d ON ri.requesting_department_id = d.department_id
            JOIN requests r ON ri.request_id_fk = r.request_id
            WHERE ri.request_id_fk = $request_id
            AND ri.is_rejected = 'no'";
        //DO NOT CHANGE THE ORDER.

        $result_get_request_items = $conn->query($sql_get_request_items);
        $prevItemId = "";
        $prevDepartmentId = "";
        while ($row_get_request_items = $result_get_request_items->fetch_assoc()) {
            $requestor_id = $row_get_request_items['requestor_id'];
            $school_year = $row_get_request_items['school_year'];
            $item_stocks = $row_get_request_items['item_stocks'];

            if ($row_get_request_items['requesting_department_id'] != 0) {
                $sql_update_department_stocks = "UPDATE accumulable SET consumed = consumed + {$row_get_request_items['request_quantity']} WHERE item_id_fk = '{$row_get_request_items['item_id_fk']}' AND department_id_fk = '{$row_get_request_items['requesting_department_id']}'";
                if ($conn->query($sql_update_department_stocks) !== TRUE) {
                    // Use addslashes() to escape any special characters in the error message
                    $error_message = addslashes($conn->error);
                    echo "<script>alert('Error updating stocks: $error_message');</script>";
                    return;
                }
            }

            $sql_get_stocks = "SELECT * FROM items WHERE item_id = '{$row_get_request_items['item_id_fk']}'";
            $result_get_stocks = $conn->query($sql_get_stocks);
            if ($result_get_stocks !== FALSE) {
                $row_get_stocks = $result_get_stocks->fetch_assoc();

                $ending_inventory_general = $row_get_stocks['item_stocks'] - $row_get_request_items['request_quantity'];
                if ($ending_inventory_general < 0) {
                    $conn->rollback();
                    echo "<script>alert('Error releasing items: Insufficient stocks for " . $row_get_stocks['item_name'] . "')</script>";
                    echo "<script>window.location.href='inbox.php';</script>";
                    return;
                }
                $sql_insert_stock_monitoring = "INSERT INTO stock_monitoring (request_id, item_id, requesting_department_id, beginning_inventory_general, item_cost, requested_quantity_general, requested_date, release_date, ending_inventory_general, school_year, charged_department_id, is_borrowable) VALUES 
                                        ('$request_id', '{$row_get_request_items['item_id_fk']}', '{$row_get_request_items['charged_department']}', '{$row_get_stocks['item_stocks']}', '{$row_get_request_items['item_price']}', '{$row_get_request_items['request_quantity']}', '{$row_get_request_items['requested_date']}', '$release_date', '$ending_inventory_general', '$school_year', '{$row_get_request_items['requesting_department_id']}', '{$row_get_request_items['borrowable']}')";
                if ($conn->query($sql_insert_stock_monitoring) === TRUE) {
                    $monitoring_id = $conn->insert_id;

                    $update_requested_items = "UPDATE requested_items SET stock_monitoring_id = $monitoring_id WHERE request_id_fk = $request_id AND item_id_fk = '{$row_get_request_items['item_id_fk']}' AND requested_items_id = '{$row_get_request_items['requested_items_id']}'";
                    if ($conn->query($update_requested_items) === TRUE) {
                        $sql_update_item_stocks = "UPDATE items SET item_stocks = item_stocks - {$row_get_request_items['request_quantity']} WHERE item_id = '{$row_get_request_items['item_id_fk']}'";
                        if ($conn->query($sql_update_item_stocks)) {
                            $prevItemId = $row_get_request_items['item_id_fk'];
                            $prevDepartmentId = $row_get_request_items['requesting_department_id'];
                        } else {
                            $conn->rollback();
                            // Use addslashes() to escape any special characters in the error message
                            $error_message = addslashes($conn->error);
                            echo "<script>alert('Error updating stocks: $error_message');</script>";
                            return;
                        }


                    } else {
                        $conn->rollback();
                        // Use addslashes() to escape any special characters in the error message
                        $error_message = addslashes($conn->error);
                        echo "<script>alert('Error updating stocks monitoring: $error_message');</script>";
                        return;
                    }



                } else {
                    $conn->rollback();
                    // Use addslashes() to escape any special characters in the error message
                    $error_message = addslashes($conn->error);
                    echo "<script>alert('Error updating stocks monitoring: $error_message');</script>";
                    return;
                }




            } else {
                $conn->rollback();
                // Use addslashes() to escape any special characters in the error message
                $error_message = addslashes($conn->error);
                echo "<script>alert('Error retrieving stocks: $error_message');</script>";
                return;
            }


        }
        $sql_get_requestor_email = "SELECT * FROM users WHERE user_id = $requestor_id";
        $result_get_requestor_email = $conn->query($sql_get_requestor_email);
        $row_get_requestor_email = $result_get_requestor_email->fetch_assoc();
        $recipientEmail = $row_get_requestor_email['email'];
        if (!(sendEmail($recipientEmail, $request_id, $conn))) {
            echo "<script>alert('Error sending email.');</script>";
        }
        $conn->commit();
        echo "<script>alert('Items successfully released!')</script>";
        echo "<script>window.location.href='inbox.php';</script>";
    } else {
        $conn->rollback();
        echo "<script>alert('Error releasing items!')</script>";
        echo "<script>window.location.href='inbox.php';</script>";
    }
}

if (isset($_POST['receive-button'])) {
    $request_id = $_POST['request_id'];
    $request_status = $_POST['request_status'];
    $receive_date = date('Y-m-d H:i:s');
    $sql_update_request = "UPDATE requests SET request_status = 'completed', requestor_approval = 'APPROVED', received_date = '$receive_date' WHERE request_id = $request_id";

    if ($conn->query($sql_update_request) === TRUE) {
        echo "<script>alert('Items successfully received!')</script>";
        echo "<script>window.location.href='inbox.php';</script>";
    } else {
        echo "<script>alert('Error receiving items!')</script>";
        echo "<script>window.location.href='inbox.php';</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inbox.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>
    <div class="inbox-container" id="inbox-container">
        <div class="settings-container">
            <input type="text" name="search" id="search" maxlength="255">
            <div>
                <?php
                if ($_SESSION['access_level'] == 'finance officer') {
                    echo "<button onclick=\"showFloatingContainerAddDepartment()\">+ Add Department</button>";
                }
                ?>
            </div>
        </div>
        <div class="inbox-table">
            <table id="inbox-table-for-search">
                <tr class="sticky-row">
                    <th>Requested By</th>
                    <th>Requesting Department</th>
                    <th>Requested On</th>
                    <th>Needed On</th>
                    <th>Request Status</th>
                    <th>Actions</th>
                </tr>
                <?php
                require '../php/get_inbox.php';
                ?>
            </table>
        </div>

        <div class="floating-editRequest-container" id="floating-editRequest-container">
            <div class="bar">
                <span>Request Details</span>
                <span class="close-icon" onclick="hideFloatingContainerEditRequest()">&#10006;</span>
            </div>
            <form action="" method="POST">
                <div class="request-details" id="request-details">

                </div>
            </form>
        </div>

    </div>
</body>
<script>
    function hideFloatingContainerEditRequest() {
        document.getElementById('floating-editRequest-container').style.display = 'none';
    }
    function showFloatingContainerAddDepartment() {
        document.querySelector('.floating-addCategory-container').style.display = 'block';
    }

    function showFloatingContainerEditRequest(requestId, requestGroupId) {
        $(document).ready(function () {
            document.getElementById('floating-editRequest-container').style.display = 'block';
            console.log(requestId);
            console.log(requestGroupId);
            if (requestGroupId == undefined) {
                requestGroupId = null;
            }
            if (requestId) {
                $('#request-details').load('../php/edit-request.php', {
                    request_id: requestId,
                    request_group_id: requestGroupId
                });
            }
        });
    }

    function resetEditRequest(requestId) {
        $(document).ready(function () {
            console.log(requestId);
            if (requestId) {
                $('#request-details').load('../php/edit-request.php', {
                    request_id: requestId
                });
            }
        });
    }



    /*document.addEventListener('change', function(event) {
        // Check if the changed element is a select element with the class 'department_charged'
        if (event.target.classList.contains('department_charged')) {
            var selectedDepartments = [];
            var requestId = $('#request_id').val();
            var requestGroupId = $('#request_group_id').val();

            // Iterate over each selected option and add its value to the array
            $('select[name="department_charged[]"] option:selected').each(function() {
                selectedDepartments.push($(this).val());
            });

            console.log(selectedDepartments);

            if (selectedDepartments) {
                $('#request-details').load('../php/edit-request.php', {
                    request_id: requestId,
                    department_ids: selectedDepartments,
                    request_group_id: requestGroupId
                });
            }
        }
    })*/

    //changeNavBarTitle('Inbox');


    document.getElementById('search').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#inbox-table-for-search tr:not(.sticky-row):not(.lend-item-row)');

        rows.forEach(function (row) {
            let rowText = row.textContent.toLowerCase();
            if (rowText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });





</script>

</html>