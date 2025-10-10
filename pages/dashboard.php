<?php
// Set page title based on user type
$page_title_map = [
  'admin' => 'Admin Dashboard',
  'supervisor' => 'Supervisor Dashboard', 
  'hr' => 'HR Dashboard',
  'employee' => 'Employee Dashboard'
];

$page_title = $page_title_map[$_SESSION['user_type']] ?? 'Dashboard';

// Different CSS/JS for different user types
if (in_array($_SESSION['user_type'], ['admin', 'supervisor', 'hr'])) {
  $additional_css = [
    '../assets/vendor/libs/apex-charts/apex-charts.css'
  ];
  $additional_js = [
    '../assets/vendor/libs/apex-charts/apexcharts.js',
    '../assets/js/dashboards-analytics.js'
  ];
} else {
  // Employee gets simpler dashboard
  $additional_css = [];
  $additional_js = [];
}

include './shared/session_handler.php';

if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}

// Route to different dashboard views based on user type
if ($_SESSION['user_type'] === 'employee') {
  include 'dashboard_employee.php';
  exit();
} elseif ($_SESSION['user_type'] === 'supervisor') {
  include 'dashboard_supervisor.php';
  exit();
}

// Continue with admin/HR dashboard for admin and hr users
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

  <!-- Advanced Analytics Cards - Powered by Subqueries -->
  <div class="col-lg-3 col-md-6 col-6 mb-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-star"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Avg Performance</p>
        <h4 class="card-title mb-3" id="avgPerformanceRating">0.0</h4>
        <small class="text-secondary fw-medium">
          <i class="bx bx-trending-up"></i> Out of 5.0
        </small>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-6 mb-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-check-circle"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Recent Evaluations</p>
        <h4 class="card-title mb-3" id="recentEvaluations">0</h4>
        <small class="text-primary fw-medium">
          <i class="bx bx-calendar"></i> Last 6 months
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
              <i class="bx bx-money"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">Avg Salary</p>
        <h4 class="card-title mb-3" id="avgBasicSalary">₱0</h4>
        <small class="text-success fw-medium">
          <i class="bx bx-up-arrow-alt"></i> <span id="aboveAvgCount">0</span> above avg
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
              <i class="bx bx-gift"></i>
            </span>
          </div>
        </div>
        <p class="mb-1">With Allowances</p>
        <h4 class="card-title mb-3" id="employeesWithAllowances">0</h4>
        <small class="text-warning fw-medium">
          <i class="bx bx-plus-circle"></i> Employees
        </small>
      </div>
    </div>
  </div>

  <!-- Top Performing Departments - Correlated Subquery Results -->
  <div class="col-12 col-lg-6 mb-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0 me-2">🏆 Top Performing Departments</h5>
        <small class="text-muted">Based on performance ratings</small>
      </div>
      <div class="card-body">
        <div id="topDepartmentsList">
          <div class="d-flex justify-content-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Leave Analytics - IN/EXISTS Subquery Results -->
  <div class="col-12 col-lg-6 mb-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0 me-2">📊 Leave Analytics</h5>
        <small class="text-muted">Subquery-powered insights</small>
      </div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-6">
            <div class="d-flex flex-column">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="bx bx-calendar-x"></i>
                </span>
              </div>
              <span class="text-nowrap">Recent Leaves</span>
              <h4 class="mb-0" id="employeesWithRecentLeaves">0</h4>
              <small class="text-muted">Last 3 months</small>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex flex-column">
              <div class="avatar mx-auto mb-2">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="bx bx-time-five"></i>
                </span>
              </div>
              <span class="text-nowrap">Pending</span>
              <h4 class="mb-0" id="pendingLeavesCount">0</h4>
              <small class="text-muted">Awaiting approval</small>
            </div>
          </div>
        </div>
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
    // Basic Statistics (Original)
    $('#totalEmployees').text(stats.total_employees);
    $('#totalDepartments').text(stats.total_departments);
    $('#totalUsers').text(stats.total_users);
    $('#pendingLeaves').text(stats.pending_leaves);

    // Employee growth
    const growthText = stats.employee_growth_percentage > 0 ?
      `+${stats.employee_growth_percentage}% this month` :
      'No growth this month';
    $('#employeeGrowth').html(`<i class="bx bx-up-arrow-alt"></i> ${growthText}`);

    // Leave status
    const leaveStatusText = stats.pending_leaves > 0 ? 'Requires attention' : 'All clear';
    $('#leaveStatus').html(`<i class="bx bx-right-arrow-alt"></i> ${leaveStatusText}`);

    // SUBQUERY-POWERED ADVANCED STATISTICS
    
    // Scalar Subquery Results - Performance Rating
    $('#avgPerformanceRating').text(stats.avg_performance_rating || '0.0');
    
    // EXISTS Subquery Results - Recent Evaluations
    $('#recentEvaluations').text(stats.employees_with_recent_evaluations || '0');
    
    // Multirow Subquery Results - Salary Analytics
    const avgSalary = stats.avg_basic_salary || 0;
    $('#avgBasicSalary').text('₱' + new Intl.NumberFormat().format(avgSalary));
    $('#aboveAvgCount').text(stats.above_avg_salary_count || '0');
    
    // IN Subquery Results - Allowances
    $('#employeesWithAllowances').text(stats.employees_with_allowances || '0');
    
    // IN/NOT IN Subquery Results - Leave Analytics
    $('#employeesWithRecentLeaves').text(stats.employees_with_recent_leaves || '0');
    $('#pendingLeavesCount').text(stats.pending_leaves || '0');

    // CORRELATED SUBQUERY RESULTS - Top Performing Departments
    updateTopDepartments(stats.top_performing_departments || []);
  }

  function updateTopDepartments(departments) {
    const container = $('#topDepartmentsList');
    
    if (!departments || departments.length === 0) {
      container.html(`
        <div class="text-center text-muted">
          <i class="bx bx-info-circle mb-2"></i>
          <p class="mb-0">No performance data available</p>
        </div>
      `);
      return;
    }

    let html = '';
    departments.forEach((dept, index) => {
      const badgeClass = index === 0 ? 'bg-primary' : index === 1 ? 'bg-success' : 'bg-info';
      const iconClass = index === 0 ? 'bx-trophy' : index === 1 ? 'bx-medal' : 'bx-award';
      const avgRating = parseFloat(dept.dept_avg_performance || 0).toFixed(1);
      
      html += `
        <div class="d-flex align-items-center mb-3">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded ${badgeClass}">
              <i class="bx ${iconClass}"></i>
            </span>
          </div>
          <div class="d-flex w-100 align-items-center justify-content-between">
            <div>
              <h6 class="mb-0">${dept.department_name}</h6>
              <small class="text-muted">${dept.employee_count} employees</small>
            </div>
            <div class="text-end">
              <span class="badge ${badgeClass}">${avgRating}/5.0</span>
            </div>
          </div>
        </div>
      `;
    });
    
    container.html(html);
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

