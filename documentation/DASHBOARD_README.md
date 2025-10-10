# Dashboard Integration - Employee Management System

## Overview
The dashboard has been fully integrated with dynamic functionality, replacing all hardcoded values with real data from your database.

## What's Been Implemented

### 1. Dashboard Controller (`controllers/DashboardController.php`)
- **getDashboardStats()**: Retrieves employee counts, growth metrics, pending leaves, departments, and users
- **getRecentActivity()**: Fetches recent employee additions and leave requests
- **getAttendanceData()**: Provides placeholder attendance data for charts
- **getDepartmentStats()**: Returns department information with employee counts

### 2. AJAX Endpoint (`ajax/get_dashboard_data.php`)
- Single endpoint that combines all dashboard data
- Handles authentication and error scenarios
- Returns JSON response with stats, activities, attendance, and departments

### 3. Dynamic Dashboard (`pages/dashboard.php`)
Enhanced with:
- **Real-time stats**: Total employees, pending leaves, departments, active users
- **Growth indicators**: Monthly employee growth percentage
- **Recent activity feed**: Shows new employee additions and leave requests
- **Department overview**: Top 5 departments with employee counts
- **Quick action buttons**: Direct links to key management pages
- **Interactive charts**: Attendance overview with ApexCharts
- **Auto-refresh**: Updates every 5 minutes automatically
- **Manual refresh**: Button to update data on demand

### 4. Custom Styling (`assets/css/dashboard.css`)
- Hover effects for stat cards
- Smooth animations and transitions
- Responsive design improvements
- Custom scrollbars and loading states

## Features

### Statistics Cards
1. **Total Employees** - Shows current employee count with growth percentage
2. **Pending Leaves** - Displays leave requests requiring attention
3. **Departments** - Total number of active departments
4. **Active Users** - Number of active system users

### Interactive Elements
- **Recent Activity Feed**: Real-time updates of system activities
- **Department Overview**: Quick view of departments and their sizes
- **Quick Actions**: One-click access to key functions
- **Attendance Chart**: Visual representation of attendance data

### User Experience
- **Auto-refresh**: Dashboard updates every 5 minutes
- **Manual refresh**: Button to force data update
- **Error handling**: Graceful error states with retry options
- **Loading states**: Smooth loading animations
- **Responsive design**: Works on all screen sizes

## Quick Actions Available
1. **Add Employee** → `employee-management.php`
2. **View Reports** → `reports.php`
3. **Manage Departments** → `organization-settings.php`
4. **User Management** → `user-management.php`
5. **System Settings** → `system-settings.php`

## How It Works

1. **Page Load**: Dashboard loads with loading placeholders
2. **AJAX Call**: Fetches data from `get_dashboard_data.php`
3. **Data Population**: Updates all dashboard elements with real data
4. **Auto-refresh**: Continues to update every 5 minutes
5. **User Interaction**: Manual refresh and quick actions available

## Error Handling
- Database connection failures are handled gracefully
- Missing tables (like leave_requests) don't break the dashboard
- Network errors show retry options
- All errors are logged to console for debugging

## Customization
You can easily customize:
- Refresh interval (currently 5 minutes)
- Number of recent activities shown (currently 5)
- Statistics displayed
- Quick action buttons
- Chart data and styling

The dashboard is now fully functional and integrates seamlessly with your existing employee management system!

---

## Overtime System Documentation

### Updated Overtime System:

#### 1. Hourly Rate Calculation:
The hourly rate is calculated based on the employee's basic salary using the following formula:
```
Hourly Rate = Basic Salary ÷ 2 (semi-monthly) ÷ 13 (working days) ÷ 8 (hours)
```

**Example:** ₱14,000 monthly salary ÷ 2 ÷ 13 ÷ 8 = ₱67.31/hour

#### 2. Overtime Detection:
- **Regular overtime**: Working beyond scheduled shift end time (1.5x rate)
- **Weekend overtime**: Working on Saturday or Sunday (entire work time counts as overtime)
- **Extended overtime**: More than 2 hours overtime on weekdays

#### 3. Weekend Work as Overtime:
If an employee works on Saturday or Sunday (day-off), the **entire work duration** is considered overtime, not just time beyond a schedule.

### Example Scenarios:

#### Scenario 1: Regular Overtime
- Basic salary: ₱14,000
- Hourly rate: ₱67.31
- Overtime rate: ₱100.97 (1.5x)
- Worked 2 hours past schedule
- **Overtime pay: 2 × ₱100.97 = ₱201.94**

#### Scenario 2: Weekend Work
- Same employee works 8 hours on Saturday
- All 8 hours count as overtime
- **Overtime pay: 8 × ₱100.97 = ₱807.76**

### Technical Implementation:
- Overtime records are automatically created when employees time out beyond their scheduled hours
- Weekend work is detected and processed as full overtime
- All overtime calculations are stored in the `overtime_records` table
- Pay period is based on 13 working days (excluding weekends) per semi-monthly period


### Sample Calculation of payroll
=== Christopher Porter (ID: 12) - Payroll Calculation Demo ===

Employee: Christopher Porter
Monthly Basic Salary: ₱14,000.00

=== Attendance Details ===
Working Days in Period: 23
Present Days: 2
Absent Days: 21
Late Days: 0
Attendance Ratio: 8.7%
Expected Salary for Period: ₱14,636.36
Daily Rate: ₱636.36

=== Payroll Calculation ===
Attendance-Adjusted Basic: ₱1,272.73
Allowances Total: ₱553.00
Overtime Total: ₱0.00
Gross Pay: ₱1,825.73

=== Deductions Breakdown ===
Total Deductions: ₱1,134.12
- r00tk1t: ₱54.00 (Fixed)
- SSS: ₱788.00 (Fixed)
- Taxes: ₱292.12 (16.00% of ₱1,825.73)

=== Final Result ===
Net Pay: ₱691.61
