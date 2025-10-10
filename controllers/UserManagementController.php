<?php

class UserManagementController
{
  private $db;

  public function __construct()
  {
    $this->db = getDBConnection();
    if (!$this->db) {
      throw new Exception("Database connection failed");
    }
  }

  public function getAllUsers()
  {
    $stmt = $this->db->prepare(
      'SELECT u.user_id, u.username, u.active_status, ut.type_name AS user_type, ut.user_type_id, 
              e.first_name, e.last_name, e.email, u.created_at
       FROM users u
       LEFT JOIN user_type ut ON u.user_type_id = ut.user_type_id
       LEFT JOIN employees e ON u.user_id = e.user_id
       ORDER BY u.created_at DESC'
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getUserById($id)
  {
    try {
      $stmt = $this->db->prepare(
        'SELECT u.user_id, u.username, u.active_status, ut.type_name AS user_type, ut.user_type_id,
                e.employee_id, e.first_name, e.last_name, e.email, e.image, e.gender, e.birthdate,
                e.contact_number, e.basic_salary, e.employment_status, e.date_hired,
                d.department_name, p.position_name
         FROM users u
         LEFT JOIN user_type ut ON u.user_type_id = ut.user_type_id
         LEFT JOIN employees e ON u.user_id = e.user_id
         LEFT JOIN department d ON e.department_id = d.department_id
         LEFT JOIN job_position p ON e.position_id = p.position_id
         WHERE u.user_id = ?'
      );
      $stmt->execute([$id]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      throw new Exception('Failed to get user: ' . $e->getMessage());
    }
  }

  public function updateUser($id, $data)
  {
    try {
      $this->db->beginTransaction();

      // Check if username already exists for another user
      if ($this->usernameExists($data['username'], $id)) {
        throw new Exception('Username already exists');
      }

      // Update users table
      $stmt = $this->db->prepare(
        'UPDATE users SET username = ?, user_type_id = ?, active_status = ? WHERE user_id = ?'
      );
      
      $stmt->execute([
        $data['username'],
        $data['user_type_id'] ?? null,
        $data['active_status'] ?? 'active',
        $id
      ]);

      // If user has an employee record and email/name is provided, update it
      if (!empty($data['first_name']) || !empty($data['last_name']) || !empty($data['email'])) {
        $employeeStmt = $this->db->prepare(
          'UPDATE employees SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?'
        );
        $employeeStmt->execute([
          $data['first_name'] ?? null,
          $data['last_name'] ?? null,
          $data['email'] ?? null,
          $id
        ]);
      }

      $this->db->commit();

      return [
        'success' => true,
        'message' => 'User updated successfully'
      ];

    } catch (Exception $e) {
      $this->db->rollBack();
      return [
        'success' => false,
        'message' => 'Failed to update user: ' . $e->getMessage()
      ];
    }
  }

  public function deleteUser($id)
  {
    try {
      $this->db->beginTransaction();

      // Check if user exists
      $stmt = $this->db->prepare('SELECT user_id FROM users WHERE user_id = ?');
      $stmt->execute([$id]);
      $user = $stmt->fetch();

      if (!$user) {
        throw new Exception('User not found');
      }

      // Soft delete by setting status to locked (don't actually delete to preserve data integrity)
      $stmt = $this->db->prepare('UPDATE users SET active_status = ? WHERE user_id = ?');
      $stmt->execute(['locked', $id]);

      // Also update employee status if exists
      $employeeStmt = $this->db->prepare('UPDATE employees SET employment_status = 0 WHERE user_id = ?');
      $employeeStmt->execute([$id]);

      $this->db->commit();

      return [
        'success' => true,
        'message' => 'User deleted successfully'
      ];

    } catch (Exception $e) {
      $this->db->rollBack();
      return [
        'success' => false,
        'message' => 'Failed to delete user: ' . $e->getMessage()
      ];
    }
  }

  public function getAllUserTypes()
  {
    try {
      $stmt = $this->db->prepare('SELECT user_type_id as id, type_name as name FROM user_type ORDER BY type_name');
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  // Helper method to check if username exists
  public function usernameExists($username, $excludeUserId = null)
  {
    try {
      $sql = "SELECT user_id FROM users WHERE username = ? AND active_status != 'deleted'";
      $params = [$username];
      
      if ($excludeUserId) {
        $sql .= " AND user_id != ?";
        $params[] = $excludeUserId;
      }
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);
      
      return $stmt->fetch() !== false;
    } catch (Exception $e) {
      return false;
    }
  }

  public function resetPassword($userId, $newPassword)
  {
    try {
      // Validate input
      if ($userId <= 0) {
        return [
          'success' => false,
          'message' => 'Invalid user ID'
        ];
      }

      if (empty($newPassword)) {
        return [
          'success' => false,
          'message' => 'New password is required'
        ];
      }

      if (strlen($newPassword) < 6) {
        return [
          'success' => false,
          'message' => 'Password must be at least 6 characters long'
        ];
      }

      // Check if user exists and get username for logging
      $stmt = $this->db->prepare("SELECT username FROM users WHERE user_id = ?");
      $stmt->execute([$userId]);
      $user = $stmt->fetch();

      if (!$user) {
        return [
          'success' => false,
          'message' => 'User not found'
        ];
      }

      // Hash the new password
      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

      // Update the password
      $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
      $result = $stmt->execute([$hashedPassword, $userId]);

      if ($result) {
        return [
          'success' => true,
          'message' => 'Password reset successfully',
          'username' => $user['username']
        ];
      } else {
        return [
          'success' => false,
          'message' => 'Failed to reset password'
        ];
      }

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

}


?>

