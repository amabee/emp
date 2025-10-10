<?php
$page_title = 'Allowances';
$additional_css = [
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'
];
$additional_js = [
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js'
];

include './shared/session_handler.php';

if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}

ob_start();
?>

<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-8">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Allowances 💵</h5>
            <p class="mb-4">Manage allowance types and assign allowances to employees from here.</p>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAllowanceModal">
              <i class="bx bx-plus-circle me-1"></i>Add Allowance Type
            </button>
          </div>
        </div>
        <div class="col-sm-4 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/payments.png" height="140" alt="Allowances">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-credit-card-front me-2"></i>Allowance Types</h5>
        <div class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm w-px-250" id="searchAllowance"
            placeholder="Search allowance types...">
          <button class="btn btn-sm btn-primary" onclick="refreshAllowances()"><i
              class="bx bx-refresh me-1"></i>Refresh</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Active</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="allowancesTableBody">
              <!-- Populated by AJAX -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Assigned employees card -->
<div class="row mt-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-user me-2"></i>Assigned Employees</h5>
        <div class="d-flex gap-2 align-items-center">
          <input type="text" class="form-control form-control-sm w-px-250" id="searchAssignedAllowances" placeholder="Search assigned employees...">
          <!-- Badge that shows selected allowance or prompt -->
          <span id="selectedAllowanceLabel" class="badge bg-secondary" role="button" tabindex="0" aria-pressed="false"
            aria-live="polite"
            title="Select an allowance to view assigned employees. Click to clear selection when one is selected.">
            Select an allowance type to view assigned employees
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-striped" id="assignedAllowancesTable">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Email</th>
                <th>Allowance Type</th>
                <th>Amount</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="assignedAllowancesBody">
              <tr>
                <td colspan="6" class="text-center text-muted">No allowance selected</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// include modals
include 'modals/add-allowance-modal.php';
include 'modals/edit-allowance-modal.php';
?>

<script>
  (function waitForjQuery() {
    if (typeof $ !== 'undefined') {
      initAllowances();
    } else {
      setTimeout(waitForjQuery, 50);
    }
  })();

  let allowancesData = [];

  function initAllowances() {
    $(document).ready(function () {
      loadAllowances();

      $('#searchAllowance').on('keyup', function () {
        refreshAllowances();
      });
    });
  }

  function loadAllowances() {
    $.ajax({
      url: '../ajax/get_allowance_types.php',
      type: 'GET',
      dataType: 'json',
      success: function (res) {
        let items = [];
        if (res && Array.isArray(res)) {
          items = res;
        } else if (res && res.allowance_types && Array.isArray(res.allowance_types)) {
          items = res.allowance_types;
        } else if (res && res.allowances && Array.isArray(res.allowances)) {
          items = res.allowances;
        }

        allowancesData = items.map(function (it) {
          return {
            allowance_type_id: it.allowance_id || it.allowance_type_id || it.id || it.ALLOWANCE_TYPE_ID,
            name: it.allowance_type || it.type_name || it.name || it.typeName || it.TYPE_NAME || '',
            description: it.description || '',
            amount_type: it.amount_type,
            active: (it.is_active || it.is_active === '1' || it.is_active === 1) ? true : false,
          };
        });

        updateAllowancesTable(allowancesData);
      },
      error: function () {
        $('#allowancesTableBody').html('<tr><td colspan="4" class="text-center text-muted py-4">Failed to load allowances</td></tr>');
      }
    });
  }

  function updateAllowancesTable(items) {
    const search = $('#searchAllowance').val().toLowerCase();
    const filtered = items.filter(i => (i.name || '').toLowerCase().includes(search) || (i.description || '').toLowerCase().includes(search));

    if (!filtered || filtered.length === 0) {
      $('#allowancesTableBody').html('<tr><td colspan="3" class="text-center py-4 text-muted"><i class="bx bx-info-circle"></i> No allowance types found</td></tr>');
      return;
    }

    let html = '';
    filtered.forEach(d => {
      const mapToLabel = function (raw) {
        if (!raw) return '—';
        const r = String(raw).toLowerCase();
        return (r.indexOf('percent') !== -1 || r.indexOf('%') !== -1 || r.indexOf('percent') !== -1) ? 'PERCENTAGE' : 'FIXED';
      };
      const amountTypeLabel = mapToLabel(d.amount_type);
      const activeBadge = d.active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
      html += `
      <tr data-allowance-id="${d.allowance_type_id}">
        <td style="cursor: pointer;"><strong>${d.name}</strong></td>
        <td style="cursor: pointer;">${d.description || '&mdash;'}</td>
        <td class="text-center">${activeBadge}</td>
        <td class="text-center">
          <div class="dropdown">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="javascript:void(0);" onclick="editAllowance(${d.allowance_type_id})"><i class="bx bx-edit-alt me-1"></i> Edit</a>
              <a class="dropdown-item" href="javascript:void(0);" onclick="loadAssignedAllowances(${d.allowance_type_id}); $('#selectedAllowanceLabel').text('Assigned employees for: ${d.name}');"><i class="bx bx-user me-1"></i> View Employees</a>
              <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteAllowance(${d.allowance_type_id})"><i class="bx bx-trash me-1"></i> Delete</a>
            </div>
          </div>
        </td>
      </tr>
    `;
    });

    $('#allowancesTableBody').html(html);
    $('#allowancesTableBody').off('click').on('click', 'tr', function () {
      const id = $(this).data('allowance-id');
      if (id) {
        $('#allowancesTableBody tr').removeClass('table-active');
        $(this).addClass('table-active');
        const label = $(this).find('td:first strong').text();
        setSelectedAllowanceLabel(label);
        loadAssignedAllowances(id);
      }
    });
  }

  function setSelectedAllowanceLabel(label) {
    const $lbl = $('#selectedAllowanceLabel');
    if (!label) {
      $lbl.text('Select an allowance type to view assigned employees');
      $lbl.removeClass('bg-primary text-white bg-success bg-danger').addClass('bg-secondary');
      $lbl.attr('aria-pressed', 'false');
      return;
    }
    $lbl.text('Assigned employees for: ' + label);
    $lbl.removeClass('bg-secondary').addClass('bg-primary text-white');
    $lbl.attr('aria-pressed', 'true');
  }

  $(document).on('click', '#selectedAllowanceLabel', function () {
    const pressed = $(this).attr('aria-pressed') === 'true';
    if (pressed) {
      $('#allowancesTableBody tr').removeClass('table-active');
      setSelectedAllowanceLabel(null);
      $('#assignedAllowancesBody').html('<tr><td colspan="6" class="text-center text-muted">No allowance selected</td></tr>');
    }
  });
  $(document).on('keydown', '#selectedAllowanceLabel', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      $(this).trigger('click');
    }
  });

  function refreshAllowances() {
    loadAllowances();
  }

  function editAllowance(id) {
    const item = allowancesData.find(d => parseInt(d.allowance_type_id) === parseInt(id));
    if (!item) return;
    $('#editAllowanceModal input[name="allowance_type_id"]').val(item.allowance_type_id);
    $('#editAllowanceModal input[name="name"]').val(item.name);
    $('#editAllowanceModal textarea[name="description"]').val(item.description);
    const amountToUse = (item.employee_amount !== null && item.employee_amount !== undefined) ? item.employee_amount : item.default_amount;
    $('#editAllowanceModal input[name="amount"]').val(amountToUse !== null && amountToUse !== undefined ? amountToUse : '');
    const mapToFrontend = function (raw) {
      if (!raw) return 'fixed';
      const r = String(raw).toLowerCase();
      return (r.indexOf('percent') !== -1 || r.indexOf('%') !== -1) ? 'percentage' : 'fixed';
    };
    const aType = item.employee_amount_type ? mapToFrontend(item.employee_amount_type) : (item.default_amount_type ? mapToFrontend(item.default_amount_type) : 'fixed');
    $('#editAllowanceModal select[name="amount_type"]').val(aType);
    if (item.department_id) $('#editAllowanceModal select[name="department_id"]').val(item.department_id);
    if (item.position_id) $('#editAllowanceModal select[name="position_id"]').val(item.position_id);
    if (item.employee_id) $('#editAllowanceModal select[name="employee_id"]').val(item.employee_id);
    // reset apply_to selections (no reassignment by default)
    $('#editAllowance_applyToSelect').val('');
    $('#editAllowance_applyDepartment, #editAllowance_applyPosition, #editAllowance_applyEmployee').addClass('d-none');

    // pre-load pickers so user can pick if they choose to reassign
    loadDepartments('#editAllowance_departmentSelect');
    loadPositions('#editAllowance_positionSelect');
    loadEmployees('#editAllowance_employeeSelect');

    // clear previous selected badges
    $('#editAllowance_selectedEmployees').empty();

    // fetch assigned employees to prefill amount and selected badges
    $.get('../ajax/get_allowance_employees.php', { allowance_type_id: id }, function (res) {
      if (res && res.success && Array.isArray(res.employees)) {
        // prefill amount from first assigned employee (if present)
        if (res.employees.length > 0 && typeof res.employees[0].amount !== 'undefined' && res.employees[0].amount !== null) {
          $('#editAllowanceModal input[name="amount"]').val(parseFloat(res.employees[0].amount).toFixed(2));
        }

        // Analyze assignments to determine apply_to type
        if (res.employees.length > 0) {
          console.log('Allowance assignments response:', res);
          console.log('Processing employees:', res.employees);
          
          // Check if all employees have the same department
          const departments = [...new Set(res.employees.map(emp => emp.department_id).filter(d => d))];
          const positions = [...new Set(res.employees.map(emp => emp.position_id).filter(p => p))];
          
          console.log('Departments found:', departments);
          console.log('Positions found:', positions);
          
          if (departments.length === 1 && res.employees.every(emp => emp.department_id === departments[0])) {
            // All employees are from the same department
            console.log('Setting apply_to to department:', departments[0]);
            $('#editAllowance_applyToSelect').val('department');
            $('#editAllowance_applyDepartment').removeClass('d-none');
            setTimeout(() => {
              $('#editAllowance_departmentSelect').val(departments[0]);
            }, 200);
          } else if (positions.length === 1 && res.employees.every(emp => emp.position_id === positions[0])) {
            // All employees are from the same position
            console.log('Setting apply_to to position:', positions[0]);
            $('#editAllowance_applyToSelect').val('position');
            $('#editAllowance_applyPosition').removeClass('d-none');
            setTimeout(() => {
              $('#editAllowance_positionSelect').val(positions[0]);
            }, 200);
          } else {
            // Mixed or specific employees
            console.log('Setting apply_to to employee');
            $('#editAllowance_applyToSelect').val('employee');
            $('#editAllowance_applyEmployee').removeClass('d-none');
            
            // populate employee select and badges
            const $empSel = $('#editAllowance_employeeSelect');
            $empSel.empty().append('<option value="">-- Select Employee --</option>');
            res.employees.forEach(function (emp) {
              const idv = emp.employee_id || emp.id || emp.employeeId || emp.user_id || '';
              const name = emp.name || emp.full_name || emp.employee_name || (emp.first_name ? (emp.first_name + (emp.last_name ? ' ' + emp.last_name : '')) : emp.email || '');
              if (idv) $empSel.append('<option value="' + idv + '">' + name + '</option>');
              if (idv) {
                const $badge = $(`<span class="badge bg-primary text-white" data-emp-id="${idv}" style="padding: .5rem .6rem;">${name} <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-emp" aria-label="Remove"></button></span>`);
                $('#editAllowance_selectedEmployees').append($badge);
              }
            });
          }
        } else {
          console.log('No employees found in allowance response');
        }
      }
    }, 'json').fail(function () {
      // ignore failures — modal still opens
    }).always(function () {
      $('#editAllowanceModal').modal('show');
    });
  }

  function deleteAllowance(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will remove the allowance type (soft delete if supported).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
      }).then(function (result) {
        if (result.isConfirmed) {
          $.post('../ajax/delete_allowance.php', { id }, function (res) {
            if (res && res.success) {
              refreshAllowances();
              Swal.fire('Deleted!', 'Allowance type deleted', 'success');
            } else {
              Swal.fire('Error!', (res && res.message) ? res.message : 'Failed to delete allowance', 'error');
            }
          }, 'json').fail(function () { Swal.fire('Error!', 'Server error while deleting allowance', 'error'); });
        }
      });
    } else {
      if (!confirm('Are you sure you want to delete this allowance type?')) return;
      $.post('../ajax/delete_allowance.php', { id }, function (res) {
        if (res && res.success) {
          refreshAllowances();
          alert('Allowance type deleted');
        } else {
          alert((res && res.message) ? res.message : 'Failed to delete allowance');
        }
      }, 'json');
    }
  }

  // Load employees assigned to an allowance type
  function loadAssignedAllowances(allowanceId) {
    $('#assignedAllowancesBody').html('<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>');
    $.ajax({
      url: '../ajax/get_allowance_employees.php',
      type: 'GET',
      data: { allowance_type_id: allowanceId },
      dataType: 'json',
      success: function (res) {
        if (!res || !res.success || !Array.isArray(res.employees)) {
          $('#assignedAllowancesBody').html('<tr><td colspan="6" class="text-center text-muted">No employees found or failed to load</td></tr>');
          return;
        }
        if (res.employees.length === 0) {
          $('#assignedAllowancesBody').html('<tr><td colspan="6" class="text-center text-muted">No employees assigned to this allowance</td></tr>');
          return;
        }
        let rows = '';
        res.employees.forEach(emp => {
          const typeLevel = emp.allowance_type_amount_type || emp.amount_type || null;
          const aType = typeLevel ? (String(typeLevel).toLowerCase().indexOf('percent') !== -1 ? 'Percentage' : 'Fixed') : '—';
          const amt = (emp.amount !== null && typeof emp.amount !== 'undefined') ? parseFloat(emp.amount).toFixed(2) : '—';
          const dedName = emp.allowance_type_name || '';
          rows += `<tr data-employee-id="${emp.employee_id}" data-assignment-id="${emp.employee_allowance_id || ''}">
                      <td>${emp.name}</td>
                      <td>${emp.email}</td>
                      <td>${dedName}</td>
                      <td class="assignment-amount">${amt}</td>
                      <td class="text-center">
                        <div>
                          <button type="button" class="btn btn-sm btn-primary edit-assignment" title="Edit assignment" data-assignment-id="${emp.employee_allowance_id || ''}" data-employee-id="${emp.employee_id}">
                            <i class="bx bx-edit"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-danger remove-assignment ms-1" title="Remove assignment" data-assignment-id="${emp.employee_allowance_id || ''}">
                            <i class="bx bx-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>`;
        });
        $('#assignedAllowancesBody').html(rows);
        // apply current search filter if any
        const currentSearch = $('#searchAssignedAllowances').val() || '';
        if ($.trim(currentSearch) !== '') {
          $('#searchAssignedAllowances').trigger('keyup');
        }
      },
      error: function () {
        $('#assignedAllowancesBody').html('<tr><td colspan="6" class="text-center text-muted">Server error while loading employees</td></tr>');
      }
    });
  }

  // delegated handler for remove-assignment button using SweetAlert2 confirmation + toast
  $(document).on('click', '#assignedAllowancesTable .remove-assignment', function () {
    const $btn = $(this);
    const assignmentId = $btn.data('assignment-id');
    if (!assignmentId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'No assignment id available', 'error');
      } else {
        alert('No assignment id available');
      }
      return;
    }

    const doRemove = function () {
      $.post('../ajax/delete_employee_allowance.php', { id: assignmentId }, function (res) {
        if (res && res.success) {
          $btn.closest('tr').fadeOut(200, function () { $(this).remove(); });
          if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true });
            Toast.fire({ icon: 'success', title: res.message || 'Assignment removed' });
          } else {
            alert(res.message || 'Assignment removed');
          }
        } else {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Error', (res && res.message) ? res.message : 'Failed to remove assignment', 'error');
          } else {
            alert((res && res.message) ? res.message : 'Failed to remove assignment');
          }
        }
      }, 'json').fail(function () {
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'Server error while removing assignment', 'error');
        } else {
          alert('Server error while removing assignment');
        }
      });
    };

    if (typeof Swal !== 'undefined') {
      Swal.fire({ title: 'Remove assignment?', text: 'This will remove the allowance assignment from the employee.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, remove', cancelButtonText: 'Cancel' }).then(function (result) {
        if (result && result.isConfirmed) doRemove();
      });
    } else {
      if (confirm('Remove this allowance assignment from the employee?')) doRemove();
    }
  });

  // delegated handler for edit-assignment button: open modal to edit amount
  $(document).on('click', '#assignedAllowancesTable .edit-assignment', function () {
    const assignmentId = $(this).data('assignment-id');
    if (!assignmentId) return;
    // fetch assignment details
    $.get('../ajax/get_employee_allowance.php', { id: assignmentId }, function (res) {
      if (!res || !res.success || !res.assignment) {
        if (typeof Swal !== 'undefined') Swal.fire('Error', (res && res.message) ? res.message : 'Failed to fetch assignment', 'error');
        else alert((res && res.message) ? res.message : 'Failed to fetch assignment');
        return;
      }
      const a = res.assignment;
      // build modal if not exists
      if ($('#editAssignmentModal').length === 0) {
        const modalHtml = `
        <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header"><h5 class="modal-title">Edit Assignment</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
              <div class="modal-body">
                <form id="editAssignmentForm">
                  <input type="hidden" name="employee_allowance_id" />
                  <div class="mb-3"><label>Employee</label><div id="editAssignmentEmployee" class="fw-semibold"></div></div>
                  <div class="mb-3"><label for="editAssignmentAmount">Amount (leave empty to keep unchanged)</label><input type="text" class="form-control form-control-sm" name="amount" id="editAssignmentAmount" placeholder="Leave blank to keep current"></div>
                  <div class="text-end"><button type="submit" class="btn btn-primary btn-sm">Save</button></div>
                </form>
              </div>
            </div>
          </div>
        </div>`;
        $('body').append(modalHtml);
      }

      // fill modal
      $('#editAssignmentModal input[name="employee_allowance_id"]').val(a.employee_allowance_id || '');
      $('#editAssignmentEmployee').text((a.name || '') + (a.email ? ' (' + a.email + ')' : ''));
      // leave amount blank by default to indicate no change; but show placeholder with current
      $('#editAssignmentAmount').val('');
      $('#editAssignmentAmount').attr('placeholder', (typeof a.amount !== 'undefined' && a.amount !== null) ? parseFloat(a.amount).toFixed(2) : 'No amount');
      // show modal
      var m = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
      m.show();
    }, 'json').fail(function () {
      if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error while fetching assignment', 'error');
      else alert('Server error while fetching assignment');
    });
  });

  // client-side search/filter for assigned employees
  $(document).on('keyup', '#searchAssignedAllowances', function () {
    const q = ($(this).val() || '').toLowerCase().trim();
    if (!q) {
      $('#assignedAllowancesBody tr').show();
      return;
    }
    $('#assignedAllowancesBody tr').each(function () {
      const $tr = $(this);
      const text = ($tr.text() || '').toLowerCase();
      if (text.indexOf(q) !== -1) $tr.show(); else $tr.hide();
    });
  });

  // submit handler for edit assignment - uses update_employee_allowance.php
  $(document).on('submit', '#editAssignmentForm', function (e) {
    e.preventDefault();
    const $form = $(this);
    const btn = $form.find('button[type="submit"]');
    const orig = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
    let post = $form.serializeArray();
    // omit amount if empty
    post = post.filter(p => !(p.name === 'amount' && $.trim(p.value) === ''));
    $.post('../ajax/update_employee_allowance.php', $.param(post), function (res) {
      btn.prop('disabled', false).html(orig);
      if (res && res.success) {
        // update table cell for the assignment row
        const assign = res.assignment;
        if (assign && assign.employee_allowance_id) {
          const $row = $(`#assignedAllowancesBody tr[data-assignment-id='${assign.employee_allowance_id}']`);
          if ($row.length) {
            const amt = (typeof assign.amount !== 'undefined' && assign.amount !== null) ? parseFloat(assign.amount).toFixed(2) : '—';
            $row.find('.assignment-amount').text(amt);
          }
        }
        $('#editAssignmentModal').modal('hide');
        if (typeof Swal !== 'undefined') {
          const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true });
          Toast.fire({ icon: 'success', title: res.message || 'Assignment updated' });
        } else {
          alert(res.message || 'Assignment updated');
        }
      } else {
        if (typeof Swal !== 'undefined') Swal.fire('Error', (res && res.message) ? res.message : 'Failed to update assignment', 'error');
        else alert((res && res.message) ? res.message : 'Failed to update assignment');
      }
    }, 'json').fail(function () {
      btn.prop('disabled', false).html(orig);
      if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error while updating assignment', 'error');
      else alert('Server error while updating assignment');
    });
  });

  // Helpers to populate department/position/employee selects (used by add/edit modals)
  function loadDepartments(selectSelector) {
    $.get('../ajax/get_departments.php', function (res) {
      var list = [];
      if (!res) return;
      if (Array.isArray(res.departments)) list = res.departments;
      else if (Array.isArray(res.data)) list = res.data;
      else if (Array.isArray(res)) list = res;
      var $sel = $(selectSelector);
      $sel.empty().append('<option value="">-- Select Department --</option>');
      list.forEach(function (d) {
        const id = d.department_id || d.id || d.ID || '';
        const name = d.department_name || d.name || d.department || d.label || '';
        if (id) $sel.append('<option value="' + id + '">' + name + '</option>');
      });
    }, 'json');
  }

  function loadPositions(selectSelector) {
    $.get('../ajax/get_positions.php', function (res) {
      var list = [];
      if (!res) return;
      if (Array.isArray(res.positions)) list = res.positions;
      else if (Array.isArray(res.data)) list = res.data;
      else if (Array.isArray(res)) list = res;
      var $sel = $(selectSelector);
      $sel.empty().append('<option value="">-- Select Position --</option>');
      list.forEach(function (p) {
        const id = p.position_id || p.id || p.ID || '';
        const name = p.position_name || p.name || p.title || '';
        if (id) $sel.append('<option value="' + id + '">' + name + '</option>');
      });
    }, 'json');
  }

  function loadEmployees(selectSelector) {
    $.get('../ajax/get_employees.php', function (res) {
      var list = [];
      if (!res) return;
      if (Array.isArray(res.employees)) list = res.employees;
      else if (Array.isArray(res.data)) list = res.data;
      else if (Array.isArray(res)) list = res;
      var $sel = $(selectSelector);
      $sel.empty().append('<option value="">-- Select Employee --</option>');
      list.forEach(function (e) {
        const id = e.employee_id || e.id || e.user_id || '';
        const name = e.full_name || (e.first_name ? (e.first_name + (e.last_name ? ' ' + e.last_name : '')) : (e.name || e.email || ''));
        if (id) $sel.append('<option value="' + id + '">' + name + '</option>');
      });
    }, 'json');
  }

  function loadEmployeesByDepartment(selectSelector, departmentId) {
    if (!departmentId) return;
    $.get(`../ajax/get_employees_by_department.php?department_id=${departmentId}`, function (res) {
      var list = [];
      if (!res) return;
      if (Array.isArray(res.employees)) list = res.employees;
      else if (Array.isArray(res.data)) list = res.data;
      var $sel = $(selectSelector);
      $sel.empty().append('<option value="">-- Select Employee --</option>');
      list.forEach(function (e) {
        const id = e.employee_id || e.id || '';
        const name = e.first_name ? (e.first_name + (e.last_name ? ' ' + e.last_name : '')) : (e.name || e.email || '');
        if (id) $sel.append('<option value="' + id + '">' + name + '</option>');
      });
    }, 'json');
  }

  function loadEmployeesByPosition(selectSelector, positionId) {
    if (!positionId) return;
    $.get(`../ajax/get_employees_by_position.php?position_id=${positionId}`, function (res) {
      var list = [];
      if (!res) return;
      if (Array.isArray(res.employees)) list = res.employees;
      else if (Array.isArray(res.data)) list = res.data;
      var $sel = $(selectSelector);
      $sel.empty().append('<option value="">-- Select Employee --</option>');
      list.forEach(function (e) {
        const id = e.employee_id || e.id || '';
        const name = e.first_name ? (e.first_name + (e.last_name ? ' ' + e.last_name : '')) : (e.name || e.email || '');
        if (id) $sel.append('<option value="' + id + '">' + name + '</option>');
      });
    }, 'json');
  }

  // Wire the edit modal apply_to selection to show/hide extras
  $(document).on('change', '#editAllowance_applyToSelect', function () {
    var val = $(this).val();
    $('#editAllowance_applyDepartment, #editAllowance_applyPosition, #editAllowance_applyEmployee').addClass('d-none');
    if (val === 'department') $('#editAllowance_applyDepartment').removeClass('d-none');
    if (val === 'position') $('#editAllowance_applyPosition').removeClass('d-none');
    if (val === 'employee') $('#editAllowance_applyEmployee').removeClass('d-none');
  });

  // when department/position change in edit modal, load corresponding employees
  $(document).on('change', '#editAllowance_departmentSelect', function () {
    const deptId = $(this).val();
    if (!deptId) return;
    loadEmployeesByDepartment('#editAllowance_employeeSelect', deptId);
  });
  $(document).on('change', '#editAllowance_positionSelect', function () {
    const posId = $(this).val();
    if (!posId) return;
    loadEmployeesByPosition('#editAllowance_employeeSelect', posId);
  });

  // Add allowance form submit is handled inside the add modal file (to include selected employee badges)

  // Edit allowance form submit handler
  // handle add/remove badges in edit modal
  $(document).on('click', '#editAllowance_addEmployeeBtn', function () {
    const $sel = $('#editAllowance_employeeSelect');
    const val = $sel.val();
    const text = $sel.find('option:selected').text();
    if (!val) return;
    if ($('#editAllowance_selectedEmployees').find(`[data-emp-id="${val}"]`).length) return;
    const $badge = $(`<span class="badge bg-primary text-white" data-emp-id="${val}" style="padding: .5rem .6rem;">${text} <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-emp" aria-label="Remove"></button></span>`);
    $('#editAllowance_selectedEmployees').append($badge);
  });
  $(document).on('click', '#editAllowance_selectedEmployees .remove-emp', function () { $(this).closest('[data-emp-id]').remove(); });

  $(document).on('submit', '#editAllowanceForm', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const origHtml = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

    // serialize and rename allowance_type_id -> allowance_id for backend
    let postArr = $form.serializeArray();
    postArr = postArr.map(function (p) { if (p.name === 'allowance_type_id') p.name = 'allowance_id'; return p; });
    // ensure apply_to fields are included (may be empty)
    postArr.push({ name: 'apply_to', value: $('#editAllowance_applyToSelect').val() || '' });
    postArr.push({ name: 'department_id', value: $('#editAllowance_departmentSelect').val() || '' });
    postArr.push({ name: 'position_id', value: $('#editAllowance_positionSelect').val() || '' });
    // if applying to specific employees, include all selected badges as employee_id[]
    if ($('#editAllowance_applyToSelect').val() === 'employee') {
      $('#editAllowance_selectedEmployees [data-emp-id]').each(function () { postArr.push({ name: 'employee_id[]', value: $(this).attr('data-emp-id') }); });
    } else {
      postArr.push({ name: 'employee_id', value: $('#editAllowance_employeeSelect').val() || '' });
    }
    const postData = $.param(postArr);

    $.ajax({
      url: '../ajax/update_allowance.php',
      type: 'POST',
      data: postData,
      dataType: 'json'
    }).done(function (res) {
      $btn.prop('disabled', false).html(origHtml);
      if (res && res.success) {
        $('#editAllowanceModal').modal('hide');
        refreshAllowances();
        if (typeof Swal !== 'undefined') {
          const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true });
          Toast.fire({ icon: 'success', title: res.message || 'Allowance updated' });
        } else {
          alert(res.message || 'Allowance updated');
        }
      } else {
        if (typeof Swal !== 'undefined') Swal.fire('Error', (res && res.message) ? res.message : 'Failed to update allowance', 'error');
        else alert((res && res.message) ? res.message : 'Failed to update allowance');
      }
    }).fail(function () {
      $btn.prop('disabled', false).html(origHtml);
      if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error while updating allowance', 'error');
      else alert('Server error while updating allowance');
    });
  });
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

