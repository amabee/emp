# Branches Feature Implementation Guide

## Overview
This document describes the implementation of the branches feature in the Employee Management System. The feature allows managing multiple store branches and assigning employees to specific branches.

## Database Changes

### New Table: `branches`
```sql
CREATE TABLE `branches` (
  `branch_id` INT PRIMARY KEY AUTO_INCREMENT,
  `branch_name` VARCHAR(100) NOT NULL,
  `branch_code` VARCHAR(20) UNIQUE,
  `address` TEXT,
  `contact_number` VARCHAR(20),
  `email` VARCHAR(100),
  `manager_id` INT,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT,
  `updated_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

### Modified Table: `employees`
- Added column: `branch_id` INT (nullable)
- Added index: `idx_branch` on `branch_id`

## Installation Steps

### 1. Run the Migration
Execute the SQL migration file to create the branches table and update the employees table:

```bash
# Using MySQL command line
mysql -u your_username -p your_database < dbs/migrations/add_branches_feature.sql

# Or using phpMyAdmin
# Import the file: dbs/migrations/add_branches_feature.sql
```

### 2. Verify Database Changes
Check that:
- `branches` table exists
- `employees` table has `branch_id` column
- Sample branches are created (Main Office, Branch 1, Branch 2)

## File Structure

```
emp/
├── ajax/
│   ├── add_branch.php           # Create new branch
│   ├── get_branches.php         # Fetch all branches
│   ├── get_branch.php           # Fetch single branch
│   ├── update_branch.php        # Update branch
│   └── delete_branch.php        # Delete branch
├── assets/
│   └── js/
│       └── branch-management.js # Frontend JavaScript
├── controllers/
│   └── OrganizationController.php # Updated with branch methods
├── dbs/
│   └── migrations/
│       └── add_branches_feature.sql # Database migration
├── pages/
│   ├── modals/
│   │   └── branch_modals.php    # Add/Edit branch modals
│   └── organization-settings.php # Updated with branches section
└── documentation/
    └── BRANCHES_IMPLEMENTATION.md
```

## Features

### 1. Branch Management (CRUD)
- **Create**: Add new branches with details (name, code, address, contact, manager)
- **Read**: View all branches with employee count
- **Update**: Modify branch information
- **Delete**: Remove branches (only if no active employees assigned)

### 2. Branch Properties
- Branch Name (required)
- Branch Code (optional, unique identifier like "CDO-01")
- Address
- Contact Number
- Email
- Branch Manager (assigned from employees)
- Status (Active/Inactive)

### 3. Employee Assignment
- Employees can be assigned to a branch via `branch_id`
- Branch deletion is prevented if active employees are assigned
- Branch manager must be an existing employee

## API Endpoints

### Get All Branches
```
GET/POST: ajax/get_branches.php
Response: {success: true, data: [branches]}
```

### Get Single Branch
```
GET: ajax/get_branch.php?id={branch_id}
Response: {success: true, data: {branch}}
```

### Add Branch
```
POST: ajax/add_branch.php
Data: {
  branch_name: string (required),
  branch_code: string,
  address: string,
  contact_number: string,
  email: string,
  manager_id: int,
  is_active: int (0|1)
}
Response: {success: true, message: string, branch_id: int}
```

### Update Branch
```
POST: ajax/update_branch.php
Data: {
  branch_id: int (required),
  branch_name: string (required),
  branch_code: string,
  address: string,
  contact_number: string,
  email: string,
  manager_id: int,
  is_active: int (0|1)
}
Response: {success: true, message: string}
```

### Delete Branch
```
POST: ajax/delete_branch.php
Data: {branch_id: int}
Response: {success: true, message: string}
```

## Access Control

### Permissions
- **View Branches**: Admin, Supervisor, HR
- **Add/Edit Branches**: Admin, Supervisor
- **Delete Branches**: Admin only

## Usage Guide

### Adding a Branch
1. Navigate to "Organization Settings"
2. Scroll to "Branches" section
3. Click "Add Branch" button
4. Fill in branch details
5. Select branch manager (optional)
6. Set status (Active/Inactive)
7. Click "Add Branch"

### Editing a Branch
1. Find the branch in the branches table
2. Click the edit icon (pencil)
3. Modify branch information
4. Click "Update Branch"

### Deleting a Branch
1. Find the branch in the branches table
2. Click the delete icon (trash)
3. Confirm deletion
4. Note: Cannot delete if active employees are assigned

### Assigning Employees to Branches
**To be implemented**: Update employee add/edit forms to include branch selection dropdown.

## Next Steps / TODO

### 1. Update Employee Management Forms
Add branch selection to employee add/edit forms:

```javascript
// In employee add/edit forms, add:
<select name="branch_id" class="form-select">
  <option value="">Select Branch</option>
  <!-- Populated from get_branches.php -->
</select>
```

### 2. Add Branch Filter to Employee List
Allow filtering employees by branch in employee management page.

### 3. Branch Dashboard/Reports
Create branch-specific reports showing:
- Employee count per branch
- Payroll summary per branch
- Attendance statistics per branch

### 4. Branch-Level Permissions
Implement role-based access where branch managers can only view/manage employees in their branch.

## Troubleshooting

### Issue: Branch code already exists
**Solution**: Use a unique branch code or leave it empty.

### Issue: Cannot delete branch
**Solution**: Ensure no active employees are assigned to the branch. Reassign employees first.

### Issue: Branches not loading
**Solution**: Check:
1. Database connection
2. Browser console for JavaScript errors
3. AJAX endpoint responses in Network tab

## Security Considerations

1. **Input Validation**: All inputs are validated server-side
2. **Email Validation**: Email format is checked using PHP filter
3. **SQL Injection Prevention**: Prepared statements used throughout
4. **Session Management**: All endpoints check user authentication
5. **Role-Based Access**: Permissions enforced on all operations
6. **XSS Prevention**: HTML escaping in JavaScript display functions

## Database Relationships

```
branches (1) ----< (N) employees
  ^
  |
  | (self-referencing)
  |
employees.manager_id
```

- One branch can have many employees
- One employee can be assigned to one branch
- One employee can be the manager of one branch

## Testing Checklist

- [ ] Run migration successfully
- [ ] Add new branch
- [ ] Edit existing branch
- [ ] Delete branch without employees
- [ ] Attempt to delete branch with employees (should fail)
- [ ] Verify duplicate branch code prevention
- [ ] Test email validation
- [ ] Verify pagination works
- [ ] Check permissions (admin, supervisor, hr, employee)
- [ ] Verify logging of actions

## Credits
Implementation Date: October 15, 2025
Developed for: Employee Management System (EMP)
