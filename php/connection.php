<?php
    date_default_timezone_set('Asia/Manila');
    // Database Configuration
    $servername = "localhost";
    $username = "root";
    $password = "assumpta_hris";
    $database = "athwebs_database_ims";

    // Create a connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Function to close the database connection
    function closeConnection() {
        global $conn;
        if ($conn) {
            $conn->close();
        }
    }
?>