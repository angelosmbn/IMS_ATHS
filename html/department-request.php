<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Start the session if it hasn't been started already
    }
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != "") {
        if ($_SESSION['access_level'] == 'inventory manager' || $_SESSION['access_level'] == 'finance officer') {
            // Allow access
        } else {
            echo "<script>alert('You do not have permission to access this page!')</script>";
            echo "<script>window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Please login first!')</script>";
        echo "<script>window.location.href='login.php';</script>";
    }
    require '../php/connection.php';
    require 'navigation-bar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/department-request.css">
    <script
    src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
    crossorigin="anonymous"></script>
</head>
<body>
    <div class="inbox-container" id="inbox-container">
        <form class="settings-container">
            <div>
                <label for="department">Department:</label>
                <select name="department_id" id="department_id" class="scrollable-select" required>
                    <?php 
                        $sql_get_departments = "SELECT * FROM departments";
                        $result_get_departments = $conn->query($sql_get_departments);
                        if ($result_get_departments->num_rows > 0) {
                            while ($row_get_departments = $result_get_departments->fetch_assoc()) {
                                
                            echo '<script>console.log("Department ID: ", '. $row_get_departments['department_id'] .')</script>';
                            echo "<option value='". $row_get_departments['department_id'] ."'>". $row_get_departments['department_name'] ."</option>";
                            }
                        }
                    ?>
                </select>
            </div>
            <div>
                <label for="school-year">School Year:</label>
                <select name="school-year" id="school-year">
                <?php 
                    $sql_get_school_years = "SELECT * FROM school_year";
                    $result_get_school_years = $conn->query($sql_get_school_years);
                    if ($result_get_school_years->num_rows > 0) {
                        while ($row_get_school_years = $result_get_school_years->fetch_assoc()) {
                            echo "<option value='". $row_get_school_years['school_year'] ."'>". $row_get_school_years['school_year'] ."</option>";
                        }
                    }
                ?>
            </select>

            </div>
            
            <div>
                <button id="generate-overview" onclick="generatePDF()">Generate PDF</button>
                <button type="submit" id="view-results">View Results</button>
            </div>
        </form>
        <div class="inbox-table">
            <table>
                <tr class="sticky-row">
                    <th>Date</th>
                    <th>Items</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th colspan="2">Total</th>
                </tr>
            </table>
        </div>
    </div>
</body>
<script>
    $(document).ready(function() {
        $('#view-results').click(function(e) {
            e.preventDefault();

            var department_id = $('#department_id').val();
            var school_year = $('#school-year').val();

            // Check if department_id has a value
            if (!department_id) {
                alert('Please select a department.');
                return; // Exit the function if department_id is empty
            }

            $.ajax({
                type: 'POST',
                url: '../php/view-requested-items.php',
                data: {
                    department_id: department_id,
                    school_year: school_year
                },
                success: function(data) {
                    $('.inbox-table').html(data);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' ' + error);
                }
            });
        });
    });


    $(document).ready(function() {
    $('#generate-overview').click(function(e) {
        e.preventDefault();

        var department_id = $('#department_id').val();
        var school_year = $('#school-year').val();

        // Check if department_id has a value
        if (!department_id) {
            alert('Please select a department.');
            return; // Exit the function if department_id is empty
        }

        var ajaxUrl = ''; // Initialize the URL
        var postData = {}; // Initialize the POST data

        // Choose the URL and data based on department_id
        if (department_id != 0) {
            ajaxUrl = '../php/generate-overview.php';
            postData = {
                department_id: department_id,
                school_year: school_year,
                generate_pdf: true // Parameter to indicate PDF generation
            };
        } else {
            ajaxUrl = '../php/generate_summary.php';
            postData = {
                school_year: school_year,
                generate_pdf: true // Parameter to indicate PDF generation
            };
        }

        // Use AJAX to send the data to the PHP file and handle the PDF generation
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: postData,
            success: function(response) {
                // Create a Blob from the response and open it in a new tab
                var blob = new Blob([response], { type: 'application/pdf' });
                var url = URL.createObjectURL(blob);
                window.open(url, '_blank');
            },
            error: function(xhr, status, error) {
                console.error("Error generating PDF:", error);
            },
            xhrFields: {
                responseType: 'blob' // Set the response type to blob for PDF
                }
            });
        });
    });




    changeNavBarTitle('Department Request');
</script>

</html>