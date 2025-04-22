document.addEventListener('DOMContentLoaded', function() {
    console.log("Checking for success message...");
    console.log("URL params:", window.location.search);
    console.log("Session storage:", sessionStorage.getItem('successMessage'));
    
    // Initialize status radio buttons and fields
    const statusReceived = document.getElementById('statusReceived');
    const statusRefused = document.getElementById('statusRefused');
    const receivedByFields = document.getElementById('receivedByFields');
    const refusedByFields = document.getElementById('refusedByFields');

<<<<<<< Updated upstream
    // Instead of using a conditional check that depends on all elements existing:
if (statusReceived && statusRefused && receivedByFields && refusedByFields) {
    // event listeners...
}

// Use individual checks before adding each event listener:
if (statusReceived) {
    statusReceived.addEventListener('change', function() {
        if (this.checked && receivedByFields) {
            receivedByFields.style.display = 'block';
            if (refusedByFields) refusedByFields.style.display = 'none';
            
            if (document.getElementById('received_by')) {
                document.getElementById('received_by').setAttribute('required', 'required');
            }
            if (document.getElementById('position')) {
                document.getElementById('position').setAttribute('required', 'required');
            }
            if (document.getElementById('witnessed_by')) {
                document.getElementById('witnessed_by').removeAttribute('required');
            }
        }
    });
}

if (statusRefused) {
    statusRefused.addEventListener('change', function() {
        if (this.checked && refusedByFields) {
            if (receivedByFields) receivedByFields.style.display = 'none';
            refusedByFields.style.display = 'block';
            
            if (document.getElementById('witnessed_by')) {
                document.getElementById('witnessed_by').setAttribute('required', 'required');
            }
            if (document.getElementById('received_by')) {
                document.getElementById('received_by').removeAttribute('required');
            }
            if (document.getElementById('position')) {
                document.getElementById('position').removeAttribute('required');
            }
        }
    });
}
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const successData = JSON.parse(sessionStorage.getItem('successMessage'));
        if (successData) {
            Swal.fire({
                icon: 'success',
                title: successData.title || 'Success',
                text: successData.text || 'Operation completed successfully.',
                confirmButtonColor: '#10346C'
            });
            sessionStorage.removeItem('successMessage');
        }
=======
    if (statusReceived && statusRefused && receivedByFields && refusedByFields) {
        function updateFieldsVisibility() {
            if (statusReceived.checked) {
                receivedByFields.style.display = 'block';
                refusedByFields.style.display = 'none';
                document.getElementById('witnessed_by').removeAttribute('required');
                document.getElementById('issued_by').setAttribute('required', 'required');
                document.getElementById('position').setAttribute('required', 'required');
            } else if (statusRefused.checked) {
                receivedByFields.style.display = 'block';
                refusedByFields.style.display = 'block';
                document.getElementById('witnessed_by').setAttribute('required', 'required');
                document.getElementById('issued_by').removeAttribute('required');
                document.getElementById('position').removeAttribute('required');
            }
        }

        statusReceived.addEventListener('change', updateFieldsVisibility);
        statusRefused.addEventListener('change', updateFieldsVisibility);
        
        // Initialize visibility on page load
        updateFieldsVisibility();
    }

    // Initialize date/time picker for issued_datetime
    if (document.getElementById('issued_datetime')) {
        flatpickr("#issued_datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: new Date()
        });
>>>>>>> Stashed changes
    }

    // Proceed to violations button handler
    const proceedBtn = document.getElementById('proceedToViolationsBtn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function(e) {
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
                const formData = new FormData(formElement);
                const region = document.getElementById('region').value;
                const province = document.getElementById('province').value;
                const municipality = document.getElementById('municipality').value;
                const barangay = document.getElementById('barangay').value;
                const street = document.getElementById('street').value;
                const fullAddress = `${street}, ${barangay}, ${municipality}, ${province}, ${region}`;
                
                document.getElementById('hiddenEstablishment').value = formData.get('establishment');
                document.getElementById('hiddenOwnerRep').value = formData.get('owner_representative');
                document.getElementById('hiddenAddress').value = fullAddress;
                document.getElementById('hiddenNatureSelect').value = formData.get('nature_select');
                document.getElementById('hiddenNatureCustom').value = formData.get('nature_custom') || '';
                document.getElementById('hiddenProducts').value = formData.get('products');
                
                new bootstrap.Modal(document.getElementById('violationsModal')).show();
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

<<<<<<< Updated upstream
    //submitViolationsBtn click handler
const submitViolationsBtn = document.getElementById('submitViolationsBtn');
if (submitViolationsBtn) {
    submitViolationsBtn.addEventListener('click', function() {
        const violationsForm = document.getElementById('violationsForm');
        const formData = new FormData(violationsForm);
        const violations = Array.from(formData.getAll('violations[]'));
        
        if (violations.length === 0) {
            Swal.fire({
                title: 'No Violations Selected',
                text: 'Please select at least one violation before proceeding.',
                icon: 'warning',
                confirmButtonColor: '#10346C'
            });
            return;
        }
        
        // Store ALL form data, not just violations
        let formDataObj = {};
        for (const [key, value] of formData.entries()) {
            if (formDataObj[key]) {
                if (!Array.isArray(formDataObj[key])) {
                    formDataObj[key] = [formDataObj[key]];
                }
                formDataObj[key].push(value);
            } else {
                formDataObj[key] = value;
            }
        }
        
        sessionStorage.setItem('violationsFormData', JSON.stringify(formDataObj));
        
        bootstrap.Modal.getInstance(document.getElementById('violationsModal')).hide();
        
        // Always show inventory modal regardless of violation type
        setTimeout(() => {
            populateInventoryModal();
            new bootstrap.Modal(document.getElementById('inventoryModal')).show();
        }, 300);
    });
}

// Add skip inventory button handler
const skipInventoryBtn = document.getElementById('skipInventoryBtn');
if (skipInventoryBtn) {
    skipInventoryBtn.addEventListener('click', function() {
        // Hide the inventory modal
        const inventoryModal = document.getElementById('inventoryModal');
        const bsInventoryModal = bootstrap.Modal.getInstance(inventoryModal);
        
        if (bsInventoryModal) {
            // Use the Bootstrap modal hide method if we found an instance
            bsInventoryModal.hide();
=======
    // Violations form submission
    const submitViolationsBtn = document.getElementById('submitViolationsBtn');
    if (submitViolationsBtn) {
        submitViolationsBtn.addEventListener('click', function() {
            const violationsForm = document.getElementById('violationsForm');
            const formData = new FormData(violationsForm);
            const violations = Array.from(formData.getAll('violations[]'));
>>>>>>> Stashed changes
            
            if (violations.length === 0) {
                Swal.fire({
                    title: 'No Violations Selected',
                    text: 'Please select at least one violation before proceeding.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            sessionStorage.setItem('violationsFormData', JSON.stringify(Object.fromEntries(formData)));
            
            bootstrap.Modal.getInstance(document.getElementById('violationsModal')).hide();
            
            // Always show inventory modal regardless of violation type
            setTimeout(() => {
                populateInventoryModal();
                new bootstrap.Modal(document.getElementById('inventoryModal')).show();
            }, 300);
        });
    }

    // Add skip inventory button handler
    const skipInventoryBtn = document.getElementById('skipInventoryBtn');
    if (skipInventoryBtn) {
        skipInventoryBtn.addEventListener('click', function() {
            const inventoryModal = document.getElementById('inventoryModal');
            const bsInventoryModal = bootstrap.Modal.getInstance(inventoryModal);
            
            if (bsInventoryModal) {
                bsInventoryModal.hide();
                
                inventoryModal.addEventListener('hidden.bs.modal', function() {
                    new bootstrap.Modal(document.getElementById('receivedRefusedModal')).show();
                }, { once: true });
            } else {
                inventoryModal.classList.remove('show');
                document.body.classList.remove('modal-open');
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                
                setTimeout(() => {
                    new bootstrap.Modal(document.getElementById('receivedRefusedModal')).show();
                }, 300);
            }
        });
    }

    function populateInventoryModal() {
        const inventoryForm = document.getElementById('inventoryForm');
        inventoryForm.querySelector('input[name="establishment"]').value = 
            document.getElementById('hiddenEstablishment').value;
        inventoryForm.querySelector('input[name="owner_representative"]').value = 
            document.getElementById('hiddenOwnerRep').value;
        inventoryForm.querySelector('input[name="address"]').value = 
            document.getElementById('hiddenAddress').value;
        inventoryForm.querySelector('input[name="nature_select"]').value = 
            document.getElementById('hiddenNatureSelect').value;
        inventoryForm.querySelector('input[name="nature_custom"]').value = 
            document.getElementById('hiddenNatureCustom').value;
        inventoryForm.querySelector('input[name="products"]').value = 
            document.getElementById('hiddenProducts').value;
    }

<<<<<<< Updated upstream
   // Status Form Submission
const submitStatusBtn = document.getElementById('submitStatusBtn');
if (submitStatusBtn) {
    submitStatusBtn.addEventListener('click', function() {
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
            return; // Stop the function here
        }

        // Validate required fields based on selected status
        if (statusData.get('notice_status') === 'Received') {
            const issuedBy = document.getElementById('received_by').value.trim();
            const position = document.getElementById('position').value.trim();
            
            if (!issuedBy) {
                Swal.fire({
                    title: 'Issuer Required',
                    text: 'Please enter the name of the issuer.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return; // Stop the function here
            }
            
            if (!position) {
                Swal.fire({
                    title: 'Position Required',
                    text: 'Please enter the position of the issuer.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return; // Stop the function here
            }
        } else if (statusData.get('notice_status') === 'Refused') {
            const witnessedBy = document.getElementById('witnessed_by').value.trim();
            
            if (!witnessedBy) {
                Swal.fire({
                    title: 'Witness Required',
                    text: 'Please enter the name of the witness.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return; // Stop the function here
            }
        }
        
        // Get all the previously collected data from violationsData
        const violationsData = sessionStorage.getItem('violationsFormData') ? 
            JSON.parse(sessionStorage.getItem('violationsFormData')) : {};
        
        // Create a summary of what's being submitted for confirmation
        const establishment = document.getElementById('hiddenEstablishment').value;
        
        // Show confirmation before submission
        Swal.fire({
            title: 'Confirm Submission',
            html: `
                <div class="text-start">
                    <p><strong>Establishment:</strong> ${establishment}</p>
                    <p><strong>Notice Status:</strong> ${statusData.get('notice_status')}</p>
                    <p><strong>Date Issued:</strong> ${statusData.get('issued_datetime')}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10346C',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Submit Notice',
            cancelButtonText: 'Review Information'
        }).then((result) => {
            if (result.isConfirmed) {
                // Store success message in session storage BEFORE submission
                sessionStorage.setItem('successMessage', JSON.stringify({
                    title: 'Notice of Violation Saved',
                    text: `NOV for ${establishment} has been successfully recorded.`
                }));
                
                // Create a form that includes ALL necessary data
                const finalForm = document.createElement('form');
                finalForm.method = 'POST';
                finalForm.action = "establishments.php?success=1";
                finalForm.style.display = 'none';
                document.body.appendChild(finalForm);
                
                // Add violations data
                for (const key in violationsData) {
                    if (Array.isArray(violationsData[key])) {
                        for (const value of violationsData[key]) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            finalForm.appendChild(input);
                        }
                    } else {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = violationsData[key];
                        finalForm.appendChild(input);
                    }
                }
                
                // Add status data
                const addHiddenField = (name, value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    finalForm.appendChild(input);
                };
                
                addHiddenField('notice_status', statusData.get('notice_status'));
                addHiddenField('issued_datetime', statusData.get('issued_datetime'));
                
                // Add the correct fields based on status
                if (statusData.get('notice_status') === 'Received') {
                    const issuedBy = document.getElementById('received_by').value;
                    addHiddenField('issued_by', issuedBy);
                    addHiddenField('position', statusData.get('position'));
                    addHiddenField('witnessed_by', ''); // Add empty value to avoid undefined
                } else {
                    const witnessedBy = document.getElementById('witnessed_by').value;
                    addHiddenField('witnessed_by', witnessedBy);
                    addHiddenField('issued_by', ''); // Add empty value to avoid undefined
                    addHiddenField('position', ''); // Add empty value to avoid undefined
                }
                
                // Show loading indicator
                Swal.fire({
                    title: 'Submitting...',
                    text: 'Please wait while your notice is being processed.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                setTimeout(() => {
                    finalForm.submit();
                }, 500); // Small delay to ensure loading dialog is displayed
            }
        });
    });
}
=======
    // Status form submission
        // Notice Status Form Handler
        const submitStatusBtn = document.getElementById('submitStatusBtn');
        if (submitStatusBtn) {
            submitStatusBtn.addEventListener('click', function() {
                // Get form and form data
                const statusForm = document.getElementById('noticeStatusForm');
                const formData = new FormData(statusForm);
                const status = formData.get('notice_status');
                
                // Basic validation
                if (!validateStatusForm(status, formData)) {
                    return;
                }
                
                // Add violations data from session storage
                addViolationsDataToForm(formData);
                
                // Add form identifier
                formData.append('submit_issuer', '1');
                
                // Submit form data
                submitNoticeStatus(formData);
            });
        }
        
        // Validation function
        function validateStatusForm(status, formData) {
            // Check basic required fields
            if (!status || !formData.get('issued_datetime')) {
                showError('Missing Information', 'Please fill in all required fields.');
                return false;
            }
            
            // Status-specific validation
            if (status === 'Received') {
                if (!formData.get('issued_by') || !formData.get('position')) {
                    showError('Missing Information', 'Please fill in issuer name and position.');
                    return false;
                }
            } else if (status === 'Refused') {
                if (!formData.get('witnessed_by')) {
                    showError('Missing Information', 'Please enter witness name.');
                    return false;
                }
            }
            
            return true;
        }
        
        // Add violations data from session storage
        function addViolationsDataToForm(formData) {
            const violationsData = sessionStorage.getItem('violationsFormData') ? 
                JSON.parse(sessionStorage.getItem('violationsFormData')) : {};
                
            Object.entries(violationsData).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach(val => formData.append(`${key}[]`, val));
                } else {
                    formData.append(key, value);
                }
            });
        }
        
        // Submit form data using fetch
        // Update this function in your existing script
function submitNoticeStatus(formData) {
    // Add submission identifier to match the PHP script
    formData.append('submit_notice', '1');  // This matches what your PHP expects
    
    fetch('establishments.php', {  // Ensure this matches your actual PHP file name
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        
        // Check if the response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.indexOf('application/json') !== -1) {
            return response.json();
        } else {
            // If not JSON, get text and throw error with response text preview
            return response.text().then(text => {
                console.error("Non-JSON response:", text.substring(0, 500)); // Log first 500 chars
                throw new Error('Received non-JSON response from server');
            });
        }
    })
    .then(data => {
        if (data.success) {
            handleSuccess(data.message || 'Notice status has been saved successfully.');
        } else {
            throw new Error(data.message || 'Failed to save notice status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', error.message || 'Failed to save notice status. Please try again.');
    });
}

// Update the success handler to use message from response
function handleSuccess(message) {
    // Close modal if open
    const modal = bootstrap.Modal.getInstance(document.getElementById('receivedRefusedModal'));
    if (modal) modal.hide();
    
    // Show success message
    Swal.fire({
        title: 'Success!',
        text: 'Notice Status had been saved successfully.',
        icon: 'success',
        confirmButtonColor: '#10346C'
    }).then(() => {
        // Clear session storage
        sessionStorage.removeItem('violationsFormData');
        // Redirect to establishments list
        window.location.href = 'establishments.php';
    });
}
        
        // Helper function for error messages
        function showError(title, message) {
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                confirmButtonColor: '#10346C'
            });
        }
    });
>>>>>>> Stashed changes

    // Save Inventory Button
    const saveInventoryBtn = document.getElementById('saveInventoryBtn');
    if (saveInventoryBtn) {
        saveInventoryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!validateInventoryForm()) return;
            
            const saveInventoryBtn = this;
            const inventoryForm = document.getElementById('inventoryForm');
            const formData = new FormData(inventoryForm);
            
            saveInventoryBtn.disabled = true;
            saveInventoryBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

            fetch('save_inventory.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        timer: 1000,
                        showConfirmButton: false,
                        willClose: () => {
                            const inventoryModal = bootstrap.Modal.getInstance(document.getElementById('inventoryModal'));
                            inventoryModal.hide();
                            document.getElementById('inventoryModal').addEventListener('hidden.bs.modal', () => {
                                new bootstrap.Modal(document.getElementById('receivedRefusedModal')).show();
                                resetButton(saveInventoryBtn);
                            }, { once: true });
                        }
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
                    text: error.message,
                    confirmButtonColor: '#10346C'
                }).then(() => resetButton(saveInventoryBtn));
            });

            function resetButton(button) {
                button.disabled = false;
                button.innerHTML = 'Save Products';
            }
        });
    }

    function validateInventoryForm() {
        const productItems = document.querySelectorAll('.product-item');
        if (productItems.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please add at least one product'
            });
            return false;
        }
        
        let isValid = true;
        productItems.forEach(item => {
            const nameInput = item.querySelector('input[name^="products["][name$="[name]"]');
            if (!nameInput.value.trim()) {
                isValid = false;
                nameInput.classList.add('is-invalid');
            } else {
                nameInput.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please fill in all required product fields'
            });
        }
        
        return isValid;
    }

    // Add Product Button
    const addProductBtn = document.getElementById('addProductBtn');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function() {
            const productsContainer = document.getElementById('productsContainer');
            const productCount = productsContainer.querySelectorAll('.product-item').length;
            
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
                    
                    <button type="button" class="btn btn-danger btn-sm remove-product">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>`;
            
            productsContainer.insertAdjacentHTML('beforeend', newProductHtml);
            
            const lastRemoveButton = productsContainer.querySelector('.product-item:last-child .remove-product');
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
            bootstrap.Modal.getInstance(document.getElementById('inventoryModal')).hide();
            setTimeout(() => {
                new bootstrap.Modal(document.getElementById('violationsModal')).show();
            }, 300);
        });
    }
    
    // Nature of business custom field toggle
    const natureSelect = document.getElementById('natureSelect');
    const natureCustom = document.getElementById('natureCustom');
    
    if (natureSelect && natureCustom) {
        natureSelect.addEventListener('change', function() {
            natureCustom.style.display = this.value === 'Others' ? 'block' : 'none';
            natureCustom.required = this.value === 'Others';
        });
<<<<<<< Updated upstream
    }

    //error catch
    window.addEventListener('error', function(e) {
        console.error('JavaScript error:', e.message, 'at', e.filename, 'line', e.lineno);
    });
    
});
=======
    }
>>>>>>> Stashed changes
