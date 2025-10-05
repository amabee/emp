<div class="modal fade" id="addAllowanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Allowance Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addAllowanceForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Amount</label>
              <input type="number" step="0.01" class="form-control" name="amount">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Apply To</label>
            <select name="apply_to" id="addAllowance_applyToSelect" class="form-select" required>
              <option value="all">All Employees</option>
              <option value="department">Specific Department</option>
              <option value="position">Specific Position</option>
              <option value="employee">Specific Employee</option>
            </select>
          </div>

          <div id="addAllowance_applyToExtras">
            <div class="mb-3 d-none" id="addAllowance_applyDepartment">
              <label class="form-label">Select Department</label>
              <select name="department_id" class="form-select" id="addAllowance_departmentSelect">
                <option value="">-- Select Department --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="addAllowance_applyPosition">
              <label class="form-label">Select Position</label>
              <select name="position_id" class="form-select" id="addAllowance_positionSelect">
                <option value="">-- Select Position --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="addAllowance_applyEmployee">
              <label class="form-label">Select Employee(s)</label>
              <div class="d-flex mb-2">
                <select id="addAllowance_employeeSelect" class="form-select me-2" aria-label="Select employee to add">
                  <option value="">-- Select Employee --</option>
                </select>
                <button type="button" class="btn btn-outline-primary" id="addAllowance_addEmployeeBtn">Add</button>
              </div>
              <div id="addAllowance_selectedEmployees" class="d-flex flex-wrap gap-2">
                <!-- badges inserted here -->
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Allowance</button>
        </div>
      </form>
    </div>
  </div>
</div>

  <script>
    (function () {
      // initialize behavior for Add Allowance modal
      $(function () {
        $('#addAllowanceModal').on('show.bs.modal', function () {
          $('#addAllowanceForm')[0].reset();
          $('#addAllowance_applyDepartment,#addAllowance_applyPosition,#addAllowance_applyEmployee').addClass('d-none');
          loadDepartmentsForAddAllowance();
          loadPositionsForAddAllowance();
        });

        $('#addAllowance_applyToSelect').on('change', function () {
          const val = $(this).val();
          $('#addAllowance_applyDepartment,#addAllowance_applyPosition,#addAllowance_applyEmployee').addClass('d-none');
          if (val === 'department') $('#addAllowance_applyDepartment').removeClass('d-none');
          if (val === 'position') $('#addAllowance_applyPosition').removeClass('d-none');
          if (val === 'employee') {
            $('#addAllowance_applyEmployee').removeClass('d-none');
            loadEmployeesForAddAllowance();
          }
        });

        // manage adding employee badges for Add modal
        $(document).on('click', '#addAllowance_addEmployeeBtn', function () {
          const $sel = $('#addAllowance_employeeSelect');
          const val = $sel.val();
          const text = $sel.find('option:selected').text();
          if (!val) return;
          // avoid duplicates
          if ($('#addAllowance_selectedEmployees').find(`[data-emp-id="${val}"]`).length) return;
          const $badge = $(`<span class="badge bg-primary text-white" data-emp-id="${val}" style="padding: .5rem .6rem;">${text} <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-emp" aria-label="Remove"></button></span>`);
          $('#addAllowance_selectedEmployees').append($badge);
        });

        // remove employee badge
        $(document).on('click', '#addAllowance_selectedEmployees .remove-emp', function () { $(this).closest('[data-emp-id]').remove(); });

        $('#addAllowance_departmentSelect').on('change', function () {
          const deptId = $(this).val();
          if (!deptId) return;
          loadEmployeesByDepartmentForAddAllowance(deptId);
        });

        $('#addAllowance_positionSelect').on('change', function () {
          const posId = $(this).val();
          if (!posId) return;
          loadEmployeesByPositionForAddAllowance(posId);
        });

        function loadDepartmentsForAddAllowance() {
          const $d = $('#addAllowance_departmentSelect');
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
          }).fail(function () { $d.html('<option value="">Failed to load departments</option>').prop('disabled', false); });
        }

        function loadPositionsForAddAllowance() {
          const $p = $('#addAllowance_positionSelect');
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
          }).fail(function () { $p.html('<option value="">Failed to load positions</option>').prop('disabled', false); });
        }

        function loadEmployeesForAddAllowance() {
          const $e = $('#addAllowance_employeeSelect');
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
          }).fail(function () { $e.html('<option value="">Failed to load employees</option>').prop('disabled', false); });
        }

        function loadEmployeesByDepartmentForAddAllowance(deptId) {
          const $e = $('#addAllowance_employeeSelect');
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
          }).fail(function () { $e.html('<option value="">Failed to load employees</option>').prop('disabled', false); });
        }

        function loadEmployeesByPositionForAddAllowance(posId) {
          const $e = $('#addAllowance_employeeSelect');
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
          }).fail(function () { $e.html('<option value="">Failed to load employees</option>').prop('disabled', false); });
        }

        // submit handler (include selected employees if any)
        $('#addAllowanceForm').on('submit', function (e) {
          e.preventDefault();
          const $form = $(this);
          const $btn = $form.find('button[type="submit"]');
          const origHtml = $btn.html();
          $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

          let data = $form.serializeArray();
          if ($('#addAllowance_applyToSelect').val() === 'employee') {
            $('#addAllowance_selectedEmployees [data-emp-id]').each(function () {
              data.push({ name: 'employee_id[]', value: $(this).attr('data-emp-id') });
            });
          }

          $.ajax({ url: '../ajax/add_allowance.php', type: 'POST', data: $.param(data), dataType: 'json' })
            .done(function (res) {
              $btn.prop('disabled', false).html(origHtml);
              if (res && res.success) {
                $('#addAllowanceModal').modal('hide');
                $form[0].reset();
                $('#addAllowance_selectedEmployees').empty();
                if (window.loadAllowances) loadAllowances();
                if (typeof Swal !== 'undefined') {
                  const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true });
                  Toast.fire({ icon: 'success', title: res.message || 'Allowance added' });
                } else {
                  alert(res.message || 'Allowance added');
                }
              } else {
                if (typeof Swal !== 'undefined') Swal.fire('Error', (res && res.message) ? res.message : 'Failed to add allowance', 'error');
                else alert((res && res.message) ? res.message : 'Failed to add allowance');
              }
            }).fail(function () { $btn.prop('disabled', false).html(origHtml); if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error while adding allowance', 'error'); else alert('Server error while adding allowance'); });
        });
      });
    })();
  </script>
