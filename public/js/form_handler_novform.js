console.log('Script loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');

     // Fetch and display records
     fetchRecords();
    
     // Initialize search functionality
     const searchInput = document.getElementById('searchInput');
     if (searchInput) {
         searchInput.addEventListener('input', filterTable);
     }
 });
 
 function fetchRecords() {
     console.log('Fetching records...');
     
     fetch('get_establishments.php')
         .then(response => {
             if (!response.ok) {
                 throw new Error(`HTTP error! status: ${response.status}`);
             }
             return response.json();
         })
         .then(data => {
             console.log('Records received:', data);
             displayRecords(data.establishments || []);
         })
         .catch(error => {
             console.error('Error fetching records:', error);
             
             // Display error message
             const tableContainer = document.getElementById('tableContainer');
             if (tableContainer) {
                 tableContainer.innerHTML = `
                     <div class="alert alert-danger">
                         <strong>Error:</strong> Failed to load establishment records. 
                         ${error.message}
                     </div>`;
             }
         });
 }
 
 function displayRecords(establishments) {
     const tableBody = document.querySelector('#recordsTable tbody');
     if (!tableBody) {
         console.error('Table body element not found');
         return;
     }
     
     // Clear existing rows
     tableBody.innerHTML = '';
     
     if (establishments.length === 0) {
         // Display no records message
         const noRecordsRow = document.createElement('tr');
         noRecordsRow.innerHTML = `
             <td colspan="9" class="text-center py-3">
                 <i class="fas fa-info-circle me-2"></i>No records found
             </td>`;
         tableBody.appendChild(noRecordsRow);
         return;
     }
     
     // Add each establishment to the table
     establishments.forEach(establishment => {
         const row = document.createElement('tr');
         row.setAttribute('data-id', establishment.id);
         
         // Format dates for display
         const createdDate = formatDateForDisplay(establishment.created_at);
         const updatedDate = formatDateForDisplay(establishment.date_updated);
         
         // Create inventory products display with badge
         let productsDisplay = '<span class="text-muted">No inventory</span>';
         if (establishment.products && establishment.products.length > 0) {
             const productNames = establishment.products.map(p => p.product_name);
             productsDisplay = productNames.join(', ') + 
                 ` <span class="badge bg-primary">${productNames.length}</span>`;
         }
         
         row.innerHTML = `
             <td>${capitalizeWords(establishment.name || '')}</td>
             <td>${capitalizeWords(establishment.address || '')}</td>
             <td>${capitalizeWords(establishment.owner_rep || '')}</td>
             <td>${establishment.violations || ''}</td>
             <td>${productsDisplay}</td>
             <td>${establishment.num_violations || '0'}</td>
             <td>${createdDate}</td>
             <td>${updatedDate}</td>
             <td>
                 <button class="btn btn-sm btn-primary" onclick="openViewModal(${establishment.id})">
                     <i class="fas fa-eye"></i> View
                 </button>
                 <button class="btn btn-sm btn-secondary ms-1" onclick="openEditModal(${establishment.id})">
                     <i class="fas fa-edit"></i> Edit
                 </button>
             </td>
         `;
         
         tableBody.appendChild(row);
     });
     
     // Initialize event listeners for view/edit buttons
     const viewButtons = document.querySelectorAll('.btn-primary');
     const editButtons = document.querySelectorAll('.btn-secondary');
     
     console.log('View buttons found:', viewButtons.length);
     console.log('Edit buttons found:', editButtons.length);
 }
 
 function formatDateForDisplay(dateString) {
     if (!dateString) return 'No date';
     
     try {
         const date = new Date(dateString);
         if (isNaN(date.getTime())) return 'Invalid date';
         
         const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                         'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
         const month = months[date.getMonth()];
         const day = date.getDate();
         const year = date.getFullYear();
         
         let hours = date.getHours();
         const ampm = hours >= 12 ? 'PM' : 'AM';
         hours = hours % 12;
         hours = hours ? hours : 12; // Convert 0 to 12 for 12-hour format
         const minutes = String(date.getMinutes()).padStart(2, '0');
         
         return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
     } catch (e) {
         console.error('Date formatting error:', e);
         return 'Invalid date';
     }
 }
    
    // Add event listeners to all edit and view buttons
    const viewButtons = document.querySelectorAll('.btn-primary');
    const editButtons = document.querySelectorAll('.btn-secondary');
    
    console.log('View buttons found:', viewButtons.length);
    console.log('Edit buttons found:', editButtons.length);


function openViewModal(id) {
    console.log('Opening view modal for ID:', id);
    
    // Find the row with the matching data-id
    const row = document.querySelector(`tr[data-id="${id}"]`);
    
    // Check if row exists
    if (!row) {
        console.error('Row not found for ID:', id);
        return;
    }

    const viewModal = document.getElementById('viewModal');
    if (!viewModal) {
        console.error('View modal element not found');
        return;
    }
    
    const viewModalContent = document.getElementById('viewModalContent');
    if (!viewModalContent) {
        console.error('View modal content element not found');
        return;
    }
        
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
    console.log('View modal should be displayed now');
        
    fetch('get_inventory.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let inventoryHtml = '<h4 class="mt-4">Inventory Products</h4>';
            if (data.products.length > 0) {
                inventoryHtml += '<table class="table table-sm"><thead><tr>' +
                    '<th>Product</th><th>Description</th><th>Price</th><th>Pieces</th>' +
                    '<th>Status</th></tr></thead><tbody>';
                
                data.products.forEach(product => {
                    let status = [];
                    if (product.sealed == 1) status.push('Sealed');
                    if (product.withdrawn == 1) status.push('Withdrawn');
                    
                    inventoryHtml += `<tr>
                        <td>${product.product_name}</td>
                        <td>${product.description || ''}</td>
                        <td>${product.price || 0}</td>
                        <td>${product.pieces || 0}</td>
                        <td>${status.join(', ') || 'None'}</td>
                    </tr>`;
                });
                
                inventoryHtml += '</tbody></table>';
            } else {
                inventoryHtml += '<p>No inventory products recorded</p>';
            }
            
            document.getElementById('viewModalContent').innerHTML += inventoryHtml;
        }
    });
}

function openEditModal(id) {
    console.log('Opening edit modal for ID:', id);
    
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) {
        console.error('Row not found for ID:', id);
        alert('Error: Establishment record not found');
        return;
    }

    const editModal = document.getElementById('editModal');
    if (!editModal) {
        console.error('Edit modal element not found');
        alert('Error: Edit modal not found');
        return;
    }
    
    const editModalContent = document.getElementById('editModalContent');
    if (!editModalContent) {
        console.error('Edit modal content element not found');
        alert('Error: Edit form not found');
        return;
    }
 
    // Get current Manila time (UTC+8)
    const now = new Date();
    const manilaOffset = 8 * 60 * 60 * 1000; // 8 hours in milliseconds
    const manilaTime = new Date(now.getTime() + manilaOffset);
    // Format for datetime-local input (YYYY-MM-DDTHH:MM)
    const year = manilaTime.getUTCFullYear();
    const month = String(manilaTime.getUTCMonth() + 1).padStart(2, '0');
    const day = String(manilaTime.getUTCDate()).padStart(2, '0');
    const hours = String(manilaTime.getUTCHours()).padStart(2, '0');
    const minutes = String(manilaTime.getUTCMinutes()).padStart(2, '0');
    currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    
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
    
    // Get current violations count
    const currentViolationsCount = parseInt(row.cells[5].innerText) || 0;
    
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
                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple violations</small>
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
                    <input type="datetime-local" class="form-control" name="" value="${currentDateTime}" readonly>
                </div>
            </div>
        </div>
        <hr>
        <h5 class="mb-3">Product Inventory</h5>
        <div id="inventory_items" class="mb-3">
            <!-- Inventory items will be added here -->
        </div>
        <button type="button" class="btn btn-sm btn-success mb-3" onclick="addNewInventoryItem()">
            <i class="fas fa-plus"></i> Add New Product
        </button>
    </form>`;
     
    const select = editModalContent.querySelector('#violationsSelect');
    if (select) {
        select.addEventListener('change', function() {
            const count = this.selectedOptions.length;
            const numViolationsInput = document.getElementById('numViolationsInput');
            if (numViolationsInput) {
                numViolationsInput.value = count;
            }
        });
    }
    
    // Display the modal
    editModal.style.display = 'block';
    console.log('Edit modal should be displayed now');

    // Fetch establishment data including inventory items
    fetch(`get_establishment.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            if(data && data.success) {
                // Clear previous inventory items
                const inventoryContainer = document.getElementById('inventory_items');
                inventoryContainer.innerHTML = '';
                
                // Add existing inventory items
                if(data.inventory && data.inventory.length > 0) {
                    data.inventory.forEach((item, index) => {
                        const itemDiv = document.createElement('div');
                        itemDiv.innerHTML = getInventoryItemHtml(item, index);
                        inventoryContainer.appendChild(itemDiv.firstElementChild);
                    });
                } else {
                    // Add an empty placeholder if no inventory exists
                    const itemDiv = document.createElement('div');
                    itemDiv.innerHTML = getInventoryItemHtml({}, 0);
                    inventoryContainer.appendChild(itemDiv.firstElementChild);
                }
            } else {
                // If no data returned, add an empty placeholder
                const inventoryContainer = document.getElementById('inventory_items');
                const itemDiv = document.createElement('div');
                itemDiv.innerHTML = getInventoryItemHtml({}, 0);
                inventoryContainer.appendChild(itemDiv.firstElementChild);
                
                throw new Error(data?.message || 'Invalid data received from server');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`Error loading establishment details: ${error.message}`);
        });
}
function getInventoryItemHtml(product = {}, index) {
    return `
    <div class="inventory-item border p-2 mb-3 rounded">
        <div class="row">
            <div class="col-md-6">
                <input type="hidden" name="inventory[${index}][id]" value="${product.id || ''}">
                <label>Product Name</label>
                <input type="text" class="form-control mb-2" name="inventory[${index}][product_name]" 
                    placeholder="Product Name" value="${product.product_name || ''}">
            </div>
            <div class="col-md-3">
                <label>Price</label>
                <input type="number" step="0.01" class="form-control mb-2" name="inventory[${index}][price]" 
                    placeholder="Price" value="${product.price || '0.00'}">
            </div>
            <div class="col-md-3">
                <label>Quantity</label>
                <input type="number" class="form-control mb-2" name="inventory[${index}][quantity]" 
                    placeholder="Quantity" value="${product.quantity || '0'}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="checkbox" name="inventory[${index}][sealed]" 
                        ${product.sealed == 1 ? 'checked' : ''}>
                    <label class="form-check-label">Sealed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="inventory[${index}][withdrawn]" 
                        ${product.withdrawn == 1 ? 'checked' : ''}>
                    <label class="form-check-label">Withdrawn</label>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-danger" 
                    onclick="removeInventoryItem(this)">Remove Item</button>
            </div>
        </div>
    </div>`;
}

// Function to add a new inventory item to the form
function addNewInventoryItem() {
    const container = document.getElementById('inventory_items');
    if (!container) {
        console.error('Inventory container not found');
        return;
    }
    
    const index = document.querySelectorAll('.inventory-item').length;
    const itemDiv = document.createElement('div');
    itemDiv.innerHTML = getInventoryItemHtml({}, index);
    container.appendChild(itemDiv.firstElementChild);
}

function removeInventoryItem(button) {
    const itemDiv = button.closest('.inventory-item');
    if (itemDiv) {
        itemDiv.remove();
    }
}


// New function to update violation count when selection changes
function updateViolationCount() {
    const select = document.getElementById('violationsSelect');
    const numViolationsInput = document.getElementById('numViolationsInput');
    
    if (!select || !numViolationsInput) {
        console.error('Could not find select or input elements');
        return;
    }
    
    const selectedCount = select.selectedOptions.length;
    numViolationsInput.value = selectedCount;
    console.log('Updated violation count to:', selectedCount);
}

function formatDateForInput(displayDate) {
    console.log('Formatting date:', displayDate);
    
    if (!displayDate || displayDate.includes('Invalid date') || displayDate.includes('No date')) {
        return ''; // Return empty string if no valid date
    }
    
    try {
        // Parse date format like "Mar 31, 2025 01:13 PM"
        const months = {
            'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04', 'May': '05', 'Jun': '06',
            'Jul': '07', 'Aug': '08', 'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
        };
        
        // Split the date string into components
        const parts = displayDate.match(/(\w+)\s+(\d+),\s+(\d+)\s+(\d+):(\d+)\s+(\w+)/);
        if (parts) {
            const month = months[parts[1]];
            const day = parts[2].padStart(2, '0');
            const year = parts[3];
            let hour = parseInt(parts[4]);
            const minute = parts[5];
            const ampm = parts[6].toUpperCase();
            
            // Convert 12-hour to 24-hour format
            if (ampm === 'PM' && hour < 12) hour += 12;
            if (ampm === 'AM' && hour === 12) hour = 0;
            
            // Format for datetime-local input
            return `${year}-${month}-${day}T${hour.toString().padStart(2, '0')}:${minute}`;
        }
        
        // If parsing fails, return empty string
        return '';
    } catch (e) {
        console.error('Date parsing error:', e);
        return '';
    }
}

// Close Modal Function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        console.log(`Closed modal: ${modalId}`);
    } else {
        console.error(`Modal not found: ${modalId}`);
    }
}

// Add click event to close modal when clicking outside of modal content
window.addEventListener('click', function(event) {
    const viewModal = document.getElementById('viewModal');
    const editModal = document.getElementById('editModal');
    
    if (viewModal && event.target == viewModal) {
        viewModal.style.display = 'none';
    }
    
    if (editModal && event.target == editModal) {
        editModal.style.display = 'none';
    }
});

function saveChanges() {
    console.log('Saving changes...');
    
    const editForm = document.getElementById('editForm');
    if (!editForm) {
        console.error('Edit form not found!');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Cannot find the form to save changes',
                icon: 'error'
            });
        } else {
            alert('Error: Cannot find the form to save changes');
        }
        return;
    }

    // Get form elements
    const violationsSelect = document.getElementById('violationsSelect');
    const numViolationsInput = document.getElementById('numViolationsInput');
    
    if (!violationsSelect || !numViolationsInput) {
        console.error('Form elements not found!');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Cannot find required form elements',
                icon: 'error'
            });
        } else {
            alert('Error: Cannot find required form elements');
        }
        return;
    }

    // Get current date and time in Manila timezone (UTC+8)
    const now = new Date();
    const manilaOffset = 8 * 60 * 60 * 1000; // 8 hours in milliseconds
    const manilaTime = new Date(now.getTime() + manilaOffset);
    
    const year = manilaTime.getUTCFullYear();
    const month = String(manilaTime.getUTCMonth() + 1).padStart(2, '0');
    const day = String(manilaTime.getUTCDate()).padStart(2, '0');
    const hours = String(manilaTime.getUTCHours()).padStart(2, '0');
    const minutes = String(manilaTime.getUTCMinutes()).padStart(2, '0');
    const seconds = String(manilaTime.getUTCSeconds()).padStart(2, '0');

    const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    
    // Format for display in the table
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    let displayHours = manilaTime.getUTCHours();
    const ampm = displayHours >= 12 ? 'PM' : 'AM';
    displayHours = displayHours % 12;
    displayHours = displayHours ? displayHours : 12; // Convert 0 to 12
    const displayMinutes = String(manilaTime.getUTCMinutes()).padStart(2, '0');
    
    const displayDateTime = `${monthNames[manilaTime.getUTCMonth()]} ${manilaTime.getUTCDate()}, ${year} ${displayHours}:${displayMinutes} ${ampm}`;

    // Show loading state with SweetAlert if available
    let loadingAlert;
    if (typeof Swal !== 'undefined') {
        loadingAlert = Swal.fire({
            title: 'Saving changes...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    // Collect inventory items
    const inventoryItems = [];
    const inventoryDivs = document.querySelectorAll('.inventory-item');
    
    inventoryDivs.forEach((div, index) => {
        const productName = div.querySelector('[name^="inventory"][name$="[product_name]"]')?.value || '';
        const price = div.querySelector('[name^="inventory"][name$="[price]"]')?.value || '0.00';
        const quantity = div.querySelector('[name^="inventory"][name$="[quantity]"]')?.value || '0';
        const sealed = div.querySelector('[name^="inventory"][name$="[sealed]"]')?.checked ? 1 : 0;
        const withdrawn = div.querySelector('[name^="inventory"][name$="[withdrawn]"]')?.checked ? 1 : 0;
        const id = div.querySelector('[name^="inventory"][name$="[id]"]')?.value || '';
        
        // Only add items that have a product name
        if (productName.trim() !== '') {
            inventoryItems.push({
                id: id,
                product_name: productName,
                price: price,
                quantity: quantity,
                sealed: sealed,
                withdrawn: withdrawn
            });
        }
    });

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
        date_updated: formattedDateTime,
        inventory: inventoryItems
    };

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
    .then(response => {
        clearTimeout(timeoutId);
        
        // First check if response is ok
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Server response:', text);
                throw new Error(`Server responded with status ${response.status}: ${response.statusText}`);
            });
        }
        
        // Try to parse as JSON
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', text);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Update failed without error message');
        }
        
        // Find and update the table row
        const row = document.querySelector(`tr[data-id="${formData.id}"]`);
        if (row) {
            // Update table cells with new data
            row.cells[0].innerText = capitalizeWords(formData.name);
            row.cells[1].innerText = capitalizeWords(formData.address);
            row.cells[2].innerText = capitalizeWords(formData.owner_rep);
            row.cells[3].innerText = formData.violations;
            
            // Update inventory products column (cell index 4)
            if (formData.inventory && formData.inventory.length > 0) {
                const productNames = formData.inventory.map(item => item.product_name);
                row.cells[4].innerHTML = productNames.join(', ') + 
                    ' <span class="badge bg-primary">' + productNames.length + '</span>';
            } else {
                row.cells[4].innerHTML = '<span class="text-muted">No inventory</span>';
            }
            
            row.cells[5].innerText = formData.num_violations;
            row.cells[7].innerText = displayDateTime; // The "Last Updated" cell
        }
        
        // Show success message with SweetAlert if available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Success!',
                text: 'Changes saved successfully',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10346C'
            }).then((result) => {
                if (result.isConfirmed) {
                    closeModal('editModal');
                }
            });
        } else {
            alert('Success: Changes saved successfully');
            closeModal('editModal');
        }
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

        // Show error message with SweetAlert if available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: `Failed to save changes: ${errorMessage}`,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10346C'
            });
        } else {
            alert(`Error: Failed to save changes: ${errorMessage}`);
        }
    });
}
function capitalizeWords(string) {
    return string.toLowerCase().split(' ').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
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

function addInventoryItem(item = null) {
    const container = document.getElementById('inventory_items');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'inventory-item mb-3 p-2 border rounded';
    
    // Create HTML for inventory item form
    itemDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label>Product Name</label>
                <input type="text" class="form-control" name="product_name[]" value="${item ? item.product_name : ''}">
            </div>
            <div class="col-md-3">
                <label>Quantity</label>
                <input type="number" class="form-control" name="quantity[]" value="${item ? item.quantity : '0'}">
            </div>
            <div class="col-md-3">
                <label>Price</label>
                <input type="number" step="0.01" class="form-control" name="price[]" value="${item ? item.price : '0.00'}">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeInventoryItem(this)">Remove</button>
    `;
    
    container.appendChild(itemDiv);
}

function removeInventoryItem(button) {
    const itemDiv = button.parentNode;
    itemDiv.parentNode.removeChild(itemDiv);
}
