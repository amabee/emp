<?php
// Supervisor Dashboard - View-Only Modules + Leave Approval
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
              <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👨‍💼</h5>
            <p class="mb-4">
              Monitor your team's activities and manage leave approvals from your supervisor dashboard.
            </p>
            <div class="d-flex gap-2">
              <a href="leaves.php" class="btn btn-sm btn-primary">
                <i class="bx bx-check-circle me-1"></i>Approve Leaves
              </a>
              <a href="attendance.php" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-group me-1"></i>Team Attendance
              </a>
            </div>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/girl-doing-yoga-light.png" height="140" alt="Supervisor Dashboard" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  <div class="col-lg-3 col-md-6 col-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-group"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Team Members</p>
        <h4 class="card-title mb-3" id="teamMembers">0</h4>
        <small class="text-info fw-medium">
          <i class="bx bx-group"></i> Active employees
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-time"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Pending Leaves</p>
        <h4 class="card-title mb-3" id="pendingLeaves">0</h4>
        <small class="text-warning fw-medium">
          <i class="bx bx-right-arrow-alt"></i> Awaiting approval
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-check-circle"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Present Today</p>
        <h4 class="card-title mb-3" id="presentToday">0</h4>
        <small class="text-success fw-medium">
          <i class="bx bx-up-arrow-alt"></i> Team attendance
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-calendar"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">This Month</p>
        <h4 class="card-title mb-3" id="monthlyStats">-</h4>
        <small class="text-primary fw-medium">
          <i class="bx bx-calendar"></i> Overview
        </small>
      </div>
    </div>
  </div>

  <!-- Quick Access Modules -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-grid-alt me-2"></i>Quick Access Modules</h5>
        <small class="text-muted">View-only access</small>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 col-6 mb-3">
            <a href="leaves.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-calendar-check bx-lg mb-2"></i>
              <span>Leave Management</span>
              <small class="text-muted">Approve/Deny</small>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="attendance.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-time-five bx-lg mb-2"></i>
              <span>Attendance</span>
              <small class="text-muted">View Only</small>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="employee-management.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-group bx-lg mb-2"></i>
              <span>Employees</span>
              <small class="text-muted">View Only</small>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="working-days-calendar.php" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
              <i class="bx bx-calendar bx-lg mb-2"></i>
              <span>Calendar</span>
              <small class="text-muted">View Only</small>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Leave Requests -->
  <div class="col-md-8 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-list-ul me-2"></i>Recent Leave Requests</h5>
        <a href="leaves.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Dates</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="recentLeaves">
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Loading recent leave requests...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Attendance Summary -->
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-user-check me-2"></i>Today's Status</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Present</span>
            <span class="fw-medium text-success" id="todayPresent">-</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Absent</span>
            <span class="fw-medium text-danger" id="todayAbsent">-</span>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Late</span>
            <span class="fw-medium text-warning" id="todayLate">-</span>
          </div>
        </div>
        <div class="mb-0">
          <div class="d-flex justify-content-between">
            <span class="text-muted">On Leave</span>
            <span class="fw-medium text-info" id="todayOnLeave">-</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Supervisor Dashboard Styles */
.btn:hover {
  transform: translateY(-2px);
  transition: transform 0.2s;
}

.card {
  box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #566a7f;
}
</style>

<script>
// Load supervisor dashboard data
$(document).ready(function() {
  loadSupervisorStats();
  loadRecentLeaves();
  loadTodayAttendance();
});

function loadSupervisorStats() {
  $.ajax({
    url: '../ajax/get_dashboard_data.php',
    method: 'GET',
    data: { type: 'supervisor' },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        $('#teamMembers').text(response.data.total_employees || 0);
        $('#pendingLeaves').text(response.data.pending_leaves || 0);
        $('#presentToday').text(response.data.present_today || 0);
        $('#monthlyStats').text(response.data.monthly_stats || '-');
      }
    },
    error: function() {
      console.error('Failed to load supervisor stats');
    }
  });
}

function loadRecentLeaves() {
  $.ajax({
    url: '../ajax/get_leaves.php',
    method: 'GET',
    data: { status: 'pending', limit: 5 },
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data.length > 0) {
        let html = '';
        response.data.forEach(leave => {
          html += `
            <tr>
              <td>${leave.employee_name}</td>
              <td>${leave.leave_type}</td>
              <td>${leave.start_date} to ${leave.end_date}</td>
              <td><span class="badge bg-warning">Pending</span></td>
              <td>
                <a href="leaves.php?id=${leave.leave_id}" class="btn btn-sm btn-outline-primary">Review</a>
              </td>
            </tr>
          `;
        });
        $('#recentLeaves').html(html);
      } else {
        $('#recentLeaves').html('<tr><td colspan="5" class="text-center text-muted py-4">No pending leave requests</td></tr>');
      }
    },
    error: function() {
      $('#recentLeaves').html('<tr><td colspan="5" class="text-center text-danger py-4">Error loading leave requests</td></tr>');
    }
  });
}

function loadTodayAttendance() {
  $.ajax({
    url: '../ajax/get_today_summary.php',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        $('#todayPresent').text(response.data.present || 0);
        $('#todayAbsent').text(response.data.absent || 0);
        $('#todayLate').text(response.data.late || 0);
        $('#todayOnLeave').text(response.data.on_leave || 0);
      }
    },
    error: function() {
      console.error('Failed to load today attendance summary');
    }
  });
}
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
