<?php
$page_title = 'Daily Time Record';
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
            <h5 class="card-title text-primary mb-3">Daily Time Record 🕐</h5>
            <p class="mb-4">Track your daily working hours with simple time-in and time-out functionality.</p>
            <div class="alert alert-info py-2 mb-0">
              <small><i class="bx bx-info-circle me-1"></i>View your personal DTR records and manage your
                time-in/time-out.</small>
            </div>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/man-with-laptop.png" height="140" alt="Time Management"
              style="max-width: 100%;">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Current Date & Time Clock -->
  <div class="col-lg-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body text-center">
        <div class="card-title d-flex align-items-center justify-content-center mb-3">
          <i class="bx bx-time text-primary me-2" style="font-size: 1.5rem;"></i>
          <h5 class="m-0">Current Time</h5>
        </div>
        <div class="digital-clock mb-3">
          <div class="time-display" id="currentTime">12:34:56</div>
          <div class="date-display" id="currentDate">Saturday, October 5, 2025</div>
        </div>
        <div class="status-badge mb-3">
          <span class="badge bg-label-success" id="workStatus">Working Hours</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Punch In/Out -->
  <div class="col-lg-4 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-center justify-content-between mb-3">
          <h5 class="m-0"><i class="bx bx-log-in text-success me-2"></i>Quick Punch</h5>
          <span class="badge bg-label-info" id="currentShift">Day Shift</span>
        </div>

        <div class="punch-buttons">
          <button class="btn btn-success w-100 mb-3" id="punchInBtn">
            <i class="bx bx-log-in me-2"></i>Time In
            <small class="d-block">Start your workday</small>
          </button>
          <button class="btn btn-danger w-100" id="punchOutBtn">
            <i class="bx bx-log-out me-2"></i>Time Out
            <small class="d-block">End your workday</small>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Summary -->
  <div class="col-lg-4 col-md-12 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-center justify-content-between mb-3">
          <h5 class="m-0"><i class="bx bx-calendar-check text-info me-2"></i>Today's Summary</h5>
          <span class="text-muted small" id="todayDate">Oct 5, 2025</span>
        </div>

        <div class="summary-stats">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Time In:</span>
            <span class="fw-bold text-success" id="timeInDisplay">--:-- --</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Time Out:</span>
            <span class="fw-bold text-danger" id="timeOutDisplay">--:-- --</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Break Time:</span>
            <span class="fw-bold text-warning" id="breakTimeDisplay">--</span>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted">Work Hours:</span>
            <span class="fw-bold text-primary" id="workHoursDisplay">--</span>
          </div>
          <div class="progress mb-2" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 75%;" id="workProgress"></div>
          </div>
          <small class="text-muted">75% of 8 hours completed</small>
        </div>
      </div>
    </div>
  </div>

  <!-- DTR Table Filters -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-4 col-md-6">
            <label class="form-label">View Records For</label>
            <select class="form-select" id="dateRangeFilter">
              <option value="today">Today</option>
              <option value="week" selected>This Week</option>
              <option value="month">This Month</option>
            </select>
          </div>
          <div class="col-lg-4 col-md-6">
            <label class="form-label">&nbsp;</label>
            <div class="d-flex gap-2">
              <button class="btn btn-primary" id="applyFiltersBtn">
                <i class="bx bx-filter me-1"></i>Apply Filter
              </button>
              <button class="btn btn-outline-secondary" id="resetFiltersBtn">
                <i class="bx bx-reset me-1"></i>Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- DTR Records Table -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-table me-2"></i>My Time Records</h5>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary" id="printDTRBtn">
            <i class="bx bx-printer me-1"></i>Print
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover" id="dtrTable">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Work Hours</th>
                <th>Status</th>
                <th>Remarks</th>
                <th width="100px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- DTR records will be populated via JavaScript -->
              <tr>
                <td colspan="6" class="text-center">
                  <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                  Loading DTR records...
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted">
            Showing 1 to 3 of 3 entries
          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled">
                <span class="page-link">Previous</span>
              </li>
              <li class="page-item active">
                <span class="page-link">1</span>
              </li>
              <li class="page-item disabled">
                <span class="page-link">Next</span>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- DTR Details Modal -->
<div class="modal fade" id="dtrDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-time me-2"></i>DTR Details - <span id="dtrEmployeeName">John Doe</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Basic Information</h6>
            <div class="mb-2">
              <strong>Date:</strong> <span id="dtrDetailDate">October 5, 2025</span>
            </div>
            <div class="mb-2">
              <strong>Employee:</strong> <span id="dtrDetailEmployee">John Doe</span>
            </div>
            <div class="mb-2">
              <strong>Department:</strong> <span id="dtrDetailDepartment">IT Department</span>
            </div>
            <div class="mb-2">
              <strong>Shift:</strong> <span id="dtrDetailShift">Day Shift (8:00 AM - 5:00 PM)</span>
            </div>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Time Summary</h6>
            <div class="mb-2">
              <strong>Regular Hours:</strong> <span id="dtrDetailRegular">8h 0m</span>
            </div>
            <div class="mb-2">
              <strong>Overtime:</strong> <span id="dtrDetailOvertime">0h 30m</span>
            </div>
            <div class="mb-2">
              <strong>Break Time:</strong> <span id="dtrDetailBreak">1h 0m</span>
            </div>
            <div class="mb-2">
              <strong>Total Hours:</strong> <span id="dtrDetailTotal">8h 30m</span>
            </div>
          </div>
        </div>

        <hr>

        <h6 class="text-muted mb-3">Time Logs</h6>
        <div class="timeline" id="dtrTimeline">
          <div class="timeline-item">
            <div class="timeline-indicator bg-success"></div>
            <div class="timeline-content">
              <h6 class="mb-1">Time In</h6>
              <p class="mb-1">08:00 AM</p>
              <small class="text-muted">Started workday</small>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-indicator bg-warning"></div>
            <div class="timeline-content">
              <h6 class="mb-1">Break Out</h6>
              <p class="mb-1">12:00 PM</p>
              <small class="text-muted">Lunch break</small>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-indicator bg-info"></div>
            <div class="timeline-content">
              <h6 class="mb-1">Break In</h6>
              <p class="mb-1">01:00 PM</p>
              <small class="text-muted">Back from lunch</small>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-indicator bg-danger"></div>
            <div class="timeline-content">
              <h6 class="mb-1">Time Out</h6>
              <p class="mb-1">05:30 PM</p>
              <small class="text-muted">End of workday</small>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <?php if ($user_type === 'admin'): ?>
          <button type="button" class="btn btn-primary" onclick="editDTR()">
            <i class="bx bx-edit me-1"></i>Edit Record
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
  /* Digital Clock Styling */
  .digital-clock {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    text-align: center;
  }

  .time-display {
    font-size: 2.5rem;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    margin-bottom: 5px;
  }

  .date-display {
    font-size: 1rem;
    opacity: 0.9;
  }

  /* Punch Button Styling */
  .punch-buttons .btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .punch-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .punch-buttons .btn small {
    font-size: 0.75rem;
    opacity: 0.8;
  }

  /* Summary Stats */
  .summary-stats {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
  }

  /* DTR Table Enhancements */
  .table th {
    border-top: none;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
  }

  .table td {
    vertical-align: middle;
  }

  .avatar img {
    width: 32px;
    height: 32px;
    object-fit: cover;
  }

  /* Timeline Styling */
  .timeline {
    position: relative;
    padding-left: 30px;
  }

  .timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
  }

  .timeline-item {
    position: relative;
    margin-bottom: 20px;
  }

  .timeline-indicator {
    position: absolute;
    left: -38px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .timeline-content h6 {
    color: #495057;
    font-size: 0.875rem;
  }

  .timeline-content p {
    font-weight: 600;
    color: #212529;
  }

  /* Status Badge Colors */
  .badge.bg-success {
    background-color: #28a745 !important;
  }

  .badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
  }

  .badge.bg-info {
    background-color: #17a2b8 !important;
  }

  .badge.bg-danger {
    background-color: #dc3545 !important;
  }

  /* Card Enhancements */
  .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
  }

  .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  }

  /* Status Indicators */
  .status-badge .badge {
    font-size: 0.75rem;
    padding: 0.5rem 1rem;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .time-display {
      font-size: 1.8rem;
    }

    .punch-buttons .btn {
      margin-bottom: 0.5rem;
    }

    .table-responsive {
      font-size: 0.875rem;
    }
  }

  /* Animation for real-time updates */
  @keyframes pulse {
    0% {
      opacity: 1;
    }

    50% {
      opacity: 0.5;
    }

    100% {
      opacity: 1;
    }
  }

  .time-display {
    animation: pulse 2s infinite;
  }
</style>

<script>
  // DTR Management System
  class DTRSystem {
    constructor() {
      this.isAdmin = <?php echo json_encode($user_type === 'admin'); ?>;
      this.userId = <?php echo json_encode($user_id); ?>;
      this.currentStatus = 'out'; // out, in
      this.init();
    }

    init() {
      this.updateClock();
      this.setupEventListeners();
      this.loadUserStatus();

      // Update clock every second
      setInterval(() => this.updateClock(), 1000);
    }

    updateClock() {
      const now = new Date();
      const timeOptions = {
        hour12: true,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      };
      const dateOptions = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };

      document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', timeOptions);
      document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', dateOptions);

      // Update work status based on time
      this.updateWorkStatus(now);
    }

    updateWorkStatus(now) {
      const hour = now.getHours();
      const statusElement = document.getElementById('workStatus');

      if (hour >= 8 && hour < 17) {
        statusElement.textContent = 'Working Hours';
        statusElement.className = 'badge bg-label-success';
      } else if (hour >= 17 && hour < 20) {
        statusElement.textContent = 'Overtime Hours';
        statusElement.className = 'badge bg-label-warning';
      } else {
        statusElement.textContent = 'Non-Working Hours';
        statusElement.className = 'badge bg-label-secondary';
      }
    }

    setupEventListeners() {
      // Punch buttons
      document.getElementById('punchInBtn')?.addEventListener('click', () => this.punchIn());
      document.getElementById('punchOutBtn')?.addEventListener('click', () => this.punchOut());

      // Filter and action buttons
      document.getElementById('applyFiltersBtn')?.addEventListener('click', () => this.applyFilters());
      document.getElementById('resetFiltersBtn')?.addEventListener('click', () => this.resetFilters());
      document.getElementById('printDTRBtn')?.addEventListener('click', () => this.printDTR());
    }

    async loadUserStatus() {
      try {
        const response = await fetch('../ajax/get_current_status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        });

        const result = await response.json();
        if (result.success) {
          this.currentStatus = result.status; // 'in' or 'out'
          this.updatePunchButtons();

          // Update display elements with current status data
          if (result.data) {
            this.updateStatusDisplay(result.data);
          }
        }
      } catch (error) {
        console.error('Error loading current status:', error);
      }

      // Load today's summary
      this.loadTodaySummary();

      // Load DTR records
      this.loadDTRRecords();
    }

    updatePunchButtons() {
      const punchInBtn = document.getElementById('punchInBtn');
      const punchOutBtn = document.getElementById('punchOutBtn');

      // Reset buttons
      if (punchInBtn) punchInBtn.disabled = false;
      if (punchOutBtn) punchOutBtn.disabled = false;

      // Update button states based on current status
      switch (this.currentStatus) {
        case 'out':
          if (punchOutBtn) punchOutBtn.disabled = true;
          break;
        case 'in':
          if (punchInBtn) punchInBtn.disabled = true;
          break;
      }
    }

    async punchIn() {
      const result = await Swal.fire({
        title: 'Time In',
        text: 'Are you ready to start your workday?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Time In!',
        cancelButtonText: 'Cancel'
      });

      if (result.isConfirmed) {
        try {
          const response = await fetch('../ajax/dtr_time_in.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            }
          });

          const apiResult = await response.json();

          if (apiResult.success) {
            this.currentStatus = 'in';
            this.updatePunchButtons();

            const now = new Date();
            document.getElementById('timeInDisplay').textContent = now.toLocaleTimeString('en-US', {
              hour12: true,
              hour: '2-digit',
              minute: '2-digit'
            });

            Swal.fire({
              title: 'Time In Successful!',
              text: `Welcome! Your workday started at ${now.toLocaleTimeString()}`,
              icon: 'success',
              timer: 3000,
              showConfirmButton: false
            });

            // Refresh data
            this.loadTodaySummary();
            this.loadDTRRecords();
          } else {
            throw new Error(apiResult.message || 'Failed to record time in');
          }
        } catch (error) {
          Swal.fire({
            title: 'Error!',
            text: error.message || 'Failed to record time in. Please try again.',
            icon: 'error'
          });
        }
      }
    }

    async punchOut() {
      const result = await Swal.fire({
        title: 'Time Out',
        text: 'Are you finishing your workday?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Time Out!',
        cancelButtonText: 'Cancel'
      });

      if (result.isConfirmed) {
        try {
          const response = await fetch('../ajax/dtr_time_out.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            }
          });

          const apiResult = await response.json();

          if (apiResult.success) {
            this.currentStatus = 'out';
            this.updatePunchButtons();

            const now = new Date();
            document.getElementById('timeOutDisplay').textContent = now.toLocaleTimeString('en-US', {
              hour12: true,
              hour: '2-digit',
              minute: '2-digit'
            });

            Swal.fire({
              title: 'Time Out Successful!',
              text: `Great work today! You clocked out at ${now.toLocaleTimeString()}`,
              icon: 'success',
              timer: 3000,
              showConfirmButton: false
            });

            // Refresh data
            this.loadTodaySummary();
            this.loadDTRRecords();
          } else {
            throw new Error(apiResult.message || 'Failed to record time out');
          }
        } catch (error) {
          Swal.fire({
            title: 'Error!',
            text: error.message || 'Failed to record time out. Please try again.',
            icon: 'error'
          });
        }
      }
    }



    async loadTodaySummary() {
      try {
        const response = await fetch('../ajax/get_today_summary.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        });

        const result = await response.json();
        if (result.success && result.data) {
          this.updateTodaySummary(result.data);
        }
      } catch (error) {
        console.error('Error loading today summary:', error);
      }
    }

    async loadDTRRecords() {
      try {
        const response = await fetch('../ajax/get_dtr_records.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        });

        const result = await response.json();
        if (result.success && result.data) {
          this.updateDTRTable(result.data);
        }
      } catch (error) {
        console.error('Error loading DTR records:', error);
      }
    }

    updateStatusDisplay(data) {
      if (data.time_in) {
        // Combine date and time for proper Date object creation
        const timeInDateTime = new Date(`${data.date || new Date().toISOString().split('T')[0]} ${data.time_in}`);
        document.getElementById('timeInDisplay').textContent = timeInDateTime.toLocaleTimeString('en-US', {
          hour12: true,
          hour: '2-digit',
          minute: '2-digit'
        });
      }

      if (data.time_out) {
        // Combine date and time for proper Date object creation
        const timeOutDateTime = new Date(`${data.date || new Date().toISOString().split('T')[0]} ${data.time_out}`);
        document.getElementById('timeOutDisplay').textContent = timeOutDateTime.toLocaleTimeString('en-US', {
          hour12: true,
          hour: '2-digit',
          minute: '2-digit'
        });
      }
    }

    updateTodaySummary(data) {
      // Update summary display elements (if they exist)
      const elements = {
        'totalHours': data.total_hours || '0h 0m',
        'regularHours': data.regular_hours || '0h 0m',
        'overtimeHours': data.overtime_hours || '0h 0m'
      };

      Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
          element.textContent = elements[id];
        }
      });
    }

    updateDTRTable(records) {
      const tbody = document.querySelector('#dtrTable tbody');
      if (!tbody) return;

      tbody.innerHTML = '';

      if (records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No DTR records found</td></tr>';
        return;
      }

      records.forEach(record => {
        const row = document.createElement('tr');
        const date = new Date(record.date);
        const timeIn = record.formatted_time_in || '-';
        const timeOut = record.formatted_time_out || '-';
        const remarks = record.remarks || 'none';

        console.log("REMARKS: ", remarks)

        row.innerHTML = `
        <td>
          <div class="d-flex flex-column">
            <span class="fw-bold">${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
            <small class="text-muted">${date.toLocaleDateString('en-US', { weekday: 'long' })}</small>
          </div>
        </td>
        <td>
          ${record.time_in ? `<span class="badge bg-success">${timeIn}</span>` : '<span class="text-muted">-</span>'}
        </td>
        <td>
          ${record.time_out ? `<span class="badge bg-danger">${timeOut}</span>` : '<span class="text-muted">-</span>'}
        </td>
        <td>
          <div class="d-flex flex-column">
            <span class="fw-bold text-primary">${record.work_hours || '0h 0m'}</span>
            <small class="text-muted">-</small>
          </div>
        </td>
        <td>
          <span class="badge bg-${this.getStatusBadgeColor(record.status)}">${record.status || 'Incomplete'}</span>
        </td>
        <td>
          <span class="text-muted">${remarks}</span>
        </td>
        <td>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
              Actions
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="viewDTRDetails(${record.attendance_id})">
                <i class="bx bx-show me-1"></i>View Details</a></li>
            </ul>
          </div>
        </td>
      `;

        tbody.appendChild(row);
      });
    }

    getStatusBadgeColor(status) {
      switch (status?.toLowerCase()) {
        case 'complete': return 'success';
        case 'incomplete': return 'warning';
        case 'absent': return 'danger';
        default: return 'info';
      }
    }

    applyFilters() {
      const filters = {
        dateRange: document.getElementById('dateRangeFilter').value,
        employee: document.getElementById('employeeFilter').value,
        department: document.getElementById('departmentFilter').value
      };

      console.log('Applying filters:', filters);

      Swal.fire({
        title: 'Filters Applied',
        text: 'DTR records have been filtered successfully.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    }

    resetFilters() {
      document.getElementById('dateRangeFilter').value = 'week';
      document.getElementById('employeeFilter').value = '';
      document.getElementById('departmentFilter').value = '';

      Swal.fire({
        title: 'Filters Reset',
        text: 'All filters have been cleared.',
        icon: 'info',
        timer: 2000,
        showConfirmButton: false
      });
    }



    printDTR() {
      window.print();
    }


  }

  // Global functions for button actions
  function viewDTRDetails(id) {
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('dtrDetailsModal'));
    
    // Set modal title using the employee name span
    const employeeNameSpan = document.getElementById('dtrEmployeeName');
    if (employeeNameSpan) {
      employeeNameSpan.textContent = 'Loading...';
    }
    
    // Show loading overlay without destroying modal content
    const modalBody = document.querySelector('#dtrDetailsModal .modal-body');
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'dtrLoadingOverlay';
    loadingOverlay.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; z-index: 1000;';
    loadingOverlay.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
    modalBody.style.position = 'relative';
    modalBody.appendChild(loadingOverlay);
    
    modal.show();
    
    // Fetch real data
    fetch('../ajax/get_dtr_details.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ attendance_id: id })
    })
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      if (data.success) {
        console.log('About to call populateDTRModal with:', data.data);
        populateDTRModal(data.data);
      } else {
        console.error('Server returned error:', data.message);
        modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
      }
    })
    .catch(error => {
      console.error('Error fetching DTR details:', error);
      
      // Remove loading overlay
      const loadingOverlay = document.getElementById('dtrLoadingOverlay');
      if (loadingOverlay) {
        loadingOverlay.remove();
      }
      
      // Show error message
      const modalBody = document.querySelector('#dtrDetailsModal .modal-body');
      const errorDiv = document.createElement('div');
      errorDiv.className = 'alert alert-danger';
      errorDiv.innerHTML = `Error loading DTR details: ${error.message}<br><small>Check browser console for details.</small>`;
      modalBody.prepend(errorDiv);
    });
  }
  
  function populateDTRModal(data) {
    try {
      console.log('populateDTRModal called with data:', data);
      
      // Remove loading overlay
      const loadingOverlay = document.getElementById('dtrLoadingOverlay');
      if (loadingOverlay) {
        loadingOverlay.remove();
        console.log('Loading overlay removed');
      }
      
      // Update modal title using the employee name span
      const employeeNameSpan = document.getElementById('dtrEmployeeName');
      if (employeeNameSpan) {
        employeeNameSpan.textContent = data.employee.name;
        console.log('Updated employee name to:', data.employee.name);
      } else {
        console.error('dtrEmployeeName element not found');
      }
      
      // Update existing elements with real data
      const updateElement = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
          element.textContent = value;
          console.log(`Updated ${id} to:`, value);
        } else {
          console.error(`Element ${id} not found`);
        }
      };
      
      updateElement('dtrDetailDate', data.date_info.formatted_date);
      updateElement('dtrDetailEmployee', data.employee.name);
      updateElement('dtrDetailDepartment', data.employee.department);
      updateElement('dtrDetailShift', data.schedule_info.shift_name);
      updateElement('dtrDetailRegular', data.time_summary.regular_hours + 'h');
      updateElement('dtrDetailOvertime', data.time_summary.overtime_hours + 'h');
      updateElement('dtrDetailTotal', data.time_summary.total_work_time);
      
      // Update timeline
      console.log('Updating timeline with:', data.timeline);
      const timeline = document.getElementById('dtrTimeline');
      if (timeline && data.timeline.length > 0) {
        timeline.innerHTML = data.timeline.map(event => `
          <div class="timeline-item">
            <div class="timeline-indicator bg-${event.type === 'time_in' ? 'success' : 'danger'}"></div>
            <div class="timeline-content">
              <h6 class="mb-1">${event.event}</h6>
              <p class="mb-1">${event.time}</p>
              <small class="text-muted">${event.session} Session</small>
            </div>
          </div>
        `).join('');
        console.log('Timeline updated successfully');
      } else if (!timeline) {
        console.error('dtrTimeline element not found');
      } else {
        console.log('No timeline data to display');
      }
      
      console.log('populateDTRModal completed successfully');
      
    } catch (error) {
      console.error('Error in populateDTRModal:', error);
      const modalBody = document.querySelector('#dtrDetailsModal .modal-body');
      if (modalBody) {
        modalBody.innerHTML = `<div class="alert alert-danger">Error displaying DTR details: ${error.message}</div>`;
      }
    }
  }

  function editDTR(id) {
    Swal.fire({
      title: 'Edit DTR Record',
      text: 'DTR editing functionality would be implemented here.',
      icon: 'info'
    });
  }

  function deleteDTR(id) {
    Swal.fire({
      title: 'Delete DTR Record',
      text: 'Are you sure you want to delete this record?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Deleted!',
          text: 'DTR record has been deleted.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      }
    });
  }

  // Initialize DTR System when DOM is loaded
  document.addEventListener('DOMContentLoaded', function () {
    new DTRSystem();
  });
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

