<div class="modal fade" id="addEmployeeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addEmployeeForm">
          <!-- Hidden field to track applicant ID for conversion -->
          <input type="hidden" name="from_applicant_id" id="fromApplicantId" value="">
          
          <!-- Option to Import from Applicant -->
          <div class="alert alert-info" role="alert">
            <i class='bx bx-info-circle'></i>
            <strong>Tip:</strong> You can select an accepted applicant to auto-fill their information.
          </div>
          
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Import from Applicant (Optional)</label>
              <select class="form-select" id="applicantSelect" name="applicant_id">
                <option value="">-- Create New Employee --</option>
              </select>
              <small class="text-muted">Select an accepted applicant to import their details</small>
            </div>
          </div>

          <hr class="my-3">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">First Name *</label>
              <input type="text" class="form-control" name="first_name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Middle Name</label>
              <input type="text" class="form-control" name="middle_name">
            </div>
            <div class="col-md-12 mt-2">
              <label class="form-label">Last Name *</label>
              <input type="text" class="form-control" name="last_name" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" name="contact_number">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Basic Salary</label>
              <input type="number" step="0.01" min="0" class="form-control" name="basic_salary" placeholder="0.00">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select class="form-select" name="gender">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Birthdate</label>
              <input type="date" class="form-control" name="birthdate">
            </div>
            <div class="col-md-4">
              <label class="form-label">Avatar (image)</label>
              <input type="file" accept="image/*" class="form-control" name="image" id="add_image">
              <div class="mt-2">
                  <img id="add_image_preview" src="" alt="Preview" style="width:80px;height:80px;object-fit:cover;border-radius:50%;display:none;border:1px solid #ddd;">
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Department</label>
              <select class="form-select" name="department" id="departmentSelect">
                <option value="">Select Department</option>
                <!-- Will be populated via JavaScript -->
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Position</label>
              <select class="form-select" name="position" id="positionSelect">
                <option value="">Select Position</option>
                <!-- Will be populated via JavaScript -->
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label"><i class="bx bx-store me-1"></i>Branch</label>
              <select class="form-select" name="branch_id" id="branchSelect">
                <option value="">No Branch (Unassigned)</option>
                <!-- Will be populated via JavaScript -->
              </select>
              <small class="text-muted">Assign employee to a branch location</small>
            </div>
          </div>

          <!-- Allowances Section -->
          <div class="row mb-3">
            <div class="col-12">
              <h6 class="mb-3">Allowances</h6>
              <div id="allowancesContainer" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                <div class="text-muted">Loading allowances...</div>
              </div>
            </div>
          </div>

          <!-- Deductions Section -->
          <div class="row mb-3">
            <div class="col-12">
              <h6 class="mb-3">Deductions (Non-Dynamic)</h6>
              <div id="deductionsContainer" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                <div class="text-muted">Loading deductions...</div>
              </div>
            </div>
          </div>

          <!-- Work Schedule Info -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="alert alert-info">
                <h6 class="mb-2">📅 Work Schedule</h6>
                <p class="mb-0">Employee work schedule will be automatically created based on the company's working calendar. The system will assign standard working hours (8:00 AM - 5:00 PM) for all future working days.</p>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addEmployeeForm" class="btn btn-primary" id="submitEmployeeBtn">
          <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
          Add Employee
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    // Handle Add Employee Modal
    $('#addEmployeeModal').on('show.bs.modal', function () {
      // Reset form
      $('#addEmployeeForm')[0].reset();
      // Load departments and positions
      loadDepartmentsAndPositions();
    });

      // Handle image preview for add modal
      $('#add_image').on('change', function(e) {
          const file = this.files && this.files[0];
          if (!file) { $('#add_image_preview').hide().attr('src',''); return; }
          const url = URL.createObjectURL(file);
          $('#add_image_preview').attr('src', url).show();
      });

    // Handle form submission (use FormData to support file upload)
    $('#addEmployeeForm').on('submit', function (e) {
      e.preventDefault();

      const $btn = $('#submitEmployeeBtn');
      const $spinner = $btn.find('.spinner-border');

      // Show loading state
      $btn.prop('disabled', true);
      $spinner.removeClass('d-none');

      // Build FormData
      const form = document.getElementById('addEmployeeForm');
      const formData = new FormData(form);

      $.ajax({
        url: '../ajax/add_employee.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let successMessage = 'Employee added successfully!<br>Work schedule has been created for the next 90 days.<br><br>';
            
            // Show credentials if they exist (from applicant conversion or new employee)
            if (response.username && response.password) {
              successMessage += `<strong>Login Credentials:</strong><br>
                           Username: <code>${response.username}</code><br>
                           Password: <code>${response.password}</code><br><br>
                           <small class="text-muted">Please save these credentials and share with the employee.</small>`;
            }
            
            // Show email status if converting from applicant
            if (response.email_sent !== undefined) {
              if (response.email_sent) {
                successMessage += '<br><br><small class="text-success"><i class="bx bx-check-circle"></i> Welcome email sent successfully!</small>';
              } else {
                successMessage += '<br><br><small class="text-warning"><i class="bx bx-error-circle"></i> Employee created but email could not be sent.</small>';
              }
            }
            
            Swal.fire({
              title: 'Success!',
              html: successMessage,
              icon: 'success',
              confirmButtonText: 'OK'
            });

            $('#addEmployeeModal').modal('hide');
            refreshEmployees(); // Refresh the employee list
          } else {
            Swal.fire('Error!', response.message, 'error');
          }
        },
        error: function (xhr, status, error) {
          $('#addEmployeeModal').modal('hide');
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

    function loadDepartmentsAndPositions() {
      $.ajax({
        url: '../ajax/get_dropdown_data.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            // Populate departments
            const $deptSelect = $('#departmentSelect');
            $deptSelect.find('option:not(:first)').remove();

            if (response.departments && response.departments.length > 0) {
              response.departments.forEach(dept => {
                $deptSelect.append(`<option value="${dept.id}">${dept.name}</option>`);
              });
            } else {
              $deptSelect.append('<option disabled>No departments available</option>');
            }

            // Populate positions
            const $posSelect = $('#positionSelect');
            $posSelect.find('option:not(:first)').remove();

            if (response.positions && response.positions.length > 0) {
              response.positions.forEach(pos => {
                $posSelect.append(`<option value="${pos.id}">${pos.name}</option>`);
              });
            } else {
              $posSelect.append('<option disabled>No positions available</option>');
            }
          } else {
            console.error('Failed to load dropdown data:', response.message);
            Swal.fire('Warning', 'Could not load departments and positions. You can still add the employee without selecting these.', 'warning');
          }
        },
        error: function (xhr, status, error) {
          console.error('Failed to load departments and positions:', error);
          Swal.fire('Warning', 'Could not load departments and positions. You can still add the employee without selecting these.', 'warning');
        }
      });

      // Load branches
      loadBranches();

      // Load allowances
      loadAllowances();
      
      // Load deductions (non-dynamic only)
      loadDeductions();
    }

    function loadAllowances() {
      $.ajax({
        url: '../ajax/get_allowance_types.php',
        type: 'GET',
        dataType: 'json',
        success: function (allowances) {
          const $container = $('#allowancesContainer');
          
          if (allowances && allowances.length > 0) {
            let html = '';
            allowances.forEach(allowance => {
              if (allowance.is_active == 1) {
                html += `
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input allowance-checkbox" 
                           name="allowances[]" value="${allowance.allowance_id}" 
                           id="allowance_${allowance.allowance_id}">
                    <label class="form-check-label" for="allowance_${allowance.allowance_id}">
                      ${allowance.allowance_type}
                      ${allowance.description ? `<small class="text-muted">- ${allowance.description}</small>` : ''}
                    </label>
                    <div class="mt-1 allowance-amount-container" id="allowance_amount_${allowance.allowance_id}" style="display: none;">
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
              $('.allowance-checkbox').on('change', function() {
                const allowanceId = $(this).val();
                const $amountContainer = $(`#allowance_amount_${allowanceId}`);
                
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
          console.error('Failed to load allowances:', error);
          $('#allowancesContainer').html('<div class="text-danger">Failed to load allowances</div>');
        }
      });
    }

    function loadDeductions() {
      $.ajax({
        url: '../ajax/get_deduction_types.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          const $container = $('#deductionsContainer');
          
          if (response.success && response.deduction_types && response.deduction_types.length > 0) {
            let html = '';
            response.deduction_types.forEach(deduction => {
              // Only show non-dynamic deductions (is_dynamic = 0)
              if (deduction.is_active == 1 && deduction.is_dynamic == 0) {
                html += `
                  <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input deduction-checkbox" 
                           name="deductions[]" value="${deduction.deduction_type_id}" 
                           id="deduction_${deduction.deduction_type_id}">
                    <label class="form-check-label" for="deduction_${deduction.deduction_type_id}">
                      ${deduction.type_name}
                      <small class="text-muted">(${deduction.amount_type || 'Fixed'})</small>
                    </label>
                    <div class="mt-1 deduction-amount-container" id="deduction_amount_${deduction.deduction_type_id}" style="display: none;">
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
              $('.deduction-checkbox').on('change', function() {
                const deductionId = $(this).val();
                const $amountContainer = $(`#deduction_amount_${deductionId}`);
                
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
          console.error('Failed to load deductions:', error);
          $('#deductionsContainer').html('<div class="text-danger">Failed to load deductions</div>');
        }
      });
    }

    function loadBranches(selectedBranchId = null) {
      $.ajax({
        url: '../ajax/get_branches.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          if (response.success && response.branches) {
            const $branchSelect = $('#branchSelect');
            $branchSelect.find('option:not(:first)').remove();

            if (response.branches.length > 0) {
              response.branches.forEach(branch => {
                if (branch.is_active == 1) {
                  const selected = selectedBranchId && branch.id == selectedBranchId ? 'selected' : '';
                  $branchSelect.append(`<option value="${branch.id}" ${selected}>${branch.name} (${branch.code})</option>`);
                }
              });
            }
          } else {
            console.warn('No branches available');
          }
        },
        error: function (xhr, status, error) {
          console.error('Failed to load branches:', error);
        }
      });
    }

  }); // End document ready
</script>
