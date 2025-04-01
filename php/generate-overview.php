<?php
require('../fpdf186/fpdf.php');
require '../php/connection.php';
require_once '../php/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it hasn't been started already
}

// Define the custom PDF class
class PDF extends FPDF {
    function Header() {
        // Add image to all pages
        $this->Image('../resources/aths-logo-transparent.png', 60, 100, 95, 95);
    
        // Add image only on the first page
        if ($this->PageNo() == 1) {
            $this->Image('../resources/athsss.png', 45, 7, 15, 15);
        }
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Initialize the PDF
$pdf = new PDF();
$pdf->AddPage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = $_POST['department_id'];
    $school_year = $_POST['school_year'];
    $generate_pdf = isset($_POST['generate_pdf']) ? $_POST['generate_pdf'] : false;
}
if ($generate_pdf == true) {
    // Fetch department name
    $sql_get_department_name = "SELECT * FROM departments WHERE department_id = $department_id";
    $result_get_department_name = $conn->query($sql_get_department_name);
    $row_get_department_name = $result_get_department_name->fetch_assoc();
    $department_name = $row_get_department_name['department_name'];

    // Add content to the PDF
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(0, 5, 'ASSUMPTA TECHNICAL HIGH SCHOOL', 0, 1, "C");
    $pdf->Cell(0, 5, 'Sta. Monica, San Simon, Pampanga', 0, 1, "C");

    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 5, '', 0, 1);
    $pdf->Cell(0, 5, 'DEPARTMENT REQUEST', 0, 1, "C");

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 5, '', 0, 1);
    $pdf->Cell(145, 5, '', 0, 0);
    $pdf->Cell(50, 5, date("F j, Y"), 0, 1);

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 5, '', 0, 1);
    $pdf->Cell(15, 5, 'Department:', 0, 0);
    $pdf->Cell(120, 5, '', 0, 0);
    $pdf->Cell(35, 5, 'School Year: ', 0, 0);
    $pdf->Cell(35, 5, '', 0, 0);
    $detailsPositions = $pdf->GetY();
    $pdf->Cell(0, 10, '', 0, 1);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetY($detailsPositions);
    $pdf->Cell(24, 5, '', 0, 0);
    $pdf->Cell(102, 5, $department_name, 0, 0);
    $pdf->Cell(35, 5, '', 0, 0);
    $pdf->Cell(35, 5, $school_year, 0, 0);
    $pdf->Cell(0, 10, '', 0, 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 10, 'Date', 1, 0, "C");
    $pdf->Cell(75, 10, 'Items', 1, 0, "C");
    $pdf->Cell(20, 10, 'Price', 1, 0, "C");
    $pdf->Cell(20, 10, 'Quantity', 1, 0, "C");
    $pdf->Cell(20, 10, 'Unit', 1, 0, "C");
    $pdf->Cell(25, 10, 'Total', 1, 1, "C");

    $pdf->SetFont('Arial', '', 10);

    // Fetch stock monitoring data
    $sql = "SELECT * FROM stock_monitoring st
            JOIN departments d ON st.charged_department_id = d.department_id
            JOIN items i ON st.item_id = i.item_id
            WHERE st.charged_department_id = $department_id
            AND st.school_year = '$school_year'
            AND st.is_shown = 'yes'";
    $result = $conn->query($sql);

    $total = 0;
    $totalprice = 0;
    while ($row = $result->fetch_assoc()) {
        $date = new DateTime($row['release_date']);
        $formattedDate = $date->format('M j, Y');
        
        $pdf->Cell(30, 5, $formattedDate, 1, 0, "C");
        $pdf->Cell(75, 5, $row['item_name'], 1, 0, "C");
        $pdf->Cell(20, 5, $row['item_cost'], 1, 0, "C");
        $pdf->Cell(20, 5, $row['requested_quantity_general'], 1, 0, "C");
        $pdf->Cell(20, 5, $row['unit'], 1, 0, "C");

        if ($row['is_borrowable'] == 'yes') {
            if ($row['purpose'] == 'Returned') {
                $totalprice = $row['item_cost'] * $row['requested_quantity_general'];
                $pdf->Cell(25, 5, '- '. number_format($totalprice, 2, '.', ','), 1, 0, "C");
                $pdf->Cell(-3, 5, 'R', 0, 1, "C");
            } else {
                $totalprice = $row['item_cost'] * $row['requested_quantity_general'];
                $pdf->Cell(25, 5, number_format($totalprice, 2, '.', ','), 1, 0, "C");
                $pdf->Cell(-3, 5, 'N', 0, 1, "C");
                $total += ($row['item_cost'] * $row['requested_quantity_general']);
            }
        } else {
            $totalprice = $row['item_cost'] * $row['requested_quantity_general'];
            $pdf->Cell(25, 5, number_format($totalprice, 2, '.', ','), 1, 1, "C");
            $total += ($row['item_cost'] * $row['requested_quantity_general']);
        }
    }

    $pdf->Cell(145, 5, '', 0, 0);
    $pdf->Cell(20, 5, 'Total:', 1, 0, "C");
    $pdf->Cell(25, 5, number_format($total, 2, '.', ','), 1, 1, "C");

    // Output the PDF
    $pdf->Output();
}
?>
