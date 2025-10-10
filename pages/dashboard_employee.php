<?php
// Employee Dashboard - Simple Welcome View
ob_start();
?>

<div class="row">
  <!-- Welcome Card -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Welcome back, 
              <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h5>
            <p class="mb-4">
              Have a great day at work! Here's your quick overview.
            </p>
            <div class="d-flex gap-2">
              <a href="profile.php" class="btn btn-sm btn-primary">
                <i class="bx bx-user me-1"></i>My Profile
              </a>
              <a href="dtr.php" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-time me-1"></i>Time Tracking
              </a>
            </div>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/man-with-laptop.png" height="140" alt="Employee Dashboard" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-rocket me-2"></i>Quick Actions</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 col-6 mb-3">
            <a href="leaves.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-calendar-minus bx-lg mb-2"></i>
              <span>Request Leave</span>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="dtr.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-time-five bx-lg mb-2"></i>
              <span>Time In/Out</span>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="working-days-calendar.php" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-calendar bx-lg mb-2"></i>
              <span>Calendar</span>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="profile.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-user-circle bx-lg mb-2"></i>
              <span>My Profile</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Personal Information -->
  <div class="col-md-8 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-info-circle me-2"></i>My Information</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label text-muted">Employee ID</label>
              <div class="fw-medium" id="employee-id">Loading...</div>
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Department</label>
              <div class="fw-medium" id="employee-department">Loading...</div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label text-muted">Position</label>
              <div class="fw-medium" id="employee-position">Loading...</div>
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Date Hired</label>
              <div class="fw-medium" id="employee-hired">Loading...</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Leave Balance -->
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-calendar-check me-2"></i>Leave Balance</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Vacation</span>
            <span class="fw-medium text-primary" id="vacation-balance">-</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Sick Leave</span>
            <span class="fw-medium text-success" id="sick-balance">-</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Personal</span>
            <span class="fw-medium text-info" id="personal-balance">-</span>
          </div>
        </div>
        <div class="mb-0">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Emergency</span>
            <span class="fw-medium text-warning" id="emergency-balance">-</span>
          </div>
        </div>
      </div>
    </div>
  </div>


</div>

<style>
/* Employee Dashboard Styles */
.btn:hover {
  transform: translateY(-2px);
  transition: transform 0.2s;
}

.card {
  box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
}

.quick-action-btn {
  transition: all 0.3s ease;
}

.quick-action-btn:hover {
  transform: scale(1.05);
}
</style>

<script>
// Load employee personal information
$(document).ready(function() {
  loadEmployeeInfo();
  loadLeaveBalances();
  loadWorkSchedule();
  loadUpcomingHolidays();
  loadWeekSchedule();
});

function loadEmployeeInfo() {
  $.ajax({
    url: '../ajax/get_user_profile.php',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data) {
        const data = response.data;
        $('#employee-id').text(data.employee_number || 'N/A');
        $('#employee-department').text(data.department_name || 'N/A');
        $('#employee-position').text(data.position_name || 'N/A');
        
        if (data.date_hired) {
          const hiredDate = new Date(data.date_hired);
          $('#employee-hired').text(hiredDate.toLocaleDateString());
        } else {
          $('#employee-hired').text('N/A');
        }
      }
    },
    error: function() {
      $('#employee-id, #employee-department, #employee-position, #employee-hired').text('Error loading');
    }
  });
}

function loadLeaveBalances() {
  $.ajax({
    url: '../ajax/get_leave_balances.php',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data) {
        const balances = response.data;
        $('#vacation-balance').text((balances.vacation_total - balances.vacation_used) + '/' + balances.vacation_total);
        $('#sick-balance').text((balances.sick_total - balances.sick_used) + '/' + balances.sick_total);
        $('#personal-balance').text((balances.personal_total - balances.personal_used) + '/' + balances.personal_total);
        $('#emergency-balance').text((balances.emergency_total - balances.emergency_used) + '/' + balances.emergency_total);
      }
    },
    error: function() {
      $('#vacation-balance, #sick-balance, #personal-balance, #emergency-balance').text('Error');
    }
  });
}

function loadWorkSchedule() {
  const today = new Date();
  const todayStr = today.toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
  
  $('#today-date').text(todayStr);
  
  $.ajax({
    url: '../ajax/get_working_calendar.php',
    method: 'GET',
    data: { 
      date: today.toISOString().split('T')[0],
      type: 'today_status'
    },
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data) {
        const workData = response.data;
        const $statusIcon = $('#today-status-icon');
        const $statusText = $('#today-status');
        
        if (workData.is_holiday) {
          $statusIcon.html('<span class="avatar-initial rounded bg-label-danger"><i class="bx bx-gift"></i></span>');
          $statusText.html(`<span class="text-danger fw-medium">Holiday</span><br><small>${workData.holiday_name || 'Public Holiday'}</small>`);
        } else if (!workData.is_working) {
          $statusIcon.html('<span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five"></i></span>');
          $statusText.html('<span class="text-warning fw-medium">Non-Working Day</span><br><small>Rest day</small>');
        } else {
          $statusIcon.html('<span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle"></i></span>');
          $statusText.html('<span class="text-success fw-medium">Working Day</span><br><small>Regular work schedule</small>');
        }
      } else {
        $('#today-status').html('<span class="text-muted">Unable to load work status</span>');
      }
    },
    error: function() {
      $('#today-status-icon').html('<span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-error"></i></span>');
      $('#today-status').html('<span class="text-muted">Error loading work status</span>');
    }
  });
}

function loadUpcomingHolidays() {
  $.ajax({
    url: '../ajax/get_working_calendar.php',
    method: 'GET',
    data: { 
      type: 'upcoming_holidays',
      limit: 3
    },
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data && response.data.length > 0) {
        let html = '';
        response.data.forEach(holiday => {
          const date = new Date(holiday.work_date);
          const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
          
          html += `
            <div class="d-flex align-items-center mb-2">
              <div class="avatar flex-shrink-0 me-2">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="bx bx-gift"></i>
                </span>
              </div>
              <div class="flex-grow-1">
                <div class="fw-medium">${holiday.holiday_name || 'Holiday'}</div>
                <small class="text-muted">${dateStr}</small>
              </div>
            </div>
          `;
        });
        $('#upcoming-holidays').html(html);
      } else {
        $('#upcoming-holidays').html(`
          <div class="text-center text-muted">
            <i class="bx bx-calendar-check me-1"></i>No upcoming holidays
          </div>
        `);
      }
    },
    error: function() {
      $('#upcoming-holidays').html(`
        <div class="text-center text-muted">
          <i class="bx bx-error me-1"></i>Error loading holidays
        </div>
      `);
    }
  });
}

function loadWeekSchedule() {
  const today = new Date();
  const startOfWeek = new Date(today);
  startOfWeek.setDate(today.getDate() - today.getDay()); // Get Sunday
  
  $.ajax({
    url: '../ajax/get_working_calendar.php',
    method: 'GET',
    data: { 
      type: 'week_schedule',
      start_date: startOfWeek.toISOString().split('T')[0]
    },
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data) {
        let html = '';
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const weekData = response.data;
        
        days.forEach((day, index) => {
          const dayData = weekData[index] || {};
          let statusClass = 'secondary';
          let statusIcon = 'bx-time';
          let statusText = 'Unknown';
          
          if (dayData.is_holiday) {
            statusClass = 'danger';
            statusIcon = 'bx-gift';
            statusText = 'Holiday';
          } else if (!dayData.is_working) {
            statusClass = 'warning';
            statusIcon = 'bx-time-five';
            statusText = 'Rest';
          } else {
            statusClass = 'success';
            statusIcon = 'bx-check-circle';
            statusText = 'Work';
          }
          
          html += `
            <div class="col text-center">
              <div class="mb-2">
                <span class="badge bg-${statusClass}">
                  <i class="bx ${statusIcon} me-1"></i>${statusText}
                </span>
              </div>
              <small class="text-muted d-block">${day}</small>
            </div>
          `;
        });
        
        $('#week-schedule').html(html);
      } else {
        $('#week-schedule').html('<div class="col text-center text-muted">Unable to load week schedule</div>');
      }
    },
    error: function() {
      $('#week-schedule').html('<div class="col text-center text-muted">Error loading week schedule</div>');
    }
  });
}
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
