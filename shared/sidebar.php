<?php
include 'controllers/SystemController.php';
$systemController = new SystemController();
$system_details = $systemController->getSystemDetails();

// Get current page name for active menu item from URI (since there's routing)
$current_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', trim($current_uri, '/'));
$current_page = '';

// Extract page name from URI - handle dynamic admin path
foreach ($uri_parts as $part) {
  if (strpos($part, '.php') !== false) {
    $current_page = basename($part, '.php');
    break;
  }
}

// If no .php found, try the last part of the URI
if (empty($current_page) && !empty($uri_parts)) {
  $last_part = end($uri_parts);
  if (!empty($last_part) && $last_part !== 'admin') {
    $current_page = $last_part;
  }
}


// Function to check if menu item should be active
function isMenuActive($page_names, $current_page)
{
  // Handle both single page name and array of page names
  if (is_array($page_names)) {
    return in_array($current_page, $page_names) ? 'active' : '';
  }
  return $page_names === $current_page ? 'active' : '';
}

// Handle special cases for pages that might have different names but same section
$page_aliases = [
  'index' => 'dashboard',
  'home' => 'dashboard',
  'user-management' => 'user-management',
  'system-settings' => 'system-settings',
  'attendance' => 'attendance',
  'leaves' => 'leaves',
  'payroll' => 'payroll',
  'profile' => 'profile',
  'logout' => 'logout',
  'employee-management' => 'employee-management',
  'organization-settings' => 'organization-settings',
  'deductions' => 'deductions',
  'allowances' => 'allowances',
  'system-logs' => 'system-logs',
  'reports' => 'reports',
  'dtr' => 'dtr',
  'performance' => 'performance'
];

// Check if current page has an alias
if (isset($page_aliases[$current_page])) {
  $current_page = $page_aliases[$current_page];
}
?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand mt-5">
    <a href="#" class="app-brand-link">
      <span class="app-brand-logo">
        <span class="text-primary">
          <img src="/uploads/company/<?= htmlspecialchars($system_details['logo']) ?>" alt="Logo"
            style="max-width: 50px; max-height: 50px; width: auto; height: auto; object-fit: contain; border-radius: 50%;">
        </span>
      </span>
      <span
        class="app-brand-text menu-text fw-bold ms-2 fs-6"><?= htmlspecialchars($system_details['name'] ?? 'EMP System') ?>
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
    </a>
  </div>

  <div class="menu-divider mt-0"></div>
  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Dashboard - All users -->
    <li class="menu-item <?php echo isMenuActive('dashboard', $current_page); ?>">
      <a href="./dashboard.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div data-i18n="Dashboard">Dashboard</div>
      </a>
    </li>

    <?php if ($user_type === 'admin'): ?>
      <!-- Admin Only Section -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Administration</span>
      </li>
      <li class="menu-item <?php echo isMenuActive('user-management', $current_page); ?>">
        <a href="./user-management.php" class="menu-link">
          <!-- <i class="menu-icon tf-icons bx bx-cog"></i> -->
          <i class='menu-icon tf-icons bx  bx-user'  ></i> 
          <div data-i18n="Users">User Management</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('system-settings', $current_page); ?>">
        <a href="./system-settings.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-cog"></i>
          <div data-i18n="Settings">System Settings</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('system-logs', $current_page); ?>">
        <a href="./system-logs.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-list-ul"></i>
          <div data-i18n="Logs">System Logs</div>
        </a>
      </li>
    <?php endif; ?>

    <?php if (in_array($user_type, ['admin', 'hr'])): ?>
      <!-- Admin + HR Section -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">HR Management</span>
      </li>
      <li class="menu-item <?php echo isMenuActive('employee-management', $current_page); ?>">
        <a href="./employee-management.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-group"></i>
          <div data-i18n="Employees">Employee Management</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('organization-settings', $current_page); ?>">
        <a href="./organization-settings.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-buildings"></i>
          <div data-i18n="Organization">Organization Settings</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive(['payroll'], $current_page); ?>">
        <a href="./payroll.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-money"></i>
          <div data-i18n="Payroll">Payroll Management</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('deductions', $current_page); ?>">
        <a href="./deductions.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-minus-circle"></i>
          <div data-i18n="Deductions">Deductions</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('allowances', $current_page); ?>">
        <a href="./allowances.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-plus-circle"></i>
          <div data-i18n="Allowances">Allowances</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('performance', $current_page); ?>">
        <a href="./performance.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-bar-chart"></i>
          <div data-i18n="Performance">Performance Reports (View Only)</div>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($user_type === 'supervisor'): ?>
      <!-- Supervisor Section -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Team Management</span>
      </li>
      <li class="menu-item <?php echo isMenuActive('employee-management', $current_page); ?>">
        <a href="./employee-management.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-group"></i>
          <div data-i18n="Employees">View Team Members</div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('performance', $current_page); ?>">
        <a href="./performance.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-bar-chart"></i>
          <div data-i18n="Performance">Performance Evaluations</div>
        </a>
      </li>
    <?php endif; ?>

    <?php if (in_array($user_type, ['admin', 'supervisor', 'hr'])): ?>
      <!-- Admin + HR + Supervisor Section -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Monitoring</span>
      </li>
      
      <li class="menu-item <?php echo isMenuActive('attendance', $current_page); ?>">
        <a href="./attendance.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-calendar-check"></i>
          <div data-i18n="Attendance">Attendance <?php echo $user_type === 'supervisor' ? '(View)' : ''; ?></div>
        </a>
      </li>
      <li class="menu-item <?php echo isMenuActive('working-days-calendar', $current_page); ?>">
        <a href="./working-days-calendar.php" class="menu-link">
          <i class="menu-icon tf-icons bx bx-calendar-event"></i>
          <div data-i18n="Calendar">Working Calendar</div>
        </a>
      </li>
    <?php endif; ?>

    <!-- Leave Management - All users -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text"><?php echo $user_type === 'employee' ? 'My Requests' : 'Leave Management'; ?></span>
    </li>
    <li class="menu-item <?php echo isMenuActive('leaves', $current_page); ?>">
      <a href="./leaves.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-calendar-x"></i>
        <div data-i18n="Leaves">
          <?php 
            if ($user_type === 'employee') echo 'My Leave Requests';
            elseif ($user_type === 'supervisor') echo 'Leave Approvals';
            else echo 'Leave Management';
          ?>
        </div>
      </a>
    </li>

    <!-- Personal Section - All users -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Personal</span>
    </li>
    <li class="menu-item <?php echo isMenuActive('dtr', $current_page); ?>">
      <a href="./dtr.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-time"></i>
        <div data-i18n="DTR">Time Tracking (DTR)</div>
      </a>
    </li>
    <li class="menu-item <?php echo isMenuActive('profile', $current_page); ?>">
      <a href="./profile.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-user-circle"></i>
        <div data-i18n="Profile">My Profile</div>
      </a>
    </li>

    <!-- Logout -->
    <li class="menu-divider mt-3"></li>
    <li class="menu-item <?php echo isMenuActive('logout', $current_page); ?>">
      <a href="../logout.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
        <div data-i18n="Logout">Logout</div>
      </a>
    </li>

  </ul>
</aside>
