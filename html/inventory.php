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
    require '../php/add-item.php';
    require '../php/add-request.php';
    require '../php/add-category.php';
    require '../php/add-unit.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit-item'])) {
        $item_id = $_POST['edit-item'];
        $new_item_name = $_POST['item-name'];
        $new_item_category = $_POST['item-category'];
        $new_item_brand = $_POST['item-brand'];
        $new_item_description = $_POST['item-description'];
        $new_item_stocks = $_POST['item-stocks'];
        $new_item_unit = $_POST['item-unit'];
        $new_item_price = $_POST['item-price'];
        $new_item_indicator = $_POST['item-indicator'];
        $new_borrowable = $_POST['borrowable'];
        $new_hide = $_POST['hide'];
        $sql_check_changes = "SELECT * FROM items WHERE item_id = $item_id";
        $result = $conn->query($sql_check_changes);

        $row = $result->fetch_assoc();
        $item_name = $row['item_name'];
        $item_category = $row['item_category'];
        $item_brand = $row['item_brand'];
        $item_description = $row['item_description'];
        $item_stocks = $row['item_stocks'];
        $item_unit = $row['unit'];
        $item_price = $row['item_price'];
        $item_indicator = $row['restock_indicator'];
        $borrowable = $row['borrowable'];
        $hide = $row['hide'];


        if ($new_item_name != $item_name || $new_item_category != $item_category || $new_item_brand != $item_brand || $new_item_description != $item_description || $new_item_stocks != $item_stocks || $new_item_unit != $item_unit || $new_item_price != $item_price || $new_item_indicator != $item_indicator || $new_borrowable != $borrowable || $new_hide!= $hide) {
            $sql_update_item = "UPDATE items SET item_name = '$new_item_name', item_category = '$new_item_category', item_brand = '$new_item_brand', item_description = '$new_item_description', item_stocks = $new_item_stocks, unit = '$new_item_unit', item_price = $new_item_price, restock_indicator = $new_item_indicator, borrowable = '$new_borrowable', hide_status = '$new_hide' WHERE item_id = $item_id";
            if ($conn->query($sql_update_item) === TRUE) {
                echo "<script>alert('Item updated successfully.')</script>";
                echo "<script>window.location.href = 'inventory.php'</script>";
            } else {
                echo "<script>alert('Error updating item: " . $conn->error . "')</script>";
            }
        }else {
            echo "<script>alert('No changes were made.')</script>";
            echo "<script>window.location.href = 'inventory.php'</script>";
        }

    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add-stocks'])) {
        $item_id = $_POST['add-stocks'];
        $stocks_quantity = $_POST['stocks-quantity'];
        
        $sql_add_stocks = "UPDATE items SET item_stocks = item_stocks + $stocks_quantity WHERE item_id = $item_id";
        if ($conn->query($sql_add_stocks) === TRUE) {
            $sql_get_items = "SELECT * FROM items WHERE item_id = $item_id";
            $result = $conn->query($sql_get_items);
            $row = $result->fetch_assoc();
            $beginning_inventory = $row['item_stocks'] - $stocks_quantity;
            $ending_inventory = $beginning_inventory + $stocks_quantity;
            echo "<script>alert('". $beginning_inventory . " + " . $stocks_quantity . " = " . $ending_inventory . "')</script>";

            $purchase_date = date('Y-m-d H:i:s');
            $sql_add_monitoring = "INSERT INTO stock_monitoring (item_id, beginning_inventory_general, item_purchases, requested_date, release_date, ending_inventory_general, purpose) 
            VALUES ('$item_id', '$beginning_inventory', '$stocks_quantity', '$purchase_date', '$purchase_date', '$ending_inventory', 'Add Stocks')";
            $conn->query($sql_add_monitoring);


            echo "<script>alert('Stocks added successfully.')</script>";
            echo "<script>window.location.href = 'inventory.php'</script>";
        } else {
            echo "<script>alert('Error adding stocks: " . $conn->error . "')</script>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="../css/inventory.css">
    <link rel="stylesheet" href="../fontawesome-free-6.5.1-web/css/all.min.css"> 
    <link rel="stylesheet" type="text/css" href="../css/view-item.css">
    <link rel="stylesheet" type="text/css" href="../css/edit-item.css">
    <link rel="stylesheet" type="text/css" href="../css/add-stocks.css">
    <title>Document</title>
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
</head>
<body>
    <div class="inventory-container" id="inventory-container">
        <div class="settings-container">
        <input type="text" name="searchInput" id="searchInput" placeholder="Search">
            <div>
                <?php   
                    echo '<button onclick="showFloatingContainerAddRequest()">+ Add Request</button>';
                    if($_SESSION['access_level'] == 'inventory manager') {
    
                        echo '<button onclick="showFloatingContainerAddItem()">+ Add Item</button>';
                        echo '<button onclick="showFloatingContainerAddCategory()">+ Add Category</button>';
                        echo '<button onclick="showFloatingContainerAddUnit()">+ Add Unit</button>';
                    }
                ?>
            </div>
        </div>
        <div class="items-table">
            <table id="items-table">
                <tr class="sticky-row">
                    <th>Item</th>
                    <th>Brand</th>
                    <th>Description</th>
                    <th class="small-width">Available Stocks</th>
                    <th class="small-width">Requested Stocks</th>
                    <th>Price</th>
                    <?php 
                        if($_SESSION['access_level'] == 'inventory manager') {
                            echo '<th>Action</th>';
                        }
                    ?>
                </tr>
                
                <?php 
                    require '../php/get_items.php';
                ?>
            </table>
        </div>
    </div>

    <div class="floating-viewItem-container" id="floating-viewItem-container">
                    
    </div>

    <div class="floating-editItem-container" id="floating-editItem-container">
        
    </div>

    <div class="floating-addStocks-container" id="floating-addStocks-container">
        
    </div>

    <div class="floating-addUnit-container" id="floating-addUnit-container">
        
    </div>
</body>
<script>
    <?php if(isset($_GET['act'])): ?>
        // Get the PHP flag value and embed it into JavaScript
        var actValue = "<?php echo $_GET['act']; ?>";

        // Check if the flag value equals 'CH-CATEGORY' and set display accordingly
        if (actValue === 'CH-CATEGORY') {
            document.querySelector('.floating-addCategory-container').style.display = 'block';
        }
        if (actValue === 'CH-UNIT') {
            document.querySelector('.floating-addUnit-container').style.display = 'block';
        }
    <?php endif; ?>


    function showFloatingContainerAddItem() {
        document.querySelector('.floating-addItem-container').style.display = 'block';
    }
    function showFloatingContainerAddRequest() {
        document.querySelector('.floating-addRequest-container').style.display = 'block';
    }
    function showFloatingContainerAddCategory() {
        document.querySelector('.floating-addCategory-container').style.display = 'block';
    }
    function showFloatingContainerAddUnit() {
        document.querySelector('.floating-addUnit-container').style.display = 'block';
    }
    
    function hideFloatingContainerViewItem() {
        document.getElementById('floating-viewItem-container').style.display = 'none';
    }
    function hideFloatingContainerEditItem() {
        document.getElementById('floating-editItem-container').style.display = 'none';
    }
    function hideFloatingContainerAddStocks() {
        document.getElementById('floating-addStocks-container').style.display = 'none';
    }
    function hideFloatingContainerAddUnit() {
        document.getElementById('floating-addUnit-container').style.display = 'none';
    }


    function attachBtn1ClickEvent() {
        document.addEventListener('DOMContentLoaded', function() {
            const btn1Elements = document.querySelectorAll('.btn1');
            btn1Elements.forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    document.querySelector('.floating-viewItem-container').style.display = 'block';
                    // Now you have the value of item_id in the itemId variable
                    showFloatingContainerViewItem(itemId); // Pass itemId to the function
                });
            });
        });
    }

    function showFloatingContainerViewItem(itemId) {
        $(document).ready(function() {
            console.log(itemId); // Make sure itemId is not undefined
            if (itemId) {
                $('#floating-viewItem-container').load('../php/view-item.php', {
                    item_id: itemId
                });
            }
        });
    }

    function attachBtn2ClickEvent() {
        document.addEventListener('DOMContentLoaded', function() {
            const btn2Elements = document.querySelectorAll('.btn2');
            btn2Elements.forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    document.querySelector('.floating-editItem-container').style.display = 'block';
                    console.log(itemId);
                    // Now you have the value of item_id in the itemId variable
                    showFloatingContainerEditItem(itemId); // Pass itemId to the function
                });
            });
        });
    }

    function showFloatingContainerEditItem(itemId) {
        $(document).ready(function() {
            console.log(itemId); // Make sure itemId is not undefined
            if (itemId) {
                $('#floating-editItem-container').load('../php/edit-item.php', {
                    item_id: itemId
                });
            }
        });
    }


    function attachBtn3ClickEvent() {
        document.addEventListener('DOMContentLoaded', function() {
            const btn2Elements = document.querySelectorAll('.btn3');
            btn2Elements.forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    document.querySelector('.floating-addStocks-container').style.display = 'block';
                    console.log(itemId);
                    // Now you have the value of item_id in the itemId variable
                    showFloatingContainerAddStocks(itemId); // Pass itemId to the function
                });
            });
        });
    }

    function showFloatingContainerAddStocks(itemId) {
        $(document).ready(function() {
            console.log(itemId); // Make sure itemId is not undefined
            if (itemId) {
                $('#floating-addStocks-container').load('../php/add-stocks.php', {
                    item_id: itemId
                });
            }
        });
    }

    attachBtn1ClickEvent(); // Call this function to set up event listeners
    attachBtn2ClickEvent();
    attachBtn3ClickEvent();

    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#items-table tr:not(.sticky-row):not(.lend-item-row)');

        rows.forEach(function(row) {
            let rowText = row.textContent.toLowerCase();
            if (rowText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });






    changeNavBarTitle('Inventory');
</script>
</html>