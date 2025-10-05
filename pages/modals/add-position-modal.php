<!-- Add Position Modal -->
<div class="modal fade" id="addPositionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPositionForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-pos-name" class="form-label">Position Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-pos-name" name="position_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-pos-dept" class="form-label">Department</label>
                        <select class="form-select" id="add-pos-dept" name="department_id">
                            <option value="">Select Department</option>
                        </select>
                        <div class="form-text">Optional - Leave blank if not assigned to a specific department</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Add Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
