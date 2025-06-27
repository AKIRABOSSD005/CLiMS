<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require '../conn/dbcon.php';

// Get selected values from POST request
$reportType = $_POST['report_type'] ?? '';
$selectedMonth = $_POST['selected_month'] ?? '';
$selectedQuarter = $_POST['selected_quarter'] ?? '';
$selectedYear = $_POST['selected_year'] ?? '';
$selectedQuarterYear = $_POST['selected_quarter_year'] ?? '';




$query = "SELECT m.member_id, m.fname, m.mname, m.lname, m.membership_fee, 
                 l.loan_id, l.updated_balance_history, l.share_capital, l.loanable_amount, l.updated_balance, 
                 r.annual_principal_amount, r.net_proceeds, r.annual_processing_fee, r.annual_loan_interest, r.report_history
          FROM members m
          LEFT JOIN loan l ON m.member_id = l.member_id
          LEFT JOIN loan_summary_reports r ON l.loan_id = r.loan_id
          WHERE 1=1";


// Add the role_id condition (this ensures you're excluding role_id = 1)
$query .= " AND m.role_id != 1";  // Exclude records with role_id = 1

// Add the loan_status condition (this ensures you're excluding loan_status = 0)
$query .= " AND l.loan_status != 2";  // Exclude records with loan_status = 0

// Add conditions based on report type
if ($reportType == 'monthly' && $selectedMonth && $selectedYear) {
    $query .= " AND MONTH(r.report_history) = $selectedMonth AND YEAR(r.report_history) = $selectedYear";
} elseif ($reportType == 'quarterly' && $selectedQuarter && $selectedQuarterYear) {
    // Determine the start and end dates based on the selected quarter
    if ($selectedQuarter == 'Q1') {
        $start_date = "$selectedQuarterYear-01-01";
        $end_date = "$selectedQuarterYear-03-31";
    } elseif ($selectedQuarter == 'Q2') {
        $start_date = "$selectedQuarterYear-04-01";
        $end_date = "$selectedQuarterYear-06-30";
    } elseif ($selectedQuarter == 'Q3') {
        $start_date = "$selectedQuarterYear-07-01";
        $end_date = "$selectedQuarterYear-09-30";
    } elseif ($selectedQuarter == 'Q4') {
        $start_date = "$selectedQuarterYear-10-01";
        $end_date = "$selectedQuarterYear-12-31";
    } else {
        die('Invalid quarter selected.');
    }

    // Add the start and end dates to the query
    $query .= " AND r.report_history BETWEEN '$start_date' AND '$end_date'";
} elseif ($reportType == 'annual' && $selectedYear) {
    $query .= " AND YEAR(r.report_history) = $selectedYear";
    
    // Filter by report history if provided
    if (!empty($reportHistory)) {
        $query .= " AND r.report_history = '$reportHistory'";
    }
}

// Add the ORDER BY clause after all conditions
$query .= " ORDER BY r.report_history ASC, l.updated_balance_history ASC";  // Adjust based on your sorting preferences

$result = $conn->query($query);




// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Start row numbering with the title row
$row = 1;

// Add the custom header with spacing using row incrementation
$sheet->setCellValue('A' . $row++, "Coop: Loan Management " . ucfirst($reportType) . " Report for BASCPCC");

// Add blank rows using row increment
$row += 2; // Skipping 2 rows

// Display the selected month or quarter/year with row incrementation
if ($reportType == 'monthly' && $selectedMonth && $selectedYear) {
    $monthName = DateTime::createFromFormat('!m', $selectedMonth)->format('F');
    $sheet->setCellValue('A' . $row++, "$monthName $selectedYear");
} elseif ($reportType == 'quarterly' && $selectedQuarter && $selectedQuarterYear) {
    $sheet->setCellValue('A' . $row++, "Q$selectedQuarter $selectedQuarterYear");
} elseif ($reportType == 'annual' && $selectedYear) {
    $sheet->setCellValue('A' . $row++, "$selectedYear");
}

// Add blank rows using row increment before headers
$row += 2; // Skipping 2 rows before the headers

// Set the headers at the current row, starting from column B (excluding Member ID and Loan ID)
$headers = [
    'First Name',
    'Middle Name',
    'Last Name',
    'Membership Fee',
    'Share Capital',
    'Loanable Amount',
    'Updated Balance',
    'Annual Principal Amount',
    'Net Proceeds',
    'Annual Processing Fee',
    'Annual Loan Interest',
    'Start Loan Date',
    'Updated History'
];
$sheet->fromArray($headers, NULL, 'B' . $row++);

// Initialize total variables
$totalMembershipFee = 0;
$totalShareCapital = 0;
$totalLoanableAmount = 0;
$totalUpdatedBalance = 0;
$totalAnnualPrincipalAmount = 0;
$totalNetProceeds = 0;
$totalAnnualProcessingFee = 0;
$totalAnnualLoanInterest = 0;

// Variables for monthly totals
$monthlyTotals = [
    'shareCapital' => 0,
    'loanableAmount' => 0,
    'updatedBalance' => 0,
    'annualPrincipalAmount' => 0,
    'netProceeds' => 0,
    'annualProcessingFee' => 0,
    'annualLoanInterest' => 0,
];  // Array to store monthly totals
$lastMonth = '';      // Track the last processed month

if ($result->num_rows > 0) {
    while ($rowData = $result->fetch_assoc()) {
        // Get the current month from updated_balance_history
        $currentMonth = '';
        if (!empty($rowData['report_history'])) {
            $currentMonth = DateTime::createFromFormat('Y-m-d', $rowData['report_history'])->format('F Y'); // e.g., "September 2024"
        }

        // Handle monthly totals and add spacing
        if ($currentMonth && $currentMonth !== $lastMonth) {
            // Add total for the previous month
// Add total for the last processed month
            if ($lastMonth !== '') {
                $row++; // Add a blank row for spacing
                $sheet->setCellValue('B' . $row, 'Total for ' . $lastMonth);
                $sheet->mergeCells('B' . $row . ':E' . $row); // Merge total label cells

                // Populate monthly total row values
                $sheet->setCellValue('F' . $row, $monthlyTotals['shareCapital']);
                $sheet->setCellValue('G' . $row, $monthlyTotals['loanableAmount']);
                $sheet->setCellValue('H' . $row, $monthlyTotals['updatedBalance']);
                $sheet->setCellValue('I' . $row, $monthlyTotals['annualPrincipalAmount']);
                $sheet->setCellValue('J' . $row, $monthlyTotals['netProceeds']);
                $sheet->setCellValue('K' . $row, $monthlyTotals['annualProcessingFee']);
                $sheet->setCellValue('L' . $row, $monthlyTotals['annualLoanInterest']);
                $sheet->setCellValue('M' . $row, '');   // Monthly Total Report History (can be left blank)
                $sheet->setCellValue('N' . $row, '');   // Updated Balance History remains blank

                // Add an additional blank row for spacing after the monthly total
                $row += 2; // Increment for two blank rows below the total
            }

            // Reset monthly totals for the new month
            $monthlyTotals = [
                'shareCapital' => 0,
                'loanableAmount' => 0,
                'updatedBalance' => 0,
                'annualPrincipalAmount' => 0,
                'netProceeds' => 0,
                'annualProcessingFee' => 0,
                'annualLoanInterest' => 0,
            ];

            $lastMonth = $currentMonth; // Update lastMonth to the current month
        }

        // Fill in the data
        $sheet->setCellValue('B' . $row, $rowData['fname']);
        $sheet->setCellValue('C' . $row, $rowData['mname']);
        $sheet->setCellValue('D' . $row, $rowData['lname']);
        $sheet->setCellValue('E' . $row, $rowData['membership_fee']);
        $sheet->setCellValue('F' . $row, $rowData['share_capital']);
        $sheet->setCellValue('G' . $row, $rowData['loanable_amount']);
        $sheet->setCellValue('H' . $row, $rowData['updated_balance']);
        $sheet->setCellValue('I' . $row, $rowData['annual_principal_amount']);
        $sheet->setCellValue('J' . $row, $rowData['net_proceeds']);
        $sheet->setCellValue('K' . $row, $rowData['annual_processing_fee']);
        $sheet->setCellValue('L' . $row, $rowData['annual_loan_interest']);
        $sheet->setCellValue('M' . $row, $rowData['report_history']);

        if (!empty($rowData['report_history'])) {
            $formattedReportHistory = DateTime::createFromFormat('Y-m-d', $rowData['report_history'])->format('F j, Y');
            $sheet->setCellValue('M' . $row, $formattedReportHistory);  // Set formatted report history
        } else {
            $sheet->setCellValue('M' . $row, '');  // Leave blank if there's no report history
        }


        // Format the updated_balance_history as "Month Day, Year"
        if (!empty($rowData['updated_balance_history'])) {
            $formattedDate = DateTime::createFromFormat('Y-m-d', $rowData['updated_balance_history'])->format('F j, Y');
            $sheet->setCellValue('N' . $row, $formattedDate);  // Set formatted date
        } else {
            $sheet->setCellValue('N' . $row, '');  // Leave blank if there's no date
        }

        // Update total variables
        $totalMembershipFee += $rowData['membership_fee'];
        $totalShareCapital += $rowData['share_capital'];
        $totalLoanableAmount += $rowData['loanable_amount'];
        $totalUpdatedBalance += $rowData['updated_balance'];
        $totalAnnualPrincipalAmount += $rowData['annual_principal_amount'];
        $totalNetProceeds += $rowData['net_proceeds'];
        $totalAnnualProcessingFee += $rowData['annual_processing_fee'];
        $totalAnnualLoanInterest += $rowData['annual_loan_interest'];

        // Update monthly totals
        $monthlyTotals['shareCapital'] += $rowData['share_capital'];
        $monthlyTotals['loanableAmount'] += $rowData['loanable_amount'];
        $monthlyTotals['updatedBalance'] += $rowData['updated_balance'];
        $monthlyTotals['annualPrincipalAmount'] += $rowData['annual_principal_amount'];
        $monthlyTotals['netProceeds'] += $rowData['net_proceeds'];
        $monthlyTotals['annualProcessingFee'] += $rowData['annual_processing_fee'];
        $monthlyTotals['annualLoanInterest'] += $rowData['annual_loan_interest'];

        $row++; // Increment row for the next entry
    }

    // Add total for the last processed month
    if ($lastMonth !== '') {
        $row++; // Add a blank row for spacing
        $sheet->setCellValue('B' . $row, 'Total for ' . $lastMonth);
        $sheet->mergeCells('B' . $row . ':E' . $row); // Merge total label cells

        // Populate monthly total row values
        $sheet->setCellValue('F' . $row, $monthlyTotals['shareCapital']);
        $sheet->setCellValue('G' . $row, $monthlyTotals['loanableAmount']);
        $sheet->setCellValue('H' . $row, $monthlyTotals['updatedBalance']);
        $sheet->setCellValue('I' . $row, $monthlyTotals['annualPrincipalAmount']);
        $sheet->setCellValue('J' . $row, $monthlyTotals['netProceeds']);
        $sheet->setCellValue('K' . $row, $monthlyTotals['annualProcessingFee']);
        $sheet->setCellValue('L' . $row, $monthlyTotals['annualLoanInterest']);
        $sheet->setCellValue('M' . $row, '');   // Monthly Total Report History (can be left blank)
        $sheet->setCellValue('N' . $row, '');   // Updated Balance History remains blank
    }
}



// Apply auto-sizing for columns A to K, but exclude the merged cells (A1 to K1)
foreach (range('B', 'N') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Add overall totals at the end
$row += 2; // Add some space
$sheet->setCellValue('B' . $row, 'Grand Total');
$sheet->mergeCells('B' . $row . ':E' . $row); // Merge total label cells

// Populate grand total row values
$sheet->setCellValue('F' . $row, $totalShareCapital);
$sheet->setCellValue('G' . $row, $totalLoanableAmount);
$sheet->setCellValue('H' . $row, $totalUpdatedBalance);
$sheet->setCellValue('I' . $row, $totalAnnualPrincipalAmount);
$sheet->setCellValue('J' . $row, $totalNetProceeds);
$sheet->setCellValue('K' . $row, $totalAnnualProcessingFee);
$sheet->setCellValue('L' . $row, $totalAnnualLoanInterest);
$sheet->setCellValue('M' . $row, ''); // Grand Total Report History (can be left blank)
$sheet->setCellValue('N' . $row, ''); // Overall Updated Balance History remains blank


// Prepare the file for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"" . ucfirst($reportType) . "_BASCPCC.xlsx\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit;

?>