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
    $school_year = $_POST['school_year'];
    $generate_pdf = isset($_POST['generate_pdf']) ? $_POST['generate_pdf'] : false;
}
if ($generate_pdf == true) {

    // Add content to the PDF
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(0, 5, 'ASSUMPTA TECHNICAL HIGH SCHOOL', 0, 1, "C");
    $pdf->Cell(0, 5, 'Sta. Monica, San Simon, Pampanga', 0, 1, "C");

    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 5, '', 0, 1);
    $pdf->Cell(0, 5, 'DEPARTMENT REQUEST', 0, 1, "C");

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 5, '', 0, 1);
    $pdf->Cell(15, 5, 'School Year: ', 0, 0);
    $pdf->Cell(127, 5, '', 0, 0);
    $pdf->Cell(10, 5, 'Date: ', 0, 0);
    $detailsPositions = $pdf->GetY();
    $pdf->Cell(0, 10, '', 0, 1);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetY($detailsPositions);
    $pdf->Cell(25, 5, '', 0, 0);
    $pdf->Cell(102, 5, $school_year, 0, 0);
    $pdf->Cell(27, 5, '', 0, 0);
    $pdf->Cell(36, 5, date("F j, Y"), 0, 0, 'R');
    $pdf->Cell(0, 10, '', 0, 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(95, 10, 'Department', 1, 0, "C");
    $pdf->Cell(95, 10, 'Sub Total', 1, 1, "C");

    $pdf->SetFont('Arial', '', 10);

    $sql_get_departments = "SELECT * FROM departments WHERE department_id != 0 AND department_id != 1 ORDER BY department_name";
    $result_get_departments = $conn->query($sql_get_departments);
    $grandtotal = 0;
    while ($row = $result_get_departments->fetch_assoc()) {
        $department_id = $row['department_id'];
        $department_name = $row['department_name'];

        $sql = "SELECT SUM(item_cost * requested_quantity_general) AS total FROM stock_monitoring
                WHERE charged_department_id = $department_id
                AND school_year = '$school_year'
                AND is_shown = 'yes'
                AND purpose != 'Returned'";
        $result = $conn->query($sql);
        $total = $result->fetch_assoc()['total'];
        $grandtotal += $total;
        $pdf->Cell(95, 7, $department_name, 1, 0, "");
        $pdf->Cell(95, 7, $total, 1, 1, "R");
    }
    $pdf->SetFont('Arial', 'B', 10);
    // Set background color to yellow (RGB: 255, 255, 0)
    $pdf->SetFillColor(255, 255, 0);
    $pdf->Cell(95, 7, 'Grand Total:', 1, 0, "", true);
    $pdf->Cell(95, 7, number_format($grandtotal, 2, '.', ','), 1, 1, "R", true);

    // Output the PDF
    $pdf->Output();
}
?>
