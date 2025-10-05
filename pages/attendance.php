<?php
$page_title = 'Attendance';
$additional_css = [
  // add page-specific css if needed
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
  <!-- Welcome Card (match employee-management style) -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="d-flex align-items-start row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary mb-3">Attendance 📋</h5>
            <p class="mb-4">View and manage daily attendance records. This is a design-only page with dummy data.</p>
            <button class="btn btn-sm btn-primary" id="addAttendanceBtn">
              <i class="bx bx-plus-circle me-1"></i>Add Attendance
            </button>
            <button class="btn btn-sm btn-outline-secondary ms-2" id="exportAttendanceBtn">Export CSV</button>
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
            <option>HR</option>
            <option>Engineering</option>
            <option>Sales</option>
          </select>
          <select class="form-select form-select-sm w-px-150" id="filterStatus">
            <option value="">All</option>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="onleave">On Leave</option>
          </select>
          <input type="text" class="form-control form-control-sm w-px-200" id="searchAttendance" placeholder="Search employees...">
          <button class="btn btn-sm btn-primary" id="refreshAttendanceBtn"><i class="bx bx-refresh me-1"></i>Refresh</button>
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

        <!-- Pagination (static for design) -->
        <nav aria-label="Attendance pagination" class="mt-3">
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

<style>
/* Attendance small tweaks */
#filterDate { max-width: 190px; }
.table td, .table th { vertical-align: middle; }
</style>

<script>
  (function waitForjQuery() {
    if (typeof $ !== 'undefined') {
      initAttendance();
    } else {
      setTimeout(waitForjQuery, 50);
    }
  })();

  const dummyAttendance = [
    { id: 'EMP-0012', name: 'Maria Santos', department: 'Engineering', time_in: '08:45', time_out: '17:15', status: 'present', notes: 'On time' },
    { id: 'EMP-0005', name: 'John Doe', department: 'HR', time_in: '', time_out: '', status: 'absent', notes: 'Uninformed' },
    { id: 'EMP-0034', name: 'Leila Karim', department: 'Sales', time_in: '09:30', time_out: '18:00', status: 'onleave', notes: 'Half day' },
    { id: 'EMP-0021', name: 'Pedro Alvarez', department: 'Engineering', time_in: '08:55', time_out: '17:05', status: 'present', notes: 'Late by 10m' }
  ];

  function initAttendance() {
    $(document).ready(function () {
      renderAttendance(dummyAttendance);

      $('#searchAttendance').on('keyup', function () { filterAndRender(); });
      $('#filterDepartment, #filterStatus, #filterDate').on('change', function () { filterAndRender(); });
      $('#refreshAttendanceBtn').on('click', function () { renderAttendance(dummyAttendance); });
    });
  }

  function renderAttendance(items) {
    const $body = $('#attendanceBody');
    if (!items || items.length === 0) {
      $body.html('<tr><td colspan="9" class="text-center text-muted py-4">No records</td></tr>');
      return;
    }
    let html = '';
    items.forEach((r, i) => {
      const statusLabel = r.status === 'present' ? '<span class="badge bg-success">Present</span>' : (r.status === 'absent' ? '<span class="badge bg-danger">Absent</span>' : '<span class="badge bg-warning text-dark">On Leave</span>');
      html += `
        <tr>
          <th scope="row">${i+1}</th>
          <td>${r.name}</td>
          <td>${r.id}</td>
          <td>${r.department}</td>
          <td>${r.time_in || '--'}</td>
          <td>${r.time_out || '--'}</td>
          <td>${statusLabel}</td>
          <td>${r.notes || ''}</td>
          <td class="text-center"><button class="btn btn-sm btn-outline-secondary">View</button></td>
        </tr>
      `;
    });
    $body.html(html);
  }

  function filterAndRender() {
    const q = $('#searchAttendance').val().toLowerCase();
    const dept = $('#filterDepartment').val();
    const status = $('#filterStatus').val();

    const filtered = dummyAttendance.filter(r => {
      if (dept && r.department !== dept) return false;
      if (status) {
        if (status === 'present' && r.status !== 'present') return false;
        if (status === 'absent' && r.status !== 'absent') return false;
        if (status === 'onleave' && r.status !== 'onleave') return false;
      }
      if (q && !(r.name.toLowerCase().includes(q) || r.id.toLowerCase().includes(q))) return false;
      return true;
    });

    renderAttendance(filtered);
  }
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
