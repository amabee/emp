<?php

class Login
{
  private $db;

  public function __construct()
  {
    $this->db = getDBConnection();
    if (!$this->db) {
      throw new Exception("Database connection failed");
    }
  }

  public function authenticate($username, $password)
  {
    // include logger (best-effort)
    require_once __DIR__ . '/../SystemLogger.php';
    $logger = null;

    try {
      $logger = new SystemLogger();
    } catch (Exception $e) {
      error_log("Logger init failed: " . $e->getMessage());
    }

    $stmt = $this->db->prepare(
      'SELECT u.user_id as user_id, u.username, u.password, e.first_name as firstname, 
              e.middle_name as middlename, e.last_name as lastname, ut.type_name AS type,
              e.image as image
       FROM users u 
       LEFT JOIN employees e ON u.user_id = e.user_id 
       LEFT JOIN user_type ut ON u.user_type_id = ut.user_type_id
       WHERE u.username = ? OR e.email = ?'
    );
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname'];
      $_SESSION['user_type'] = strtolower($user['type']);
      $_SESSION['last_login'] = date('Y-m-d H:i:s');
      $_SESSION['user_image'] = $user['image'] ?: null;

      $updateStmt = $this->db->prepare('UPDATE users SET last_login = ? WHERE user_id = ?');
      $updateStmt->execute([$_SESSION['last_login'], $user['user_id']]);

      // Log successful login (best-effort)
      try {
        if ($logger) {
          $logger->logAuthAction($user['user_id'], 'LOGIN');
        }
      } catch (Exception $e) { /* don't interrupt login on logging errors */
        error_log('Login logging failed: ' . $e->getMessage());
      }

      return true;
    }
    // Log failed login attempt (best-effort)
    try {
      if ($logger) {
        $logger->log(null, 'LOGIN', "Failed login attempt for '{$username}'");
      }
    } catch (Exception $e) { 
      /* ignore */
      error_log('Failed login attempt logging failed: ' . $e->getMessage());
    }
    return false;
  }
}

?>

