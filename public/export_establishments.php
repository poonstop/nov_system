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
    
    // Build the SQL query based on filters
    $sql = "SELECT e.establishment_id, e.name, e.owner_representative, e.nature, 
                   e.violations, e.notice_status, e.remarks, e.issued_datetime, 
                   e.expiry_date, a.street, a.barangay, a.municipality, a.province, 
                   ns.witnessed_by, nr.notice_type as action_type, nr.date_responded, 
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
    
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Establishment Management System')
        ->setLastModifiedBy('Establishment Management System')
        ->setTitle('Establishment Report')
        ->setSubject('Establishments Data Export')
        ->setDescription('Export of establishment data filtered by custom criteria');
    
    // Set up the header row
    $headerRow = [
        'ID', 'Establishment Name', 'Owner/Representative', 'Nature of Business',
        'Address', 'Violations', 'Status', 'Action Type', 'Date Responded', 
        'Expiry Date', 'Witnessed By', 'Remarks'
    ];
    
    // Style the header row
    $sheet->fromArray([$headerRow], NULL, 'A1');
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
    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
    
    // Auto-size columns
    foreach(range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Add data rows
    $row = 2;
    foreach ($establishments as $establishment) {
        // Combine address components
        $address = implode(', ', array_filter([
            $establishment['street'] ?? '',
            $establishment['barangay'] ?? '',
            $establishment['municipality'] ?? '',
            $establishment['province'] ?? ''
        ]));
        
        // Format dates
        $expiry_date = !empty($establishment['expiry_date']) ? 
            date('M d, Y', strtotime($establishment['expiry_date'])) : 'Not Set';
        $date_responded = !empty($establishment['date_responded']) ? 
            date('M d, Y', strtotime($establishment['date_responded'])) : 'Not Responded';
        
        $dataRow = [
            $establishment['establishment_id'],
            $establishment['name'],
            $establishment['owner_representative'] ?? 'N/A',
            $establishment['nature'] ?? 'N/A',
            $address,
            $establishment['violations'] ?? 'N/A',
            $establishment['notice_status'] ?? 'Pending',
            $establishment['action_type'] ?? 'Not Set',
            $date_responded,
            $expiry_date,
            $establishment['witnessed_by'] ?? 'N/A',
            $establishment['remarks'] ?? $establishment['action_remarks'] ?? 'N/A'
        ];
        
        $sheet->fromArray([$dataRow], NULL, 'A' . $row);
        
        // Style data row
        $rowStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($rowStyle);
        
        $row++;
    }
    
    // Wrap text in cells
    $sheet->getStyle('A1:L' . ($row - 1))->getAlignment()->setWrapText(true);
    
    // Create the Excel file
    $writer = new Xlsx($spreadsheet);
    
    // Set the filename based on filters
    $filename = 'establishments_report';
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
                <h2><i class="fas fa-file-export me-2"></i> Export Establishments Data</h2>
                <a href="manage_establishments.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Export Options</h5>
                </div>
                <div class="card-body">
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
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> The exported Excel file will include establishment details, violations, status, action type, and other relevant information based on your selected filters.
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" name="export" class="btn btn-primary">
                                <i class="fas fa-file-excel me-1"></i> Generate Excel Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Preview section (if needed) -->
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
                                <li><strong>By Month:</strong> Export establishments created in the specific month of selected year</li>
                                <li><strong>Custom Range:</strong> Export establishments created between two specific dates</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Additional Filters:</h6>
                            <ul>
                                <li><strong>Action Type:</strong> Filter by specific action taken (CFO, Formal Charge, etc.)</li>
                                <li><strong>Status:</strong> Filter by current status (Pending, Complied, etc.)</li>
                                <li><strong>Violation Search:</strong> Filter by specific keywords in violation descriptions</li>
                            </ul>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted">Note: The generated Excel file will be automatically downloaded to your device.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle visibility of date filter sections based on selection
    const filterRadios = document.querySelectorAll('input[name="filter_type"]');
    const yearSection = document.getElementById('yearSection');
    const monthSection = document.getElementById('monthSection');
    const customSection = document.getElementById('customSection');
    
    filterRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all sections first
            yearSection.style.display = 'none';
            monthSection.style.display = 'none';
            customSection.style.display = 'none';
            
            // Show the selected section
            if (this.value === 'year') {
                yearSection.style.display = 'flex';
            } else if (this.value === 'month') {
                monthSection.style.display = 'flex';
            } else if (this.value === 'custom') {
                customSection.style.display = 'flex';
            }
        });
    });
    
    // Form validation before submission
    document.getElementById('exportForm').addEventListener('submit', function(event) {
        const filterType = document.querySelector('input[name="filter_type"]:checked').value;
        
        if (filterType === 'custom') {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (!startDate || !endDate) {
                event.preventDefault();
                alert('Please select both start and end dates for custom date range');
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                event.preventDefault();
                alert('Start date must be before or equal to end date');
                return;
            }
        } else if (filterType === 'month') {
            const month = document.getElementById('month').value;
            if (!month) {
                event.preventDefault();
                alert('Please select a month');
                return;
            }
        }
    });
});
</script>

<?php include '../templates/footer.php'; ?>