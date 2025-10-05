<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addDepartmentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-dept-name" class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-dept-name" name="department_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-dept-head" class="form-label">Department Head</label>
                        <select class="form-select" id="add-dept-head" name="department_head_id">
                            <option value="">Select Department Head</option>
                        </select>
                        <div class="form-text">Optional - Leave blank if no head assigned yet</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Add Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
