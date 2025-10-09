<?php
return [
  // Admin-only pages
  'user-management.php' => ['admin'],
  'system-logs.php' => ['admin'],
  'system-settings.php' => ['admin'],
  
  // Admin + HR only pages  
  'payroll.php' => ['admin', 'hr'],
  'organization-settings.php' => ['admin', 'hr'],
  'deductions.php' => ['admin', 'hr'],
  'allowances.php' => ['admin', 'hr'],
  
  // Admin + HR + Supervisor pages (view-only for supervisor)
  'employee-management.php' => ['admin', 'supervisor', 'hr'],
  'attendance.php' => ['admin', 'supervisor', 'hr'],
  'reports.php' => ['admin', 'supervisor', 'hr'],
  
  // All users can access these pages
  'dashboard.php' => ['admin', 'supervisor', 'hr', 'employee'],
  'profile.php' => ['admin', 'supervisor', 'hr', 'employee'],
  'leaves.php' => ['admin', 'supervisor', 'hr', 'employee'],
  'working-days-calendar.php' => ['admin', 'supervisor', 'hr', 'employee'],
  'dtr.php' => ['admin', 'supervisor', 'hr', 'employee'],
  
  // Additional route files
  'dashboard_employee.php' => ['employee'],
  'dashboard_supervisor.php' => ['supervisor']
];
