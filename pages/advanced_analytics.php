<?php
$page_title = 'Advanced Analytics';

$additional_css = [
    '../assets/vendor/libs/apex-charts/apex-charts.css'
];
$additional_js = [
    '../assets/vendor/libs/apex-charts/apexcharts.js'
];

include './shared/session_handler.php';

if (!isset($user_id)) {
    header('Location: ./login.php');
    exit();
}

// Check user permissions - only allow Admin, HR, and Supervisor to view analytics
if (!in_array($user_type, ['admin', 'hr', 'supervisor'])) {
    header('Location: ./dashboard.php');
    exit();
}

ob_start();
?>

<style>
    .view-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    .view-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .view-type-1 { border-left-color: #28a745; }
    .view-type-2 { border-left-color: #ffc107; }
    .view-type-3 { border-left-color: #dc3545; }
    
    .insight-badge {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }
    
    .performance-badge {
        font-size: 0.8em;
        padding: 0.3em 0.6em;
    }
    
    .rank-badge {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.85em;
    }
    
    .loading-spinner {
        display: none;
    }
    
    .filters-panel {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
</style>

<div class="row">
    <!-- Page Header -->
    <div class="col-12 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-2">Advanced Analytics Dashboard</h4>
                        <p class="card-text text-muted">Comprehensive database views for employee management insights</p>
                    </div>
                    <div class="text-end">
                        <i class="bx bx-trending-up text-primary" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
        <!-- View Statistics Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Database Views Overview</h5>
                        <div id="viewStats" class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="loading-spinner spinner-border spinner-border-sm text-success me-2" role="status"></div>
                                    <span class="text-muted">Loading statistics...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW TYPE 1: Summary View -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card view-card view-type-1">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title">
                                <i class="bi bi-bar-chart-line me-2"></i>
                                Department Summary Analytics
                                <span class="badge bg-success">Summary View</span>
                            </h5>
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#summaryFilters">
                                <i class="bi bi-funnel"></i> Filters
                            </button>
                        </div>
                        
                        <!-- Filters Panel -->
                        <div class="collapse filters-panel" id="summaryFilters">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Min Employees</label>
                                    <input type="number" class="form-control" id="minEmployees" min="1" placeholder="e.g., 5">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Min Performance Rating</label>
                                    <input type="number" class="form-control" id="minPerformance" min="1" max="5" step="0.1" placeholder="e.g., 3.5">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Department Name</label>
                                    <input type="text" class="form-control" id="deptName" placeholder="e.g., IT">
                                </div>
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-success btn-sm" onclick="loadDepartmentSummary()">
                                    <i class="bi bi-search"></i> Apply Filters
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="clearSummaryFilters()">
                                    <i class="bi bi-arrow-clockwise"></i> Clear
                                </button>
                            </div>
                        </div>

                        <div id="departmentSummary">
                            <div class="text-center p-4">
                                <div class="loading-spinner spinner-border text-success" role="status"></div>
                                <p class="mt-2 text-muted">Loading department summary...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW TYPE 2: Filtered View -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card view-card view-type-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title">
                                <i class="bi bi-people-fill me-2"></i>
                                Active Employees (Pre-filtered)
                                <span class="badge bg-warning">Filtered View</span>
                            </h5>
                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#filteredFilters">
                                <i class="bi bi-funnel"></i> Additional Filters
                            </button>
                        </div>

                        <!-- Additional Filters Panel -->
                        <div class="collapse filters-panel" id="filteredFilters">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Tenure Category</label>
                                    <select class="form-select" id="tenureCategory">
                                        <option value="">All</option>
                                        <option value="New">New (0-1 year)</option>
                                        <option value="Experienced">Experienced (1-5 years)</option>
                                        <option value="Senior">Senior (5+ years)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Salary Category</label>
                                    <select class="form-select" id="salaryCategory">
                                        <option value="">All</option>
                                        <option value="Entry Level">Entry Level</option>
                                        <option value="Mid Level">Mid Level</option>
                                        <option value="Senior Level">Senior Level</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Recent Evaluation</label>
                                    <select class="form-select" id="hasRecentEval">
                                        <option value="">All</option>
                                        <option value="true">Has Recent</option>
                                        <option value="false">No Recent</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Min Performance</label>
                                    <input type="number" class="form-control" id="filteredMinPerf" min="1" max="5" step="0.1">
                                </div>
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-warning btn-sm" onclick="loadFilteredEmployees()">
                                    <i class="bi bi-search"></i> Apply Filters
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="clearFilteredFilters()">
                                    <i class="bi bi-arrow-clockwise"></i> Clear
                                </button>
                            </div>
                        </div>

                        <div id="filteredEmployees">
                            <div class="text-center p-4">
                                <div class="loading-spinner spinner-border text-warning" role="status"></div>
                                <p class="mt-2 text-muted">Loading filtered employees...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW TYPE 3: Multi-table View -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card view-card view-type-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title">
                                <i class="bi bi-graph-up me-2"></i>
                                Comprehensive Employee Analytics
                                <span class="badge bg-danger">Multi-table View</span>
                            </h5>
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#comprehensiveFilters">
                                <i class="bi bi-sliders"></i> Advanced Filters
                            </button>
                        </div>

                        <!-- Advanced Filters Panel -->
                        <div class="collapse filters-panel" id="comprehensiveFilters">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Performance Category</label>
                                    <select class="form-select" id="performanceCategory">
                                        <option value="">All</option>
                                        <option value="Outstanding">Outstanding</option>
                                        <option value="Excellent">Excellent</option>
                                        <option value="Good">Good</option>
                                        <option value="Satisfactory">Satisfactory</option>
                                        <option value="Needs Improvement">Needs Improvement</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Evaluation Recency</label>
                                    <select class="form-select" id="evaluationRecency">
                                        <option value="">All</option>
                                        <option value="Recent">Recent (within 6 months)</option>
                                        <option value="Moderate">Moderate (6-12 months)</option>
                                        <option value="Old">Old (over 12 months)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Max Dept Rank</label>
                                    <input type="number" class="form-control" id="maxDeptRank" min="1" placeholder="e.g., 5">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label class="form-label">Order By</label>
                                    <select class="form-select" id="orderBy">
                                        <option value="company_performance_rank">Company Rank</option>
                                        <option value="dept_performance_rank">Department Rank</option>
                                        <option value="avg_performance_rating">Performance Rating</option>
                                        <option value="basic_salary">Salary</option>
                                        <option value="employee_name">Name</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Order Direction</label>
                                    <select class="form-select" id="orderDirection">
                                        <option value="ASC">Ascending</option>
                                        <option value="DESC">Descending</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Min Salary</label>
                                    <input type="number" class="form-control" id="minSalary" step="1000" placeholder="e.g., 50000">
                                </div>
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-danger btn-sm" onclick="loadComprehensiveAnalytics()">
                                    <i class="bi bi-search"></i> Apply Filters
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="clearComprehensiveFilters()">
                                    <i class="bi bi-arrow-clockwise"></i> Clear
                                </button>
                            </div>
                        </div>

                        <div id="comprehensiveAnalytics">
                            <div class="text-center p-4">
                                <div class="loading-spinner spinner-border text-danger" role="status"></div>
                                <p class="mt-2 text-muted">Loading comprehensive analytics...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadViewStatistics();
    loadDepartmentSummary();
    loadFilteredEmployees();
    loadComprehensiveAnalytics();
});

// Load view statistics
function loadViewStatistics() {
    $.ajax({
        url: '../ajax/get_view_statistics.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const stats = response.statistics;
                let html = '';
                
                if (stats.summary_view) {
                    html += `
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>${stats.summary_view.dept_count}</h4>
                                    <p class="mb-0">Departments</p>
                                    <small>Avg: ${parseFloat(stats.summary_view.avg_employees_per_dept).toFixed(1)} employees/dept</small>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                if (stats.filtered_view) {
                    html += `
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>${stats.filtered_view.active_employees}</h4>
                                    <p class="mb-0">Active Employees</p>
                                    <small>Across ${stats.filtered_view.active_departments} departments</small>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                if (stats.multitable_view) {
                    html += `
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4>${stats.multitable_view.total_records}</h4>
                                    <p class="mb-0">Analytics Records</p>
                                    <small>Avg Performance: ${parseFloat(stats.multitable_view.overall_avg_performance).toFixed(2)}</small>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                $('#viewStats').html(html);
            }
        },
        error: function() {
            $('#viewStats').html('<div class="col-12"><div class="alert alert-warning">Failed to load view statistics</div></div>');
        }
    });
}

// Department Summary Functions
function loadDepartmentSummary() {
    showLoading('#departmentSummary');
    
    const params = new URLSearchParams();
    const minEmployees = $('#minEmployees').val();
    const minPerformance = $('#minPerformance').val();
    const deptName = $('#deptName').val();
    
    if (minEmployees) params.append('min_employees', minEmployees);
    if (minPerformance) params.append('min_performance', minPerformance);
    if (deptName) params.append('department_name', deptName);
    
    $.ajax({
        url: '../ajax/get_department_summary.php?' + params.toString(),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderDepartmentSummary(response);
            } else {
                $('#departmentSummary').html(`<div class="alert alert-danger">${response.message}</div>`);
            }
        },
        error: function() {
            $('#departmentSummary').html('<div class="alert alert-danger">Failed to load department summary</div>');
        }
    });
}

function renderDepartmentSummary(response) {
    const data = response.data;
    const summary = response.summary;
    
    let html = '';
    
    // Summary cards
    if (summary) {
        html += `
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>${summary.total_departments}</h5>
                            <small>Total Departments</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card ">
                        <div class="card-body text-center">
                            <h5>${summary.total_employees_across_all}</h5>
                            <small>Total Employees</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card ">
                        <div class="card-body text-center">
                            <h5>₱${summary.avg_salary_across_all}</h5>
                            <small>Avg Salary</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>${summary.best_performing_dept}</h5>
                            <small>Best Department</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Department details
    if (data.length > 0) {
        html += '<div class="table-responsive"><table class="table table-striped">';
        html += `
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Employees</th>
                    <th>Avg Salary</th>
                    <th>Avg Performance</th>
                    <th>Evaluations</th>
                    <th>Leave Days</th>
                </tr>
            </thead>
            <tbody>
        `;
        
        data.forEach(dept => {
            const perfRating = parseFloat(dept.avg_performance_rating);
            const perfBadge = getPerformanceBadge(perfRating);
            
            html += `
                <tr>
                    <td><strong>${dept.department_name}</strong></td>
                    <td><span class="badge bg-primary">${dept.total_employees}</span></td>
                    <td>₱${(() => { const s = parseFloat(dept.avg_salary); return Number.isFinite(s) ? s.toLocaleString() : '0'; })()}</td>
                    <td>${perfBadge}</td>
                    <td>${dept.total_evaluations}</td>
                    <td>${dept.total_leave_days}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
    } else {
        html += '<div class="alert alert-info">No departments found matching the criteria.</div>';
    }
    
    $('#departmentSummary').html(html);
}

function clearSummaryFilters() {
    $('#minEmployees, #minPerformance, #deptName').val('');
    loadDepartmentSummary();
}

// Filtered Employees Functions
function loadFilteredEmployees() {
    showLoading('#filteredEmployees');
    
    const params = new URLSearchParams();
    const tenure = $('#tenureCategory').val();
    const salary = $('#salaryCategory').val();
    const hasEval = $('#hasRecentEval').val();
    const minPerf = $('#filteredMinPerf').val();
    
    if (tenure) params.append('tenure_category', tenure);
    if (salary) params.append('salary_category', salary);
    if (hasEval) params.append('has_recent_evaluation', hasEval);
    if (minPerf) params.append('min_performance', minPerf);
    
    params.append('limit', '10'); // Show first 10 for demo
    
    $.ajax({
        url: '../ajax/get_filtered_employees.php?' + params.toString(),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderFilteredEmployees(response);
            } else {
                $('#filteredEmployees').html(`<div class="alert alert-danger">${response.message}</div>`);
            }
        },
        error: function() {
            $('#filteredEmployees').html('<div class="alert alert-danger">Failed to load filtered employees</div>');
        }
    });
}

function renderFilteredEmployees(response) {
    const data = response.data;
    const pagination = response.pagination;
    
    let html = '';
    
    if (pagination) {
        html += `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted">Showing ${data.length} of ${pagination.total_records} active employees</span>
                </div>
                <div>
                    <small class="text-muted">Page ${pagination.current_page} of ${pagination.total_pages}</small>
                </div>
            </div>
        `;
    }
    
    if (data.length > 0) {
        data.forEach(emp => {
            const perfBadge = emp.latest_performance_rating ? getPerformanceBadge(parseFloat(emp.latest_performance_rating)) : '<span class="badge bg-secondary">No Rating</span>';
            const evalBadge = emp.has_recent_evaluation == 1 ? '<span class="badge bg-success">Recent</span>' : '<span class="badge bg-warning">Outdated</span>';
            
            html += `
                <div class="card mb-2">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>${emp.employee_name}</strong><br>
                                <small class="text-muted">${emp.department_name}</small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-info">${emp.employee_tenure_category}</span>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-secondary">${emp.salary_category}</span>
                            </div>
                            <div class="col-md-2">
                                ${perfBadge}
                            </div>
                            <div class="col-md-2">
                                ${evalBadge}
                            </div>
                            <div class="col-md-1 text-end">
                                <small>₱${parseFloat(emp.basic_salary).toLocaleString()}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    } else {
        html += '<div class="alert alert-info">No active employees found matching the criteria.</div>';
    }
    
    $('#filteredEmployees').html(html);
}

function clearFilteredFilters() {
    $('#tenureCategory, #salaryCategory, #hasRecentEval, #filteredMinPerf').val('');
    loadFilteredEmployees();
}

// Comprehensive Analytics Functions
function loadComprehensiveAnalytics() {
    showLoading('#comprehensiveAnalytics');
    
    const params = new URLSearchParams();
    const perfCategory = $('#performanceCategory').val();
    const evalRecency = $('#evaluationRecency').val();
    const maxRank = $('#maxDeptRank').val();
    const orderBy = $('#orderBy').val();
    const orderDir = $('#orderDirection').val();
    const minSalary = $('#minSalary').val();
    
    if (perfCategory) params.append('performance_category', perfCategory);
    if (evalRecency) params.append('evaluation_recency', evalRecency);
    if (maxRank) params.append('max_dept_rank', maxRank);
    if (orderBy) params.append('order_by', orderBy);
    if (orderDir) params.append('order_direction', orderDir);
    if (minSalary) params.append('min_salary', minSalary);
    
    params.append('limit', '15'); // Show first 15 for demo
    
    $.ajax({
        url: '../ajax/get_comprehensive_analytics.php?' + params.toString(),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderComprehensiveAnalytics(response);
            } else {
                $('#comprehensiveAnalytics').html(`<div class="alert alert-danger">${response.message}</div>`);
            }
        },
        error: function() {
            $('#comprehensiveAnalytics').html('<div class="alert alert-danger">Failed to load comprehensive analytics</div>');
        }
    });
}

function renderComprehensiveAnalytics(response) {
    const data = response.data;
    const insights = response.insights;
    
    let html = '';
    
    // Insights section
    if (insights && Object.keys(insights).length > 0) {
        html += '<div class="row mb-4">';
        
        // Performance distribution
        if (insights.performance_distribution) {
            html += '<div class="col-md-4"><div class="card "><div class="card-body">';
            html += '<h6>Performance Distribution</h6>';
            Object.entries(insights.performance_distribution).forEach(([category, count]) => {
                html += `<div class="d-flex justify-content-between mt-2"><span>${category}:</span><span class="badge bg-primary">${count}</span></div>`;
            });
            html += '</div></div></div>';
        }
        
        // Top performers
        if (insights.top_performers && insights.top_performers.length > 0) {
            html += '<div class="col-md-4"><div class="card "><div class="card-body">';
            html += '<h6>Top Performers</h6>';
            insights.top_performers.slice(0, 5).forEach(name => {
                html += `<div><i class="bi bi-star-fill text-warning"></i> ${name}</div>`;
            });
            html += '</div></div></div>';
        }
        
        // Salary insights
        if (insights.salary_performance_correlation) {
            const salaryInsight = insights.salary_performance_correlation;
            html += '<div class="col-md-4"><div class="card "><div class="card-body">';
            html += '<h6>Salary vs Performance</h6>';
            html += `<div>High Performers: ₱${salaryInsight.high_performers_avg_salary.toLocaleString()}</div>`;
            html += `<div>Others: ₱${salaryInsight.others_avg_salary.toLocaleString()}</div>`;
            html += `<div class="text-success">Premium: ₱${salaryInsight.salary_premium_for_performance.toLocaleString()}</div>`;
            html += '</div></div></div>';
        }
        
        html += '</div>';
    }
    
    // Employee details
    if (data.length > 0) {
        html += '<div class="table-responsive"><table class="table table-sm">';
        html += `
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Performance</th>
                    <th>Salary</th>
                    <th>Leave Days</th>
                    <th>Evaluation</th>
                </tr>
            </thead>
            <tbody>
        `;
        
        data.forEach(emp => {
            const perfBadge = emp.performance_category ? `<span class="performance-badge badge bg-${getPerformanceColor(emp.performance_category)}">${emp.performance_category}</span>` : '<span class="badge bg-secondary">N/A</span>';
            const evalBadge = `<span class="performance-badge badge bg-${getEvaluationColor(emp.evaluation_recency)}">${emp.evaluation_recency}</span>`;
            
            html += `
                <tr>
                    <td>
                        <span class="rank-badge">${emp.company_performance_rank}</span>
                        <small class="d-block text-muted">Dept: ${emp.dept_performance_rank}</small>
                    </td>
                    <td>
                        <strong>${emp.employee_name}</strong><br>
                        <small class="text-muted">${emp.position_name}</small>
                    </td>
                    <td>${emp.department_name}</td>
                    <td>
                        ${perfBadge}<br>
                        <small>${emp.avg_performance_rating ? parseFloat(emp.avg_performance_rating).toFixed(2) : 'N/A'}</small>
                    </td>
                    <td>₱${parseFloat(emp.basic_salary).toLocaleString()}</td>
                    <td>${emp.total_leave_days_taken}</td>
                    <td>${evalBadge}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
    } else {
        html += '<div class="alert alert-info">No employees found matching the comprehensive criteria.</div>';
    }
    
    $('#comprehensiveAnalytics').html(html);
}

function clearComprehensiveFilters() {
    $('#performanceCategory, #evaluationRecency, #maxDeptRank, #minSalary').val('');
    $('#orderBy').val('company_performance_rank');
    $('#orderDirection').val('ASC');
    loadComprehensiveAnalytics();
}

// Utility functions
function showLoading(selector) {
    $(selector).html(`
        <div class="text-center p-4">
            <div class="spinner-border" role="status"></div>
            <p class="mt-2 text-muted">Loading...</p>
        </div>
    `);
}

function getPerformanceBadge(rating) {
    if (rating >= 4.5) return '<span class="performance-badge badge bg-success">Outstanding</span>';
    if (rating >= 4.0) return '<span class="performance-badge badge bg-primary">Excellent</span>';
    if (rating >= 3.5) return '<span class="performance-badge badge bg-info">Good</span>';
    if (rating >= 3.0) return '<span class="performance-badge badge bg-warning">Satisfactory</span>';
    return '<span class="performance-badge badge bg-danger">Needs Improvement</span>';
}

function getPerformanceColor(category) {
    const colors = {
        'Outstanding': 'success',
        'Excellent': 'primary',
        'Good': 'info',
        'Satisfactory': 'warning',
        'Needs Improvement': 'danger'
    };
    return colors[category] || 'secondary';
}

function getEvaluationColor(recency) {
    const colors = {
        'Recent': 'success',
        'Moderate': 'warning',
        'Old': 'danger'
    };
    return colors[recency] || 'secondary';
}
</script>

<?php
$content = ob_get_clean();
include './shared/layout.php';
?>
