<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }

    require '../php/connection.php';
    require '../php/email.php';

    $email_message = (isset($email_message) && $email_message != '' ) ? $email_message : "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email-entered'])) {
        $email = $_POST['email'];
        
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        $resultCheck = mysqli_num_rows($result);
        if ($resultCheck > 0) {
            sendOTP($email, $conn);
            $email_message = "OTP sent";
            $_SESSION['email'] = $email;
        } else {
            $email_message = "Email not found";
        }

        echo $email_message;
        exit;
    }

    $otp_message = (isset($otp_message) && $otp_message != '' ) ? $otp_message : "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp-entered'])) {
        $otp = $_POST['otp'];
        $email = $_SESSION['email'];

        $sql = "SELECT * FROM users WHERE email = '$email' AND otp = '$otp'";
        $result = mysqli_query($conn, $sql);
        $resultCheck = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if ($resultCheck > 0) {
            if (strtotime($row['otp_expiry']) < time()) {
                $otp_message = "OTP expired";
            } else {
                $otp_message = "OTP verified";
            }
        } else {
            $otp_message = "Invalid OTP";
        }

        echo $otp_message;
        exit;
    }

    $password_message = (isset($password_message) && $password_message != '' ) ? $password_message : "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password-reset'])) {
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];
        

        if ($new_password == $confirm_password) {
            $email = $_SESSION['email'];
            $new_password = md5($new_password);
            $sql = "UPDATE users SET password = '$new_password', otp = NULL, otp_expiry = NULL WHERE email = '$email'";
            mysqli_query($conn, $sql);
            if (mysqli_affected_rows($conn) > 0) {
                $password_message = "Password changed successfully.";
                session_destroy();
            } else {
                $password_message = "An error occurred. Please try again later.";
            }
            
        } else {
            $password_message = "Passwords do not match.";
        }

        // Return the updated page content for the AJAX request
        echo $password_message;
        exit;
    }


    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend_otp') {
        $email = $_POST['email'];

        // Validate the email and resend OTP
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            sendOTP($email, $conn);
            echo 'OTP sent';
        } else {
            echo 'Email not found';
        }
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/reset-password.css">
    <title>Document</title>
    <style>
        #resend-link {
            display: none;
        }
        #resend-link {
        color: blue;
        cursor: pointer;
        text-decoration: underline;
    }

    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Email Entry Container -->
        <div class="email-entry-container">
            <h2>Reset Password</h2>
            <form action="" method="POST" id="email-entry">
                <div class="instructions">
                    Please enter your email address. We will send you an OTP to reset your password.
                </div><br>
                <label for="email">Email:</label>
                <input type="text" name="email" id="email" placeholder="Enter your email"><br>
                <input type="hidden" name="email-entered">
                <p id="email-message"></p>
                <input type="submit" value="Submit" id="submit-button">
                <button id="cancel-button">Cancel</button>
            </form>
        </div>

        <!-- OTP Entry Container -->
        <div class="otp-entry-container">
            <h2>Reset Password</h2>
            <div class="instructions">
                Please check your email for the OTP we sent you. Enter it below to reset your password.
            </div><br>
            <form action="" method="POST" id="otp-entry">
                <label for="otp">OTP:</label>
                <input type="text" name="otp" id="otp" maxlength="6" placeholder="Enter the OTP"><br>
                <input type="hidden" name="otp-entered">
                <p id="otp-message"></p>
                <p id="resend-otp">You can resend the OTP in <span id="timer">60</span> seconds.</p>
                <a href="#" id="resend-link">Resend OTP</a>
                <input type="submit" value="Submit">
                <button id="cancel-button">Cancel</button>
            </form>
        </div>

        <!-- Reset Password Container -->
        <div class="reset-password-container">
            <h2>Reset Password</h2>
            <div class="instructions">
                Please enter your new password below.
            </div><br>
            <form action="" method="POST" id="reset-password">
                <label for="password">New Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter your new password"><br>
                <label for="confirm-password" id="confirm-password-label">Confirm Password:</label>
                <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm your new password"><br>
                <input type="hidden" name="password-reset">
                <p id="password-message"></p>
                <input type="submit" value="Reset Password">
            </form>
        </div>
    </div>

    <script>
        let resendTimer;
        let countdown = 60; // Time in seconds

        function startResendTimer() {
            $('#resend-otp').show();
            $('#resend-button').hide();
            resendTimer = setInterval(function() {
                countdown--;
                $('#timer').text(countdown);
                if (countdown <= 0) {
                    clearInterval(resendTimer);
                    $('#resend-otp').hide();
                    $('#resend-link').show();
                    countdown = 180; // Reset countdown
                }
            }, 1000);
        }

        function resendOtp() {
            $('#resend-link').hide();

            // Make AJAX request to resend OTP
            $.ajax({
                url: '', // URL of your PHP script handling OTP resend
                type: 'POST',
                data: { action: 'resend_otp', email: localStorage.getItem('email') }, // Pass email and action
                success: function(response) {
                    var responseString = typeof response === 'string' ? response : JSON.stringify(response);
                    responseString = responseString.trim();

                    if (responseString === 'OTP sent') {
                        // Start timer to prevent resending immediately
                        $('#otp-message').text('OTP sent').css('color', 'green');
                        startResendTimer();
                    } else {
                        // Handle failure case
                        $('#resend-link').show(); // Show resend link if there's an error
                        console.error('Failed to resend OTP:', responseString);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' ' + error);
                    $('#resend-link').show(); // Show resend link on AJAX error
                }
            });
        }


        $(document).ready(function() {
            // Check and apply stored state on page load
            var isOtpSent = localStorage.getItem('otpSent') === 'true';
            var isOtpVerified = localStorage.getItem('otpVerified') === 'true';
            if (isOtpSent) {
                $('.email-entry-container').hide();
                $('.otp-entry-container').show();
                startResendTimer();
            }
            if (isOtpVerified) {
                $('.email-entry-container').hide();
                $('.otp-entry-container').hide();
                $('.reset-password-container').show();
                setTimeout(resetOtpState, 15 * 60 * 1000);
            }

            $('#email-entry').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting the default way
                $('#email-message').text('Please wait');
                $.ajax({
                    url: '', // The current page will handle the request
                    type: 'POST',
                    data: $(this).serialize(), // Serialize form data
                    success: function(response) {
                        // Parse response to ensure it’s a string
                        var responseString = typeof response === 'string' ? response : JSON.stringify(response);
                        responseString = responseString.trim();

                        console.log(responseString);

                        if (responseString === 'OTP sent') {
                            // Store state in localStorage
                            localStorage.setItem('otpSent', 'true');
                            localStorage.setItem('email', $('#email').val());
                            // Ensure these actions always run
                            $('.email-entry-container').hide();
                            $('.otp-entry-container').show();
                            startResendTimer();
                        } else {
                            // Update the message if OTP was not sent
                            $('#email-message').text(responseString).css('color', 'red');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle any AJAX errors
                        console.error('AJAX Error: ' + status + ' ' + error);
                        $('#email-message').text('An error occurred. Please try again later.').css('color', 'red');
                    }
                });
            });

            $('#otp-entry').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting the default way
                if (localStorage.getItem('otpSent') === 'true') {

                }
                $.ajax({
                    url: '', // The current page will handle the request
                    type: 'POST',
                    data: $(this).serialize(), // Serialize form data
                    success: function(response) {
                        // Parse response to ensure it’s a string
                        var responseString = typeof response === 'string' ? response : JSON.stringify(response);
                        responseString = responseString.trim();

                        console.log(responseString);

                        if (responseString === 'OTP verified') {
                            $('#otp-message').text(responseString).css('color', 'green');
                            localStorage.setItem('otpVerified', 'true');
                            localStorage.setItem('otpVerifiedTime', new Date().getTime());

                            $('.email-entry-container').hide();
                            $('.otp-entry-container').hide();
                            $('.reset-password-container').show();

                            setTimeout(resetOtpState, 15 * 60 * 1000);
                        } else {
                            $('#otp-message').text(responseString).css('color', 'red');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                        $('#otp-message').text('An error occurred. Please try again later.').css('color', 'red');
                    }
                });
            });

            $('#cancel-button').on('click', function(event) {
                event.preventDefault(); // Prevent the default action of the button
                
                var userConfirmed = confirm('Are you sure you want to cancel? You will be redirected to the login page.');

                if (userConfirmed) {
                    resetOtpState();
                    window.location.href = 'login.php';
                }
            });

            $('#reset-password').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting the default way
                $.ajax({
                    url: '', // The current page will handle the request
                    type: 'POST',
                    data: $(this).serialize(), // Serialize form data
                    success: function(response) {
                        var responseString = typeof response === 'string' ? response : JSON.stringify(response);
                        responseString = responseString.trim();

                        console.log(responseString);

                        if (responseString === 'Password changed successfully.') {
                            var countdownSeconds = 3; // Number of seconds to countdown

                            function updateCountdown() {
                                $('#password-message').html(responseString + '<br> You will be redirected to the login page in ' + countdownSeconds + ' seconds.');
                                $('#password-message').css('color', 'green');
                                
                                countdownSeconds--;

                                if (countdownSeconds < 0) {
                                    resetOtpState();
                                    window.location.href = 'login.php';
                                } else {
                                    setTimeout(updateCountdown, 1000);
                                }
                            }

                            updateCountdown();
                        } else {
                            $('#password-message').text(responseString).css('color', 'red');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                        $('#password-message').text('An error occurred. Please try again later.').css('color', 'red');
                    }
                });
            });

            function resetOtpState() {
                localStorage.removeItem('otpSent');
                localStorage.removeItem('otpVerified');
                localStorage.removeItem('otpVerifiedTime');
                $('.email-entry-container').show();
                $('.otp-entry-container').hide();
                $('.reset-password-container').hide();
            }
        });

        $('#resend-link').on('click', function(event) {
            event.preventDefault(); // Prevent the default link behavior
            resendOtp();
        });

        window.addEventListener('beforeunload', function(event) {
            if (localStorage.getItem('otpVerified') === 'true') {
                const message = 'Are you sure you want to leave? Your password reset process will be cancelled.';
                event.returnValue = message; // For modern browsers
                return message; // For older browsers
            }
        });

        window.addEventListener('unload', function() {
            if (localStorage.getItem('otpVerified') === 'true') {
                // Clear localStorage and reset OTP state
                localStorage.clear();
                resetOtpState();
            }
        });



    </script>
</body>
</html>

