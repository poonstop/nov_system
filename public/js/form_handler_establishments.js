$(document).ready(function() {
    // Calculate expiry date when issue date changes
   $('#issue_date').on('change', function() {
        if ($(this).val()) {
            const issueDate = new Date($(this).val());
            
            // Function to add business days (excluding weekends)
            function addBusinessDays(date, days) {
                let result = new Date(date);
                let addedDays = 0;
                
                while (addedDays < days) {
                    // Add one day
                    result.setDate(result.getDate() + 1);
                    
                    // Skip weekends (0 = Sunday, 6 = Saturday)
                    const dayOfWeek = result.getDay();
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        addedDays++;
                    }
                }
                
                return result;
            }
            
            // 48 hours = 2 business days
            const expiryDate = addBusinessDays(issueDate, 2);
            
            // Format the date as YYYY-MM-DD for input field
            const yyyy = expiryDate.getFullYear();
            const mm = String(expiryDate.getMonth() + 1).padStart(2, '0');
            const dd = String(expiryDate.getDate()).padStart(2, '0');
            
            $('#expiry_date').val(`${yyyy}-${mm}-${dd}`);
        }
    });
    
    // Show/hide other nature text field
    $('#nature').on('change', function() {
        if ($(this).val() === 'Others') {
            $('#other_nature_container').show();
        } else {
            $('#other_nature_container').hide();
        }
    });

    // Show/hide the "witnessed by" field based on the selected notice status
    $('input[name="notice_status"]').on('change', function() {
        if ($(this).val() === 'Refused') {
            $('#witnessed_by_section').show();
        } else {
            $('#witnessed_by_section').hide();
        }
    });

    // Show/hide "Other violations" text field
    $('#viol_other_violations').on('change', function() {
        if ($(this).is(':checked')) {
            $('#other_violations_container').show();
        } else {
            $('#other_violations_container').hide();
        }
    });

    // Add product button in inventory modal
    $('#addProductBtn').on('click', function() {
        const newItem = $('.inventory-item').first().clone();
        newItem.find('input, textarea').val('');
        newItem.find('input[type="checkbox"]').prop('checked', false);
        $('#inventoryItems').append(newItem);
    });

    // Add issuer button in status modal
    $('#addIssuerBtn').on('click', function() {
        const newIssuer = $('.issuer-item').first().clone();
        newIssuer.find('input').val('');
        newIssuer.find('select').prop('selectedIndex', 0);
        $('#issuersList').append(newIssuer);
    });

    // === Navigation between forms/modals ===
    
    // Establishment -> Violations
    $('#establishmentProceedBtn').on('click', function() {
        // Validate establishment form
        let isValid = true;
        $('#establishmentForm input[required], #establishmentForm select[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Validate "Other" nature if selected
        if ($('#nature').val() === 'Others' && !$('#other_nature').val()) {
            isValid = false;
            $('#other_nature').addClass('is-invalid');
        }
        
        if (isValid) {
            $('#violationsModal').modal('show');
        } else {
            alert('Please fill in all required fields.');
        }
    });
    
    // Violations -> Establishment (back button)
    $('#violationsBackBtn').on('click', function() {
        $('#violationsModal').modal('hide');
    });
    
    // Violations -> Inventory
    $('#violationsProceedBtn').on('click', function() {
        $('#violationsModal').modal('hide');
        $('#inventoryModal').modal('show');
    });
    
    // Inventory -> Violations (back button)
    $('#inventoryBackBtn').on('click', function() {
        $('#inventoryModal').modal('hide');
        $('#violationsModal').modal('show');
    });
    
    // Inventory -> Status (skip button)
    $('#inventorySkipBtn').on('click', function() {
        $('#inventoryModal').modal('hide');
        $('#statusModal').modal('show');
        // Mark that the skip button was clicked
        $(this).data('was-clicked', true);
    });
    
    // Inventory -> Status
    $('#inventoryProceedBtn').on('click', function() {
        $('#inventoryModal').modal('hide');
        $('#statusModal').modal('show');
        // Reset skip button flag
        $('#inventorySkipBtn').data('was-clicked', false);
    });
    
    // Status -> Inventory (back button)
    $('#statusBackBtn').on('click', function() {
        $('#statusModal').modal('hide');
        $('#inventoryModal').modal('show');
    });
    
    // Submit all forms
    $('#statusSaveBtn').on('click', function() {
        // Collect all form data
        const formData = new FormData();
        
        // Establishment form data
        $('#establishmentForm').find('input, select, textarea').each(function() {
            const input = $(this);
            if (input.attr('name')) {
                formData.append(input.attr('name'), input.val());
            }
        });
        
        // If "Others" is selected for nature, add the value
        if ($('#nature').val() === 'Others') {
            formData.append('nature', $('#other_nature').val());
        }
        
        // FIXED: Violations form data processing
        const violations = [];

        // Process ALL regular violation checkboxes EXCEPT the DAO "Others" checkbox
        $('#violationsForm input[name="violations[]"]:checked').each(function() {
            const violationValue = $(this).val();
            // Skip ONLY the DAO "Others" checkbox - we handle its text separately
            if (violationValue !== 'Others') {
                violations.push(violationValue);
            }
        });

        // FIXED: Handle DAO "Others" - Add the actual text from the input field
        if ($('#viol_others').is(':checked')) {
            const othersDetails = $('#others_details').val().trim();
            if (othersDetails) {
                violations.push(othersDetails); // Add the actual text input
            }
        }

        // Handle "Other Violations" section
        if ($('#viol_other_violations').is(':checked')) {
            const otherViolationsText = $('#other_violations_text').val().trim();
            if (otherViolationsText) {
                violations.push('Other Violations: ' + otherViolationsText);
            } else {
                violations.push('Other Violations');
            }
        }

        // Add each violation as array element to FormData
        violations.forEach(function(violation, index) {
            formData.append(`violations[${index}]`, violation);
        });

        console.log('Violations being sent:', violations); // Debug log
        
        // Inventory form data
        const inventorySkipped = $('#inventorySkipBtn').data('was-clicked') === true;
        formData.append('inventory_skipped', inventorySkipped ? 'true' : 'false');
        
        if (!inventorySkipped) {
            $('.inventory-item').each(function(index) {
                const item = $(this);
                const productName = item.find('.product_name').val();
                
                // Only process items with product names
                if (productName) {
                    formData.append(`product_name[${index}]`, productName);
                    formData.append(`sealed[${index}]`, item.find('.sealed').is(':checked') ? '1' : '0');
                    formData.append(`withdrawn[${index}]`, item.find('.withdrawn').is(':checked') ? '1' : '0');
                    formData.append(`description[${index}]`, item.find('.description').val() || '');
                    formData.append(`price[${index}]`, item.find('.price').val() || '0');
                    formData.append(`pieces[${index}]`, item.find('.pieces').val() || '0');
                    formData.append(`dao_violation[${index}]`, item.find('.dao_violation').is(':checked') ? '1' : '0');
                    formData.append(`other_violation[${index}]`, item.find('.other_violation').is(':checked') ? '1' : '0');
                    formData.append(`product_remarks[${index}]`, item.find('.product_remarks').val() || '');
                }
            });
            
            // Inventory remarks
            formData.append('sealed_products_left', $('#sealedProductsLeft').is(':checked') ? '1' : '0');
            formData.append('withdrawn_products_to_dti', $('#withdrawnProductsToDti').is(':checked') ? '1' : '0');
        }
        
        // Status form data
        formData.append('notice_status', $('input[name="notice_status"]:checked').val());
        formData.append('witnessed_by', $('#witnessed_by').val() || '');
        
        // Issuers form data
        $('.issuer-item').each(function(index) {
            const issuer = $(this);
            const issuerName = issuer.find('.issuer_name').val();
            
            if (issuerName) {
                formData.append(`issuer_name[${index}]`, issuerName);
                formData.append(`issuer_position[${index}]`, issuer.find('.issuer_position').val() || '');
            }
        });
        
        // Add form submission flag
        formData.append('form_submit', 'save_notice');
        
        // Debug: Log form data
        console.log('Form data being submitted:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Submit form via AJAX to the current page (instead of process_notice.php)
        $.ajax({
            url: window.location.href, // Submit to the current page
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Server response:', response);
                if (response.status === 'success') {
                    // Show success modal
                    $('#statusModal').modal('hide');
                    $('#successModal').modal('show');
                } else {
                    // Show error message
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.log('Server Response:', xhr.responseText);
                
                // Try to parse the error response
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    alert('Error: ' + (errorResponse.message || 'Unknown error occurred'));
                } catch (e) {
                    // If not JSON, it's probably an HTML error page
                    alert('A server error occurred. Please try again later.');
                }
            }
        });
    });
    
    // Handle success modal redirection
    $('#goToEstablishmentFormBtn').on('click', function() {
        window.location.reload();
    });
});

function toggleOthersInput() {
    var othersCheckbox = document.getElementById('viol_others');
    var othersInputContainer = document.getElementById('others_input_container');
    
    if (othersCheckbox.checked) {
        othersInputContainer.classList.remove('d-none');
    } else {
        othersInputContainer.classList.add('d-none');
        // Clear the input when hiding
        document.getElementById('others_details').value = '';
    }
}

function toggleAddressEdit() {
    const displayEl = document.getElementById('address-display');
    const editEl = document.getElementById('address-edit');
    
    if (displayEl.style.display === 'none') {
        displayEl.style.display = 'block';
        editEl.style.display = 'none';
    } else {
        displayEl.style.display = 'none';
        editEl.style.display = 'block';
    }
}