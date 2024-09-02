<?php
require('../fpdf186/fpdf.php');
require '../php/connection.php';
require_once '../php/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it hasn't been started already
}

// Extend FPDF to add a header method
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

// Initialize variables to prevent undefined variable errors
$name = $request_date = $coordinator_signature = $finance_signature = $requestor_signature = '';
$coordinator_name = $finance_name = $requestor_name = $request_status = $comment = '';

if (isset($_GET['request_id'])) {

    $requestId = decryptData($_GET['request_id'], $key);
    $sql = "SELECT * FROM requests r 
            JOIN users u ON r.requestor_id = u.user_id
            JOIN departments d ON r.charged_department = d.department_id
            JOIN requested_items ri ON r.request_id = ri.request_id_fk
            WHERE request_id = '$requestId'";
    $result = mysqli_query($conn, $sql);
    $resultCheck = mysqli_num_rows($result);
    if ($resultCheck > 0) {
        $row = mysqli_fetch_assoc($result);

        $name = $row['first_name'] . ' ' . $row['last_name'];
        $timestamp = strtotime($row['requested_date']);
        $request_date = date('F j, Y', $timestamp);
        $requestor_signature = $row['requestor_approval'] != null ? $row['requestor_approval'] : null;
        $coordinator_signature = $row['coordinator_approval'] != null ? $row['coordinator_approval'] : null;
        $finance_signature = $row['finance_approval'] != null ? $row['finance_approval'] : null;
        $requestor_name = $name;
        $request_status = $row['request_status'];
        $comment = $row['finance_comment'];

        $get_coordinator_name = "SELECT * FROM users WHERE user_id = " . $row['coordinator_id'];
        $result_coordinator_name = mysqli_query($conn, $get_coordinator_name);
        $row_coordinator = mysqli_fetch_assoc($result_coordinator_name);
        $coordinator_name = $row_coordinator['first_name'] . ' ' . $row_coordinator['last_name'];

        $get_finance_name = "SELECT * FROM users WHERE user_id = " . $row['finance_id'];
        $result_finance_name = mysqli_query($conn, $get_finance_name);
        $row_finance = mysqli_fetch_assoc($result_finance_name);
        $finance_name = $row_finance['first_name'] . ' ' . $row_finance['last_name'];
    }
}else {
    echo "<script>alert('asdf');</script>";
}
ob_start();

$pdf = new PDF("P", "mm", "Letter");

$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(0, 5, 'ASSUMPTA TECHNICAL HIGH SCHOOL',0,1,"C");
$pdf->Cell(0, 5, 'Sta. Monica, San Simon, Pampanga',0,1,"C");

$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 5, '',0,1);
$pdf->Cell(0, 5, 'REQUISITION SLIP',0,1,"C");

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 5, 'MATERIALS',0,1,"C");

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 5, '',0,1);
$pdf->Cell(15, 5, 'NAME:',0,0);
$pdf->Cell(111, 5, '',0,0);
$pdf->Cell(35, 5, 'REQUEST DATE: ', 0, 0);
$pdf->Cell(35, 5, '',0,0);
$detailsPositions = $pdf->GetY();
$pdf->Cell(0,10,'',0,1);

$pdf->SetFont('Arial', '', 11);

$pdf->SetY($detailsPositions);
$pdf->Cell(15, 5, '',0,0);
$pdf->Cell(111, 5, $name,0,0);
$pdf->Cell(35, 5, '',0,0);
$pdf->Cell(35, 5, $request_date,0,0);
$pdf->Cell(0,10,'',0,1);

$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(50, 10, 'ITEM', 1, 0, "C");
$pdf->Cell(51, 10, 'DESCRIPTION', 1, 0, "C");
$pdf->Cell(20, 10, 'QUANTITY', 1, 0, "C");

// Save the current Y position
$yPosition1 = $pdf->GetY();
$xPosition1 = $pdf->GetX();
$pdf->MultiCell(30, 5, 'APPROXIMATE' . "\n" . 'PRICE', 1, "C");

// Set the Y position to the saved position
$pdf->SetY($yPosition1);
$pdf->SetX($xPosition1 + 30);

$pdf->MultiCell(20, 5, 'DATE' . "\n" . 'NEEDED', 1, "C");
$pdf->SetY($yPosition1);
$pdf->SetX($xPosition1 + 50);
$pdf->MultiCell(25, 5, 'CHARGE' . "\n" . 'ACCOUNT', 1, "C");

$pdf->SetFont('Arial', '', 10);

// If request_id is set, fetch and display requested items
if (true) {
    $total = 0;
    $sql = "SELECT * FROM stock_monitoring st
            JOIN departments d ON st.requesting_department_id = d.department_id
            JOIN items i ON st.item_id = i.item_id
            JOIN requests r ON st.request_id = r.request_id
            WHERE st.request_id = '$requestId'";
    $result = mysqli_query($conn, $sql);
    $resultCheck = mysqli_num_rows($result);

    if ($resultCheck > 0) {
        
        $i = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf->Cell(50, 5, $row['item_name'], 1, 0, "C");
            $pdf->Cell(51, 5, $row['item_description'], 1, 0, "C");
            $pdf->Cell(20, 5, $row['requested_quantity_general'] . ' pcs', 1, 0, "C");
            $pdf->Cell(30, 5, '' . number_format($row['item_cost'], 2), 1, 0, "C");
            $pdf->Cell(20, 5, $row['needed_date'], 1, 0, "C");
            $pdf->Cell(25, 5, $row['department_name'], 1, 1, "C");
            $total += $row['item_cost'];
            $i++;
        }

        // Fill remaining rows if there are less than 12 items
        if ($i < 12) {
            for ($j = $i; $j <= 11; $j++) {
                $pdf->Cell(50, 5, '', 1, 0, "C");
                $pdf->Cell(51, 5, '', 1, 0, "C");
                $pdf->Cell(20, 5, '', 1, 0, "C");
                $pdf->Cell(30, 5, '', 1, 0, "C");
                $pdf->Cell(20, 5, '', 1, 0, "C");
                $pdf->Cell(25, 5, '', 1, 1, "C");
            }
        }

        $pdf->Cell(25, 5, '', 0, 1, "C");

        $pdf->SetX(150);
        $pdf->Cell(31, 5, 'Grand Total', 1, 0, "C");
        $pdf->Cell(25, 5, '' . $total, 1, 1, "C");
    }
}

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 5, '', 0, 1);
$pdf->Cell(0, 5, 'REQUISITION PROCEDURE:', 0, 1);
$pdf->Cell(0, 5, '1. Course request through Coordinator', 0, 1);
$pdf->Cell(0, 5, '2. Coordinator refers it to Finance Officer for implementation', 0, 1);

// Requestor signature
$namePositions = $pdf->GetY();

$pdf->SetY($namePositions);
$pdf->Cell(133, 5, '', 0, 1);

$pdf->Cell(133, 8, 'Approved by:', 0, 0);
$pdf->Cell(50, 8, 'Received by:', 0, 1);
$pdf->SetFont('Arial', 'B', 10);
if ($coordinator_signature == 'APPROVED') {
    $pdf->Cell(57, 2, 'SIGNED', 0, 0, "C");
} else {
    $pdf->Cell(57, 2, '', 0, 0, "C");
}

if ($finance_signature == 'APPROVED') {
    $pdf->Cell(77, 2, 'SIGNED', 0, 0, "C");
} else {
    $pdf->Cell(77, 2, '', 0, 0, "C");
}

if ($requestor_signature == 'APPROVED') {  
    $pdf->Cell(55, 2, 'SIGNED', 0, 0, "C");
} else {
    $pdf->Cell(55, 2, '', 0, 0, "C");
}
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(57, 2, '', 0, 1, "C");
$namePositions = $pdf->GetY();

// Save current X and Y positions
$startX = $pdf->GetX();
$startY = $pdf->GetY();

$pdf->Cell(57, 5, '____________________________', 0, 0);
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(57, 5, '____________________________', 0, 0);
$pdf->Cell(9, 5, '', 0, 0);
$pdf->Cell(57, 5, '____________________________', 0, 1);

$pdf->SetY($namePositions);
$pdf->Cell(57, 5, $coordinator_name, 0, 0, "C");
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(57, 5, $finance_name, 0, 0, "C");
$pdf->Cell(9, 5, '', 0, 0);
$pdf->Cell(57, 5, $requestor_name, 0, 1, "C");

$pdf->SetY($namePositions);
$pdf->Cell(57, 5, '', 0, 0); // Empty cell to reserve space

// Set the Y position back to the original position
$pdf->SetXY($startX, $startY + 5);

$pdf->Cell(57, 5, 'Coordinator', 0, 0, "C");
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(57, 5, 'Finance Officer', 0, 1, "C");
ob_get_clean();

$pdf->Output();
$pdf_file = $pdf->Output("S");

?>
