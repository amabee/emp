<?php
$page_title = 'Admin Dashboard';
$additional_css = [
  '../assets/vendor/libs/apex-charts/apex-charts.css'
];
$additional_js = [
  '../assets/vendor/libs/apex-charts/apexcharts.js',
  '../assets/js/dashboards-analytics.js'
];

include './shared/session_handler.php';

if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}

// THIS IS THE OUTPUT BUFFER START FOR RENDERING DASHBOARD CONTENT
// IF EVER YOU GET CONFUSED ON WHAT THIS DOES, IT CAPTURES ALL THE HTML BELOW UNTIL THE ob_get_clean() FUNCTION AND STORES IT IN $content VARIABLE
// WHICH IS THEN PASSED TO layout.php TO BE RENDERED INSIDE THE LAYOUT
// ANYWAYS I WILL BE INCLUDING A README DOCU ABOUT THIS SO BETTER READ IT :))
ob_start();
?>

<div class="row">
  <!-- Welcome Card -->
  <div class="col-12 mb-6">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Welcome back,
              <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 🎉</h5>
             <?php echo htmlspecialchars($_SESSION['user_type']); ?>! 🎉</h5>
            <p class="mb-6">
              Here's what's happening with your employee management system today.
            </p>
            <a href="reports.php" class="btn btn-sm btn-outline-primary">View Reports</a>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-6">
            <img src="../assets/img/illustrations/man-with-laptop.png" height="175" alt="Admin Dashboard" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="col-lg-3 col-md-6 col-6 mb-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-user"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Total Employees</p>
        <h4 class="card-title mb-3" id="totalEmployees">0</h4>
        <small class="text-success fw-medium" id="employeeGrowth">
          <i class="bx bx-up-arrow-alt"></i> Loading...
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-6 mb-6">
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
        <small class="text-warning fw-medium" id="leaveStatus">
          <i class="bx bx-right-arrow-alt"></i> Loading...
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-6 mb-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-buildings"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Departments</p>
        <h4 class="card-title mb-3" id="totalDepartments">0</h4>
        <small class="text-info fw-medium">
          <i class="bx bx-category-alt"></i> Active departments
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-6 mb-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-user-check"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Active Users</p>
        <h4 class="card-title mb-3" id="totalUsers">0</h4>
        <small class="text-success fw-medium">
          <i class="bx bx-shield-check"></i> System users
        </small>
      </div>
    </div>
  </div>

  <!-- Attendance Overview -->
  <div class="col-12 col-lg-8 mb-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0 me-2">Attendance Overview</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="attendanceOverview" data-bs-toggle="dropdown">
            <i class="bx bx-dots-vertical-rounded"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="javascript:void(0);">View More</a>
            <a class="dropdown-item" href="javascript:void(0);">Export Report</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="attendanceChart"></div>
      </div>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="col-12 col-lg-4 mb-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0 me-2">Recent Activity</h5>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0" id="recentActivityList">
          <li class="d-flex mb-4">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-loader-alt bx-spin"></i></span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Loading...</h6>
                <small class="text-muted">Please wait while we load recent activities</small>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    loadDashboardData();

    // Auto-refresh dashboard every 5 minutes
    setInterval(loadDashboardData, 300000);
  });

  function loadDashboardData() {
    $.ajax({
      url: '../ajax/get_dashboard_data.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          updateDashboardStats(response.data.stats);
          updateRecentActivity(response.data.recent_activity);
          updateAttendanceChart(response.data.attendance);
        } else {
          console.error('Error loading dashboard data:', response.message);
          showErrorState();
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX error:', error);
        showErrorState();
      }
    });
  }

  function updateDashboardStats(stats) {
    // Update employee count
    $('#totalEmployees').text(stats.total_employees);

    // Update employee growth
    const growthText = stats.employee_growth_percentage > 0 ?
      `+${stats.employee_growth_percentage}% this month` :
      'No growth this month';
    $('#employeeGrowth').html(`<i class="bx bx-up-arrow-alt"></i> ${growthText}`);

    // Update pending leaves
    $('#pendingLeaves').text(stats.pending_leaves);

    // Update leave status
    const leaveStatusText = stats.pending_leaves > 0 ? 'Requires attention' : 'All clear';
    $('#leaveStatus').html(`<i class="bx bx-right-arrow-alt"></i> ${leaveStatusText}`);

    // Update departments count
    $('#totalDepartments').text(stats.total_departments);

    // Update users count
    $('#totalUsers').text(stats.total_users);
  }

  function updateRecentActivity(activities) {
    const activityList = $('#recentActivityList');
    activityList.empty();

    if (activities.length === 0) {
      activityList.html(`
            <li class="d-flex mb-4">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-info-circle"></i></span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">No Recent Activity</h6>
                        <small class="text-muted">No recent activities to display</small>
                    </div>
                </div>
            </li>
        `);
      return;
    }

    activities.forEach(function (activity) {
      const activityHtml = `
            <li class="d-flex mb-4">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-${activity.color}">
                        <i class="bx ${activity.icon}"></i>
                    </span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">${activity.title}</h6>
                        <small class="text-muted">${activity.description}</small>
                    </div>
                    <small class="text-muted">${activity.time}</small>
                </div>
            </li>
        `;
      activityList.append(activityHtml);
    });
  }

  function updateAttendanceChart(attendanceData) {
    // Only update if we have data and the chart container exists
    if (attendanceData.labels.length > 0 && $('#attendanceChart').length > 0) {
      // Clear existing chart
      $('#attendanceChart').empty();

      // Chart configuration
      const chartOptions = {
        series: [
          {
            name: 'Present',
            data: attendanceData.present,
            color: '#28a745'
          },
          {
            name: 'Absent',
            data: attendanceData.absent,
            color: '#dc3545'
          },
          {
            name: 'Late',
            data: attendanceData.late,
            color: '#ffc107'
          }
        ],
        chart: {
          type: 'bar',
          height: 300,
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: attendanceData.labels
        },
        yaxis: {
          title: {
            text: 'Number of Employees'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + " employees";
            }
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'center'
        }
      };

      const chart = new ApexCharts(document.querySelector("#attendanceChart"), chartOptions);
      chart.render();
    }
  }

  function showErrorState() {
    // Show error in stats
    $('#totalEmployees').text('Error');
    $('#pendingLeaves').text('Error');
    $('#totalDepartments').text('Error');
    $('#totalUsers').text('Error');
    $('#employeeGrowth').html('<i class="bx bx-error-circle"></i> Error loading');
    $('#leaveStatus').html('<i class="bx bx-error-circle"></i> Error loading');

    // Show error in activity list
    $('#recentActivityList').html(`
        <li class="d-flex mb-4">
            <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-danger">
                    <i class="bx bx-error-circle"></i>
                </span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                    <h6 class="mb-0">Error Loading Data</h6>
                    <small class="text-muted">Please refresh the page to try again</small>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="loadDashboardData()">
                    <i class="bx bx-refresh"></i>
                </button>
            </div>
        </li>
    `);
  }
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

