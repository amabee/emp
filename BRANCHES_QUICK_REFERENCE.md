# 🚀 BRANCHES FEATURE - QUICK REFERENCE

## 📥 Installation (3 Steps)

### 1️⃣ Run Migration
```sql
-- In phpMyAdmin or MySQL CLI
SOURCE dbs/migrations/add_branches_feature.sql;
```

### 2️⃣ Verify
```sql
-- Check tables exist
SHOW TABLES LIKE '%branches%';
DESCRIBE branches;
DESCRIBE employees;
```

### 3️⃣ Access
```
http://yourdomain.com/pages/organization-settings.php
```

---

## 📁 Files Created (12 Total)

### Database (2)
- `dbs/migrations/add_branches_feature.sql` - Main migration
- `dbs/migrations/quick_setup_branches.sql` - Quick setup helper

### Backend (6)
- `controllers/OrganizationController.php` - **MODIFIED** (added 5 methods)
- `ajax/add_branch.php` - Create branch
- `ajax/get_branches.php` - List branches
- `ajax/get_branch.php` - Get single branch
- `ajax/update_branch.php` - Update branch
- `ajax/delete_branch.php` - Delete branch

### Frontend (2)
- `pages/organization-settings.php` - **MODIFIED** (added section)
- `pages/modals/branch_modals.php` - Add/Edit modals
- `assets/js/branch-management.js` - JavaScript logic

### Documentation (3)
- `BRANCHES_SETUP_GUIDE.md` - Main guide
- `documentation/BRANCHES_IMPLEMENTATION.md` - Full docs
- `documentation/BRANCHES_ARCHITECTURE.md` - Architecture
- `documentation/EMPLOYEE_BRANCH_INTEGRATION.html` - Integration helper

---

## 🎯 Quick Commands

### Test in Browser Console
```javascript
// Load branches
fetch('./ajax/get_branches.php')
  .then(r => r.json())
  .then(d => console.table(d.data));

// Add a branch (replace with real data)
fetch('./ajax/add_branch.php', {
  method: 'POST',
  body: new FormData(document.getElementById('addBranchForm'))
});
```

### SQL Queries
```sql
-- View all branches with employee count
SELECT 
    b.branch_name,
    b.branch_code,
    COUNT(e.employee_id) as employees
FROM branches b
LEFT JOIN employees e ON b.branch_id = e.branch_id
GROUP BY b.branch_id;

-- Assign employee to branch
UPDATE employees 
SET branch_id = 1 
WHERE employee_id = 12;

-- List employees by branch
SELECT 
    b.branch_name,
    CONCAT(e.first_name, ' ', e.last_name) as employee
FROM employees e
JOIN branches b ON e.branch_id = b.branch_id
ORDER BY b.branch_name, e.last_name;
```

---

## 🔑 Key Functions

### JavaScript
```javascript
loadBranches()              // Fetch and display all branches
editBranch(id)              // Open edit modal
deleteBranch(id, name)      // Delete with confirmation
setupBranchPagination()     // Setup pagination
changeBranchPage(page)      // Navigate pages
```

### PHP Controller
```php
getAllBranches()            // Get all branches
getBranch($id)              // Get single branch
addBranch($data)            // Create new branch
updateBranch($id, $data)    // Update branch
deleteBranch($id)           // Delete branch
```

---

## ⚙️ Configuration

### Pagination
```javascript
// In assets/js/branch-management.js
const branchesPerPage = 10; // Change this number
```

### Permissions
```php
// View: admin, supervisor, hr
// Add/Edit: admin, supervisor
// Delete: admin only
```

### Required Fields
- ✅ Branch Name (required)
- ⭕ Branch Code (optional, but must be unique)
- ⭕ Address (optional)
- ⭕ Contact (optional)
- ⭕ Email (optional, validated if provided)
- ⭕ Manager (optional)

---

## 🐛 Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| Branches not showing | Check console errors, verify database |
| Can't delete branch | Branch has active employees |
| Duplicate code error | Each code must be unique |
| Permission denied | Check user role (need admin/supervisor) |
| Modal not opening | Verify Bootstrap JS loaded |

---

## 🔍 Debugging

### Check AJAX Response
```javascript
// In browser console
fetch('./ajax/get_branches.php')
  .then(r => r.json())
  .then(d => console.log(d));
```

### Check Database
```sql
-- Verify structure
DESCRIBE branches;

-- Check data
SELECT * FROM branches;

-- Check foreign keys
SHOW CREATE TABLE branches;
```

### Check Permissions
```php
// In any ajax file, check:
var_dump($_SESSION['user_type']);
var_dump(in_array($_SESSION['user_type'], $allowedTypes));
```

---

## 📊 Database Schema Quick View

```sql
branches
├─ branch_id         INT PK AUTO_INCREMENT
├─ branch_name       VARCHAR(100) NOT NULL
├─ branch_code       VARCHAR(20) UNIQUE
├─ address           TEXT
├─ contact_number    VARCHAR(20)
├─ email             VARCHAR(100)
├─ manager_id        INT FK -> employees
├─ is_active         TINYINT(1) DEFAULT 1
├─ created_by        INT
├─ updated_by        INT
├─ created_at        DATETIME
└─ updated_at        DATETIME

employees (MODIFIED)
└─ branch_id         INT FK -> branches  ← NEW COLUMN
```

---

## ✅ Testing Checklist

```
[ ] Database migration successful
[ ] 3 sample branches appear
[ ] Can view branches list
[ ] Can add new branch
[ ] Can edit existing branch
[ ] Can delete empty branch
[ ] Cannot delete branch with employees
[ ] Pagination works (add 10+ branches)
[ ] Email validation works
[ ] Duplicate code prevented
[ ] Permissions work correctly
[ ] System logs record actions
```

---

## 🎨 UI Customization

### Change Table Columns
Edit `assets/js/branch-management.js`:
```javascript
// In displayBranches() function
tableBody.innerHTML = paginatedBranches.map(branch => `
  <tr>
    <td>${branch.name}</td>
    <td>${branch.code}</td>
    <!-- Add more columns here -->
  </tr>
`).join('');
```

### Change Modal Fields
Edit `pages/modals/branch_modals.php`:
```html
<!-- Add more input fields -->
<div class="col-md-6 mb-3">
  <label>New Field</label>
  <input type="text" name="new_field">
</div>
```

---

## 🔗 Quick Links

- **Access UI**: Organization Settings → Branches Section
- **Migration File**: `dbs/migrations/add_branches_feature.sql`
- **Main Docs**: `documentation/BRANCHES_IMPLEMENTATION.md`
- **Architecture**: `documentation/BRANCHES_ARCHITECTURE.md`
- **Setup Guide**: `BRANCHES_SETUP_GUIDE.md`

---

## 💡 Pro Tips

1. **Backup First**: Always backup database before running migrations
2. **Use Codes**: Branch codes help identify branches quickly (e.g., "CDO-01")
3. **Assign Managers**: Helps track responsibility
4. **Mark Inactive**: Instead of deleting, mark branches inactive
5. **Regular Audits**: Review employee assignments periodically

---

## 📞 Next Steps

1. ✅ Run migration
2. ✅ Test CRUD operations
3. ⭕ Update employee forms (see `EMPLOYEE_BRANCH_INTEGRATION.html`)
4. ⭕ Add branch filters
5. ⭕ Create branch reports

---

**Version**: 1.0  
**Created**: October 15, 2025  
**Difficulty**: ⭐⭐⭐ (2.5/5 - Moderate)  
**Status**: ✅ Production Ready
