<?php 
    $password_message = (isset($password_message) && $password_message != '' ) ? $password_message : "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change-password'])) {
        $old_password = md5($_POST['old-password']);
        $new_password = $_POST['new-password'];
        $confirm_password = $_POST['confirm-password'];

        if ($new_password == $confirm_password) {
            $sql = "SELECT * FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($old_password == $row['password']) {
                $new_password = md5($new_password);
                $sql = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_password, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
                $password_message = "Password changed successfully.";
            } else {
                $password_message = "Current password is incorrect.";
            }
        } else {
            $password_message = "Passwords do not match.";
        }

        // Return the updated page content for the AJAX request
        echo $password_message;
        exit;
    }



    $email = $_SESSION['email'];
    $email_message = (isset($email_message) && $email_message != '' ) ? $email_message : "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change-email'])) {
        $new_email = $_POST['new-email'];

        if ($new_email == $email) {
            $email_message = "No changes made.";
        } else {
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $new_email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                $email_message = "Email already exists.";
            } else {
                $sql = "UPDATE users SET email = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_email, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                $email = $new_email;
                $email_message = "Email changed successfully.";
                $email = $new_email;
                $_SESSION['email'] = $new_email;
            }
        }
        // Create an associative array with the data you want to return
        $response = array(
            'message' => $email_message,
            'new_email' => $new_email // Include the new email in the response
        );

        // Encode the array as JSON
        echo json_encode($response);
        exit;
    }

    $username = $_SESSION['username'];
    $username_message = (isset($username_message) && $username_message != '' ) ? $username_message : "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change-username'])) {
        $new_username = $_POST['new-username'];

        if ($new_username == $username) {
            $username_message = "No changes made.";
        } else {
            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $new_username);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                $username_message = "Username already exists.";
            } else {
                $sql = "UPDATE users SET username = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_username, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                $username = $new_username;
                $username_message = "Username changed successfully.";
                $username = $new_username;
                $_SESSION['username'] = $new_username;
            }
        }
        // Create an associative array with the data you want to return
        $response = array(
            'message' => $username_message,
            'new_username' => $new_username // Include the new username in the response
        );

        // Encode the array as JSON
        echo json_encode($response);
        exit;
    }

    $school_year = $_SESSION['school_year'];
    $school_year_message = (isset($school_year_message) && $school_year_message != '' ) ? $school_year_message : "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add-school-year'])) {
        $school_year1 = (int)$_POST['school-year1'];
        $school_year2 = (int)$_POST['school-year2'];
        $new_school_year = "$school_year1-$school_year2";
    
        if (($school_year2 - $school_year1) == 1) {
            if ($new_school_year == $school_year) {
                $school_year_message = "No changes made.";
            } else {
                $sql = "SELECT * FROM school_year WHERE school_year = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $new_school_year);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
    
                if ($result->num_rows > 0) {
                    $school_year_message = "School year already exists.";
                } else {
                    $sql = "INSERT INTO school_year (school_year) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $new_school_year);
                    $stmt->execute();
                    $stmt->close();
    
                    $school_year_message = "School year changed successfully.";
                    $_SESSION['school_year'] = $new_school_year; // Update session with the new school year
                }
            }
        } else {
            $school_year_message = "Invalid school year.";
        }
    
        $response = array(
            'message' => $school_year_message,
            'new_school_year' => $new_school_year
        );
    
        echo json_encode($response);
        exit;
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/settings.css">
    <link rel="stylesheet" href="../fontawesome-free-6.5.1-web/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Document</title>
</head>
<body>
    <div class="floating-settings-container" id="floating-settings-container">
        <div class="bar">
            <span>Settings</span>
            <span class="close-icon" onclick="hideFloatingContainerSettings()">&#10006;</span>
        </div>

        <div class="settings-choices">
            <div class="settings-choice" id="settings-choice1" onclick="showContent(1)">
                <span>Change Password</span>
                <i class="fas fa-key"></i>
            </div>
            <div class="settings-choice" id="settings-choice2" onclick="showContent(2)">
                <span>Change Email</span>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="settings-choice" id="settings-choice3" onclick="showContent(3)">
                <span>Change Username</span>
                <i class="fas fa-user"></i>
            </div>
            <?php 
                if ($_SESSION['access_level'] == 'inventory manager') {
            ?>
            <div class="settings-choice" id="settings-choice4" onclick="showContent(4)">
                <span>Add School Year</span>
                <i class="fas fa-calendar-alt"></i>
            </div>
            <?php 
                }
            ?>
        </div>
        <div class="settings-content">
            <div class="instructions" id="instructions">
                Please select one of the options above.
            </div>
            <div class="settings-content-item" id="change-password">
                <span>Change Password</span>
                <form action="" method="post" id="changePasswordForm">
                    <table>
                        <tr>
                            <td>
                                <label for="old-password">Old Password</label>
                            </td>
                            <td>
                                <input type="password" name="old-password" id="old-password" autocomplete="on" placeholder="Current Password">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="new-password">New Password</label>
                            
                            </td>
                            <td>
                                <input type="password" name="new-password" id="new-password" autocomplete="on" placeholder="New Password">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="confirm-password">Confirm Password</label>
                            </td>
                            <td>
                                <input type="password" name="confirm-password" id="confirm-password" autocomplete="on" placeholder="Confirm Password">
                            </td>
                        </tr>
                        <?php 
                            echo "<tr><td colspan='2' id='password-message'></td></tr>";
                        ?>
                        <tr>
                            <td colspan="2" id="settings-button">
                                <button type="submit">Change Password</button>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="change-password">
                </form>
            </div>
            <div class="settings-content-item" id="change-email">
                <span>Change Email</span>
                <form action="" method="post" id="changeEmailForm">
                    <table>
                        <tr>
                            <td>
                                <label for="current-email">Current Email</label>
                            </td>
                            <td>
                                <span id="current-email"><?php echo $email?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="new-email">New Email</label>
                            </td>
                            <td>
                                <input type="email" name="new-email" id="new-email" placeholder="New Email">
                            </td>
                        </tr>
                        <?php 
                            echo "<tr><td colspan='2' id='email-message'></td></tr>";
                        ?>
                        <tr>
                            <td colspan="2"  id="settings-button">
                                <button type="submit">Change Email</button>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="change-email">
                </form>
            </div>
            <div class="settings-content-item" id="change-username">
                <span>Change Username</span>
                <form action="" method="post" id="changeUsernameForm">
                    <table>
                        <tr>
                            <td>
                                <label for="current-username">Current Username</label>
                            </td>
                            <td>
                                <span id="current-username"><?php echo $username?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="new-username">New Username</label>
                            </td>
                            <td>
                                <input type="text" name="new-username" id="new-username" placeholder="New Username">
                            </td>
                        </tr>
                        <?php 
                            echo "<tr><td colspan='2' id='username-message'></td></tr>";
                        ?>
                        <tr>
                            <td colspan="2"  id="settings-button">
                                <button type="submit">Change Username</button>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="change-username">
                </form>
            </div>
            <?php 
                if ($_SESSION['access_level'] == 'inventory manager') {
                    echo "<script>console.log('Inventory Manager');</script>";
            ?>
            <div class="settings-content-item" id="add-school-year">
                <span>Add School Year</span>
                <form action="" method="post" id="changeSchoolYearForm">
                    <table>
                        <tr>
                            <td>
                                <label for="current-school-year">Current School Year</label>
                            </td>
                            <td>
                                <span id="current-school-year"><?php echo $school_year?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="school-year">New School Year</label>
                            </td>
                            <td id="school-year-input">
                                <input type="text" name="school-year1" id="school-year1" maxlength="4" placeholder="0000">
                                -
                                <input type="text" name="school-year2" id="school-year2" maxlength="4" placeholder="0000">
                            </td>
                        </tr>
                        <?php 
                            echo "<tr><td colspan='2' id='school_year_message'></td></tr>";
                        ?>
                        <tr>
                            <td colspan="2"  id="settings-button">
                                <button type="submit">Add School Year</button>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="add-school-year">
                </form>
            </div>
            <?php 
                }
            ?>
        </div>
    </div>
    <div id="confirmationModal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to commit the new school year?</p>
        <button id="confirmYes">Yes</button>
        <button id="confirmNo">No</button>
    </div>
</div>

</body>
<script>
    

    function hideFloatingContainerSettings() {
        document.getElementById('floating-settings-container').style.display = 'none';
    }
    
    function showContent(content) {
        console.log(content);
        var contentItems = document.getElementsByClassName('settings-content-item');
        for (var i = 0; i < contentItems.length; i++) {
            contentItems[i].style.display = 'none';
        }
        document.getElementById('change-password').style.display = 'none';
        document.getElementById('change-email').style.display = 'none';
        document.getElementById('change-username').style.display = 'none';
        document.getElementById('instructions').style.display = 'none';

        // Check if the element exists before hiding it
        var addSchoolYear = document.getElementById('add-school-year');
        if (addSchoolYear) {
            addSchoolYear.style.display = 'none';
        }

        var settingsChoices = document.getElementsByClassName('settings-choice');
        for (var i = 0; i < settingsChoices.length; i++) {
            settingsChoices[i].style.backgroundColor = 'white';
            settingsChoices[i].style.color = 'black';
        }

        if (content == 1) {
            document.getElementById('change-password').style.display = 'block';
            document.getElementById('settings-choice1').style.backgroundColor = 'rgb(128, 128, 128)';
            document.getElementById('settings-choice1').style.color = 'white';
        } else if (content == 2) {
            document.getElementById('change-email').style.display = 'block';
            document.getElementById('settings-choice2').style.backgroundColor = 'rgb(128, 128, 128)';
            document.getElementById('settings-choice2').style.color = 'white';
        } else if (content == 3) {
            document.getElementById('change-username').style.display = 'block';
            document.getElementById('settings-choice3').style.backgroundColor = 'rgb(128, 128, 128)';
            document.getElementById('settings-choice3').style.color = 'white';
        } else if (content == 4 && addSchoolYear) {
            addSchoolYear.style.display = 'block';
            document.getElementById('settings-choice4').style.backgroundColor = 'rgb(128, 128, 128)';
            document.getElementById('settings-choice4').style.color = 'white';
        }
    }

    // Set the initial form based on PHP variable
    if ("<?php echo $password_message; ?>" === "") {
        console.log('No message');
    } else {
        console.log('Message');
        document.getElementById('change-password').style.display = 'block';
    }


   

    $(document).ready(function() {
        $('#changePasswordForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting the default way
            $.ajax({
                url: '', // The current page will handle the request
                type: 'POST',
                data: $(this).serialize(), // Serialize form data
                success: function(response) {
                    // Assuming response is a string, otherwise convert it accordingly
                    var responseString = typeof response === 'string' ? response : JSON.stringify(response);

                    // Trim leading and trailing whitespace characters from the response string
                    responseString = responseString.trim();
                    
                    console.log(responseString);

                    // Update the password message dynamically
                    $('#password-message').html(responseString);

                    if (responseString === 'Password changed successfully.') {
                        $('#password-message').css('color', 'green');
                        $('#old-password').val('');
                        $('#new-password').val('');
                        $('#confirm-password').val('');
                    } else {
                        $('#password-message').css('color', 'red');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' ' + error);
                }
            });
        });
    });

    $(document).ready(function() {
        $('#changeEmailForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting the default way
            $.ajax({
                url: '', // The current page will handle the request
                type: 'POST',
                data: $(this).serialize(), // Serialize form data
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    // Extract the message and new email from the response
                    var message = response.message;
                    var newEmail = response.new_email;

                    console.log('Message:', message);
                    console.log('New Email:', newEmail);

                    // Update the email message dynamically
                    $('#email-message').html(message);

                    if (message === 'Email changed successfully.') {
                        $('#email-message').css('color', 'green');
                        // Optionally, update the displayed email on the page
                        $('#current-email').text(newEmail);
                        $('#new-email').val('');
                    } else {
                        $('#email-message').css('color', 'red');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' ' + error);
                    $('#email-message').html('An error occurred. Please try again later.');
                    $('#email-message').css('color', 'red');
                }
            });
        });
    });

    $(document).ready(function() {
        $('#changeUsernameForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting the default way
            $.ajax({
                url: '', // The current page will handle the request
                type: 'POST',
                data: $(this).serialize(), // Serialize form data
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    // Extract the message and new username from the response
                    var message = response.message;
                    var newUsername = response.new_username;

                    console.log('Message:', message);
                    console.log('New Username:', newUsername);

                    // Update the username message dynamically
                    $('#username-message').html(message);

                    if (message === 'Username changed successfully.') {
                        $('#username-message').css('color', 'green');
                        // Optionally, update the displayed username on the page
                        $('#current-username').text(newUsername);
                        $('#new-username').val('');
                    } else {
                        $('#username-message').css('color', 'red');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' ' + error);
                    $('#username-message').html('An error occurred. Please try again later.');
                    $('#username-message').css('color', 'red');
                }
            });
        });
    });



    $(document).ready(function() {
        $('#changeSchoolYearForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting the default way

            // Get the values from the form inputs
            var schoolYear1 = $('#school-year1').val();
            var schoolYear2 = $('#school-year2').val();
            var newSchoolYear = schoolYear1 + '-' + schoolYear2;

            // Insert the new school year into the confirmation message
            $('#confirmationModal p').text('Are you sure you want to commit the new school year: ' + newSchoolYear + '?');

            // Show the custom confirmation modal
            $('#confirmationModal').css('display', 'block');

            $('#confirmYes').on('click', function() {
                $('#confirmationModal').css('display', 'none');

                // Proceed with the AJAX request
                $.ajax({
                    url: '', // The current page will handle the request
                    type: 'POST',
                    data: $('#changeSchoolYearForm').serialize(), // Serialize form data
                    dataType: 'json', // Expect JSON response
                    success: function(response) {
                        var message = response.message;
                        var newSchoolYear = response.new_school_year;

                        console.log('Message:', message);
                        console.log('New School Year:', newSchoolYear);

                        $('#school_year_message').html(message);

                        if (message === 'School year changed successfully.') {
                            $('#school_year_message').css('color', 'green');
                            $('#current-school-year').text(newSchoolYear);
                            $('#school-year1').val('');
                            $('#school-year2').val('');
                        } else {
                            $('#school_year_message').css('color', 'red');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                        console.log('Response:', xhr.responseText);
                        $('#school_year_message').html('An error occurred. Please try again later.');
                        $('#school_year_message').css('color', 'red');
                    }
                });
            });

            $('#confirmNo').on('click', function() {
                $('#confirmationModal').css('display', 'none');
                $('#school_year_message').html('Action canceled.');
                $('#school_year_message').css('color', 'orange');
            });
        });
    });









</script>
</html>