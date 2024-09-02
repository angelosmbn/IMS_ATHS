<?php 
    require '../php/connection.php';
    session_start();

    //check if there is a current session
    if (isset($_SESSION['username'])) {
        echo "<script>window.location.href='inventory.php'</script>";
    }

    // Set the initial value of the login and signup error message
    $login_message = (isset($login_message) && $login_message != '' ) ? $login_message : "";
    $signup_message = (isset($signup_message) && $signup_message != '' ) ? $signup_message : "";

    // Check if the user is trying to login
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_form']) && $_POST['login_form'] == 1) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password-login']);
        $hashedPasswordLogin = md5($password);

        // Check if the username and password is correct
        $sql = "SELECT * FROM users WHERE (username = '$username' OR email = '$username') and password = '$hashedPasswordLogin'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['account_status'] == "disabled") {
                echo "<script>
                    alert('Your account is disabled. Please contact the administrator.');
                    window.location.href='login.php'
                    </script>";

            } elseif ($row['account_status'] == "pending") {
                echo "<script>
                    alert('Your account is not yet confirmed. Please wait for the administrator to confirm your account.');
                    window.location.href='login.php'
                    </script>";
            } else{
                $_SESSION['username'] = $row['username'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
                $_SESSION['middle_name'] = $row['middle_name'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['department'] = explode(', ', $row['department']);
                $_SESSION['initials'] = $row['first_name'][0] . $row['last_name'][0];
                $_SESSION['access_level'] = $row['access_level'];
                $_SESSION['email'] = $row['email'];
                if ($row['access_level'] == "coordinator" || $row['access_level'] == "finance officer") {
                    $_SESSION['handled_department'] = $row['handled_department'];
                }
                
                $sql_get_school_year = "SELECT * FROM school_year ORDER BY school_year_id ASC LIMIT 1";
                $result_get_school_year = $conn->query($sql_get_school_year);
                $row_get_school_year = $result_get_school_year->fetch_assoc();
                $_SESSION['school_year'] = $row_get_school_year['school_year'];
                
                


                $_SESSION['access_level'] = $row['access_level'];
                $middle_initial = substr($row['middle_name'], 0, 1);
                if ($middle_initial == "") {
                    $_SESSION['name'] = $row['first_name'] . " " . $row['last_name'];
                }else{
                    $_SESSION['name'] = $row['first_name'] . " " . $middle_initial . ". " . $row['last_name'];
                }
                $_SESSION['profile_name'] = $row['first_name'] . " " . substr($row['last_name'], 0, 1) . ".";
                
                echo "<script>window.location.href='inventory.php'</script>";
            }
            
        }else {
            $login_message = "Invalid username or password.";
        }

        // Return the updated page content for the AJAX request
        echo $login_message;
        exit;
    }

    // Check if the user is trying to signup
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup_form']) && $_POST['signup_form'] == 1) {
        // Extract user input
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        $acc_username = mysqli_real_escape_string($conn, $_POST['acc_username']);
        $password = mysqli_real_escape_string($conn, $_POST['password-signup']);
        $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);
        $creation_date = date("Y-m-d H:i:s");
    
        // Check if the email is already taken
        $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
        $result_email = $conn->query($checkEmailQuery);

        // Check if the username is already taken
        $checkUsernameQuery = "SELECT * FROM users WHERE username = '$acc_username'";
        $result_username = $conn->query($checkUsernameQuery);
    
        if ($result_email->num_rows > 0) {
            // Email is already taken
            $signup_message = "Email is already taken. Please try again.";
        } elseif ($result_username->num_rows > 0) {
            // Username is already taken
            $signup_message = "Username is already taken. Please try again.";
        } else {
            // Email is not taken, proceed with registration
            if ($password == $confirmPassword) {
                // Hash the password before storing it in the database
                $hashedPassword = md5($password);
    
                // Insert user data into the database
                $insertQuery = "INSERT INTO users (first_name, last_name, middle_name, email, contact, username, password, creation_date)
                                VALUES ('$first_name', '$last_name', '$middle_name', '$email', '$contact', '$acc_username', '$hashedPassword', '$creation_date')";
    
                if ($conn->query($insertQuery) === TRUE) {
                    // Registration successful
                    $email = $_POST['email'];
                    $request_id = null;
                    $subject = "Your Signup to ATHS Inventory System is Successful";
                    $body = "Kindly wait for your Admin to confirm your registration and activate your account.";
                    //sendEmail($email, $subject, $body, $request_id, $conn);
                    echo "<script>alert('Registration successful. Wait for your Admin to confirm your registration.')</script>";
                    echo "<script>window.location.href='login.php'</script>";
                } else {
                    echo "Error: " . $insertQuery . "<br>" . $conn->error;
                }
            } else {
                // Passwords do not match
                $signup_message = "Passwords do not match. Please try again.";
            }

            //dito ko nalagay kanina tong 
            //echo $signup_message;
            //exit;

            //dapat nandun sa baba
            
        }
        // Add the other need for login.
        echo $signup_message;
        exit;
    }else{
        // Set the initial value of the input fields
        $first_name = '';
        $last_name = '';
        $middle_name = '';
        $email = '';
        $contact = '';
        $gender = '';
        $password = '';
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/login.css">
    <title>Document</title>
</head>
<body>
    <div class="main">

        <div class="form-container" id="a-container">
            <div class="login-form" id="login-form">
                
                <form action="" name="login" id="login" method="POST" autocomplete="on">
                    <h2>Sign in to Website</h2>
                    <input type="hidden" name="login_form" value="1">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password"  autocomplete="on" name="password-login" placeholder="Password" required>
                    <a href="reset-password.php">Forgot Password?</a>
                    <button type="submit">SIGN IN</button><br>
                    <div id="login-message"></div>
                </form>
            </div>
            <div class="signup-form" id="signup-form">
                
                <form action="" name="signup" id="signup" method="POST" autocomplete="on">
                    <input type="hidden" name="signup_form" value="1">
                    <h2>Sign up</h2>
                    
                        <input type="text" name="first_name" placeholder="First Name" value="<?php echo !empty($first_name) ? $first_name : "" ?>" required>
                        <input type="text" name="middle_name" placeholder="Middle Name" value="<?php echo !empty($middle_name) ? $middle_name : "" ?>">
                    
                        <input type="text" name="last_name" placeholder="Last Name" value="<?php echo !empty($last_name) ? $last_name : "" ?>" required>
                        <input type="text" name="acc_username" placeholder="Username"  value="<?php echo !empty($acc_username) ? $acc_username : "" ?>" required>
                        
                    
                        <input type="text" name="email" placeholder="Email"  value="<?php echo !empty($email) ? $email : "" ?>" required>
                        <input type="text" name="contact" placeholder="Contact" value="<?php echo !empty($contact) ? $contact : "" ?>" required>
                    
                        <input type="password" name="password-signup"  autocomplete="on" placeholder="Password" required >
                        <input type="password" name="confirmPassword"  autocomplete="on" placeholder="Confirm Password" required>
                    
                    <button type="submit">SIGN UP</button>
                    <div id="signup-message"></div>
                </form>
            </div>
        </div>

        <div class="welcome-container" id="b-container">
            <h2 id="welcome-container-h2">Don't have an account?</h2>
            <button id="welcome-container-button" onclick="toggleSignup()">SIGN UP</button>
            <div class="circle1" id="circle1"></div>
            <div class="circle2" id="circle2"></div>
        </div>
    </div>
    <script>
        var a =document.getElementById("a-container");
        var b =document.getElementById("b-container");
        var bH2 =document.getElementById("welcome-container-h2");
        var bButton =document.getElementById("welcome-container-button");

        var loginForm =document.getElementById("login-form");
        var signupForm =document.getElementById("signup-form");

        
        var circle1 =document.getElementById("circle1");
        var circle2 =document.getElementById("circle2");

        // Set the initial form based on PHP variable

        // Function to toggle the login and signup form
        function toggleSignup() {
            if (bButton.innerHTML === "SIGN UP") {
                setTimeout(function () {

                    a.style.left = "300px";
                    b.style.left = "-500px";

                    a.style.borderTopLeftRadius = "0px";
                    a.style.borderBottomLeftRadius = "0px";
                    b.style.borderTopRightRadius = "0px";
                    b.style.borderBottomRightRadius = "0px";

                    a.style.borderTopRightRadius = "10px";
                    a.style.borderBottomRightRadius = "10px";
                    b.style.borderTopLeftRadius = "10px";
                    b.style.borderBottomLeftRadius = "10px";

                    circle1.style.left = "50%";
                    circle2.style.left = "-40%";


                    bH2.classList.add("fadeOut");
                    bButton.classList.add("fadeOut");
                    setTimeout(function () {
                        b.style.width = "500px";
                        b.classList.remove("widen-b");
                        bH2.classList.remove("fadeOut");
                        bH2.classList.add("fadeIn");
                        bButton.classList.remove("fadeOut");
                        bButton.classList.add("fadeIn");
                        bH2.innerHTML = "Already have an account?";
                        bButton.innerHTML = "SIGN IN";
                        
                    }, 700);
                    setTimeout(function () {
                        signupForm.style.display = "block";
                        loginForm.style.display = "none";
                    }, 600);
                    
                    
                });
            }else{
                setTimeout(function () {

                    a.style.left = "0px";
                    b.style.left = "0px";

                    a.style.borderTopLeftRadius = "10px";
                    a.style.borderBottomLeftRadius = "10px";
                    b.style.borderTopRightRadius = "10px";
                    b.style.borderBottomRightRadius = "10px";

                    a.style.borderTopRightRadius = "0px";
                    a.style.borderBottomRightRadius = "0px";
                    b.style.borderTopLeftRadius = "0px";
                    b.style.borderBottomLeftRadius = "0px";

                    circle1.style.left = "0%";
                    circle2.style.left = "10%";
                    
                    bH2.classList.add("fadeOut");
                    bButton.classList.add("fadeOut");
                    setTimeout(function () {
                        bH2.classList.remove("fadeOut");
                        bH2.classList.add("fadeIn");
                        bButton.classList.remove("fadeOut");
                        bButton.classList.add("fadeIn");
                        bH2.innerHTML = "Don't have an account?";
                        bButton.innerHTML = "SIGN UP";
                    }, 700);
                    setTimeout(function () {
                            signupForm.style.display = "none";
                            loginForm.style.display = "block";
                    }, 600);
                });
            }
        }

        $(document).ready(function() {
            $('#login').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting the default way
                $.ajax({
                    url: '', // The current page will handle the request
                    type: 'POST',
                    data: $(this).serialize(), // Serialize form data
                    success: function(response) {
                        // Extract the message and new username from the response
                        var responseString = typeof response === 'string' ? response : JSON.stringify(response);
                        responseString = responseString.trim();

                        console.log(responseString);

                        // Update the username message dynamically
                        $('#login-message').html(responseString);
                        $('#login-message').css('color', 'red');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                        $('#login-message').html('An error occurred. Please try again later.');
                        $('#login-message').css('color', 'red');
                    }
                });
            });
        });

        $(document).ready(function() {
            $('#signup').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting the default way
                $.ajax({
                    url: '', // The current page will handle the request
                    type: 'POST',
                    data: $(this).serialize(), // Serialize form data
                    success: function(response) {
                        // Extract the message and new username from the response
                        var responseString = typeof response === 'string' ? response : JSON.stringify(response);
                        responseString = responseString.trim();

                        console.log(responseString);

                        // Update the username message dynamically
                        $('#signup-message').html(responseString);
                        $('#signup-message').css('color', 'red');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                        $('#signup-message').html('An error occurred. Please try again later.');
                        $('#signup-message').css('color', 'red');
                    }
                });
            });
        });
    </script>


</body>
</html>
