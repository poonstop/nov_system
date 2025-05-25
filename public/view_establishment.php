
<?php
require_once '../connection.php';
include '../templates/header.php';

/**
 * Debug function to show image path resolution details
 * 
 * @param string $image_path The original image path
 * @return array Debugging information about path resolution attempts
 */
function debugImagePaths($image_path) {
    if (empty($image_path)) {
        return ['status' => 'error', 'message' => 'Empty image path provided'];
    }
    
    $filename = basename($image_path);
    $results = [
        'original_path' => $image_path,
        'filename' => $filename,
        'server_info' => [
            'document_root' => $_SERVER['DOCUMENT_ROOT'],
            'script_filename' => $_SERVER['SCRIPT_FILENAME'],
            'current_file' => __FILE__,
            'current_dir' => dirname(__FILE__),
            'parent_dir' => dirname(dirname(__FILE__))
        ],
        'path_tests' => []
    ];
    
    // Web paths to try
    $web_paths = [
        '/' . ltrim($image_path, '/'),
        '/uploads/notice_images/' . $filename,
        '/NOV_SYSTEM/uploads/notice_images/' . $filename,
        '/nov_system/uploads/notice_images/' . $filename,
        '/uploads/' . $filename
    ];
    
    foreach ($web_paths as $path) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $path;
        $results['path_tests'][] = [
            'type' => 'web_path',
            'test_path' => $path,
            'full_path' => $full_path,
            'exists' => file_exists($full_path),
            'is_readable' => is_readable($full_path),
            'file_size' => file_exists($full_path) ? filesize($full_path) : 'N/A'
        ];
    }
    
    // Server paths to try
    $server_paths = [
        $_SERVER['DOCUMENT_ROOT'] . '/uploads/notice_images/' . $filename,
        dirname(dirname(__FILE__)) . '/uploads/notice_images/' . $filename,
        'C:/xampp/htdocs/nov_system/uploads/notice_images/' . $filename,
        'C:/xampp/htdocs/uploads/notice_images/' . $filename
    ];
    
    foreach ($server_paths as $path) {
        $results['path_tests'][] = [
            'type' => 'server_path',
            'test_path' => $path,
            'exists' => file_exists($path),
            'is_readable' => is_readable($path),
            'file_size' => file_exists($path) ? filesize($path) : 'N/A'
        ];
    }
    
    // Check permissions on parent directories
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads';
    $notice_images_dir = $upload_dir . '/notice_images';
    
    $results['directory_info'] = [
        'uploads_dir' => [
            'path' => $upload_dir,
            'exists' => file_exists($upload_dir),
            'is_dir' => is_dir($upload_dir),
            'is_readable' => is_readable($upload_dir),
            'is_writable' => is_writable($upload_dir),
            'permissions' => file_exists($upload_dir) ? substr(sprintf('%o', fileperms($upload_dir)), -4) : 'N/A'
        ],
        'notice_images_dir' => [
            'path' => $notice_images_dir,
            'exists' => file_exists($notice_images_dir),
            'is_dir' => is_dir($notice_images_dir),
            'is_readable' => is_readable($notice_images_dir),
            'is_writable' => is_writable($notice_images_dir),
            'permissions' => file_exists($notice_images_dir) ? substr(sprintf('%o', fileperms($notice_images_dir)), -4) : 'N/A'
        ]
    ];
    
    // Add xampp specific paths
    $xampp_path = 'C:/xampp/htdocs/nov_system/uploads/notice_images';
    if (file_exists($xampp_path)) {
        $results['directory_info']['xampp_path'] = [
            'path' => $xampp_path,
            'exists' => true,
            'is_dir' => is_dir($xampp_path),
            'is_readable' => is_readable($xampp_path),
            'is_writable' => is_writable($xampp_path),
            'permissions' => substr(sprintf('%o', fileperms($xampp_path)), -4),
            'contents' => scandir($xampp_path)
        ];
    }
    
    return $results;
}

// Add this code to view_establishment.php to enable more comprehensive debugging

// Start of debug section - add this after the existing debug section
if (isset($_GET['advanced_debug']) && $_GET['advanced_debug'] == 1) {
    echo '<div class="container mt-3">';
    echo '<div class="alert alert-warning">';
    echo '<h4>Advanced Image Path Debugging</h4>';
    
    // Test image paths with visual results
    echo '<div class="mb-3">';
    echo '<h5>Visual Path Tests</h5>';
    
    if (!empty($images) && count($images) > 0) {
        $test_image = $images[0];
        echo '<p>Testing image: <strong>' . htmlspecialchars($test_image['image_name']) . '</strong></p>';
        echo '<p>Original path: <code>' . htmlspecialchars($test_image['image_path']) . '</code></p>';
        
        // Generate a list of potential paths to test
        $filename = basename($test_image['image_path']);
        $test_paths = [
            '/' . ltrim($test_image['image_path'], '/'),
            '/uploads/notice_images/' . $filename,
            '/NOV_SYSTEM/uploads/notice_images/' . $filename,
            '/nov_system/uploads/notice_images/' . $filename,
            '/uploads/' . $filename
        ];
        
        echo '<div class="row border p-2 mb-3">';
        foreach ($test_paths as $index => $path) {
            echo '<div class="col-md-4 mb-3">';
            echo '<div class="card h-100">';
            echo '<div class="card-header bg-light">Test Path ' . ($index + 1) . '</div>';
            echo '<div class="card-body">';
            echo '<p class="card-text small"><code>' . htmlspecialchars($path) . '</code></p>';
            echo '<img src="' . htmlspecialchars($path) . '" class="img-fluid border" style="max-height: 100px;" alt="Test image">';
            echo '<p class="mt-2 small ' . (file_exists($_SERVER['DOCUMENT_ROOT'] . $path) ? 'text-success' : 'text-danger') . '">';
            echo file_exists($_SERVER['DOCUMENT_ROOT'] . $path) ? 'File exists!' : 'File not found!';
            echo '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        
        // Show detailed debug information
        $debug_results = debugImagePaths($test_image['image_path']);
        
        // Display server information
        echo '<h5>Server Information</h5>';
        echo '<div class="table-responsive mb-3">';
        echo '<table class="table table-sm table-bordered">';
        foreach ($debug_results['server_info'] as $key => $value) {
            echo '<tr>';
            echo '<th>' . htmlspecialchars($key) . '</th>';
            echo '<td><code>' . htmlspecialchars($value) . '</code></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        
        // Display directory information
        echo '<h5>Directory Information</h5>';
        echo '<div class="table-responsive mb-3">';
        echo '<table class="table table-sm table-bordered">';
        echo '<thead><tr><th>Directory</th><th>Path</th><th>Exists</th><th>Is Dir</th><th>Readable</th><th>Writable</th><th>Permissions</th></tr></thead>';
        echo '<tbody>';
        foreach ($debug_results['directory_info'] as $dir_name => $dir_info) {
            echo '<tr>';
            echo '<th>' . htmlspecialchars($dir_name) . '</th>';
            echo '<td><code>' . htmlspecialchars($dir_info['path']) . '</code></td>';
            echo '<td>' . ($dir_info['exists'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>') . '</td>';
            echo '<td>' . ($dir_info['is_dir'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>') . '</td>';
            echo '<td>' . ($dir_info['is_readable'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>') . '</td>';
            echo '<td>' . ($dir_info['is_writable'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>') . '</td>';
            echo '<td><code>' . htmlspecialchars($dir_info['permissions']) . '</code></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        // Display directory contents if available
        if (isset($debug_results['directory_info']['xampp_path']['contents'])) {
            echo '<h5>Directory Contents</h5>';
            echo '<div class="mb-3 border p-2">';
            echo '<h6>Contents of: <code>' . htmlspecialchars($debug_results['directory_info']['xampp_path']['path']) . '</code></h6>';
            echo '<ul class="list-group">';
            foreach ($debug_results['directory_info']['xampp_path']['contents'] as $item) {
                if ($item != '.' && $item != '..') {
                    echo '<li class="list-group-item">' . htmlspecialchars($item) . '</li>';
                }
            }
            echo '</ul>';
            echo '</div>';
        }
        
        // Display all path tests
        echo '<h5>Path Resolution Tests</h5>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-sm table-striped table-bordered">';
        echo '<thead><tr><th>Type</th><th>Test Path</th><th>Exists</th><th>Readable</th><th>File Size</th></tr></thead>';
        echo '<tbody>';
        foreach ($debug_results['path_tests'] as $test) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($test['type']) . '</td>';
            echo '<td><code>' . htmlspecialchars($test['test_path']) . '</code></td>';
            echo '<td>' . ($test['exists'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>') . '</td>';
            echo '<td>' . ($test['is_readable'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>') . '</td>';
            echo '<td>' . ($test['file_size'] !== 'N/A' ? htmlspecialchars($test['file_size']) . ' bytes' : 'N/A') . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p>No images available to test.</p>';
    }
    
    echo '</div>';
    echo '</div>'; // End alert
    echo '</div>'; // End container
}
// End of advanced debug section
/**
 * Enhanced image path resolution function for NOV_SYSTEM
 * 
 * @param string $image_path The original image path from database
 * @return string The web-accessible path to the image or placeholder if not found
 */
function getCorrectFilePath($file_path, $file_type = 'image') {
    // Handle empty path
    if (empty($file_path)) {
        return $file_type === 'pdf' ? '/NOV_SYSTEM/assets/img/pdf-placeholder.jpg' : '/NOV_SYSTEM/assets/img/placeholder.jpg';
    }

    // If it's already a URL, return as is
    if (preg_match('/^https?:\/\//', $file_path)) {
        return $file_path;
    }

    // Extract just the filename from any path format
    $filename = basename($file_path);
    
    // Clean the filename to prevent path traversal
    $filename = str_replace(['../', '..\\', '/', '\\'], '', $filename);
    
    // If filename is empty after cleaning, return placeholder
    if (empty($filename)) {
        return $file_type === 'pdf' ? '/NOV_SYSTEM/assets/img/pdf-placeholder.jpg' : '/NOV_SYSTEM/assets/img/placeholder.jpg';
    }
    
    // Define web paths to try in priority order
    $web_paths = [
        '/NOV_SYSTEM/uploads/notice_images/' . $filename,     // Current structure
        '/NOV_SYSTEM/uploads/notice_files/' . $filename,     // If you separate files
        '/NOV_SYSTEM/public/uploads/notice_images/' . $filename,
        '/nov_system/uploads/notice_images/' . $filename,
        '/uploads/notice_images/' . $filename,
    ];

    // Check if any web path exists on the server
    foreach ($web_paths as $path) {
        $full_server_path = $_SERVER['DOCUMENT_ROOT'] . $path;
        
        if (file_exists($full_server_path) && is_readable($full_server_path) && filesize($full_server_path) > 0) {
            return $path;
        }
    }
    
    // Log the issue for debugging
    error_log("File not found: " . $file_path . " (Filename: " . $filename . ", Type: " . $file_type . ")");
    
    // Return appropriate placeholder
    return $file_type === 'pdf' ? '/NOV_SYSTEM/assets/img/pdf-placeholder.jpg' : '/NOV_SYSTEM/assets/img/placeholder.jpg';
}

/**
 * Check if file is a PDF based on extension or mime type
 */
function isPdfFile($filename, $mime_type = '') {
    $pdf_extensions = ['pdf'];
    $pdf_mimes = ['application/pdf', 'application/x-pdf'];
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    return in_array($extension, $pdf_extensions) || in_array($mime_type, $pdf_mimes);
}

/**
 * Get file icon class based on file type
 */
function getFileIcon($filename, $mime_type = '') {
    if (isPdfFile($filename, $mime_type)) {
        return 'fas fa-file-pdf text-danger';
    }
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    
    if (in_array($extension, $image_extensions)) {
        return 'fas fa-image text-primary';
    }
    
    return 'fas fa-file text-secondary';
}


function debugImageAccess($image_path, $record_id = null) {
    $filename = basename($image_path);
    $debug_info = [
        'original_path' => $image_path,
        'filename' => $filename,
        'record_id' => $record_id,
        'tests' => []
    ];
    
    // Test various paths
    $test_paths = [
        '/NOV_SYSTEM/uploads/notice_images/' . $filename,
        '/nov_system/uploads/notice_images/' . $filename,
        '/uploads/notice_images/' . $filename,
        'C:/xampp/htdocs/NOV_SYSTEM/uploads/notice_images/' . $filename,
        'C:/xampp/htdocs/nov_system/uploads/notice_images/' . $filename
    ];
    
    foreach ($test_paths as $path) {
        $is_web_path = !preg_match('/^[A-Z]:/', $path);
        $full_path = $is_web_path ? $_SERVER['DOCUMENT_ROOT'] . $path : $path;
        
        $debug_info['tests'][] = [
            'path' => $path,
            'type' => $is_web_path ? 'web' : 'absolute',
            'full_path' => $full_path,
            'exists' => file_exists($full_path),
            'readable' => is_readable($full_path),
            'size' => file_exists($full_path) ? filesize($full_path) : 0
        ];
    }
    
    return $debug_info;
}

 
function createImageTestUrl($image_path, $image_name) {
    $corrected_path = getCorrectImagePath($image_path);
    
    // Add timestamp to prevent caching issues only if not placeholder
    if ($corrected_path !== '/assets/img/placeholder.jpg') {
        $timestamp = time();
        $test_url = $corrected_path . '?t=' . $timestamp;
    } else {
        $test_url = $corrected_path;
    }
    
    return [
        'url' => $test_url,
        'original' => $image_path,
        'corrected' => $corrected_path,
        'name' => $image_name
    ];
}

// Check if ID is provided in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to the main page if no ID is provided
    header("Location: nov_form.php");
    exit();
}

// Get establishment ID
$establishment_id = $_GET['id'];

// Fetch establishment details
$estab_query = $conn->prepare("
    SELECT e.*, a.street, a.barangay, a.municipality, a.province, a.region 
    FROM establishments e
    LEFT JOIN addresses a ON e.establishment_id = a.establishment_id
    WHERE e.establishment_id = ?
");
$estab_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$estab_query->execute();

if ($estab_query->rowCount() == 0) {
    // Establishment not found, redirect back
    header("Location: nov_form.php?error=not_found");
    exit();
}

// Get establishment data
$establishment = $estab_query->fetch(PDO::FETCH_ASSOC);

// Fetch inventory items
$inventory_query = $conn->prepare("
    SELECT * FROM inventory 
    WHERE establishment_id = ?
    ORDER BY date_created DESC
");
$inventory_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$inventory_query->execute();
$inventory_items = $inventory_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch notice status
$status_query = $conn->prepare("
    SELECT ns.*, ni.issuer_name, ni.issuer_position 
    FROM notice_status ns
    LEFT JOIN notice_issuers ni ON ns.establishment_id = ni.establishment_id
    WHERE ns.establishment_id = ?
    ORDER BY ni.created_at DESC
");
$status_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$status_query->execute();
$status_records = $status_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch notice records (action types)
$records_query = $conn->prepare("
    SELECT * FROM notice_records
    WHERE establishment_id = ?
    ORDER BY created_at DESC
");
$records_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$records_query->execute();
$notice_records = $records_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch images related to this establishment
$images_query = $conn->prepare("
    SELECT ni.* 
    FROM notice_images ni
    JOIN notice_records nr ON ni.record_id = nr.record_id
    WHERE nr.establishment_id = ?
    ORDER BY ni.upload_date DESC
");
$images_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$images_query->execute();
$images = $images_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch penalties
$penalties_query = $conn->prepare("
    SELECT * FROM penalties
    WHERE establishment_id = ?
    ORDER BY created_at DESC
");
$penalties_query->bindParam(1, $establishment_id, PDO::PARAM_INT);
$penalties_query->execute();
$penalties = $penalties_query->fetchAll(PDO::FETCH_ASSOC);

// Helper function to get the appropriate Bootstrap badge class based on status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Complied':
            return 'bg-success';
        case 'Non-Compliant':
            return 'bg-danger';
        case 'In Progress':
            return 'bg-warning';
        case 'Closed':
            return 'bg-secondary';
        case 'Pending':
        default:
            return 'bg-info';
    }
}

// Helper function to get the appropriate Bootstrap badge class based on action type
function getActionTypeBadgeClass($action_type) {
    switch ($action_type) {
        case 'CFO':
            return 'bg-primary';
        case 'FC':
            return 'bg-info';
        case 'Compliance':
            return 'bg-success';
        case 'Other':
        default:
            return 'bg-secondary';
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="nov_form.php">Establishments</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($establishment['name']); ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2>Establishment Details</h2>
                <div>
                    <a href="edit_establishment.php?id=<?php echo $establishment_id; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="nov_form.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Establishment information updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Establishment Information Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-building me-2"></i>Establishment Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Establishment Name:</th>
                            <td><strong><?php echo htmlspecialchars($establishment['name']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Owner/Representative:</th>
                            <td><?php echo htmlspecialchars($establishment['owner_representative'] ?? 'Not specified'); ?></td>
                        </tr>
                        <tr>
                            <th>Nature of Business:</th>
                            <td><?php echo htmlspecialchars($establishment['nature'] ?? 'Not specified'); ?></td>
                        </tr>
                        <tr>
                            <th>Products:</th>
                            <td><?php echo nl2br(htmlspecialchars($establishment['products'] ?? 'Not specified')); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Address:</th>
                            <td>
                                <?php
                                $address_parts = [];
                                if (!empty($establishment['street'])) $address_parts[] = $establishment['street'];
                                if (!empty($establishment['barangay'])) $address_parts[] = 'Brgy. ' . $establishment['barangay'];
                                if (!empty($establishment['municipality'])) $address_parts[] = $establishment['municipality'];
                                if (!empty($establishment['province'])) $address_parts[] = $establishment['province'];
                                if (!empty($establishment['region'])) $address_parts[] = $establishment['region'];
                                
                                echo !empty($address_parts) ? htmlspecialchars(implode(', ', $address_parts)) : 'No address on record';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Notice Status:</th>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($establishment['notice_status']); ?> fs-6">
                                    <?php echo htmlspecialchars($establishment['notice_status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?php echo date('F d, Y h:i A', strtotime($establishment['date_created'])); ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td><?php echo date('F d, Y h:i A', strtotime($establishment['date_updated'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Violations and Inventory Section -->
    <div class="row">
        <!-- Violations Section -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Violations</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($establishment['violations'])): ?>
                        <div class="p-3 border rounded">
                            <?php echo nl2br(htmlspecialchars($establishment['violations'])); ?>
                        </div>
                        
                        <?php if (!empty($establishment['remarks'])): ?>
                            <div class="mt-3">
                                <h5>Additional Remarks:</h5>
                                <div class="p-3 border rounded">
                                    <?php echo nl2br(htmlspecialchars($establishment['remarks'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($establishment['issued_datetime'])): ?>
                            <div class="mt-3">
                                <p><strong>Issued Date:</strong> <?php echo date('F d, Y h:i A', strtotime($establishment['issued_datetime'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($establishment['expiry_date'])): ?>
                            <div class="mt-1">
                                <p>
                                    <strong>Expiry Date:</strong> 
                                    <?php echo date('F d, Y', strtotime($establishment['expiry_date'])); ?>
                                    <?php if (strtotime($establishment['expiry_date']) < time()): ?>
                                        <span class="badge bg-danger">Expired</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">No violations recorded for this establishment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

       <!-- Inventory Items Section -->
<div class="col-md-6">
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Inventory Items</h4>
        </div>
        <div class="card-body">
            <?php if (count($inventory_items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['pieces'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($item['price']): ?>
                                            ₱<?php echo number_format($item['price'], 2); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['sealed']): ?>
                                            <span class="badge bg-warning">Sealed</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['withdrawn']): ?>
                                            <span class="badge bg-danger">Withdrawn</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!$item['sealed'] && !$item['withdrawn']): ?>
                                            <span class="badge bg-light text-dark">Normal</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['dao_violation']): ?>
                                            <span class="badge bg-info">DAO Violation</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['other_violation']): ?>
                                            <span class="badge bg-secondary">Other Violation</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Display product remarks if available
                                        if (!empty($item['product_remarks'])) {
                                            echo '<div class="mb-1"><strong>Product:</strong> ' . htmlspecialchars($item['product_remarks']) . '</div>';
                                        }
                                        
                                        // Display inventory remarks if available
                                        if (!empty($item['inv_remarks'])) {
                                            echo '<div' . (empty($item['product_remarks']) ? '' : ' class="mt-1 pt-1 border-top"') . '>';
                                            echo '<strong>Inventory:</strong> ' . htmlspecialchars($item['inv_remarks']);
                                            echo '</div>';
                                        }
                                        
                                        // If neither is available
                                        if (empty($item['product_remarks']) && empty($item['inv_remarks'])) {
                                            echo 'No remarks';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No inventory items recorded for this establishment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

    <!-- Notice Status and Issuers Section -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Status History and Issuers</h4>
        </div>
        <div class="card-body">
            <?php if (count($status_records) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Updated On</th>
                                <th>Issued By</th>
                                <th>Position</th>
                                <th>Witnessed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status_records as $status): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($status['status']); ?>">
                                            <?php echo htmlspecialchars($status['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('F d, Y h:i A', strtotime($status['updated_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($status['issuer_name'] ?? 'Not specified'); ?></td>
                                    <td><?php echo htmlspecialchars($status['issuer_position'] ?? 'Not specified'); ?></td>
                                    <td><?php echo htmlspecialchars($status['witnessed_by'] ?? 'None'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No status history or issuers recorded for this establishment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions Taken Section -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-tasks me-2"></i>Actions Taken</h4>
        </div>
        <div class="card-body">
            <?php if (count($notice_records) > 0): ?>
                <div class="accordion" id="actionsAccordion">
                    <?php foreach ($notice_records as $index => $record): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button <?php echo ($index > 0) ? 'collapsed' : ''; ?>" type="button" 
                                       data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" 
                                       aria-expanded="<?php echo ($index === 0) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <span class="badge <?php echo getActionTypeBadgeClass($record['notice_type']); ?> me-2">
                                                <?php echo htmlspecialchars($record['notice_type']); ?>
                                            </span>
                                            <span class="badge <?php echo getStatusBadgeClass($record['status']); ?>">
                                                <?php echo htmlspecialchars($record['status']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted me-3">
                                            <?php echo date('F d, Y', strtotime($record['created_at'])); ?>
                                        </small>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo ($index === 0) ? 'show' : ''; ?>" 
                                 aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#actionsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Record Details</h5>
                                            <table class="table table-bordered table-striped">
                                                <tr>
                                                    <th width="40%">Action Type:</th>
                                                    <td><?php echo htmlspecialchars($record['notice_type']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Date Created:</th>
                                                    <td><?php echo date('F d, Y h:i A', strtotime($record['created_at'])); ?></td>
                                                </tr>
                                                <?php if ($record['date_responded']): ?>
                                                <tr>
                                                    <th>Date Responded:</th>
                                                    <td><?php echo date('F d, Y h:i A', strtotime($record['date_responded'])); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <th>Status:</th>
                                                    <td><?php echo htmlspecialchars($record['status']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Remarks:</th>
                                                    <td><?php echo nl2br(htmlspecialchars($record['remarks'] ?? 'No remarks')); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Last Updated:</th>
                                                    <td><?php echo date('F d, Y h:i A', strtotime($record['updated_at'])); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <?php
                                            // Filter images belonging to this record
                                            $record_images = array_filter($images, function($img) use ($record) {
                                                return $img['record_id'] == $record['record_id'];
                                            });
                                            
                                            // Filter penalties belonging to around this record's creation time
                                            // (Since there's no direct foreign key relationship)
                                            $record_penalties = array_filter($penalties, function($penalty) use ($record) {
                                                $penalty_time = strtotime($penalty['created_at']);
                                                $record_time = strtotime($record['created_at']);
                                                // Check if penalty was created within 24 hours of the record
                                                return abs($penalty_time - $record_time) < 86400; // 24 hours in seconds
                                            });
                                            ?>
                                            
                                            <!-- Related Images Section with Debug Option -->
                                            <?php if (!empty($record_images)): ?>
    <h5>Related Files 
        <?php if (isset($_GET['debug_images'])): ?>
            <small class="text-muted">(Debug Mode)</small>
        <?php endif; ?>
    </h5>
    
    <div class="row">
        <?php foreach ($record_images as $file): ?>
            <?php
                // Validate file data before processing
                if (empty($file['image_name']) || empty($file['image_path'])) {
                    continue; // Skip invalid file records
                }
                
                // Determine if this is a PDF or image
                $is_pdf = isPdfFile($file['image_name'], $file['image_type']);
                $file_type = $is_pdf ? 'pdf' : 'image';
                
                $file_path = getCorrectFilePath($file['image_path'], $file_type);
                $file_icon = getFileIcon($file['image_name'], $file['image_type']);
                
                // Create test data for modal
                $file_test = [
                    'url' => $file_path,
                    'original' => $file['image_path'],
                    'corrected' => $file_path,
                    'name' => $file['image_name'],
                    'type' => $file_type,
                    'is_pdf' => $is_pdf
                ];
                
                // Debug information if requested
                if (isset($_GET['debug_images']) && $_GET['debug_images'] == 1) {
                    $debug_info = debugImageAccess($file['image_path'], $file['record_id']);
                }
            ?>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <?php if (isset($_GET['debug_images'])): ?>
                        <!-- Debug Information Panel -->
                        <div class="card-header bg-info text-white">
                            <small>
                                <strong>Debug Info:</strong><br>
                                Original: <?php echo htmlspecialchars($file_test['original']); ?><br>
                                Corrected: <?php echo htmlspecialchars($file_test['corrected']); ?><br>
                                Type: <?php echo htmlspecialchars($file_type); ?><br>
                                Record ID: <?php echo $file['record_id']; ?>
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <!-- File preview with different handling for PDFs vs Images -->
                    <div class="position-relative">
                        <?php if ($is_pdf): ?>
                            <!-- PDF Preview -->
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" 
                                 style="height: 150px; cursor: pointer;"
                                 onclick="openFileModal(<?php echo htmlspecialchars(json_encode($file_test), ENT_QUOTES, 'UTF-8'); ?>)">
                                <div class="text-center">
                                    <i class="<?php echo $file_icon; ?>" style="font-size: 3rem;"></i>
                                    <div class="mt-2 small text-muted">PDF Document</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Image Preview -->
                            <img id="img-<?php echo $file['image_id']; ?>" 
                                 src="<?php echo htmlspecialchars($file_path); ?>" 
                                 class="card-img-top img-thumbnail" 
                                 alt="<?php echo htmlspecialchars($file['image_name']); ?>"
                                 style="height: 150px; object-fit: cover; cursor: pointer;"
                                 onclick="openFileModal(<?php echo htmlspecialchars(json_encode($file_test), ENT_QUOTES, 'UTF-8'); ?>)"
                                 onerror="handleImageError(this, '<?php echo htmlspecialchars($file['image_name'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo $file['image_id']; ?>')">
                        <?php endif; ?>
                        
                        <!-- Loading indicator -->
                        <div id="loading-<?php echo $file['image_id']; ?>" 
                             class="position-absolute top-50 start-50 translate-middle d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        
                        <!-- Error indicator -->
                        <div id="error-<?php echo $file['image_id']; ?>" 
                             class="position-absolute top-0 end-0 d-none">
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body py-2">
                        <p class="card-text small mb-1">
                            <i class="<?php echo $file_icon; ?> me-1"></i>
                            <strong><?php echo htmlspecialchars($file['image_name']); ?></strong>
                        </p>
                        <p class="card-text small text-muted mb-1">
                            Type: <?php echo htmlspecialchars($file['image_type'] ?? ($is_pdf ? 'PDF Document' : 'Image')); ?>
                        </p>
                        <p class="card-text small text-muted">
                            Uploaded: <?php echo date('M d, Y H:i', strtotime($file['upload_date'] ?? 'now')); ?>
                        </p>
                        
                        <!-- Action buttons -->
                        <div class="btn-group btn-group-sm w-100" role="group">
                            
                            <button type="button" class="btn btn-outline-success btn-sm" 
                                    onclick="downloadFile('<?php echo htmlspecialchars($file_path); ?>', '<?php echo htmlspecialchars($file['image_name']); ?>')">
                                <i class="fas fa-download"></i> Download
                            </button>
                            <?php if (isset($_GET['debug_images'])): ?>
                                <button type="button" class="btn btn-outline-info btn-sm" 
                                        onclick="showDebugInfo(<?php echo htmlspecialchars(json_encode($debug_info ?? []), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="fas fa-bug"></i> Debug
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add debug toggle link -->
<div class="mt-2">
        <?php if (!isset($_GET['debug_images'])): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['debug_images' => '1'])); ?>" 
               class="btn btn-sm btn-outline-info">
                <i class="fas fa-bug"></i> Enable File Debug Mode
            </a>
        <?php else: ?>
            <a href="?<?php echo http_build_query(array_filter($_GET, function($key) { return $key !== 'debug_images'; }, ARRAY_FILTER_USE_KEY)); ?>" 
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i> Disable Debug Mode
            </a>
        <?php endif; ?>
    </div>
    
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No files found for this record.
    </div>
<?php endif; ?>
    
                                            
                                            <!-- Related Penalties Section -->
                                            <?php if (!empty($record_penalties)): ?>
                                                <h5 class="mt-3">Related Penalties</h5>
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Reference #</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($record_penalties as $penalty): ?>
                                                            <tr>
                                                                <td>₱<?php echo number_format($penalty['amount'], 2); ?></td>
                                                                <td>
                                                                    <span class="badge <?php echo ($penalty['status'] == 'Paid') ? 'bg-success' : 'bg-warning'; ?>">
                                                                        <?php echo htmlspecialchars($penalty['status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($penalty['reference_number'] ?? 'N/A'); ?></td>
                                                                <td><?php echo date('M d, Y', strtotime($penalty['issued_date'])); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <div class="mt-2">
                                                    <strong>Issued By:</strong> <?php echo htmlspecialchars($record_penalties[0]['issued_by'] ?? 'Not specified'); ?>
                                                </div>
                                                <?php if (!empty($record_penalties[0]['description'])): ?>
                                                    <div class="mt-2">
                                                        <strong>Description:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($record_penalties[0]['description'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if (empty($record_images) && empty($record_penalties)): ?>
                                                <div class="alert alert-secondary">
                                                    <p class="mb-0">No images or penalties associated with this record.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">No action records found for this establishment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Enhanced Image Modal -->
<div class="modal fade" id="enhancedImageModal" tabindex="-1" aria-labelledby="enhancedImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enhancedImageModalLabel">File Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div id="modalFileContainer">
                        <!-- Image container -->
                        <img id="modalImage" src="" class="img-fluid d-none" alt="Full size image" style="max-height: 80vh;">
                        
                        <!-- PDF container -->
                        <div id="modalPdfContainer" class="d-none">
                            <div class="mb-3">
                                <i class="fas fa-file-pdf text-danger" style="font-size: 4rem;"></i>
                                <h4 class="mt-2" id="pdfFileName">PDF Document</h4>
                            </div>
                            
                            <!-- PDF embed (if browser supports) -->
                            <div id="pdfEmbedContainer" class="mb-3" style="height: 70vh;">
                                <embed id="pdfEmbed" src="" type="application/pdf" width="100%" height="100%" />
                            </div>
                            
                            <!-- Fallback for browsers that don't support PDF embed -->
                            <div id="pdfFallback" class="d-none">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> PDF Preview Not Available</h5>
                                    <p>Your browser doesn't support PDF preview. Please download the file to view it.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="modalFileStatus" class="mt-3"></div>
                </div>
                
                <!-- File Information -->
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>File Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td id="modalFileName"></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td id="modalFileType"></td>
                                </tr>
                                <tr>
                                    <th>Original Path:</th>
                                    <td><code id="modalOriginalPath"></code></td>
                                </tr>
                                <tr>
                                    <th>Resolved Path:</th>
                                    <td><code id="modalResolvedPath"></code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Alternative Paths Test</h6>
                            <div id="pathTestResults"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="downloadFileBtn">
                    <i class="fas fa-download"></i> Download
                </button>
                <button type="button" class="btn btn-primary" id="openInNewTabBtn">
                    <i class="fas fa-external-link-alt"></i> Open in New Tab
                </button>
                <button type="button" class="btn btn-info" id="refreshFileBtn">
                    <i class="fas fa-refresh"></i> Refresh
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
    // Enhanced JavaScript for handling both images and PDFs

let currentFileData = null;

// Replace the existing openImageModal function with this enhanced version
function openFileModal(fileData) {
    console.log('Opening modal for file:', fileData);
    currentFileData = fileData;
    
    const modal = new bootstrap.Modal(document.getElementById('enhancedImageModal'));
    const modalImage = document.getElementById('modalImage');
    const modalPdfContainer = document.getElementById('modalPdfContainer');
    const modalStatus = document.getElementById('modalFileStatus');
    const modalTitle = document.getElementById('enhancedImageModalLabel');
    
    // Reset modal
    modalImage.classList.add('d-none');
    modalPdfContainer.classList.add('d-none');
    modalStatus.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    
    // Set file information
    document.getElementById('modalFileName').textContent = fileData.name;
    document.getElementById('modalFileType').textContent = fileData.is_pdf ? 'PDF Document' : 'Image';
    document.getElementById('modalOriginalPath').textContent = fileData.original;
    document.getElementById('modalResolvedPath').textContent = fileData.corrected;
    
    if (fileData.is_pdf) {
        // Handle PDF
        modalTitle.textContent = 'PDF Preview: ' + fileData.name;
        document.getElementById('pdfFileName').textContent = fileData.name;
        
        const pdfEmbed = document.getElementById('pdfEmbed');
        const pdfFallback = document.getElementById('pdfFallback');
        
        // Try to embed PDF
        pdfEmbed.src = fileData.url;
        modalPdfContainer.classList.remove('d-none');
        
        // Check if PDF loaded successfully
        pdfEmbed.onload = function() {
            modalStatus.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> PDF loaded successfully!</div>';
        };
        
        pdfEmbed.onerror = function() {
            pdfFallback.classList.remove('d-none');
            document.getElementById('pdfEmbedContainer').classList.add('d-none');
            modalStatus.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> PDF preview not available in this browser</div>';
        };
        
    } else {
        // Handle Image
        modalTitle.textContent = 'Image Preview: ' + fileData.name;
        
        const testImg = new Image();
        testImg.onload = function() {
            modalImage.src = fileData.url;
            modalImage.classList.remove('d-none');
            modalStatus.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> Image loaded successfully!</div>';
        };
        
        testImg.onerror = function() {
            modalStatus.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Image could not be loaded</div>';
            testAlternativeFilePaths(fileData.original);
        };
        
        testImg.src = fileData.url;
    }
    
    // Set up action buttons
    setupModalButtons(fileData);
    modal.show();
}

function setupModalButtons(fileData) {
    // Download button
    const downloadBtn = document.getElementById('downloadFileBtn');
    if (downloadBtn) {
        downloadBtn.onclick = function() {
            downloadFile(fileData.url, fileData.name);
        };
    }
    
    // Open in new tab button
    const openTabBtn = document.getElementById('openInNewTabBtn');
    if (openTabBtn) {
        openTabBtn.onclick = function() {
            window.open(fileData.url, '_blank');
        };
    }
    
    // Refresh button
    const refreshBtn = document.getElementById('refreshFileBtn');
    if (refreshBtn) {
        refreshBtn.onclick = function() {
            openFileModal(fileData);
        };
    }
}

function downloadFile(fileUrl, fileName) {
    try {
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = fileName;
        link.target = '_blank'; // Fallback for some browsers
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (e) {
        console.error('Download failed:', e);
        // Fallback: open in new window
        window.open(fileUrl, '_blank');
    }
}

function testAlternativeFilePaths(originalPath) {
    const filename = originalPath.split('/').pop();
    const testPaths = [
        '/NOV_SYSTEM/uploads/notice_images/' + filename,
        '/NOV_SYSTEM/uploads/notice_files/' + filename,
        '/nov_system/uploads/notice_images/' + filename,
        '/uploads/notice_images/' + filename,
        '/public/uploads/notice_images/' + filename
    ];
    
    const resultsContainer = document.getElementById('pathTestResults');
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = '<h6>Testing Alternative Paths...</h6>';
    
    testPaths.forEach((path, index) => {
        const resultDiv = document.createElement('div');
        resultDiv.className = 'small mb-1';
        resultDiv.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Testing: <code>${path}</code>`;
        resultsContainer.appendChild(resultDiv);
        
        // For PDFs, we can't use Image() to test, so we'll use fetch
        fetch(path, { method: 'HEAD' })
            .then(response => {
                if (response.ok) {
                    resultDiv.innerHTML = `<i class="fas fa-check text-success"></i> <strong>Found:</strong> <code>${path}</code>`;
                    
                    // Update modal if this path works
                    if (currentFileData && currentFileData.is_pdf) {
                        const pdfEmbed = document.getElementById('pdfEmbed');
                        if (pdfEmbed && pdfEmbed.src === '') {
                            pdfEmbed.src = path;
                        }
                    } else {
                        const modalImage = document.getElementById('modalImage');
                        if (modalImage && modalImage.classList.contains('d-none')) {
                            modalImage.src = path;
                            modalImage.classList.remove('d-none');
                            const statusDiv = document.getElementById('modalFileStatus');
                            if (statusDiv) {
                                statusDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> File found using alternative path!</div>';
                            }
                        }
                    }
                } else {
                    resultDiv.innerHTML = `<i class="fas fa-times text-danger"></i> Not found: <code>${path}</code>`;
                }
            })
            .catch(() => {
                resultDiv.innerHTML = `<i class="fas fa-times text-danger"></i> Not found: <code>${path}</code>`;
            });
    });
}
// Fix 4: Improved JavaScript error handling
let currentImageData = null;

function handleImageError(img, imageName, imageId) {
    console.log('Image error for:', imageName, 'Image ID:', imageId);
    
    // Show error indicator
    const errorIndicator = document.getElementById('error-' + imageId);
    if (errorIndicator) {
        errorIndicator.classList.remove('d-none');
    }
    
    // Try alternative paths
    const filename = imageName;
    
    const alternativePaths = [
        '/NOV_SYSTEM/uploads/notice_images/' + filename,
        '/nov_system/uploads/notice_images/' + filename,
        '/uploads/notice_images/' + filename,
        '/assets/img/placeholder.jpg'
    ];
    
    let currentIndex = 0;
    let originalSrc = img.src;
    
    function tryNextPath() {
        if (currentIndex < alternativePaths.length) {
            const testImg = new Image();
            testImg.onload = function() {
                console.log('Found image at:', alternativePaths[currentIndex]);
                img.src = alternativePaths[currentIndex];
                if (errorIndicator) {
                    errorIndicator.classList.add('d-none');
                }
            };
            testImg.onerror = function() {
                console.log('Failed to load:', alternativePaths[currentIndex]);
                currentIndex++;
                setTimeout(tryNextPath, 100); // Small delay to prevent overwhelming
            };
            
            // Don't test the same path again
            if (alternativePaths[currentIndex] !== originalSrc) {
                testImg.src = alternativePaths[currentIndex];
            } else {
                currentIndex++;
                tryNextPath();
            }
        } else {
            // All paths failed, show placeholder or default
            console.log('All paths failed for:', imageName);
            img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmaWxsPSIjNmM3NTdkIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';
            img.alt = 'Image not found: ' + imageName;
        }
    }
    
    tryNextPath();
}

function openImageModal(imageData) {
    console.log('Opening modal for:', imageData);
    currentImageData = imageData;
    
    const modal = new bootstrap.Modal(document.getElementById('enhancedImageModal'));
    const modalImage = document.getElementById('modalImage');
    const modalStatus = document.getElementById('modalImageStatus');
    const modalTitle = document.getElementById('enhancedImageModalLabel');
    
    // Reset modal
    modalImage.style.display = 'none';
    modalStatus.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    modalTitle.textContent = 'Loading: ' + imageData.name;
    
    // Set image information
    document.getElementById('modalImageName').textContent = imageData.name;
    document.getElementById('modalOriginalPath').textContent = imageData.original;
    document.getElementById('modalResolvedPath').textContent = imageData.corrected;
    
    // Test image loading
    const testImg = new Image();
    testImg.onload = function() {
        modalImage.src = imageData.url;
        modalImage.style.display = 'block';
        modalStatus.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> Image loaded successfully!</div>';
        modalTitle.textContent = imageData.name;
        
        // Set up download button
        const downloadBtn = document.getElementById('downloadImageBtn');
        if (downloadBtn) {
            downloadBtn.onclick = function() {
                try {
                    const link = document.createElement('a');
                    link.href = imageData.url;
                    link.download = imageData.name;
                    link.click();
                } catch (e) {
                    console.error('Download failed:', e);
                    alert('Download failed. Please try right-clicking the image and selecting "Save As".');
                }
            };
        }
    };
    
    testImg.onerror = function() {
        modalStatus.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Image could not be loaded</div>';
        modalTitle.textContent = imageData.name + ' (Not Found)';
        testAlternativePaths(imageData.original);
    };
    
    testImg.src = imageData.url;
    modal.show();
}

function testImagePaths(imageId, originalPath) {
    console.log('Testing paths for image:', imageId, originalPath);
    
    const filename = originalPath.split('/').pop();
    const img = document.getElementById('img-' + imageId);
    const loading = document.getElementById('loading-' + imageId);
    
    if (!img) {
        console.error('Image element not found:', 'img-' + imageId);
        return;
    }
    
    if (loading) loading.classList.remove('d-none');
    
    // Test paths and update image if found
    const testPaths = [
        '/NOV_SYSTEM/uploads/notice_images/' + filename,
        '/nov_system/uploads/notice_images/' + filename,
        '/uploads/notice_images/' + filename
    ];
    
    let found = false;
    let tested = 0;
    
    testPaths.forEach(path => {
        const testImg = new Image();
        testImg.onload = function() {
            if (!found) {
                found = true;
                img.src = path;
                const errorIndicator = document.getElementById('error-' + imageId);
                if (errorIndicator) errorIndicator.classList.add('d-none');
                console.log('Image found at:', path);
            }
            tested++;
            if (tested === testPaths.length && loading) {
                loading.classList.add('d-none');
            }
        };
        testImg.onerror = function() {
            tested++;
            if (tested === testPaths.length && loading) {
                loading.classList.add('d-none');
                if (!found) {
                    const errorIndicator = document.getElementById('error-' + imageId);
                    if (errorIndicator) errorIndicator.classList.remove('d-none');
                    console.log('No working path found for:', originalPath);
                }
            }
        };
        testImg.src = path;
    });
}

function testAlternativePaths(originalPath) {
    const filename = originalPath.split('/').pop();
    const testPaths = [
        '/NOV_SYSTEM/uploads/notice_images/' + filename,
        '/nov_system/uploads/notice_images/' + filename,
        '/uploads/notice_images/' + filename,
        '/public/uploads/notice_images/' + filename
    ];
    
    const resultsContainer = document.getElementById('pathTestResults');
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = '<h6>Testing Alternative Paths...</h6>';
    
    testPaths.forEach((path, index) => {
        const testImg = new Image();
        const resultDiv = document.createElement('div');
        resultDiv.className = 'small mb-1';
        resultDiv.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Testing: <code>${path}</code>`;
        resultsContainer.appendChild(resultDiv);
        
        testImg.onload = function() {
            resultDiv.innerHTML = `<i class="fas fa-check text-success"></i> <strong>Found:</strong> <code>${path}</code>`;
            // If this path works, update the modal image
            const modalImage = document.getElementById('modalImage');
            if (modalImage && modalImage.style.display === 'none') {
                modalImage.src = path;
                modalImage.style.display = 'block';
                const statusDiv = document.getElementById('modalImageStatus');
                if (statusDiv) {
                    statusDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> Image found using alternative path!</div>';
                }
            }
        };
        
        testImg.onerror = function() {
            resultDiv.innerHTML = `<i class="fas fa-times text-danger"></i> Not found: <code>${path}</code>`;
        };
        
        testImg.src = path;
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Image display system initialized');
    
    // Set up refresh button
    const refreshBtn = document.getElementById('refreshImageBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            if (currentImageData) {
                openImageModal(currentImageData);
            }
        });
    }
});
</script>