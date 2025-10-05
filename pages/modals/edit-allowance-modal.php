<div class="modal fade" id="editAllowanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Allowance Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editAllowanceForm">
        <input type="hidden" name="allowance_type_id" value="">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Amount</label>
              <input type="number" step="0.01" class="form-control" name="amount">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Apply To (optional - reassign)</label>
            <select name="apply_to" id="editAllowance_applyToSelect" class="form-select">
              <option value="">-- No reassignment --</option>
              <option value="all">All Employees</option>
              <option value="department">Specific Department</option>
              <option value="position">Specific Position</option>
              <option value="employee">Specific Employee</option>
            </select>
          </div>

          <div id="editAllowance_applyToExtras">
            <div class="mb-3 d-none" id="editAllowance_applyDepartment">
              <label class="form-label">Select Department</label>
              <select name="department_id" class="form-select" id="editAllowance_departmentSelect">
                <option value="">-- Select Department --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="editAllowance_applyPosition">
              <label class="form-label">Select Position</label>
              <select name="position_id" class="form-select" id="editAllowance_positionSelect">
                <option value="">-- Select Position --</option>
              </select>
            </div>

            <div class="mb-3 d-none" id="editAllowance_applyEmployee">
              <label class="form-label">Select Employee(s)</label>
              <div class="d-flex mb-2">
                <select id="editAllowance_employeeSelect" class="form-select me-2" aria-label="Select employee to add">
                  <option value="">-- Select Employee --</option>
                </select>
                <button type="button" class="btn btn-outline-primary" id="editAllowance_addEmployeeBtn">Add</button>
              </div>
              <div id="editAllowance_selectedEmployees" class="d-flex flex-wrap gap-2">
                <!-- badges inserted here -->
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
