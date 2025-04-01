<?php
    session_start();
    if(isset($_SESSION['user_id'])) {
        echo "<script>window.location.href='html/inventory.php';</script>";
    } else {
        echo "<script>window.location.href='html/login.php';</script>";
    }
?>
