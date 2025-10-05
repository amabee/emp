<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    <input type="hidden" name="id" id="edit_employee_id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" id="edit_middle_name">
                        </div>
                        <div class="col-md-12 mt-2">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact_number" id="edit_contact_number">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" id="edit_gender">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birthdate</label>
                            <input type="date" class="form-control" name="birthdate" id="edit_birthdate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Avatar (image)</label>
                            <input type="file" accept="image/*" class="form-control" name="image" id="edit_image">
                            <div class="mt-2">
                                <img id="edit_image_preview" src="" alt="Preview" style="width:80px;height:80px;object-fit:cover;border-radius:50%;display:none;border:1px solid #ddd;">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Basic Salary</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="basic_salary" id="edit_basic_salary" placeholder="0.00">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department" id="edit_department">
                                <option value="">Select Department</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <select class="form-select" name="position" id="edit_position">
                                <option value="">Select Position</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                    </div>

                    <!-- Allowances Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3">Allowances</h6>
                            <div id="edit_allowancesContainer" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-muted">Loading allowances...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Deductions Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3">Deductions (Non-Dynamic)</h6>
                            <div id="edit_deductionsContainer" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-muted">Loading deductions...</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">User Type</label>
                            <select class="form-select" name="user_type" id="edit_user_type">
                                <option value="">Select User Type</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employment Status</label>
                            <select class="form-select" name="employment_status" id="edit_employment_status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editEmployeeForm" class="btn btn-primary" id="updateEmployeeBtn">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
// Handle Edit Employee Modal
$('#editEmployeeModal').on('show.bs.modal', function () {
    // Load departments and positions for edit modal
    loadDepartmentsAndPositionsForEdit();
});

    // Preview for edit image
    $('#edit_image').on('change', function(e) {
        const file = this.files && this.files[0];
        if (!file) { $('#edit_image_preview').hide().attr('src',''); return; }
        const url = URL.createObjectURL(file);
        $('#edit_image_preview').attr('src', url).show();
    });

// Handle edit form submission (support file upload via FormData)
$('#editEmployeeForm').on('submit', function (e) {
    e.preventDefault();
    
    const $btn = $('#updateEmployeeBtn');
    const $spinner = $btn.find('.spinner-border');
    
    // Show loading state
    $btn.prop('disabled', true);
    $spinner.removeClass('d-none');

    const form = document.getElementById('editEmployeeForm');
    const formData = new FormData(form);

    $.ajax({
        url: '../ajax/update_employee.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#editEmployeeModal').modal('hide');
                refreshEmployees(); // Refresh the employee list
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        },
        complete: function () {
            // Hide loading state
            $btn.prop('disabled', false);
            $spinner.addClass('d-none');
        }
    });
});

function loadDepartmentsAndPositionsForEdit() {
    $.ajax({
        url: '../ajax/get_dropdown_data.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // Populate departments for edit modal
                const $deptSelect = $('#edit_department');
                $deptSelect.find('option:not(:first)').remove();
                
                if (response.departments && response.departments.length > 0) {
                    response.departments.forEach(dept => {
                        $deptSelect.append(`<option value="${dept.id}">${dept.name}</option>`);
                    });
                }

                // Populate positions for edit modal
                const $posSelect = $('#edit_position');
                $posSelect.find('option:not(:first)').remove();
                
                if (response.positions && response.positions.length > 0) {
                    response.positions.forEach(pos => {
                        $posSelect.append(`<option value="${pos.id}">${pos.name}</option>`);
                    });
                }

                // Populate user types for edit modal
                const $userTypeSelect = $('#edit_user_type');
                $userTypeSelect.find('option:not(:first)').remove();
                
                if (response.user_types && response.user_types.length > 0) {
                    response.user_types.forEach(type => {
                        $userTypeSelect.append(`<option value="${type.id}">${type.name}</option>`);
                    });
                }
            } else {
                console.error('Failed to load dropdown data for edit:', response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to load departments and positions for edit:', error);
        }
    });

    // Load allowances for edit modal
    loadAllowancesForEdit();
    
    // Load deductions for edit modal (non-dynamic only)
    loadDeductionsForEdit();
}

function loadAllowancesForEdit() {
    $.ajax({
        url: '../ajax/get_allowance_types.php',
        type: 'GET',
        dataType: 'json',
        success: function (allowances) {
            const $container = $('#edit_allowancesContainer');
            
            if (allowances && allowances.length > 0) {
                let html = '';
                allowances.forEach(allowance => {
                    if (allowance.is_active == 1) {
                        html += `
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input edit-allowance-checkbox" 
                                       name="allowances[]" value="${allowance.allowance_id}" 
                                       id="edit_allowance_${allowance.allowance_id}">
                                <label class="form-check-label" for="edit_allowance_${allowance.allowance_id}">
                                    ${allowance.allowance_type}
                                    ${allowance.description ? `<small class="text-muted">- ${allowance.description}</small>` : ''}
                                </label>
                                <div class="mt-1 edit-allowance-amount-container" id="edit_allowance_amount_${allowance.allowance_id}" style="display: none;">
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" 
                                           name="allowance_amounts[${allowance.allowance_id}]" 
                                           placeholder="Enter amount">
                                </div>
                            </div>
                        `;
                    }
                });
                
                if (html === '') {
                    $container.html('<div class="text-muted">No active allowances available</div>');
                } else {
                    $container.html(html);
                    
                    // Handle checkbox changes to show/hide amount input
                    $('.edit-allowance-checkbox').on('change', function() {
                        const allowanceId = $(this).val();
                        const $amountContainer = $(`#edit_allowance_amount_${allowanceId}`);
                        
                        if ($(this).is(':checked')) {
                            $amountContainer.show();
                        } else {
                            $amountContainer.hide();
                            $amountContainer.find('input').val('');
                        }
                    });
                }
            } else {
                $container.html('<div class="text-muted">No allowances available</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to load allowances for edit:', error);
            $('#edit_allowancesContainer').html('<div class="text-danger">Failed to load allowances</div>');
        }
    });
}

function loadDeductionsForEdit() {
    $.ajax({
        url: '../ajax/get_deduction_types.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const $container = $('#edit_deductionsContainer');
            
            if (response.success && response.deduction_types && response.deduction_types.length > 0) {
                let html = '';
                response.deduction_types.forEach(deduction => {
                    // Only show non-dynamic deductions (is_dynamic = 0)
                    if (deduction.is_active == 1 && deduction.is_dynamic == 0) {
                        html += `
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input edit-deduction-checkbox" 
                                       name="deductions[]" value="${deduction.deduction_type_id}" 
                                       id="edit_deduction_${deduction.deduction_type_id}">
                                <label class="form-check-label" for="edit_deduction_${deduction.deduction_type_id}">
                                    ${deduction.type_name}
                                    <small class="text-muted">(${deduction.amount_type || 'Fixed'})</small>
                                </label>
                                <div class="mt-1 edit-deduction-amount-container" id="edit_deduction_amount_${deduction.deduction_type_id}" style="display: none;">
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" 
                                           name="deduction_amounts[${deduction.deduction_type_id}]" 
                                           placeholder="Enter ${deduction.amount_type === 'PERCENTAGE' ? 'percentage' : 'amount'}">
                                </div>
                            </div>
                        `;
                    }
                });
                
                if (html === '') {
                    $container.html('<div class="text-muted">No active non-dynamic deductions available</div>');
                } else {
                    $container.html(html);
                    
                    // Handle checkbox changes to show/hide amount input
                    $('.edit-deduction-checkbox').on('change', function() {
                        const deductionId = $(this).val();
                        const $amountContainer = $(`#edit_deduction_amount_${deductionId}`);
                        
                        if ($(this).is(':checked')) {
                            $amountContainer.show();
                        } else {
                            $amountContainer.hide();
                            $amountContainer.find('input').val('');
                        }
                    });
                }
            } else {
                $container.html('<div class="text-muted">No deductions available</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to load deductions for edit:', error);
            $('#edit_deductionsContainer').html('<div class="text-danger">Failed to load deductions</div>');
        }
    });
}

}); // End document ready

// Function to populate edit modal with employee data (global function)
function populateEditModal(employee) {
    $('#edit_employee_id').val(employee.employee_id);
    $('#edit_first_name').val(employee.first_name);
    $('#edit_middle_name').val(employee.middle_name || '');
    $('#edit_last_name').val(employee.last_name);
    $('#edit_email').val(employee.email);
    $('#edit_contact_number').val(employee.contact_number || '');
    $('#edit_gender').val(employee.gender || '');
    $('#edit_birthdate').val(employee.birthdate || '');
    $('#edit_basic_salary').val(employee.basic_salary || '');
    // Show existing image if present
    if (employee.image) {
        $('#edit_image_preview').attr('src', employee.image).show();
    } else {
        $('#edit_image_preview').hide().attr('src','');
    }
    
    // Set selected department, position, user type and employment status
    setTimeout(function() {
        $('#edit_department').val(employee.department_id || '');
        $('#edit_position').val(employee.position_id || '');
        $('#edit_user_type').val(employee.user_type_id || '');
        $('#edit_employment_status').val(employee.employment_status || '1');
        
        // Load existing allowances and deductions for this employee
        loadExistingEmployeeAllowancesAndDeductions(employee.employee_id);
    }, 200); // Small delay to ensure dropdowns and checkboxes are populated first
}

// Function to load existing employee allowances and deductions
function loadExistingEmployeeAllowancesAndDeductions(employeeId) {
    // Load existing allowances
    $.ajax({
        url: '../ajax/get_allowance_employees.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response && response.length > 0) {
                response.forEach(allowanceAssignment => {
                    if (allowanceAssignment.employee_id == employeeId) {
                        const allowanceId = allowanceAssignment.allowance_id;
                        const amount = allowanceAssignment.amount;
                        
                        // Check the checkbox
                        const $checkbox = $(`#edit_allowance_${allowanceId}`);
                        if ($checkbox.length) {
                            $checkbox.prop('checked', true);
                            
                            // Show and populate the amount field
                            const $amountContainer = $(`#edit_allowance_amount_${allowanceId}`);
                            $amountContainer.show();
                            $amountContainer.find('input').val(amount);
                        }
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to load existing allowances:', error);
        }
    });

    // Load existing deductions
    $.ajax({
        url: '../ajax/get_deduction_employees.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.employee_deductions && response.employee_deductions.length > 0) {
                response.employee_deductions.forEach(deductionAssignment => {
                    if (deductionAssignment.employee_id == employeeId) {
                        const deductionId = deductionAssignment.deduction_type_id;
                        const amount = deductionAssignment.amount;
                        
                        // Check the checkbox
                        const $checkbox = $(`#edit_deduction_${deductionId}`);
                        if ($checkbox.length) {
                            $checkbox.prop('checked', true);
                            
                            // Show and populate the amount field
                            const $amountContainer = $(`#edit_deduction_amount_${deductionId}`);
                            $amountContainer.show();
                            $amountContainer.find('input').val(amount);
                        }
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to load existing deductions:', error);
        }
    });
}
</script>
