<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-edit-alt me-2"></i>Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editUserForm">
        <div class="modal-body">
          <input type="hidden" name="user_id" id="edit_user_id">
          
          <div class="row">
            <!-- Account Information -->
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bx bx-user me-1"></i>Username <span class="text-danger">*</span></label>
              <input type="text" name="username" id="edit_username" class="form-control" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bx bx-shield me-1"></i>User Type <span class="text-danger">*</span></label>
              <select name="user_type_id" id="edit_user_type" class="form-select" required>
                <option value="">Select User Type</option>
                <!-- Will be populated via AJAX -->
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bx bx-check-circle me-1"></i>Status</label>
              <select name="active_status" id="edit_status" class="form-select">
                <option value="active">Active</option>
                <option value="locked">Locked</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Personal Information (Optional) -->
            <div class="col-12">
              <hr class="my-3">
              <h6 class="text-muted mb-3"><i class="bx bx-id-card me-1"></i>Personal Information (Optional)</h6>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" id="edit_first_name" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" id="edit_last_name" class="form-control">
            </div>

            <div class="col-12 mb-3">
              <label class="form-label"><i class="bx bx-envelope me-1"></i>Email</label>
              <input type="email" name="email" id="edit_email" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
            <i class="bx bx-save me-1"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Edit User Form Handler
$('#editUserForm').on('submit', function(e) {
    e.preventDefault();
    
    const $btn = $(this).find('button[type="submit"]');
    const $spinner = $btn.find('.spinner-border');
    
    // Show loading state
    $btn.prop('disabled', true);
    $spinner.removeClass('d-none');
    
    $.ajax({
        url: '../ajax/update_user.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#editUserModal').modal('hide');
                refreshUsers();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error!', 'Failed to update user', 'error');
        },
        complete: function () {
            $btn.prop('disabled', false);
            $spinner.addClass('d-none');
        }
    });
});

function loadUserTypesForEdit() {
    $.ajax({
        url: '../ajax/get_user_types.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                const $select = $('#edit_user_type');
                $select.find('option:not(:first)').remove();
                
                if (response.userTypes && response.userTypes.length > 0) {
                    response.userTypes.forEach(type => {
                        $select.append(`<option value="${type.id}">${type.name}</option>`);
                    });
                }
            }
        },
        error: function () {
            console.error('Failed to load user types');
        }
    });
}

// Load user types when modal is shown
$('#editUserModal').on('show.bs.modal', function () {
    loadUserTypesForEdit();
});
</script>
