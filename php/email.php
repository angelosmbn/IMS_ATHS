<?php 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require "../phpmailer/src/Exception.php";
    require "../phpmailer/src/PHPMailer.php";
    require "../phpmailer/src/SMTP.php";

    function sendEmail($recipientEmail, $request_id, $conn) {
        
        $mail = new PHPMailer(true);
    
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'angelosimbulan16@gmail.com';
        $mail->Password = ''; //password not written for security purposes
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
    
        $mail->setFrom('angelosimbulan16@gmail.com', 'ATHS INVENTORY SYSTEM');
    
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = "The Items You Requested Have Been Released";
        // Adding HTML and CSS for email body design

        // Fetch data from the database
        $sql = "SELECT * FROM stock_monitoring st
        JOIN departments d ON st.requesting_department_id = d.department_id
        JOIN items i ON st.item_id = i.item_id
        JOIN requests r ON st.request_id = r.request_id
        WHERE st.request_id = '$request_id'";
        $result = mysqli_query($conn, $sql);
        $resultCheck = mysqli_num_rows($result);

        // Start building the email body
        $mail->Body = "
        <html>
            <head>
                <style>
                /* Example CSS styles */
                body {
                    font-family: Arial, sans-serif;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f4f4f4;
                    border-radius: 10px;
                }
                h1 {
                    color: #333;
                }
                p {
                    color: #555;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                }
                th {
                    background-color: #f2f2f2;
                    text-align: left;
                }
                </style>
            </head>
            <body>
                <div class='container'>
                <h1>Your Items are Ready</h1>
                    <table>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                        </tr>";

        // Loop through the results and append to the email body
        if ($resultCheck > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $mail->Body .= "
                    <tr>
                        <td>" . htmlspecialchars($row['item_name']) . "</td>
                        <td>" . htmlspecialchars($row['requested_quantity_general']) . "</td>
                    </tr>";
            }
        }

        // Close the table and the rest of the HTML
        $mail->Body .= "
                    </table>
                    <p>
                        Please acknowledge receipt of your request by marking it as received on the Inbox page of the Assumpta Inventory Management System. 
                        If you need a copy of your requisition slip, you can download it from the History page once your request has been marked as received.
                        Click <a href='http://localhost/IMS_ATHS/html/index.php'>here</a> to access the Assumpta Inventory Management System.
                    </p>
                </div>
            </body>
        </html>";
    
        $mail->send();
    }



    function generateOTP($length = 6) {
        $characters = '0123456789';
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $otp;
    }
    
    function sendOTP($recipientEmail, $conn) {
        $otp = generateOTP();  // Generate a 6-digit OTP
        $expiry = date("Y-m-d H:i:s", time() + 900); // OTP valid for 15 minutes
    
        // Store the OTP and its expiry in the database
        $sql = "UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $otp, $expiry, $recipientEmail);
        $stmt->execute();
    
        // Initialize PHPMailer
        $mail = new PHPMailer(true);
    
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'angelosimbulan16@gmail.com';
        $mail->Password = ''; // Replace with your actual password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
    
        $mail->setFrom('no-reply@aths.com', 'ATHS INVENTORY SYSTEM');
        $mail->addAddress($recipientEmail);
    
        $mail->isHTML(true);
        $mail->Subject = "Your OTP for Password Reset";
    
        // Build the email body with the OTP
        $mail->Body = "
        <html>
            <head>
                <style>
                body {
                    font-family: Arial, sans-serif;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f4f4f4;
                    border-radius: 10px;
                }
                h1 {
                    color: #333;
                }
                p {
                    color: #555;
                }
                .otp {
                    font-size: 20px;
                    font-weight: bold;
                    color: #000;
                }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>Password Reset Request</h1>
                    <p>You have requested to reset your password. Use the OTP below to reset your password:</p>
                    <p class='otp'>$otp</p>
                    <p>This OTP is valid for 15 minutes.</p>
                    <p>If you did not request a password reset, please ignore this email.</p>
                </div>
            </body>
        </html>";
        // Set reply-to address (where recipients will respond)
        $mail->addReplyTo('inventory-support@aths.edu.ph', 'ATHS Inventory System Support'); // Replace with desired reply-to address
        $mail->send();
    }
    
    
?>
