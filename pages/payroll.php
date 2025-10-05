<?php
$page_title = 'Payroll Management';
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
                <h3 class="card-title mb-0">₱1,245,500</h3>
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
                <h3 class="card-title mb-0">847</h3>
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
                <h3 class="card-title mb-0">23</h3>
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
                <h3 class="card-title mb-0">Oct 2025</h3>
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
            <option>HR</option>
            <option>Engineering</option>
            <option>Sales</option>
            <option>Marketing</option>
            <option>Finance</option>
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

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Generate Payroll</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="generatePayrollForm">
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
              <option value="HR">HR</option>
              <option value="Engineering">Engineering</option>
              <option value="Sales">Sales</option>
              <option value="Marketing">Marketing</option>
              <option value="Finance">Finance</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Specific Employees (Optional)</label>
            <select class="form-select" name="employees[]" multiple>
              <option value="1">Maria Santos</option>
              <option value="2">John Doe</option>
              <option value="3">Leila Karim</option>
              <option value="4">Pedro Alvarez</option>
              <option value="5">Sarah Johnson</option>
            </select>
            <small class="form-text text-muted">Hold Ctrl to select multiple employees</small>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Pay Date *</label>
              <input type="date" class="form-control" name="pay_date" value="2025-10-15" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cut-off Date *</label>
              <input type="date" class="form-control" name="cutoff_date" value="2025-10-31" required>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="include_allowances" id="includeAllowances" checked>
              <label class="form-check-label" for="includeAllowances">
                Include Active Allowances
              </label>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="include_deductions" id="includeDeductions" checked>
              <label class="form-check-label" for="includeDeductions">
                Include Active Deductions
              </label>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="auto_process" id="autoProcess">
              <label class="form-check-label" for="autoProcess">
                Auto-process after generation
              </label>
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

  const dummyPayrollData = [
    { 
      id: 1, 
      employee: 'Maria Santos', 
      department: 'Engineering',
      pay_period: '2025-10',
      basic_salary: 65000,
      allowances: 8500,
      deductions: 4200,
      net_pay: 69300,
      status: 'paid', 
      pay_date: '2025-10-15'
    },
    { 
      id: 2, 
      employee: 'John Doe', 
      department: 'HR',
      pay_period: '2025-10',
      basic_salary: 45000,
      allowances: 5200,
      deductions: 2800,
      net_pay: 47400,
      status: 'paid', 
      pay_date: '2025-10-15'
    },
    { 
      id: 3, 
      employee: 'Leila Karim', 
      department: 'Sales',
      pay_period: '2025-10',
      basic_salary: 52000,
      allowances: 7800,
      deductions: 3600,
      net_pay: 56200,
      status: 'processed', 
      pay_date: '2025-10-15'
    },
    { 
      id: 4, 
      employee: 'Pedro Alvarez', 
      department: 'Engineering',
      pay_period: '2025-10',
      basic_salary: 58000,
      allowances: 6200,
      deductions: 3200,
      net_pay: 61000,
      status: 'pending', 
      pay_date: '2025-10-15'
    },
    { 
      id: 5, 
      employee: 'Sarah Johnson', 
      department: 'Marketing',
      pay_period: '2025-10',
      basic_salary: 48000,
      allowances: 4500,
      deductions: 2400,
      net_pay: 50100,
      status: 'pending', 
      pay_date: '2025-10-15'
    },
    { 
      id: 6, 
      employee: 'Ahmed Hassan', 
      department: 'Finance',
      pay_period: '2025-09',
      basic_salary: 55000,
      allowances: 6800,
      deductions: 3800,
      net_pay: 58000,
      status: 'paid', 
      pay_date: '2025-09-15'
    }
  ];

  function initPayrollManagement() {
    $(document).ready(function () {
      renderPayrollRecords(dummyPayrollData);

      // Search and filter functionality
      $('#searchPayroll').on('keyup', function () { filterAndRenderPayroll(); });
      $('#filterPayPeriod, #filterPayStatus, #filterPayDepartment').on('change', function () { filterAndRenderPayroll(); });
      $('#refreshPayrollBtn').on('click', function () { renderPayrollRecords(dummyPayrollData); });

      // Form submission
      $('#generatePayrollForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        console.log('Payroll generation requested:', formData);
        
        // Show success message
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Generating Payroll...',
            text: 'Please wait while we process the payroll data',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
              setTimeout(() => {
                Swal.fire('Success!', 'Payroll generated successfully for selected employees', 'success');
              }, 3000);
            }
          });
        } else {
          alert('Payroll generated successfully');
        }
        
        $('#generatePayrollModal').modal('hide');
        this.reset();
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

    const filtered = dummyPayrollData.filter(record => {
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
    const record = dummyPayrollData.find(r => r.id === id);
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
      Swal.fire('Marked as Paid!', 'Payroll record has been marked as paid', 'success');
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
