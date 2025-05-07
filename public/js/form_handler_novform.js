// Global variables
let currentEstablishmentId = null;
let updateTimersInterval = null; // Added declaration for missing variable

/**
 * Format date and time with proper relative time indicators
 * @param {string} dateString - Date string from database
 * @returns {string} - Formatted date string with appropriate badge
 */
function formatLastUpdated(dateString, timezone = 'Asia/Manila') {
    if (!dateString || dateString === '0000-00-00 00:00:00') {
        return 'No date';
    }
    
    try {
        // Parse the date - FIXED: Don't add 'Z' to treat it as local time from DB
        // The database times are already in the server's local time
        const date = new Date(dateString);
        
        // Format the date nicely for Philippines locale
        const formattedDate = new Intl.DateTimeFormat('en-PH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
            timeZone: timezone
        }).format(date);
        
        // Calculate time difference
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        // Create appropriate badge based on time difference
        let badge = '';
        if (diffMins < 5) {
            badge = '<span class="badge bg-success">Just now</span>';
        } else if (diffMins < 60) {
            badge = `<span class="badge bg-success">${diffMins} min${diffMins !== 1 ? 's' : ''} ago</span>`;
        } else if (diffHours < 24) {
            badge = `<span class="badge bg-success">${diffHours} hour${diffHours !== 1 ? 's' : ''} ago</span>`;
        } else if (diffDays === 0) {
            badge = '<span class="badge bg-success">Today</span>';
        } else if (diffDays === 1) {
            badge = '<span class="badge bg-success">Yesterday</span>';
        } else if (diffDays <= 7) {
            badge = `<span class="badge bg-success">${diffDays} days ago</span>`;
        } else if (diffDays <= 30) {
            badge = `<span class="badge bg-warning">${diffDays} days ago</span>`;
        } else {
            badge = `<span class="badge bg-danger">${diffDays} days ago</span>`;
        }
        
        return `${formattedDate} ${badge}`;
    } catch (e) {
        console.error('Date formatting error:', e);
        return 'Invalid date';
    }
}

// Update the PHP table display for last updated
function updateLastUpdatedDisplay() {
    // Get all elements with timestamps
    const timeElements = document.querySelectorAll('[data-timestamp]');
    
    timeElements.forEach(element => {
        const timestamp = element.getAttribute('data-timestamp');
        if (timestamp && timestamp !== '0000-00-00 00:00:00') {
            try {
                element.innerHTML = formatLastUpdated(timestamp);
            } catch (e) {
                console.error('Error updating time display:', e, timestamp);
            }
        }
    });
}

function startTimerUpdates() {
    // Clear any existing interval
    if (updateTimersInterval) {
        clearInterval(updateTimersInterval);
    }
    
    // Update immediately
    updateLastUpdatedDisplay();
    
    // Then update every minute
    updateTimersInterval = setInterval(updateLastUpdatedDisplay, 60000);
}

function loadEstablishmentData(establishmentId) {
    currentEstablishmentId = establishmentId;
    
    // Show loading spinner
    const tableBody = document.querySelector('#recordsTable tbody');
    tableBody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';
    
    // FIXED: Use correct variable establishmentId instead of Id
    fetch(`get_establishment.php?id=${establishmentId}`)
        .then(response => response.json())
        .then(data => {
            // Clear loading indicator
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                // Show empty message
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No records found</td></tr>';
                return;
            }
            
            // Populate table with data
            data.forEach(record => {
                const row = document.createElement('tr');
                
                // Create cells for other columns
                // ...
                
                // Create last updated cell with timestamp data attribute
                const lastUpdatedCell = document.createElement('td');
                if (record.date_updated && record.date_updated !== '0000-00-00 00:00:00') {
                    lastUpdatedCell.innerHTML = `<span data-timestamp="${record.date_updated}"></span>`;
                } else {
                    lastUpdatedCell.textContent = 'No date';
                }
                
                row.appendChild(lastUpdatedCell);
                tableBody.appendChild(row);
            });
            
            // Update all timestamps after populating the table
            updateLastUpdatedDisplay();
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
        });
}

function refreshCurrentData() {
    if (currentEstablishmentId !== null) {
        loadEstablishmentData(currentEstablishmentId);
    } else {
        // Just update the time displays if we're not loading new data
        updateLastUpdatedDisplay();
    }
}

// ADDED: Missing function from original code
function updateExistingTable() {
    updateLastUpdatedDisplay();
}

// Example usage with a refresh button
document.addEventListener('DOMContentLoaded', function() {
    // Start timer updates
    startTimerUpdates();
    
    // Handle refresh button clicks
    const refreshButton = document.getElementById('refreshButton');
    if (refreshButton) {
        refreshButton.addEventListener('click', function(e) {
            e.preventDefault();
            refreshCurrentData();
        });
    }
    
    // Handle establishment selection changes
    const establishmentSelect = document.getElementById('establishmentSelect');
    if (establishmentSelect) {
        establishmentSelect.addEventListener('change', function() {
            const selectedId = parseInt(this.value, 10);
            if (!isNaN(selectedId)) {
                loadEstablishmentData(selectedId);
            }
        });
        
        // Trigger initial load if a value is selected
        if (establishmentSelect.value) {
            const initialId = parseInt(establishmentSelect.value, 10);
            if (!isNaN(initialId)) {
                loadEstablishmentData(initialId);
            }
        }
    }
    
    // Update any existing table content for backward compatibility
    updateExistingTable();
});
    
    // Improved AJAX call to get establishment details
    fetchWithTimeout(`get_establishment.php?id=${id}`, {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayEstablishmentDetails(data.establishment);
            
            // Set up the edit button to open edit modal
            document.getElementById('editFromViewBtn').onclick = function() {
                closeModal('viewModal');
                openEditModal(id);
            };
            
            // Set up the NOV button with error handling
            document.getElementById('generateNovBtn').onclick = function() {
                try {
                    // Use a more reliable navigation method
                    const novUrl = `generate_nov.php?id=${id}`;
                    const a = document.createElement('a');
                    a.href = novUrl;
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                } catch (err) {
                    console.error('Navigation error:', err);
                    alert('Error generating NOV. Please try again.');
                }
            };
        } else {
            document.getElementById('viewModalContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading establishment details: ${data.message || 'Unknown error'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error fetching establishment details:', error);
        document.getElementById('viewModalContent').innerHTML = `
            <div class="alert alert-danger">
                ${error.message || 'Network error. Please check your connection and try again.'}
                <button class="btn btn-sm btn-primary mt-2" onclick="openViewModal(${id})">
                    <i class="fas fa-sync"></i> Retry
                </button>
            </div>
        `;
    });


function displayEstablishmentDetails(establishment) {
    // Format the violations
    let violationsBadges = '';
    if (establishment.all_violations) {
        const violations = establishment.all_violations.split(',').map(v => v.trim());
        violationsBadges = violations.map(v => {
            let className = 'bg-secondary';
            if (v.toLowerCase().includes('ps/icc')) className = 'bg-danger';
            if (v.toLowerCase().includes('invalid/expired')) className = 'bg-warning';
            if (v.toLowerCase().includes('improper labeling')) className = 'bg-info';
            if (v.toLowerCase().includes('price tag')) className = 'bg-secondary';
            
            return `<span class="badge ${className} me-1">${v}</span>`;
        }).join(' ');
    } else {
        violationsBadges = '<span class="text-muted">No violations recorded</span>';
    }
    
    // Format the products
    let productsHTML = '';
    if (establishment.inventory_products) {
        try {
            const products = JSON.parse(establishment.inventory_products);
            if (Array.isArray(products) && products.length > 0) {
                productsHTML = `
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                products.forEach(product => {
                    const status = [];
                    if (product.sealed == 1) status.push('<span class="badge bg-info">Sealed</span>');
                    if (product.withdrawn == 1) status.push('<span class="badge bg-warning">Withdrawn</span>');
                    
                    productsHTML += `
                        <tr>
                            <td>${product.product_name}</td>
                            <td>₱${parseFloat(product.price).toFixed(2)}</td>
                            <td>${product.quantity}</td>
                            <td>${status.length ? status.join(' ') : '<span class="text-muted">-</span>'}</td>
                        </tr>
                    `;
                });
                
                productsHTML += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                productsHTML = '<p class="text-muted">No products in inventory</p>';
            }
        } catch (e) {
            productsHTML = `<p class="text-muted">${establishment.inventory_products}</p>`;
        }
    } else {
        productsHTML = '<p class="text-muted">No products in inventory</p>';
    }
    
    // Determine status badge
    let statusBadge = '<span class="badge bg-warning">Pending</span>';
    if (establishment.remarks) {
        const remarks = establishment.remarks.toLowerCase();
        if (remarks.includes('urgent')) {
            statusBadge = '<span class="badge bg-danger">Urgent</span>';
        } else if (remarks.includes('resolved') || remarks.includes('complete')) {
            statusBadge = '<span class="badge bg-success">Resolved</span>';
        }
    }
    
    // Format dates
    let createdDate = 'Not available';
    let updatedDate = 'Not available';
    
    if (establishment.date_created && establishment.date_created !== '0000-00-00 00:00:00') {
        createdDate = formatLastUpdated(establishment.date_created).split('<span')[0].trim(); // Just the date without badge
    }
    
    if (establishment.date_updated && establishment.date_updated !== '0000-00-00 00:00:00') {
        updatedDate = formatLastUpdated(establishment.date_updated);
    }
    
    // NOV files section
    let novFilesHTML = '<p class="text-muted">No NOV files uploaded</p>';
    if (establishment.nov_files) {
        try {
            const novFiles = JSON.parse(establishment.nov_files);
            if (Array.isArray(novFiles) && novFiles.length > 0) {
                novFilesHTML = `
                    <ul class="list-group">
                        ${novFiles.map(file => `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-file-pdf text-danger me-2"></i>${file.name}</span>
                                <a href="uploads/nov/${file.filename}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                `;
            }
        } catch (e) {
            novFilesHTML = '<p class="text-muted">Error loading NOV files</p>';
        }
    }
    
    // Build the complete details HTML
    document.getElementById('viewModalContent').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h4>${establishment.name}</h4>
                <p><i class="fas fa-map-marker-alt text-danger"></i> ${establishment.address || 'No address provided'}</p>
                <p><i class="fas fa-user text-primary"></i> ${establishment.owner_rep || 'No representative specified'}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p><strong>Status:</strong> ${statusBadge}</p>
                <p><strong>Business Type:</strong> ${establishment.nature || 'Not specified'}</p>
                <p><strong>Last Updated:</strong> ${updatedDate}</p>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Violations</h5>
            </div>
            <div class="card-body">
                ${violationsBadges}
                <div class="mt-2">
                    <small class="text-muted">Total violations: ${establishment.num_violations || '0'}</small>
                </div>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-box"></i> Products Inventory</h5>
            </div>
            <div class="card-body">
                ${productsHTML}
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt"></i> NOV Documents</h5>
            </div>
            <div class="card-body">
                ${novFilesHTML}
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-comment"></i> Remarks</h5>
            </div>
            <div class="card-body">
                ${establishment.remarks ? `<p>${establishment.remarks}</p>` : '<p class="text-muted">No remarks provided</p>'}
            </div>
        </div>
        
        <div class="text-muted mt-3">
            <small>Record created on: ${createdDate}</small>
        </div>
    `;
}

function openEditModal(id) {
    currentEstablishmentId = id;
    document.getElementById('editModal').style.display = 'block';
    document.getElementById('edit_id').value = id;
    
    // Improved AJAX call with error handling
    fetchWithTimeout(`get_establishment.php?id=${id}`, {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            populateEditForm(data.establishment);
        } else {
            throw new Error(data.message || 'Error loading establishment details');
        }
    })
    .catch(error => {
        console.error('Error fetching establishment data:', error);
        alert(error.message || 'Network error. Please check your connection and try again.');
        closeModal('editModal');
    });
}

function populateEditForm(establishment) {
    // Basic info
    document.getElementById('edit_name').value = establishment.name || '';
    document.getElementById('edit_address').value = establishment.address || '';
    document.getElementById('edit_owner_rep').value = establishment.owner_rep || '';
    document.getElementById('edit_nature').value = establishment.nature || 'Retail Trade';
    document.getElementById('edit_remarks').value = establishment.remarks || '';
    
    // Status
    let status = 'pending';
    if (establishment.remarks) {
        const remarks = establishment.remarks.toLowerCase();
        if (remarks.includes('urgent')) {
            status = 'urgent';
        } else if (remarks.includes('resolved') || remarks.includes('complete')) {
            status = 'resolved';
        }
    }
    document.getElementById('edit_status').value = status;
    
    // Clear violation checkboxes
    document.getElementById('violation_psicc').checked = false;
    document.getElementById('violation_expired').checked = false;
    document.getElementById('violation_labeling').checked = false;
    document.getElementById('violation_price').checked = false;
    
    // Set violation checkboxes
    if (establishment.all_violations) {
        const violations = establishment.all_violations.split(',').map(v => v.trim().toLowerCase());
        if (violations.some(v => v.includes('ps/icc'))) {
            document.getElementById('violation_psicc').checked = true;
        }
        if (violations.some(v => v.includes('invalid/expired'))) {
            document.getElementById('violation_expired').checked = true;
        }
        if (violations.some(v => v.includes('improper labeling'))) {
            document.getElementById('violation_labeling').checked = true;
        }
        if (violations.some(v => v.includes('price tag'))) {
            document.getElementById('violation_price').checked = true;
        }
    }
    
    // Clear existing inventory items
    document.getElementById('inventory_items').innerHTML = '';
    
    // Add inventory items
    if (establishment.inventory_products) {
        try {
            const products = JSON.parse(establishment.inventory_products);
            if (Array.isArray(products) && products.length > 0) {
                products.forEach(product => {
                    addInventoryItemWithData(product);
                });
            } else {
                // Add one empty item if no products
                addInventoryItem();
            }
        } catch (e) {
            console.error('Error parsing inventory products:', e);
            // Add one empty item if error
            addInventoryItem();
        }
    } else {
        // Add one empty item if no inventory
        addInventoryItem();
    }
}

function addInventoryItem() {
    const container = document.getElementById('inventory_items');
    const itemDiv = document.createElement('div');
    itemDiv.classList.add('inventory-item');
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Product Name</label>
                <input type="text" class="form-control" name="product_name[]">
            </div>
            <div class="col-md-3 mb-2">
                <label>Price</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" name="product_price[]" min="0" step="0.01" value="0.00">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label>Quantity</label>
                <input type="number" class="form-control" name="product_quantity[]" min="0" value="0">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_sealed[]" value="1">
                    <label class="form-check-label">Sealed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_withdrawn[]" value="1">
                    <label class="form-check-label">Withdrawn</label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-item" onclick="removeInventoryItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

function addInventoryItemWithData(product) {
    const container = document.getElementById('inventory_items');
    const itemDiv = document.createElement('div');
    itemDiv.classList.add('inventory-item');
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Product Name</label>
                <input type="text" class="form-control" name="product_name[]" value="${product.product_name || ''}">
            </div>
            <div class="col-md-3 mb-2">
                <label>Price</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" name="product_price[]" min="0" step="0.01" value="${parseFloat(product.price || 0).toFixed(2)}">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label>Quantity</label>
                <input type="number" class="form-control" name="product_quantity[]" min="0" value="${product.quantity || 0}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_sealed[]" value="1" ${product.sealed == 1 ? 'checked' : ''}>
                    <label class="form-check-label">Sealed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_withdrawn[]" value="1" ${product.withdrawn == 1 ? 'checked' : ''}>
                    <label class="form-check-label">Withdrawn</label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-item" onclick="removeInventoryItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

function removeInventoryItem(button) {
    const item = button.closest('.inventory-item');
    item.remove();
}

function addInventoryItemToAdd() {
    const container = document.getElementById('add_inventory_items');
    const itemDiv = document.createElement('div');
    itemDiv.classList.add('inventory-item');
    
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Product Name</label>
                <input type="text" class="form-control" name="product_name[]">
            </div>
            <div class="col-md-3 mb-2">
                <label>Price</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control" name="product_price[]" min="0" step="0.01" value="0.00">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label>Quantity</label>
                <input type="number" class="form-control" name="product_quantity[]" min="0" value="0">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_sealed[]" value="1">
                    <label class="form-check-label">Sealed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="product_withdrawn[]" value="1">
                    <label class="form-check-label">Withdrawn</label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-item" onclick="removeInventoryItem(this)">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function confirmDelete(id) {
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteModal').style.display = 'block';
}

function deleteEstablishment() {
    const id = document.getElementById('delete_id').value;
    
    // Improved AJAX call to delete establishment
    fetchWithTimeout('delete_establishment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cache-Control': 'no-cache'
        },
        body: `id=${id}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove the row from the table
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
            }
            
            // Close the modal and show success message
            closeModal('deleteModal');
            alert('Establishment successfully deleted');
            
            // Check if table is empty
            const tbody = document.querySelector('#recordsTable tbody');
            if (tbody.children.length === 0) {
                document.getElementById('noResults').style.display = 'block';
            }
        } else {
            throw new Error(data.message || 'Unknown error deleting establishment');
        }
    })
    .catch(error => {
        console.error('Error deleting establishment:', error);
        alert(error.message || 'Network error. Please check your connection and try again.');
    });
}

    function saveChanges() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    
    // Add status to remarks if needed
    const status = document.getElementById('edit_status').value;
    let remarks = formData.get('remarks') || '';
    
    if (status === 'urgent' && !remarks.toLowerCase().includes('urgent')) {
        remarks = '[URGENT] ' + remarks;
    } else if (status === 'resolved' && !remarks.toLowerCase().includes('resolved')) {
        remarks = '[RESOLVED] ' + remarks;
    }
    
    formData.set('remarks', remarks);
    
    // AJAX call to update establishment with improved error handling
    fetchWithTimeout('update_establishment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message before reloading
            alert('Establishment successfully updated');
            location.reload();
        } else {
            throw new Error(data.message || 'Unknown error updating establishment');
        }
    })
    .catch(error => {
        console.error('Error updating establishment:', error);
        alert(error.message || 'Network error. Please check your connection and try again.');
    });
}

    function saveNewEstablishment() {
    const form = document.getElementById('addForm');
    const formData = new FormData(form);
    
    // Validate required fields
    const name = formData.get('name');
    const address = formData.get('address');
    const nature = formData.get('nature');
    
    if (!name || !address || !nature) {
        alert('Please fill in all required fields (Name, Address, and Business Type)');
        return;
    }
    
    // Show loading state
    const saveButton = document.querySelector('#addModal .btn-primary');
    const originalButtonText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveButton.disabled = true;
    
    // Process violations
    const selectedViolations = [];
    document.querySelectorAll('#addForm input[name="violations[]"]:checked').forEach(checkbox => {
        selectedViolations.push(checkbox.value);
    });
    
    // AJAX call to save establishment
    fetchWithTimeout('save_establishment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Establishment successfully added');
            // Reload the page to show the new establishment
            location.reload();
        } else {
            // Reset button and show error
            saveButton.innerHTML = originalButtonText;
            saveButton.disabled = false;
            throw new Error(data.message || 'Unknown error adding establishment');
        }
    })
    .catch(error => {
        console.error('Error adding establishment:', error);
        // Reset button and show error
        saveButton.innerHTML = originalButtonText;
        saveButton.disabled = false;
        alert(error.message || 'Network error. Please check your connection and try again.');
    });
}

// Table filtering and sorting functions
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const violationFilter = document.getElementById('violationFilter').value.toLowerCase();
    const natureFilter = document.getElementById('natureFilter').value.toLowerCase();
    const showUrgent = document.getElementById('showUrgent').checked;
    const showPending = document.getElementById('showPending').checked;
    const showResolved = document.getElementById('showResolved').checked;
    
    const rows = document.querySelectorAll('#recordsTable tbody tr');
    let anyVisible = false;
    
    rows.forEach(row => {
        const statusValue = row.getAttribute('data-status');
        const natureValue = row.getAttribute('data-nature').toLowerCase();
        const address = row.getAttribute('data-address').toLowerCase();
        const owner = row.getAttribute('data-owner').toLowerCase();
        
        // Status filter check
        const statusMatch = (statusValue === 'urgent' && showUrgent) || 
                           (statusValue === 'pending' && showPending) || 
                           (statusValue === 'resolved' && showResolved);
        
        // Nature filter check
        const natureMatch = natureFilter === '' || natureValue.includes(natureFilter);
        
        // Get all content from the row including nested elements
        const establishmentName = row.querySelector('td:first-child strong')?.textContent.toLowerCase() || '';
        const addressVisible = row.querySelector('td:first-child small')?.textContent.toLowerCase() || '';
        
        // Get all violation badges text
        const violationsCell = row.querySelector('.violations-cell');
        const violationBadges = violationsCell.querySelectorAll('.badge');
        let violationTexts = '';
        violationBadges.forEach(badge => {
            violationTexts += ' ' + badge.textContent.toLowerCase();
        });
        
        // Violation filter check - check both badge elements and text content
        const violationMatch = violationFilter === '' || 
                               violationsCell.innerHTML.toLowerCase().includes(violationFilter) ||
                               violationTexts.includes(violationFilter);
        
        // Text search check - search in all relevant data
        const textMatch = searchInput === '' || 
                          establishmentName.includes(searchInput) || 
                          addressVisible.includes(searchInput) ||
                          address.includes(searchInput) || 
                          owner.includes(searchInput) || 
                          violationsCell.textContent.toLowerCase().includes(searchInput) ||
                          violationTexts.includes(searchInput);
        
        // Combine all filters
        const visible = statusMatch && natureMatch && violationMatch && textMatch;
        row.style.display = visible ? '' : 'none';
        
        if (visible) {
            anyVisible = true;
        }
    });
    
    // Show/hide "no results" message
    document.getElementById('noResults').style.display = anyVisible ? 'none' : 'block';
}

function sortTable() {
    const sortOption = document.getElementById('sortBy').value;
    const table = document.getElementById('recordsTable');
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    
    rows.sort((a, b) => {
        if (sortOption === 'nameAsc') {
            const nameA = a.querySelector('td:first-child strong')?.textContent.trim().toLowerCase() || '';
            const nameB = b.querySelector('td:first-child strong')?.textContent.trim().toLowerCase() || '';
            return nameA.localeCompare(nameB);
        } else if (sortOption === 'nameDesc') {
            const nameA = a.querySelector('td:first-child strong')?.textContent.trim().toLowerCase() || '';
            const nameB = b.querySelector('td:first-child strong')?.textContent.trim().toLowerCase() || '';
            return nameB.localeCompare(nameA);
        } else if (sortOption === 'dateAsc' || sortOption === 'dateDesc') {
            // Find the date cell (4th column, index 3)
            const dateCellA = a.querySelector('td:nth-child(4)');
            const dateCellB = b.querySelector('td:nth-child(4)');
            
            // Try to get timestamp from data attribute first
            const dateAElem = dateCellA.querySelector('[data-timestamp]');
            const dateBElem = dateCellB.querySelector('[data-timestamp]');
            
            let dateA, dateB;
            
            if (dateAElem && dateBElem) {
                dateA = dateAElem.getAttribute('data-timestamp');
                dateB = dateBElem.getAttribute('data-timestamp');
            } else {
                // Fall back to text content if no data attribute
                dateA = dateCellA.textContent.trim();
                dateB = dateCellB.textContent.trim();
            }
            
            const comparison = new Date(dateA) - new Date(dateB);
            return sortOption === 'dateAsc' ? comparison : -comparison;
        }
        return 0;
    });
    
    // Reattach sorted rows to table
    const tbody = table.querySelector('tbody');
    rows.forEach(row => tbody.appendChild(row));
}

// Add event listeners for search and filters
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all filter controls
    document.getElementById('searchInput').addEventListener('input', filterTable);
    document.getElementById('violationFilter').addEventListener('change', filterTable);
    document.getElementById('natureFilter').addEventListener('change', filterTable);
    document.getElementById('showUrgent').addEventListener('change', filterTable);
    document.getElementById('showPending').addEventListener('change', filterTable);
    document.getElementById('showResolved').addEventListener('change', filterTable);
    document.getElementById('sortBy').addEventListener('change', sortTable);
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initial sort and filter
    sortTable();
    filterTable();
});

// Helper function for AJAX requests with timeout
function fetchWithTimeout(url, options, timeout = 10000) {
    return Promise.race([
        fetch(url, options),
        new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Request timed out')), timeout)
        )
    ]);
}