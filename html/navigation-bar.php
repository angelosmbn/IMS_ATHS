<?php 
    if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
        // Perform logout actions
        session_destroy(); // Destroy the session data
        echo "<script>window.location.href='login.php';</script>"; // Redirect to login page
        exit;
    }
    require 'settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/navigation-bar.css">
    <link rel="stylesheet" href="../fontawesome-free-6.5.1-web/css/all.min.css"> 
    <link href='https://fonts.googleapis.com/css?family=Jomhuria' rel='stylesheet'>
    <title>Document</title>
</head>
<body>
    
    <div class="navigation-container">
        <ul>
            <li>
                <img class="logo" src="../resources/logo.png" alt="none">
                <div class="arrow">
                    <a href="">
                        <i class="fa-solid fa-circle-chevron-left"></i>
                    </a>
                </div>
            </li>
            
        </ul>
        <ul class="links">
            <li>
                <a href="inventory.php" id="inventory-link">
                    <i class="fa-solid fa-box-open"></i>
                </a>
            </li>
            <?php 
                if($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<a href="department-request.php">';
                    echo '<i class="fa-solid fa-clipboard-list"></i>';
                    echo '</a>';
                    echo '</li>';

                    echo '<li>';
                    echo '<a href="departments-stocks.php">';
                    echo '<i class="fa-solid fa-boxes-stacked"></i>';
                    echo '</a>';
                    echo '</li>';
                }
            ?>
            <li>
                <a href="inbox.php" id="inbox-link">
                    <i class="fa-solid fa-inbox"></i>
                </a>
            </li> 
            <li>
                <a href="history.php">
                    <i class="fa-regular fa-clock"></i>
                </a>
            </li>  
            <li>
                <a href="returns.php">
                <i class="fa-solid fa-toolbox"></i>
                </a>
            </li> 
            <?php 
                if($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<a href="requisition-slips.php">';
                    echo '<i class="fa-solid fa-box-archive"></i>';
                    echo '</a>';
                    echo '</li>';
                }
            ?>
            <?php 
                if($_SESSION['access_level'] == 'admin' || $_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<a href="users.php">';
                    echo '<i class="fa-solid fa-users"></i>';
                    echo '</a>';
                    echo '</li>';
                }
            ?>
            
            
        </ul>

        
        <ul class="sub-menu-links" id="sub-menu-links">
            <li>
                <ul class="sub-menu">
                    <li class="sub-menu-link">
                        <a href="inventory.php" id="inventory-link" class="link-name">Inventory</a>
                    </li>
                </ul>
            </li>
            <?php 
                if($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<ul class="sub-menu">';
                    echo '<li class="sub-menu-link">';
                    echo '<a href="department-request.php" class="link-name">Overview</a>';
                    echo '</li>';
                    echo '</ul>';
                    echo '</li>';

                    echo '<li>';
                    echo '<ul class="sub-menu">';
                    echo '<li class="sub-menu-link">';
                    echo '<a href="departments-stocks.php" class="link-name">Department</a>';
                    echo '</li>';
                    echo '</ul>';
                    echo '</li>';
                }
            ?>
            <li>
                <ul class="sub-menu">
                    <li class="sub-menu-link">
                        <a href="inbox.php" id="inbox-link" class="link-name">Inbox</a>
                    </li>
                </ul>
            </li> 
            <li>
                <ul class="sub-menu">
                    <li class="sub-menu-link">
                        <a href="history.php" class="link-name">History</a>
                    </li>
                </ul>
            </li>  
            <li>
                <ul class="sub-menu">
                    <li class="sub-menu-link">
                        <a href="returns.php" class="link-name">Borrowed</a>
                    </li>
                </ul>
            </li>
            <?php 
                if($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<ul class="sub-menu">';
                    echo '<li class="sub-menu-link">';
                    echo '<a href="requisition-slips.php" class="link-name">Requisition Slips</a>';
                    echo '</li>';
                    echo '</ul>';
                    echo '</li>';
                }
            ?>
            <?php 
                if($_SESSION['access_level'] == 'admin' || $_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<ul class="sub-menu">';
                    echo '<li class="sub-menu-link">';
                    echo '<a href="users.php" class="link-name">Users</a>';
                    echo '</li>';
                    echo '</ul>';
                    echo '</li>';
                }
            ?>
            
        </ul>
    </div>



    <div class="navigation-container-large">
        <ul>
            <li class="logo">
                <img  src="../resources/logo.png" alt="">
                <div class="organization-name">
                    ATHS Inventory
                    <div class="organization-system">
                        Management System
                    </div>
                </div>
                <div class="arrow">
                    <a href="">
                        <i class="fa-solid fa-circle-chevron-left"></i>
                    </a>
                </div>
            </li>
        </ul>
        <ul class="main-menu">
            <div class="label">
                Main menu
            </div>
            <li>
                <a href="inventory.php">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Inventory</span>
                    
                </a>
            </li>
            <?php 
                if($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<a href="department-request.php">';
                    echo '<i class="fa-solid fa-clipboard-list"></i>';
                    echo '<span>Overview</span>';
                    echo '</a>';
                    echo '</li>';

                    echo '<li>';
                    echo '<a href="departments-stocks.php">';
                    echo '<i class="fa-solid fa-boxes-stacked"></i>';
                    echo '<span>Department</span>';
                    echo '</a>';
                    echo '</li>';
                }
            ?>
            <li>
                <a href="inbox.php">
                    <i class="fa-solid fa-inbox"></i>
                    <span>Inbox</span>
                </a>
            </li>
            <li>
                <a href="history.php">
                    <i class="fa-regular fa-clock"></i>
                    <span>History</span>
                </a>
            </li>
            <li>
                <a href="returns.php">
                    <i class="fa-solid fa-toolbox"></i>
                    <span>Borrowed</span>
                </a>
            </li>
            <?php 
                if($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<a href="requisition-slips.php">';
                    echo '<i class="fa-solid fa-box-archive"></i>';
                    echo '<span>Requisition Slips</span>';
                    echo '</a>';
                    echo '</li>';
                }
            ?>
            <?php 
                if($_SESSION['access_level'] == 'admin' || $_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
                    echo '<li>';
                    echo '<a href="users.php">';
                    echo '<i class="fa-solid fa-users"></i>';
                    echo '<span>Users</span>';
                    echo '</a>';
                    echo '</li>';
                }
            ?>
            

        </ul>
    </div>
    <div class="navigation-container-horizontal">
        <div class="notification" id="title-link">

        </div>
        <div class="notification2">
            <a href="">
                <!--<i class="fa-regular fa-bell"></i>-->
            </a>
            <button class="circle" id="profile-btn" onclick="showSubMenu()">
                <span><?php echo $_SESSION['initials'];?></span>
            </button>
            <div class="profile-sub-menu">
                <div class="name">
                    <?php echo strtoupper($_SESSION['name']); ?>
                </div>
                <ul>
                    <li><button id="settings-button" onclick="showFloatingSettingsContainer()">Settings</button></li>
                    <li><a href="?logout=true">Logout</a></li>
                </ul>
            </div>
        </div>
        
    </div>
</body>
<script>
    function showSubMenu() {
        const profileSubMenu = document.querySelector('.profile-sub-menu');
        if (profileSubMenu.style.display === 'block') {
            profileSubMenu.style.display = 'none';
        } else {
            profileSubMenu.style.display = 'block';
        }
    }


    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener to the arrow icon
        document.querySelector('.arrow a').addEventListener('click', function(event) {
            // Prevent default behavior of anchor tag
            event.preventDefault();
            // Hide navigation containers
            document.querySelector('.navigation-container').style.display = 'none';
            document.querySelector('.navigation-container-large').transition = 'all 0.5s';
            document.querySelector('.navigation-container-large').transform = 'scale(1.5)';
            document.querySelector('.navigation-container-large').style.display = 'block';
            
            document.querySelector('.notification').style.marginLeft = '370px';
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the elements
        const linksList = document.querySelector('.links');
        const subMenuLinks1 = document.querySelectorAll('.sub-menu-links');
        const subMenuLinks = document.querySelectorAll('.sub-menu');

        // Add event listeners to each link in the links list
        linksList.querySelectorAll('li').forEach((link, index) => {
            link.addEventListener('mouseover', function() {
                // Show the corresponding submenu link
                subMenuLinks[index].style.opacity = '1';
                subMenuLinks[index].style.transition = '.4s';
                subMenuLinks[index].style.transform = 'translateX(20%)';
            });

            // Add event listener to hide the corresponding submenu link when mouse leaves the link
            link.addEventListener('mouseout', function() {
                // Hide the corresponding submenu link
                subMenuLinks[index].style.opacity = '0';
                subMenuLinks[index].style.transition = '.4s';
                subMenuLinks[index].style.transform = 'translateX(-100%)';
            });
        });
    });

    function showFloatingSettingsContainer() {
        if (document.querySelector('.floating-settings-container').style.display === 'block') {
            document.querySelector('.floating-settings-container').style.display = 'none';
        } else {
            document.querySelector('.floating-settings-container').style.display = 'block';
        }
    }

    /*document.getElementById('settings-button').addEventListener('click', function(event) {
        event.preventDefault();
        console.log('Settings button clicked1');
        document.querySelector('.floating-settings-container').style.display = 'block';
        console.log('Settings button clicked');
    });*/
    


    function changeNavBarTitle(newTitle) {
        document.getElementById('title-link').textContent = newTitle;
    }

    <?php if(isset($_GET['act'])): ?>
        // Get the PHP flag value and embed it into JavaScript
        var actValue = "<?php echo $_GET['act']; ?>";

        // Check if the flag value equals 'CH-CATEGORY' and set display accordingly
        if (actValue === 'CH-PASSWORD') {
            document.querySelector('.floating-settings-container').style.display = 'block';
            showContent(1);
        }
    <?php endif; ?>

    


</script>
</html>