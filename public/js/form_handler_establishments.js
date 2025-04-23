document.addEventListener('DOMContentLoaded', function() {
    console.log("Checking for success message...");
    console.log("URL params:", window.location.search);
    console.log("Session storage:", sessionStorage.getItem('successMessage'));

    // Initialize status radio buttons and fields
    const statusReceived = document.getElementById('statusReceived');
    const statusRefused = document.getElementById('statusRefused');
    const receivedByFields = document.getElementById('receivedByFields');
    const refusedByFields = document.getElementById('refusedByFields');

    // Status radio button handlers
    function handleStatusChange() {
        if (statusReceived && statusReceived.checked && receivedByFields) {
            receivedByFields.style.display = 'block';
            if (refusedByFields) refusedByFields.style.display = 'none';
            
            const receivedBy = document.getElementById('received_by');
            const position = document.getElementById('position');
            const witnessedBy = document.getElementById('witnessed_by');
            
            if (receivedBy) receivedBy.setAttribute('required', 'required');
            if (position) position.setAttribute('required', 'required');
            if (witnessedBy) witnessedBy.removeAttribute('required');
        }
        else if (statusRefused && statusRefused.checked && refusedByFields) {
            if (receivedByFields) receivedByFields.style.display = 'none';
            refusedByFields.style.display = 'block';
            
            const witnessedBy = document.getElementById('witnessed_by');
            const receivedBy = document.getElementById('received_by');
            const position = document.getElementById('position');
            
            if (witnessedBy) witnessedBy.setAttribute('required', 'required');
            if (receivedBy) receivedBy.removeAttribute('required');
            if (position) position.removeAttribute('required');
        }
    }

    if (statusReceived) statusReceived.addEventListener('change', handleStatusChange);
    if (statusRefused) statusRefused.addEventListener('change', handleStatusChange);

    // Show success message if present
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
    }

    // Proceed to violations button handler
    const proceedBtn = document.getElementById('proceedToViolationsBtn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function(e) {
            const formElement = document.getElementById('novForm');
            if (!formElement) return;
            
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
                const region = document.getElementById('region')?.value || '';
                const province = document.getElementById('province')?.value || '';
                const municipality = document.getElementById('municipality')?.value || '';
                const barangay = document.getElementById('barangay')?.value || '';
                const street = document.getElementById('street')?.value || '';
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

    // Submit violations button handler
    const submitViolationsBtn = document.getElementById('submitViolationsBtn');
    if (submitViolationsBtn) {
        submitViolationsBtn.addEventListener('click', function() {
            const violationsForm = document.getElementById('violationsForm');
            if (!violationsForm) return;
            
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
            
            // Store ALL form data
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
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('violationsModal'));
            if (modal) modal.hide();
            
            // Show inventory modal
            setTimeout(() => {
                populateInventoryModal();
                new bootstrap.Modal(document.getElementById('inventoryModal')).show();
            }, 300);
        });
    }

    // Skip inventory button handler
    const skipInventoryBtn = document.getElementById('skipInventoryBtn');
    if (skipInventoryBtn) {
        skipInventoryBtn.addEventListener('click', function() {
            const inventoryModal = document.getElementById('inventoryModal');
            const bsInventoryModal = bootstrap.Modal.getInstance(inventoryModal);
            
            const hideModal = () => {
                if (bsInventoryModal) {
                    bsInventoryModal.hide();
                } else {
                    inventoryModal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                }
            };
            
            hideModal();
            
            setTimeout(() => {
                new bootstrap.Modal(document.getElementById('receivedRefusedModal')).show();
            }, 300);
        });
    }

    function populateInventoryModal() {
        const inventoryForm = document.getElementById('inventoryForm');
        if (!inventoryForm) return;
        
        const setValue = (id, value) => {
            const el = inventoryForm.querySelector(`input[name="${id}"]`);
            if (el) el.value = value;
        };
        
        setValue('establishment', document.getElementById('hiddenEstablishment').value);
        setValue('owner_representative', document.getElementById('hiddenOwnerRep').value);
        setValue('address', document.getElementById('hiddenAddress').value);
        setValue('nature_select', document.getElementById('hiddenNatureSelect').value);
        setValue('nature_custom', document.getElementById('hiddenNatureCustom').value);
        setValue('products', document.getElementById('hiddenProducts').value);
    }

   // Status Form Submission - Replace the existing submitStatusBtn event listener
const submitStatusBtn = document.getElementById('submitStatusBtn');
if (submitStatusBtn) {
    submitStatusBtn.addEventListener('click', function() {
        const statusForm = document.getElementById('noticeStatusForm');
        if (!statusForm) return;
        
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

        // Validate required fields based on selected status
        if (statusData.get('notice_status') === 'Received') {
            const issuedBy = document.getElementById('received_by')?.value.trim();
            const position = document.getElementById('position')?.value.trim();
            
            if (!issuedBy) {
                Swal.fire({
                    title: 'Issuer Required',
                    text: 'Please enter the name of the issuer.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            if (!position) {
                Swal.fire({
                    title: 'Position Required',
                    text: 'Please enter the position of the issuer.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
        } else if (statusData.get('notice_status') === 'Refused') {
            const witnessedBy = document.getElementById('witnessed_by')?.value.trim();
            
            if (!witnessedBy) {
                Swal.fire({
                    title: 'Witness Required',
                    text: 'Please enter the name of the witness.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
        }
        
        // Get previously collected data
        const violationsData = sessionStorage.getItem('violationsFormData') ? 
            JSON.parse(sessionStorage.getItem('violationsFormData')) : {};
        
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
                sessionStorage.setItem('successMessage', JSON.stringify({
                    title: 'Notice of Violation Saved',
                    text: `NOV for ${establishment} has been successfully recorded.`
                }));
                
                // Create final form with all data
                const finalForm = document.createElement('form');
                finalForm.method = 'POST';
                finalForm.action = "establishments.php";  // Removed query param to avoid redirection issues
                finalForm.style.display = 'none';
                document.body.appendChild(finalForm);
                
                // Add violations data
                for (const key in violationsData) {
                    if (Array.isArray(violationsData[key])) {
                        for (const value of violationsData[key]) {
                            addHiddenField(key, value);
                        }
                    } else {
                        addHiddenField(key, violationsData[key]);
                    }
                }
                
                // Add inventory data from sessionStorage if exists
                const inventoryData = JSON.parse(sessionStorage.getItem('inventoryData') || '{}');
                if (inventoryData.products && inventoryData.products.length > 0) {
                    for (let i = 0; i < inventoryData.products.length; i++) {
                        const product = inventoryData.products[i];
                        for (const key in product) {
                            addHiddenField(`products[${i}][${key}]`, product[key]);
                        }
                    }
                }
                
                // Add status data
                addHiddenField('notice_status', statusData.get('notice_status'));
                addHiddenField('issued_datetime', statusData.get('issued_datetime'));
                
                if (statusData.get('notice_status') === 'Received') {
                    addHiddenField('issued_by', document.getElementById('received_by').value);
                    addHiddenField('position', document.getElementById('position').value);
                    addHiddenField('witnessed_by', '');
                } else {
                    addHiddenField('witnessed_by', document.getElementById('witnessed_by').value);
                    addHiddenField('issued_by', '');
                    addHiddenField('position', '');
                }
                
                // Add flag for server processing - critical for proper handling
                addHiddenField('submit_issuer', '1');
                
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
                
                // Submit with small delay to ensure UI updates
                setTimeout(() => {
                    finalForm.submit();
                }, 500);
            }
        });
        
        function addHiddenField(name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value || '';
            finalForm.appendChild(input);
        }
    });
}

// Save Inventory Button - Improved with better validation and data handling
const saveInventoryBtn = document.getElementById('saveInventoryBtn');
if (saveInventoryBtn) {
    saveInventoryBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!validateInventoryForm()) return;
        
        const inventoryForm = document.getElementById('inventoryForm');
        if (!inventoryForm) return;
        
        // Collect form data
        const formData = new FormData(inventoryForm);
        const products = [];
        
        // Process product entries
        const productItems = document.querySelectorAll('.product-item');
        productItems.forEach((item, index) => {
            const product = {
                name: formData.get(`products[${index}][name]`) || '',
                sealed: formData.has(`products[${index}][sealed]`) ? 1 : 0,
                withdrawn: formData.has(`products[${index}][withdrawn]`) ? 1 : 0,
                description: formData.get(`products[${index}][description]`) || '',
                price: formData.get(`products[${index}][price]`) || 0,
                pieces: formData.get(`products[${index}][pieces]`) || 0,
                dao_violation: formData.has(`products[${index}][dao_violation]`) ? 1 : 0,
                other_violation: formData.has(`products[${index}][other_violation]`) ? 1 : 0,
                remarks: formData.get(`products[${index}][remarks]`) || ''
            };
            products.push(product);
        });
        
        // Store in sessionStorage
        sessionStorage.setItem('inventoryData', JSON.stringify({
            establishment: formData.get('establishment'),
            products: products
        }));
        
        // Show success message and continue to next modal
        Swal.fire({
            icon: 'success',
            title: 'Products Saved',
            text: 'Inventory products have been saved successfully.',
            timer: 1500,
            showConfirmButton: false,
            willClose: () => {
                const inventoryModal = bootstrap.Modal.getInstance(document.getElementById('inventoryModal'));
                if (inventoryModal) {
                    inventoryModal.hide();
                    setTimeout(() => {
                        new bootstrap.Modal(document.getElementById('receivedRefusedModal')).show();
                    }, 300);
                }
            }
        });
    });
}

// Improved validation for inventory form
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
        if (nameInput && !nameInput.value.trim()) {
            isValid = false;
            nameInput.classList.add('is-invalid');
        } else if (nameInput) {
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
            if (!productsContainer) return;
            
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
            const inventoryModal = bootstrap.Modal.getInstance(document.getElementById('inventoryModal'));
            if (inventoryModal) inventoryModal.hide();
            
            setTimeout(() => {
                new bootstrap.Modal(document.getElementById('violationsModal')).show();
            }, 300);
        });
    }

    // Initialize datetime picker
    if (document.getElementById('issued_datetime')) {
        flatpickr("#issued_datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            defaultDate: new Date(),
            minuteIncrement: 1
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
        
        // Initialize on load
        natureCustom.style.display = natureSelect.value === 'Others' ? 'block' : 'none';
        natureCustom.required = natureSelect.value === 'Others';
    }

    // Error handling
    window.addEventListener('error', function(e) {
        console.error('JavaScript error:', e.message, 'at', e.filename, 'line', e.lineno);
    });
});

