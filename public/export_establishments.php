<?php
require_once '../connection.php';
// Include PhpSpreadsheet library - make sure it's installed via Composer
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// If form submitted, generate and download the report
if (isset($_POST['export'])) {
    // Get filter parameters
    $filter_type = $_POST['filter_type'];
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
    $month = isset($_POST['month']) ? $_POST['month'] : '';
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $action_type = isset($_POST['action_type']) ? $_POST['action_type'] : '';
    $violation_search = isset($_POST['violation_search']) ? $_POST['violation_search'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Establishment Management System')
        ->setLastModifiedBy('Establishment Management System')
        ->setTitle('Comprehensive Establishment Report')
        ->setSubject('Establishments Data Export')
        ->setDescription('Comprehensive export of establishment data from all tables');
    
    // Get the main establishments data
    // Build the SQL query based on filters
    $sql = "SELECT e.establishment_id, e.name, e.owner_representative, e.nature, 
                   e.products, e.violations, e.notice_status, e.remarks, e.issued_datetime, 
                   e.expiry_date, e.num_violations,
                   a.street, a.barangay, a.municipality, a.province, a.region,
                   ns.witnessed_by, ns.status as notice_current_status,
                   nr.notice_type as action_type, nr.date_responded, nr.status as record_status,
                   nr.remarks as action_remarks
            FROM establishments e
            LEFT JOIN addresses a ON e.establishment_id = a.establishment_id
            LEFT JOIN notice_status ns ON e.establishment_id = ns.establishment_id
            LEFT JOIN (
                SELECT * FROM notice_records 
                WHERE (establishment_id, created_at) IN (
                    SELECT establishment_id, MAX(created_at) 
                    FROM notice_records 
                    GROUP BY establishment_id
                )
            ) nr ON e.establishment_id = nr.establishment_id
            WHERE 1=1";
    
    $params = [];
    
    // Apply date filters
    if ($filter_type == 'year') {
        $sql .= " AND YEAR(e.date_created) = ?";
        $params[] = $year;
    } elseif ($filter_type == 'month' && !empty($month) && !empty($year)) {
        $sql .= " AND YEAR(e.date_created) = ? AND MONTH(e.date_created) = ?";
        $params[] = $year;
        $params[] = $month;
    } elseif ($filter_type == 'custom' && !empty($start_date) && !empty($end_date)) {
        $sql .= " AND e.date_created BETWEEN ? AND ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }
    
    // Apply action type filter
    if (!empty($action_type)) {
        $sql .= " AND nr.notice_type = ?";
        $params[] = $action_type;
    }
    
    // Apply violation search
    if (!empty($violation_search)) {
        $sql .= " AND e.violations LIKE ?";
        $params[] = "%$violation_search%";
    }
    
    // Apply status filter
    if (!empty($status)) {
        $sql .= " AND e.notice_status = ?";
        $params[] = $status;
    }
    
    // Order by created date
    $sql .= " ORDER BY e.date_created DESC";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    
    // Bind parameters if any
    for ($i = 0; $i < count($params); $i++) {
        $stmt->bindParam($i+1, $params[$i]);
    }
    
    $stmt->execute();
    $establishments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ----------------- SHEET 1: MAIN ESTABLISHMENTS DATA -----------------
    $mainSheet = $spreadsheet->getActiveSheet();
    $mainSheet->setTitle('Establishments');
    
    // Set up the header row
    $headerRow = [
        'Establishment Name', 'Owner/Representative', 'Nature of Business', 'Products',
        'Address', 'Violations', 'Num of Violations', 'Status', 'Action Type', 'Date Responded', 
        'Expiry Date', 'Witnessed By', 'Remarks', 'Record Status', 'Action Remarks'
    ];
    
    // Style the header row
    $mainSheet->fromArray([$headerRow], NULL, 'A1');
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '0066CC'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];
    $mainSheet->getStyle('A1:O1')->applyFromArray($headerStyle);
    
    // Auto-size columns
    foreach(range('A', 'O') as $col) {
        $mainSheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Add data rows
    $row = 2;
    $all_establishment_ids = [];
    foreach ($establishments as $establishment) {
        // Store establishment_id for later use
        $all_establishment_ids[] = $establishment['establishment_id'];
        
        // Combine address components
        $address = implode(', ', array_filter([
            $establishment['street'] ?? '',
            $establishment['barangay'] ?? '',
            $establishment['municipality'] ?? '',
            $establishment['province'] ?? '',
            $establishment['region'] ?? ''
        ]));
        
        // Format dates
        $expiry_date = !empty($establishment['expiry_date']) ? 
            date('M d, Y', strtotime($establishment['expiry_date'])) : 'Not Set';
        $date_responded = !empty($establishment['date_responded']) ? 
            date('M d, Y', strtotime($establishment['date_responded'])) : 'Not Responded';
        $issued_datetime = !empty($establishment['issued_datetime']) ? 
            date('M d, Y H:i', strtotime($establishment['issued_datetime'])) : 'Not Set';
        
        $dataRow = [
            $establishment['name'],
            $establishment['owner_representative'] ?? 'N/A',
            $establishment['nature'] ?? 'N/A',
            $establishment['products'] ?? 'N/A',
            $address,
            $establishment['violations'] ?? 'N/A',
            $establishment['num_violations'] ?? '0',
            $establishment['notice_status'] ?? 'Pending',
            $establishment['action_type'] ?? 'Not Set',
            $date_responded,
            $expiry_date,
            $establishment['witnessed_by'] ?? 'N/A',
            $establishment['remarks'] ?? 'N/A',
            $establishment['record_status'] ?? 'N/A',
            $establishment['action_remarks'] ?? 'N/A'
        ];
        
        $mainSheet->fromArray([$dataRow], NULL, 'A' . $row);
        
        // Style data row
        $rowStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $mainSheet->getStyle('A' . $row . ':O' . $row)->applyFromArray($rowStyle);
        
        $row++;
    }
    
    // Wrap text in cells
    $mainSheet->getStyle('A1:O' . ($row - 1))->getAlignment()->setWrapText(true);
    
    // ----------------- SHEET 2: INVENTORY DATA -----------------
    if (!empty($all_establishment_ids)) {
        // Create a new sheet for inventory
        $inventorySheet = $spreadsheet->createSheet();
        $inventorySheet->setTitle('Inventory');
        
        // Set headers
        $invHeaders = [
            'Establishment Name', 'Product Name', 'Description', 'Price', 'Pieces', 
            'Sealed', 'Withdrawn', 'DAO Violation', 'Other Violation', 'Product Remarks', 'Inventory Remarks'
        ];
        
        $inventorySheet->fromArray([$invHeaders], NULL, 'A1');
        $inventorySheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach(range('A', 'K') as $col) {
            $inventorySheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Prepare placeholders for the IN clause
        $placeholders = str_repeat('?,', count($all_establishment_ids) - 1) . '?';
        
        // Get inventory data
        $invSql = "SELECT i.*, e.name as establishment_name 
                  FROM inventory i
                  LEFT JOIN establishments e ON i.establishment_id = e.establishment_id
                  WHERE i.establishment_id IN ($placeholders)
                  ORDER BY i.establishment_id, i.date_created";
        
        $invStmt = $conn->prepare($invSql);
        for ($i = 0; $i < count($all_establishment_ids); $i++) {
            $invStmt->bindValue($i+1, $all_establishment_ids[$i]);
        }
        
        $invStmt->execute();
        $inventoryItems = $invStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add inventory data
        $invRow = 2;
        foreach ($inventoryItems as $item) {
            $inventorySheet->setCellValue('A' . $invRow, $item['establishment_name'] ?? 'Unknown');
            $inventorySheet->setCellValue('B' . $invRow, $item['product_name'] ?? 'N/A');
            $inventorySheet->setCellValue('C' . $invRow, $item['description'] ?? 'N/A');
            $inventorySheet->setCellValue('D' . $invRow, $item['price'] ?? 'N/A');
            $inventorySheet->setCellValue('E' . $invRow, $item['pieces'] ?? 'N/A');
            $inventorySheet->setCellValue('F' . $invRow, $item['sealed'] ? 'Yes' : 'No');
            $inventorySheet->setCellValue('G' . $invRow, $item['withdrawn'] ? 'Yes' : 'No');
            $inventorySheet->setCellValue('H' . $invRow, $item['dao_violation'] ? 'Yes' : 'No');
            $inventorySheet->setCellValue('I' . $invRow, $item['other_violation'] ? 'Yes' : 'No');
            $inventorySheet->setCellValue('J' . $invRow, $item['product_remarks'] ?? 'N/A');
            $inventorySheet->setCellValue('K' . $invRow, $item['inv_remarks'] ?? 'N/A');
            
            // Style row
            $inventorySheet->getStyle('A' . $invRow . ':K' . $invRow)->applyFromArray($rowStyle);
            $invRow++;
        }
        
        // Wrap text
        $inventorySheet->getStyle('A1:K' . ($invRow - 1))->getAlignment()->setWrapText(true);
        
        // ----------------- SHEET 3: PENALTIES DATA -----------------
        $penaltySheet = $spreadsheet->createSheet();
        $penaltySheet->setTitle('Penalties');
        
        // Set headers
        $penHeaders = [
            'Establishment Name', 'Amount', 'Description', 'Reference Number', 
            'Status', 'Issued By', 'Issued Date'
        ];
        
        $penaltySheet->fromArray([$penHeaders], NULL, 'A1');
        $penaltySheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach(range('A', 'G') as $col) {
            $penaltySheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Get penalties data
        $penSql = "SELECT p.*, e.name as establishment_name 
                  FROM penalties p
                  LEFT JOIN establishments e ON p.establishment_id = e.establishment_id
                  WHERE p.establishment_id IN ($placeholders)
                  ORDER BY p.establishment_id, p.issued_date";
        
        $penStmt = $conn->prepare($penSql);
        for ($i = 0; $i < count($all_establishment_ids); $i++) {
            $penStmt->bindValue($i+1, $all_establishment_ids[$i]);
        }
        
        $penStmt->execute();
        $penalties = $penStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add penalties data
        $penRow = 2;
        foreach ($penalties as $penalty) {
            $issued_date = !empty($penalty['issued_date']) ? 
                date('M d, Y', strtotime($penalty['issued_date'])) : 'N/A';
                
            $penaltySheet->setCellValue('A' . $penRow, $penalty['establishment_name'] ?? 'Unknown');
            $penaltySheet->setCellValue('B' . $penRow, $penalty['amount'] ?? 'N/A');
            $penaltySheet->setCellValue('C' . $penRow, $penalty['description'] ?? 'N/A');
            $penaltySheet->setCellValue('D' . $penRow, $penalty['reference_number'] ?? 'N/A');
            $penaltySheet->setCellValue('E' . $penRow, $penalty['status'] ?? 'N/A');
            $penaltySheet->setCellValue('F' . $penRow, $penalty['issued_by'] ?? 'N/A');
            $penaltySheet->setCellValue('G' . $penRow, $issued_date);
            
            // Style row
            $penaltySheet->getStyle('A' . $penRow . ':G' . $penRow)->applyFromArray($rowStyle);
            $penRow++;
        }
        
        // Wrap text
        $penaltySheet->getStyle('A1:G' . ($penRow - 1))->getAlignment()->setWrapText(true);
        
        // ----------------- SHEET 4: NOTICE RECORDS DATA -----------------
        $noticeSheet = $spreadsheet->createSheet();
        $noticeSheet->setTitle('Notice Records');
        
        // Set headers
        $noticeHeaders = [
            'Establishment Name', 'Notice Type', 'Date Responded', 
            'Status', 'Remarks', 'Images Count', 'Issuers'
        ];
        
        $noticeSheet->fromArray([$noticeHeaders], NULL, 'A1');
        $noticeSheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach(range('A', 'G') as $col) {
            $noticeSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Get notice records data with image count and issuers
        $noticeSql = "SELECT nr.*, e.name as establishment_name,
                      (SELECT COUNT(*) FROM notice_images ni WHERE ni.record_id = nr.record_id) as image_count,
                      (SELECT GROUP_CONCAT(CONCAT(issuer_name, ' (', issuer_position, ')') SEPARATOR '; ') 
                       FROM notice_issuers ni 
                       WHERE ni.establishment_id = nr.establishment_id) as issuers
                      FROM notice_records nr
                      LEFT JOIN establishments e ON nr.establishment_id = e.establishment_id
                      WHERE nr.establishment_id IN ($placeholders)
                      ORDER BY nr.establishment_id, nr.created_at DESC";
        
        $noticeStmt = $conn->prepare($noticeSql);
        for ($i = 0; $i < count($all_establishment_ids); $i++) {
            $noticeStmt->bindValue($i+1, $all_establishment_ids[$i]);
        }
        
        $noticeStmt->execute();
        $noticeRecords = $noticeStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add notice records data
        $noticeRow = 2;
        foreach ($noticeRecords as $record) {
            $date_responded = !empty($record['date_responded']) ? 
                date('M d, Y', strtotime($record['date_responded'])) : 'Not Responded';
                
            $noticeSheet->setCellValue('A' . $noticeRow, $record['establishment_name'] ?? 'Unknown');
            $noticeSheet->setCellValue('B' . $noticeRow, $record['notice_type'] ?? 'N/A');
            $noticeSheet->setCellValue('C' . $noticeRow, $date_responded);
            $noticeSheet->setCellValue('D' . $noticeRow, $record['status'] ?? 'N/A');
            $noticeSheet->setCellValue('E' . $noticeRow, $record['remarks'] ?? 'N/A');
            $noticeSheet->setCellValue('F' . $noticeRow, $record['image_count'] ?? '0');
            $noticeSheet->setCellValue('G' . $noticeRow, $record['issuers'] ?? 'None');
            
            // Style row
            $noticeSheet->getStyle('A' . $noticeRow . ':G' . $noticeRow)->applyFromArray($rowStyle);
            $noticeRow++;
        }
        
        // Wrap text
        $noticeSheet->getStyle('A1:G' . ($noticeRow - 1))->getAlignment()->setWrapText(true);
    }
    
    // Set the first sheet as active
    $spreadsheet->setActiveSheetIndex(0);
    
    // Create the Excel file
    $writer = new Xlsx($spreadsheet);
    
    // Set the filename based on filters
    $filename = 'comprehensive_report';
    if ($filter_type == 'year') {
        $filename .= "_$year";
    } elseif ($filter_type == 'month') {
        $filename .= "_$year-$month";
    } elseif ($filter_type == 'custom') {
        $filename .= "_" . str_replace('-', '', $start_date) . "_to_" . str_replace('-', '', $end_date);
    }
    
    if (!empty($action_type)) {
        $filename .= "_" . str_replace(' ', '_', strtolower($action_type));
    }
    
    if (!empty($status)) {
        $filename .= "_" . str_replace(' ', '_', strtolower($status));
    }
    
    if (!empty($violation_search)) {
        $filename .= "_violation_" . substr(preg_replace('/[^a-zA-Z0-9]/', '', $violation_search), 0, 10);
    }
    
    $filename .= '.xlsx';
    
    // Set headers to download the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}

// Get data for dropdown options
// Get unique action types
$action_query = $conn->query("SELECT DISTINCT notice_type FROM notice_records WHERE notice_type IS NOT NULL");
$action_types = [];
while ($action_row = $action_query->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($action_row['notice_type'])) {
        $action_types[] = $action_row['notice_type'];
    }
}

// Add all possible enum values if they don't exist in the results
$all_action_types = ['Certified First Offence', 'Formal Charge', 'Compliance', 'Other'];
foreach ($all_action_types as $type) {
    if (!in_array($type, $action_types)) {
        $action_types[] = $type;
    }
}

// Get unique status values
$status_query = $conn->query("SELECT DISTINCT notice_status FROM establishments WHERE notice_status IS NOT NULL");
$statuses = [];
while ($status_row = $status_query->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($status_row['notice_status'])) {
        $statuses[] = $status_row['notice_status'];
    }
}

// Get available years for reports
$year_query = $conn->query("SELECT DISTINCT YEAR(date_created) as year FROM establishments ORDER BY year DESC");
$years = [];
while ($year_row = $year_query->fetch(PDO::FETCH_ASSOC)) {
    $years[] = $year_row['year'];
}

// If no years found, add current year
if (empty($years)) {
    $years[] = date('Y');
}

include '../templates/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fas fa-file-export me-2"></i> Export Comprehensive Establishments Data</h2>
                <a href="nov_form.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Comprehensive Export Options</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> This export will create a multi-sheet Excel file containing establishment details, inventory items, penalties, notice records, and more.
                    </div>
                    <form method="POST" id="exportForm">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Date Filter Type</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="filter_type" id="filterYear" value="year" checked>
                                    <label class="btn btn-outline-primary" for="filterYear">By Year</label>
                                    
                                    <input type="radio" class="btn-check" name="filter_type" id="filterMonth" value="month">
                                    <label class="btn btn-outline-primary" for="filterMonth">By Month</label>
                                    
                                    <input type="radio" class="btn-check" name="filter_type" id="filterCustom" value="custom">
                                    <label class="btn btn-outline-primary" for="filterCustom">Custom Range</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Year selection (default visible) -->
                        <div class="row mb-3 filter-section" id="yearSection">
                            <div class="col-md-3">
                                <label for="year" class="form-label">Select Year</label>
                                <select class="form-select" id="year" name="year">
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Month selection (initially hidden) -->
                        <div class="row mb-3 filter-section" id="monthSection" style="display: none;">
                            <div class="col-md-3">
                                <label for="year_month" class="form-label">Select Year</label>
                                <select class="form-select" id="year_month" name="year">
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="month" class="form-label">Select Month</label>
                                <select class="form-select" id="month" name="month">
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Custom date range (initially hidden) -->
                        <div class="row mb-3 filter-section" id="customSection" style="display: none;">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="action_type" class="form-label">Action Type (Optional)</label>
                                <select class="form-select" id="action_type" name="action_type">
                                    <option value="">All Action Types</option>
                                    <?php foreach ($action_types as $action): ?>
                                        <option value="<?php echo htmlspecialchars($action); ?>">
                                            <?php echo htmlspecialchars($action); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status (Optional)</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo htmlspecialchars($status); ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="violation_search" class="form-label">Violation Search (Optional)</label>
                                <input type="text" class="form-control" id="violation_search" name="violation_search" 
                                       placeholder="Enter keyword to search in violations">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-table me-2"></i>Report Content</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="mb-0">
                                                    <li><strong>Sheet 1:</strong> Main Establishment Data</li>
                                                    <li><strong>Sheet 2:</strong> Inventory Items</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="mb-0">
                                                    <li><strong>Sheet 3:</strong> Penalties Information</li>
                                                    <li><strong>Sheet 4:</strong> Notice Records & Actions</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <button type="submit" name="export" class="btn btn-primary">
                                <i class="fas fa-file-excel me-1"></i> Generate Comprehensive Excel Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Instructions section -->
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Export Instructions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Date Filter Options:</h6>
                            <ul>
                                <li><strong>By Year:</strong> Export all establishments created in the selected year</li>
                                <li><strong>By Month:</strong> Export all establishments created in the selected month and year</li>
                                <li><strong>Custom Range:</strong> Export establishments created between two specific dates</li>
                            </ul>
                            
                            <h6 class="mt-3">Additional Filters:</h6>
                            <ul>
                                <li><strong>Action Type:</strong> Filter by specific notice types (e.g., Certified First Offence)</li>
                                <li><strong>Status:</strong> Filter by establishment status</li>
                                <li><strong>Violation Search:</strong> Search for specific violations in the records</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Generated Excel Report Contains:</h6>
                            <ul>
                                <li><strong>Sheet 1 (Establishments):</strong> Main establishment data including name, owner, nature, address, violations, status, etc.</li>
                                <li><strong>Sheet 2 (Inventory):</strong> All products/items associated with each establishment</li>
                                <li><strong>Sheet 3 (Penalties):</strong> All penalties and fines issued to establishments</li>
                                <li><strong>Sheet 4 (Notice Records):</strong> All notice records, issuers, and action details</li>
                            </ul>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i> For large datasets, the export may take several seconds to complete. Please wait for the download to start automatically.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle filter type switching
    const filterControls = document.querySelectorAll('input[name="filter_type"]');
    const filterSections = document.querySelectorAll('.filter-section');
    
    function updateFilterSections() {
        const selectedFilter = document.querySelector('input[name="filter_type"]:checked').value;
        
        document.getElementById('yearSection').style.display = 'none';
        document.getElementById('monthSection').style.display = 'none';
        document.getElementById('customSection').style.display = 'none';
        
        if (selectedFilter === 'year') {
            document.getElementById('yearSection').style.display = 'flex';
        } else if (selectedFilter === 'month') {
            document.getElementById('monthSection').style.display = 'flex';
        } else if (selectedFilter === 'custom') {
            document.getElementById('customSection').style.display = 'flex';
        }
    }
    
    filterControls.forEach(control => {
        control.addEventListener('change', updateFilterSections);
    });
    
    // Set default values for date inputs
    const today = new Date();
    const endDateInput = document.getElementById('end_date');
    endDateInput.valueAsDate = today;
    
    const startDate = new Date();
    startDate.setMonth(startDate.getMonth() - 1);
    const startDateInput = document.getElementById('start_date');
    startDateInput.valueAsDate = startDate;
    
    // Set current month in month selector
    const currentMonth = today.getMonth() + 1; // JavaScript months are 0-indexed
    document.getElementById('month').value = currentMonth;
    
    // Form validation
    document.getElementById('exportForm').addEventListener('submit', function(event) {
        const selectedFilter = document.querySelector('input[name="filter_type"]:checked').value;
        
        if (selectedFilter === 'custom') {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (!startDate || !endDate) {
                event.preventDefault();
                alert('Please select both start and end dates for custom range filtering.');
                return;
            }
            
            if (new Date(endDate) < new Date(startDate)) {
                event.preventDefault();
                alert('End date must be after start date.');
                return;
            }
        } else if (selectedFilter === 'month') {
            if (!document.getElementById('month').value) {
                event.preventDefault();
                alert('Please select a month.');
                return;
            }
        }
    });
    
    // Initialize the form
    updateFilterSections();
});
</script>

<?php include '../templates/footer.php'; ?>