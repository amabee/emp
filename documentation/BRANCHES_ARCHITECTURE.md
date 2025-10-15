# Branches Feature - Architecture Overview

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE                           │
│                  (Organization Settings Page)                    │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Departments │  │   Branches   │  │  Positions   │          │
│  │    Section   │  │   Section    │  │   Section    │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                           │                                       │
│                           ▼                                       │
│                  ┌─────────────────┐                            │
│                  │  Branch Table   │                            │
│                  │  - Name/Code    │                            │
│                  │  - Manager      │                            │
│                  │  - Employees    │                            │
│                  │  - Actions      │                            │
│                  └─────────────────┘                            │
│                           │                                       │
│                           ▼                                       │
│          ┌──────────────────────────────────┐                   │
│          │    Add/Edit Branch Modals        │                   │
│          │  - Branch Name (required)        │                   │
│          │  - Branch Code (optional)        │                   │
│          │  - Address, Contact, Email       │                   │
│          │  - Branch Manager (dropdown)     │                   │
│          │  - Status (Active/Inactive)      │                   │
│          └──────────────────────────────────┘                   │
└───────────────────────────────┬───────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    JAVASCRIPT LAYER                              │
│                (branch-management.js)                            │
│                                                                   │
│  Functions:                                                      │
│  • loadBranches()           - Fetch all branches                │
│  • displayBranches()        - Render table                      │
│  • editBranch(id)           - Open edit modal                   │
│  • deleteBranch(id)         - Delete with confirmation          │
│  • setupBranchPagination()  - Handle pagination                 │
│                                                                   │
└───────────────────────────────┬───────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      AJAX ENDPOINTS                              │
│                      (ajax/*.php)                                │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐                    │
│  │  get_branches    │  │   add_branch     │                    │
│  │  GET all         │  │   POST create    │                    │
│  └──────────────────┘  └──────────────────┘                    │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐                    │
│  │  get_branch      │  │  update_branch   │                    │
│  │  GET by ID       │  │  POST update     │                    │
│  └──────────────────┘  └──────────────────┘                    │
│                                                                   │
│  ┌──────────────────┐                                           │
│  │  delete_branch   │                                           │
│  │  POST delete     │                                           │
│  └──────────────────┘                                           │
│                                                                   │
└───────────────────────────────┬───────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                   CONTROLLER LAYER                               │
│              (OrganizationController.php)                        │
│                                                                   │
│  Methods:                                                        │
│  • getAllBranches()      - SELECT with JOIN to get manager      │
│  • getBranch($id)        - SELECT single branch                 │
│  • addBranch($data)      - INSERT with validation               │
│  • updateBranch($id)     - UPDATE with validation               │
│  • deleteBranch($id)     - DELETE with safety check             │
│                                                                   │
│  Security Features:                                              │
│  ✓ Prepared statements (SQL injection prevention)               │
│  ✓ Transaction support                                          │
│  ✓ Duplicate code checking                                      │
│  ✓ Employee assignment validation                               │
│                                                                   │
└───────────────────────────────┬───────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                              │
│                      (MySQL/MariaDB)                             │
│                                                                   │
│  ┌──────────────────────────────────────────────┐               │
│  │           branches table                     │               │
│  ├──────────────────────────────────────────────┤               │
│  │ branch_id (PK)                               │               │
│  │ branch_name                                  │               │
│  │ branch_code (UNIQUE)                         │               │
│  │ address                                      │               │
│  │ contact_number                               │               │
│  │ email                                        │               │
│  │ manager_id (FK -> employees)                 │               │
│  │ is_active                                    │               │
│  │ created_by, updated_by                       │               │
│  │ created_at, updated_at                       │               │
│  └──────────────────────────────────────────────┘               │
│                      │                                           │
│                      │ (1:N relationship)                        │
│                      ▼                                           │
│  ┌──────────────────────────────────────────────┐               │
│  │         employees table                      │               │
│  ├──────────────────────────────────────────────┤               │
│  │ employee_id (PK)                             │               │
│  │ branch_id (FK -> branches) ← NEW             │               │
│  │ department_id                                │               │
│  │ position_id                                  │               │
│  │ ... (other fields)                           │               │
│  └──────────────────────────────────────────────┘               │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘


## Data Flow Examples

### 1. Loading Branches (Read)
```
User visits page
    ↓
JavaScript: loadBranches()
    ↓
AJAX: fetch('./ajax/get_branches.php')
    ↓
PHP: OrganizationController::getAllBranches()
    ↓
SQL: SELECT branches with JOIN to employees
    ↓
Return: JSON response with branches array
    ↓
JavaScript: displayBranches() renders table
    ↓
User sees branch list
```

### 2. Adding a Branch (Create)
```
User clicks "Add Branch"
    ↓
Modal opens with form
    ↓
User fills form and submits
    ↓
JavaScript: Form submit handler
    ↓
AJAX: POST to './ajax/add_branch.php'
    ↓
PHP: Validate session & permissions
    ↓
PHP: Validate input data
    ↓
PHP: Check duplicate branch code
    ↓
PHP: OrganizationController::addBranch()
    ↓
SQL: INSERT INTO branches
    ↓
PHP: Log action via SystemLogger
    ↓
Return: JSON success response
    ↓
JavaScript: Show success toast
    ↓
JavaScript: Close modal & reload list
    ↓
User sees new branch in table
```

### 3. Deleting a Branch (Delete with Safety)
```
User clicks delete icon
    ↓
JavaScript: Confirm dialog
    ↓
User confirms
    ↓
AJAX: POST to './ajax/delete_branch.php'
    ↓
PHP: Validate session & permissions (Admin only)
    ↓
PHP: OrganizationController::deleteBranch()
    ↓
SQL: Check if branch has active employees
    ↓
If employees exist:
    Return error: "Cannot delete"
    ↓
    Show error toast
Else:
    SQL: DELETE FROM branches
    ↓
    Log action
    ↓
    Return success
    ↓
    Show success toast
    ↓
    Reload list
```

## Security Layers

```
┌─────────────────────────────────────┐
│  1. Session Authentication          │
│     Check $_SESSION['user_id']      │
└─────────────┬───────────────────────┘
              ↓
┌─────────────────────────────────────┐
│  2. Role-Based Access Control       │
│     Admin/Supervisor/HR only        │
└─────────────┬───────────────────────┘
              ↓
┌─────────────────────────────────────┐
│  3. Input Validation                │
│     Required fields, email format   │
└─────────────┬───────────────────────┘
              ↓
┌─────────────────────────────────────┐
│  4. Business Logic Validation       │
│     Duplicate codes, emp assignment │
└─────────────┬───────────────────────┘
              ↓
┌─────────────────────────────────────┐
│  5. SQL Injection Prevention        │
│     Prepared statements             │
└─────────────┬───────────────────────┘
              ↓
┌─────────────────────────────────────┐
│  6. XSS Prevention                  │
│     HTML escaping in display        │
└─────────────────────────────────────┘
```

## File Dependencies

```
organization-settings.php
│
├─ Includes:
│  ├─ shared/session_handler.php
│  ├─ shared/layout.php
│  ├─ modals/add-department-modal.php
│  ├─ modals/edit-department-modal.php
│  ├─ modals/add-position-modal.php
│  ├─ modals/edit-position-modal.php
│  └─ modals/branch_modals.php ← NEW
│
└─ Scripts:
   └─ assets/js/branch-management.js ← NEW

branch-management.js
│
└─ Calls:
   ├─ ajax/get_branches.php
   ├─ ajax/add_branch.php
   ├─ ajax/update_branch.php
   ├─ ajax/delete_branch.php
   └─ ajax/get_employees.php (for manager dropdown)

ajax/add_branch.php
│
├─ Requires:
│  ├─ shared/config.php
│  ├─ controllers/OrganizationController.php
│  └─ controllers/SystemLogger.php
│
└─ Uses:
   └─ OrganizationController::addBranch()

OrganizationController.php
│
└─ Uses:
   └─ Database connection via getDBConnection()
```

## Integration Points

### Current Integration:
✅ Organization Settings page
✅ System Logger (activity tracking)
✅ User authentication system
✅ Role-based permissions

### Future Integration (TODO):
⭕ Employee add/edit forms
⭕ Employee list filtering
⭕ Dashboard branch statistics
⭕ Payroll by branch
⭕ Attendance reports by branch
⭕ Branch-specific permissions
