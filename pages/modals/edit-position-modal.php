<!-- Edit Position Modal -->
<div class="modal fade" id="editPositionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPositionForm">
                <div class="modal-body">
                    <input type="hidden" name="position_id" id="edit-pos-id">
                    <div class="mb-3">
                        <label for="edit-pos-name" class="form-label">Position Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-pos-name" name="position_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-pos-dept" class="form-label">Department</label>
                        <select class="form-select" id="edit-pos-dept" name="department_id">
                            <option value="">Select Department</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-pos-status" class="form-label">Status</label>
                        <select class="form-select" id="edit-pos-status" name="active_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Update Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
