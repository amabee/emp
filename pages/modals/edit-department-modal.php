<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDepartmentForm">
                <div class="modal-body">
                    <input type="hidden" name="department_id" id="edit-dept-id">
                    <div class="mb-3">
                        <label for="edit-dept-name" class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-dept-name" name="department_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-dept-head" class="form-label">Department Head</label>
                        <select class="form-select" id="edit-dept-head" name="department_head_id">
                            <option value="">Select Department Head</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-dept-status" class="form-label">Status</label>
                        <select class="form-select" id="edit-dept-status" name="active_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Update Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
