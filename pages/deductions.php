<?php
$page_title = 'Deductions';
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
            <h5 class="card-title text-primary mb-3">Deductions 💰</h5>
            <p class="mb-4">Manage deduction types and assign deductions to employees from here.</p>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeductionModal">
              <i class="bx bx-plus-circle me-1"></i>Add Deduction Type
            </button>
          </div>
        </div>
        <div class="col-sm-4 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/deductions.png" height="140" alt="Deductions">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-credit-card-front me-2"></i>Deduction Types</h5>
        <div class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm w-px-250" id="searchDeduction"
            placeholder="Search deduction types...">
          <button class="btn btn-sm btn-primary" onclick="refreshDeductions()"><i
              class="bx bx-refresh me-1"></i>Refresh</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-borderless">
            <thead>
              <tr>
                  <th>Name</th>
                  <th>Amount Type</th>
                  <th class="text-center">Statutory</th>
                  <th class="text-center">Dynamic</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="deductionsTableBody">
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
        <div>
          <!-- Badge that shows selected deduction or prompt -->
          <span id="selectedDeductionLabel" class="badge bg-secondary" role="button" tabindex="0" aria-pressed="false"
            aria-live="polite"
            title="Select a deduction to view assigned employees. Click to clear selection when one is selected.">
            Select a deduction type to view assigned employees
          </span>
        </div>
      </div>
        <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <input type="text" id="searchAssignedEmployees" class="form-control form-control-sm w-px-250" placeholder="Search assigned employees...">
          <div></div>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-striped" id="assignedEmployeesTable">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Email</th>
                <th>Deduction Type</th>
                <th>Amount</th>
                <th>Amount Type</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="assignedEmployeesBody">
              <tr>
                <td colspan="6" class="text-center text-muted">No deduction selected</td>
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
include 'modals/add-deduction-modal.php';
include 'modals/edit-deduction-modal.php';
?>

<script>
  // Wait for jQuery then initialize
  (function waitForjQuery() {
    if (typeof $ !== 'undefined') {
      initDeductions();
    } else {
      setTimeout(waitForjQuery, 50);
    }
  })();

  let deductionsData = [];

  function initDeductions() {
    $(document).ready(function () {
      loadDeductions();

      $('#searchDeduction').on('keyup', function () {
        refreshDeductions();
      });

      $('#searchAssignedEmployees').on('keyup', function () {
        const q = $(this).val().toLowerCase();
        $('#assignedEmployeesBody tr').each(function () {
          const txt = $(this).text().toLowerCase();
          if (txt.indexOf(q) === -1) $(this).hide(); else $(this).show();
        });
      });
    });
  }

  function loadDeductions() {
    $.ajax({
      url: '../ajax/get_deduction_types.php',
      type: 'GET',
      dataType: 'json',
      success: function (res) {
        let items = [];
        if (res && Array.isArray(res)) {
          items = res;
        } else if (res && res.deduction_types && Array.isArray(res.deduction_types)) {
          items = res.deduction_types;
        } else if (res && res.deductions && Array.isArray(res.deductions)) {
          items = res.deductions;
        }

        deductionsData = items.map(function (it) {
          const statRaw = it.is_statutory || it.isStatutory || it.statutory || '';
          const statStr = String(statRaw).toLowerCase();
          const is_statutory = (statStr === '1' || statStr === 'true');
          const dynRaw = it.is_dynamic || it.isDynamic || it.dynamic || '';
          const dynStr = String(dynRaw).toLowerCase();
          const is_dynamic = (dynStr === '1' || dynStr === 'true');
          return {
            deduction_type_id: it.deduction_type_id || it.id || it.DEDUCTION_TYPE_ID,
            type_name: it.type_name || it.name || it.typeName || it.TYPE_NAME || '',
            description: it.description || '',
            amount_type: it.amount_type,
            default_amount: it.default_amount,
            is_active: it.is_active ? true : false,
            statutory: it.statutory,
            is_statutory: is_statutory,
            is_dynamic: is_dynamic,
            created_at: it.created_at,
            updated_at: it.updated_at
          };
        });

        updateDeductionsTable(deductionsData);
      },
      error: function () {
        $('#deductionsTableBody').html('<tr><td colspan="6" class="text-center text-muted py-4">Failed to load deductions</td></tr>');
      }
    });
  }

  function updateDeductionsTable(items) {
    const search = $('#searchDeduction').val().toLowerCase();
    const filtered = items.filter(i => (i.type_name || '').toLowerCase().includes(search) || (i.description || '').toLowerCase().includes(search));

    if (!filtered || filtered.length === 0) {
      $('#deductionsTableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bx bx-info-circle"></i> No deduction types found</td></tr>');
      return;
    }

    let html = '';
      filtered.forEach(d => {
      // pick the most specific amount available
      const amt = (d.employee_amount !== null && d.employee_amount !== undefined) ? d.employee_amount : d.default_amount;
      const mapToLabel = function (raw) {
        if (!raw) return '—';
        const r = String(raw).toLowerCase();
        return (r.indexOf('percent') !== -1 || r.indexOf('%') !== -1 || r.indexOf('percent') !== -1) ? 'PERCENTAGE' : 'FIXED';
      };
      const amountTypeLabel = mapToLabel(d.amount_type);
      const statutoryLabel = d.is_statutory ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-secondary">No</span>';
      const dynamicLabel = d.is_dynamic ? '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-secondary">No</span>';
      html += `
      <tr data-deduction-id="${d.deduction_type_id}">
        <td style="cursor: pointer;"><strong>${d.type_name}</strong></td>
        <td style="cursor: pointer;">${amountTypeLabel}</td>
        <td class="text-center">${statutoryLabel}</td>
        <td class="text-center">${dynamicLabel}</td>
        <td class="text-center">
          ${d.is_active
          ? '<span class="badge bg-success">Active</span>'
          : '<span class="badge bg-danger">Inactive</span>'}
        </td>
        <td class="text-center">
          <div class="dropdown">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="javascript:void(0);" onclick="editDeduction(${d.deduction_type_id})"><i class="bx bx-edit-alt me-1"></i> Edit</a>
              <a class="dropdown-item" href="javascript:void(0);" onclick="loadAssignedEmployees(${d.deduction_type_id}); $('#selectedDeductionLabel').text('Assigned employees for: ${d.type_name}');"><i class="bx bx-user me-1"></i> View Employees</a>
              <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteDeduction(${d.deduction_type_id})"><i class="bx bx-trash me-1"></i> Delete</a>
            </div>
          </div>
        </td>
      </tr>
    `;
    });

    $('#deductionsTableBody').html(html);
    // bind click to load assigned employees when a row is clicked
    $('#deductionsTableBody').off('click').on('click', 'tr', function () {
      const id = $(this).data('deduction-id');
      if (id) {
        // highlight selected row
        $('#deductionsTableBody tr').removeClass('table-active');
        $(this).addClass('table-active');
        const label = $(this).find('td:first strong').text();
        setSelectedDeductionLabel(label);
        loadAssignedEmployees(id);
      }
    });
  }

  function setSelectedDeductionLabel(label) {
    const $lbl = $('#selectedDeductionLabel');
    if (!label) {
      $lbl.text('Select a deduction type to view assigned employees');
      $lbl.removeClass('bg-primary text-white bg-success bg-danger').addClass('bg-secondary');
      $lbl.attr('aria-pressed', 'false');
      return;
    }
    $lbl.text('Assigned employees for: ' + label);
    $lbl.removeClass('bg-secondary').addClass('bg-primary text-white');
    $lbl.attr('aria-pressed', 'true');
  }

  // Make the badge clickable/keyboard accessible to clear the selection
  $(document).on('click', '#selectedDeductionLabel', function () {
    const pressed = $(this).attr('aria-pressed') === 'true';
    if (pressed) {
      // clear selection
      $('#deductionsTableBody tr').removeClass('table-active');
      setSelectedDeductionLabel(null);
      $('#assignedEmployeesBody').html('<tr><td colspan="6" class="text-center text-muted">No deduction selected</td></tr>');
    }
  });
  $(document).on('keydown', '#selectedDeductionLabel', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      $(this).trigger('click');
    }
  });

  // Add action buttons for assigned employees and delegated handlers

  function refreshDeductions() {
    loadDeductions();
  }

  function editDeduction(id) {
    const item = deductionsData.find(d => parseInt(d.deduction_type_id) === parseInt(id));
    if (!item) return;

    // Set form values using the correct field names from the modal
    $('#editDeductionModal input[name="deduction_type_id"]').val(item.deduction_type_id);
    $('#editDeductionModal input[name="name"]').val(item.type_name);
    $('#editDeductionModal input[name="default_amount"]').val(item.default_amount || '');
    $('#editDeductionModal select[name="amount_type"]').val(item.amount_type);
    
    // Set checkboxes - convert to boolean for clarity
    $('#editDeduction_isStatutory').prop('checked', Boolean(Number(item.statutory)));
    $('#editDeduction_isDynamic').prop('checked', Boolean(Number(item.is_dynamic)));
    
    // Reset apply_to selections initially
    $('#editDeduction_applyToSelect').val('all');
    $('#editApplyDepartment, #editApplyPosition, #editApplyEmployee').addClass('d-none');
    
    // Clear selected employees badges
    $('#editDeduction_selectedEmployees').empty();
    
    // Get current assignments to determine apply_to type
    $.get('../ajax/get_deduction_employees.php', { deduction_type_id: id }, function (res) {
      console.log('Deduction assignments response:', res);
      if (res && res.success && Array.isArray(res.employees) && res.employees.length > 0) {
        const employees = res.employees;
        console.log('Processing employees:', employees);
        
        // Check if all employees have the same department
        const departments = [...new Set(employees.map(emp => emp.department_id).filter(d => d))];
        const positions = [...new Set(employees.map(emp => emp.position_id).filter(p => p))];
        
        console.log('Departments found:', departments);
        console.log('Positions found:', positions);
        
        if (departments.length === 1 && employees.every(emp => emp.department_id === departments[0])) {
          // All employees are from the same department
          console.log('Setting apply_to to department:', departments[0]);
          $('#editDeduction_applyToSelect').val('department');
          $('#editApplyDepartment').removeClass('d-none');
          setTimeout(() => {
            $('#editDeduction_departmentSelect').val(departments[0]);
          }, 200);
        } else if (positions.length === 1 && employees.every(emp => emp.position_id === positions[0])) {
          // All employees are from the same position
          console.log('Setting apply_to to position:', positions[0]);
          $('#editDeduction_applyToSelect').val('position');
          $('#editApplyPosition').removeClass('d-none');
          setTimeout(() => {
            $('#editDeduction_positionSelect').val(positions[0]);
          }, 200);
        } else if (employees.length > 0) {
          // Mixed or specific employees
          console.log('Setting apply_to to employee');
          $('#editDeduction_applyToSelect').val('employee');
          $('#editApplyEmployee').removeClass('d-none');
          
          // Clear existing badges
          $('#editDeduction_selectedEmployees').empty();
          
          // Add badges for each assigned employee
          setTimeout(() => {
            employees.forEach(function(emp) {
              const empId = emp.employee_id;
              const empName = emp.name;
              if (empId && empName) {
                const $badge = $(`<span class="badge bg-primary text-white" data-emp-id="${empId}" style="padding: .5rem .6rem;">${empName} <button type="button" class="btn-close btn-close-white btn-sm ms-2 remove-emp" aria-label="Remove"></button></span>`);
                $('#editDeduction_selectedEmployees').append($badge);
              }
            });
          }, 200);
        }
      } else {
        console.log('No employees found or response not successful');
      }
    }, 'json').fail(function (xhr, status, error) {
      // ignore failures — modal still opens
      console.log('Failed to load deduction assignments:', error);
    }).always(function () {
      $('#editDeductionModal').modal('show');
    });
  }

  function deleteDeduction(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will remove the deduction type (soft delete if supported).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
      }).then(function (result) {
        if (result.isConfirmed) {
          $.post('../ajax/delete_deduction.php', { id }, function (res) {
            if (res && res.success) {
              refreshDeductions();
              Swal.fire('Deleted!', 'Deduction type deleted', 'success');
            } else {
              Swal.fire('Error!', (res && res.message) ? res.message : 'Failed to delete deduction', 'error');
            }
          }, 'json').fail(function () { Swal.fire('Error!', 'Server error while deleting deduction', 'error'); });
        }
      });
    } else {
      if (!confirm('Are you sure you want to delete this deduction type?')) return;
      $.post('../ajax/delete_deduction.php', { id }, function (res) {
        if (res && res.success) {
          refreshDeductions();
          alert('Deduction type deleted');
        } else {
          alert((res && res.message) ? res.message : 'Failed to delete deduction');
        }
      }, 'json');
    }
  }

  // Load employees assigned to a deduction type
  function loadAssignedEmployees(deductionId) {
    $('#assignedEmployeesBody').html('<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>');
    $.ajax({
      url: '../ajax/get_deduction_employees.php',
      type: 'GET',
      data: { deduction_type_id: deductionId },
      dataType: 'json',
      success: function (res) {
        if (!res || !res.success || !Array.isArray(res.employees)) {
          $('#assignedEmployeesBody').html('<tr><td colspan="6" class="text-center text-muted">No employees found or failed to load</td></tr>');
          return;
        }
        if (res.employees.length === 0) {
          $('#assignedEmployeesBody').html('<tr><td colspan="6" class="text-center text-muted">No employees assigned to this deduction</td></tr>');
          return;
        }
        let rows = '';
        res.employees.forEach(emp => {
          const typeLevel = emp.deduction_type_amount_type || emp.amount_type || null;
          const aType = typeLevel ? (String(typeLevel).toLowerCase().indexOf('percent') !== -1 ? 'Percentage' : 'Fixed') : '—';
          const amt = (emp.amount !== null && typeof emp.amount !== 'undefined') ? parseFloat(emp.amount).toFixed(2) : '—';
          const dedName = emp.deduction_type_name || '';
          rows += `<tr data-employee-id="${emp.employee_id}" data-assignment-id="${emp.employee_deduction_id || ''}">
                      <td>${emp.name}</td>
                      <td>${emp.email}</td>
                      <td>${dedName}</td>
                      <td class="assignment-amount">${amt}</td>
                      <td>${aType}</td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-primary edit-assignment" title="Edit assignment" data-assignment-id="${emp.employee_deduction_id || ''}" data-employee-id="${emp.employee_id}">
                          <i class="bx bx-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger remove-assignment ms-1" title="Remove assignment" data-assignment-id="${emp.employee_deduction_id || ''}">
                          <i class="bx bx-trash"></i>
                        </button>
                      </td>
                    </tr>`;
        });
        $('#assignedEmployeesBody').html(rows);
      },
      error: function () {
        $('#assignedEmployeesBody').html('<tr><td colspan="6" class="text-center text-muted">Server error while loading employees</td></tr>');
      }
    });
  }

  // delegated handler for view-employee button
  $(document).on('click', '.view-employee', function () {
    const empId = $(this).data('employee-id');
    if (typeof viewEmployee === 'function') {
      viewEmployee(empId);
      return;
    }
    if (empId) window.location.href = './pages/employee-management.php?employee_id=' + empId;
  });

  // delegated handler for remove-assignment button using SweetAlert2 confirmation + toast
  $(document).on('click', '.remove-assignment', function () {
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

    const confirmOpts = {
      title: 'Remove assignment?',
      text: 'This will remove the deduction assignment from the employee.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, remove',
      cancelButtonText: 'Cancel'
    };

    const doRemove = function () {
      $.post('../ajax/delete_employee_deduction.php', { id: assignmentId }, function (res) {
        if (res && res.success) {
          $btn.closest('tr').fadeOut(150, function () { $(this).remove(); });
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: '<span style="font-size:1rem;">Success</span>',
              text: res.message || 'Assignment removed',
              showConfirmButton: false,
              timer: 3500,
              customClass: {
                popup: 'p-2',
                title: 'fs-6',
                content: 'fs-7'
              }
            });
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
      Swal.fire(confirmOpts).then(function (result) {
        if (result && result.isConfirmed) doRemove();
      });
    } else {
      if (confirm(confirmOpts.text)) doRemove();
    }
  });

  // delegated handler for edit-assignment button
  $(document).on('click', '.edit-assignment', function (e) {
    e.stopPropagation();
    const assignmentId = $(this).data('assignment-id');
    if (!assignmentId) {
      if (typeof Swal !== 'undefined') Swal.fire('Error', 'No assignment id available', 'error');
      else alert('No assignment id available');
      return;
    }

    // Fetch assignment details
    $.getJSON('../ajax/get_employee_assignment.php', { id: assignmentId })
      .done(function (res) {
        if (!res || !res.success) {
          if (typeof Swal !== 'undefined') Swal.fire('Error', res && res.message ? res.message : 'Failed to load assignment', 'error');
          else alert((res && res.message) ? res.message : 'Failed to load assignment');
          return;
        }
        const a = res.assignment;
        // create modal if not present
        if ($('#editAssignmentModal').length === 0) {
          const modalHtml = `
            <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Edit Assignment Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form id="editAssignmentForm">
                    <input type="hidden" name="employee_deduction_id" />
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" name="employee_name" readonly />
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Amount (leave empty to keep current)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" />
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>`;
          $('body').append(modalHtml);

          // submit handler (ensure single binding)
          $(document).off('submit', '#editAssignmentForm').on('submit', '#editAssignmentForm', function (e) {
            e.preventDefault();
            console && console.log && console.log('Submitting editAssignmentForm');
            const fd = $(this).serializeArray();
            const payload = {};
            fd.forEach(function (f) {
              if (f.name === 'amount' && (f.value === null || f.value === '')) return; // omit
              payload[f.name] = f.value;
            });
            $.post('../ajax/update_employee_assignment.php', payload, function (res) {
              if (res && res.success) {
                $('#editAssignmentModal').modal('hide');
                // update table cell if amount returned / provided
                const row = $(`#assignedEmployeesBody tr[data-assignment-id='${payload.employee_deduction_id}']`);
                if (row.length) {
                  if (payload.amount !== undefined) row.find('.assignment-amount').text(parseFloat(payload.amount).toFixed(2));
                  else if (res.assignment && res.assignment.amount !== undefined && res.assignment.amount !== null) row.find('.assignment-amount').text(parseFloat(res.assignment.amount).toFixed(2));
                }
                if (typeof Swal !== 'undefined') Swal.fire('Success', 'Assignment updated', 'success');
                else alert('Assignment updated');
              } else {
                if (typeof Swal !== 'undefined') Swal.fire('Error', res && res.message ? res.message : 'Failed to update assignment', 'error');
                else alert((res && res.message) ? res.message : 'Failed to update assignment');
              }
            }, 'json').fail(function () {
              if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error while updating assignment', 'error');
              else alert('Server error while updating assignment');
            });
          });
        }

        $('#editAssignmentForm input[name="employee_deduction_id"]').val(a.employee_deduction_id || assignmentId);
        $('#editAssignmentForm input[name="employee_name"]').val(a.employee_name || a.name || '');
        $('#editAssignmentForm input[name="amount"]').val(a.amount !== null && a.amount !== undefined ? parseFloat(a.amount).toFixed(2) : '');
        $('#editAssignmentModal').modal('show');
      })
      .fail(function () {
        if (typeof Swal !== 'undefined') Swal.fire('Error', 'Server error while loading assignment', 'error');
        else alert('Server error while loading assignment');
      });
  });
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

