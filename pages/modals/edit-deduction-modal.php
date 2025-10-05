<?php
// Modal: Edit Deduction Type (mirrors add modal UX but for editing)
?>
<div class="modal fade" id="editDeductionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Deduction Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editDeductionForm">
        <input type="hidden" name="deduction_type_id" value="" />
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Deduction Name</label>
            <input type="text" name="name" class="form-control" required />
          </div>

          <div class="row">
            <div class="mb-3 col-md-6">
              <label class="form-label">Amount</label>
              <!-- use 'default_amount' to align with server-side updateDeduction field name; allow empty to mean "do not change" -->
              <input type="number" step="0.01" name="default_amount" class="form-control" value="" />
            </div>
            <div class="mb-3 col-md-6">
              <label class="form-label">Amount Type</label>
              <select name="amount_type" class="form-select">
                <option value="fixed">Fixed Amount</option>
                <option value="percentage">Percentage</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Apply To</label>
            <select name="apply_to" id="editDeduction_applyToSelect" class="form-select" required>
              <option value="all">All Employees</option>
              <option value="department">Specific Department</option>
              <option value="position">Specific Position</option>
              <option value="employee">Specific Employee</option>
            </select>
          </div>

          <div id="editApplyToExtras">
            <div class="mb-3 d-none" id="editApplyDepartment">
              <label class="form-label">Select Department</label>
              <select name="department_id" class="form-select" id="editDeduction_departmentSelect">
                <option value="">-- Select Department --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="editApplyPosition">
              <label class="form-label">Select Position</label>
              <select name="position_id" class="form-select" id="editDeduction_positionSelect">
                <option value="">-- Select Position --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="editApplyEmployee">
              <label class="form-label">Select Employee(s)</label>
              <div class="d-flex mb-2">
                <select id="editDeduction_employeeSelect" class="form-select me-2" aria-label="Select employee to add">
                  <option value="">-- Select Employee --</option>
                </select>
                <button type="button" class="btn btn-outline-primary" id="editDeduction_addEmployeeBtn">Add</button>
              </div>
              <div id="editDeduction_selectedEmployees" class="d-flex flex-wrap gap-2">
                <!-- badges inserted here -->
              </div>
            </div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_statutory" id="editDeduction_isStatutory" value="1">
            <label class="form-check-label" for="editDeduction_isStatutory">Is Statutory</label>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_dynamic" id="editDeduction_isDynamic" value="1">
            <label class="form-check-label" for="editDeduction_isDynamic">Dynamic Calculation</label>
            <div class="form-text">If enabled, deduction will be calculated dynamically and not assigned to individual employees</div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  $(function () {
    // When the edit modal is shown, ensure dropdowns are available and pre-fill if values present
    $('#editDeductionModal').on('show.bs.modal', function () {
      // reset visibility
      $('#editApplyDepartment,#editApplyPosition,#editApplyEmployee').addClass('d-none');

      // load lists
      loadDepartmentsForEdit();
      loadPositionsForEdit();

      // if apply_to is 'employee' or department/position preselected, load employees as needed
      const applyTo = $('#editDeductionForm [name="apply_to"]').val();
      if (applyTo === 'employee') {
        loadEmployeesForEdit();
      }
      
      // Check dynamic checkbox state and disable Apply To section if needed
      setTimeout(function() {
        $('#editDeduction_isDynamic').trigger('change');
      }, 100);
    });

    $('#editDeduction_applyToSelect').on('change', function () {
      const val = $(this).val();
      $('#editApplyDepartment,#editApplyPosition,#editApplyEmployee').addClass('d-none');
      if (val === 'department') $('#editApplyDepartment').removeClass('d-none');
      if (val === 'position') $('#editApplyPosition').removeClass('d-none');
      if (val === 'employee') {
        $('#editApplyEmployee').removeClass('d-none');
        loadEmployeesForEdit();
      }
    });

    $('#editDeduction_departmentSelect').on('change', function () {
      const deptId = $(this).val();
      if (!deptId) return;
      loadEmployeesByDepartmentForEdit(deptId);
    });

    $('#editDeduction_positionSelect').on('change', function () {
      const posId = $(this).val();
      if (!posId) return;
      loadEmployeesByPositionForEdit(posId);
    });

    // Handle dynamic checkbox - disable Apply To section when checked
    $('#editDeduction_isDynamic').on('change', function () {
      const isDynamic = $(this).is(':checked');
      const $applyToSection = $('#editDeduction_applyToSelect, #editDeduction_departmentSelect, #editDeduction_positionSelect, #editDeduction_employeeSelect');
      
      if (isDynamic) {
        $applyToSection.prop('disabled', true);
        $('#editDeduction_applyToSelect').val('all'); // Reset to 'all'
        $('#editApplyDepartment, #editApplyPosition, #editApplyEmployee').addClass('d-none');
      } else {
        $applyToSection.prop('disabled', false);
      }
    });

    $('#editDeductionForm').on('submit', function (e) {
      e.preventDefault();
      let data = $(this).serialize();
      
      // Manually add checkbox values since unchecked checkboxes aren't included in serialize()
      const isStatutory = $('#editDeduction_isStatutory').is(':checked') ? '1' : '0';
      const isDynamic = $('#editDeduction_isDynamic').is(':checked') ? '1' : '0';
      data += '&is_statutory=' + isStatutory + '&is_dynamic=' + isDynamic;
      
      // Add selected employees from badges (for multiple employee selection)
      const selectedEmployees = [];
      $('#editDeduction_selectedEmployees [data-emp-id]').each(function () {
        selectedEmployees.push($(this).attr('data-emp-id'));
      });
      if (selectedEmployees.length > 0) {
        selectedEmployees.forEach(function(empId) {
          data += '&employee_id[]=' + encodeURIComponent(empId);
        });
      }
      
      $.post('../ajax/update_deduction.php', data, function (res) {
        if (res && res.success) {
          console.log("DATA PASSED: ", data)
          $('#editDeductionModal').modal('hide');
          refreshDeductions();
          Swal.fire('Success', 'Deduction type updated', 'success');
        } else {
          Swal.fire('Error', res.message || 'Failed to update deduction', 'error');
        }
      }, 'json').fail(function () {
        Swal.fire('Error', 'Server error while updating deduction', 'error');
      });
    });

    function loadDepartmentsForEdit() {
      const $d = $('#editDeduction_departmentSelect');
      $d.prop('disabled', true).html('<option value="">Loading departments...</option>');
      $.getJSON('../ajax/get_departments.php', function (res) {
        let html = '<option value="">-- Select Department --</option>';
        if (res && res.success && Array.isArray(res.data)) {
          res.data.forEach(function (dep) {
            const id = dep.department_id || dep.id || '';
            const name = dep.name || dep.department_name || '';
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $d.html(html).prop('disabled', false);

        // if a department is already selected, keep it selected
        const sel = $('#editDeductionForm [name="department_id"]').val();
        if (sel) $d.val(sel).trigger('change');
      }).fail(function () {
        $d.html('<option value="">Failed to load departments</option>').prop('disabled', false);
      });
    }

    function loadPositionsForEdit() {
      const $p = $('#editDeduction_positionSelect');
      $p.prop('disabled', true).html('<option value="">Loading positions...</option>');
      $.getJSON('../ajax/get_positions.php', function (res) {
        let html = '<option value="">-- Select Position --</option>';
        if (res && res.success && Array.isArray(res.data)) {
          res.data.forEach(function (pos) {
            const id = pos.position_id || pos.id || '';
            const name = pos.name || pos.position_name || '';
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $p.html(html).prop('disabled', false);

        const sel = $('#editDeductionForm [name="position_id"]').val();
        if (sel) $p.val(sel).trigger('change');
      }).fail(function () {
        $p.html('<option value="">Failed to load positions</option>').prop('disabled', false);
      });
    }

    function loadEmployeesForEdit() {
      const $e = $('#editDeduction_employeeSelect');
      $e.prop('disabled', true).html('<option value="">Loading employees...</option>');
      $.getJSON('../ajax/get_employees.php', function (res) {
        let html = '<option value="">-- Select Employee --</option>';
        if (res && res.employees && Array.isArray(res.employees)) {
          res.employees.forEach(function (emp) {
            const id = emp.id || emp.employee_id || '';
            const name = emp.name || `${emp.first_name} ${emp.last_name}` || emp.email || '';
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $e.html(html).prop('disabled', false);

        const sel = $('#editDeductionForm [name="employee_id"]').val();
        if (sel) $e.val(sel);
      }).fail(function () {
        $e.html('<option value="">Failed to load employees</option>').prop('disabled', false);
      });
    }

    function loadEmployeesByDepartmentForEdit(deptId) {
      const $e = $('#editDeduction_employeeSelect');
      $e.prop('disabled', true).html('<option value="">Loading employees...</option>');
      $.getJSON(`../ajax/get_employees_by_department.php?department_id=${deptId}`, function (res) {
        let html = '<option value="">-- Select Employee --</option>';
        if (res && res.success && Array.isArray(res.employees)) {
          res.employees.forEach(function (emp) {
            const id = emp.employee_id || emp.id || '';
            const name = emp.first_name ? `${emp.first_name} ${emp.last_name || ''}`.trim() : (emp.name || emp.email || '');
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $e.html(html).prop('disabled', false);

        const sel = $('#editDeductionForm [name="employee_id"]').val();
        if (sel) $e.val(sel);
      }).fail(function () {
        $e.html('<option value="">Failed to load employees</option>').prop('disabled', false);
      });
    }

    function loadEmployeesByPositionForEdit(posId) {
      const $e = $('#editDeduction_employeeSelect');
      $e.prop('disabled', true).html('<option value="">Loading employees...</option>');
      $.getJSON(`../ajax/get_employees_by_position.php?position_id=${posId}`, function (res) {
        let html = '<option value="">-- Select Employee --</option>';
        if (res && res.success && Array.isArray(res.employees)) {
          res.employees.forEach(function (emp) {
            const id = emp.employee_id || emp.id || '';
            const name = emp.first_name ? `${emp.first_name} ${emp.last_name || ''}`.trim() : (emp.name || emp.email || '');
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $e.html(html).prop('disabled', false);

        const sel = $('#editDeductionForm [name="employee_id"]').val();
        if (sel) $e.val(sel);
      }).fail(function () {
        $e.html('<option value="">Failed to load employees</option>').prop('disabled', false);
      });
    }

    // Handle add/remove employee badges for edit modal
    $(document).on('click', '#editDeduction_addEmployeeBtn', function () {
      const $sel = $('#editDeduction_employeeSelect');
      const val = $sel.val();
      const text = $sel.find('option:selected').text();
      if (!val) return;
      if ($('#editDeduction_selectedEmployees').find(`[data-emp-id="${val}"]`).length) return;
      const $badge = $(`<span class="badge bg-primary text-white" data-emp-id="${val}" style="padding: .5rem .6rem;">${text} <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-emp" aria-label="Remove"></button></span>`);
      $('#editDeduction_selectedEmployees').append($badge);
      $sel.val(''); // Reset selection
    });

    $(document).on('click', '#editDeduction_selectedEmployees .remove-emp', function () { 
      $(this).closest('[data-emp-id]').remove(); 
    });

  });
</script>
