<div class="modal fade" id="viewEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div style="width:140px;height:140px;margin:0 auto;border-radius:50%;overflow:hidden;border:1px solid #e9ecef;">
                            <img src="" id="view_employee_photo" style="width:100%;height:100%;object-fit:cover;" alt="Employee Photo">
                        </div>
                        <h4 class="mt-3 mb-0" id="view_employee_name"></h4>
                        <div class="text-muted" id="view_employee_position"></div>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Email</label>
                                <div id="view_email" class="fw-semibold"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Contact Number</label>
                                <div id="view_contact_number" class="fw-semibold"></div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Department</label>
                                <div id="view_department" class="fw-semibold"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Status</label>
                                <div id="view_status" class="fw-semibold"></div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Gender</label>
                                <div id="view_gender" class="fw-semibold"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Birthdate</label>
                                <div id="view_birthdate" class="fw-semibold"></div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Basic Salary</label>
                                <div id="view_basic_salary" class="fw-semibold"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
