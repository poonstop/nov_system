// form-handler.js
    document.addEventListener('DOMContentLoaded', function() {
    // Initialize status radio buttons and fields
    const statusReceived = document.getElementById('statusReceived');
    const statusRefused = document.getElementById('statusRefused');
    const receivedByFields = document.getElementById('receivedByFields');
    const refusedByFields = document.getElementById('refusedByFields');

    if (statusReceived && statusRefused && receivedByFields && refusedByFields) {
        // Show/hide fields based on selection
        statusReceived.addEventListener('change', function() {
            if (this.checked) {
                receivedByFields.style.display = 'block';
                refusedByFields.style.display = 'none';
                
                // Set required attributes
                document.getElementById('received_by').setAttribute('required', 'required');
                document.getElementById('position').setAttribute('required', 'required');
                document.getElementById('witnessed_by').removeAttribute('required');
                
                // Clear refused fields
                document.getElementById('witnessed_by').value = '';
            }
        });
        
        statusRefused.addEventListener('change', function() {
            if (this.checked) {
                receivedByFields.style.display = 'none';
                refusedByFields.style.display = 'block';
                
                // Set required attributes
                document.getElementById('received_by').removeAttribute('required');
                document.getElementById('position').removeAttribute('required');
                document.getElementById('witnessed_by').setAttribute('required', 'required');
                
                // Clear received fields
                document.getElementById('received_by').value = '';
                document.getElementById('position').value = '';
            }
        });
    }

    // Proceed to violations button handler
    const proceedBtn = document.getElementById('proceedToViolationsBtn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function(e) {
            // Validate required fields before proceeding
            const formElement = document.getElementById('novForm');
            const requiredFields = formElement.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (isValid) {
                // Capture form data
                const formData = new FormData(formElement);
                
                // Populate the violations modal with form data
                document.getElementById('hiddenEstablishment').value = formData.get('establishment');
                document.getElementById('hiddenOwnerRep').value = formData.get('owner_representative');
                document.getElementById('hiddenAddress').value = formData.get('address'); 
                document.getElementById('hiddenNatureSelect').value = formData.get('nature_select');
                document.getElementById('hiddenNatureCustom').value = formData.get('nature_custom') || '';
                document.getElementById('hiddenProducts').value = formData.get('products');
                
                // Show the modal
                const violationsModal = new bootstrap.Modal(document.getElementById('violationsModal'));
                violationsModal.show();
            } else {
                Swal.fire({
                    title: 'Missing Information',
                    text: 'Please fill in all required fields before proceeding.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
            }
        });
    }

    // Violations form submission
    const submitViolationsBtn = document.getElementById('submitViolationsBtn');
    if (submitViolationsBtn) {
        submitViolationsBtn.addEventListener('click', function() {
            // Get form data from the violations modal
            const violationsForm = document.getElementById('violationsForm');
            const formData = new FormData(violationsForm);
            
            // Check if at least one violation is checked
            const violations = formData.getAll('violations[]');
            if (violations.length === 0) {
                Swal.fire({
                    title: 'No Violations Selected',
                    text: 'Please select at least one violation before proceeding.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            // Store the violations form data in session storage
            sessionStorage.setItem('violationsFormData', JSON.stringify(Object.fromEntries(formData)));
            
            // Hide violations modal and show received/refused modal
            const violationsModal = bootstrap.Modal.getInstance(document.getElementById('violationsModal'));
            violationsModal.hide();
            
            // Check for product violations
            const productRelatedViolations = [
                'No PS/ICC Mark',
                'Invalid/suspended or cancelled BPS license or permit',
                'No Manufacturer\'s Name',
                'No Manufacturer\'s Address',
                'No Date Manufactured',
                'No Country of Origin',
                'No/Inappropriate Price Tag',
                'Price grossly in excess of its true worth',
                'Price is beyond the Price Ceiling'
            ];
            
            const hasProductViolations = Array.isArray(violations) ?
                violations.some(v => productRelatedViolations.includes(v)) :
                productRelatedViolations.includes(violations);
                
            if (hasProductViolations) {
                // Show inventory modal if there are product violations
                const inventoryModal = new bootstrap.Modal(document.getElementById('inventoryModal'));
                inventoryModal.show();
            } else {
                // Show received/refused modal next
                const receivedRefusedModal = new bootstrap.Modal(document.getElementById('receivedRefusedModal'));
                receivedRefusedModal.show();
            }
        });
    }

    // Status form submission
    const submitStatusBtn = document.getElementById('submitStatusBtn');
    if (submitStatusBtn) {
        submitStatusBtn.addEventListener('click', function() {
            // Get form data
            const statusForm = document.getElementById('noticeStatusForm');
            const statusData = new FormData(statusForm);
            
            // Validate status selection
            if (!statusData.get('notice_status')) {
                Swal.fire({
                    title: 'Status Required',
                    text: 'Please select whether the notice was received or refused.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            // Additional validation based on selected status
            if (statusData.get('notice_status') === 'Received') {
                if (!statusData.get('received_by').trim()) {
                    Swal.fire({
                        title: 'Receiver Name Required',
                        text: 'Please enter the name of the person who received the notice.',
                        icon: 'warning',
                        confirmButtonColor: '#10346C'
                    });
                    return;
                }
                
                if (!statusData.get('position').trim()) {
                    Swal.fire({
                        title: 'Position Required',
                        text: 'Please enter the position of the person who received the notice.',
                        icon: 'warning',
                        confirmButtonColor: '#10346C'
                    });
                    return;
                }
            } else if (statusData.get('notice_status') === 'Refused') {
                if (!statusData.get('witnessed_by').trim()) {
                    Swal.fire({
                        title: 'Witness Name Required',
                        text: 'Please enter the name of the person who witnessed the refusal.',
                        icon: 'warning',
                        confirmButtonColor: '#10346C'
                    });
                    return;
                }
            }
            
            // Store status data in session storage
            const statusFormData = Object.fromEntries(statusData);
            sessionStorage.setItem('statusFormData', JSON.stringify(statusFormData));
            
            // Close status modal
            const statusModal = bootstrap.Modal.getInstance(document.getElementById('receivedRefusedModal'));
            statusModal.hide();
            
            // Proceed to issuer modal
            window.location.href = 'establishments.php?show_issuer_modal=1';
        });
    }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const inventoryForm = document.getElementById('inventoryForm');
        const saveInventoryBtn = document.getElementById('saveInventoryBtn');
        
        if (saveInventoryBtn && inventoryForm) {
            // Track submission state to prevent duplicate submissions
            let isSubmitting = false;
            
            saveInventoryBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Prevent multiple submissions
                if (isSubmitting) return;
                isSubmitting = true;
                
                // Disable button during submission
                saveInventoryBtn.disabled = true;
                saveInventoryBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                
                // Validate the form
                if (!validateInventoryForm()) {
                    isSubmitting = false;
                    saveInventoryBtn.disabled = false;
                    saveInventoryBtn.innerHTML = 'Save Products';
                    return;
                }
                
                // Collect all form data
                const formData = new FormData(inventoryForm);
                
                // Add CSRF token if available
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (csrfToken) {
                    formData.append('csrf_token', csrfToken);
                }
                
                // Add timestamp to prevent caching issues
                formData.append('timestamp', Date.now());
                
                // Send AJAX request
                fetch('save_inventory.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Close inventory modal
                            const inventoryModal = bootstrap.Modal.getInstance(document.getElementById('inventoryModal'));
                            if (inventoryModal) {
                                inventoryModal.hide();
                            }
                            
                            // Show the status modal
                            const statusModal = new bootstrap.Modal(document.getElementById('receivedRefusedModal'));
                            statusModal.show();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to save inventory products');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'An error occurred while saving the inventory',
                        timer: 3000
                    });
                })
                .finally(() => {
                    isSubmitting = false;
                    saveInventoryBtn.disabled = false;
                    saveInventoryBtn.innerHTML = 'Save Products';
                });
            });
        }
        
        // Inventory form validation
        function validateInventoryForm() {
            const productItems = document.querySelectorAll('.product-item');
            let isValid = true;
            
            // Check at least one product exists
            if (productItems.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please add at least one product'
                });
                return false;
            }
            
            // Validate each product
            productItems.forEach((item, index) => {
                const nameInput = item.querySelector('input[name^="products["][name$="[name]"]');
                
                if (!nameInput || !nameInput.value.trim()) {
                    isValid = false;
                    nameInput.classList.add('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `Please enter a name for product ${index + 1}`
                    });
                } else {
                    nameInput.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }
    });


          // Add Product Button
    const addProductBtn = document.getElementById('addProductBtn');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function() {
            const productsContainer = document.getElementById('productsContainer');
            const productCount = productsContainer.querySelectorAll('.product-item').length;
            
            // Create new product item template
            const newProductHtml = `
                <div class="product-item border p-3 mb-3 rounded">
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <label for="product_name">Product:</label>
                            <input type="text" class="form-control" name="products[${productCount}][name]" required>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex mt-4">
                                <div class="form-check me-4">
                                    <input class="form-check-input" type="checkbox" name="products[${productCount}][sealed]" value="1">
                                    <label class="form-check-label">Sealed</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="products[${productCount}][withdrawn]" value="1">
                                    <label class="form-check-label">Withdrawn</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="brand_description">Brand Description:</label>
                        <textarea class="form-control" name="products[${productCount}][description]" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label for="price">Price:</label>
                            <input type="number" class="form-control" name="products[${productCount}][price]" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label for="pieces">No. of Pieces:</label>
                            <input type="number" class="form-control" name="products[${productCount}][pieces]">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex mt-4">
                                <div class="form-check me-4">
                                    <input class="form-check-input" type="checkbox" name="products[${productCount}][dao_violation]" value="1">
                                    <label class="form-check-label">Violation of DAO</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="products[${productCount}][other_violation]" value="1">
                                    <label class="form-check-label">Other Violation</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="remarks">Product Remarks:</label>
                        <input type="text" class="form-control" name="products[${productCount}][remarks]">
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-product">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;
            
            // Add the new product to the container
            productsContainer.insertAdjacentHTML('beforeend', newProductHtml);
            
            // Add event listener to the new remove button
            const removeButtons = document.querySelectorAll('.remove-product');
            const lastRemoveButton = removeButtons[removeButtons.length - 1];
            
            if (lastRemoveButton) {
                lastRemoveButton.addEventListener('click', function() {
                    this.closest('.product-item').remove();
                });
            }
        });
    }
    
    // Back from inventory button
    const backFromInventoryBtn = document.getElementById('backFromInventoryBtn');
    if (backFromInventoryBtn) {
        backFromInventoryBtn.addEventListener('click', function() {
            // Close inventory modal and show violations modal
            const inventoryModal = bootstrap.Modal.getInstance(document.getElementById('inventoryModal'));
            if (inventoryModal) {
                inventoryModal.hide();
            }
            
            // Show the violations modal
            const violationsModal = new bootstrap.Modal(document.getElementById('violationsModal'));
            violationsModal.show();
        });
    }
    
    // Restore form data on page load from session backup
    const backupData = sessionStorage.getItem('novFormBackup');
    if (backupData && formElement) {
        try {
            const parsedData = JSON.parse(backupData);
            
            // Populate regular inputs
            for (const [key, value] of Object.entries(parsedData)) {
                const field = formElement.elements[key];
                if (field && field.type !== 'checkbox' && field.type !== 'radio') {
                    field.value = value;
                } else if (field && field.type === 'checkbox') {
                    field.checked = parsedData[key] === 'on';
                }
            }
        } catch (error) {
            console.error('Error restoring form data:', error);
        }
    }

    // Initialize datetime picker
    if (document.getElementById('issued_datetime')) {
        flatpickr("#issued_datetime", {
            enableTime: true,
            dateFormat: "Y-m-d h:i K", 
            time_24hr: true,
            maxDate: new Date(),
            defaultDate: new Date(),
            minuteIncrement: 1,
            disableMobile: true,
            allowInput: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates[0] > new Date()) {
                    instance.setDate(new Date());
                    Swal.fire({
                        title: 'Invalid Date',
                        text: 'Future dates are not allowed. Date has been reset to current date and time.',
                        icon: 'warning',
                        confirmButtonColor: '#10346C'
                    });
                }
            }
        });
    }

// Helper function to get current datetime
function getCurrentDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day} ${hours}:${minutes}`;
}

// Populate inventory modal with establishment data
document.addEventListener('DOMContentLoaded', function() {
    // Function to handle showing the inventory modal
    function showInventoryModal() {
        // Get establishment data from the violations form
        const establishment = document.getElementById('hiddenEstablishment').value;
        const ownerRep = document.getElementById('hiddenOwnerRep').value;
        const address = document.getElementById('hiddenAddress').value;
        const natureSelect = document.getElementById('hiddenNatureSelect').value;
        const natureCustom = document.getElementById('hiddenNatureCustom').value;
        const products = document.getElementById('hiddenProducts').value;
        
        // Populate hidden fields in inventory form
        const inventoryForm = document.getElementById('inventoryForm');
        inventoryForm.querySelector('input[name="establishment"]').value = establishment;
        inventoryForm.querySelector('input[name="owner_representative"]').value = ownerRep;
        inventoryForm.querySelector('input[name="address"]').value = address;
        inventoryForm.querySelector('input[name="nature_select"]').value = natureSelect;
        inventoryForm.querySelector('input[name="nature_custom"]').value = natureCustom;
        inventoryForm.querySelector('input[name="products"]').value = products;
        
        // Show the inventory modal
        const inventoryModal = new bootstrap.Modal(document.getElementById('inventoryModal'));
        inventoryModal.show();
    }
    
    // If we're coming from the violations modal
    const submitViolationsBtn = document.getElementById('submitViolationsBtn');
    if (submitViolationsBtn) {
        submitViolationsBtn.addEventListener('click', function() {
            // Hide violations modal
            const violationsModal = bootstrap.Modal.getInstance(document.getElementById('violationsModal'));
            if (violationsModal) {
                violationsModal.hide();
            }
            
            // Populate and show inventory modal
            showInventoryModal();
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Main form processing
    const proceedToViolationsBtn = document.getElementById('proceedToViolationsBtn');
    const novForm = document.getElementById('novForm');
    
    if (proceedToViolationsBtn && novForm) {
        proceedToViolationsBtn.addEventListener('click', function() {
            // Basic form validation
            if (!novForm.checkValidity()) {
                novForm.reportValidity();
                return;
            }
            
            // Combine address fields
            const region = document.getElementById('region').value;
            const province = document.getElementById('province').value;
            const municipality = document.getElementById('municipality').value;
            const barangay = document.getElementById('barangay').value;
            const street = document.getElementById('street').value;
            
            const fullAddress = `${street}, ${barangay}, ${municipality}, ${province}, ${region}`;
            
            // Get form values
            const establishment = document.getElementById('establishment').value;
            const ownerRep = document.getElementById('owner_representative').value;
            const natureSelect = document.getElementById('natureSelect').value;
            const natureCustom = document.getElementById('natureCustom').value;
            const products = document.querySelector('input[name="products"]').value;
            
            // Populate hidden fields in the violations modal
            document.getElementById('hiddenEstablishment').value = establishment;
            document.getElementById('hiddenOwnerRep').value = ownerRep;
            document.getElementById('hiddenAddress').value = fullAddress;
            document.getElementById('hiddenNatureSelect').value = natureSelect;
            document.getElementById('hiddenNatureCustom').value = natureCustom;
            document.getElementById('hiddenProducts').value = products;
            
            // Show violations modal
            const violationsModal = new bootstrap.Modal(document.getElementById('violationsModal'));
            violationsModal.show();
        });
    }
    
    // Nature of business custom field toggle
    const natureSelect = document.getElementById('natureSelect');
    const natureCustom = document.getElementById('natureCustom');
    
    if (natureSelect && natureCustom) {
        natureSelect.addEventListener('change', function() {
            if (this.value === 'Others') {
                natureCustom.style.display = 'block';
                natureCustom.required = true;
            } else {
                natureCustom.style.display = 'none';
                natureCustom.required = false;
            }
        });
    }
});