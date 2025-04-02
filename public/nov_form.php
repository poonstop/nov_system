<?php
include __DIR__ . '/../connection.php';
include '../templates/header.php';

// Helper function to capitalize first letter of each word
function capitalizeWords($string) {
    return ucwords(strtolower($string));
}
// Previous query remains the same
$query = "
    SELECT 
        id,
        name, 
        address, 
        IFNULL(owner_representative, 'Not specified') AS owner_rep,
        GROUP_CONCAT(violations SEPARATOR ', ') AS all_violations,
        COUNT(violations) AS num_violations,
        date_created,
        date_updated,
        nov_files 
    FROM establishments 
    GROUP BY name, address, owner_representative
    ORDER BY date_updated DESC
";
$result = $conn->query($query);
?>

<style>
    body {
        background: linear-gradient(to bottom, #ffffff 0%, #10346C 100%);
    }
    .container {
        margin-top: 20px;
    }
    .table-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .search-bar {
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .search-bar:focus {
        box-shadow: 0 0 10px rgba(16, 52, 108, 0.3);
        border-color: #10346C;
    }
     /* Modal Styles */
     .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 10px;
    }
    .close-btn {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close-btn:hover {
        color: #000;
    }
    .btn-primary, .btn-secondary {
        margin: 5px;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary {
        background-color: #10346C;
        color: white;
        border: none;
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }
    
    /* Responsive Table Styles */
    @media (max-width: 768px) {
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
        }
        
        .table td, .table th {
            white-space: nowrap;
        }
    }
    
    /* Search Result Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    #recordsTable tbody tr {
        transition: all 0.3s ease;
    }
    
    #recordsTable tbody tr.search-match {
        animation: fadeIn 0.5s ease;
        background-color: rgba(16, 52, 108, 0.05);
    }
    
    #recordsTable tbody tr:hover {
        background-color: rgba(16, 52, 108, 0.1);
        transform: scale(1.01);
    }
    
    /* Empty Search Result Styling */
    .no-results {
        text-align: center;
        color: #6c757d;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
</style>

<div class="container">
    <h4 class="mb-3 text-center">Establishment Management</h4>

    <!-- Search Bar with Icon -->
    <div class="input-group mb-3">
        <span class="input-group-text" style="background-color: #10346C; color: white;">
            <i class="fas fa-search"></i>
        </span>
        <input 
            type="text" 
            id="searchInput" 
            class="form-control search-bar" 
            placeholder="Search establishment, address, or violations"
            onkeyup="filterTable()"
        >
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="recordsTable">
                <!-- Previous table structure remains the same -->
                <thead>
                    <tr>
                        <th onclick="sortTable(0)" style="cursor: pointer;">Establishment</th>
                        <th onclick="sortTable(1)" style="cursor: pointer;">Address</th>
                        <th onclick="sortTable(2)" style="cursor: pointer;">Owner/Representative</th>  
                        <th onclick="sortTable(3)" style="cursor: pointer;">Violations</th>
                        <th>Actions</th>
                        <th onclick="sortTable(4)" style="cursor: pointer;">No. of Violations</th>
                        <th onclick="sortTable(5)" style="cursor: pointer;">Date Created</th>
                         <th onclick="sortTable(6)" style="cursor: pointer;">Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?= $row['id'] ?>">
                    <td><?= ucwords(strtolower(htmlspecialchars($row['name']))) ?></td>
                    <td><?= ucwords(strtolower(htmlspecialchars($row['address']))) ?></td>
                    <td><?= ucwords(strtolower(htmlspecialchars($row['owner_rep']))) ?></td>
                    <td>
                        <?php 
                              
                              // Process violations for proper capitalization
                $violations = array_map('trim', explode(',', $row['all_violations']));
                $formattedViolations = array_map(function($v) {
                    // Special case for PS/ICC
                    if (strpos(strtolower($v), 'ps/icc') !== false) {
                        return 'No PS/ICC Mark';
                    }
                    // Special case for accreditation
                    if (strpos(strtolower($v), 'invalid/expired') !== false) {
                        return 'Invalid/Expired Accreditation';
                    }
                    // Default capitalization
                    return ucwords(strtolower($v));
                }, $violations);
                echo htmlspecialchars(implode(', ', array_unique($formattedViolations)));
                     ?>
                    </td>
                    <td>
                    <?php if (!empty($row['nov_files'])): ?>
                        <button type="button" class="btn btn-sm btn-primary" onclick="openViewModal(<?= $row['id'] ?>)">View NOV</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="openEditModal(<?= $row['id'] ?>)">Edit</button>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= $row['num_violations'] ?></td>
                        <td>
                     <?php 
                     if (!empty($row['date_created']) && $row['date_created'] != '0000-00-00 00:00:00') {
                     try {
                     $date = new DateTime($row['date_created']);
                    echo $date->format('M d, Y h:i A');
                      } catch (Exception $e) {
                        echo 'Invalid date';
                  }
     } else {
        echo 'No date';
    }
    ?>
</td>
    <td>
    <?php 
    if (!empty($row['date_updated']) && $row['date_updated'] != '0000-00-00 00:00:00') {
        try {
            // Create DateTime object (database stores UTC)
            $date = new DateTime($row['date_updated'], new DateTimeZone('UTC'));
            // Convert to Manila time
            $date->setTimezone(new DateTimeZone('Asia/Manila'));
            // Format for display
            echo $date->format('M d, Y h:i A');
            
            /* Debug output */
            error_log("Time Conversion - UTC: {$row['date_updated']} → Manila: ".$date->format('Y-m-d H:i:s'));
        } catch (Exception $e) {
            echo 'Invalid date';
            error_log("Date Error: ".$e->getMessage());
        }
    } else {
        echo 'No date';
    }
    ?>
</td>
</tr>
    <?php endwhile; ?>
</tbody>
</table>
        </div>
    </div>
</div>

<!-- View NOV Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>NOV Details</h3>
            <span class="close-btn" onclick="closeModal('viewModal')">&times;</span>
        </div>
        <div id="viewModalContent"></div>
        <div class="text-center mt-3">
            <button class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Establishment Details</h3>
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
        </div>
        <div id="editModalContent">
            <!-- Form content will be inserted here by JavaScript -->
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
        </div>
    </div>
</div>


<script>
     function openViewModal(id) {
        // Find the row with the matching data-id
        const row = document.querySelector(`tr[data-id="${id}"]`);
         // Check if row exists
        if (!row) {
            console.error('Row not found for ID:', id);
            return;
        }

        const viewModal = document.getElementById('viewModal');
        const viewModalContent = document.getElementById('viewModalContent');
        
        // Construct detailed view content
        viewModalContent.innerHTML = `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Establishment Details</h5>
                        <p><strong>Name:</strong> ${row.cells[0].innerText}</p>
                        <p><strong>Address:</strong> ${row.cells[1].innerText}</p>
                        <p><strong>Owner/Representative:</strong> ${row.cells[2].innerText}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Violation Information</h5>
                        <p><strong>Violations:</strong> ${row.cells[3].innerText}</p>
                        <p><strong>Number of Violations:</strong> ${row.cells[5].innerText}</p>
                        <p><strong>Date Created:</strong> ${row.cells[6].innerText}</p>
                        <p><strong>Last Updated:</strong> ${row.cells[7].innerText}</p>
                    </div>
                </div>
            </div>
        `;
        
        // Display the modal
        viewModal.style.display = 'block';
    }

   // Edit Modal Function
   function openEditModal(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;

     //// Get current Manila time (UTC+8)
     const now = new Date();
    const manilaTime = new Date(now.getTime() + (8 * 60 * 60 * 1000));
    const manilaDateStr = manilaTime.toISOString().slice(0, 16); // Define BEFORE using it
    
    const editModal = document.getElementById('editModal');
    const editModalContent = document.getElementById('editModalContent');
    
    // Get current violations from the row
    const violations = row.cells[3].innerText.split(', ');
    
    const violationOptions = [
        'No PS/ICC Mark',
        'Invalid/Expired Accreditation',
        'Improper Labeling',
        'Price Tag Violations'
    ].map(option => {
        return `<option value="${option}" ${violations.includes(option) ? 'selected' : ''}>${option}</option>`;
    }).join('');
    
    // Get current violations count - fixed this line
    const currentViolationsCount = parseInt(row.cells[5].innerText) || 0;
    
    // Get last updated value
    const lastUpdatedCell = row.cells[7].innerText;
    
    editModalContent.innerHTML = `
    <form id="editForm">
        <input type="hidden" name="id" value="${id}">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Establishment Name</label>
                    <input type="text" class="form-control" name="name" value="${row.cells[0].innerText}">
                </div>
                <div class="form-group mb-3">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" value="${row.cells[1].innerText}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Owner/Representative</label>
                    <input type="text" class="form-control" name="owner_rep" value="${row.cells[2].innerText}">
                </div>
                <div class="form-group mb-3">
                    <label>Violations</label>
                    <select class="form-control" name="violations[]" multiple id="violationsSelect" onchange="updateViolationCount()">
                        ${violationOptions}
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Number of Violations</label>
                    <input type="number" class="form-control" name="num_violations" 
                       id="numViolationsInput" value="${currentViolationsCount}" readonly>
                </div>
            </div>
         <div class="col-md-6">
            <div class="form-group mb-3">
                <label>Last Updated</label>
                <input type="datetime-local" class="form-control" name="date_updated" value="${manilaDateStr}"readonly>
                </div>
            </div>
        </div>
    </form>`;
     // Initialize the select element change handler
     const select = editModalContent.querySelector('#violationsSelect');
    select.addEventListener('change', function() {
        const count = this.selectedOptions.length;
        document.getElementById('numViolationsInput').value = count;
    });
    
    editModal.style.display = 'block';
}
   
// New function to update violation count when selection changes
    function updateViolationCount() {
    const select = document.getElementById('violationsSelect');
    const numViolationsInput = document.getElementById('numViolationsInput');
    const selectedCount = select.selectedOptions.length;
    numViolationsInput.value = selectedCount;
}

    function formatDateForInput(displayDate) {
    if (!displayDate || displayDate.includes('Invalid date') || displayDate.includes('No date')) {
        return new Date().toISOString().slice(0, 16);
    }
    
    try {
        // Try parsing from displayed format (Mar 31, 2025 01:13 PM)
        const months = {
            Jan: '01', Feb: '02', Mar: '03', Apr: '04', May: '05', Jun: '06',
            Jul: '07', Aug: '08', Sep: '09', Oct: '10', Nov: '11', Dec: '12'
        };
        
        const parts = displayDate.split(/[\s,:]+/);
        if (parts.length >= 6) {
            const month = months[parts[0]];
            const day = parts[1].padStart(2, '0');
            const year = parts[2];
            let hour = parseInt(parts[3]);
            const minute = parts[4];
            const ampm = parts[5].toUpperCase();
            
            if (ampm === 'PM' && hour < 12) hour += 12;
            if (ampm === 'AM' && hour === 12) hour = 0;
            
            return `${year}-${month}-${day}T${hour.toString().padStart(2, '0')}:${minute}`;
        }
        
        return new Date(displayDate).toISOString().slice(0, 16);
    } catch (e) {
        console.error('Date parsing error:', e);
        return new Date().toISOString().slice(0, 16);
    }
}

    // Close Modal Function
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Add click event to close modal when clicking outside of modal content
    window.addEventListener('click', function(event) {
        const viewModal = document.getElementById('viewModal');
        const editModal = document.getElementById('editModal');
        
        if (event.target == viewModal) {
            viewModal.style.display = 'none';
        }
        
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }
    });
   
    function saveChanges() {
    const editForm = document.getElementById('editForm');
    if (!editForm) {
        console.error('Edit form not found!');
        Swal.fire({
            title: 'Error!',
            text: 'Cannot find the form to save changes',
            icon: 'error'
        });
        return;
    }

     // Get all form elements
     const violationsSelect = document.getElementById('violationsSelect');
    const numViolationsInput = document.getElementById('numViolationsInput');
    
    if (!violationsSelect || !numViolationsInput) {
        console.error('Form elements not found!');
        Swal.fire({
            title: 'Error!',
            text: 'Cannot find required form elements',
            icon: 'error'
        });
        return;
    }

     // Get current Manila time (UTC+8)
     const now = new Date();
     const manilaTime = new Date(now.getTime() + (8 * 60 * 60 * 1000));
     
    // Format for MySQL (YYYY-MM-DD HH:MM:SS)
    const year = manilaTime.getUTCFullYear();
    const month = String(manilaTime.getUTCMonth() + 1).padStart(2, '0');
    const day = String(manilaTime.getUTCDate()).padStart(2, '0');
    const hours = String(manilaTime.getUTCHours()).padStart(2, '0');
    const minutes = String(manilaTime.getUTCMinutes()).padStart(2, '0');
    const seconds = String(manilaTime.getUTCSeconds()).padStart(2, '0');
    
    const mysqlDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

    // Prepare form data with validation
    const formData = {
        id: editForm.querySelector('[name="id"]').value,
        name: editForm.querySelector('[name="name"]')?.value || '',
        address: editForm.querySelector('[name="address"]')?.value || '',
        owner_rep: editForm.querySelector('[name="owner_rep"]')?.value || '',
        violations: Array.from(violationsSelect.selectedOptions)
                      .map(opt => opt.value)
                      .join(', '),
        num_violations: parseInt(numViolationsInput.value) || 0,
        date_updated: mysqlDateTime // Always use current date/time
    };

    const dateUpdatedInput = editForm.querySelector('[name="date_updated"]');
     if (dateUpdatedInput?.value) {
         formData.date_updated = dateUpdatedInput.value.replace('T', ' ') + ':00';
    }

    // Debug output
    console.log('Form data being submitted:', formData);

    // Enhanced fetch request with timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

    fetch('update_establishment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData),
        signal: controller.signal
    })
    .then(async response => {
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            // Try to get error details from response
            const errorData = await response.json().catch(() => ({}));
            throw new Error(
                errorData.message || 
                `Server responded with status ${response.status}: ${response.statusText}`
            );
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Update failed without error message');
        }
        
        Swal.fire({
            title: 'Success!',
            text: 'Changes saved successfully',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            closeModal('editModal');
            location.reload(); // Refresh to show updated data
        });
    })
    .catch(error => {
        clearTimeout(timeoutId);
        console.error('Full error details:', error);
        
        let errorMessage = error.message;
        if (error.name === 'AbortError') {
            errorMessage = 'Request timed out. Please try again.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage = 'Network connection failed. Please check your internet connection.';
        }

        Swal.fire({
            title: 'Error!',
            text: `Failed to save changes: ${errorMessage}`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

    function filterTable() {
        const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('recordsTable');
    if (!table) {
        console.error('Table not found');
        return;
    }

    const searchValue = searchInput.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');
    let visibleRowCount = 0;

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;

        cells.forEach((cell, index) => {
            // Skip actions column (assuming it's index 4)
            if (index !== 4 && cell.textContent.toLowerCase().includes(searchValue)) {
                match = true;
            }
        });

        if (match) {
            row.style.display = '';
            row.classList.add('search-match');
            visibleRowCount++;
        } else {
            row.style.display = 'none';
            row.classList.remove('search-match');
        }
    });

    // Show/hide no results message if you have one
    const noResultsDiv = document.getElementById('noResults');
    if (noResultsDiv) {
        noResultsDiv.style.display = visibleRowCount === 0 ? 'block' : 'none';
    }
}
    // Previous sortTable function remains the same
    function sortTable(columnIndex) {
        const table = document.getElementById('recordsTable');
        const rows = Array.from(table.rows).slice(1);
        const isAscending = table.getAttribute('data-sort') !== 'asc';
        const multiplier = isAscending ? 1 : -1;

        // Previous sorting logic remains the same
        if (columnIndex === 5) {
            rows.sort((a, b) => {
                const aNum = parseInt(a.cells[columnIndex].innerText);
                const bNum = parseInt(b.cells[columnIndex].innerText);
                return (aNum - bNum) * multiplier;
            });
        } 
        else if (columnIndex === 6) {
            rows.sort((a, b) => {
                const aDate = new Date(a.cells[columnIndex].innerText);
                const bDate = new Date(b.cells[columnIndex].innerText);
                return (aDate - bDate) * multiplier;
            });
        } 
        else {
            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].innerText.toLowerCase();
                const bText = b.cells[columnIndex].innerText.toLowerCase();
                return aText.localeCompare(bText) * multiplier;
            });
        }

        rows.forEach(row => table.appendChild(row));
        table.setAttribute('data-sort', isAscending ? 'asc' : 'desc');
    }
</script>