 console.log('Script loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Add event listeners to all edit and view buttons
    const viewButtons = document.querySelectorAll('.btn-primary');
    const editButtons = document.querySelectorAll('.btn-secondary');
    
    console.log('View buttons found:', viewButtons.length);
    console.log('Edit buttons found:', editButtons.length);
});
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
}

   // Edit Modal Function
   function openEditModal(id) {
   console.log('Opening edit modal for ID:', id);
    
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) {
        console.error('Row not found for ID:', id);
        return;
    }

    const editModal = document.getElementById('editModal');
    if (!editModal) {
        console.error('Edit modal element not found');
        return;
    }
    
    const editModalContent = document.getElementById('editModalContent');
    if (!editModalContent) {
        console.error('Edit modal content element not found');
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
    
    const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    
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
        // Default to current time if date is invalid
        const now = new Date();
        return now.toISOString().slice(0, 16);
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
        
        // If parsing fails, use current time
        const now = new Date();
        return now.toISOString().slice(0, 16);
    } catch (e) {
        console.error('Date parsing error:', e);
        const now = new Date();
        return now.toISOString().slice(0, 16);
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
    
    // Format for display in the table (e.g., "Apr 04, 2025 12:30 PM")
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    let displayHours = manilaTime.getUTCHours();
    const ampm = displayHours >= 12 ? 'PM' : 'AM';
    displayHours = displayHours % 12;
    displayHours = displayHours ? displayHours : 12; // Convert 0 to 12
    const displayMinutes = String(manilaTime.getUTCMinutes()).padStart(2, '0');
    
    const displayDateTime = `${monthNames[manilaTime.getUTCMonth()]} ${manilaTime.getUTCDate()}, ${year} ${displayHours}:${displayMinutes} ${ampm}`;

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
        date_updated: formattedDateTime  // Make sure this matches your PHP field name
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
        
        // Find and update the table row instead of reloading the page
        const row = document.querySelector(`tr[data-id="${formData.id}"]`);
        if (row) {
            // Update table cells with new data
            row.cells[0].innerText = capitalizeWords(formData.name);
            row.cells[1].innerText = capitalizeWords(formData.address);
            row.cells[2].innerText = capitalizeWords(formData.owner_rep);
            row.cells[3].innerText = formData.violations;
            row.cells[5].innerText = formData.num_violations;
            row.cells[7].innerText = displayDateTime; // The "Last Updated" cell
            
            console.log('Updated row with new date_updated timestamp:', displayDateTime);
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Success!',
                text: 'Changes saved successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                closeModal('editModal');
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

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: `Failed to save changes: ${errorMessage}`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`Error: Failed to save changes: ${errorMessage}`);
        }
    });
}

// Helper function to capitalize first letter of each word
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
    // Add click handlers to sort headers
    document.querySelectorAll('.sort-header').forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const columnIndex = header.getAttribute('data-column');
            sortTable(columnIndex);
        });
    });

    function sortTable(columnIndex) {
    const table = document.getElementById('recordsTable');
    const headers = document.querySelectorAll('.sort-header');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Determine new sort direction
    const currentHeader = headers[columnIndex];
    const currentSort = currentHeader.getAttribute('data-sort') || 'none';
    const newSort = currentSort === 'asc' ? 'desc' : 'asc';

    // Reset all headers
    headers.forEach(header => {
        header.setAttribute('data-sort', 'none');
        const icon = header.querySelector('.sort-icon');
        icon.className = 'sort-icon fa-solid fa-sort';
        header.classList.remove('sort-asc', 'sort-desc');
    });

    // Update current header
    currentHeader.setAttribute('data-sort', newSort);
    const sortIcon = currentHeader.querySelector('.sort-icon');
    sortIcon.className = `sort-icon fa-solid ${newSort === 'asc' ? 'fa-sort-up' : 'fa-sort-down'}`;
    currentHeader.classList.add(`sort-${newSort}`);

    // Sort the rows array
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();

        // Try parsing as float to support number sorting
        const aNum = parseFloat(aText);
        const bNum = parseFloat(bText);
        const isNumeric = !isNaN(aNum) && !isNaN(bNum);

        if (isNumeric) {
            return newSort === 'asc' ? aNum - bNum : bNum - aNum;
        } else {
            return newSort === 'asc'
                ? aText.localeCompare(bText)
                : bText.localeCompare(aText);
        }
    });

    // Clear existing rows and append sorted ones
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}
