<?php
$page_title = 'Attendance';
$additional_css = [
];
$additional_js = [];

include './shared/session_handler.php';

if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}

ob_start();
?>

<div class="row">

  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Attendance 📋</h5>
            <p class="mb-4">View and manage daily attendance records with comprehensive filtering and export options.
            </p>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="bx bx-download me-1"></i>Export Data
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item export-option" href="#" data-format="csv">
                    <i class="bx bx-file me-2"></i>Export as CSV
                  </a></li>
                <li><a class="dropdown-item export-option" href="#" data-format="pdf">
                    <i class="bx bx-file-doc me-2"></i>Export as PDF
                  </a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/attendance.png" height="140" alt="Attendance">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Attendance List -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-calendar-check me-2"></i>Daily Attendance</h5>
        <div class="d-flex gap-2">
          <input type="date" class="form-control form-control-sm" id="filterDate" value="<?php echo date('Y-m-d'); ?>">
          <select class="form-select form-select-sm w-px-200" id="filterDepartment">
            <option value="">All Departments</option>
            <!-- Populated dynamically from database -->
          </select>
          <select class="form-select form-select-sm w-px-150" id="filterStatus">
            <option value="">All</option>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="on leave">On Leave</option>
            <option value="overtime">Overtime</option>
          </select>
          <input type="text" class="form-control form-control-sm w-px-200" id="searchAttendance"
            placeholder="Search employees...">
          <button class="btn btn-sm btn-primary" id="refreshAttendanceBtn"><i
              class="bx bx-refresh me-1"></i>Refresh</button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-borderless" id="attendanceTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Employee</th>
                <th>ID</th>
                <th>Department</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
                <th>Notes</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="attendanceBody">
              <!-- populated by client-side dummy data -->
            </tbody>
          </table>
        </div>

        <!-- Pagination and Info -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="pagination-info text-muted">
            <!-- Populated by JavaScript -->
          </div>
          <nav aria-label="Attendance pagination">
            <ul class="pagination pagination-sm">
              <!-- Populated by JavaScript -->
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Attendance Details Modal -->
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1" aria-labelledby="attendanceDetailsModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="attendanceDetailsModalLabel">
          <i class="bx bx-calendar-check me-2"></i>Attendance Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <!-- Employee Information -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-user me-1"></i>Employee Information</h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label fw-bold">Name:</label>
                  <div id="modal-employee-name" class="text-muted">-</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Employee Number:</label>
                  <div id="modal-employee-number" class="text-muted">-</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Department:</label>
                  <div id="modal-department" class="text-muted">-</div>
                </div>
                <div class="mb-0">
                  <label class="form-label fw-bold">Position:</label>
                  <div id="modal-position" class="text-muted">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Attendance Information -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-time me-1"></i>Attendance Information</h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label fw-bold">Date:</label>
                  <div id="modal-date" class="text-muted">-</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Time In:</label>
                  <div id="modal-time-in" class="text-muted">-</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Time Out:</label>
                  <div id="modal-time-out" class="text-muted">-</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">Hours Worked:</label>
                  <div id="modal-hours-worked" class="text-muted">-</div>
                </div>
                <div class="mb-0">
                  <label class="form-label fw-bold">Status:</label>
                  <div id="modal-status" class="text-muted">-</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Remarks Section -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-note me-1"></i>Remarks</h6>
              </div>
              <div class="card-body">
                <div id="modal-remarks" class="text-muted">No remarks</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  /* Attendance small tweaks */
  #filterDate {
    max-width: 190px;
  }

  .table td,
  .table th {
    vertical-align: middle;
  }

  .modal-body .card {
    border: 1px solid #e0e0e0;
  }

  .modal-body .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
  }
</style>

<script>
  (function waitForjQuery() {
    if (typeof $ !== 'undefined') {
      initAttendance();
    } else {
      setTimeout(waitForjQuery, 50);
    }
  })();

  let currentPage = 1;
  let totalPages = 1;
  let isLoading = false;

  function initAttendance() {
    $(document).ready(function () {
      // Load departments for filter
      loadDepartments();

      // Load initial attendance data
      loadAttendanceData();

      $('#searchAttendance').on('keyup debounce', debounce(function () {
        currentPage = 1; 
        loadAttendanceData();
      }, 500));

      $('#filterDepartment, #filterStatus, #filterDate').on('change', function () {
        currentPage = 1; 
        loadAttendanceData();
      });

      $('#refreshAttendanceBtn').on('click', function () {
        loadAttendanceData();
      });

      $(document).on('click', '.pagination .page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && page !== currentPage && !isLoading) {
          currentPage = page;
          loadAttendanceData();
        }
      });

      $(document).on('click', '.view-attendance-btn', function () {
        const attendanceId = $(this).data('attendance-id');
        viewAttendanceDetails(attendanceId);
      });

      $('.export-option').on('click', function (e) {
        e.preventDefault();
        const format = $(this).data('format');
        console.log('Export clicked with format:', format);
        exportAttendanceData(format);
      });
    });
  }

  function loadDepartments() {
    $.ajax({
      url: '../ajax/get_attendance_departments.php',
      method: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          const $select = $('#filterDepartment');
          const allOption = $select.find('option[value=""]');
          $select.empty().append(allOption);

          response.data.forEach(dept => {
            $select.append(`<option value="${dept.department_id}">${dept.department_name}</option>`);
          });
        }
      },
      error: function () {
        console.error('Failed to load departments');
      }
    });
  }

  function loadAttendanceData() {
    if (isLoading) return;

    isLoading = true;
    const $refreshBtn = $('#refreshAttendanceBtn');
    const originalText = $refreshBtn.html();
    $refreshBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i>Loading...');

    const filters = {
      date: $('#filterDate').val(),
      department: $('#filterDepartment').val(),
      status: $('#filterStatus').val(),
      search: $('#searchAttendance').val(),
      page: currentPage,
      limit: 25
    };

    $.ajax({
      url: '../ajax/get_attendance.php',
      method: 'GET',
      data: filters,
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          renderAttendance(response.data);
          updatePagination(response.pagination);
        } else {
          showError('Failed to load attendance data: ' + response.message);
          renderAttendance([]);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
        showError('Failed to load attendance data. Please try again.');
        renderAttendance([]);
      },
      complete: function () {
        isLoading = false;
        $refreshBtn.html(originalText);
      }
    });
  }

  function renderAttendance(items) {
    const $body = $('#attendanceBody');
    if (!items || items.length === 0) {
      $body.html('<tr><td colspan="9" class="text-center text-muted py-4">No attendance records found for the selected criteria</td></tr>');
      return;
    }

    let html = '';
    items.forEach((record, index) => {
      const rowNumber = ((currentPage - 1) * 25) + index + 1;
      html += `
        <tr>
          <th scope="row">${rowNumber}</th>
          <td>${record.employee_name}</td>
          <td>${record.employee_number || 'N/A'}</td>
          <td>${record.department_name}</td>
          <td>${record.time_in || '--'}</td>
          <td>${record.time_out || '--'}</td>
          <td>${record.status_badge}</td>
          <td>${record.remarks || ''}</td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary view-attendance-btn" data-attendance-id="${record.attendance_id}">
              <i class="bx bx-show me-1"></i>View
            </button>
          </td>
        </tr>
      `;
    });
    $body.html(html);
  }

  function updatePagination(pagination) {
    currentPage = pagination.current_page;
    totalPages = pagination.total_pages;

    const $pagination = $('.pagination');
    let html = '';

    // Previous button
    html += `<li class="page-item ${!pagination.has_prev ? 'disabled' : ''}">
              <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
             </li>`;

    // Page numbers (show max 5 pages around current)
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
      html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
               </li>`;
    }

    // Next button
    html += `<li class="page-item ${!pagination.has_next ? 'disabled' : ''}">
              <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
             </li>`;

    $pagination.html(html);

    // Update pagination info
    const start = ((currentPage - 1) * pagination.records_per_page) + 1;
    const end = Math.min(currentPage * pagination.records_per_page, pagination.total_records);
    $('.pagination-info').text(`Showing ${start}-${end} of ${pagination.total_records} records`);
  }

  function viewAttendanceDetails(attendanceId) {
    $.ajax({
      url: '../ajax/get_attendance_details.php',
      method: 'POST',
      data: JSON.stringify({ attendance_id: attendanceId }),
      contentType: 'application/json',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          showAttendanceDetailsModal(response.data);
        } else {
          showError('Failed to load attendance details: ' + response.message);
        }
      },
      error: function () {
        showError('Failed to load attendance details. Please try again.');
      }
    });
  }

  function showAttendanceDetailsModal(details) {
    // Populate modal fields
    $('#modal-employee-name').text(details.employee_name || 'N/A');
    $('#modal-employee-number').text(details.employee_number || 'N/A');
    $('#modal-department').text(details.department_name || 'N/A');
    $('#modal-position').text(details.position_name || 'N/A');
    $('#modal-date').text(details.date || 'N/A');
    $('#modal-time-in').text(details.time_in || 'Not recorded');
    $('#modal-time-out').text(details.time_out || 'Not recorded');
    $('#modal-hours-worked').text(details.hours_worked ? details.hours_worked + ' hours' : 'N/A');
    $('#modal-status').html(details.status_badge || details.status);
    $('#modal-remarks').text(details.remarks || 'No remarks');

    // Show the modal
    $('#attendanceDetailsModal').modal('show');
  }

  function showError(message) {
    // Create a temporary alert div
    const alertHtml = `
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error-circle me-2"></i>
        <strong>Error:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;

    // Show at the top of the card
    const $alertContainer = $('.card-body').first();
    $alertContainer.prepend(alertHtml);

    // Auto-hide after 5 seconds
    setTimeout(() => {
      $('.alert-danger').fadeOut(() => {
        $('.alert-danger').remove();
      });
    }, 5000);
  }

  // Export functionality
  function exportAttendanceData(format) {
    console.log('Export function called with format:', format);

    // Get current filters
    const filters = {
      date: $('#filterDate').val(),
      department: $('#filterDepartment').val(),
      status: $('#filterStatus').val(),
      search: $('#searchAttendance').val(),
      format: format
    };

    console.log('Export filters:', filters);

    // Show loading state
    const $exportBtn = $('.dropdown-toggle');
    const originalText = $exportBtn.html();
    $exportBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i>Exporting...');
    $exportBtn.prop('disabled', true);

    // Use AJAX with blob response for file downloads
    $.ajax({
      url: '../ajax/export_attendance.php',
      method: 'POST',
      data: filters,
      xhrFields: {
        responseType: 'blob'
      },
      success: function (data, status, xhr) {
        console.log('Export response received');

        // Get filename from Content-Disposition header
        const contentDisposition = xhr.getResponseHeader('Content-Disposition');
        let filename = 'attendance_report.' + (format === 'pdf' ? 'pdf' : 'csv');

        if (contentDisposition) {
          const matches = contentDisposition.match(/filename="([^"]+)"/);
          if (matches) {
            filename = matches[1];
          }
        }

        if (format === 'pdf') {
          // For PDF, create blob URL and open in new window
          const blob = new Blob([data], { type: 'application/pdf' });
          const url = window.URL.createObjectURL(blob);
          const newWindow = window.open(url, '_blank');

          // Clean up the blob URL after a delay
          setTimeout(() => {
            window.URL.revokeObjectURL(url);
          }, 1000);

          if (!newWindow || newWindow.closed) {
            showError('Pop-up blocked. Please allow pop-ups for this site to export PDF.');
          }
        } else {
          // For CSV, create download link
          const blob = new Blob([data], { type: 'text/csv' });
          const url = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.download = filename;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);
        }
      },
      error: function (xhr, status, error) {
        console.error('Export failed:', error);
        let errorMessage = 'Export failed. ';

        if (xhr.responseText) {
          errorMessage += xhr.responseText;
        } else {
          errorMessage += 'Please try again.';
        }

        showError(errorMessage);
      },
      complete: function () {
        // Reset button state
        $exportBtn.html(originalText);
        $exportBtn.prop('disabled', false);
      }
    });
  }

  // Debounce function for search
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>

