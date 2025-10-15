/**
 * Branch Management JavaScript
 * Handles CRUD operations for branches
 */

// Global variables
let branches = [];
let currentBranchPage = 1;
const branchesPerPage = 10;

// Load branches on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    
    // Set up form handlers
    setupBranchForms();
    
    // Load available managers when add modal is opened
    const addModal = document.getElementById('addBranchModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function() {
            loadEmployeesForBranchManager('add_branch_manager');
        });
    }
});

// Load all branches
function loadBranches() {
    fetch('../ajax/get_branches.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                branches = data.data;
                displayBranches(currentBranchPage);
                setupBranchPagination();
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading branches:', error);
            showToast('Error', 'Failed to load branches', 'error');
        });
}

// Display branches in table
function displayBranches(page = 1) {
    const tableBody = document.getElementById('branchesTable');
    if (!tableBody) return;

    const start = (page - 1) * branchesPerPage;
    const end = start + branchesPerPage;
    const paginatedBranches = branches.slice(start, end);

    if (paginatedBranches.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bx bx-store bx-lg mb-2"></i>
                    <p>No branches found</p>
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = paginatedBranches.map(branch => `
        <tr>
            <td>
                <strong>${escapeHtml(branch.name)}</strong>
                ${branch.code ? `<br><small class="text-muted">${escapeHtml(branch.code)}</small>` : ''}
            </td>
            <td>
                ${branch.manager_name && branch.manager_name.trim() ? escapeHtml(branch.manager_name) : '<span class="text-muted">Not assigned</span>'}
            </td>
            <td>
                <span class="badge bg-label-info">${branch.employee_count || 0}</span>
            </td>
            <td>
                ${branch.address || '<span class="text-muted">N/A</span>'}
            </td>
            <td>
                <span class="badge ${branch.is_active == 1 ? 'bg-label-success' : 'bg-label-secondary'}">
                    ${branch.is_active == 1 ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-icon btn-outline-primary" onclick="editBranch(${branch.id})" title="Edit">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-outline-danger" onclick="deleteBranch(${branch.id}, '${escapeHtml(branch.name)}')" title="Delete">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Setup pagination
function setupBranchPagination() {
    const totalPages = Math.ceil(branches.length / branchesPerPage);
    const paginationContainer = document.getElementById('branchesPagination');
    const paginationNav = document.getElementById('branchesPaginationNav');
    
    if (!paginationContainer || totalPages <= 1) {
        if (paginationNav) paginationNav.classList.add('d-none');
        return;
    }
    
    paginationNav.classList.remove('d-none');
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentBranchPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeBranchPage(${currentBranchPage - 1}); return false;">
                <i class="tf-icon bx bx-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        paginationHTML += `
            <li class="page-item ${i === currentBranchPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changeBranchPage(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentBranchPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changeBranchPage(${currentBranchPage + 1}); return false;">
                <i class="tf-icon bx bx-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
}

// Change page
function changeBranchPage(page) {
    const totalPages = Math.ceil(branches.length / branchesPerPage);
    if (page < 1 || page > totalPages) return;
    
    currentBranchPage = page;
    displayBranches(currentBranchPage);
    setupBranchPagination();
}

// Load employees for branch manager dropdown
function loadEmployeesForBranchManager(selectElementId = 'add_branch_manager', excludeBranchId = null) {
    const url = excludeBranchId 
        ? `../ajax/get_available_branch_managers.php?exclude_branch_id=${excludeBranchId}`
        : '../ajax/get_available_branch_managers.php';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById(selectElementId);
                if (!select) return;
                
                const options = data.data.map(emp => {
                    const dept = emp.department_name ? ` - ${emp.department_name}` : '';
                    const position = emp.position_name ? ` (${emp.position_name})` : '';
                    return `<option value="${emp.id}">${escapeHtml(emp.name)}${dept}${position}</option>`;
                }).join('');
                
                select.innerHTML = '<option value="">Select Manager</option>' + options;
            }
        })
        .catch(error => console.error('Error loading employees:', error));
}

// Setup form handlers
function setupBranchForms() {
    // Add branch form
    const addForm = document.getElementById('addBranchForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../ajax/add_branch.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    const modalElement = document.getElementById('addBranchModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    addForm.reset();
                    loadBranches();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to add branch', 'error');
            });
        });
    }
    
    // Edit branch form
    const editForm = document.getElementById('editBranchForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../ajax/update_branch.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    const modalElement = document.getElementById('editBranchModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    loadBranches();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to update branch', 'error');
            });
        });
    }
}

// Edit branch
function editBranch(id) {
    const branch = branches.find(b => b.id === id);
    if (!branch) {
        showToast('Error', 'Branch not found', 'error');
        return;
    }
    
    // Populate form
    document.getElementById('edit_branch_id').value = branch.id;
    document.getElementById('edit_branch_name').value = branch.name || '';
    document.getElementById('edit_branch_code').value = branch.code || '';
    document.getElementById('edit_branch_address').value = branch.address || '';
    document.getElementById('edit_branch_contact').value = branch.contact_number || '';
    document.getElementById('edit_branch_email').value = branch.email || '';
    document.getElementById('edit_branch_status').value = branch.is_active || 1;
    
    // Load available managers (excluding current branch so current manager stays available)
    loadEmployeesForBranchManager('edit_branch_manager', branch.id);
    
    // Set current manager after a brief delay to ensure options are loaded
    setTimeout(() => {
        document.getElementById('edit_branch_manager').value = branch.manager_id || '';
    }, 100);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editBranchModal'));
    modal.show();
}

// Delete branch
function deleteBranch(id, name) {
    if (!confirm(`Are you sure you want to delete the branch "${name}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('branch_id', id);
    
    fetch('../ajax/delete_branch.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            loadBranches();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to delete branch', 'error');
    });
}

// Utility function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Toast notification using SweetAlert2
function showToast(title, message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        const iconType = type === 'error' ? 'error' : type === 'success' ? 'success' : 'info';
        Swal.fire({
            icon: iconType,
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: { popup: 'swal-zindex-9999' },
            willOpen: () => {
              const popup = Swal.getPopup();
              if (popup) popup.style.zIndex = '9999';
            },
        });
        return;
    }
    
    // Check if custom toast function exists globally
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
        return;
    }
    
    // Fallback to alert if no toast system
    alert(`${title}: ${message}`);
}
