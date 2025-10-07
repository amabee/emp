<?php
$page_title = 'Leave Management';
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
  <!-- Welcome Card -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Leave Management 🏖️</h5>
            <p class="mb-4">Manage employee leave requests, approvals, and leave balances. Track vacation days, sick leave, and other time-off requests.</p>
            <button class="btn btn-sm btn-primary" id="addLeaveBtn" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
              <i class="bx bx-plus-circle me-1"></i>New Leave Request
            </button>
            <button class="btn btn-sm btn-outline-secondary ms-2" id="exportLeaveBtn">Export Report</button>
            <button class="btn btn-sm btn-outline-info ms-2" id="leaveBalanceBtn" data-bs-toggle="modal" data-bs-target="#leaveBalanceModal">
              <i class="bx bx-calendar-alt me-1"></i>Leave Balances
            </button>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/leave-management.png" height="140" alt="Leave Management" style="max-width: 100%;">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="col-12 mb-4">
    <div class="row">
      <div class="col-md-3 col-6 mb-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">Pending Requests</span>
                <h3 id="statPending" class="card-title mb-0">0</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">Approved</span>
                <h3 id="statApproved" class="card-title mb-0">0</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">Rejected</span>
                <h3 id="statRejected" class="card-title mb-0">0</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-calendar"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">This Month</span>
                <h3 id="statThisMonth" class="card-title mb-0">0</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Leave Requests List -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-calendar-event me-2"></i>Leave Requests</h5>
        <div class="d-flex gap-2">
          <select class="form-select form-select-sm w-px-150" id="filterLeaveType">
            <option value="">All Types</option>
            <option value="vacation">Vacation</option>
            <option value="sick">Sick Leave</option>
            <option value="personal">Personal</option>
            <option value="emergency">Emergency</option>
          </select>
          <select class="form-select form-select-sm w-px-150" id="filterStatus">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
          <select class="form-select form-select-sm w-px-200" id="filterDepartment">
            <option value="">All Departments</option>
            <option>HR</option>
            <option>Engineering</option>
            <option>Sales</option>
            <option>Marketing</option>
            <option>Finance</option>
          </select>
          <input type="text" class="form-control form-control-sm w-px-200" id="searchLeave" placeholder="Search employee...">
          <button class="btn btn-sm btn-primary" id="refreshLeaveBtn"><i class="bx bx-refresh me-1"></i>Refresh</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-borderless" id="leaveTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Status</th>
                <th>Applied On</th>
                <th>Reason</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="leaveBody">
              <!-- populated by client-side dummy data -->
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Leave pagination" class="mt-3">
          <ul class="pagination pagination-sm justify-content-end">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Add Leave Request Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Leave Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addLeaveForm">
          <div class="mb-3">
            <label class="form-label">Employee *</label>
            <select class="form-select" name="employee_id" required>
              <option value="">Select Employee</option>
              <option value="1">Maria Santos</option>
              <option value="2">John Doe</option>
              <option value="3">Leila Karim</option>
              <option value="4">Pedro Alvarez</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Leave Type *</label>
            <select class="form-select" name="leave_type" required>
              <option value="">Select Leave Type</option>
              <option value="vacation">Vacation Leave</option>
              <option value="sick">Sick Leave</option>
              <option value="personal">Personal Leave</option>
              <option value="emergency">Emergency Leave</option>
              <option value="maternity">Maternity Leave</option>
              <option value="paternity">Paternity Leave</option>
            </select>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Start Date *</label>
              <input type="date" class="form-control" name="start_date" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date *</label>
              <input type="date" class="form-control" name="end_date" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason *</label>
            <textarea class="form-control" name="reason" rows="3" placeholder="Please provide reason for leave request..." required></textarea>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="half_day" id="halfDay">
              <label class="form-check-label" for="halfDay">
                Half Day Leave
              </label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addLeaveForm" class="btn btn-primary">Submit Request</button>
      </div>
    </div>
  </div>
</div>

<!-- Leave Balance Modal -->
<div class="modal fade" id="leaveBalanceModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Employee Leave Balances</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Vacation</th>
                <th>Sick Leave</th>
                <th>Personal</th>
                <th>Total Used</th>
              </tr>
            </thead>
            <tbody id="leaveBalanceBody">
              <tr>
                <td><strong>Maria Santos</strong><br><small class="text-muted">Engineering</small></td>
                <td><span class="badge bg-success">15/20</span></td>
                <td><span class="badge bg-info">3/10</span></td>
                <td><span class="badge bg-warning">2/5</span></td>
                <td><span class="badge bg-primary">20/35</span></td>
              </tr>
              <tr>
                <td><strong>John Doe</strong><br><small class="text-muted">HR</small></td>
                <td><span class="badge bg-success">8/20</span></td>
                <td><span class="badge bg-info">5/10</span></td>
                <td><span class="badge bg-warning">1/5</span></td>
                <td><span class="badge bg-primary">14/35</span></td>
              </tr>
              <tr>
                <td><strong>Leila Karim</strong><br><small class="text-muted">Sales</small></td>
                <td><span class="badge bg-success">12/20</span></td>
                <td><span class="badge bg-info">2/10</span></td>
                <td><span class="badge bg-warning">3/5</span></td>
                <td><span class="badge bg-primary">17/35</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Update Balances</button>
      </div>
    </div>
  </div>
</div>

<style>
/* Leave Management specific styles */
.w-px-150 { width: 150px !important; }
.w-px-200 { width: 200px !important; }
.table td, .table th { vertical-align: middle; }
.avatar-initial { display: flex; align-items: center; justify-content: center; }

/* Status badges */
.status-pending { background-color: #ff9f43 !important; }
.status-approved { background-color: #28c76f !important; }
.status-rejected { background-color: #ea5455 !important; }

/* Leave type colors */
.leave-vacation { color: #00cfe8; }
.leave-sick { color: #ff9f43; }
.leave-personal { color: #7367f0; }
.leave-emergency { color: #ea5455; }
</style>

<script>
  (function waitForjQuery() {
    if (typeof $ !== 'undefined') {
      initLeaveManagement();
    } else {
      setTimeout(waitForjQuery, 50);
    }
  })();

  // Global variables
  let allLeaveData = [];
  let allEmployees = [];
  let allDepartments = [];

  function initLeaveManagement() {
    $(document).ready(function () {
      // Load initial data
      loadLeaveData();
      loadLeaveStatistics();
      loadEmployeesForDropdown();
      
      // Search and filter functionality
      $('#searchLeave').on('keyup', function () { filterAndRenderLeave(); });
      $('#filterLeaveType, #filterStatus, #filterDepartment').on('change', function () { filterAndRenderLeave(); });
      $('#refreshLeaveBtn').on('click', function () { loadLeaveData(); });
      
      // Modal buttons
      $('#leaveBalanceBtn').on('click', function() { loadLeaveBalances(); });

      // Form submission
      $('#addLeaveForm').on('submit', function (e) {
        e.preventDefault();
        submitLeaveRequest($(this));
      });

      // Calculate days when dates change
      $('input[name="start_date"], input[name="end_date"]').on('change', calculateLeaveDays);
    });
  }

  // AJAX Functions
  function loadLeaveData() {
    const filters = {
      search: $('#searchLeave').val(),
      leave_type: $('#filterLeaveType').val(),
      status: $('#filterStatus').val(),
      department_id: $('#filterDepartment').val()
    };

    $.ajax({
      url: '../ajax/get_leaves.php',
      method: 'GET',
      data: filters,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          allLeaveData = response.data;
          renderLeaveRequests(allLeaveData);
        } else {
          console.error('Error loading leave data:', response.message);
          showAlert('Error loading leave data: ' + response.message, 'error');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading leave data:', error);
        showAlert('Error loading leave data. Please try again.', 'error');
      }
    });
  }

  function loadLeaveStatistics() {
    $.ajax({
      url: '../ajax/get_leave_statistics.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          updateStatisticsCards(response.data);
        } else {
          console.error('Error loading leave statistics:', response.message);
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading leave statistics:', error);
      }
    });
  }

  function loadEmployeesForDropdown() {
    $.ajax({
      url: '../ajax/get_employees.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          allEmployees = response.employees || [];
          allDepartments = response.departments || [];
          populateEmployeeDropdown();
          populateDepartmentFilter();
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading employees:', error);
      }
    });
  }

  function submitLeaveRequest(form) {
    const formData = form.serialize();
    
    $.ajax({
      url: '../ajax/add_leave.php',
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          showAlert('Leave request submitted successfully!', 'success');
          $('#addLeaveModal').modal('hide');
          form[0].reset();
          loadLeaveData(); // Refresh the list
          loadLeaveStatistics(); // Refresh stats
        } else {
          showAlert('Error: ' + response.message, 'error');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error submitting leave request:', error);
        showAlert('Error submitting leave request. Please try again.', 'error');
      }
    });
  }

  function loadLeaveBalances() {
    $.ajax({
      url: '../ajax/get_leave_balances.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          populateLeaveBalancesModal(response.data);
        } else {
          showAlert('Error loading leave balances: ' + response.message, 'error');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading leave balances:', error);
        showAlert('Error loading leave balances. Please try again.', 'error');
      }
    });
  }

  // Helper Functions
  function updateStatisticsCards(stats) {
    if (stats.year) {
      // Update the statistics cards with real data using stable IDs
      $('#statPending').text(stats.year.pending || 0);
      $('#statApproved').text(stats.year.approved || 0);
      $('#statRejected').text(stats.year.rejected || 0);
      $('#statThisMonth').text(stats.month.total || 0);
    }
  }

  function populateEmployeeDropdown() {
    const employeeSelect = $('select[name="employee_id"]');
    employeeSelect.empty().append('<option value="">Select Employee</option>');
    
    allEmployees.forEach(function(employee) {
      // support multiple possible shapes returned by backend
      const id = employee.employee_id || employee.id || employee.employeeId || employee.user_id || '';
      const nameFromFull = employee.name || employee.employee_name || employee.employeeName;
      const nameFromParts = ((employee.first_name || employee.firstName || '') + ' ' + (employee.last_name || employee.lastName || '')).trim();
      const displayName = (nameFromFull || nameFromParts || 'Unknown').trim();
      if (!id) return; // skip malformed entries
      employeeSelect.append(`<option value="${id}">${escapeHtml(displayName)}</option>`);
    });
  }

  function populateDepartmentFilter() {
    const deptFilter = $('#filterDepartment');
    deptFilter.empty().append('<option value="">All Departments</option>');
    
    allDepartments.forEach(function(dept) {
      const id = dept.department_id || dept.id || dept.departmentId || '';
      const name = dept.department_name || dept.name || 'Unknown';
      if (!id) return;
      deptFilter.append(`<option value="${id}">${escapeHtml(name)}</option>`);
    });
  }

  // small helper to avoid injecting raw HTML when inserting names
  function escapeHtml(unsafe) {
    return String(unsafe)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function populateLeaveBalancesModal(balances) {
    const tbody = $('#leaveBalanceBody');
    tbody.empty();
    
    balances.forEach(function(balance) {
      // Coerce numeric-like strings to numbers to avoid string concatenation when summing
      const vacationRemaining = Number(balance.vacation_remaining) || 0;
      const vacationTotal = Number(balance.vacation_total) || 0;
      const vacationUsed = Number(balance.vacation_used) || 0;

      const sickRemaining = Number(balance.sick_remaining) || 0;
      const sickTotal = Number(balance.sick_total) || 0;
      const sickUsed = Number(balance.sick_used) || 0;

      const personalRemaining = Number(balance.personal_remaining) || 0;
      const personalTotal = Number(balance.personal_total) || 0;
      const personalUsed = Number(balance.personal_used) || 0;

      const totalUsed = vacationUsed + sickUsed + personalUsed;
      const totalAll = vacationTotal + sickTotal + personalTotal;

      const row = `
        <tr>
          <td><strong>${escapeHtml(balance.employee_name)}</strong><br><small class="text-muted">${escapeHtml(balance.department_name || 'N/A')}</small></td>
          <td><span class="badge bg-success">${vacationRemaining}/${vacationTotal}</span></td>
          <td><span class="badge bg-info">${sickRemaining}/${sickTotal}</span></td>
          <td><span class="badge bg-warning">${personalRemaining}/${personalTotal}</span></td>
          <td><span class="badge bg-primary">${totalUsed}/${totalAll}</span></td>
        </tr>
      `;
      tbody.append(row);
    });
  }

  function showAlert(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
      const swalType = type === 'error' ? 'error' : type === 'success' ? 'success' : 'info';
      Swal.fire({
        title: type === 'error' ? 'Error' : type === 'success' ? 'Success' : 'Info',
        text: message,
        icon: swalType,
        timer: 3000,
        showConfirmButton: false
      });
    } else {
      alert(message);
    }
  }

  function renderLeaveRequests(items) {
    const $body = $('#leaveBody');
    if (!items || items.length === 0) {
      $body.html('<tr><td colspan="10" class="text-center text-muted py-4">No leave requests found</td></tr>');
      return;
    }

    let html = '';
    items.forEach((request, index) => {
      const statusBadge = getStatusBadge(request.status);
      const leaveTypeIcon = getLeaveTypeIcon(request.leave_type);
      
      html += `
        <tr>
          <th scope="row">${index + 1}</th>
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-label-primary">${request.employee_name.charAt(0)}</span>
              </div>
              <div>
                <strong>${request.employee_name}</strong>
                <br><small class="text-muted">${request.department_name || 'N/A'}</small>
              </div>
            </div>
          </td>
          <td>${leaveTypeIcon} ${capitalizeFirst(request.leave_type)}</td>
          <td>${formatDate(request.start_date)}</td>
          <td>${formatDate(request.end_date)}</td>
          <td><span class="badge bg-info">${request.total_days} day${request.total_days > 1 ? 's' : ''}</span></td>
          <td>${statusBadge}</td>
          <td>${formatDate(request.created_at)}</td>
          <td>
            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="${request.reason || 'No reason provided'}">
              ${request.reason || 'No reason provided'}
            </span>
          </td>
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                Actions
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);" onclick="viewLeaveDetails(${request.leave_id})">
                  <i class="bx bx-show me-1"></i>View Details
                </a>
                ${request.status === 'Pending' ? `
                <a class="dropdown-item text-success" href="javascript:void(0);" onclick="approveLeave(${request.leave_id})">
                  <i class="bx bx-check me-1"></i>Approve
                </a>
                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="rejectLeave(${request.leave_id})">
                  <i class="bx bx-x me-1"></i>Reject
                </a>
                ` : ''}
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteLeave(${request.leave_id})">
                  <i class="bx bx-trash me-1"></i>Delete
                </a>
              </div>
            </div>
          </td>
        </tr>
      `;
    });
    $body.html(html);
  }

  function getStatusBadge(status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return '<span class="badge status-pending">Pending</span>';
      case 'approved':
        return '<span class="badge status-approved">Approved</span>';
      case 'rejected':
        return '<span class="badge status-rejected">Rejected</span>';
      default:
        return '<span class="badge bg-secondary">Unknown</span>';
    }
  }

  function getLeaveTypeIcon(type) {
    switch (type) {
      case 'vacation':
        return '<i class="bx bx-sun leave-vacation"></i>';
      case 'sick':
        return '<i class="bx bx-plus-medical leave-sick"></i>';
      case 'personal':
        return '<i class="bx bx-user leave-personal"></i>';
      case 'emergency':
        return '<i class="bx bx-error leave-emergency"></i>';
      default:
        return '<i class="bx bx-calendar"></i>';
    }
  }

  function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  }

  function filterAndRenderLeave() {
    // For real-time filtering, we'll reload data with filters
    loadLeaveData();
  }

  function calculateLeaveDays() {
    const startDate = $('input[name="start_date"]').val();
    const endDate = $('input[name="end_date"]').val();
    
    if (startDate && endDate) {
      const start = new Date(startDate);
      const end = new Date(endDate);
      const timeDiff = end.getTime() - start.getTime();
      const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
      
      if (daysDiff > 0) {
        console.log('Leave duration:', daysDiff, 'days');
      }
    }
  }

  // Action functions
  function viewLeaveDetails(id) {
    const request = allLeaveData.find(r => r.leave_id == id);
    if (request && typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Leave Request Details',
        html: `
          <div class="text-start">
            <p><strong>Employee:</strong> ${request.employee_name}</p>
            <p><strong>Department:</strong> ${request.department_name || 'N/A'}</p>
            <p><strong>Position:</strong> ${request.position_name || 'N/A'}</p>
            <p><strong>Leave Type:</strong> ${capitalizeFirst(request.leave_type)}</p>
            <p><strong>Duration:</strong> ${formatDate(request.start_date)} to ${formatDate(request.end_date)} (${request.total_days} days)</p>
            <p><strong>Status:</strong> ${capitalizeFirst(request.status)}</p>
            <p><strong>Reason:</strong> ${request.reason || 'No reason provided'}</p>
            <p><strong>Applied On:</strong> ${formatDate(request.created_at)}</p>
            ${request.approved_by_name ? `<p><strong>Approved/Rejected By:</strong> ${request.approved_by_name}</p>` : ''}
            ${request.updated_at && request.updated_at !== request.created_at ? `<p><strong>Last Updated:</strong> ${formatDate(request.updated_at)}</p>` : ''}
          </div>
        `,
        width: 600
      });
    }
  }

  function approveLeave(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Approve Leave Request',
        input: 'textarea',
        inputLabel: 'Comments (optional)',
        inputPlaceholder: 'Add any comments about the approval...',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        confirmButtonColor: '#28a745',
        preConfirm: (comments) => {
          return updateLeaveStatus(id, 'Approved', comments);
        }
      });
    }
  }

  function rejectLeave(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Reject Leave Request',
        input: 'textarea',
        inputLabel: 'Reason for rejection',
        inputPlaceholder: 'Please provide a reason for rejecting this leave request...',
        inputValidator: (value) => {
          if (!value) {
            return 'You need to provide a reason for rejection!';
          }
        },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc3545',
        preConfirm: (comments) => {
          return updateLeaveStatus(id, 'Rejected', comments);
        }
      });
    }
  }

  function updateLeaveStatus(leaveId, status, comments) {
    return $.ajax({
      url: '../ajax/update_leave_status.php',
      method: 'POST',
      data: {
        leave_id: leaveId,
        status: status,
        comments: comments || ''
      },
      dataType: 'json'
    }).then(function(response) {
      if (response.success) {
        Swal.fire({
          title: 'Success!',
          text: response.message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
        loadLeaveData(); // Refresh the list
        loadLeaveStatistics(); // Refresh stats
      } else {
        Swal.fire('Error!', response.message, 'error');
      }
    }).catch(function(xhr, status, error) {
      console.error('AJAX error updating leave status:', error);
      Swal.fire('Error!', 'Failed to update leave status. Please try again.', 'error');
    });
  }

  function deleteLeave(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire('Info', 'Delete functionality will be implemented soon', 'info');
    }
  }
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
