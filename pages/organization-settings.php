<?php
$page_title = 'Organization Settings';
$additional_css = [];
$additional_js = [];

include './shared/session_handler.php';

if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}

// Restrict access to admin, supervisor, and HR only
if (!in_array($user_type, ['admin', 'supervisor', 'hr'])) {
  header('Location: ./index.php');
  exit();
}

ob_start();
?>

<div class="row">
  <!-- Welcome Card -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Organization Structure 🏢</h5>
            <p class="mb-4">
              Manage departments, positions, and organizational structure from this centralized dashboard.
            </p>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/organization.png" height="170" alt="Organization">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Department Management -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-buildings me-2"></i>Departments</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
          <i class="bx bx-plus me-1"></i>Add Department
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Department Name</th>
                <th>Head/Supervisor</th>
                <th>No. of Employees</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="departmentsTable">
              <!-- Will be populated via AJAX -->
            </tbody>
          </table>
        </div>
        <!-- Departments Pagination -->
        <nav aria-label="Departments pagination" id="departmentsPaginationNav" class="d-none">
          <ul class="pagination pagination-sm justify-content-center" id="departmentsPagination">
            <!-- Will be populated via JavaScript -->
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Position Management -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-user-pin me-2"></i>Positions</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPositionModal">
          <i class="bx bx-plus me-1"></i>Add Position
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Position Title</th>
                <th>Department</th>
                <th>No. of Employees</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="positionsTable">
              <!-- Will be populated via AJAX -->
            </tbody>
          </table>
        </div>
        <!-- Positions Pagination -->
        <nav aria-label="Positions pagination" id="positionsPaginationNav" class="d-none">
          <ul class="pagination pagination-sm justify-content-center" id="positionsPagination">
            <!-- Will be populated via JavaScript -->
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<?php
// Include modals with correct paths
include 'modals/add-department-modal.php';
include 'modals/edit-department-modal.php';
include 'modals/add-position-modal.php';
include 'modals/edit-position-modal.php';
include 'modals/branch_modals.php';
?>

<script>
  // Global variables for data
  let departmentsData = [];
  let positionsData = [];

  // Pagination settings
  const ITEMS_PER_PAGE = 5;
  let currentDepartmentPage = 1;
  let currentPositionPage = 1;

  // Load data on page load
  $(document).ready(function () {
    loadDepartments();
    loadPositions();
    loadEmployeesForDropdown();

    // Setup form handlers
    setupFormHandlers();
  });

  // === DEPARTMENT FUNCTIONS ===
  function loadDepartments() {
    $.ajax({
      url: '../ajax/get_departments.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          departmentsData = response.data;
          updateDepartmentsTable(response.data);
          updateDepartmentDropdowns();
        } else {
          showToast('Error loading departments: ' + response.message, 'error');
        }
      },
      error: function () {
        showToast('Failed to load departments', 'error');
      }
    });
  }

  function updateDepartmentsTable(departments) {
    // Calculate pagination
    const totalItems = departments.length;
    const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
    const startIndex = (currentDepartmentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = startIndex + ITEMS_PER_PAGE;
    const paginatedDepartments = departments.slice(startIndex, endIndex);

    let html = '';
    if (paginatedDepartments.length === 0) {
      html = '<tr><td colspan="5" class="text-center text-muted">No departments found</td></tr>';
    } else {
      paginatedDepartments.forEach(dept => {
        const statusClass = dept.status === 'Active' ? 'success' : 'secondary';
        html += `
                <tr>
                    <td><strong>${dept.name}</strong></td>
                    <td>${dept.head_name || '<span class="text-muted">Not Assigned</span>'}</td>
                    <td><span class="badge bg-label-info">${dept.employee_count}</span></td>
                    <td><span class="badge bg-label-${statusClass}">${dept.status}</span></td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" onclick="editDepartment(${dept.id})">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteDepartment(${dept.id}, '${dept.name}')">
                                    <i class="bx bx-trash me-1"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
      });
    }
    $('#departmentsTable').html(html);
    updateDepartmentsPagination(totalPages, totalItems);
  }

  function addDepartment() {
    $('#addDepartmentModal').modal('show');
  }

  function editDepartment(id) {
    $.ajax({
      url: '../ajax/get_department.php?id=' + id,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          const dept = response.data;
          $('#edit-dept-id').val(dept.id);
          $('#edit-dept-name').val(dept.name);
          $('#edit-dept-head').val(dept.department_head_id || '');
          $('#edit-dept-status').val(dept.status);

          // Populate department head dropdown
          populateEmployeeDropdown('#edit-dept-head', response.employees, dept.department_head_id);

          $('#editDepartmentModal').modal('show');
        } else {
          showToast('Error loading department: ' + response.message, 'error');
        }
      },
      error: function () {
        showToast('Failed to load department details', 'error');
      }
    });
  }

  function deleteDepartment(id, name) {
    if (confirm(`Are you sure you want to delete the department "${name}"? This action cannot be undone.`)) {
      $.ajax({
        url: '../ajax/delete_department.php',
        type: 'POST',
        data: { department_id: id },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showToast(response.message, 'success');
            loadDepartments();
            loadPositions(); // Refresh positions as they may be affected
          } else {
            showToast(response.message, 'error');
          }
        },
        error: function () {
          showToast('Failed to delete department', 'error');
        }
      });
    }
  }

  // === POSITION FUNCTIONS ===
  function loadPositions() {
    $.ajax({
      url: '../ajax/get_positions.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          positionsData = response.data;
          updatePositionsTable(response.data);
        } else {
          showToast('Error loading positions: ' + response.message, 'error');
        }
      },
      error: function () {
        showToast('Failed to load positions', 'error');
      }
    });
  }

  function updatePositionsTable(positions) {
    // Calculate pagination
    const totalItems = positions.length;
    const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
    const startIndex = (currentPositionPage - 1) * ITEMS_PER_PAGE;
    const endIndex = startIndex + ITEMS_PER_PAGE;
    const paginatedPositions = positions.slice(startIndex, endIndex);

    let html = '';
    if (paginatedPositions.length === 0) {
      html = '<tr><td colspan="5" class="text-center text-muted">No positions found</td></tr>';
    } else {
      paginatedPositions.forEach(pos => {
        const statusClass = pos.status === 'Active' ? 'success' : 'secondary';
        html += `
                <tr>
                    <td><strong>${pos.title}</strong></td>
                    <td>${pos.department_name || '<span class="text-muted">No Department</span>'}</td>
                    <td><span class="badge bg-label-info">${pos.employee_count}</span></td>
                    <td><span class="badge bg-label-${statusClass}">${pos.status}</span></td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);" onclick="editPosition(${pos.id})">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deletePosition(${pos.id}, '${pos.title}')">
                                    <i class="bx bx-trash me-1"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
      });
    }
    $('#positionsTable').html(html);
    updatePositionsPagination(totalPages, totalItems);
  }

  function addPosition() {
    $('#addPositionModal').modal('show');
  }

  function editPosition(id) {
    $.ajax({
      url: '../ajax/get_position.php?id=' + id,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          const pos = response.data;
          $('#edit-pos-id').val(pos.id);
          $('#edit-pos-name').val(pos.title);
          $('#edit-pos-dept').val(pos.department_id || '');
          $('#edit-pos-status').val(pos.status);

          // Populate department dropdown
          populateDepartmentDropdown('#edit-pos-dept', response.departments, pos.department_id);

          $('#editPositionModal').modal('show');
        } else {
          showToast('Error loading position: ' + response.message, 'error');
        }
      },
      error: function () {
        showToast('Failed to load position details', 'error');
      }
    });
  }

  function deletePosition(id, title) {
    if (confirm(`Are you sure you want to delete the position "${title}"? This action cannot be undone.`)) {
      $.ajax({
        url: '../ajax/delete_position.php',
        type: 'POST',
        data: { position_id: id },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showToast(response.message, 'success');
            loadPositions();
          } else {
            showToast(response.message, 'error');
          }
        },
        error: function () {
          showToast('Failed to delete position', 'error');
        }
      });
    }
  }

  // === HELPER FUNCTIONS ===
  function loadEmployeesForDropdown() {
    // This is called when modals are opened to populate dropdowns
  }

  function updateDepartmentDropdowns() {
    const options = '<option value="">Select Department</option>' +
      departmentsData.map(dept => `<option value="${dept.id}">${dept.name}</option>`).join('');
    $('#add-pos-dept, #edit-pos-dept').html(options);
  }

  function populateEmployeeDropdown(selector, employees, selectedId) {
    let options = '<option value="">Select Department Head</option>';
    if (employees && employees.length > 0) {
      options += employees.map(emp =>
        `<option value="${emp.id}" ${emp.id == selectedId ? 'selected' : ''}>${emp.name}</option>`
      ).join('');
    }
    $(selector).html(options);
  }

  function populateDepartmentDropdown(selector, departments, selectedId) {
    let options = '<option value="">Select Department</option>';
    if (departments && departments.length > 0) {
      options += departments.map(dept =>
        `<option value="${dept.id}" ${dept.id == selectedId ? 'selected' : ''}>${dept.name}</option>`
      ).join('');
    }
    $(selector).html(options);
  }

  // === FORM HANDLERS ===
  function setupFormHandlers() {
    // Add Department Form
    $('#addDepartmentForm').on('submit', function (e) {
      e.preventDefault();
      const btn = $(this).find('button[type="submit"]');
      const spinner = btn.find('.spinner-border');

      btn.prop('disabled', true);
      spinner.removeClass('d-none');

      $.ajax({
        url: '../ajax/add_department.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showToast(response.message, 'success');
            $('#addDepartmentModal').modal('hide');
            $('#addDepartmentForm')[0].reset();
            loadDepartments();
          } else {
            showToast(response.message, 'error');
          }
        },
        error: function () {
          showToast('Failed to add department', 'error');
        },
        complete: function () {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
        }
      });
    });

    // Edit Department Form
    $('#editDepartmentForm').on('submit', function (e) {
      e.preventDefault();
      const btn = $(this).find('button[type="submit"]');
      const spinner = btn.find('.spinner-border');

      btn.prop('disabled', true);
      spinner.removeClass('d-none');

      $.ajax({
        url: '../ajax/update_department.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showToast(response.message, 'success');
            $('#editDepartmentModal').modal('hide');
            loadDepartments();
          } else {
            showToast(response.message, 'error');
          }
        },
        error: function () {
          showToast('Failed to update department', 'error');
        },
        complete: function () {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
        }
      });
    });

    // Add Position Form
    $('#addPositionForm').on('submit', function (e) {
      e.preventDefault();
      const btn = $(this).find('button[type="submit"]');
      const spinner = btn.find('.spinner-border');

      btn.prop('disabled', true);
      spinner.removeClass('d-none');

      $.ajax({
        url: '../ajax/add_position.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showToast(response.message, 'success');
            $('#addPositionModal').modal('hide');
            $('#addPositionForm')[0].reset();
            loadPositions();
          } else {
            showToast(response.message, 'error');
          }
        },
        error: function () {
          showToast('Failed to add position', 'error');
        },
        complete: function () {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
        }
      });
    });

    // Edit Position Form
    $('#editPositionForm').on('submit', function (e) {
      e.preventDefault();
      const btn = $(this).find('button[type="submit"]');
      const spinner = btn.find('.spinner-border');

      btn.prop('disabled', true);
      spinner.removeClass('d-none');

      $.ajax({
        url: '../ajax/update_position.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showToast(response.message, 'success');
            $('#editPositionModal').modal('hide');
            loadPositions();
          } else {
            showToast(response.message, 'error');
          }
        },
        error: function () {
          showToast('Failed to update position', 'error');
        },
        complete: function () {
          btn.prop('disabled', false);
          spinner.addClass('d-none');
        }
      });
    });

    // Load employees when add department modal is opened
    $('#addDepartmentModal').on('show.bs.modal', function () {
      $.ajax({
        url: '../ajax/get_department.php?id=0', // This will return employees list
        type: 'GET',
        dataType: 'json',
        success: function (response) {
          if (response.employees) {
            populateEmployeeDropdown('#add-dept-head', response.employees);
          }
        }
      });
    });

    // Load departments when add position modal is opened
    $('#addPositionModal').on('show.bs.modal', function () {
      updateDepartmentDropdowns();
    });
  }

  // Toast notification function
  function showToast(message, type = 'info') {
    // Create toast notification
    const toastClass = type === 'success' ? 'text-bg-success' : type === 'error' ? 'text-bg-danger' : 'text-bg-info';
    const toastId = 'toast_' + Date.now();

    const toast = `
        <div id="${toastId}" class="toast align-items-center ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    // Add to toast container or create one if it doesn't exist
    if (!$('#toastContainer').length) {
      $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
    }

    $('#toastContainer').append(toast);

    // Show toast
    const toastElement = new bootstrap.Toast(document.getElementById(toastId));
    toastElement.show();

    // Remove toast element after it's hidden
    $(`#${toastId}`).on('hidden.bs.toast', function () {
      $(this).remove();
    });
  }

  // === PAGINATION FUNCTIONS ===
  function updateDepartmentsPagination(totalPages, totalItems) {
    if (totalPages <= 1) {
      $('#departmentsPaginationNav').addClass('d-none');
      return;
    }

    $('#departmentsPaginationNav').removeClass('d-none');
    let paginationHtml = '';

    // Previous button
    paginationHtml += `
      <li class="page-item ${currentDepartmentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0);" onclick="changeDepartmentPage(${currentDepartmentPage - 1})">
          <i class="tf-icon bx bx-chevrons-left"></i>
        </a>
      </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= currentDepartmentPage - 1 && i <= currentDepartmentPage + 1)) {
        paginationHtml += `
          <li class="page-item ${i === currentDepartmentPage ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0);" onclick="changeDepartmentPage(${i})">${i}</a>
          </li>
        `;
      } else if (i === currentDepartmentPage - 2 || i === currentDepartmentPage + 2) {
        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
      }
    }

    // Next button
    paginationHtml += `
      <li class="page-item ${currentDepartmentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0);" onclick="changeDepartmentPage(${currentDepartmentPage + 1})">
          <i class="tf-icon bx bx-chevrons-right"></i>
        </a>
      </li>
    `;

    // Add page info
    const startItem = (currentDepartmentPage - 1) * ITEMS_PER_PAGE + 1;
    const endItem = Math.min(currentDepartmentPage * ITEMS_PER_PAGE, totalItems);
    paginationHtml += `
      <li class="page-item disabled">
        <span class="page-link text-muted small">Showing ${startItem}-${endItem} of ${totalItems}</span>
      </li>
    `;

    $('#departmentsPagination').html(paginationHtml);
  }

  function updatePositionsPagination(totalPages, totalItems) {
    if (totalPages <= 1) {
      $('#positionsPaginationNav').addClass('d-none');
      return;
    }

    $('#positionsPaginationNav').removeClass('d-none');
    let paginationHtml = '';

    // Previous button
    paginationHtml += `
      <li class="page-item ${currentPositionPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0);" onclick="changePositionPage(${currentPositionPage - 1})">
          <i class="tf-icon bx bx-chevrons-left"></i>
        </a>
      </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= currentPositionPage - 1 && i <= currentPositionPage + 1)) {
        paginationHtml += `
          <li class="page-item ${i === currentPositionPage ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0);" onclick="changePositionPage(${i})">${i}</a>
          </li>
        `;
      } else if (i === currentPositionPage - 2 || i === currentPositionPage + 2) {
        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
      }
    }

    // Next button
    paginationHtml += `
      <li class="page-item ${currentPositionPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0);" onclick="changePositionPage(${currentPositionPage + 1})">
          <i class="tf-icon bx bx-chevrons-right"></i>
        </a>
      </li>
    `;

    // Add page info
    const startItem = (currentPositionPage - 1) * ITEMS_PER_PAGE + 1;
    const endItem = Math.min(currentPositionPage * ITEMS_PER_PAGE, totalItems);
    paginationHtml += `
      <li class="page-item disabled">
        <span class="page-link text-muted small">Showing ${startItem}-${endItem} of ${totalItems}</span>
      </li>
    `;

    $('#positionsPagination').html(paginationHtml);
  }

  function changeDepartmentPage(page) {
    if (page >= 1 && page <= Math.ceil(departmentsData.length / ITEMS_PER_PAGE)) {
      currentDepartmentPage = page;
      updateDepartmentsTable(departmentsData);
    }
  }

  function changePositionPage(page) {
    if (page >= 1 && page <= Math.ceil(positionsData.length / ITEMS_PER_PAGE)) {
      currentPositionPage = page;
      updatePositionsTable(positionsData);
    }
  }
</script>

<!-- Include branch management script -->
<script src="../assets/js/branch-management.js"></script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

