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
        CASE 
            WHEN MAX(created_at) IS NULL THEN CURRENT_TIMESTAMP
            WHEN MAX(created_at) = '0000-00-00' THEN CURRENT_TIMESTAMP
            ELSE MAX(created_at)
        END AS latest_date,
        nov_files 
    FROM establishments 
    GROUP BY name, address, owner_representative
    ORDER BY latest_date DESC
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
    <h4 class="mb-3 text-center">Existing Records</h4>

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
                        <th onclick="sortTable(5)" style="cursor: pointer;">Date</th>
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
                        <button class="btn btn-sm btn-primary" onclick="openViewModal(<?= $row['id'] ?>)">View NOV</button>
                        <button class="btn btn-sm btn-secondary" onclick="openEditModal(<?= $row['id'] ?>)">Edit</button>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= $row['num_violations'] ?></td>
                        <td><?= date('M d, Y', strtotime($row['latest_date'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="noResults" class="no-results" style="display: none;">
                <p>No results found. Try a different search term.</p>
            </div>
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
        <div id="editModalContent"></div>
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

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
                        <p><strong>Date:</strong> ${row.cells[6].innerText}</p>
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
        if (!row) {
            console.error('Row not found for ID:', id);
            return;
        }
        
        const editModal = document.getElementById('editModal');
        const editModalContent = document.getElementById('editModalContent');

        // Format violations for select options
        const violations = row.cells[3].innerText.split(', ');
        const violationOptions = [
            'No PS/ICC Mark',
            'Invalid/Expired Accreditation',
            'Other Violation 1',
            'Other Violation 2'
        ].map(option => {
            return `<option value="${option}" ${violations.includes(option) ? 'selected' : ''}>${option}</option>`;
        }).join('');
        
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
                            <select class="form-control" name="violations[]" multiple>
                                ${violationOptions}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Number of Violations</label>
                            <input type="number" class="form-control" name="num_violations" value="${row.cells[5].innerText}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
        <label>Date</label>
        <input type="date" class="form-control" name="date" 
               value="${formatDateForInput(row.cells[6].innerText)}"
               min="2000-01-01" max="${new Date().toISOString().split('T')[0]}">
                        </div>
                    </div>
                </div>
            </form>
        `;
        
        editModal.style.display = 'block';
    }

    // Helper function to format date for input field
    function formatDateForInput(displayDate) {
    // Handle cases where date is invalid (like "Nov 30, -0001")
    if (displayDate.includes('-0001')) {
        return ''; // Return empty or current date
    }
    
    try {
        // Parse from displayed format (M d, Y)
        const date = new Date(displayDate);
        if (isNaN(date.getTime())) {
            // Try parsing from other formats if needed
            const parts = displayDate.split(/[\s,]+/);
            if (parts.length === 3) {
                const monthMap = {Jan:'01', Feb:'02', Mar:'03', Apr:'04', May:'05', Jun:'06', 
                                 Jul:'07', Aug:'08', Sep:'09', Oct:'10', Nov:'11', Dec:'12'};
                const year = parts[2].length === 4 ? parts[2] : new Date().getFullYear();
                return `${year}-${monthMap[parts[0]]}-${parts[1].padStart(2,'0')}`;
            }
            return ''; // Fallback
        }
        return date.toISOString().split('T')[0];
    } catch (e) {
        console.error('Date parse error:', e);
        return ''; // Fallback to empty
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
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    
    // Ensure ID is captured correctly
    const id = form.querySelector('input[name="id"]').value;
    // Convert date to proper format
    let dateValue = formData.get('date');
    if (!dateValue) {
        // If no date selected, use current date
        dateValue = new Date().toISOString().split('T')[0];
    }
    
    const data = {
        id: formData.get('id'),
        name: formData.get('name'),
        address: formData.get('address'),
        owner_rep: formData.get('owner_rep'),
        violations: formData.getAll('violations[]').join(', '),
        num_violations: formData.get('num_violations'),
        date: dateValue
    };
    
    console.log('Sending data:', data);
    
    fetch('update_establishment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json', // Changed back to JSON
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Raw response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Changes saved successfully');
            closeModal('editModal');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save changes'));
        }
    })
    .catch(error => {
        console.error('Full error:', error);
        alert('An error occurred while saving changes: ' + error);
    });
}


    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('recordsTable');
        const rows = table.getElementsByTagName('tr');
        const noResultsDiv = document.getElementById('noResults');
        let visibleRowCount = 0;

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (j !== 4 && cells[j] && cells[j].innerText.toLowerCase().includes(searchInput)) {
                    match = true;
                    break;
                }
            }

            if (match) {
                rows[i].style.display = '';
                rows[i].classList.add('search-match');
                visibleRowCount++;
            } else {
                rows[i].style.display = 'none';
                rows[i].classList.remove('search-match');
            }
        }

        // Show/hide no results message
        noResultsDiv.style.display = visibleRowCount === 0 ? 'block' : 'none';
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