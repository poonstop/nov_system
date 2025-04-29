document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr for datetime picker
    flatpickr('.datepicker', {
        enableTime: true,
        dateFormat: "Y-m-d H:i:S",
        defaultDate: new Date()
    });

    // Handle nature of business custom field toggle
    const natureSelect = document.getElementById('nature_select');
    const natureCustom = document.getElementById('nature_custom');
    
    if (natureSelect && natureCustom) {
        natureSelect.addEventListener('change', function() {
            if (this.value === 'Others') {
                natureCustom.style.display = 'block';
                natureCustom.setAttribute('required', 'required');
            } else {
                natureCustom.style.display = 'none';
                natureCustom.removeAttribute('required');
            }
        });
    }

    // Initialize modals
    const violationsModal = new bootstrap.Modal(document.getElementById('violationsModal'), {
        backdrop: 'static',
        keyboard: false
    });
    
    const inventoryModal = new bootstrap.Modal(document.getElementById('inventoryModal'), {
        backdrop: 'static',
        keyboard: false
    });
    
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'), {
        backdrop: 'static',
        keyboard: false
    });

    // Show modals based on URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('show_violations_modal') === '1') {
        violationsModal.show();
    }
    
    if (urlParams.get('show_inventory_modal') === '1') {
        inventoryModal.show();
    }
    
    if (urlParams.get('show_status_modal') === '1') {
        statusModal.show();
    }

    // Handle 'Back' buttons on modals
    document.querySelectorAll('.modal .btn-secondary').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            
            if (modal.id === 'violationsModal') {
                // Go back to main form
                window.location.href = 'establishments.php';
            } else if (modal.id === 'inventoryModal') {
                // Go back to violations modal
                inventoryModal.hide();
                setTimeout(() => {
                    violationsModal.show();
                }, 500);
            } else if (modal.id === 'statusModal') {
                // Go back to inventory modal
                statusModal.hide();
                setTimeout(() => {
                    inventoryModal.show();
                }, 500);
            }
        });
    });

    // Handle adding products in inventory modal
    let productCounter = 1;
    const addProductBtn = document.getElementById('addProductBtn');
    const productsContainer = document.getElementById('productsContainer');
    
    if (addProductBtn && productsContainer) {
        addProductBtn.addEventListener('click', function() {
            const productHTML = `
                <div class="product-item border p-3 mb-3 rounded">
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <label for="product_name_${productCounter}">Product:</label>
                            <input type="text" class="form-control" id="product_name_${productCounter}" name="products[${productCounter}][name]">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex mt-4">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" id="product_sealed_${productCounter}" name="products[${productCounter}][sealed]" value="1">
                                    <label class="form-check-label" for="product_sealed_${productCounter}">Sealed</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="product_withdrawn_${productCounter}" name="products[${productCounter}][withdrawn]" value="1">
                                    <label class="form-check-label" for="product_withdrawn_${productCounter}">Withdrawn</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="product_description_${productCounter}">Description:</label>
                        <textarea class="form-control" id="product_description_${productCounter}" name="products[${productCounter}][description]" rows="2"></textarea>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label for="product_price_${productCounter}">Price:</label>
                            <input type="number" class="form-control" id="product_price_${productCounter}" name="products[${productCounter}][price]" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label for="product_pieces_${productCounter}">No. of Pieces:</label>
                            <input type="number" class="form-control" id="product_pieces_${productCounter}" name="products[${productCounter}][pieces]">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex mt-4">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" id="product_dao_${productCounter}" name="products[${productCounter}][dao_violation]" value="1">
                                    <label class="form-check-label" for="product_dao_${productCounter}">DAO</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="product_other_${productCounter}" name="products[${productCounter}][other_violation]" value="1">
                                    <label class="form-check-label" for="product_other_${productCounter}">Other</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label for="product_remarks_${productCounter}">Remarks:</label>
                        <input type="text" class="form-control" id="product_remarks_${productCounter}" name="products[${productCounter}][remarks]">
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-product">Remove</button>
                    </div>
                </div>
            `;
            
            // Add the new product form to the container
            productsContainer.insertAdjacentHTML('beforeend', productHTML);
            
            // Increment counter for next product
            productCounter++;
            
            // Add event listener to the newly added remove button
            attachRemoveProductListeners();
        });
        
        // Function to attach remove event listeners
        function attachRemoveProductListeners() {
            document.querySelectorAll('.remove-product').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.product-item').remove();
                });
            });
        }
        
        // Initialize remove buttons for initial product
        attachRemoveProductListeners();
    }

    // Form validation
    const novForm = document.getElementById('novForm');
    if (novForm) {
        novForm.addEventListener('submit', function(event) {
            // Validate nature of business if "Others" is selected
            if (natureSelect && natureSelect.value === 'Others' && 
                (!natureCustom.value || natureCustom.value.trim() === '')) {
                event.preventDefault();
                alert('Please specify the nature of business.');
                natureCustom.focus();
            }
        });
    }

    // Handle success messages with SweetAlert2
    const successMessage = document.getElementById('success-message');
    if (successMessage && successMessage.textContent.trim() !== '') {
        try {
            const successData = JSON.parse(successMessage.textContent);
            Swal.fire({
                icon: 'success',
                title: successData.title || 'Success!',
                text: successData.text || 'Operation completed successfully.',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                // Optional: Redirect to a listing page or clear the form
                if (urlParams.get('success') === '1') {
                    // Clear any form data if needed
                }
            });
        } catch (e) {
            // Fallback if JSON parsing fails
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: successMessage.textContent,
                confirmButtonColor: '#3085d6'
            });
        }
    }

    const statusRadios = document.querySelectorAll('.status-radio');
    const receivedFields = document.querySelector('.received-only-fields');
    const refusedFields = document.querySelector('.refused-only-fields');
    const issuedByField = document.getElementById('issued_by');
    const positionField = document.getElementById('position');
    const witnessedByField = document.getElementById('witnessed_by');
    
    if (statusRadios.length && receivedFields && refusedFields) {
        statusRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Received') {
                    // Show fields for "Received" status
                    receivedFields.style.display = 'block';
                    refusedFields.style.display = 'none';
                    
                    // Make fields required accordingly
                    issuedByField.setAttribute('required', 'required');
                    positionField.setAttribute('required', 'required');
                    witnessedByField.removeAttribute('required');
                } else if (this.value === 'Refused') {
                    // Show fields for "Refused" status
                    receivedFields.style.display = 'none';
                    refusedFields.style.display = 'block';
                    
                    // Make fields required accordingly
                    issuedByField.removeAttribute('required');
                    positionField.removeAttribute('required');
                    witnessedByField.setAttribute('required', 'required');
                }
            });
        });
    }
    
    // Validate violations form submission
    const violationsForm = document.querySelector('#violationsModal form');
    if (violationsForm) {
        violationsForm.addEventListener('submit', function(event) {
            // Check if at least one violation checkbox is checked
            const violationChecks = this.querySelectorAll('input[name="violations[]"]:checked');
            if (violationChecks.length === 0) {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'No Violations Selected',
                    text: 'Please select at least one violation before proceeding.',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    }
    
    // Validate inventory form submission
    const inventoryForm = document.querySelector('#inventoryModal form');
    if (inventoryForm) {
        const saveInventoryBtn = inventoryForm.querySelector('button[name="save_inventory"]');
        if (saveInventoryBtn) {
            saveInventoryBtn.addEventListener('click', function(event) {
                // Check if at least one product has a name
                let hasValidProduct = false;
                const productInputs = inventoryForm.querySelectorAll('input[id^="product_name_"]');
                
                productInputs.forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        hasValidProduct = true;
                    }
                });
                
                if (!hasValidProduct) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Product Information',
                        text: 'Please add at least one product with a name before proceeding.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        }
    }
    
    // Update form validation for the status modal
    const finalSubmitBtn = document.querySelector('button[name="submit_status"]');
    if (finalSubmitBtn) {
        finalSubmitBtn.addEventListener('click', function(event) {
            // Validate required fields before submitting
            const statusReceived = document.getElementById('statusReceived');
            const statusRefused = document.getElementById('statusRefused');
            
            if (!statusReceived.checked && !statusRefused.checked) {
                event.preventDefault();
                alert('Please select a notice status.');
                return;
            }
            
            if (statusReceived.checked) {
                // Validate "Received" specific fields
                if (!issuedByField.value || issuedByField.value.trim() === '') {
                    event.preventDefault();
                    alert('Please enter who issued the notice.');
                    issuedByField.focus();
                    return;
                }
                
                if (!positionField.value || positionField.value.trim() === '') {
                    event.preventDefault();
                    alert('Please enter the position of the issuer.');
                    positionField.focus();
                    return;
                }
            } else if (statusRefused.checked) {
                // Validate "Refused" specific fields
                if (!witnessedByField.value || witnessedByField.value.trim() === '') {
                    event.preventDefault();
                    alert('Please enter who witnessed the notice.');
                    witnessedByField.focus();
                    return;
                }
            }
            
            // If all validations pass, show a loading indicator
            Swal.fire({
                title: 'Submitting...',
                text: 'Please wait while we process your submission.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Let the form submit normally
        });
    }
});