<?php
include './controllers/UserManagementController.php';

$page_title = 'User Management';
$additional_css = [
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'
];
$additional_js = [
  'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js'
];

// Get users from database
try {
  $userController = new UserManagementController();
  $users = $userController->getAllUsers();
} catch (Exception $e) {
  $users = [];
  $error_message = "Error loading users: " . $e->getMessage();
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
            <h5 class="card-title text-primary mb-3">User Management 🔐</h5>
            <p class="mb-4">
              Manage your system users, roles and permissions from this centralized dashboard.
            </p>
            <!-- <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
              <i class="bx bx-plus-circle me-1"></i>Add New User
            </button> -->
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="../assets/img/illustrations/security.jpg" height="170" alt="User Management">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Users List -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="m-0"><i class="bx bx-list-ul me-2"></i>System Users</h5>
        <div class="d-flex gap-2">
          <input type="text" class="form-control form-control-sm w-px-200" id="searchUser"
            placeholder="Search users...">
          <button class="btn btn-sm btn-primary" onclick="refreshUsers()">
            <i class="bx bx-refresh me-1"></i>Refresh
          </button>
        </div>
      </div>
      <div class="card-body">
        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger">
            <?= htmlspecialchars($error_message) ?>
          </div>
        <?php endif; ?>

        <div class="table-responsive text-nowrap">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th class="text-truncate fw-medium">User</th>
                <th class="text-truncate fw-medium">Username</th>
                <th class="text-truncate fw-medium">Role</th>
                <th class="text-truncate text-center fw-medium">Actions</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <!-- Users will be loaded here via AJAX -->
            </tbody>
          </table>
        </div>
        <!-- Users Pagination -->
        <nav aria-label="Users pagination" id="usersPaginationNav" class="d-none">
          <ul class="pagination pagination-sm justify-content-center" id="usersPagination">
            <!-- Will be populated via JavaScript -->
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<?php
// Include the modals
include 'modals/add-user-modal.php';
include 'modals/edit-user-modal.php';
?>

<script>
  // Global variables for pagination
  let usersData = [];
  const USERS_PER_PAGE = 5;
  let currentUserPage = 1;

  // Load users on page load
  $(document).ready(function () {
    // Wait for jQuery to be loaded
    function waitForJQuery() {
      if (typeof $ !== 'undefined') {
        refreshUsers();
      } else {
        setTimeout(waitForJQuery, 50);
      }
    }
    waitForJQuery();

    // Search functionality
    $('#searchUser').on('keyup', function () {
      const searchTerm = $(this).val().toLowerCase();
      $('#usersTableBody tr').each(function () {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.includes(searchTerm));
      });
    });
  });

  function refreshUsers() {
    $.ajax({
      url: '../ajax/get_users.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          usersData = response.users;
          updateUsersTable(response.users);
        } else {
          Swal.fire('Error!', response.message, 'error');
        }
      },
      error: function () {
        Swal.fire('Error!', 'Failed to load users', 'error');
      }
    });
  }

  function updateUsersTable(users) {
    // Calculate pagination
    const totalItems = users.length;
    const totalPages = Math.ceil(totalItems / USERS_PER_PAGE);
    const startIndex = (currentUserPage - 1) * USERS_PER_PAGE;
    const endIndex = startIndex + USERS_PER_PAGE;
    const paginatedUsers = users.slice(startIndex, endIndex);

    let html = '';

    if (paginatedUsers.length === 0) {
      html = '<tr><td colspan="4" class="text-center">No users found</td></tr>';
    } else {
      paginatedUsers.forEach(user => {
        const displayName = user.first_name ? `${user.first_name} ${user.last_name || ''}`.trim() : 'N/A';
        const avatar = user.first_name ? user.first_name.charAt(0).toUpperCase() : user.username.charAt(0).toUpperCase();
        const badgeColor = user.user_type && user.user_type.toLowerCase() === 'admin' ? 'primary' : 'secondary';
        const statusColor = user.active_status === 'active' ? 'success' : user.active_status === 'locked' ? 'danger' : 'warning';

        html += `
          <tr data-user-id="${user.user_id}">
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded-circle bg-label-${badgeColor}">
                    ${avatar}
                  </span>
                </div>
                <div>
                  <span class="user-name">${displayName}</span>
                  ${user.email ? `<small class="text-muted d-block">${user.email}</small>` : ''}
                </div>
              </div>
            </td>
            <td>
              <span class="username">${user.username}</span>
              <small class="text-muted d-block">
                <span class="badge bg-label-${statusColor}">${user.active_status}</span>
              </small>
            </td>
            <td>
              <span class="badge bg-label-${badgeColor} user-role">
                ${user.user_type ? user.user_type.toUpperCase() : 'N/A'}
              </span>
            </td>
            <td class="text-center">
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="javascript:void(0);" onclick="editUser(${user.user_id})">
                    <i class="bx bx-edit-alt me-1"></i> Edit
                  </a>
                  <a class="dropdown-item" href="javascript:void(0);" onclick="resetPassword(${user.user_id}, '${user.username}')">
                    <i class="bx bx-key me-1"></i> Reset Password
                  </a>
                  <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteUser(${user.user_id})">
                    <i class="bx bx-trash me-1"></i> Delete
                  </a>
                </div>
              </div>
            </td>
          </tr>
        `;
      });
    }

    $('#usersTableBody').html(html);
    updateUsersPagination(totalPages, totalItems);
  }

  function editUser(userId) {
    $.get('../ajax/get_user.php', { id: userId }, function (response) {
      if (response.success) {
        const user = response.user;

        // Populate form fields
        $('#edit_user_id').val(user.user_id);
        $('#edit_username').val(user.username);
        $('#edit_user_type').val(user.user_type_id);
        $('#edit_status').val(user.active_status);
        $('#edit_first_name').val(user.first_name || '');
        $('#edit_last_name').val(user.last_name || '');
        $('#edit_email').val(user.email || '');

        // Show modal
        $('#editUserModal').modal('show');
      } else {
        Swal.fire('Error!', response.message, 'error');
      }
    }, 'json').fail(function () {
      Swal.fire('Error!', 'Failed to load user data', 'error');
    });
  }

  function deleteUser(userId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('../ajax/delete_user.php', { id: userId }, function (response) {
          if (response.success) {
            Swal.fire('Deleted!', 'User has been deleted.', 'success');
            refreshUsers();
          } else {
            Swal.fire('Error!', response.message, 'error');
          }
        }, 'json').fail(function () {
          Swal.fire('Error!', 'Failed to delete user', 'error');
        });
      }
    });
  }

  // === PAGINATION FUNCTIONS ===
  function updateUsersPagination(totalPages, totalItems) {
    if (totalPages <= 1) {
      $('#usersPaginationNav').addClass('d-none');
      return;
    }

    $('#usersPaginationNav').removeClass('d-none');
    let paginationHtml = '';

    // Previous button
    paginationHtml += `
      <li class="page-item ${currentUserPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0);" onclick="changeUserPage(${currentUserPage - 1})">
          <i class="tf-icon bx bx-chevrons-left"></i>
        </a>
      </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= currentUserPage - 1 && i <= currentUserPage + 1)) {
        paginationHtml += `
          <li class="page-item ${i === currentUserPage ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0);" onclick="changeUserPage(${i})">${i}</a>
          </li>
        `;
      } else if (i === currentUserPage - 2 || i === currentUserPage + 2) {
        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
      }
    }

    // Next button
    paginationHtml += `
      <li class="page-item ${currentUserPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0);" onclick="changeUserPage(${currentUserPage + 1})">
          <i class="tf-icon bx bx-chevrons-right"></i>
        </a>
      </li>
    `;

    // Add page info
    const startItem = (currentUserPage - 1) * USERS_PER_PAGE + 1;
    const endItem = Math.min(currentUserPage * USERS_PER_PAGE, totalItems);
    paginationHtml += `
      <li class="page-item disabled">
        <span class="page-link text-muted small">Showing ${startItem}-${endItem} of ${totalItems}</span>
      </li>
    `;

    $('#usersPagination').html(paginationHtml);
  }

  function changeUserPage(page) {
    if (page >= 1 && page <= Math.ceil(usersData.length / USERS_PER_PAGE)) {
      currentUserPage = page;
      updateUsersTable(usersData);
    }
  }

  // === RESET PASSWORD FUNCTION ===
  function resetPassword(userId, username) {
    Swal.fire({
      title: 'Reset Password',
      html: `
        <div class="text-start">
          <p class="mb-3">Reset password for user: <strong>${username}</strong></p>
          <div class="mb-3">
            <label for="newPassword" class="form-label">New Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" minlength="6">
              <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                <i class="bx bx-hide" id="newPasswordIcon"></i>
              </button>
            </div>
            <div class="form-text">Password must be at least 6 characters long</div>
          </div>
          <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
              <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                <i class="bx bx-hide" id="confirmPasswordIcon"></i>
              </button>
            </div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="generatePassword">
            <label class="form-check-label" for="generatePassword">
              Generate random password
            </label>
          </div>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Reset Password',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#696cff',
      cancelButtonColor: '#8592a3',
      customClass: {
        popup: 'swal2-popup-custom'
      },
      didOpen: () => {
        // Handle generate password checkbox
        const generateCheckbox = document.getElementById('generatePassword');
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const newPasswordIcon = document.getElementById('newPasswordIcon');
        const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');
        
        // Toggle visibility for new password
        toggleNewPassword.addEventListener('click', function() {
          if (newPasswordInput.type === 'password') {
            newPasswordInput.type = 'text';
            newPasswordIcon.className = 'bx bx-show';
          } else {
            newPasswordInput.type = 'password';
            newPasswordIcon.className = 'bx bx-hide';
          }
        });
        
        // Toggle visibility for confirm password
        toggleConfirmPassword.addEventListener('click', function() {
          if (confirmPasswordInput.type === 'password') {
            confirmPasswordInput.type = 'text';
            confirmPasswordIcon.className = 'bx bx-show';
          } else {
            confirmPasswordInput.type = 'password';
            confirmPasswordIcon.className = 'bx bx-hide';
          }
        });
        
        generateCheckbox.addEventListener('change', function() {
          if (this.checked) {
            const randomPassword = generateRandomPassword();
            newPasswordInput.value = randomPassword;
            confirmPasswordInput.value = randomPassword;
            newPasswordInput.disabled = true;
            confirmPasswordInput.disabled = true;
            // Enable eye buttons when password is generated so user can see it
            toggleNewPassword.disabled = false;
            toggleConfirmPassword.disabled = false;
          } else {
            newPasswordInput.value = '';
            confirmPasswordInput.value = '';
            newPasswordInput.disabled = false;
            confirmPasswordInput.disabled = false;
            // Reset password visibility to hidden when unchecked
            newPasswordInput.type = 'password';
            confirmPasswordInput.type = 'password';
            newPasswordIcon.className = 'bx bx-hide';
            confirmPasswordIcon.className = 'bx bx-hide';
          }
        });
      },
      preConfirm: () => {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (!newPassword) {
          Swal.showValidationMessage('Please enter a new password');
          return false;
        }
        
        if (newPassword.length < 6) {
          Swal.showValidationMessage('Password must be at least 6 characters long');
          return false;
        }
        
        if (newPassword !== confirmPassword) {
          Swal.showValidationMessage('Passwords do not match');
          return false;
        }
        
        return { newPassword: newPassword };
      }
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading
        Swal.fire({
          title: 'Resetting Password...',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Send AJAX request to reset password
        $.ajax({
          url: '../ajax/reset_password.php',
          type: 'POST',
          data: {
            user_id: userId,
            new_password: result.value.newPassword
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Show success message with the new password
              Swal.fire({
                title: 'Password Reset Successful!',
                html: `
                  <div class="text-start">
                    <p class="mb-3">Password has been reset successfully for user: <strong>${username}</strong></p>
                    <div class="alert alert-info">
                      <h6 class="alert-heading mb-2"><i class="bx bx-key me-1"></i>New Password:</h6>
                      <div class="input-group">
                        <input type="text" class="form-control" id="newPasswordDisplay" value="${result.value.newPassword}" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copyPasswordBtn" onclick="copyPasswordToClipboard()">
                          <i class="bx bx-copy" id="copyIcon"></i>
                        </button>
                      </div>
                      <small class="text-muted mt-2 d-block">Click the copy button to copy the password to clipboard</small>
                    </div>
                    <div class="alert alert-warning">
                      <small><i class="bx bx-info-circle me-1"></i><strong>Important:</strong> Please share this password securely with the user. For security reasons, this will not be shown again.</small>
                    </div>
                  </div>
                `,
                icon: 'success',
                confirmButtonColor: '#696cff',
                confirmButtonText: 'Got it!',
                width: '32em',
                customClass: {
                  popup: 'swal2-popup-password-display'
                },
                didOpen: () => {
                  // Add copy functionality
                  window.copyPasswordToClipboard = function() {
                    const passwordField = document.getElementById('newPasswordDisplay');
                    const copyBtn = document.getElementById('copyPasswordBtn');
                    const copyIcon = document.getElementById('copyIcon');
                    
                    passwordField.select();
                    passwordField.setSelectionRange(0, 99999); // For mobile devices
                    
                    try {
                      document.execCommand('copy');
                      copyIcon.className = 'bx bx-check';
                      copyBtn.classList.remove('btn-outline-primary');
                      copyBtn.classList.add('btn-success');
                      
                      setTimeout(() => {
                        copyIcon.className = 'bx bx-copy';
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-outline-primary');
                      }, 2000);
                    } catch (err) {
                      console.error('Failed to copy password');
                    }
                  };
                }
              });
            } else {
              Swal.fire({
                title: 'Error!',
                text: response.message || 'Failed to reset password',
                icon: 'error',
                confirmButtonColor: '#696cff'
              });
            }
          },
          error: function() {
            Swal.fire({
              title: 'Error!',
              text: 'Failed to reset password. Please try again.',
              icon: 'error',
              confirmButtonColor: '#696cff'
            });
          }
        });
      }
    });
  }

  // Generate random password function
  function generateRandomPassword() {
    const length = 8;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    for (let i = 0, n = charset.length; i < length; ++i) {
      password += charset.charAt(Math.floor(Math.random() * n));
    }
    return password;
  }
</script>

<style>
.swal2-popup-custom {
  width: 28em !important;
}

.swal2-popup-custom .form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #566a7f;
}

.swal2-popup-custom .form-control {
  border: 1px solid #d9dee3;
  border-radius: 0.375rem;
  padding: 0.4375rem 0.875rem;
  font-size: 0.9375rem;
  margin-bottom: 0.25rem;
}

.swal2-popup-custom .form-control:focus {
  border-color: #696cff;
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.swal2-popup-custom .form-text {
  font-size: 0.75rem;
  color: #a1acb8;
}

.swal2-popup-custom .form-check {
  margin-top: 1rem;
}

.swal2-popup-custom .form-check-input:checked {
  background-color: #696cff;
  border-color: #696cff;
}

.swal2-popup-custom .form-check-label {
  font-size: 0.875rem;
  color: #566a7f;
}

.swal2-popup-custom .input-group {
  display: flex;
}

.swal2-popup-custom .input-group .form-control {
  margin-bottom: 0;
}

.swal2-popup-custom .input-group .btn {
  border: 1px solid #d9dee3;
  padding: 0.4375rem 0.875rem;
  font-size: 0.9375rem;
  border-left: none;
}

.swal2-popup-custom .input-group .btn:hover {
  background-color: #f5f5f9;
  border-color: #d9dee3;
}

.swal2-popup-custom .input-group .btn:focus {
  box-shadow: none;
  border-color: #696cff;
}

.swal2-popup-custom .input-group .btn i {
  font-size: 1rem;
  color: #8592a3;
}

/* Password Display Modal Styles */
.swal2-popup-password-display {
  width: 32em !important;
}

.swal2-popup-password-display .alert {
  border-radius: 0.375rem;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
}

.swal2-popup-password-display .alert-info {
  color: #0c63e4;
  background-color: #e7f1ff;
  border-color: #b6d7ff;
}

.swal2-popup-password-display .alert-warning {
  color: #8a6914;
  background-color: #fff3cd;
  border-color: #ffecb5;
}

.swal2-popup-password-display .alert-heading {
  color: inherit;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.swal2-popup-password-display .input-group {
  display: flex;
}

.swal2-popup-password-display .input-group .form-control {
  font-family: 'Courier New', Courier, monospace;
  font-weight: 600;
  letter-spacing: 1px;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
}

.swal2-popup-password-display .input-group .btn {
  border: 1px solid #dee2e6;
  border-left: none;
  padding: 0.375rem 0.75rem;
}

.swal2-popup-password-display .input-group .btn:hover {
  background-color: #e9ecef;
}

.swal2-popup-password-display .input-group .btn.btn-success {
  background-color: #198754;
  border-color: #198754;
  color: white;
}

.swal2-popup-password-display .input-group .btn.btn-success:hover {
  background-color: #157347;
  border-color: #146c43;
}
</style>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
