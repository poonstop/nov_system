document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr for datetime picker
    flatpickr('.datepicker', {
        enableTime: true,
        dateFormat: "Y-m-d H:i:S",
        defaultDate: new Date()
    });

    // Initialize modals
    const violationsModal = new bootstrap.Modal(document.getElementById('violationsModal'));
    const inventoryModal = new bootstrap.Modal(document.getElementById('inventoryModal'));
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    
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
        
        // Trigger the change event on page load to set initial state
        natureSelect.dispatchEvent(new Event('change'));
    }

    const regionInput = document.getElementById('region');
    const provinceInput = document.getElementById('province');
    const municipalityInput = document.getElementById('municipality');
    const barangayInput = document.getElementById('barangay');
    
    // Data mapping for regions and provinces
    const regionProvinces = {
        'CAR (Cordillera Administrative Region)': ['Benguet', 'Abra', 'Apayao', 'Ifugao', 'Kalinga', 'Mountain Province'],
        'Region I (Ilocos Region)': ['Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan'],
        'Region II (Cagayan Valley)': ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino'],
        'Region III (Central Luzon)': ['Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales']
    };
    
    // Data mapping for provinces and municipalities
    const provinceMunicipalities = {
        'Benguet': ['Baguio City', 'La Trinidad', 'Itogon', 'Tuba', 'Tublay', 'Bokod', 'Kabayan', 'Atok', 'Kapangan', 'Kibungan', 'Mankayan', 'Buguias', 'Bakun'],
        'Ilocos Norte': ['Batac City', 'Currimao', 'Dingras', 'Laoag City', 'Marcos', 'Nueva Era', 'Paoay', 'Pasuquin', 'Piddig', 'San Nicolas', 'Sarrat', 'Solsona', 'Burgos', 'Bangui', 'Vintar', 'Badoc'],
        'Ilocos Sur': ['Vigan City', 'Candon City', 'Caoayan', 'Galimuyod', 'Gregorio del Pilar', 'Lidlidda', 'Magsingal', 'Narvacan', 'Quirino', 'Salcedo', 'San Ildefonso', 'San Juan', 'San Vicente', 'Santa Catalina', 'Santa Lucia', 'Santa Maria', 'Bantay', 'Tagudin', 'Santa Cruz', 'San Esteban', 'Santiago', 'Sinait', 'Cabugao', 'Santa Lucia', 'Alilem', 'Sugpon', 'Suyo'],
        'La Union': ['San Fernando City (La Union)', 'Agoo', 'Aringay', 'Bacnotan', 'Bagulin', 'Balaoan', 'Bangar', 'Bauang', 'Burgos', 'Caba', 'Luna', 'Naguilian', 'Pugo', 'Rosario', 'San Gabriel', 'San Juan', 'Santol', 'Santo Tomas', 'Sudipen', 'Tubao'],
        'Pangasinan': ['Alaminos City', 'Bani', 'Basista', 'Binalonan', 'Binmaley', 'Bolinao', 'Bugallon', 'Calasiao', 'Dasol', 'Lingayen', 'San Carlos City', 'San Fabian', 'San Jacinto', 'San Manuel', 'San Nicolas', 'San Quintin', 'Sison', 'Umingan']
    };
    
    // Data mapping for municipalities and barangays
    const municipalityBarangays = {
        'Baguio City': ['Asin Road', 'Aurora Hill', 'Cabinet Hill', 'Camp 7', 'City Camp', 'Engineers Hill', 'Fairview', 'Happy Hollow', 'Hillside', 'Imelda Village', 'Irisan', 'Loakan', 'Mines View', 'Pacdal', 'Quezon Hill'],
        'La Trinidad': ['Beckel', 'Betag', 'Bokawkan', 'Bontoc', 'Cabalatan', 'Carmen', 'Cecilia', 'Cruz', 'Dagsian', 'Lamtang', 'Longlong', 'Poblacion', 'Shilan'],
        'San Fernando City (La Union)': [
            'Barangay I (Pob.)', 'Barangay II (Pob.)', 'Barangay III (Pob.)', 'Barangay IV (Pob.)', 
            'Barangay V (Pob.)', 'Barangay VI (Pob.)', 'Barangay VII (Pob.)', 'Barangay VIII (Pob.)', 
            'Barangay IX (Pob.)', 'Barangay X (Pob.)', 'Barangay XI (Pob.)', 'Barangay XII (Pob.)', 
            'Bacsil', 'Bangbangolan', 'Baraoas', 'Biday', 'Cabaroan', 'Camansi', 'Canaoay', 
            'Dalumpinas Este', 'Dalumpinas Oeste', 'Dallangayan Este', 'Dallangayan Oeste', 
            'Langcuas', 'Lingsat', 'Nagyubuyuban', 'Pagdaraoan', 'Pao Norte', 'Pao Sur', 
            'Puspus', 'San Agustin', 'Santiago Norte', 'Santiago Sur', 'Tanqui', 'Tanquigan', 
            'Udiao', 'Wangal'
        ],
        'Aringay': [
            'Alaska', 'Basca', 'Dulao', 'Gallano', 'Macabato', 'Poblacion', 
            'Samara', 'San Antonio', 'San Benito', 'San Eugenio', 'San Juan', 
            'San Simon East', 'San Simon West', 'Santa Lucia', 'Santa Rita East', 
            'Santa Rita West', 'Santo Rosario East', 'Santo Rosario West'
        ],
        'Bauang': [
            'Acao', 'Bagbag', 'Ballay', 'Bawanta', 'Boy-utan', 'Cabalayangan', 'Carmay', 
            'Central East', 'Central West', 'Dili', 'Disso-or', 'Guerrero', 'Lower San Agustin', 
            'Nagrebcan', 'Palintucang', 'Pagdalagan', 'Paringao', 'Payocpoc Norte Este', 
            'Payocpoc Norte Oeste', 'Payocpoc Sur', 'Pilar', 'Pottot', 'Quinavite', 
            'Santa Monica', 'Santo Domingo', 'Upper San Agustin', 'Urayong'
        ],
        'Laoag City': [
            'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5',
            'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10',
            'Barangay 11', 'Barangay 12', 'Barangay 13', 'Barangay 14', 'Barangay 15',
            'Calayab', 'San Mateo', 'Santa Rosa', 'San Nicolas', 'Nangalisan'
        ],
        'Batac': [
            'Aglipay', 'Ben-agan', 'Bungon', 'Caaoacan', 'Cangrunaan',
            'Callaguip', 'Camandingan', 'Colo', 'Mabaleng', 'Pimentel',
            'Quiling Norte', 'Quiling Sur', 'Ricarte', 'San Julian', 'San Mateo',
            'San Pedro', 'Tabug', 'Valdez'
        ],
        'Vigan City': [
            'Ayusan Norte', 'Ayusan Sur', 'Bantay', 'Bulala', 'Cabalangegan',
            'Cabaroan Daya', 'Cabaroan Laud', 'Camangaan', 'Mindoro', 'Pantay Daya',
            'Pantay Fatima', 'Pantay Laud', 'Paoa', 'Paratong', 'Poblacion',
            'Raois', 'Salindeg', 'San Jose', 'San Julian Norte', 'San Julian Sur',
            'San Pedro', 'Tamag'
        ]
    };

    // Update datalist options with all available regions
    function populateRegions() {
        const regionList = document.getElementById('regionList');
        if (!regionList) return;
        
        clearDropdownList('regionList');
        
        for (const region in regionProvinces) {
            const option = document.createElement('option');
            option.value = region;
            regionList.appendChild(option);
        }
    }

    // Update provinces when region changes
    if (regionInput) {
        // Populate regions on load
        populateRegions();
        
        regionInput.addEventListener('input', function() {
            const selectedRegion = this.value;
            
            // Clear province, municipality, and barangay when region changes
            if (provinceInput) {
                provinceInput.value = '';
                updateDropdownList(selectedRegion, provinceInput, regionProvinces, 'provinceList');
            }
            
            if (municipalityInput) {
                municipalityInput.value = '';
                clearDropdownList('municipalityList');
            }
            
            if (barangayInput) {
                barangayInput.value = '';
                clearDropdownList('barangayList');
            }
        });
    }
    
    // Update municipalities when province changes
    if (provinceInput) {
        provinceInput.addEventListener('input', function() {
            const selectedProvince = this.value;
            
            // Clear municipality and barangay when province changes
            if (municipalityInput) {
                municipalityInput.value = '';
                updateDropdownList(selectedProvince, municipalityInput, provinceMunicipalities, 'municipalityList');
            }
            
            if (barangayInput) {
                barangayInput.value = '';
                clearDropdownList('barangayList');
            }
        });
    }
    
    // Update barangays when municipality changes
    if (municipalityInput) {
        municipalityInput.addEventListener('input', function() {
            const selectedMunicipality = this.value;
            
            // Update barangay options
            if (barangayInput) {
                barangayInput.value = '';
                updateDropdownList(selectedMunicipality, barangayInput, municipalityBarangays, 'barangayList');
            }
        });
    }
    
    // Function to update dropdown list based on selection
    function updateDropdownList(selectedValue, targetInput, dataMapping, datalistId) {
        const datalist = document.getElementById(datalistId);
        if (!datalist) return;
        
        // Clear existing options
        clearDropdownList(datalistId);
        
        // Add new options if the selected value has mappings
        if (dataMapping[selectedValue]) {
            const items = dataMapping[selectedValue];
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                datalist.appendChild(option);
            });
        }
    }
    
    // Function to clear dropdown list
    function clearDropdownList(datalistId) {
        const datalist = document.getElementById(datalistId);
        if (datalist) {
            while (datalist.firstChild) {
                datalist.removeChild(datalist.firstChild);
            }
        }
    }

    // Establishment form submission
    const novForm = document.getElementById('novForm');
    if (novForm) {
        novForm.addEventListener('submit', function(event) {
            // Prevent the default form submission
            event.preventDefault();
            
            // Validate required fields
            const requiredFields = ['establishment', 'owner_representative', 'street', 'barangay', 
                                    'municipality', 'province', 'region', 'nature_select'];
            
            let isValid = true;
            for (const field of requiredFields) {
                const element = document.getElementById(field);
                if (element && (!element.value || element.value.trim() === '')) {
                    isValid = false;
                    element.classList.add('is-invalid');
                } else if (element) {
                    element.classList.remove('is-invalid');
                }
            }
            
            // Validate nature of business if "Others" is selected
            if (natureSelect && natureSelect.value === 'Others' && 
                (!natureCustom.value || natureCustom.value.trim() === '')) {
                isValid = false;
                natureCustom.classList.add('is-invalid');
            } else if (natureCustom) {
                natureCustom.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.'
                });
                return;
            }
            
            // All validations passed, show the violations modal
            violationsModal.show();
        });
    }
    
    // Handle violations form submission
    const violationsForm = document.querySelector('#violationsModal form');
    if (violationsForm) {
        violationsForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Validate at least one violation is selected
            const violationCheckboxes = document.querySelectorAll('input[name="violations[]"]:checked');
            if (violationCheckboxes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Violations Selected',
                    text: 'Please select at least one violation or add remarks to explain the issue.'
                });
                return;
            }
            
            violationsModal.hide();
            inventoryModal.show();
        });
    }
    
    // Handle inventory form submission
    const inventoryForm = document.querySelector('#inventoryModal form');
    if (inventoryForm) {
        inventoryForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Check if they clicked "Save" or "Skip"
            const isSkip = event.submitter && event.submitter.name === 'skip_inventory';
            
            // If not skipping, validate product entries
            if (!isSkip) {
                const products = document.querySelectorAll('.product-item');
                let hasValidProduct = false;
                
                products.forEach(product => {
                    const nameField = product.querySelector('input[name$="[name]"]');
                    if (nameField && nameField.value.trim() !== '') {
                        hasValidProduct = true;
                    }
                });
                
                if (products.length > 0 && !hasValidProduct) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Products',
                        text: 'Please add at least one product with a name or use "Skip" if no products are needed.'
                    });
                    return;
                }
            }
            
            inventoryModal.hide();
            statusModal.show();
        });
    }
    
    // For the final status form, we implement the combined form submission
    const statusForm = document.querySelector('#statusModal form');
    if (statusForm) {
        statusForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Validate required fields before submitting
            const statusReceived = document.getElementById('statusReceived');
            const statusRefused = document.getElementById('statusRefused');
            const issuedByField = document.getElementById('issued_by');
            const positionField = document.getElementById('position');
            const witnessedByField = document.getElementById('witnessed_by');
            
            if (!statusReceived || !statusRefused || (!statusReceived.checked && !statusRefused.checked)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Status Not Selected',
                    text: 'Please select a notice status (Received or Refused).'
                });
                return;
            }
            
            if (statusReceived.checked) {
                // Validate "Received" specific fields
                if (!issuedByField || !issuedByField.value || issuedByField.value.trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please enter who issued the notice.'
                    });
                    if (issuedByField) issuedByField.focus();
                    return;
                }
                
                if (!positionField || !positionField.value || positionField.value.trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please enter the position of the issuer.'
                    });
                    if (positionField) positionField.focus();
                    return;
                }
            } else if (statusRefused.checked) {
                // Validate "Refused" specific fields
                if (!witnessedByField || !witnessedByField.value || witnessedByField.value.trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please enter who witnessed the notice.'
                    });
                    if (witnessedByField) witnessedByField.focus();
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
            
            // Create a combined form data object
            const formData = new FormData();
            
            // Add a flag to indicate this is a combined submission
            formData.append('combined_form_submission', '1');
            
            // Append establishment form data
            if (novForm) {
                new FormData(novForm).forEach((value, key) => {
                    formData.append(key, value);
                });
            }
            
            // Append violations data
            if (violationsForm) {
                new FormData(violationsForm).forEach((value, key) => {
                    formData.append(key, value);
                });
            }
            
            // Append inventory data
            if (inventoryForm) {
                const inventoryFormData = new FormData(inventoryForm);
                const productItems = document.querySelectorAll('.product-item');
                
                // Handle product array properly
                productItems.forEach((product, index) => {
                    const nameField = product.querySelector('input[name$="[name]"]');
                    if (nameField && nameField.value.trim() !== '') {
                        // For each field in this product
                        product.querySelectorAll('input, textarea').forEach(field => {
                            const fieldName = field.name;
                            if (fieldName) {
                                // For checkboxes, only include if checked
                                if (field.type === 'checkbox') {
                                    if (field.checked) {
                                        formData.append(fieldName, field.value || '1');
                                    }
                                } else {
                                    formData.append(fieldName, field.value || '');
                                }
                            }
                        });
                    }
                });
            }
            
            // Append status data
            if (statusForm) {
                new FormData(statusForm).forEach((value, key) => {
                    formData.append(key, value);
                });
            }

            // Set the notice status based on radio selection
            if (statusReceived && statusReceived.checked) {
                formData.append('notice_status', 'Received');
            } else if (statusRefused && statusRefused.checked) {
                formData.append('notice_status', 'Refused');
            }
            
            // Send via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error('Network response was not ok.');
            })
            .then(html => {
                // Check if the response contains a success message
                if (html.includes('success=1')) {
                    // Redirect to the success page or show success message
                    window.location.href = 'establishments.php?success=1';
                } else {
                    // Parse success message from HTML if available
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const successMsg = doc.getElementById('success-message');
                    
                    if (successMsg && successMsg.textContent.trim() !== '') {
                        try {
                            const successData = JSON.parse(successMsg.textContent);
                            Swal.fire({
                                icon: 'success',
                                title: successData.title || 'Success!',
                                text: successData.text || 'Operation completed successfully.',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                window.location.href = 'establishments.php?success=1';
                            });
                        } catch (e) {
                            // Redirect anyway
                            window.location.href = 'establishments.php?success=1';
                        }
                    } else {
                        // Show a generic success message and redirect
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Notice of Violation has been recorded successfully.',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.href = 'establishments.php?success=1';
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Error',
                    text: 'There was a problem submitting the form. Please try again.'
                });
            });
        });
    }

    // Show modals based on URL parameters (keep this for direct links)
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
                violationsModal.hide();
                // Don't navigate away from the page
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

    // Handle status radio toggle
    const statusRadios = document.querySelectorAll('.status-radio');
    const receivedFields = document.querySelector('.received-only-fields');
    const refusedFields = document.querySelector('.refused-only-fields');
    
    if (statusRadios.length && receivedFields && refusedFields) {
        statusRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Received') {
                    // Show fields for "Received" status
                    receivedFields.style.display = 'block';
                    refusedFields.style.display = 'none';
                    
                    // Make fields required accordingly
                    document.getElementById('issued_by')?.setAttribute('required', 'required');
                    document.getElementById('position')?.setAttribute('required', 'required');
                    document.getElementById('witnessed_by')?.removeAttribute('required');
                } else if (this.value === 'Refused') {
                    // Show fields for "Refused" status
                    receivedFields.style.display = 'none';
                    refusedFields.style.display = 'block';
                    
                    // Make fields required accordingly
                    document.getElementById('issued_by')?.removeAttribute('required');
                    document.getElementById('position')?.removeAttribute('required');
                    document.getElementById('witnessed_by')?.setAttribute('required', 'required');
                }
            });
            
            // Trigger change event on page load for the checked radio
            if (radio.checked) {
                radio.dispatchEvent(new Event('change'));
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
                    // Reset forms if needed
                    if (novForm) novForm.reset();
                    if (violationsForm) violationsForm.reset();
                    if (inventoryForm) inventoryForm.reset();
                    if (statusForm) statusForm.reset();
                    
                    // Remove all products except the first one
                    const productItems = document.querySelectorAll('.product-item:not(:first-child)');
                    productItems.forEach(item => item.remove());
                    
                    // Reset first product form
                    const firstProduct = document.querySelector('.product-item');
                    if (firstProduct) {
                        firstProduct.querySelectorAll('input, textarea').forEach(field => {
                            if (field.type === 'checkbox') {
                                field.checked = false;
                            } else {
                                field.value = '';
                            }
                        });
                    }
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
    
    // Function to handle errors
    function handleError(errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage || 'An error occurred. Please try again.',
            confirmButtonColor: '#d33'
        });
    }
    
    // Check for error messages
    const errorMessage = document.getElementById('error-message');
    if (errorMessage && errorMessage.textContent.trim() !== '') {
        handleError(errorMessage.textContent);
    }
});
