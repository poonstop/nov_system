// Fix for action buttons functionality

// First, let's fix the path issue in the buttons
// The buttons are showing file paths instead of functioning properly

document.addEventListener('DOMContentLoaded', function() {
    // Fix the action buttons in the table
    fixActionButtons();
    
    // Initialize the tooltips for the action buttons
    initializeTooltips();
});

function fixActionButtons() {
    // Get all rows in the table
    const rows = document.querySelectorAll('#recordsTable tbody tr');
    
    rows.forEach(row => {
        const id = row.getAttribute('data-id');
        if (!id) return;
        
        // Select the actions cell (last column)
        const actionsCell = row.querySelector('td:last-child');
        if (!actionsCell) return;
        
        // Replace the content with proper buttons
        actionsCell.innerHTML = `
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-primary" onclick="openViewModal(${id})" data-bs-toggle="tooltip" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="openEditModal(${id})" data-bs-toggle="tooltip" title="Edit Record">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(${id})" data-bs-toggle="tooltip" title="Delete Record">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
}

function initializeTooltips() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Modal functions
function openViewModal(id) {
    const modal = document.getElementById('viewModal');
    if (!modal) return;
    
    // Show loading spinner
    document.getElementById('viewModalContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Open the modal
    modal.style.display = 'block';
    
    // Set up the edit button to open edit modal for the same record
    const editBtn = document.getElementById('editFromViewBtn');
    if (editBtn) {
        editBtn.onclick = function() {
            closeModal('viewModal');
            openEditModal(id);
        };
    }
    
    // Fetch the establishment details
    fetchEstablishmentDetails(id);
}

function openEditModal(id) {
    const modal = document.getElementById('editModal');
    if (!modal) return;
    
    // Set the ID in the form
    document.getElementById('edit_id').value = id;
    
    // Open the modal
    modal.style.display = 'block';
    
    // Fetch the establishment details and populate the form
    fetchEstablishmentDetailsForEdit(id);
}

function confirmDelete(id) {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;
    
    // Set the ID in the hidden field
    document.getElementById('delete_id').value = id;
    
    // Open the modal
    modal.style.display = 'block';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Data fetching functions
function fetchEstablishmentDetails(id) {
    // In a real implementation, this would be an AJAX request
    // For demo purposes, we'll simulate loading data
    
    setTimeout(() => {
        const modalContent = document.getElementById('viewModalContent');
        if (!modalContent) return;
        
        // Get the row data from the table
        const row = document.querySelector(`#recordsTable tbody tr[data-id="${id}"]`);
        if (!row) {
            modalContent.innerHTML = '<div class="alert alert-danger">Establishment not found</div>';
            return;
        }
        
        const cells = row.querySelectorAll('td');
        const name = cells[0].textContent;
        const address = cells[1].textContent;
        const owner = cells[2].textContent;
        const nature = cells[3].textContent;
        const violations = cells[4].innerHTML;
        const products = cells[5].textContent;
        const lastUpdated = cells[6].textContent;
        const status = cells[7].querySelector('.badge').textContent;
        
        // Display the details
        modalContent.innerHTML = `
            <div class="details-container">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4>${name}</h4>
                        <p><strong>Address:</strong> ${address}</p>
                        <p><strong>Owner/Representative:</strong> ${owner}</p>
                        <p><strong>Nature of Business:</strong> ${nature}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> ${status}</p>
                        <p><strong>Last Updated:</strong> ${lastUpdated}</p>
                    </div>
                </div>
                
                <h5>Violations</h5>
                <div class="mb-3">
                    ${violations}
                </div>
                
                <h5>Products</h5>
                <div class="mb-3">
                    ${products}
                </div>
            </div>
        `;
    }, 500);
}

function fetchEstablishmentDetailsForEdit(id) {
    // In a real implementation, this would be an AJAX request
    // For demo purposes, we'll simulate loading data
    
    setTimeout(() => {
        // Get the row data from the table
        const row = document.querySelector(`#recordsTable tbody tr[data-id="${id}"]`);
        if (!row) {
            alert('Establishment not found');
            closeModal('editModal');
            return;
        }
        
        const cells = row.querySelectorAll('td');
        const name = cells[0].textContent;
        const address = cells[1].textContent;
        const owner = cells[2].textContent;
        const nature = cells[3].textContent;
        const status = cells[7].querySelector('.badge').textContent.toLowerCase();
        
        // Populate the form fields
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_address').value = address;
        document.getElementById('edit_owner_rep').value = owner;
        document.getElementById('edit_nature').value = nature;
        document.getElementById('edit_status').value = status;
        
        // Check the appropriate violation checkboxes
        const violationsCell = cells[4];
        document.getElementById('violation_psicc').checked = violationsCell.textContent.includes('PS/ICC');
        document.getElementById('violation_expired').checked = violationsCell.textContent.includes('Invalid/Expired');
        document.getElementById('violation_labeling').checked = violationsCell.textContent.includes('Improper Labeling');
        document.getElementById('violation_price').checked = violationsCell.textContent.includes('Price Tag');
        
        // Clear existing inventory items
        document.getElementById('inventory_items').innerHTML = '';
        
        // Add demo inventory items if products exist
        const productsText = cells[5].textContent.trim();
        if (productsText && !productsText.includes('No inventory')) {
            const products = productsText.split(',').map(p => p.trim()).filter(p => !p.match(/^\d+$/));
            
            products.forEach(product => {
                addInventoryItem(product);
            });
        }
    }, 500);
}

// Function to delete the establishment
function deleteEstablishment() {
    const id = document.getElementById('delete_id').value;
    if (!id) return;
    
    // In a real implementation, this would be an AJAX request
    // For demo purposes, we'll just remove the row from the table
    
    const row = document.querySelector(`#recordsTable tbody tr[data-id="${id}"]`);
    if (row) {
        row.remove();
    }
    
    // Close the modal
    closeModal('deleteModal');
    
    // Show success message
    showToast('Establishment deleted successfully', 'success');
}

// Function to save changes to an establishment
function saveChanges() {
    const id = document.getElementById('edit_id').value;
    if (!id) return;
    
    // Get form values
    const name = document.getElementById('edit_name').value;
    const address = document.getElementById('edit_address').value;
    const owner = document.getElementById('edit_owner_rep').value;
    const nature = document.getElementById('edit_nature').value;
    const status = document.getElementById('edit_status').value;
    
    // Get selected violations
    const violations = [];
    if (document.getElementById('violation_psicc').checked) violations.push('No PS/ICC Mark');
    if (document.getElementById('violation_expired').checked) violations.push('Invalid/Expired Accreditation');
    if (document.getElementById('violation_labeling').checked) violations.push('Improper Labeling');
    if (document.getElementById('violation_price').checked) violations.push('Price Tag Violations');
    
    // In a real implementation, this would be an AJAX request
    // For demo purposes, we'll just update the row in the table
    
    const row = document.querySelector(`#recordsTable tbody tr[data-id="${id}"]`);
    if (row) {
        const cells = row.querySelectorAll('td');
        
        // Update cell values
        cells[0].textContent = name;
        cells[1].textContent = address;
        cells[2].textContent = owner;
        cells[3].textContent = nature;
        
        // Update violations with badges
        let violationsHTML = '';
        if (violations.length > 0) {
            violations.forEach(violation => {
                let badgeClass = 'bg-secondary';
                if (violation.includes('PS/ICC')) badgeClass = 'bg-danger';
                else if (violation.includes('Invalid/Expired')) badgeClass = 'bg-warning';
                else if (violation.includes('Improper Labeling')) badgeClass = 'bg-info';
                else if (violation.includes('Price Tag')) badgeClass = 'bg-secondary';
                
                violationsHTML += `<span class="badge ${badgeClass}">${violation}</span> `;
            });
        } else {
            violationsHTML = '<span class="text-muted">No violations</span>';
        }
        cells[4].innerHTML = violationsHTML;
        
        // Update status badge
        let statusBadge = 'bg-warning';
        let statusText = 'Pending';
        if (status === 'urgent') {
            statusBadge = 'bg-danger';
            statusText = 'Urgent';
        } else if (status === 'resolved') {
            statusBadge = 'bg-success';
            statusText = 'Resolved';
        }
        
        cells[7].innerHTML = `
            <span class="status-indicator status-${status}"></span>
            <span class="badge ${statusBadge}">${statusText}</span>
        `;
        
        // Update the row's status data attribute
        row.setAttribute('data-status', status);
        
        // Update last updated date to current time
        const now = new Date();
        const formattedDate = `${now.toLocaleString('default', { month: 'short' })} ${now.getDate()}, ${now.getFullYear()} ${now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;
        cells[6].innerHTML = `${formattedDate} <span class="badge bg-success">Today</span>`;
    }
    
    // Close the modal
    closeModal('editModal');
    
    // Show success message
    showToast('Establishment updated successfully', 'success');
}

// Function to add inventory item to the form
function addInventoryItem(productName = '') {
    const container = document.getElementById('inventory_items');
    const itemId = 'item_' + Math.random().toString(36).substr(2, 9);
    
    const itemHTML = `
        <div class="inventory-item mb-2" id="${itemId}">
            <div class="input-group">
                <input type="text" class="form-control" name="products[]" value="${productName}" placeholder="Product Name">
                <button type="button" class="btn btn-outline-danger" onclick="removeInventoryItem('${itemId}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHTML);
}

// Function to remove inventory item from the form
function removeInventoryItem(itemId) {
    const item = document.getElementById(itemId);
    if (item) {
        item.remove();
    }
}

// Function to display toast message
function showToast(message, type = 'info') {
    // Check if toast container exists, if not create it
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast_' + Math.random().toString(36).substr(2, 9);
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 3000 });
    toast.show();
    
    // Remove the toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Function to filter the table based on search and filter criteria
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const violationFilter = document.getElementById('violationFilter').value;
    const natureFilter = document.getElementById('natureFilter').value;
    const showUrgent = document.getElementById('showUrgent').checked;
    const showPending = document.getElementById('showPending').checked;
    const showResolved = document.getElementById('showResolved').checked;
    
    const rows = document.querySelectorAll('#recordsTable tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const establishment = row.cells[0].textContent.toLowerCase();
        const address = row.cells[1].textContent.toLowerCase();
        const violations = row.cells[4].textContent.toLowerCase();
        const nature = row.getAttribute('data-nature');
        const status = row.getAttribute('data-status');
        
        // Check search criteria
        const matchesSearch = 
            establishment.includes(searchInput) || 
            address.includes(searchInput) || 
            violations.includes(searchInput);
        
        // Check violation filter
        const matchesViolation = !violationFilter || violations.includes(violationFilter.toLowerCase());
        
        // Check nature filter
        const matchesNature = !natureFilter || nature === natureFilter;
        
        // Check status filters
        const matchesStatus = 
            (status === 'urgent' && showUrgent) || 
            (status === 'pending' && showPending) || 
            (status === 'resolved' && showResolved);
        
        // Show or hide the row
        if (matchesSearch && matchesViolation && matchesNature && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show or hide the "no results" message
    const noResults = document.getElementById('noResults');
    if (noResults) {
        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

// Function to sort the table
function sortTable() {
    const sortBy = document.getElementById('sortBy').value;
    const tbody = document.querySelector('#recordsTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        switch (sortBy) {
            case 'dateDesc':
                return new Date(b.cells[6].textContent) - new Date(a.cells[6].textContent);
            case 'dateAsc':
                return new Date(a.cells[6].textContent) - new Date(b.cells[6].textContent);
            case 'nameAsc':
                return a.cells[0].textContent.localeCompare(b.cells[0].textContent);
            case 'nameDesc':
                return b.cells[0].textContent.localeCompare(a.cells[0].textContent);
            case 'violationsDesc':
                const aViolations = a.cells[4].querySelectorAll('.badge').length;
                const bViolations = b.cells[4].querySelectorAll('.badge').length;
                return bViolations - aViolations;
            default:
                return 0;
        }
    });
    
    // Clear and re-append rows in the new order
    rows.forEach(row => tbody.appendChild(row));
}

// Additional CSS to fix styling issues
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .toast-container {
            z-index: 9999;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-urgent {
            background-color: #dc3545;
        }
        
        .status-pending {
            background-color: #ffc107;
        }
        
        .status-resolved {
            background-color: #198754;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 5px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-btn:hover {
            color: black;
        }
        
        .inventory-container {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    `;
    document.head.appendChild(style);
});