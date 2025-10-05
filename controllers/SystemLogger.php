<?php
require_once __DIR__ . '/../shared/config.php';

class SystemLogger
{
  private $db;

  public function __construct()
  {
    $this->db = getDBConnection();
  }

  /**
   * Log system activities
   */
  public function log($userId, $action, $description, $ipAddress = null)
  {
    try {
      if ($ipAddress === null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
      }

      // Map actions to enum values
      $action = $this->mapActionToEnum($action);

      $stmt = $this->db->prepare("
                INSERT INTO system_logs (user_id, action_performed, full_description, date_performed, ip_address) 
                VALUES (?, ?, ?, NOW(), ?)
            ");

      return $stmt->execute([$userId, $action, $description, $ipAddress]);
    } catch (Exception $e) {
      error_log("SystemLogger Error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Map action strings to database enum values
   */
  private function mapActionToEnum($action)
  {
    $action = strtoupper(trim($action));
    
    // Handle different variations of actions
    switch ($action) {
      case 'CREATED':
      case 'CREATE':
      case 'ADD':
      case 'ADDED':
        return 'CREATE';
        
      case 'UPDATED':
      case 'UPDATE':
      case 'EDIT':
      case 'EDITED':
      case 'MODIFIED':
      case 'RESET PASSWORD FOR':
      case 'COMPANY_UPDATE':
      case 'SETTING_UPDATE':
        return 'UPDATE';
        
      case 'DELETED':
      case 'DELETE':
      case 'REMOVE':
      case 'REMOVED':
        return 'DELETE';
        
      case 'LOGIN':
      case 'LOGGED IN':
      case 'SIGN IN':
        return 'LOGIN';
        
      case 'LOGOUT':
      case 'LOGGED OUT':
      case 'SIGN OUT':
        return 'LOGOUT';
        
      default:
        // Default to UPDATE for unknown actions
        return 'UPDATE';
    }
  }

  /**
   * Log user management actions with full names
   */
  public function logUserAction($performerId, $action, $targetUserId, $details = '')
  {
    try {
      // Get performer's full name (join with employees table)
      $performerStmt = $this->db->prepare("
                SELECT CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name, u.username 
                FROM users u
                LEFT JOIN employees e ON u.user_id = e.user_id
                WHERE u.user_id = ?
            ");
      $performerStmt->execute([$performerId]);
      $performer = $performerStmt->fetch();
      $performerName = trim($performer['full_name']) ?: $performer['username'];

      // Get target user's full name (join with employees table)
      $targetStmt = $this->db->prepare("
                SELECT CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name, u.username 
                FROM users u
                LEFT JOIN employees e ON u.user_id = e.user_id
                WHERE u.user_id = ?
            ");
      $targetStmt->execute([$targetUserId]);
      $target = $targetStmt->fetch();
      $targetName = trim($target['full_name']) ?: $target['username'];

      $description = "{$performerName} {$action} user account for {$targetName}";
      if (!empty($details)) {
        $description .= " - {$details}";
      }

      return $this->log($performerId, $action, $description);
    } catch (Exception $e) {
      error_log("SystemLogger UserAction Error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Log employee management actions
   */
  public function logEmployeeAction($performerId, $action, $employeeId, $details = '')
  {
    try {
      // Get performer's full name (join with employees table)
      $performerStmt = $this->db->prepare("
                SELECT CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name, u.username 
                FROM users u
                LEFT JOIN employees e ON u.user_id = e.user_id
                WHERE u.user_id = ?
            ");
      $performerStmt->execute([$performerId]);
      $performer = $performerStmt->fetch();
      $performerName = trim($performer['full_name']) ?: $performer['username'];

      // Get employee's full name
      $employeeStmt = $this->db->prepare("
                SELECT CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name, employee_id 
                FROM employees WHERE employee_id = ?
            ");
      $employeeStmt->execute([$employeeId]);
      $employee = $employeeStmt->fetch();
      $employeeName = trim($employee['full_name']) ?: "Employee ID: {$employee['employee_id']}";

      $description = "{$performerName} {$action} employee record for {$employeeName}";
      if (!empty($details)) {
        $description .= " - {$details}";
      }

      return $this->log($performerId, $action, $description);
    } catch (Exception $e) {
      error_log("SystemLogger EmployeeAction Error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Log organizational changes (departments, positions)
   */
  public function logOrganizationalAction($performerId, $action, $type, $name, $details = '')
  {
    try {
      // Get performer's full name (join with employees table)
      $performerStmt = $this->db->prepare("
                SELECT CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name, u.username 
                FROM users u
                LEFT JOIN employees e ON u.user_id = e.user_id
                WHERE u.user_id = ?
            ");
      $performerStmt->execute([$performerId]);
      $performer = $performerStmt->fetch();
      $performerName = trim($performer['full_name']) ?: $performer['username'];

      $description = "{$performerName} {$action} {$type}: {$name}";
      if (!empty($details)) {
        $description .= " - {$details}";
      }

      return $this->log($performerId, $action, $description);
    } catch (Exception $e) {
      error_log("SystemLogger OrganizationalAction Error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Log authentication actions
   */
  public function logAuthAction($userId, $action, $details = '')
  {
    try {
      // Get user's full name (join with employees table)
      $userStmt = $this->db->prepare("
                SELECT CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name, u.username 
                FROM users u
                LEFT JOIN employees e ON u.user_id = e.user_id
                WHERE u.user_id = ?
            ");
      $userStmt->execute([$userId]);
      $user = $userStmt->fetch();
      $userName = trim($user['full_name']) ?: $user['username'];

      $description = "{$userName} {$action}";
      if (!empty($details)) {
        $description .= " - {$details}";
      }

      return $this->log($userId, $action, $description);
    } catch (Exception $e) {
      error_log("SystemLogger AuthAction Error: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Log system settings changes
   */
  public function logSettingChange($performerId, $setting, $oldValue, $newValue)
  {
    try {
      // Get performer's full name (join with employees table)
      $performerStmt = $this->db->prepare("
                SELECT CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as full_name, u.username 
                FROM users u
                LEFT JOIN employees e ON u.user_id = e.user_id
                WHERE u.user_id = ?
            ");
      $performerStmt->execute([$performerId]);
      $performer = $performerStmt->fetch();
      $performerName = trim($performer['full_name']) ?: $performer['username'];

      $description = "{$performerName} changed {$setting} from '{$oldValue}' to '{$newValue}'";

      return $this->log($performerId, 'SETTING_UPDATE', $description);
    } catch (Exception $e) {
      error_log("SystemLogger SettingChange Error: " . $e->getMessage());
      return false;
    }
  }
}
?>

