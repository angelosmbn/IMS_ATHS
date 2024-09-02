<link rel="stylesheet" type="text/css" href="../css/add-stocks.css">
<?php 
    require '../php/connection.php';
    if(isset($_POST['item_id'])) {
        $item_id = $_POST['item_id'];
        $sql_get_item = "SELECT * FROM items WHERE item_id = $item_id";
        $result = $conn->query($sql_get_item);
        $row = $result->fetch_assoc();
        $item_name = $row['item_name'];
        $stocks = $row['item_stocks'];
        $unit = $row['unit'];
    }
    else{
        echo "<script>alert('No item selected.')</script>";
        echo "<script>window.location.href = 'inventory.php'</script>";
    }
?>
<div class="bar">
    <span>Add Stocks</span>
    <span class="close-icon" onclick="hideFloatingContainerAddStocks()">&#10006;</span>
</div>
<form class="add-stocks-from" action="" method="POST">
    <div class="item-labels">
        <div><span>Item:</span> <?php echo $item_name ?></div>
        <div><span>Current Stocks:</span> <?php echo $stocks . " " . $unit?></div>
    </div>
    <input type="number" name="stocks-quantity" id="stocks-quantity" step="1" min="1" required> 
    <input type="hidden" name="add-stocks" value="<?php echo $item_id ?>">
    <button type="submit">Add Stocks</button>
</form>