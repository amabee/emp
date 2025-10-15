<?php
$page_title = 'Employee Management';
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

// Check role permissions
requireRole(['admin', 'hr', 'supervisor']);

// Determine if user can modify (only admin and hr)
$canModify = canModify();
$viewOnly = isSupervisor();

ob_start();
?>

<div class="row">
  <!-- Welcome Card -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Employee Management System 👥</h5>
            <p class="mb-4">
              <?php if ($viewOnly): ?>
                View employee information and details from this dashboard. 
                <span class="badge bg-info">View Only Access</span>
              <?php else: ?>
                Manage employee information, departments, and employment details from this centralized dashboard.
              <?php endif; ?>
            </p>
            <?php if ($canModify): ?>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bx bx-plus-circle me-1"></i>Add New Employee
              </button>
            <?php else: ?>
              <button class="btn btn-sm btn-outline-info" disabled>
                <i class="bx bx-info-circle me-1"></i>View Only Mode
              </button>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/employee.jpg" height="170" alt="Employee Management">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Employees List -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-list-ul me-2"></i>Employees</h5>
        <div class="d-flex gap-2">
          <select class="form-select form-select-sm w-px-150" id="departmentFilter">
            <option value="">All Departments</option>
            <!-- Will be populated via AJAX -->
          </select>
          <input type="text" class="form-control form-control-sm w-px-200" id="searchEmployee"
            placeholder="Search employees...">
          <button class="btn btn-sm btn-primary" onclick="refreshEmployees()">
            <i class="bx bx-refresh me-1"></i>Refresh
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Position</th>
                <th>Branch</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="employeesTableBody">
              <!-- Will be populated via AJAX -->
            </tbody>
          </table>
        </div>
        <!-- Employees Pagination -->
        <nav aria-label="Employees pagination" id="employeesPaginationNav" class="d-none">
          <ul class="pagination pagination-sm justify-content-center" id="employeesPagination">
            <!-- Will be populated via JavaScript -->
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<?php
// Include the modals with correct path
include 'modals/add-employee-modal.php';
include 'modals/edit-employee-modal.php';
include 'modals/view-employee-modal.php';
?>

<script>
// Wait for jQuery to be available
(function waitForjQuery() {
    if (typeof $ !== 'undefined') {
        // jQuery is loaded, proceed with initialization
        initEmployeeManagement();
    } else {
        // jQuery not loaded yet, wait a bit and try again
        setTimeout(waitForjQuery, 50);
    }
})();

// Global variables for pagination
let employeesData = [];
const EMPLOYEES_PER_PAGE = 5;
let currentEmployeePage = 1;

function loadEmployees(page = 1) {
    const department = $('#departmentFilter').val();
    const search = $('#searchEmployee').val();

    $.ajax({
      url: '../ajax/get_employees.php',
      type: 'GET',
      dataType: 'json',
      data: { page, department, search },
      success: function (result) {
        employeesData = result.employees || [];
        updateEmployeesTable(result.employees);
        updateDepartments(result.departments);
      }
    });
  }

  function updateEmployeesTable(employees) {
    // Calculate pagination
    const totalItems = employees ? employees.length : 0;
    const totalPages = Math.ceil(totalItems / EMPLOYEES_PER_PAGE);
    const startIndex = (currentEmployeePage - 1) * EMPLOYEES_PER_PAGE;
    const endIndex = startIndex + EMPLOYEES_PER_PAGE;
    const paginatedEmployees = employees ? employees.slice(startIndex, endIndex) : [];

    let html = '';
    if (paginatedEmployees && paginatedEmployees.length > 0) {
      console.log(paginatedEmployees);
      paginatedEmployees.forEach(emp => {
        html += `
            <tr data-employee-id="${emp.id}">
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <img src="${emp.image}" class="rounded-circle">
                        </div>
                        <div>
                            <strong>${emp.name}</strong><br>
                            <small class="text-muted">${emp.email || 'No email'}</small>
                        </div>
                    </div>
                </td>
                <td>${emp.department || 'No Department'}</td>
                <td>${emp.position || 'No Position'}</td>
                <td>
                    ${emp.branch && emp.branch !== 'No Branch' ? `<span class="badge bg-label-info"><i class="bx bx-building"></i> ${emp.branch}</span>` : '<span class="text-muted">No Branch</span>'}
                </td>
                <td>
                    <span class="badge bg-label-${emp.status === 'Active' ? 'success' : 'secondary'}">
                        ${emp.status}
                    </span>
                </td>
                <td class="text-center">
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0);" onclick="viewEmployee(${emp.id})">
                                <i class="bx bx-show me-1"></i> View
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="editEmployee(${emp.id})">
                                <i class="bx bx-edit-alt me-1"></i> Edit
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="deleteEmployee(${emp.id})">
                                <i class="bx bx-trash me-1"></i> Delete
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        `;
      });
    } else {
      html = `
        <tr>
          <td colspan="5" class="text-center py-4">
            <div class="text-muted">
              <i class="bx bx-user-x bx-lg mb-2"></i><br>
              No employees found
            </div>
          </td>
        </tr>
      `;
    }
    $('#employeesTableBody').html(html);
    updateEmployeesPagination(totalPages, totalItems);
  }

  function updateDepartments(departments) {
    const $filter = $('#departmentFilter');
    const currentValue = $filter.val();
    
    // Keep the "All Departments" option and clear others
    $filter.find('option:not(:first)').remove();
    
    if (departments && departments.length > 0) {
      departments.forEach(dept => {
        $filter.append(`<option value="${dept.id}">${dept.name}</option>`);
      });
    } else {
      // If no departments from main query, try to load them separately
      loadDepartmentsForFilter();
    }
    
    // Restore the selected value if it still exists
    $filter.val(currentValue);
  }

  function loadDepartmentsForFilter() {
    $.ajax({
      url: '../ajax/get_dropdown_data.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success && response.departments) {
          const $filter = $('#departmentFilter');
          // Clear existing options except the first one (All Departments)
          $filter.find('option:not(:first)').remove();
          response.departments.forEach(dept => {
            $filter.append(`<option value="${dept.id}">${dept.name}</option>`);
          });
        }
      },
      error: function () {
        console.error('Failed to load departments for filter');
      }
    });
  }

  function refreshEmployees() {
    currentEmployeePage = 1;
    loadEmployees(currentEmployeePage);
  }

  function viewEmployee(id) {
    $.get('../ajax/get_employee.php', { id }, function (res) {
      if (!res || !res.success) {
        Swal.fire('Error!', res && res.message ? res.message : 'Failed to load employee', 'error');
        return;
      }
      const emp = res.employee || {};
      // Populate view modal with employee data
      $('#view_employee_name').text((emp.first_name || '') + ' ' + (emp.last_name || ''));
      $('#view_employee_position').text(emp.position_name || emp.position || '');
      $('#view_email').text(emp.email || '');
      $('#view_contact_number').text(emp.contact_number || '');
      $('#view_department').text(emp.department_name || emp.department || '');
      $('#view_status').text(emp.employment_status == 1 ? 'Active' : 'Inactive');
      $('#view_gender').text(emp.gender || '');
      $('#view_birthdate').text(emp.birthdate || '');
      $('#view_basic_salary').text(emp.basic_salary ? parseFloat(emp.basic_salary).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '');
      if (emp.image) {
        $('#view_employee_photo').attr('src', emp.image);
      } else {
        $('#view_employee_photo').attr('src', '');
      }
      $('#viewEmployeeModal').modal('show');
    }, 'json');
  }

  function editEmployee(id) {
    $.get('../ajax/get_employee.php', { id }, function (result) {
      if (result.success) {
        const emp = result.employee;
        // Populate edit form with employee data
        populateEditModal(emp);
        $('#editEmployeeModal').modal('show');
      } else {
        Swal.fire('Error!', result.message, 'error');
      }
    }, 'json').fail(function() {
      Swal.fire('Error!', 'Failed to load employee data', 'error');
    });
  }

  function deleteEmployee(id) {
    Swal.fire({
      title: 'Are you sure?',
      text: "This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('../ajax/delete_employee.php', { id }, function (result) {
          if (result.status === 'success') {
            Swal.fire('Deleted!', 'Employee has been deleted.', 'success');
            refreshEmployees();
          } else {
            Swal.fire('Error!', result.message, 'error');
          }
        }, 'json');
      }
    });
  }

function initEmployeeManagement() {
  $(document).ready(function() {
    // Initialize when page loads
    loadEmployees();

    $('#departmentFilter, #searchEmployee').on('change keyup', function () {
      refreshEmployees();
    });
  });
}

// === PAGINATION FUNCTIONS ===
function updateEmployeesPagination(totalPages, totalItems) {
  if (totalPages <= 1) {
    $('#employeesPaginationNav').addClass('d-none');
    return;
  }

  $('#employeesPaginationNav').removeClass('d-none');
  let paginationHtml = '';

  // Previous button
  paginationHtml += `
    <li class="page-item ${currentEmployeePage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="javascript:void(0);" onclick="changeEmployeePage(${currentEmployeePage - 1})">
        <i class="tf-icon bx bx-chevrons-left"></i>
      </a>
    </li>
  `;

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= currentEmployeePage - 1 && i <= currentEmployeePage + 1)) {
      paginationHtml += `
        <li class="page-item ${i === currentEmployeePage ? 'active' : ''}">
          <a class="page-link" href="javascript:void(0);" onclick="changeEmployeePage(${i})">${i}</a>
        </li>
      `;
    } else if (i === currentEmployeePage - 2 || i === currentEmployeePage + 2) {
      paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
  }

  // Next button
  paginationHtml += `
    <li class="page-item ${currentEmployeePage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="javascript:void(0);" onclick="changeEmployeePage(${currentEmployeePage + 1})">
        <i class="tf-icon bx bx-chevrons-right"></i>
      </a>
    </li>
  `;

  // Add page info
  const startItem = (currentEmployeePage - 1) * EMPLOYEES_PER_PAGE + 1;
  const endItem = Math.min(currentEmployeePage * EMPLOYEES_PER_PAGE, totalItems);
  paginationHtml += `
    <li class="page-item disabled">
      <span class="page-link text-muted small">Showing ${startItem}-${endItem} of ${totalItems}</span>
    </li>
  `;

  $('#employeesPagination').html(paginationHtml);
}

function changeEmployeePage(page) {
  if (page >= 1 && page <= Math.ceil(employeesData.length / EMPLOYEES_PER_PAGE)) {
    currentEmployeePage = page;
    updateEmployeesTable(employeesData);
  }
}
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

