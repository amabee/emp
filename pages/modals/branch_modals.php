<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-store me-2"></i>Add New Branch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addBranchForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="add_branch_name" class="form-label">Branch Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="add_branch_name" name="branch_name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="add_branch_code" class="form-label">Branch Code</label>
              <input type="text" class="form-control" id="add_branch_code" name="branch_code" placeholder="e.g., CDO-01">
              <small class="text-muted">Optional unique identifier</small>
            </div>
          </div>
          <div class="row">
            <div class="col-12 mb-3">
              <label for="add_branch_address" class="form-label">Address</label>
              <textarea class="form-control" id="add_branch_address" name="address" rows="2"></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="add_branch_contact" class="form-label">Contact Number</label>
              <input type="text" class="form-control" id="add_branch_contact" name="contact_number" placeholder="09123456789">
            </div>
            <div class="col-md-6 mb-3">
              <label for="add_branch_email" class="form-label">Email</label>
              <input type="email" class="form-control" id="add_branch_email" name="email" placeholder="branch@company.com">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="add_branch_manager" class="form-label">Branch Manager</label>
              <select class="form-select" id="add_branch_manager" name="manager_id">
                <option value="">Select Manager</option>
                <!-- Will be populated via AJAX -->
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="add_branch_status" class="form-label">Status</label>
              <select class="form-select" id="add_branch_status" name="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Add Branch
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Branch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editBranchForm">
        <input type="hidden" id="edit_branch_id" name="branch_id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit_branch_name" class="form-label">Branch Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_branch_name" name="branch_name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit_branch_code" class="form-label">Branch Code</label>
              <input type="text" class="form-control" id="edit_branch_code" name="branch_code" placeholder="e.g., CDO-01">
              <small class="text-muted">Optional unique identifier</small>
            </div>
          </div>
          <div class="row">
            <div class="col-12 mb-3">
              <label for="edit_branch_address" class="form-label">Address</label>
              <textarea class="form-control" id="edit_branch_address" name="address" rows="2"></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit_branch_contact" class="form-label">Contact Number</label>
              <input type="text" class="form-control" id="edit_branch_contact" name="contact_number" placeholder="09123456789">
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit_branch_email" class="form-label">Email</label>
              <input type="email" class="form-control" id="edit_branch_email" name="email" placeholder="branch@company.com">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit_branch_manager" class="form-label">Branch Manager</label>
              <select class="form-select" id="edit_branch_manager" name="manager_id">
                <option value="">Select Manager</option>
                <!-- Will be populated via AJAX -->
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit_branch_status" class="form-label">Status</label>
              <select class="form-select" id="edit_branch_status" name="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Update Branch
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
