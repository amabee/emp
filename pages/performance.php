<?php
$page_title = 'Performance Evaluation';
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

// Determine user capabilities - Only supervisors can modify performance evaluations
$canModify = isSupervisor(); // Only supervisors can create/edit/delete
$canView = canView();
$isEmployee = isEmployee();
$isSupervisor = isSupervisor();

ob_start();
?>

<div class="row">
  <!-- Welcome Card -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <?php if ($isEmployee): ?>
              <h5 class="card-title text-primary mb-3">My Performance Reviews 📊</h5>
              <p class="mb-4">View your performance evaluations and track your professional growth over time. <span class="badge bg-label-info">View Only</span></p>
              <small class="text-muted">Performance evaluations are created and managed by your supervisor.</small>
            <?php elseif ($isSupervisor): ?>
              <h5 class="card-title text-primary mb-3">Team Performance Management 👥</h5>
              <p class="mb-4">Evaluate and manage your team's performance. Create evaluations and track progress.</p>
            <?php else: ?>
              <h5 class="card-title text-primary mb-3">Performance Reports Overview 📈</h5>
              <p class="mb-4">View employee performance evaluations and analyze performance trends across the organization. <span class="badge bg-label-info">Read Only</span></p>
              <small class="text-muted">Only supervisors can create and modify performance evaluations.</small>
            <?php endif; ?>
            
            <div class="d-flex flex-wrap gap-2">
              <?php if ($canModify): ?>
                <button class="btn btn-sm btn-primary" id="addPerformanceBtn" data-bs-toggle="modal" data-bs-target="#addPerformanceModal">
                  <i class="bx bx-plus-circle me-1"></i>New Evaluation
                </button>
              <?php endif; ?>
              
              <?php if ($canView): ?>
                <button class="btn btn-sm btn-outline-primary" id="viewStatsBtn">
                  <i class="bx bx-stats me-1"></i>Statistics
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="exportBtn">
                  <i class="bx bx-download me-1"></i>Export
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/performance.png" height="140" alt="Performance" data-app-dark-img="illustrations/performance-dark.png" data-app-light-img="illustrations/performance.png" style="max-width: 100%;">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user"></i></span>
          </div>
          <div>
            <span class="fw-medium d-block mb-1">Total Evaluations</span>
            <h3 id="statTotalEvaluations" class="card-title mb-0">0</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-star"></i></span>
          </div>
          <div>
            <span class="fw-medium d-block mb-1">Average Rating</span>
            <h3 id="statAverageRating" class="card-title mb-0">0.0</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-trophy"></i></span>
          </div>
          <div>
            <span class="fw-medium d-block mb-1">Top Performers</span>
            <h3 id="statTopPerformers" class="card-title mb-0">0</h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-group"></i></span>
          </div>
          <div>
            <span class="fw-medium d-block mb-1">Employees Evaluated</span>
            <h3 id="statEmployeesEvaluated" class="card-title mb-0">0</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Performance Evaluations Table -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Performance Evaluations</h5>
    <div class="d-flex gap-2">
      <div class="input-group" style="width: 300px;">
        <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
        <input type="text" class="form-control" id="searchPerformance" placeholder="Search evaluations...">
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card-body border-bottom">
    <div class="row g-3">
      <?php if (!$isEmployee): ?>
      <div class="col-md-3">
        <label class="form-label">Employee</label>
        <select class="form-select" id="filterEmployee">
          <option value="">All Employees</option>
        </select>
      </div>
      <?php endif; ?>
      
      <div class="col-md-3">
        <label class="form-label">Department</label>
        <select class="form-select" id="filterDepartment">
          <option value="">All Departments</option>
        </select>
      </div>
      
      <div class="col-md-2">
        <label class="form-label">Year</label>
        <select class="form-select" id="filterYear">
          <option value="">All Years</option>
          <?php 
          $currentYear = date('Y');
          for ($year = $currentYear; $year >= ($currentYear - 5); $year--): ?>
            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
          <?php endfor; ?>
        </select>
      </div>
      
      <div class="col-md-2">
        <label class="form-label">Min Rating</label>
        <select class="form-select" id="filterMinRating">
          <option value="">Any Rating</option>
          <option value="1">1+ Stars</option>
          <option value="2">2+ Stars</option>
          <option value="3">3+ Stars</option>
          <option value="4">4+ Stars</option>
          <option value="5">5 Stars</option>
        </select>
      </div>
      
      <div class="col-md-2">
        <label class="form-label">&nbsp;</label>
        <div class="d-grid">
          <button class="btn btn-outline-secondary" id="clearFiltersBtn">
            <i class="bx bx-x me-1"></i>Clear
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <?php if (!$isEmployee): ?>
          <th>Employee</th>
          <th>Department</th>
          <?php endif; ?>
          <th>Period</th>
          <th>Rating</th>
          <th>Evaluator</th>
          <th>Remarks</th>
          <?php if ($canModify): ?>
          <th class="text-center">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody id="performanceBody">
        <!-- Data will be populated by JavaScript -->
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Edit Performance Modal -->
<div class="modal fade" id="addPerformanceModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Performance Evaluation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addPerformanceForm">
          <input type="hidden" name="performance_id" id="performance_id">
          
          <?php if (!$isEmployee): ?>
          <div class="mb-3">
            <label class="form-label">Employee *</label>
            <select class="form-select" name="employee_id" id="employee_id" required>
              <option value="">Select Employee</option>
            </select>
          </div>
          <?php endif; ?>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Period Start *</label>
                <input type="date" class="form-control" name="period_start" id="period_start" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Period End *</label>
                <input type="date" class="form-control" name="period_end" id="period_end" required>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Rating *</label>
            <div class="rating-input">
              <div class="star-rating mb-2">
                <span class="star" data-rating="1">★</span>
                <span class="star" data-rating="2">★</span>
                <span class="star" data-rating="3">★</span>
                <span class="star" data-rating="4">★</span>
                <span class="star" data-rating="5">★</span>
              </div>
              <input type="hidden" name="rating" id="rating" required>
              <div class="rating-labels d-flex justify-content-between">
                <small class="text-muted">Poor</small>
                <small class="text-muted">Below Average</small>
                <small class="text-muted">Average</small>
                <small class="text-muted">Good</small>
                <small class="text-muted">Excellent</small>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" id="remarks" rows="4" placeholder="Enter evaluation comments and feedback..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="savePerformanceBtn">Save Evaluation</button>
      </div>
    </div>
  </div>
</div>

<!-- Performance Statistics Modal -->
<div class="modal fade" id="statsModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Performance Statistics</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="statisticsContent">
          <!-- Statistics content will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Performance page styles */
.star-rating {
  font-size: 2rem;
  cursor: pointer;
}

.star {
  color: #ddd;
  transition: color 0.2s;
}

.star:hover,
.star.active {
  color: #ffc107;
}

.rating-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.rating-stars {
  color: #ffc107;
}

.performance-period {
  font-size: 0.875rem;
  color: #6c757d;
}

.performance-remarks {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.btn:hover {
  transform: translateY(-2px);
  transition: transform 0.2s;
}

.card-hover:hover {
  transform: translateY(-5px);
  transition: transform 0.3s;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
</style>

<script>
$(document).ready(function() {
  const CURRENT_USER_TYPE = <?php echo json_encode($user_type ?? ''); ?>;
  const IS_EMPLOYEE = <?php echo json_encode($isEmployee); ?>;
  
  let allPerformanceData = [];
  
  // Initialize page
  loadPerformanceData();
  loadStatistics();
  loadDropdownData();
  
  // Event listeners
  $('#searchPerformance').on('input', filterPerformances);
  $('#filterEmployee, #filterDepartment, #filterYear, #filterMinRating').on('change', filterPerformances);
  $('#clearFiltersBtn').on('click', clearFilters);
  $('#addPerformanceBtn').on('click', showAddModal);
  $('#savePerformanceBtn').on('click', savePerformance);
  $('#viewStatsBtn').on('click', showStatistics);
  $('#exportBtn').on('click', exportPerformances);
  
  // Star rating functionality
  $('.star').on('click', function() {
    const rating = $(this).data('rating');
    $('#rating').val(rating);
    updateStarDisplay(rating);
  });
  
  $('.star').on('mouseenter', function() {
    const rating = $(this).data('rating');
    highlightStars(rating);
  });
  
  $('.star-rating').on('mouseleave', function() {
    const currentRating = $('#rating').val();
    updateStarDisplay(currentRating);
  });

  function loadPerformanceData() {
    const filters = {
      employee_id: $('#filterEmployee').val(),
      department_id: $('#filterDepartment').val(),
      period_year: $('#filterYear').val(),
      rating_min: $('#filterMinRating').val()
    };

    $.ajax({
      url: '../ajax/get_performance.php',
      method: 'GET',
      data: filters,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          allPerformanceData = response.data;
          renderPerformances(allPerformanceData);
        } else {
          console.error('Error loading performance data:', response.message);
          showAlert('Error loading performance data: ' + response.message, 'error');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading performance data:', error);
        showAlert('Error loading performance data. Please try again.', 'error');
      }
    });
  }

  function loadStatistics() {
    $.ajax({
      url: '../ajax/get_performance_statistics.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          updateStatisticsCards(response.data);
        } else {
          console.error('Error loading statistics:', response.message);
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error loading statistics:', error);
      }
    });
  }

  function loadDropdownData() {
    // Load employees
    if (!IS_EMPLOYEE) {
      $.ajax({
        url: '../ajax/get_employees.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            $('#filterEmployee, #employee_id').empty().append('<option value="">Select Employee</option>');
            response.data.forEach(function(employee) {
              const option = `<option value="${employee.employee_id}">${employee.first_name} ${employee.last_name}</option>`;
              $('#filterEmployee, #employee_id').append(option);
            });
          }
        }
      });
    }

    // Load departments
    $.ajax({
      url: '../ajax/get_departments.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          $('#filterDepartment').empty().append('<option value="">All Departments</option>');
          response.data.forEach(function(dept) {
            $('#filterDepartment').append(`<option value="${dept.department_id}">${dept.department_name}</option>`);
          });
        }
      }
    });
  }

  function renderPerformances(performances) {
    const tbody = $('#performanceBody');
    tbody.empty();

    if (performances.length === 0) {
      const colspan = IS_EMPLOYEE ? 4 : 6;
      tbody.append(`
        <tr>
          <td colspan="${colspan}" class="text-center py-4">
            <div class="text-muted">
              <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
              No performance evaluations found
            </div>
          </td>
        </tr>
      `);
      return;
    }

    performances.forEach(function(perf) {
      const periodStart = new Date(perf.period_start).toLocaleDateString();
      const periodEnd = new Date(perf.period_end).toLocaleDateString();
      const stars = '★'.repeat(perf.rating) + '☆'.repeat(5 - perf.rating);
      
      let row = `
        <tr>
          ${!IS_EMPLOYEE ? `
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm flex-shrink-0 me-3">
                  <span class="avatar-initial rounded-circle bg-label-primary">${perf.employee_name.charAt(0)}</span>
                </div>
                <div>
                  <span class="fw-medium">${perf.employee_name}</span>
                </div>
              </div>
            </td>
            <td><span class="badge bg-label-info">${perf.department_name || 'N/A'}</span></td>
          ` : ''}
          <td>
            <div class="performance-period">
              <div>${periodStart}</div>
              <div>to ${periodEnd}</div>
            </div>
          </td>
          <td>
            <div class="rating-badge">
              <span class="rating-stars">${stars}</span>
              <span class="fw-medium">${perf.rating}/5</span>
            </div>
          </td>
          <td>${perf.evaluator_name || 'N/A'}</td>
          <td>
            <span class="performance-remarks" title="${perf.remarks || ''}">
              ${perf.remarks || 'No remarks'}
            </span>
          </td>
          ${canModify() ? `
            <td class="text-center">
              <div class="dropdown">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                  Actions
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="javascript:void(0);" onclick="viewPerformance(${perf.performance_id})">
                    <i class="bx bx-show me-1"></i>View Details
                  </a>
                  <a class="dropdown-item" href="javascript:void(0);" onclick="editPerformance(${perf.performance_id})">
                    <i class="bx bx-edit me-1"></i>Edit
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deletePerformance(${perf.performance_id})">
                    <i class="bx bx-trash me-1"></i>Delete
                  </a>
                </div>
              </div>
            </td>
          ` : ''}
        </tr>
      `;
      tbody.append(row);
    });
  }

  function updateStatisticsCards(stats) {
    $('#statTotalEvaluations').text(stats.overall.total_evaluations || 0);
    $('#statAverageRating').text(parseFloat(stats.overall.average_rating || 0).toFixed(1));
    $('#statTopPerformers').text(stats.overall.excellent_performers || 0);
    $('#statEmployeesEvaluated').text(stats.overall.total_employees_evaluated || 0);
  }

  function filterPerformances() {
    const searchTerm = $('#searchPerformance').val().toLowerCase();
    const employeeFilter = $('#filterEmployee').val();
    const departmentFilter = $('#filterDepartment').val();
    const yearFilter = $('#filterYear').val();
    const minRatingFilter = $('#filterMinRating').val();
    
    let filteredData = allPerformanceData.filter(function(perf) {
      const matchesSearch = !searchTerm || 
        perf.employee_name.toLowerCase().includes(searchTerm) ||
        (perf.remarks && perf.remarks.toLowerCase().includes(searchTerm));
      
      const matchesEmployee = !employeeFilter || perf.employee_id == employeeFilter;
      const matchesDepartment = !departmentFilter || perf.department_id == departmentFilter;
      const matchesYear = !yearFilter || new Date(perf.period_start).getFullYear() == yearFilter;
      const matchesRating = !minRatingFilter || perf.rating >= minRatingFilter;
      
      return matchesSearch && matchesEmployee && matchesDepartment && matchesYear && matchesRating;
    });
    
    renderPerformances(filteredData);
  }

  function clearFilters() {
    $('#searchPerformance').val('');
    $('#filterEmployee, #filterDepartment, #filterYear, #filterMinRating').val('');
    renderPerformances(allPerformanceData);
  }

  function showAddModal() {
    $('#addPerformanceModal .modal-title').text('New Performance Evaluation');
    $('#addPerformanceForm')[0].reset();
    $('#performance_id').val('');
    $('#rating').val('');
    updateStarDisplay(0);
    $('#addPerformanceModal').modal('show');
  }

  function updateStarDisplay(rating) {
    $('.star').each(function(index) {
      if (index < rating) {
        $(this).addClass('active');
      } else {
        $(this).removeClass('active');
      }
    });
  }

  function highlightStars(rating) {
    $('.star').each(function(index) {
      if (index < rating) {
        $(this).css('color', '#ffc107');
      } else {
        $(this).css('color', '#ddd');
      }
    });
  }

  function savePerformance() {
    const formData = new FormData($('#addPerformanceForm')[0]);
    const performanceId = $('#performance_id').val();
    const url = performanceId ? '../ajax/update_performance.php' : '../ajax/add_performance.php';
    
    if (performanceId) {
      formData.append('performance_id', performanceId);
    }

    $.ajax({
      url: url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          showAlert(response.message, 'success');
          $('#addPerformanceModal').modal('hide');
          loadPerformanceData();
          loadStatistics();
        } else {
          showAlert(response.message, 'error');
        }
      },
      error: function(xhr, status, error) {
        showAlert('Error saving performance evaluation. Please try again.', 'error');
      }
    });
  }

  // Global functions for actions
  window.editPerformance = function(id) {
    $.ajax({
      url: '../ajax/get_performance.php',
      method: 'GET',
      data: { performance_id: id },
      dataType: 'json',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          const perf = response.data[0];
          $('#addPerformanceModal .modal-title').text('Edit Performance Evaluation');
          $('#performance_id').val(perf.performance_id);
          $('#employee_id').val(perf.employee_id);
          $('#period_start').val(perf.period_start);
          $('#period_end').val(perf.period_end);
          $('#rating').val(perf.rating);
          $('#remarks').val(perf.remarks);
          updateStarDisplay(perf.rating);
          $('#addPerformanceModal').modal('show');
        }
      }
    });
  };

  window.deletePerformance = function(id) {
    Swal.fire({
      title: 'Delete Performance Evaluation?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '../ajax/delete_performance.php',
          method: 'POST',
          data: { performance_id: id },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              showAlert(response.message, 'success');
              loadPerformanceData();
              loadStatistics();
            } else {
              showAlert(response.message, 'error');
            }
          },
          error: function() {
            showAlert('Error deleting performance evaluation.', 'error');
          }
        });
      }
    });
  };

  window.viewPerformance = function(id) {
    // Implementation for viewing performance details
    window.editPerformance(id);
  };

  function showStatistics() {
    $('#statsModal').modal('show');
    // Load detailed statistics
  }

  function exportPerformances() {
    // Implementation for exporting performance data
    showAlert('Export functionality coming soon!', 'info');
  }

  function showAlert(message, type) {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });

    Toast.fire({
      icon: type,
      title: message
    });
  }

  function canModify() {
    return <?php echo json_encode($canModify); ?>;
  }
});
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
