<?php
$page_title = 'Job Applications';
$additional_css = [];
$additional_js = [];

include './shared/session_handler.php';

if (!isset($user_id)) {
  header('Location: ./login.php');
  exit();
}

if (!in_array($user_type, ['admin', 'hr'])) {
  header('Location: ./dashboard.php');
  exit();
}

ob_start();
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <i class='bx bx-briefcase-alt-2 me-2'></i>Job Applications
      </h4>
      <p class="text-muted mb-0">Manage applicant submissions and track hiring pipeline</p>
    </div>
    <div>
      <button class="btn btn-sm btn-outline-secondary" onclick="refreshApplicants()">
        <i class='bx bx-refresh'></i> Refresh
      </button>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class='bx bx-user-plus bx-sm'></i>
              </span>
            </div>
            <div>
              <small class="text-muted d-block">Total Applications</small>
              <h4 class="mb-0" id="totalApplicants">0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-warning">
                <i class='bx bx-time-five bx-sm'></i>
              </span>
            </div>
            <div>
              <small class="text-muted d-block">Pending Review</small>
              <h4 class="mb-0" id="pendingApplicants">0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-info">
                <i class='bx bx-calendar-check bx-sm'></i>
              </span>
            </div>
            <div>
              <small class="text-muted d-block">Interview Scheduled</small>
              <h4 class="mb-0" id="interviewApplicants">0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-success">
                <i class='bx bx-check-circle bx-sm'></i>
              </span>
            </div>
            <div>
              <small class="text-muted d-block">Accepted</small>
              <h4 class="mb-0" id="acceptedApplicants">0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters and Search -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Status Filter</label>
          <select class="form-select" id="statusFilter">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="reviewing">Reviewing</option>
            <option value="interview_scheduled">Interview Scheduled</option>
            <option value="interviewed">Interviewed</option>
            <option value="accepted">Accepted</option>
            <option value="hired">Hired</option>
            <option value="rejected">Rejected</option>
            <option value="withdrawn">Withdrawn</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Position Filter</label>
          <select class="form-select" id="positionFilter">
            <option value="">All Positions</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Branch Filter</label>
          <select class="form-select" id="branchFilter">
            <option value="">All Branches</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" class="form-control" id="searchApplicant" placeholder="Search by name or email...">
        </div>
      </div>
    </div>
  </div>

  <!-- Applicants Table -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Applicant List</h5>
      <span class="badge bg-label-primary" id="applicantCount">0 applicants</span>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>Applicant</th>
            <th>Position Applied</th>
            <th>Branch</th>
            <th>Experience</th>
            <th>Applied Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="applicantsTableBody">
          <tr>
            <td colspan="7" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="text-muted mt-2 mb-0">Loading applicants...</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <nav aria-label="Applicants pagination" id="applicantsPaginationNav" class="d-none">
      <ul class="pagination justify-content-center my-3" id="applicantsPagination">
      </ul>
    </nav>
  </div>
</div>

<!-- View Applicant Modal -->
<div class="modal fade" id="viewApplicantModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Applicant Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="applicantDetails">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="convertToEmployee()">
          <i class='bx bx-user-check'></i> Hire as Employee
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Application Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="updateStatusForm">
        <div class="modal-body">
          <input type="hidden" id="statusApplicantId">
          
          <div class="mb-3">
            <label class="form-label">New Status <span class="text-danger">*</span></label>
            <select class="form-select" id="newStatus" required>
              <option value="">Select Status</option>
              <option value="pending">Pending</option>
              <option value="reviewing">Reviewing</option>
              <option value="interview_scheduled">Interview Scheduled</option>
              <option value="interviewed">Interviewed</option>
              <option value="accepted">Accepted</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div class="mb-3" id="interviewDateGroup" style="display: none;">
            <label class="form-label">Interview Date & Time</label>
            <input type="datetime-local" class="form-control" id="interviewDate">
          </div>

          <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea class="form-control" id="statusNotes" rows="3" placeholder="Add any notes about this status change..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Status</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Documents Modal -->
<div class="modal fade" id="viewDocumentsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class='bx bx-file me-2'></i>
          <span id="documentsApplicantName">Applicant Documents</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="documentsLoader" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted mt-2">Loading documents...</p>
        </div>
        <div id="documentsContent" style="display: none;">
          <!-- Documents will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables
let applicantsData = [];
const APPLICANTS_PER_PAGE = 10;
let currentApplicantPage = 1;
let currentViewApplicantId = null;

// Initialize on page load
$(document).ready(function() {
  loadApplicants();
  loadFilters();
  
  // Filter change handlers
  $('#statusFilter, #positionFilter, #branchFilter').on('change', function() {
    refreshApplicants();
  });
  
  // Search handler with debounce
  let searchTimeout;
  $('#searchApplicant').on('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(refreshApplicants, 300);
  });

  // Show/hide interview date based on status
  $('#newStatus').on('change', function() {
    if ($(this).val() === 'interview_scheduled') {
      $('#interviewDateGroup').show();
      $('#interviewDate').attr('required', true);
    } else {
      $('#interviewDateGroup').hide();
      $('#interviewDate').attr('required', false);
    }
  });

  // Handle status update form
  $('#updateStatusForm').on('submit', function(e) {
    e.preventDefault();
    updateApplicantStatus();
  });
});

// Load all applicants
function loadApplicants() {
  const status = $('#statusFilter').val();
  const position = $('#positionFilter').val();
  const branch = $('#branchFilter').val();
  
  $.ajax({
    url: '../ajax/get_all_applicants.php',
    type: 'GET',
    data: { status, position_id: position, branch_id: branch },
    dataType: 'json',
    success: function(result) {
      if (result.success) {
        applicantsData = result.applicants || [];
        updateApplicantsTable(applicantsData);
        updateStatistics();
      } else {
        showError('Failed to load applicants');
      }
    },
    error: function() {
      showError('Error loading applicants');
    }
  });
}

// Update applicants table
function updateApplicantsTable(applicants) {
  // Filter by search term
  const searchTerm = $('#searchApplicant').val().toLowerCase();
  if (searchTerm) {
    applicants = applicants.filter(app => 
      (app.first_name + ' ' + app.last_name).toLowerCase().includes(searchTerm) ||
      app.email.toLowerCase().includes(searchTerm)
    );
  }

  const totalItems = applicants.length;
  const totalPages = Math.ceil(totalItems / APPLICANTS_PER_PAGE);
  const startIndex = (currentApplicantPage - 1) * APPLICANTS_PER_PAGE;
  const endIndex = startIndex + APPLICANTS_PER_PAGE;
  const paginatedApplicants = applicants.slice(startIndex, endIndex);

  $('#applicantCount').text(`${totalItems} applicant${totalItems !== 1 ? 's' : ''}`);

  let html = '';
  if (paginatedApplicants.length > 0) {
    paginatedApplicants.forEach(app => {
      const fullName = `${app.first_name} ${app.middle_name || ''} ${app.last_name}`.trim();
      const statusBadge = getStatusBadge(app.status);
      const appliedDate = new Date(app.application_date).toLocaleDateString('en-US', { 
        year: 'numeric', month: 'short', day: 'numeric' 
      });

      html += `
        <tr>
          <td>
            <div class="d-flex flex-column">
              <strong>${fullName}</strong>
              <small class="text-muted">${app.email}</small>
              ${app.phone ? `<small class="text-muted"><i class='bx bx-phone'></i> ${app.phone}</small>` : ''}
            </div>
          </td>
          <td>${app.position_name || '<span class="text-muted">Not specified</span>'}</td>
          <td>${app.branch_name || '<span class="text-muted">Any</span>'}</td>
          <td>${app.experience_years ? app.experience_years + ' years' : '<span class="text-muted">N/A</span>'}</td>
          <td>
            <small>${appliedDate}</small>
            ${app.interview_date ? `<br><small class="text-info"><i class='bx bx-calendar'></i> ${new Date(app.interview_date).toLocaleDateString()}</small>` : ''}
          </td>
          <td>${statusBadge}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);" onclick="viewApplicant(${app.applicant_id})">
                  <i class="bx bx-show me-1"></i> View Details
                </a>
                <a class="dropdown-item" href="javascript:void(0);" onclick="viewDocuments(${app.applicant_id}, '${fullName}')">
                  <i class="bx bx-file me-1"></i> View Documents
                </a>
                <a class="dropdown-item" href="javascript:void(0);" onclick="showUpdateStatus(${app.applicant_id})">
                  <i class="bx bx-edit me-1"></i> Update Status
                </a>
                ${app.status === 'accepted' ? `
                <a class="dropdown-item text-success" href="javascript:void(0);" onclick="viewApplicant(${app.applicant_id})">
                  <i class="bx bx-user-check me-1"></i> Hire as Employee
                </a>` : ''}
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteApplicant(${app.applicant_id})">
                  <i class="bx bx-trash me-1"></i> Delete
                </a>
              </div>
            </div>
          </td>
        </tr>
      `;
    });
  } else {
    html = `
      <tr>
        <td colspan="7" class="text-center py-4">
          <i class='bx bx-user-x bx-lg text-muted mb-2'></i>
          <p class="text-muted mb-0">No applicants found</p>
        </td>
      </tr>
    `;
  }

  $('#applicantsTableBody').html(html);
  updatePagination(totalPages, totalItems);
}

// Get status badge HTML
function getStatusBadge(status) {
  const badges = {
    'pending': '<span class="badge bg-label-warning">Pending</span>',
    'reviewing': '<span class="badge bg-label-info">Reviewing</span>',
    'interview_scheduled': '<span class="badge bg-label-primary">Interview Scheduled</span>',
    'interviewed': '<span class="badge bg-label-secondary">Interviewed</span>',
    'accepted': '<span class="badge bg-label-success">Accepted</span>',
    'hired': '<span class="badge bg-success">Hired</span>',
    'rejected': '<span class="badge bg-label-danger">Rejected</span>',
    'withdrawn': '<span class="badge bg-label-dark">Withdrawn</span>'
  };
  return badges[status] || '<span class="badge bg-label-secondary">' + status + '</span>';
}

// Update statistics cards
function updateStatistics() {
  const total = applicantsData.length;
  const pending = applicantsData.filter(a => a.status === 'pending').length;
  const interview = applicantsData.filter(a => a.status === 'interview_scheduled').length;
  const accepted = applicantsData.filter(a => a.status === 'accepted').length;

  $('#totalApplicants').text(total);
  $('#pendingApplicants').text(pending);
  $('#interviewApplicants').text(interview);
  $('#acceptedApplicants').text(accepted);
}

// Load filter dropdowns
function loadFilters() {
  // Load positions
  $.get('../ajax/get_dropdown_data.php', function(response) {
    if (response.success) {
      if (response.positions) {
        response.positions.forEach(pos => {
          $('#positionFilter').append(`<option value="${pos.id}">${pos.name}</option>`);
        });
      }
      if (response.branches) {
        response.branches.forEach(branch => {
          $('#branchFilter').append(`<option value="${branch.id}">${branch.name}</option>`);
        });
      }
    }
  }, 'json');
}

// View applicant details
function viewApplicant(id) {
  currentViewApplicantId = id;
  $('#viewApplicantModal').modal('show');
  
  $.ajax({
    url: '../ajax/get_applicant_profile.php',
    type: 'POST',
    data: { applicant_id: id },
    dataType: 'json',
    success: function(result) {
      if (result.success) {
        displayApplicantDetails(result.applicant);
      } else {
        $('#applicantDetails').html('<p class="text-danger">Failed to load applicant details</p>');
      }
    },
    error: function() {
      $('#applicantDetails').html('<p class="text-danger">Error loading applicant details</p>');
    }
  });
}

// Display applicant details in modal
function displayApplicantDetails(app) {
  const fullName = `${app.first_name} ${app.middle_name || ''} ${app.last_name}`.trim();
  
  const html = `
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="alert alert-${app.status === 'accepted' ? 'success' : 'info'}">
          <strong>Status:</strong> ${getStatusBadge(app.status)}
        </div>
      </div>
    </div>
    
    <h6 class="mb-3">Personal Information</h6>
    <div class="row mb-3">
      <div class="col-md-6">
        <p><strong>Full Name:</strong><br>${fullName}</p>
      </div>
      <div class="col-md-6">
        <p><strong>Email:</strong><br>${app.email}</p>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <p><strong>Phone:</strong><br>${app.phone || 'N/A'}</p>
      </div>
      <div class="col-md-6">
        <p><strong>Date of Birth:</strong><br>${app.date_of_birth || 'N/A'}</p>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-12">
        <p><strong>Address:</strong><br>${app.address || 'N/A'}</p>
      </div>
    </div>
    
    <hr>
    <h6 class="mb-3">Application Details</h6>
    <div class="row mb-3">
      <div class="col-md-6">
        <p><strong>Position Applied:</strong><br>${app.position_name || 'Not specified'}</p>
      </div>
      <div class="col-md-6">
        <p><strong>Branch:</strong><br>${app.branch_name || 'Any branch'}</p>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <p><strong>Experience:</strong><br>${app.experience_years ? app.experience_years + ' years' : 'N/A'}</p>
      </div>
      <div class="col-md-6">
        <p><strong>Application Date:</strong><br>${new Date(app.application_date).toLocaleDateString()}</p>
      </div>
    </div>
    ${app.skills ? `
    <div class="row mb-3">
      <div class="col-md-12">
        <p><strong>Skills:</strong><br>${app.skills}</p>
      </div>
    </div>
    ` : ''}
    
    ${app.interview_date ? `
    <hr>
    <h6 class="mb-3">Interview Details</h6>
    <div class="row mb-3">
      <div class="col-md-6">
        <p><strong>Interview Date:</strong><br>${new Date(app.interview_date).toLocaleString()}</p>
      </div>
      <div class="col-md-6">
        <p><strong>Interviewer:</strong><br>${app.interviewer_name || 'Not assigned'}</p>
      </div>
    </div>
    ` : ''}
  `;
  
  $('#applicantDetails').html(html);
}

// View applicant documents
function viewDocuments(applicantId, applicantName) {
  $('#documentsApplicantName').text(applicantName + ' - Documents');
  $('#viewDocumentsModal').modal('show');
  $('#documentsLoader').show();
  $('#documentsContent').hide();
  
  $.ajax({
    url: '../ajax/get_applicant_documents.php',
    type: 'GET',
    data: { applicant_id: applicantId },
    dataType: 'json',
    success: function(result) {
      $('#documentsLoader').hide();
      
      if (result.success && result.documents.length > 0) {
        let html = '<div class="list-group list-group-flush">';
        
        result.documents.forEach(doc => {
          const icon = getDocumentIcon(doc.type);
          const fileSize = doc.size ? formatFileSize(doc.size) : '';
          const uploadDate = doc.uploaded_at ? 
            `<small class="text-muted">Uploaded: ${new Date(doc.uploaded_at).toLocaleDateString()}</small>` : '';
          
          html += `
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class='bx ${icon} bx-md me-3 text-primary'></i>
                <div>
                  <h6 class="mb-0">${doc.name}</h6>
                  <small class="text-muted">${fileSize}</small>
                  ${uploadDate}
                </div>
              </div>
              <div>
                <a href="${doc.url}" target="_blank" class="btn btn-sm btn-primary">
                  <i class='bx bx-show'></i> View
                </a>
                <a href="${doc.url}" download class="btn btn-sm btn-outline-secondary">
                  <i class='bx bx-download'></i> Download
                </a>
              </div>
            </div>
          `;
        });
        
        html += '</div>';
        $('#documentsContent').html(html).show();
      } else {
        $('#documentsContent').html(`
          <div class="text-center py-4">
            <i class='bx bx-file-blank bx-lg text-muted mb-2'></i>
            <p class="text-muted">No documents found</p>
          </div>
        `).show();
      }
    },
    error: function() {
      $('#documentsLoader').hide();
      $('#documentsContent').html(`
        <div class="alert alert-danger">
          <i class='bx bx-error'></i> Error loading documents. Please try again.
        </div>
      `).show();
    }
  });
}

// Get document icon based on type
function getDocumentIcon(type) {
  const icons = {
    'resume': 'bxs-file-doc',
    'cover_letter': 'bxs-file',
    'certificate': 'bxs-award',
    'portfolio': 'bxs-image',
    'default': 'bxs-file'
  };
  return icons[type] || icons['default'];
}

// Format file size
function formatFileSize(bytes) {
  if (!bytes) return '';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

// Show update status modal
function showUpdateStatus(id) {
  currentViewApplicantId = id;
  $('#statusApplicantId').val(id);
  $('#updateStatusForm')[0].reset();
  $('#interviewDateGroup').hide();
  $('#updateStatusModal').modal('show');
}

// Update applicant status
function updateApplicantStatus() {
  const formData = {
    applicant_id: $('#statusApplicantId').val(),
    status: $('#newStatus').val(),
    notes: $('#statusNotes').val(),
    interview_date: $('#interviewDate').val()
  };

  $.ajax({
    url: '../ajax/update_applicant_status.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(result) {
      if (result.success) {
        $('#updateStatusModal').modal('hide');
        Swal.fire('Success!', 'Application status updated', 'success');
        refreshApplicants();
      } else {
        Swal.fire('Error!', result.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Error!', 'Failed to update status', 'error');
    }
  });
}

// Convert applicant to employee
function convertToEmployee() {
  if (!currentViewApplicantId) return;
  
  // Redirect to employee management with applicant pre-selected
  $('#viewApplicantModal').modal('hide');
  window.location.href = './employee-management.php?import_applicant=' + currentViewApplicantId;
}

// Delete applicant
function deleteApplicant(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: "This will permanently delete the applicant's data!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      // Implement delete functionality
      Swal.fire('Info', 'Delete functionality to be implemented', 'info');
    }
  });
}

// Pagination
function updatePagination(totalPages, totalItems) {
  if (totalPages <= 1) {
    $('#applicantsPaginationNav').addClass('d-none');
    return;
  }

  $('#applicantsPaginationNav').removeClass('d-none');
  let html = '';

  // Previous button
  html += `
    <li class="page-item ${currentApplicantPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="javascript:void(0);" onclick="changePage(${currentApplicantPage - 1})">
        <i class="tf-icon bx bx-chevrons-left"></i>
      </a>
    </li>
  `;

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= currentApplicantPage - 1 && i <= currentApplicantPage + 1)) {
      html += `
        <li class="page-item ${i === currentApplicantPage ? 'active' : ''}">
          <a class="page-link" href="javascript:void(0);" onclick="changePage(${i})">${i}</a>
        </li>
      `;
    } else if (i === currentApplicantPage - 2 || i === currentApplicantPage + 2) {
      html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
  }

  // Next button
  html += `
    <li class="page-item ${currentApplicantPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="javascript:void(0);" onclick="changePage(${currentApplicantPage + 1})">
        <i class="tf-icon bx bx-chevrons-right"></i>
      </a>
    </li>
  `;

  $('#applicantsPagination').html(html);
}

function changePage(page) {
  const totalPages = Math.ceil(applicantsData.length / APPLICANTS_PER_PAGE);
  if (page >= 1 && page <= totalPages) {
    currentApplicantPage = page;
    updateApplicantsTable(applicantsData);
  }
}

// Utility functions
function refreshApplicants() {
  currentApplicantPage = 1;
  loadApplicants();
}

function showError(message) {
  Swal.fire('Error!', message, 'error');
}
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
