# Branch Manager Selection - Update Documentation

## What Changed

Updated the branch manager selection to show only **available employees** who are not already assigned as branch managers in other branches.

## Files Modified

1. **`ajax/get_available_branch_managers.php`** (NEW)
   - New endpoint to get employees who aren't already branch managers
   - Accepts optional `exclude_branch_id` parameter for edit mode

2. **`controllers/OrganizationController.php`** (MODIFIED)
   - Added `getAvailableBranchManagers($excludeBranchId)` method
   - Filters out employees who are already managing other branches
   - When editing a branch, excludes that branch from the filter (so current manager stays available)

3. **`assets/js/branch-management.js`** (MODIFIED)
   - Updated `loadEmployeesForBranchManager()` to use new endpoint
   - Now shows department and position in dropdown for better identification
   - Passes branch ID when editing to keep current manager available

4. **`pages/branches.php`** (NEW)
   - Separate page for branches management (not in organization settings)

## How It Works

### Adding a New Branch
1. User clicks "Add Branch"
2. Manager dropdown loads employees who are NOT currently managing any branch
3. User selects an available employee
4. Branch is created with that manager

### Editing an Existing Branch
1. User clicks edit on a branch
2. Manager dropdown loads:
   - The current manager (even if they're managing this branch)
   - All other employees who are NOT managing other branches
3. User can keep current manager or select a different available employee

### Manager Dropdown Display
Shows employees with additional info:
```
John Doe - Human Resources (HR Manager)
Jane Smith - Finance (Accountant)
Bob Johnson - IT (Systems Admin)
```

## SQL Logic

The query excludes employees who are already managers:

```sql
SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as name
FROM employees e
WHERE e.employment_status = 1
AND e.employee_id NOT IN (
    SELECT manager_id 
    FROM branches 
    WHERE manager_id IS NOT NULL
    AND branch_id != ? -- Excludes current branch when editing
)
```

## Benefits

✅ **Prevents Double Assignment**: An employee can only be manager of one branch
✅ **Better UX**: Shows only valid options in dropdown
✅ **Flexible Editing**: Current manager stays selectable when editing
✅ **Clear Display**: Shows department and position for easier identification
✅ **Data Integrity**: Maintains one-to-one relationship between employees and branch management

## Testing

1. Add Branch 1 with Employee A as manager
2. Try to add Branch 2 - Employee A should NOT appear in manager dropdown
3. Edit Branch 1 - Employee A should still appear (current manager)
4. Remove Employee A from Branch 1
5. Employee A should now appear in all branch manager dropdowns again

## API Endpoint

**GET/POST** `ajax/get_available_branch_managers.php`

**Parameters:**
- `exclude_branch_id` (optional) - Branch ID to exclude when editing

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "first_name": "John",
      "last_name": "Doe",
      "department_name": "Human Resources",
      "position_name": "HR Manager"
    }
  ]
}
```

## Access

Navigate to: `pages/branches.php`

This is now a **standalone page** separate from organization settings.
