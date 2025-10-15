<?php
$page_title = 'Branches Management';
$additional_css = ['https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'];
$additional_js = [
  '../assets/js/branch-management.js',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js'
];

include './shared/session_handler.php';

if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}

// Restrict access to admin, supervisor, and HR only
if (!in_array($user_type, ['admin', 'supervisor', 'hr'])) {
  header('Location: ./index.php');
  exit();
}

ob_start();
?>

<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-store me-2"></i>Branches</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBranchModal">
          <i class="bx bx-plus me-1"></i>Add Branch
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Branch Name</th>
                <th>Manager</th>
                <th>Employees</th>
                <th>Address</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="branchesTable">
              <!-- Will be populated via AJAX -->
            </tbody>
          </table>
        </div>
        <!-- Branches Pagination -->
        <nav aria-label="Branches pagination" id="branchesPaginationNav" class="d-none">
          <ul class="pagination pagination-sm justify-content-center" id="branchesPagination">
            <!-- Will be populated via JavaScript -->
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<?php
include 'modals/branch_modals.php';

$content = ob_get_clean();
include './shared/layout.php';
?>

