<div class="modal fade" id="exportLogsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Export System Logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="exportLogsForm">
          <div class="mb-3">
            <label class="form-label">Date Range</label>
            <div class="d-flex gap-2 align-items-center">
              <div class="flex-grow-1">
                <label class="form-label small">From</label>
                <input type="date" class="form-control" id="exportDateFrom" required>
              </div>
              <div class="flex-grow-1">
                <label class="form-label small">To</label>
                <input type="date" class="form-control" id="exportDateTo" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Activity Type</label>
            <select class="form-select" id="exportType">
              <option value="">All Activities</option>
              <option value="login">Login Activity</option>
              <option value="user">User Management</option>
              <option value="settings">System Settings</option>
              <option value="database">Database Operations</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="confirmExport()">
          <i class="bx bx-export me-1"></i>Export
        </button>
      </div>
    </div>
  </div>
</div>
