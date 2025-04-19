document.addEventListener('DOMContentLoaded', function() {
    // Initialize status radio buttons and fields
    const statusReceived = document.getElementById('statusReceived');
    const statusRefused = document.getElementById('statusRefused');
    const receivedByFields = document.getElementById('receivedByFields');
    const refusedByFields = document.getElementById('refusedByFields');

    if (statusReceived && statusRefused && receivedByFields && refusedByFields) {
        statusReceived.addEventListener('change', function() {
            if (this.checked) {
                receivedByFields.style.display = 'block';
                refusedByFields.style.display = 'none';
                document.getElementById('witnessed_by').setAttribute('required', 'required');
                document.getElementById('issued_by').removeAttribute('required');
                document.getElementById('position').removeAttribute('required');
            }
        });
        
        statusRefused.addEventListener('change', function() {
            if (this.checked) {
                receivedByFields.style.display = 'none';
                refusedByFields.style.display = 'block';
                document.getElementById('witnessed_by').setAttribute('required', 'required');
                document.getElementById('issued_by').removeAttribute('required');
                document.getElementById('position').removeAttribute('required');
            }
        });
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

    // Violations form submission - modify this part
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
        // Hide the inventory modal
        const inventoryModal = document.getElementById('inventoryModal');
        const bsInventoryModal = bootstrap.Modal.getInstance(inventoryModal);
        
        if (bsInventoryModal) {
            // Use the Bootstrap modal hide method if we found an instance
            bsInventoryModal.hide();
            
            // Wait for the modal to finish hiding before showing the next one
            inventoryModal.addEventListener('hidden.bs.modal', function() {
                // Show the received/refused modal
                const receivedRefusedModal = document.getElementById('receivedRefusedModal');
                new bootstrap.Modal(receivedRefusedModal).show();
            }, { once: true });
        } else {
            // Manual hide if we can't find the Bootstrap modal instance
            inventoryModal.classList.remove('show');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            
            // Wait a moment then show the next modal
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

    // Status form submission
    const submitStatusBtn = document.getElementById('submitStatusBtn');
    if (submitStatusBtn) {
        submitStatusBtn.addEventListener('click', function() {
            const statusForm = document.getElementById('noticeStatusForm');
            const statusData = new FormData(statusForm);
            
            if (!statusData.get('notice_status')) {
                Swal.fire({
                    title: 'Status Required',
                    text: 'Please select whether the notice was received or refused.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            const status = statusData.get('notice_status');
            if (status === 'Received' && (!statusData.get('issued_by') || !statusData.get('position'))) {
                Swal.fire({
                    title: 'Information Required',
                    text: 'Please enter both issuer name and position.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            if (status === 'Refused' && !statusData.get('witnessed_by')) {
                Swal.fire({
                    title: 'Witness Required',
                    text: 'Please enter the name of the witness.',
                    icon: 'warning',
                    confirmButtonColor: '#10346C'
                });
                return;
            }
            
            sessionStorage.setItem('statusFormData', JSON.stringify(Object.fromEntries(statusData)));
            bootstrap.Modal.getInstance(document.getElementById('receivedRefusedModal')).hide();
            window.location.href = 'establishments.php?show_issuer_modal=1';
        });
    }

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
            .then(handleResponse)
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
        });
    }

    function handleResponse(response) {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    }

    function resetButton(button) {
        button.disabled = false;
        button.innerHTML = 'Save Products';
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
                    <!-- Product form fields -->
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
    }

    
});