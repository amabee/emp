# Advanced Analytics Setup Guide

## 🚀 Quick Setup Instructions

### 1. Create Database Views
Run the following SQL script in your database to create the required views:

```sql
-- Option 1: Run the execute script (recommended)
SOURCE execute_views.sql;

-- Option 2: Run the main views script directly  
SOURCE database_views.sql;
```

### 2. Verify Database Views
After running the script, verify the views were created:

```sql
SHOW TABLES LIKE '%view';
```

You should see:
- `employee_summary_view`
- `active_employees_detailed_view` 
- `comprehensive_employee_analytics_view`

### 3. Access the Analytics Page
Navigate to: **Your Site** → **Advanced Analytics** (in sidebar)

**User Access:**
- ✅ **Admin** - Full access
- ✅ **HR** - Full access  
- ✅ **Supervisor** - Full access
- ❌ **Employee** - No access

## 📊 Features Available

### View Type 1: Department Summary Analytics
- **Purpose**: Aggregated department-level statistics
- **Features**: Employee counts, average salaries, performance ratings
- **Filters**: Min employees, min performance, department name

### View Type 2: Active Employees (Pre-filtered)
- **Purpose**: Pre-filtered employee data with business logic
- **Features**: Tenure categories, salary categories, evaluation status
- **Filters**: Tenure, salary level, recent evaluations, performance

### View Type 3: Comprehensive Employee Analytics
- **Purpose**: Complex multi-table analytics with rankings
- **Features**: Company & department rankings, performance categories
- **Filters**: Performance category, evaluation recency, department rank

## 🛠️ Files Created

### Core Files:
- `database_views.sql` - Database views definitions
- `execute_views.sql` - Setup and test script
- `controllers/DatabaseViewsController.php` - Backend logic
- `pages/advanced_analytics.php` - Frontend interface

### AJAX Endpoints:
- `ajax/get_department_summary.php`
- `ajax/get_filtered_employees.php` 
- `ajax/get_comprehensive_analytics.php`
- `ajax/get_view_statistics.php`

### Updated Files:
- `shared/sidebar.php` - Added Analytics menu item

## 🔍 Troubleshooting

### If Analytics page shows errors:
1. Check database views are created: `SHOW TABLES LIKE '%view';`
2. Verify user permissions in session
3. Check browser console for JavaScript errors
4. Ensure AJAX endpoints are accessible

### If sidebar doesn't show Analytics:
1. Clear browser cache
2. Verify user_type is 'admin', 'hr', or 'supervisor'
3. Check session variables

### Database Connection Issues:
1. Verify `shared/config.php` is accessible
2. Check database credentials
3. Test with other existing pages

## 📈 Usage Examples

### Sample Queries (for testing):
```sql
-- Test department summary
SELECT * FROM employee_summary_view LIMIT 5;

-- Test filtered employees  
SELECT employee_name, department_name, salary_category 
FROM active_employees_detailed_view LIMIT 5;

-- Test comprehensive analytics
SELECT employee_name, performance_category, company_performance_rank
FROM comprehensive_employee_analytics_view 
WHERE company_performance_rank <= 10;
```

## 🎯 Next Steps

1. **Execute the database views** using `execute_views.sql`
2. **Login as Admin/HR/Supervisor** to test the interface
3. **Explore different filters** to see the dynamic functionality
4. **Customize views** by modifying `database_views.sql` if needed

---

**✅ Setup Complete!** Your Advanced Analytics system is ready to use.
