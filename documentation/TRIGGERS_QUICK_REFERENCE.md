# 🚀 Database Triggers - Quick Reference Card
*Employee Management System*

## ⚡ Quick Setup
```bash
# 1. Set up database permissions (production)
mysql -u root -p < setup_grants.sql

# 2. Install triggers
php create_triggers.php

# 3. Test triggers  
php final_trigger_test.php
```

## 🔐 **Essential Grants (Production)**
```sql
-- Create app user with minimal permissions
CREATE USER 'emp_app'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE ON emp.employees TO 'emp_app'@'localhost';
GRANT SELECT, INSERT ON emp.system_logs TO 'emp_app'@'localhost';
GRANT SESSION_VARIABLES_ADMIN ON *.* TO 'emp_app'@'localhost';
FLUSH PRIVILEGES;
```

## 🔧 Integration Checklist

### ✅ **Step 1: Add to Employee Operations**
```php
// Add this line BEFORE any employee INSERT/UPDATE
$pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
```

### ✅ **Step 2: Update These Files**
- `ajax/add_employee.php` ← Add user context line
- `ajax/update_employee.php` ← Add user context line  
- Any custom employee management scripts

### ✅ **Step 3: Test Integration**
```php
// Test validation (should fail)
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) 
VALUES (1, '', 'Test', 'invalid-email', -1000, 'male', '09123456789');

// Test success (should work)
INSERT INTO employees (user_id, first_name, last_name, email, basic_salary, gender, contact_number) 
VALUES (1, 'John', 'Doe', 'john@example.com', 25000, 'male', '09123456789');
```

## 🛡️ **4 Triggers Overview**

| Trigger | When | What It Does |
|---------|------|--------------|
| `before_employee_insert_validation` | Before INSERT | Validates: name, email, salary > 0 |
| `before_employee_update_validation` | Before UPDATE | Same validation rules |
| `after_employee_insert_logging` | After INSERT | Logs employee creation |
| `after_employee_update_logging` | After UPDATE | Logs employee changes |

## ⚠️ **Validation Rules**
- ❌ First name cannot be empty
- ❌ Last name cannot be empty  
- ❌ Email cannot be empty or invalid format
- ❌ Salary must be > 0
- ✅ Auto-sets hire date if missing
- ✅ Auto-sets employment status to active

## 📊 **Error Handling Template**
```php
try {
    $pdo->exec("SET @current_user_id = {$_SESSION['user_id']}");
    
    // Your employee operation here
    $stmt = $pdo->prepare("INSERT INTO employees ...");
    $stmt->execute([...]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Trigger errors caught here with descriptive messages
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

## 🔍 **Quick Debug Commands**
```sql
-- Check if triggers exist
SELECT TRIGGER_NAME FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = 'employees';

-- Check recent logs
SELECT * FROM system_logs WHERE full_description LIKE '%Employee%' 
ORDER BY date_performed DESC LIMIT 5;
```

## 📁 **Key Files**
- `create_triggers.php` - Install triggers
- `final_trigger_test.php` - Test suite
- `TRIGGER_DOCUMENTATION.md` - Full documentation
- `libs/DatabaseTriggersHelper.php` - Helper class

## 🎯 **Remember**
1. **Always** set `@current_user_id` before employee operations
2. **Catch** exceptions for validation errors  
3. **Test** after integration
4. **Monitor** system_logs table for audit trail

---
*Generated for Employee Management System - Database Triggers v1.0*
