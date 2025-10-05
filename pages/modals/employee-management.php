<?php
$page_title = 'Employee Management';
$additional_css = [];
$additional_js = [];

include '../../shared/session_handler.php';
require_once '../../includes/permissions.php';

if (!isset($user_id)) {
  header('Location: ../../login.php');
  exit();
}

// Get user's role permissions
$user_role = $user_type;
$permissions = can_manage_employees($user_role);

ob_start();
?>

<div class="row">
  <!-- Welcome Card -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Employee Management 👥</h5>
            <p class="mb-4">
              <?php if ($user_role === 'employee'): ?>
                View and manage your employee profile and information.
              <?php else: ?>
                Manage employee information, departments, and employment details.
              <?php endif; ?>
            </p>
            <?php if (in_array('add', $permissions)): ?>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bx bx-plus-circle me-1"></i>Add New Employee
              </button>
            <?php endif; ?>
          </div>
        </div>
        // ...existing card code...
      </div>
    </div>
  </div>

  <!-- Employees List -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-list-ul me-2"></i>Employees</h5>
        <?php if ($user_role !== 'employee'): ?>
          <div class="d-flex gap-2">
            <?php if (in_array('view_all', $permissions)): ?>
              <select class="form-select form-select-sm w-px-150" id="departmentFilter">
                <option value="">All Departments</option>
              </select>
            <?php endif; ?>
            <input type="text" class="form-control form-control-sm w-px-200" id="searchEmployee"
              placeholder="Search employees...">
            <button class="btn btn-sm btn-primary" onclick="refreshEmployees()">
              <i class="bx bx-refresh me-1"></i>Refresh
            </button>
          </div>
        <?php endif; ?>
      </div>
      // ...existing table code...
    </div>
  </div>
</div>

<?php
// Include modals based on permissions
if (in_array('add', $permissions)) {
  include 'modals/add-employee-modal.php';
}
if (in_array('edit', $permissions)) {
  include 'modals/edit-employee-modal.php';
}
include 'modals/view-employee-modal.php';
?>

<script>
  const userRole = '<?php echo $user_role; ?>';
  const userPermissions = <?php echo json_encode($permissions); ?>;

  // ...existing JavaScript code...

  function updateEmployeesTable(employees) {
    let html = '';
    employees.forEach(emp => {
      const actions = [];

      // Add actions based on permissions
      if (userPermissions.includes('view')) {
        actions.push(`
                <a class="dropdown-item" href="javascript:void(0);" onclick="viewEmployee(${emp.id})">
                    <i class="bx bx-show me-1"></i> View
                </a>
            `);
      }
      if (userPermissions.includes('edit')) {
        actions.push(`
                <a class="dropdown-item" href="javascript:void(0);" onclick="editEmployee(${emp.id})">
                    <i class="bx bx-edit-alt me-1"></i> Edit
                </a>
            `);
      }
      if (userPermissions.includes('delete')) {
        actions.push(`
                <a class="dropdown-item" href="javascript:void(0);" onclick="deleteEmployee(${emp.id})">
                    <i class="bx bx-trash me-1"></i> Delete
                </a>
            `);
      }

      html += `
            <tr data-employee-id="${emp.id}">
                // ...existing row code...
                <td class="text-center">
                    ${actions.length > 0 ? `
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                ${actions.join('')}
                            </div>
                        </div>
                    ` : ''}
                </td>
            </tr>
        `;
    });
    $('#employeesTableBody').html(html);
  }

  // ...rest of existing JavaScript code...
</script>

<?php
$content = ob_get_clean();
include '../../shared/layout.php';
?>

