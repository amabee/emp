<?php
$page_title = 'Payroll Management';
$additional_css = [
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
  'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
];
$additional_js = [
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js',
  'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
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
            <h5 class="card-title text-primary mb-3">Payroll Management 💰</h5>
            <p class="mb-4">Generate payroll, manage salary calculations, process payments, and handle employee compensation including allowances and deductions.</p>
            <button class="btn btn-sm btn-primary" id="generatePayrollBtn" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
              <i class="bx bx-calculator me-1"></i>Generate Payroll
            </button>
            <button class="btn btn-sm btn-outline-success ms-2" id="processPaymentsBtn">
              <i class="bx bx-credit-card me-1"></i>Process Payments
            </button>
            <button class="btn btn-sm btn-outline-secondary ms-2" id="exportPayrollBtn">Export Report</button>
            <button class="btn btn-sm btn-outline-info ms-2" id="payrollSummaryBtn" data-bs-toggle="modal" data-bs-target="#payrollSummaryModal">
              <i class="bx bx-pie-chart-alt me-1"></i>Summary
            </button>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/payroll-management.png" height="140" alt="Payroll Management" style="max-width: 100%;">
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
                <span class="avatar-initial rounded bg-label-success"><i class="bx bx-dollar-circle"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">Total Payroll</span>
                <h3 class="card-title mb-0 loading-stat" id="totalPayrollStat">₱0</h3>
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
                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-group"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">Employees Paid</span>
                <h3 class="card-title mb-0 loading-stat" id="employeesPaidStat">0</h3>
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
                <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">Pending</span>
                <h3 class="card-title mb-0 loading-stat" id="pendingStat">0</h3>
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
                <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-calendar-check"></i></span>
              </div>
              <div>
                <span class="fw-medium d-block mb-1">This Period</span>
                <h3 class="card-title mb-0 loading-stat" id="currentPeriodStat">Loading...</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Payroll Records List -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-receipt me-2"></i>Payroll Records</h5>
        <div class="d-flex gap-2">
          <select class="form-select form-select-sm w-px-150" id="filterPayPeriod">
            <option value="">All Periods</option>
            <option value="2025-10">October 2025</option>
            <option value="2025-09">September 2025</option>
            <option value="2025-08">August 2025</option>
          </select>
          <select class="form-select form-select-sm w-px-150" id="filterPayStatus">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="processed">Processed</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
          </select>
          <select class="form-select form-select-sm w-px-200" id="filterPayDepartment">
            <option value="">All Departments</option>
            <!-- departments will be populated dynamically via AJAX -->
          </select>
          <input type="text" class="form-control form-control-sm w-px-200" id="searchPayroll" placeholder="Search employee...">
          <button class="btn btn-sm btn-primary" id="refreshPayrollBtn"><i class="bx bx-refresh me-1"></i>Refresh</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-borderless" id="payrollTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Pay Period</th>
                <th>Basic Salary</th>
                <th>Allowances</th>
                <th>Deductions</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Pay Date</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="payrollBody">
              <!-- populated by client-side dummy data -->
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Payroll pagination" class="mt-3">
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

<!-- Generate Payroll Modal (XL, split layout) -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Generate Payroll</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="generatePayrollForm">
          <div class="row">
            <!-- Left: Employee list rendered as a table with checkboxes -->
            <div class="col-lg-6 border-end" style="max-height:60vh; overflow:auto;">
              <div class="p-3">
                <h6 class="mb-3">Employees</h6>
                <div class="table-responsive" style="max-height:48vh; overflow:auto;">
                  <table class="table table-sm table-hover" id="payrollEmployeesTable">
                    <thead>
                      <tr>
                        <th style="width:36px;"><input type="checkbox" id="payrollSelectAllTop"></th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Position</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- rows inserted via AJAX -->
                    </tbody>
                  </table>
                </div>
                <small class="form-text text-muted">Select employees to include in payroll.</small>
                <hr>
                <div class="d-flex justify-content-between">
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllEmployees">Select All</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelectedEmployees">Clear</button>
                </div>
              </div>
            </div>

            <!-- Right: Pay period and options -->
            <div class="col-lg-6">
              <div class="p-3">
                <div class="mb-3">
                  <label class="form-label">Pay Period *</label>
                  <select class="form-select" name="pay_period" required>
                    <option value="">Select Pay Period</option>
                    <option value="2025-10">October 2025</option>
                    <option value="2025-11">November 2025</option>
                    <option value="2025-12">December 2025</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Department</label>
                  <select class="form-select" name="department">
                    <option value="">All Departments</option>
                    <!-- departments will be populated dynamically via AJAX; modal uses id values for server-side filtering -->
                  </select>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Pay Date *</label>
                    <input type="date" class="form-control" name="pay_date" value="<?php echo date('Y-m-d'); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Cut-off Date *</label>
                    <input type="date" class="form-control" name="cutoff_date" value="2025-10-31" required>
                  </div>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="include_allowances" id="includeAllowances" checked>
                    <label class="form-check-label" for="includeAllowances">Include Active Allowances</label>
                  </div>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="include_deductions" id="includeDeductions" checked>
                    <label class="form-check-label" for="includeDeductions">Include Active Deductions</label>
                  </div>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="auto_process" id="autoProcess">
                    <label class="form-check-label" for="autoProcess">Auto-process after generation</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="generatePayrollForm" class="btn btn-primary">Generate Payroll</button>
      </div>
    </div>
  </div>
</div>

<!-- Payroll Summary Modal -->
<div class="modal fade" id="payrollSummaryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payroll Summary - October 2025</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-4">
          <div class="col-md-3">
            <div class="text-center">
              <h4 class="text-primary">₱1,245,500</h4>
              <p class="text-muted mb-0">Total Gross Pay</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <h4 class="text-success">₱180,750</h4>
              <p class="text-muted mb-0">Total Allowances</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <h4 class="text-warning">₱95,230</h4>
              <p class="text-muted mb-0">Total Deductions</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <h4 class="text-info">₱1,331,020</h4>
              <p class="text-muted mb-0">Total Net Pay</p>
            </div>
          </div>
        </div>
        
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Gross Pay</th>
                <th>Allowances</th>
                <th>Deductions</th>
                <th>Net Pay</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Engineering</strong></td>
                <td>45</td>
                <td>₱512,300</td>
                <td>₱75,200</td>
                <td>₱38,150</td>
                <td>₱549,350</td>
              </tr>
              <tr>
                <td><strong>Sales</strong></td>
                <td>32</td>
                <td>₱385,600</td>
                <td>₱48,750</td>
                <td>₱25,480</td>
                <td>₱408,870</td>
              </tr>
              <tr>
                <td><strong>Marketing</strong></td>
                <td>18</td>
                <td>₱198,450</td>
                <td>₱28,200</td>
                <td>₱15,670</td>
                <td>₱210,980</td>
              </tr>
              <tr>
                <td><strong>HR</strong></td>
                <td>12</td>
                <td>₱95,150</td>
                <td>₱18,600</td>
                <td>₱10,930</td>
                <td>₱102,820</td>
              </tr>
              <tr>
                <td><strong>Finance</strong></td>
                <td>8</td>
                <td>₱54,000</td>
                <td>₱10,000</td>
                <td>₹5,000</td>
                <td>₱59,000</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Export Summary</button>
      </div>
    </div>
  </div>
</div>

<style>
/* Payroll Management specific styles */
.w-px-150 { width: 150px !important; }
.w-px-200 { width: 200px !important; }
.table td, .table th { vertical-align: middle; }
.avatar-initial { display: flex; align-items: center; justify-content: center; }

/* Status badges */
.status-pending { background-color: #ff9f43 !important; }
.status-processed { background-color: #00cfe8 !important; }
.status-paid { background-color: #28c76f !important; }
.status-failed { background-color: #ea5455 !important; }

/* Amount styling */
.amount-positive { color: #28c76f; font-weight: 600; }
.amount-negative { color: #ea5455; font-weight: 600; }
.amount-neutral { color: #6c757d; font-weight: 600; }
</style>

<script>
  (function waitForjQuery() {
    if (typeof $ !== 'undefined') {
      initPayrollManagement();
    } else {
      setTimeout(waitForjQuery, 50);
    }
  })();

  // Will hold the payroll rows fetched/generated for the current view
  let currentPayrollRows = [];

  function initPayrollManagement() {
    $(document).ready(function () {
  loadPayrollStats();
  loadPayrollData();

  // Populate department selects (header filter and modal) from server
  function populateDepartmentSelects() {
    $.ajax({
      url: '../ajax/get_departments.php',
      method: 'GET',
      dataType: 'json'
    }).done(function(resp) {
      if (!resp.success) {
        console.warn('get_departments denied or failed, attempting fallback to get_employees.php');
        // Fallback: call legacy employees endpoint which returns departments as part of full-list response
        $.ajax({ url: '../ajax/get_employees.php', method: 'GET', dataType: 'json' }).done(function(eResp) {
          const depts = eResp.departments || eResp.data || [];
          applyDepartments(depts);
        }).fail(function() { console.error('Fallback to get_employees failed'); });
        return;
      }
      // resp.data expected to be an array of {id, name}
      const depts = resp.data || [];
      applyDepartments(depts);
    }).fail(function(xhr, status, err) {
      console.warn('AJAX error loading departments, attempting fallback to get_employees.php', err);
      $.ajax({ url: '../ajax/get_employees.php', method: 'GET', dataType: 'json' }).done(function(eResp) {
        const depts = eResp.departments || eResp.data || [];
        applyDepartments(depts);
      }).fail(function() { console.error('Fallback to get_employees failed'); });
    });
  }

  // Helper to actually add options to the selects
  function applyDepartments(depts) {
    const $header = $('#filterPayDepartment');
    const $modal = $('#generatePayrollForm [name="department"]');
    $header.find('option.dynamic-dept').remove();
    $modal.find('option.dynamic-dept').remove();
    (depts || []).forEach(function(d) {
      // departments may be objects (id,name) or simple strings
      const id = (typeof d === 'object' && d.id != null) ? d.id : d.department_id || d.departmentId || d.id || d.name;
      const name = (typeof d === 'object') ? (d.name || d.department_name || d.department) : d;
      const opt = `<option class="dynamic-dept" value="${escapeHtml(String(id))}">${escapeHtml(String(name))}</option>`;
      $header.append(opt);
      $modal.append(opt);
    });
  }

  // populate department selects immediately
  populateDepartmentSelects();

  // Populate employee table on modal open
  function populateEmployeeTable(filters = {}, callback) {
    // If department is a numeric id, send it to the server for filtering. If it's a name/string, we'll filter client-side.
    const data = {};
    if (filters.department && (/^\d+$/.test(String(filters.department)))) data.department = filters.department;

    $.ajax({
      url: '../ajax/get_employees.php',
      method: 'GET',
      data: data,
      dataType: 'json',
      success: function(resp) {
        const $tbody = $('#payrollEmployeesTable tbody');
        $tbody.empty();
        let employees = resp.employees || resp.data || [];

        // If a non-numeric department filter was provided, apply it client-side by comparing names
        if (filters.department && !(/^\d+$/.test(String(filters.department)))) {
          const wanted = String(filters.department).toLowerCase();
          employees = employees.filter(function(e) {
            const dept = (e.department || e.department_name || '').toLowerCase();
            return dept === wanted;
          });
        }

        employees.forEach(function(e) {
          const id = e.id || e.employee_id || e.employeeId;
          const name = e.name || e.employee_name || e.employee || (e.first_name ? (e.first_name + ' ' + e.last_name) : '');
          const dept = e.department || e.department_name || '';
          const pos = e.position || e.position_name || '';
          const row = `<tr data-employee-id="${id}">
            <td><input type="checkbox" class="payroll-employee-checkbox" value="${id}" name="employees[]"></td>
            <td>${escapeHtml(name)}</td>
            <td>${escapeHtml(dept)}</td>
            <td>${escapeHtml(pos)}</td>
          </tr>`;
          $tbody.append(row);
        });
        if (typeof callback === 'function') callback();
      },
      error: function() {
        $('#payrollEmployeesTable tbody').html('<tr><td colspan="4">Failed to load employees</td></tr>');
        if (typeof callback === 'function') callback();
      }
    });
  }

  // Utility to escape HTML
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  // Select all / Clear buttons for table
  $('#selectAllEmployees').on('click', function() {
    $('#payrollEmployeesTable tbody').find('.payroll-employee-checkbox').prop('checked', true);
  });

  $('#clearSelectedEmployees').on('click', function() {
    $('#payrollEmployeesTable tbody').find('.payroll-employee-checkbox').prop('checked', false);
  });

  // Top select-all checkbox in table header
  $(document).on('change', '#payrollSelectAllTop', function() {
    const checked = $(this).is(':checked');
    $('#payrollEmployeesTable tbody').find('.payroll-employee-checkbox').prop('checked', checked);
  });

  // When modal opens, populate the table
  $('#generatePayrollModal').on('show.bs.modal', function() {
    // populateEmployeeTable also used earlier for Select2 fallback; reuse it here
    const dept = $('#generatePayrollForm').find('[name="department"]').val() || '';
    populateEmployeeTable({ department: dept });
  });

  // If department selection in the modal changes, update the employee table
  $(document).on('change', '#generatePayrollForm [name="department"]', function() {
    const dept = $(this).val() || '';
    populateEmployeeTable({ department: dept });
  });

      // Search and filter functionality
  $('#searchPayroll').on('keyup', function () { filterAndRenderPayroll(); });
  $('#filterPayPeriod, #filterPayStatus, #filterPayDepartment').on('change', function () { loadPayrollData(); });
  $('#refreshPayrollBtn').on('click', function () { loadPayrollData(); });

      // Form submission
      $('#generatePayrollForm').on('submit', function (e) {
        e.preventDefault();
        const pay_period = $(this).find('[name="pay_period"]').val();
        const department = $(this).find('[name="department"]').val();
        const pay_date = $(this).find('[name="pay_date"]').val();
        let employeeIds = $(this).find('[name="employees[]"]').val() || [];
        // If using table checkboxes, ensure we collect checked values
        if ((!employeeIds || employeeIds.length === 0) && $('#payrollEmployeesTable').length) {
          employeeIds = $('#payrollEmployeesTable tbody').find('.payroll-employee-checkbox:checked').map(function() { return $(this).val(); }).get();
        }

        // Validation and debugging
        if (!pay_period) {
          if (typeof Swal !== 'undefined') Swal.fire('Error', 'Please select a pay period', 'error');
          return;
        }
        console.log('Form submission debug:', { pay_period, department, pay_date, employeeIds });

        // Normalize employeeIds to an array (could be a single string/number or a single value from legacy select)
        if (!Array.isArray(employeeIds)) {
          if (employeeIds === null || employeeIds === undefined) {
            employeeIds = [];
          } else if (typeof employeeIds === 'string' || typeof employeeIds === 'number') {
            employeeIds = [String(employeeIds)];
          } else {
            try {
              employeeIds = Array.from(employeeIds);
            } catch (e) {
              employeeIds = [];
            }
          }
        }
        const autoProcess = $(this).find('[name="auto_process"]').is(':checked');

        // Build params for GET
        const params = { pay_period: pay_period };
        if (department) params.department = department;
        if (employeeIds && employeeIds.length) params.employee_ids = employeeIds.join(',');

        // Debug log params being sent
        console.log('GET params being sent to get_payroll.php:', params);

        // Show loading
        if (typeof Swal !== 'undefined') {
          Swal.fire({ title: 'Generating Payroll...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        }

        $.ajax({
          url: '../ajax/get_payroll.php',
          method: 'GET',
          data: params,
          dataType: 'json'
        }).done(function(resp) {
          if (!resp.success) {
            if (typeof Swal !== 'undefined') Swal.fire('Error', resp.message || 'Failed to generate payroll', 'error');
            console.error('get_payroll error', resp);
            return;
          }

          const rows = resp.data || [];
          // Backend now provides properly formatted data
          const mapped = rows.map((r) => ({
            ...r,
            pay_period: pay_period // Ensure correct pay period
          }));

          // Keep runtime state of current rows for filtering and actions
          currentPayrollRows = mapped;
          renderPayrollRecords(currentPayrollRows);

          // Auto-process if requested
          if (autoProcess) {
            const postData = { pay_period: pay_period };
            if (department) postData.department = department;
            if (employeeIds && employeeIds.length) postData.employee_ids = employeeIds.join(',');
            if (pay_date) postData.pay_date = pay_date;

            // Log payload for debugging
            console.log('Processing payroll POST payload:', postData);
            if (typeof Swal !== 'undefined') Swal.fire({ title: 'Processing payroll...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
              url: '../ajax/process_payroll.php',
              method: 'POST',
              data: postData,
              dataType: 'json'
            }).done(function(procResp) {
              console.log('process_payroll response:', procResp);
              if (procResp.success) {
                if (typeof Swal !== 'undefined') Swal.fire('Processed', procResp.message || 'Payroll processed', 'success');
              } else {
                if (typeof Swal !== 'undefined') Swal.fire('Error', procResp.message || 'Payroll processing failed', 'error');
                console.error('process_payroll returned error', procResp);
              }

              // If server returned processed employee ids or counts, log them and update UI mapping
              const procIds = procResp.processed_employee_ids || [];
              // Update status for processed employees
              const mappedAfterProcess = rows.map((r) => ({
                ...r,
                status: procIds.includes(r.employee_id) ? 'processed' : r.status,
                pay_period: pay_period // Ensure correct pay period
              }));

              currentPayrollRows = mappedAfterProcess;
              renderPayrollRecords(currentPayrollRows);
            }).fail(function(xhr, status, err) {
              if (typeof Swal !== 'undefined') Swal.fire('Error', 'Failed to process payroll', 'error');
              console.error('AJAX process_payroll failed', err, xhr && xhr.responseText);
            }).always(function() {
              if (typeof Swal !== 'undefined') Swal.close();
              // Close modal and reset form after processing completes
              $('#generatePayrollModal').modal('hide');
              $('#generatePayrollForm')[0].reset();
            });
          } else {
            // Close modal and reset form after generation (no processing)
            $('#generatePayrollModal').modal('hide');
            $('#generatePayrollForm')[0].reset();
          }

        }).fail(function(xhr, status, err) {
          if (typeof Swal !== 'undefined') Swal.fire('Error', 'Failed to generate payroll', 'error');
          console.error('AJAX get_payroll failed', err);
        }).always(function() {
          if (typeof Swal !== 'undefined') Swal.close();
        });
      });

      // Process payments button
      $('#processPaymentsBtn').on('click', function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Process Payments?',
            text: 'This will process all pending payroll payments',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, process payments'
          }).then((result) => {
            if (result.isConfirmed) {
              Swal.fire('Processing!', 'Payments are being processed', 'success');
            }
          });
        }
      });
    });

      // Load payroll statistics
      function loadPayrollStats() {
        $.ajax({
          url: '../ajax/get_payroll_stats.php',
          method: 'GET',
          dataType: 'json',
          success: function(resp) {
            if (resp.success) {
              const stats = resp.data;
              $('#totalPayrollStat').text('₱' + formatCurrency(stats.total_payroll));
              $('#employeesPaidStat').text(stats.employees_paid);
              $('#pendingStat').text(stats.pending);
              $('#currentPeriodStat').text(stats.current_period);
            } else {
              console.error('Failed to load payroll stats:', resp.message);
            }
          },
          error: function(xhr, status, err) {
            console.error('AJAX error loading payroll stats:', err);
          }
        });
      }

      // Load payroll data via AJAX
      function loadPayrollData() {
        const period = $('#filterPayPeriod').val() || '';
        const department = $('#filterPayDepartment').val() || '';
        const loadParams = {};
        if (period) loadParams.pay_period = period;
        if (department) loadParams.department = department;
        
        console.log('loadPayrollData params:', loadParams);
        
        $.ajax({
          url: '../ajax/get_payroll.php',
          method: 'GET',
          data: loadParams,
          dataType: 'json',
          success: function(resp) {
            if (resp.success) {
                const rows = resp.data || [];
                // map rows into payroll record shape used by renderPayrollRecords
                // Backend now provides properly formatted data, no need for complex mapping
                const mapped = rows;
                // If backend returned persisted rows, show them. Otherwise show generated preview rows (marked as preview).
                if (resp.persisted === true) {
                  currentPayrollRows = mapped;
                  renderPayrollRecords(currentPayrollRows);
                } else {
                  // mark preview rows so UI can indicate they're not yet persisted
                  const preview = mapped.map(r => ({ ...r, status: 'preview' }));
                  currentPayrollRows = preview;
                  renderPayrollRecords(currentPayrollRows);
                }
            } else {
              console.error('Failed to load payroll:', resp.message);
              renderPayrollRecords([]);
            }
          },
          error: function(xhr, status, err) {
            console.error('AJAX error loading payroll:', err);
            renderPayrollRecords([]);
          }
        });
      }

      // Employee table population is handled via populateEmployeeTable() when the modal opens
  }

  function renderPayrollRecords(items) {
    const $body = $('#payrollBody');
    if (!items || items.length === 0) {
      $body.html('<tr><td colspan="10" class="text-center text-muted py-4">No payroll records found</td></tr>');
      return;
    }

    let html = '';
    items.forEach((record, index) => {
      const statusBadge = getPayrollStatusBadge(record.status);
      const payPeriodFormatted = formatPayPeriod(record.pay_period);
      
      html += `
        <tr>
          <th scope="row">${index + 1}</th>
          <td>
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-label-primary">${record.employee.charAt(0)}</span>
              </div>
              <div>
                <strong>${record.employee}</strong>
                <br><small class="text-muted">${record.department}</small>
              </div>
            </div>
          </td>
          <td>${payPeriodFormatted}</td>
          <td><span class="amount-neutral">₱${formatCurrency(record.basic_salary)}</span></td>
          <td><span class="amount-positive">+₱${formatCurrency(record.allowances)}</span></td>
          <td><span class="amount-negative">-₱${formatCurrency(record.deductions)}</span></td>
          <td><strong class="amount-positive">₱${formatCurrency(record.net_pay)}</strong></td>
          <td>${statusBadge}</td>
          <td>${formatDate(record.pay_date)}</td>
          <td class="text-center">
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                Actions
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);" onclick="viewPayslip(${record.id})">
                  <i class="bx bx-receipt me-1"></i>View Payslip
                </a>
                <a class="dropdown-item" href="javascript:void(0);" onclick="printPayslip(${record.id})">
                  <i class="bx bx-printer me-1"></i>Print Payslip
                </a>
                ${record.status === 'pending' ? `
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-success" href="javascript:void(0);" onclick="processPayment(${record.id})">
                  <i class="bx bx-check me-1"></i>Process Payment
                </a>
                ` : ''}
                ${record.status === 'processed' ? `
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-primary" href="javascript:void(0);" onclick="markAsPaid(${record.id})">
                  <i class="bx bx-credit-card me-1"></i>Mark as Paid
                </a>
                ` : ''}
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="javascript:void(0);" onclick="editPayroll(${record.id})">
                  <i class="bx bx-edit me-1"></i>Edit
                </a>
                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deletePayroll(${record.id})">
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

  function getPayrollStatusBadge(status) {
    switch (status) {
      case 'pending':
        return '<span class="badge status-pending">Pending</span>';
      case 'processed':
        return '<span class="badge status-processed">Processed</span>';
      case 'paid':
        return '<span class="badge status-paid">Paid</span>';
      case 'failed':
        return '<span class="badge status-failed">Failed</span>';
      default:
        return '<span class="badge bg-secondary">Unknown</span>';
    }
  }

  function formatCurrency(amount) {
    return amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function formatPayPeriod(period) {
    const [year, month] = period.split('-');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${monthNames[parseInt(month) - 1]} ${year}`;
  }

  function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  }

  function filterAndRenderPayroll() {
    const searchQuery = $('#searchPayroll').val().toLowerCase();
    const periodFilter = $('#filterPayPeriod').val();
    const statusFilter = $('#filterPayStatus').val();
    const deptFilter = $('#filterPayDepartment').val();

  const filtered = currentPayrollRows.filter(record => {
      if (periodFilter && record.pay_period !== periodFilter) return false;
      if (statusFilter && record.status !== statusFilter) return false;
      if (deptFilter && record.department !== deptFilter) return false;
      if (searchQuery && !record.employee.toLowerCase().includes(searchQuery)) return false;
      return true;
    });

    renderPayrollRecords(filtered);
  }

  // Action functions (placeholder implementations)
  function viewPayslip(id) {
  const record = currentPayrollRows.find(r => r.id === id);
    if (record && typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Payslip Details',
        html: `
          <div class="text-start">
            <h6><strong>${record.employee}</strong> - ${record.department}</h6>
            <hr>
            <div class="row">
              <div class="col-6">
                <p><strong>Pay Period:</strong> ${formatPayPeriod(record.pay_period)}</p>
                <p><strong>Basic Salary:</strong> ₱${formatCurrency(record.basic_salary)}</p>
                <p><strong>Allowances:</strong> +₱${formatCurrency(record.allowances)}</p>
                <p><strong>Deductions:</strong> -₱${formatCurrency(record.deductions)}</p>
              </div>
              <div class="col-6">
                <p><strong>Status:</strong> ${record.status.toUpperCase()}</p>
                <p><strong>Pay Date:</strong> ${formatDate(record.pay_date)}</p>
                <hr>
                <p><strong>Net Pay:</strong> <span class="text-success">₱${formatCurrency(record.net_pay)}</span></p>
              </div>
            </div>
          </div>
        `,
        width: 600
      });
    }
  }

  function printPayslip(id) {
    console.log('Print payslip for ID:', id);
    if (typeof Swal !== 'undefined') {
      Swal.fire('Printing...', 'Payslip is being prepared for printing', 'info');
    }
  }

  function processPayment(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire('Payment Processed!', 'Payment has been processed successfully', 'success');
    }
  }

  function markAsPaid(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Mark as Paid',
        text: 'Select the payment date:',
        input: 'date',
        inputValue: new Date().toISOString().split('T')[0],
        showCancelButton: true,
        confirmButtonText: 'Mark as Paid',
        preConfirm: (payDate) => {
          if (!payDate) {
            Swal.showValidationMessage('Please select a payment date');
            return false;
          }
          return payDate;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const payDate = result.value;
          
          // Show loading
          Swal.fire({
            title: 'Updating...',
            text: 'Marking payroll as paid',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });
          
          // Update pay date via AJAX
          $.ajax({
            url: '../ajax/update_pay_date.php',
            method: 'POST',
            data: {
              payroll_id: id,
              pay_date: payDate
            },
            dataType: 'json',
            success: function(resp) {
              if (resp.success) {
                Swal.fire('Success!', 'Payroll has been marked as paid on ' + payDate, 'success');
                // Refresh the payroll data to show updated status
                loadPayrollData();
              } else {
                Swal.fire('Error', resp.message || 'Failed to update pay date', 'error');
              }
            },
            error: function(xhr, status, err) {
              Swal.fire('Error', 'Failed to update pay date', 'error');
              console.error('AJAX error:', err);
            }
          });
        }
      });
    } else {
      // Fallback for when SweetAlert is not available
      const payDate = prompt('Enter payment date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
      if (payDate) {
        $.ajax({
          url: '../ajax/update_pay_date.php',
          method: 'POST',
          data: {
            payroll_id: id,
            pay_date: payDate
          },
          dataType: 'json',
          success: function(resp) {
            if (resp.success) {
              alert('Payroll marked as paid!');
              loadPayrollData();
            } else {
              alert('Error: ' + (resp.message || 'Failed to update pay date'));
            }
          },
          error: function() {
            alert('Failed to update pay date');
          }
        });
      }
    }
  }

  function editPayroll(id) {
    console.log('Edit payroll record:', id);
  }

  function deletePayroll(id) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Delete Payroll Record?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it'
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire('Deleted!', 'Payroll record has been deleted', 'success');
        }
      });
    }
  }
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
