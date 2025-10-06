<?php
$page_title = 'Working Days Calendar';
$additional_css = [
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'
];
$additional_js = [
  '../assets/vendor/libs/popper/popper.js',
  '../assets/js/ui-popover.js',
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
            <h5 class="card-title text-primary mb-3">Working Days Calendar 📅</h5>
            <p class="mb-4">Manage working days and holidays for your organization. Set non-working days, add custom
              holidays, and track the work schedule efficiently.</p>
            <?php if ($user_type === 'admin'): ?>
              <button class="btn btn-sm btn-success ms-2" id="autoAssignBtn">
                <i class="bx bx-calendar-check me-1"></i>Auto-Assign Work Days
              </button>
              <button class="btn btn-sm btn-outline-secondary ms-2" id="exportCalendarBtn">Export Calendar</button>
              <button class="btn btn-sm btn-outline-info ms-2" id="resetCalendarBtn">Reset Month</button>
            <?php else: ?>
              <div class="mb-2">
                <span class="badge bg-secondary me-2">
                  <i class="bx bx-show me-1"></i>Read-Only Access
                </span>
              </div>
              <div class="alert alert-info py-2 mb-0">
                <small><i class="bx bx-info-circle me-1"></i>Calendar is in read-only mode. Only administrators can modify working days, holidays, or export data.</small>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/calendar-management.png" height="140" alt="Calendar Management"
              style="max-width: 100%;">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Calendar Navigation and Summary -->
  <div class="col-12 mb-4">
    <div class="row">
      <!-- Calendar Navigation -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="m-0"><i class="bx bx-calendar me-2"></i>Calendar Navigation</h5>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-outline-primary" id="prevMonthBtn">
                <i class="bx bx-chevron-left"></i> Previous
              </button>
              <select class="form-select form-select-sm" id="monthSelect">
                <option value="0">January</option>
                <option value="1">February</option>
                <option value="2">March</option>
                <option value="3">April</option>
                <option value="4">May</option>
                <option value="5">June</option>
                <option value="6">July</option>
                <option value="7">August</option>
                <option value="8">September</option>
                <option value="9">October</option>
                <option value="10">November</option>
                <option value="11">December</option>
              </select>
              <select class="form-select form-select-sm" id="yearSelect">
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
                <option value="2027">2027</option>
              </select>
              <button class="btn btn-sm btn-outline-primary" id="nextMonthBtn">
                Next <i class="bx bx-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="m-0">Month Summary</h6>
              <span class="badge bg-label-primary" id="currentMonthLabel">October 2025</span>
            </div>
            <div class="row text-center">
              <div class="col-6">
                <div class="d-flex flex-column">
                  <small class="text-muted">Working Days</small>
                  <h4 class="text-success mb-0" id="workingDaysCount">22</h4>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex flex-column">
                  <small class="text-muted">Non-Working</small>
                  <h4 class="text-danger mb-0" id="nonWorkingDaysCount">9</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Calendar Grid -->
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="m-0"><i class="bx bx-grid me-2"></i><span id="calendarTitle">October 2025</span></h5>
      </div>
      <div class="card-body p-0">
        <div class="calendar-container">
          <div class="calendar-header">
            <div class="calendar-day-header">Sun</div>
            <div class="calendar-day-header">Mon</div>
            <div class="calendar-day-header">Tue</div>
            <div class="calendar-day-header">Wed</div>
            <div class="calendar-day-header">Thu</div>
            <div class="calendar-day-header">Fri</div>
            <div class="calendar-day-header">Sat</div>
          </div>
          <div class="calendar-grid" id="calendarGrid">
            <!-- Calendar days will be generated by JavaScript -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Legend -->
  <div class="col-12 mt-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-3">Legend</h6>
        <div class="row">
          <div class="col-md-3 col-6 mb-2">
            <div class="d-flex align-items-center">
              <div class="legend-box working-day me-2"></div>
              <small>Working Day</small>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-2">
            <div class="d-flex align-items-center">
              <div class="legend-box half-day me-2"></div>
              <small>Half Day</small>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-2">
            <div class="d-flex align-items-center">
              <div class="legend-box non-working-day me-2"></div>
              <small>Non-Working Day</small>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-2">
            <div class="d-flex align-items-center">
              <div class="legend-box holiday me-2"></div>
              <small>Holiday</small>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-2">
            <div class="d-flex align-items-center">
              <div class="legend-box today me-2"></div>
              <small>Today</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<?php if ($user_type === 'admin'): ?>
<!-- Popover Template for Day Configuration -->
<div id="dayPopoverTemplate" style="display: none;">
  <div class="day-popover-content">
    <form id="dayContextForm">
      <input type="hidden" id="contextDate" name="context_date">

      <div class="mb-3">
        <label class="form-label fw-bold">Day Type</label>
        <select class="form-select" name="day_type" id="dayTypeSelect" style="width: 95%;">
          <option value="working">🏢 Working Day</option>
          <option value="non-working">🏠 Day Off</option>
          <option value="holiday">🎉 Holiday</option>
        </select>
      </div>

      <div class="mb-3" id="holidayNameSection" style="display: none;">
        <label class="form-label">Holiday Name</label>
        <input type="text" class="form-control form-control-sm" name="holiday_name" placeholder="Holiday name">
      </div>

      <div class="mb-3" id="workingTypeSection" style="display: none;">
        <label class="form-label">Working Hours</label>
        <select class="form-select" name="working_type" id="workingTypeSelect" style="width: 95%;">
          <option value="full">🕘 Full Day</option>
          <option value="half">🕐 Half Day</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control form-control-sm" name="description" rows="2" placeholder="Optional notes..."
          style="width: 95%;"></textarea>
      </div>

      <!-- <div class="mb-3">
        <div class="form-check form-check-sm">
          <input class="form-check-input" type="checkbox" name="apply_recurring" id="applyRecurring">
          <label class="form-check-label small" for="applyRecurring">
            Repeat annually
          </label>
        </div>
      </div> -->

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-sm" style="width: 95%;">
          <i class="bx bx-check me-1"></i>Save Changes
        </button>
        <div class="btn-group" role="group" style="width: 95%;">
          <button type="button" class="btn btn-outline-danger btn-sm" id="clearDayBtn">
            <i class="bx bx-trash me-1"></i>Clear
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelPopoverBtn">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<style>
  /* Calendar Styles */
  .calendar-container {
    width: 100%;
    max-width: 100%;
  }

  .calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
  }

  .calendar-day-header {
    padding: 15px 10px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    border-right: 1px solid #dee2e6;
  }

  .calendar-day-header:last-child {
    border-right: none;
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #dee2e6;
  }

  .calendar-day {
    background-color: #fff;
    min-height: 80px;
    padding: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
  }

  .calendar-day:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .calendar-day.other-month {
    background-color: #f8f9fa;
    color: #adb5bd;
  }

  .calendar-day.today {
    background-color: #e3f2fd;
    border: 2px solid #2196f3;
    font-weight: bold;
  }

  .calendar-day.working-day {
    background-color: #e8f5e8;
    border-left: 4px solid #28a745;
  }

  .calendar-day.non-working-day {
    background-color: #ffe6e6;
    border-left: 4px solid #dc3545;
  }

  .calendar-day.holiday {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
  }

  .calendar-day-number {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
  }

  .calendar-day-status {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    background-color: rgba(0, 0, 0, 0.1);
    color: #fff;
    margin-bottom: 2px;
  }

  .working-day .calendar-day-status {
    background-color: #28a745;
  }

  .non-working-day .calendar-day-status {
    background-color: #dc3545;
  }

  .holiday .calendar-day-status {
    background-color: #ffc107;
    color: #000;
  }

  /* Half-day status badge - blue background */
  .calendar-day-status.half-day-status {
    background-color: #17a2b8 !important;
    color: #fff !important;
  }

  .holiday-name {
    font-size: 9px;
    color: #6c757d;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    width: 100%;
  }

  .half-day-name {
    font-size: 9px;
    color: #17a2b8;
    font-weight: 500;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    width: 100%;
    margin-top: 1px;
  }

  .day-badges {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-top: 4px;
  }

  .day-badge {
    font-size: 12px;
    padding: 4px 4px;
    border-radius: 8px;
    background-color: rgba(0, 0, 0, 1);
    color: #fff;
    text-align: center;
    line-height: 1.2;
  }

  .badge-half-day {
    background-color: #17a2b8;
  }

  .badge-description {
    background-color: #6c757d;
    color: #fff;
    max-width: 100%;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
  }

  .calendar-day.admin-editable {
    cursor: context-menu;
  }

  .calendar-day.read-only {
    cursor: default;
    opacity: 0.9;
  }

  .calendar-day.read-only:hover {
    transform: none;
    box-shadow: none;
    background-color: #f8f9fa !important;
    cursor: not-allowed;
  }

  /* Add visual indicator for read-only mode */
  .calendar-day.read-only::after {
    content: "👁️";
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 10px;
    opacity: 0.6;
  }

  /* Popover Styles */
  .day-popover-content {
    width: 450px;
    padding: 0;
  }

  .popover {
    max-width: 470px;
  }

  .popover-body {
    padding: 20px;
  }

  .day-popover-content .btn-group {
    display: flex;
  }

  .day-popover-content .btn-group .btn {
    flex: 1;
    font-size: 13px;
    padding: 8px 12px;
    font-weight: 500;
  }

  .day-popover-content .form-label {
    font-size: 14px;
    margin-bottom: 8px;
    font-weight: 600;
  }

  .day-popover-content .form-control-sm {
    font-size: 13px;
    padding: 8px 12px;
  }

  .day-popover-content .form-check-label {
    font-size: 13px;
  }

  .day-popover-content .btn-sm {
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 500;
  }

  .day-popover-content textarea {
    min-height: 60px;
  }

  .day-popover-content .mb-3 {
    margin-bottom: 1rem;
  }

  .popover-header {
    font-size: 15px;
    font-weight: 600;
    padding: 12px 20px;
  }

  /* Active/Selected Button States - Bootstrap 5 Compatible */
  /* Day Type Buttons */
  .btn-check:checked+.btn-outline-success,
  .btn-check:focus+.btn-outline-success {
    color: #fff !important;
    background-color: #198754 !important;
    border-color: #198754 !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 1px rgba(0, 0, 0, 0.075) !important;
  }

  .btn-check:checked+.btn-outline-danger,
  .btn-check:focus+.btn-outline-danger {
    color: #fff !important;
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 1px rgba(0, 0, 0, 0.075) !important;
  }

  .btn-check:checked+.btn-outline-warning,
  .btn-check:focus+.btn-outline-warning {
    color: #000 !important;
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 1px rgba(0, 0, 0, 0.075) !important;
  }

  .btn-check:checked+.btn-outline-primary,
  .btn-check:focus+.btn-outline-primary {
    color: #fff !important;
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 1px rgba(0, 0, 0, 0.075) !important;
  }

  .btn-check:checked+.btn-outline-info,
  .btn-check:focus+.btn-outline-info {
    color: #fff !important;
    background-color: #0dcaf0 !important;
    border-color: #0dcaf0 !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 1px rgba(0, 0, 0, 0.075) !important;
  }

  /* Additional states for active buttons */
  .btn-check:checked+.btn-outline-success:hover,
  .btn-check:active+.btn-outline-success:focus {
    background-color: #157347 !important;
    border-color: #146c43 !important;
  }

  .btn-check:checked+.btn-outline-danger:hover,
  .btn-check:active+.btn-outline-danger:focus {
    background-color: #bb2d3b !important;
    border-color: #b02a37 !important;
  }

  .btn-check:checked+.btn-outline-warning:hover,
  .btn-check:active+.btn-outline-warning:focus {
    background-color: #ffca2c !important;
    border-color: #ffc720 !important;
  }

  /* Hover effects for better UX */
  .day-popover-content .btn-group .btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
  }

  .day-popover-content .btn-check:checked+.btn:hover {
    transform: translateY(0px);
  }

  /* Legend Styles */
  .legend-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    flex-shrink: 0;
  }

  .legend-box.working-day {
    background-color: #e8f5e8;
    border-left: 4px solid #28a745;
  }

  .legend-box.half-day {
    background-color: #e1f7fa;
    border-left: 4px solid #17a2b8;
  }

  .legend-box.non-working-day {
    background-color: #ffe6e6;
    border-left: 4px solid #dc3545;
  }

  .legend-box.holiday {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
  }

  .legend-box.today {
    background-color: #e3f2fd;
    border: 2px solid #2196f3;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .calendar-day {
      min-height: 60px;
      padding: 4px;
    }

    .calendar-day-number {
      font-size: 14px;
    }

    .calendar-day-status {
      font-size: 8px;
      padding: 1px 4px;
    }

    .holiday-name {
      font-size: 8px;
    }

    .calendar-day-header {
      padding: 10px 5px;
      font-size: 12px;
    }
  }

  @media (max-width: 576px) {
    .calendar-day {
      min-height: 50px;
      padding: 2px;
    }

    .calendar-day-number {
      font-size: 12px;
    }
  }
</style>

<script>
  class WorkingDaysCalendar {
    constructor() {
      this.currentDate = new Date();
      this.currentMonth = this.currentDate.getMonth();
      this.currentYear = this.currentDate.getFullYear();
      this.workingDays = new Set();
      this.nonWorkingDays = new Set();
      this.holidays = new Map(); // Map of date strings to holiday objects
      this.dayDetails = new Map(); // Map for storing day details (half-day, descriptions, etc.)
      this.isAdmin = <?php echo json_encode($user_type === 'admin'); ?>;
      this.selectedDate = null;
      this.currentPopover = null;
      this.currentPopoverElement = null; this.init();
    }

    init() {
      this.setupEventListeners();
      this.loadWorkingCalendar();
      this.updateSelectors();
    }

    /**
     * Load working calendar data from backend
     */
    loadWorkingCalendar() {
      const month = this.currentMonth + 1;
      const year = this.currentYear;

      fetch(`../ajax/get_working_calendar.php?month=${month}&year=${year}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Clear existing data
            this.workingDays.clear();
            this.nonWorkingDays.clear();
            this.holidays.clear();
            this.dayDetails.clear();

            // Process calendar data
            data.data.forEach(entry => {
              const dateStr = entry.work_date;
              console.log("ENTRY: ", entry);
              console.log(`Date: ${dateStr}, is_working: ${entry.is_working}, is_half_day: ${entry.is_half_day}, is_holiday: ${entry.is_holiday}`);
              
              if (entry.is_holiday) {
                this.holidays.set(dateStr, {
                  name: entry.holiday_name,
                  type: 'custom',
                  recurring: false
                });
              }
              else if (entry.is_working) {
                this.workingDays.add(dateStr);
                
                // Check if it's a half-day
                if (entry.is_half_day) {
                  this.dayDetails.set(dateStr, {
                    description: entry.remarks || 'Half day working day',
                    workingType: 'half',
                    recurring: false
                  });
                } else {
                  this.dayDetails.set(dateStr, {
                    description: entry.remarks || 'Full working day',
                    workingType: 'full',
                    recurring: false
                  });
                }
              }
              else {
                this.nonWorkingDays.add(dateStr);
                if (entry.remarks) {
                  this.dayDetails.set(dateStr, {
                    description: entry.remarks,
                    workingType: 'full',
                    recurring: false
                  });
                }
              }
            });

            // If no data exists, set defaults
            if (data.data.length === 0) {
              this.setDefaultNonWorkingDays();
            }

            this.updateCalendar();
          } else {
            console.error('Error loading working calendar:', data.message);
            // Fallback to defaults
            this.setDefaultNonWorkingDays();
            this.updateCalendar();
          }
        })
        .catch(error => {
          console.error('Error loading working calendar:', error);
          // Fallback to defaults
          this.setDefaultNonWorkingDays();
          this.updateCalendar();
        });
    }

    updateButtonVisualState(radioInput) {
      // Find all radio buttons in the same group
      const form = radioInput.closest('form');
      if (!form) return;

      const radioButtons = form.querySelectorAll('input[name="day_type"]');

      // Reset all buttons to outline style
      radioButtons.forEach(radio => {
        const label = radio.nextElementSibling;
        if (label && label.classList.contains('btn')) {
          label.classList.remove('active', 'btn-success', 'btn-danger', 'btn-warning', 'btn-primary', 'btn-info');

          // Add back the appropriate outline class based on the radio value
          const value = radio.value;
          switch (value) {
            case 'working':
              label.classList.add('btn-outline-success');
              break;
            case 'holiday':
              label.classList.add('btn-outline-danger');
              break;
            case 'special':
              label.classList.add('btn-outline-warning');
              break;
            case 'half_day':
              label.classList.add('btn-outline-primary');
              break;
            case 'custom':
              label.classList.add('btn-outline-info');
              break;
          }
        }
      });

      // Apply solid style to selected button
      if (radioInput.checked) {
        const selectedLabel = radioInput.nextElementSibling;
        if (selectedLabel && selectedLabel.classList.contains('btn')) {
          selectedLabel.classList.add('active');

          const value = radioInput.value;
          switch (value) {
            case 'working':
              selectedLabel.classList.remove('btn-outline-success');
              selectedLabel.classList.add('btn-success');
              break;
            case 'holiday':
              selectedLabel.classList.remove('btn-outline-danger');
              selectedLabel.classList.add('btn-danger');
              break;
            case 'special':
              selectedLabel.classList.remove('btn-outline-warning');
              selectedLabel.classList.add('btn-warning');
              break;
            case 'half_day':
              selectedLabel.classList.remove('btn-outline-primary');
              selectedLabel.classList.add('btn-primary');
              break;
            case 'custom':
              selectedLabel.classList.remove('btn-outline-info');
              selectedLabel.classList.add('btn-info');
              break;
          }
        }
      }
    }

    setupEventListeners() {
      // Navigation buttons
      document.getElementById('prevMonthBtn').addEventListener('click', () => {
        this.currentMonth--;
        if (this.currentMonth < 0) {
          this.currentMonth = 11;
          this.currentYear--;
        }
        this.loadWorkingCalendar();
        this.updateSelectors();
      });

      document.getElementById('nextMonthBtn').addEventListener('click', () => {
        this.currentMonth++;
        if (this.currentMonth > 11) {
          this.currentMonth = 0;
          this.currentYear++;
        }
        this.loadWorkingCalendar();
        this.updateSelectors();
      });

      // Month and year selectors
      document.getElementById('monthSelect').addEventListener('change', (e) => {
        this.currentMonth = parseInt(e.target.value);
        this.loadWorkingCalendar();
      });

      document.getElementById('yearSelect').addEventListener('change', (e) => {
        this.currentYear = parseInt(e.target.value);
        this.loadWorkingCalendar();
      });

      // Day context form (using event delegation for popover content)
      document.addEventListener('submit', (e) => {
        if (e.target.id === 'dayContextForm') {
          e.preventDefault();
          this.saveDayContext(e.target);
        }
      });

      // Day type dropdown change handler (using event delegation)
      document.addEventListener('change', (e) => {
        if (e.target.name === 'day_type') {
          this.handleDayTypeChange(e.target.value);
        }
      });

      // Clear day button (using event delegation)
      document.addEventListener('click', (e) => {
        if (e.target.id === 'clearDayBtn') {
          this.clearDaySettings();
        } else if (e.target.id === 'cancelPopoverBtn') {
          this.hideCurrentPopover();
        }
      });      // Admin-only buttons
      if (this.isAdmin) {
        // Auto-assign button
        document.getElementById('autoAssignBtn').addEventListener('click', () => {
          Swal.fire({
            title: 'Auto-Assign Working Days?',
            html: 'This will set:<br><strong>Weekdays (Mon-Fri)</strong> → Working Days<br><strong>Weekends (Sat-Sun)</strong> → Day Off<br><br>For the entire month. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Auto-Assign',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              this.autoAssignWorkingDays();
            }
          });
        });

        // Reset calendar button
        document.getElementById('resetCalendarBtn').addEventListener('click', () => {
          Swal.fire({
            title: 'Reset Calendar?',
            text: 'This will clear all custom settings for this month and reset to default weekends as non-working days.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reset',
            cancelButtonText: 'Cancel'
          }).then((result) => {
            if (result.isConfirmed) {
              this.resetMonth();
            }
          });
        });

        // Export calendar button
        document.getElementById('exportCalendarBtn').addEventListener('click', () => {
          this.exportCalendar();
        });
      }

      // Prevent default context menu on calendar for admin users, and all interactions for non-admin
      document.addEventListener('contextmenu', (e) => {
        if (e.target.closest('.calendar-day')) {
          e.preventDefault();
        }
      });

      // Add visual feedback for non-admin users
      if (!this.isAdmin) {
        document.addEventListener('selectstart', (e) => {
          if (e.target.closest('.calendar-day')) {
            e.preventDefault();
          }
        });
      }
    }

    setDefaultNonWorkingDays() {
      // Set weekends as default non-working days for current month
      const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();

      for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(this.currentYear, this.currentMonth, day);
        const dayOfWeek = date.getDay();

        // Sunday = 0, Saturday = 6
        if (dayOfWeek === 0 || dayOfWeek === 6) {
          this.nonWorkingDays.add(this.getDateString(date));
        }
      }
    }

    addDefaultHolidays() {
      // Add some default holidays for 2025
      const defaultHolidays = [
        { date: '2025-01-01', name: 'New Year\'s Day', type: 'regular' },
        { date: '2025-12-25', name: 'Christmas Day', type: 'regular' },
        { date: '2025-12-30', name: 'Rizal Day', type: 'regular' },
        { date: '2025-06-12', name: 'Independence Day', type: 'regular' },
        { date: '2025-11-01', name: 'All Saints\' Day', type: 'regular' }
      ];

      defaultHolidays.forEach(holiday => {
        this.holidays.set(holiday.date, holiday);
      });
    }

    updateSelectors() {
      document.getElementById('monthSelect').value = this.currentMonth;
      document.getElementById('yearSelect').value = this.currentYear;
    }

    updateCalendar() {
      this.renderCalendar();
      this.updateSummary();
      this.updateTitle();
    }

    updateTitle() {
      const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
      const title = `${monthNames[this.currentMonth]} ${this.currentYear}`;
      document.getElementById('calendarTitle').textContent = title;
      document.getElementById('currentMonthLabel').textContent = title;
    }

    renderCalendar() {
      const calendarGrid = document.getElementById('calendarGrid');
      calendarGrid.innerHTML = '';

      const firstDay = new Date(this.currentYear, this.currentMonth, 1);
      const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
      const daysInMonth = lastDay.getDate();
      const startingDayOfWeek = firstDay.getDay();

      // Add empty cells for days before the first of the month
      for (let i = 0; i < startingDayOfWeek; i++) {
        const prevMonthDay = new Date(this.currentYear, this.currentMonth, -(startingDayOfWeek - 1 - i));
        calendarGrid.appendChild(this.createDayElement(prevMonthDay, true));
      }

      // Add days of the current month
      for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(this.currentYear, this.currentMonth, day);
        calendarGrid.appendChild(this.createDayElement(date, false));
      }

      // Add empty cells for days after the last of the month
      const totalCells = Math.ceil((daysInMonth + startingDayOfWeek) / 7) * 7;
      const remainingCells = totalCells - (daysInMonth + startingDayOfWeek);

      for (let i = 1; i <= remainingCells; i++) {
        const nextMonthDay = new Date(this.currentYear, this.currentMonth + 1, i);
        calendarGrid.appendChild(this.createDayElement(nextMonthDay, true));
      }
    }

    createDayElement(date, isOtherMonth) {
      const dayElement = document.createElement('div');
      dayElement.className = 'calendar-day';

      if (isOtherMonth) {
        dayElement.classList.add('other-month');
      }

      const dateString = this.getDateString(date);
      const today = new Date();

      // Debug logging for today detection
      if (date.getDate() === 5 && date.getMonth() === 9) { // October 5th
        console.log(`Debug - Date: ${dateString}, Today: ${this.getDateString(today)}, Same: ${this.isSameDate(date, today)}`);
      }

      // Check if it's today
      if (this.isSameDate(date, today)) {
        dayElement.classList.add('today');
      }

      // Check if it's a holiday
      if (this.holidays.has(dateString)) {
        dayElement.classList.add('holiday');
      } else if (this.nonWorkingDays.has(dateString)) {
        dayElement.classList.add('non-working-day');
      } else if (this.workingDays.has(dateString)) {
        dayElement.classList.add('working-day');
      }

      // Add admin/read-only classes
      if (!isOtherMonth) {
        if (this.isAdmin) {
          dayElement.classList.add('admin-editable');
        } else {
          dayElement.classList.add('read-only');
        }
      }

      // Day number
      const dayNumber = document.createElement('div');
      dayNumber.className = 'calendar-day-number';
      dayNumber.textContent = date.getDate();
      dayElement.appendChild(dayNumber);

      // Status indicator and badges
      if (!isOtherMonth) {
        const status = this.getDayStatus(dateString);
        if (status) {
          const statusElement = document.createElement('div');
          statusElement.className = 'calendar-day-status';
          statusElement.textContent = status;

          // Add special class for half-day status
          if (status === 'Half-Day') {
            statusElement.classList.add('half-day-status');
          }

          dayElement.appendChild(statusElement);
        }

        // Holiday name
        if (this.holidays.has(dateString)) {
          const holidayName = document.createElement('div');
          holidayName.className = 'holiday-name';
          holidayName.textContent = this.holidays.get(dateString).name;
          dayElement.appendChild(holidayName);
        }

        // Day badges (half-day, description, etc.)
        this.renderDayBadges(dayElement, dateString);
      }

      // Event listeners for admin users only
      if (!isOtherMonth && this.isAdmin) {
        // Left click for quick toggle (legacy functionality)
        dayElement.addEventListener('click', (e) => {
          if (e.ctrlKey || e.metaKey) {
            // Ctrl+click for quick toggle
            this.toggleDayStatus(dateString);
            this.updateCalendar();
          }
        });

        // Right click for context menu
        dayElement.addEventListener('contextmenu', (e) => {
          e.preventDefault();
          this.showDayContextMenu(dateString, date, dayElement);
        });

        // Single click for context menu (mobile/touch friendly)
        dayElement.addEventListener('click', (e) => {
          if (!e.ctrlKey && !e.metaKey) {
            this.showDayContextMenu(dateString, date, dayElement);
          }
        });
      } else if (!isOtherMonth) {
        // For non-admin users, disable context menu and show access denied message
        dayElement.addEventListener('contextmenu', (e) => {
          e.preventDefault();
        });

        dayElement.addEventListener('click', (e) => {
          e.preventDefault();
          Swal.fire({
            icon: 'info',
            title: 'Read-Only Mode',
            text: 'Calendar is in read-only mode. Only administrators can modify working days.',
            timer: 2000,
            showConfirmButton: false
          });
        });

        // Add tooltip for non-admin users
        dayElement.setAttribute('title', 'Read-only mode - Click for more info');
        dayElement.style.cursor = 'help';
      }

      return dayElement;
    }

    getDayStatus(dateString) {
      if (this.holidays.has(dateString)) {
        return 'Holiday';
      } else if (this.nonWorkingDays.has(dateString)) {
        return 'Off';
      } else if (this.workingDays.has(dateString)) {
        // Check if it's a half-day
        const dayDetails = this.dayDetails.get(dateString);
        if (dayDetails && dayDetails.workingType === 'half') {
          return 'Half-Day';
        }
        return 'Work';
      } else {
        // Default behavior based on day of week
        const date = new Date(dateString);
        const dayOfWeek = date.getDay();
        return (dayOfWeek === 0 || dayOfWeek === 6) ? 'Off' : 'Work';
      }
    }

    toggleDayStatus(dateString) {
      // Check if user is admin
      if (!this.isAdmin) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'Only administrators can modify working calendar.'
        });
        return;
      }

      // Don't toggle holidays
      if (this.holidays.has(dateString)) {
        return;
      }

      if (this.nonWorkingDays.has(dateString)) {
        this.nonWorkingDays.delete(dateString);
        this.workingDays.add(dateString);
      } else if (this.workingDays.has(dateString)) {
        this.workingDays.delete(dateString);
        this.nonWorkingDays.add(dateString);
      } else {
        // Default behavior based on day of week
        const date = new Date(dateString);
        const dayOfWeek = date.getDay();

        if (dayOfWeek === 0 || dayOfWeek === 6) {
          // Weekend - make it working day
          this.workingDays.add(dateString);
        } else {
          // Weekday - make it non-working day
          this.nonWorkingDays.add(dateString);
        }
      }
    }

    updateSummary() {
      const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
      let workingDays = 0;
      let nonWorkingDays = 0;

      for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(this.currentYear, this.currentMonth, day);
        const dateString = this.getDateString(date);

        if (this.holidays.has(dateString) || this.nonWorkingDays.has(dateString)) {
          nonWorkingDays++;
        } else if (this.workingDays.has(dateString)) {
          workingDays++;
        } else {
          // Default behavior
          const dayOfWeek = date.getDay();
          if (dayOfWeek === 0 || dayOfWeek === 6) {
            nonWorkingDays++;
          } else {
            workingDays++;
          }
        }
      }

      document.getElementById('workingDaysCount').textContent = workingDays;
      document.getElementById('nonWorkingDaysCount').textContent = nonWorkingDays;
    }

    addHoliday(form) {
      const formData = new FormData(form);
      const holidayName = formData.get('holiday_name');
      const holidayDate = formData.get('holiday_date');
      const holidayType = formData.get('holiday_type');
      const recurring = formData.get('recurring');

      const holiday = {
        name: holidayName,
        type: holidayType,
        recurring: !!recurring
      };

      this.holidays.set(holidayDate, holiday);

      // Remove from other day status sets
      this.workingDays.delete(holidayDate);
      this.nonWorkingDays.delete(holidayDate);

      this.updateCalendar();

      // Close modal and reset form
      const modal = bootstrap.Modal.getInstance(document.getElementById('addHolidayModal'));
      modal.hide();
      form.reset();

      // Show success message
      Swal.fire({
        title: 'Holiday Added!',
        text: `"${holidayName}" has been added to the calendar`,
        icon: 'success',
        timer: 3000,
        showConfirmButton: false
      });
    }

    resetMonth() {
      // Check if user is admin
      if (!this.isAdmin) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'Only administrators can reset working calendar.'
        });
        return;
      }

      // Show loading state
      Swal.fire({
        title: 'Resetting...',
        text: 'Clearing all calendar data for this month',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Send reset request to backend
      const resetData = {
        month: this.currentMonth + 1,
        year: this.currentYear
      };

      console.log('Sending reset request:', resetData);

      fetch('../ajax/reset_working_calendar.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(resetData)
      })
      .then(response => response.json())
      .then(data => {
        console.log('Reset response:', data);
        if (data.success) {
          // Clear local data
          this.workingDays.clear();
          this.nonWorkingDays.clear();
          this.holidays.clear();
          this.dayDetails.clear();

          // Reset to default (weekends as non-working)
          this.setDefaultNonWorkingDays();
          this.updateCalendar();

          Swal.fire({
            title: 'Reset Complete!',
            text: 'Calendar has been reset to default settings',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        } else {
          Swal.fire({
            title: 'Error!',
            text: data.message,
            icon: 'error'
          });
        }
      })
      .catch(error => {
        console.error('Error resetting calendar:', error);
        Swal.fire({
          title: 'Error!',
          text: 'Failed to reset calendar. Please try again.',
          icon: 'error'
        });
      });
    }

    autoAssignWorkingDays() {
      // Check if user is admin
      if (!this.isAdmin) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'Only administrators can auto-assign working days.'
        });
        return;
      }

      // Show loading state
      Swal.fire({
        title: 'Auto-Assigning...',
        text: 'Creating working calendar and employee schedules',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Get all days in current month
      const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
      const workingDays = [];
      const nonWorkingDays = [];
      const holidays = {};
      let workingDaysCount = 0;
      let weekendDaysCount = 0;

      // Build arrays for backend
      for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(this.currentYear, this.currentMonth, day);
        const dateString = this.getDateString(date);
        const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday

        // Auto-assign based on day of week
        if (dayOfWeek === 0 || dayOfWeek === 6) {
          // Weekend - mark as non-working
          nonWorkingDays.push(dateString);
          weekendDaysCount++;
        } else {
          // Weekday - mark as working
          workingDays.push(dateString);
          workingDaysCount++;
        }
      }

      // Prepare data for backend
      const calendarData = {
        month: this.currentMonth + 1,
        year: this.currentYear,
        working_days: workingDays,
        non_working_days: nonWorkingDays,
        holidays: holidays
      };

      // Send to backend
      fetch('../ajax/create_working_calendar.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(calendarData)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update local data
            this.workingDays.clear();
            this.nonWorkingDays.clear();
            this.holidays.clear();

            workingDays.forEach(date => this.workingDays.add(date));
            nonWorkingDays.forEach(date => this.nonWorkingDays.add(date));

            // Update calendar display
            this.updateCalendar();

            // Show success message
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
              'July', 'August', 'September', 'October', 'November', 'December'];

            Swal.fire({
              title: 'Auto-Assignment Complete!',
              html: `<strong>${monthNames[this.currentMonth]} ${this.currentYear}</strong><br>
                   ✅ Working days: ${workingDaysCount}<br>
                   🏠 Weekend days: ${weekendDaysCount}<br>
                   📊 Calendar entries: ${data.calendar_entries}<br>
                   👥 Employee schedules: ${data.employee_schedules}`,
              icon: 'success',
              timer: 4000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: data.message,
              icon: 'error'
            });
          }
        })
        .catch(error => {
          console.error('Error auto-assigning working days:', error);
          Swal.fire({
            title: 'Error!',
            text: 'Failed to auto-assign working days. Please try again.',
            icon: 'error'
          });
        });
    }

    saveMonthSettings() {
      // Prepare data for saving
      const monthData = {
        month: this.currentMonth + 1,
        year: this.currentYear,
        working_days: Array.from(this.workingDays),
        non_working_days: Array.from(this.nonWorkingDays),
        holidays: Object.fromEntries(this.holidays)
      };

      // Save to database via AJAX
      fetch('../ajax/save_month_settings.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(monthData)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Month settings saved successfully');
          } else {
            console.error('Error saving month settings:', data.message);
          }
        })
        .catch(error => {
          console.error('Error saving month settings:', error);
        });
    }

    exportCalendar() {
      // Check if user is admin
      if (!this.isAdmin) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'Only administrators can export working calendar.'
        });
        return;
      }

      const month = this.currentMonth + 1;
      const year = this.currentYear;
      
      // Show loading indicator
      Swal.fire({
        title: 'Exporting Calendar...',
        text: 'Generating Excel file, please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      // Create export URL
      const exportUrl = `../ajax/export_working_calendar.php?month=${month}&year=${year}`;
      
      // Create a temporary link to trigger download
      const link = document.createElement('a');
      link.href = exportUrl;
      link.style.display = 'none';
      document.body.appendChild(link);
      
      // Trigger download
      link.click();
      
      // Clean up
      setTimeout(() => {
        document.body.removeChild(link);
        Swal.fire({
          icon: 'success',
          title: 'Export Complete!',
          text: 'Your Excel file has been downloaded.',
          timer: 2000,
          showConfirmButton: false
        });
      }, 500);
    }

    getDateString(date) {
      // Use local date instead of UTC to avoid timezone issues
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    isSameDate(date1, date2) {
      return this.getDateString(date1) === this.getDateString(date2);
    }

    renderDayBadges(dayElement, dateString) {
      const dayDetails = this.dayDetails.get(dateString);
      if (!dayDetails) return;

      const badgesContainer = document.createElement('div');
      badgesContainer.className = 'day-badges';

      // Half-day badge - REMOVED (using main status badge instead)

      // Description badge (only for user-added descriptions, not auto-generated ones)
      if (dayDetails.description && dayDetails.description.trim() && 
          !dayDetails.description.includes('Half day working day') && 
          !dayDetails.description.includes('Full working day')) {
        const descBadge = document.createElement('div');
        descBadge.className = 'day-badge badge-description';
        descBadge.textContent = dayDetails.description.substring(0, 10) + (dayDetails.description.length > 10 ? '...' : '');
        descBadge.title = dayDetails.description;
        badgesContainer.appendChild(descBadge);
      }

      if (badgesContainer.children.length > 0) {
        dayElement.appendChild(badgesContainer);
      }
    }

    showDayContextMenu(dateString, date, dayElement) {
      // Check if user is admin - don't show popover for non-admin users
      if (!this.isAdmin) {
        Swal.fire({
          icon: 'info',
          title: 'Read-Only Mode',
          text: 'Calendar is in read-only mode. Only administrators can modify working days.',
          timer: 2000,
          showConfirmButton: false
        });
        return;
      }

      // Hide any existing popover first
      this.hideCurrentPopover();

      this.selectedDate = dateString;
      this.currentPopoverElement = dayElement;

      // Get popover content template
      const template = document.getElementById('dayPopoverTemplate');
      if (!template) {
        // Template not available (non-admin user)
        Swal.fire({
          icon: 'info',
          title: 'Read-Only Mode',
          text: 'Calendar is in read-only mode. Only administrators can modify working days.',
          timer: 2000,
          showConfirmButton: false
        });
        return;
      }
      const popoverContent = template.innerHTML;

      // Create the popover title
      const popoverTitle = date.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
      });

      // Initialize popover
      this.currentPopover = new bootstrap.Popover(dayElement, {
        title: popoverTitle,
        content: popoverContent,
        html: true,
        sanitize: false,
        placement: 'auto',
        trigger: 'manual',
        template: '<div class="popover day-config-popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
      });

      // Show popover
      this.currentPopover.show();

      // Wait for popover to be rendered, then populate form
      setTimeout(() => {
        // Set hidden input
        const contextDateInput = document.querySelector('.popover #contextDate');
        if (contextDateInput) {
          contextDateInput.value = dateString;
        }

        // Pre-populate form with existing data
        this.populateContextForm(dateString);
      }, 50);
    }

    populateContextForm(dateString) {
      const popover = document.querySelector('.popover.show');
      if (!popover) return;

      // Reset form
      const form = popover.querySelector('#dayContextForm');
      if (form) form.reset();

      // Determine current day type
      let dayType = 'working';
      if (this.holidays.has(dateString)) {
        dayType = 'holiday';
        const holidayInput = popover.querySelector('input[name="holiday_name"]');
        if (holidayInput) {
          holidayInput.value = this.holidays.get(dateString).name;
        }
      } else if (this.nonWorkingDays.has(dateString)) {
        dayType = 'non-working';
      } else if (this.workingDays.has(dateString)) {
        dayType = 'working';
      } else {
        // Default based on day of week
        const date = new Date(dateString);
        const dayOfWeek = date.getDay();
        dayType = (dayOfWeek === 0 || dayOfWeek === 6) ? 'non-working' : 'working';
      }

      // Set day type dropdown
      const dayTypeSelect = popover.querySelector('select[name="day_type"]');
      if (dayTypeSelect) {
        dayTypeSelect.value = dayType;
      }

      // Handle day details if exists
      const dayDetails = this.dayDetails.get(dateString);
      if (dayDetails) {
        if (dayDetails.workingType) {
          const workingTypeSelect = popover.querySelector('select[name="working_type"]');
          if (workingTypeSelect) workingTypeSelect.value = dayDetails.workingType;
        }
        if (dayDetails.description) {
          const descTextarea = popover.querySelector('textarea[name="description"]');
          if (descTextarea) descTextarea.value = dayDetails.description;
        }
        if (dayDetails.recurring) {
          const recurringCheckbox = popover.querySelector('#applyRecurring');
          if (recurringCheckbox) recurringCheckbox.checked = true;
        }
      }

      // Show/hide relevant sections
      this.handleDayTypeChange(dayType);
    }

    handleDayTypeChange(dayType) {
      const popover = document.querySelector('.popover.show');
      if (!popover) return;

      const holidaySection = popover.querySelector('#holidayNameSection');
      const workingSection = popover.querySelector('#workingTypeSection');

      // Show/hide sections based on day type
      if (dayType === 'holiday') {
        if (holidaySection) holidaySection.style.display = 'block';
        if (workingSection) workingSection.style.display = 'none';
      } else if (dayType === 'working') {
        if (holidaySection) holidaySection.style.display = 'none';
        if (workingSection) workingSection.style.display = 'block';
      } else {
        if (holidaySection) holidaySection.style.display = 'none';
        if (workingSection) workingSection.style.display = 'none';
      }
    }

    saveDayContext(form) {
      // Check if user is admin
      if (!this.isAdmin) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'Only administrators can modify working calendar.'
        });
        return;
      }

      const popover = document.querySelector('.popover.show');
      if (!popover) return;

      const formData = new FormData(form);
      const dateString = this.selectedDate; // Use stored selected date
      const dayType = formData.get('day_type');
      const holidayName = formData.get('holiday_name');
      const workingType = formData.get('working_type');
      const description = formData.get('description');
      const recurring = formData.get('apply_recurring');

      // Show loading state
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...';

      // Prepare data for backend
      const updateData = {
        date: dateString,
        day_type: dayType,
        holiday_name: holidayName,
        working_type: workingType,
        is_half_day: workingType === 'half' ? 1 : 0, // Convert working_type to boolean
        note: description,
        recurring: !!recurring
      };

      // Send to backend
      fetch('../ajax/update_working_day.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(updateData)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update local data
            this.workingDays.delete(dateString);
            this.nonWorkingDays.delete(dateString);
            this.holidays.delete(dateString);

            // Apply new settings locally
            if (dayType === 'holiday') {
              this.holidays.set(dateString, {
                name: holidayName || 'Holiday',
                type: 'custom',
                recurring: !!recurring
              });
            } else if (dayType === 'working') {
              this.workingDays.add(dateString);
            } else if (dayType === 'non-working') {
              this.nonWorkingDays.add(dateString);
            }

            // Store day details
            if (description) {
              this.dayDetails.set(dateString, {
                workingType: workingType || 'full',
                description: description,
                recurring: !!recurring
              });
            } else {
              this.dayDetails.delete(dateString);
            }

            // Update calendar
            this.updateCalendar();

            // Hide popover
            this.hideCurrentPopover();

            // Show success message
            Swal.fire({
              title: 'Saved!',
              text: 'Day configuration has been updated successfully',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: data.message,
              icon: 'error'
            });
          }
        })
        .catch(error => {
          console.error('Error saving day context:', error);
          Swal.fire({
            title: 'Error!',
            text: 'Failed to save day configuration. Please try again.',
            icon: 'error'
          });
        })
        .finally(() => {
          // Restore button state
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
    }

    clearDaySettings() {
      // Check if user is admin
      if (!this.isAdmin) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'Only administrators can modify working calendar.'
        });
        return;
      }

      const dateString = this.selectedDate;
      if (!dateString) return;

      // Clear all settings for this date
      this.workingDays.delete(dateString);
      this.nonWorkingDays.delete(dateString);
      this.holidays.delete(dateString);
      this.dayDetails.delete(dateString);

      // Update calendar
      this.updateCalendar();

      // Hide popover
      this.hideCurrentPopover();

      // Show success message
      Swal.fire({
        title: 'Cleared!',
        text: 'Day settings have been reset to default',
        icon: 'info',
        timer: 2000,
        showConfirmButton: false
      });
    }

    hideCurrentPopover() {
      if (this.currentPopover && this.currentPopoverElement) {
        this.currentPopover.hide();
        this.currentPopover.dispose();
        this.currentPopover = null;
        this.currentPopoverElement = null;
      }
    }
  }

  // Initialize calendar when DOM is loaded
  document.addEventListener('DOMContentLoaded', function () {
    new WorkingDaysCalendar();
  });
</script>

<!-- Include Popper.js and Popover functionality -->
<script src=""></script>
<script src=""></script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

