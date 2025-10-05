<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-user-plus me-2"></i>Create New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="action" value="add_user">
          <div class="row">
            <div class="col-12 mb-3">
              <label class="form-label"><i class="bx bx-user me-1"></i>Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-12 mb-3">
              <label class="form-label"><i class="bx bx-lock-alt me-1"></i>Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-12 mb-3">
              <label class="form-label"><i class="bx bx-shield me-1"></i>Role</label>
              <select name="role" class="form-select" required>
                <option value="user">Regular User</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Save User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
