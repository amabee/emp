<div class="modal fade" id="addDeductionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Deduction Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addDeductionForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Deduction Name</label>
            <input type="text" name="name" class="form-control" required />
          </div>

          <div class="row">
            <div class="mb-3 col-md-6">
              <label class="form-label">Amount</label>
              <input type="number" step="0.01" name="amount" class="form-control" value="0.00" required />
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
            <select name="apply_to" id="addDeduction_applyToSelect" class="form-select" required>
              <option value="all">All Employees</option>
              <option value="department">Specific Department</option>
              <option value="position">Specific Position</option>
              <option value="employee">Specific Employee</option>
            </select>
          </div>

          <div id="applyToExtras">
            <div class="mb-3 d-none" id="applyDepartment">
              <label class="form-label">Select Department</label>
              <select name="department_id" class="form-select" id="addDeduction_departmentSelect">
                <option value="">-- Select Department --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="applyPosition">
              <label class="form-label">Select Position</label>
              <select name="position_id" class="form-select" id="addDeduction_positionSelect">
                <option value="">-- Select Position --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="applyEmployee">
              <label class="form-label">Select Employee(s)</label>
              <div class="d-flex mb-2">
                <select id="addDeduction_employeeSelect" class="form-select me-2" aria-label="Select employee to add">
                  <option value="">-- Select Employee --</option>
                </select>
                <button type="button" class="btn btn-outline-primary" id="addDeduction_addEmployeeBtn">Add</button>
              </div>
              <div id="addDeduction_selectedEmployees" class="d-flex flex-wrap gap-2">
                <!-- badges inserted here -->
              </div>
            </div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_statutory" id="addDeduction_isStatutory" value="1">
            <label class="form-check-label" for="addDeduction_isStatutory">Is Statutory</label>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_dynamic" id="addDeduction_isDynamic" value="1">
            <label class="form-check-label" for="addDeduction_isDynamic">Dynamic Calculation</label>
            <div class="form-text">If enabled, deduction will be calculated dynamically and not assigned to individual employees</div>
          </div>

          <!-- Notes removed per request -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  $(function () {
    // load dropdowns when modal is shown
    $('#addDeductionModal').on('show.bs.modal', function () {
      console && console.time && console.time('addDeductionModal:show');
      // quick main-thread blocking probes
      try {
        const t0 = performance.now();
        // measure when the event loop yields
        setTimeout(function () {
          const t1 = performance.now();
          console && console.log && console.log('addDeductionModal: time to timeout (ms):', (t1 - t0).toFixed(2));
          console && console.timeEnd && console.timeEnd('addDeductionModal:show');
        }, 0);
        // measure two rAFs to wait for paint/layout
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            const t2 = performance.now();
            console && console.log && console.log('addDeductionModal: rAF2 delta (ms):', (t2 - t0).toFixed(2));
          });
        });
      } catch (e) {
        // ignore if performance or rAF not available
      }
      loadDepartmentsForAdd();
      loadPositionsForAdd();
      // don't load all employees by default (can be heavy) — only load when user specifically requests
      // reset form
      $('#addDeductionForm')[0].reset();
      $('#applyDepartment,#applyPosition,#applyEmployee').addClass('d-none');
    });

    // show/hide extra selects based on apply_to
    $('#addDeduction_applyToSelect').on('change', function () {
      const val = $(this).val();
      $('#applyDepartment,#applyPosition,#applyEmployee').addClass('d-none');
      if (val === 'department') $('#applyDepartment').removeClass('d-none');
      if (val === 'position') $('#applyPosition').removeClass('d-none');
      if (val === 'employee') $('#applyEmployee').removeClass('d-none');
      // If user chooses to apply to a specific employee, fetch employees (async)
      if (val === 'employee') {
        loadEmployeesForAdd();
      }
    });

    // when department or position changes, load employees for that filter
    $('#addDeduction_departmentSelect').on('change', function () {
      const deptId = $(this).val();
      if (!deptId) return;
      loadEmployeesByDepartment(deptId);
    });

    $('#addDeduction_positionSelect').on('change', function () {
      const posId = $(this).val();
      if (!posId) return;
      loadEmployeesByPosition(posId);
    });

    // Handle dynamic checkbox - disable Apply To section when checked
    $('#addDeduction_isDynamic').on('change', function () {
      const isDynamic = $(this).is(':checked');
      const $applyToSection = $('#addDeduction_applyToSelect, #addDeduction_departmentSelect, #addDeduction_positionSelect, #addDeduction_employeeSelect');
      
      if (isDynamic) {
        $applyToSection.prop('disabled', true);
        $('#addDeduction_applyToSelect').val('all'); // Reset to 'all'
        $('#applyDepartment, #applyPosition, #applyEmployee').addClass('d-none');
      } else {
        $applyToSection.prop('disabled', false);
      }
    });

    $('#addDeductionForm').on('submit', function (e) {
      e.preventDefault();
      const data = $(this).serialize();
      $.post('../ajax/add_deduction.php', data, function (res) {
        if (res && res.success) {
          $('#addDeductionModal').modal('hide');
          refreshDeductions();
          Swal.fire('Success', 'Deduction type added', 'success');
        } else {
          $('#addDeductionModal').modal('hide');
          console.log('addDeduction response error:', res.status);
          Swal.fire('Error', res.message || 'Failed to add deduction', 'error');
        }
      }, 'json').fail(function () {

        Swal.fire('Error', 'Server error while adding deduction', 'error');

      });
    });

    function loadDepartmentsForAdd() {
      // populate department select
      const $d = $('#addDeduction_departmentSelect');
      $d.prop('disabled', true).html('<option value="">Loading departments...</option>');
      console && console.time && console.time('addDeduction:loadDepartments');
      $.getJSON('../ajax/get_departments.php', function (res) {
        console && console.timeEnd && console.timeEnd('addDeduction:loadDepartments');
        let html = '<option value="">-- Select Department --</option>';
        if (res && res.success && Array.isArray(res.data)) {
          res.data.forEach(function (dep) {
            const id = dep.department_id || dep.id || dep.departmentId || '';
            const name = dep.name || dep.department_name || dep.departmentName || '';
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $d.html(html).prop('disabled', false);
      }).fail(function () {
        $d.html('<option value="">Failed to load departments</option>').prop('disabled', false);
      });
    }

    function loadPositionsForAdd() {
      const $p = $('#addDeduction_positionSelect');
      $p.prop('disabled', true).html('<option value="">Loading positions...</option>');
      console && console.time && console.time('addDeduction:loadPositions');
      $.getJSON('../ajax/get_positions.php', function (res) {
        console && console.timeEnd && console.timeEnd('addDeduction:loadPositions');
        let html = '<option value="">-- Select Position --</option>';
        if (res && res.success && Array.isArray(res.data)) {
          res.data.forEach(function (pos) {
            const id = pos.position_id || pos.id || pos.positionId || '';
            const name = pos.name || pos.position_name || pos.positionName || '';
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $p.html(html).prop('disabled', false);
      }).fail(function () {
        $p.html('<option value="">Failed to load positions</option>').prop('disabled', false);
      });
    }

    function loadEmployeesForAdd() {
      const $e = $('#addDeduction_employeeSelect');
      $e.prop('disabled', true).html('<option value="">Loading employees...</option>');
      console && console.time && console.time('addDeduction:loadEmployees');
      $.getJSON('../ajax/get_employees.php', function (res) {
        console && console.timeEnd && console.timeEnd('addDeduction:loadEmployees');
        let html = '<option value="">-- Select Employee --</option>';
        if (res && res.employees && Array.isArray(res.employees)) {
          res.employees.forEach(function (emp) {
            const id = emp.id || emp.employee_id || emp.employeeId || '';
            const name = emp.name || `${emp.first_name} ${emp.last_name}` || emp.email || '';
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $e.html(html).prop('disabled', false);
      }).fail(function () {
        $e.html('<option value="">Failed to load employees</option>').prop('disabled', false);
      });
    }

    function loadEmployeesByDepartment(deptId) {
      const $e = $('#addDeduction_employeeSelect');
      $e.prop('disabled', true).html('<option value="">Loading employees...</option>');
      console && console.time && console.time('addDeduction:loadEmployeesByDepartment');
      $.getJSON(`../ajax/get_employees_by_department.php?department_id=${deptId}`, function (res) {
        console && console.timeEnd && console.timeEnd('addDeduction:loadEmployeesByDepartment');
        let html = '<option value="">-- Select Employee --</option>';
        if (res && res.success && Array.isArray(res.employees)) {
          res.employees.forEach(function (emp) {
            const id = emp.employee_id || emp.id || '';
            const name = emp.first_name ? `${emp.first_name} ${emp.last_name || ''}`.trim() : (emp.name || emp.email || '');
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $e.html(html).prop('disabled', false);
      }).fail(function () {
        $e.html('<option value="">Failed to load employees</option>').prop('disabled', false);
      });
    }

    function loadEmployeesByPosition(posId) {
      const $e = $('#addDeduction_employeeSelect');
      $e.prop('disabled', true).html('<option value="">Loading employees...</option>');
      console && console.time && console.time('addDeduction:loadEmployeesByPosition');
      $.getJSON(`../ajax/get_employees_by_position.php?position_id=${posId}`, function (res) {
        console && console.timeEnd && console.timeEnd('addDeduction:loadEmployeesByPosition');
        let html = '<option value="">-- Select Employee --</option>';
        if (res && res.success && Array.isArray(res.employees)) {
          res.employees.forEach(function (emp) {
            const id = emp.employee_id || emp.id || '';
            const name = emp.first_name ? `${emp.first_name} ${emp.last_name || ''}`.trim() : (emp.name || emp.email || '');
            html += `<option value="${id}">${name}</option>`;
          });
        }
        $e.html(html).prop('disabled', false);
      }).fail(function () {
        $e.html('<option value="">Failed to load employees</option>').prop('disabled', false);
      });
    }

    // Handle add/remove employee badges for add modal
    $(document).on('click', '#addDeduction_addEmployeeBtn', function () {
      const $sel = $('#addDeduction_employeeSelect');
      const val = $sel.val();
      const text = $sel.find('option:selected').text();
      if (!val) return;
      if ($('#addDeduction_selectedEmployees').find(`[data-emp-id="${val}"]`).length) return;
      const $badge = $(`<span class="badge bg-primary text-white" data-emp-id="${val}" style="padding: .5rem .6rem;">${text} <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-emp" aria-label="Remove"></button></span>`);
      $('#addDeduction_selectedEmployees').append($badge);
      $sel.val(''); // Reset selection
    });

    $(document).on('click', '#addDeduction_selectedEmployees .remove-emp', function () { 
      $(this).closest('[data-emp-id]').remove(); 
    });

  });
</script>
