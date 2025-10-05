<?php
require_once __DIR__ . '/SystemLogger.php';

class AllowanceController
{
  private $db;
  private $logger;

  public function __construct()
  {
    $this->db = getDBConnection();
    $this->logger = new SystemLogger();
    if (!$this->db)
      throw new Exception('DB connection failed');
  }

  public function getAllowanceTypes()
  {
    try {
      $sql = "SELECT allowance_id, allowance_type, description, is_active, created_by, updated_by, created_at, updated_at FROM allowance";
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      return [];
    }
  }

  public function addAllowance($data, $userId)
  {
    try {
      $this->db->beginTransaction();
      $name = trim($data['name'] ?? '');
      if ($name === '')
        throw new Exception('Allowance name required');

      // try simple insert, fallback to created_by/updated_by if schema requires
      $description = isset($data['description']) ? trim($data['description']) : null;
      try {
        $stmt = $this->db->prepare("INSERT INTO allowance (allowance_type, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $allowanceId = $this->db->lastInsertId();
      } catch (PDOException $pe) {
        $msg = $pe->getMessage();
        if ((strpos($msg, '1364') !== false || stripos($msg, 'created_by') !== false || stripos($msg, "doesn't have a default value") !== false)) {
          if (empty($userId))
            throw new Exception('Missing session user id');
          $stmt = $this->db->prepare("INSERT INTO allowance (allowance_type, description, created_by, updated_by) VALUES (?, ?, ?, ?)");
          $stmt->execute([$name, $description, $userId, $userId]);
          $allowanceId = $this->db->lastInsertId();
        } else
          throw $pe;
      }

      // optional timestamp updates
      try {
        if (!empty($userId)) {
          $now = date('Y-m-d H:i:s');
          $this->db->prepare("UPDATE allowance SET updated_at = ? WHERE allowance_id = ?")->execute([$now, $allowanceId]);
          $this->db->prepare("UPDATE allowance SET created_at = ? WHERE allowance_id = ?")->execute([$now, $allowanceId]);
        }
      } catch (Exception $ignore) {
      }

      // assign to employees if requested (mirror deduction logic)
      $applyTo = $data['apply_to'] ?? 'all';
      $department_id = !empty($data['department_id']) ? intval($data['department_id']) : null;
      $position_id = !empty($data['position_id']) ? intval($data['position_id']) : null;
      // employee_id may be scalar or array (employee_id[])
      $employee_id = null;
      if (isset($data['employee_id'])) {
        if (is_array($data['employee_id']))
          $employee_id = array_map('intval', $data['employee_id']);
        else
          $employee_id = intval($data['employee_id']);
      }

      $employeeIds = [];
      if ($applyTo === 'all') {
        $q = "SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)";
        $rows = $this->db->query($q)->fetchAll();
        foreach ($rows as $r)
          $employeeIds[] = $r['employee_id'];
      } elseif ($applyTo === 'department') {
        if (!$department_id)
          throw new Exception('Department is required');
        $rows = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.department_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
        $rows->execute([$department_id]);
        foreach ($rows->fetchAll() as $r)
          $employeeIds[] = $r['employee_id'];
      } elseif ($applyTo === 'position') {
        if (!$position_id)
          throw new Exception('Position is required');
        $rows = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.position_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
        $rows->execute([$position_id]);
        foreach ($rows->fetchAll() as $r)
          $employeeIds[] = $r['employee_id'];
      } else {
        // support multiple employee ids passed directly
        if (is_array($employee_id) && count($employee_id) > 0) {
          $placeholders = implode(',', array_fill(0, count($employee_id), '?'));
          $sql = "SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employee_id IN ($placeholders) AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)";
          $stmt = $this->db->prepare($sql);
          $stmt->execute($employee_id);
          foreach ($stmt->fetchAll() as $r)
            $employeeIds[] = $r['employee_id'];
        } else {
          if (!$employee_id)
            throw new Exception('Employee is required');
          $v = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employee_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
          $v->execute([$employee_id]);
          $found = $v->fetch();
          if ($found)
            $employeeIds[] = $found['employee_id'];
        }
      }

      // Insert employee_allowance rows
      $inserted = 0;
      $checkStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM employee_allowance WHERE employee_id = ? AND allowance_id = ?");
      $insStmt = $this->db->prepare("INSERT INTO employee_allowance (employee_id, allowance_id, allowance_amount) VALUES (?, ?, ?)");
      $amount = isset($data['amount']) ? floatval($data['amount']) : null;
      foreach ($employeeIds as $eid) {
        $checkStmt->execute([$eid, $allowanceId]);
        $c = $checkStmt->fetchColumn();
        if ($c == 0) {
          $insStmt->execute([$eid, $allowanceId, $amount]);
          $inserted++;
        }
      }

      $this->db->commit();
      try {
        if (!empty($userId))
          $this->logger->logOrganizationalAction($userId, 'CREATED', 'Allowance', $name, "Created allowance id {$allowanceId}");
      } catch (Exception $e) {
      }
      return ['success' => true, 'allowance_id' => $allowanceId, 'inserted' => $inserted];
    } catch (Exception $e) {
      if ($this->db && $this->db->inTransaction())
        $this->db->rollBack();
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function updateAllowance($data, $userId)
  {
    try {
      $id = intval($data['allowance_id'] ?? 0);
      $name = trim($data['name'] ?? '');
      if ($id <= 0 || $name === '')
        throw new Exception('Invalid input');
      $this->db->beginTransaction();
      $description = isset($data['description']) ? trim($data['description']) : null;
      $this->db->prepare("UPDATE allowance SET allowance_type = ?, description = ? WHERE allowance_id = ?")->execute([$name, $description, $id]);
      try {
        if (!empty($userId))
          $this->db->prepare("UPDATE allowance SET updated_by = ?, updated_at = ? WHERE allowance_id = ?")->execute([$userId, date('Y-m-d H:i:s'), $id]);
      } catch (Exception $ignore) {
      }
      // Optionally handle apply_to assignments (if provided)
      $applyTo = $data['apply_to'] ?? '';
      if (!empty($applyTo)) {
        $department_id = !empty($data['department_id']) ? intval($data['department_id']) : null;
        $position_id = !empty($data['position_id']) ? intval($data['position_id']) : null;
        $employee_id = null;
        if (isset($data['employee_id'])) {
          if (is_array($data['employee_id']))
            $employee_id = array_map('intval', $data['employee_id']);
          else
            $employee_id = intval($data['employee_id']);
        }

        $employeeIds = [];
        if ($applyTo === 'all') {
          $q = "SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)";
          $rows = $this->db->query($q)->fetchAll();
          foreach ($rows as $r)
            $employeeIds[] = $r['employee_id'];
        } elseif ($applyTo === 'department') {
          if (!$department_id)
            throw new Exception('Department is required for reassignment');
          $rows = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.department_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
          $rows->execute([$department_id]);
          foreach ($rows->fetchAll() as $r)
            $employeeIds[] = $r['employee_id'];
        } elseif ($applyTo === 'position') {
          if (!$position_id)
            throw new Exception('Position is required for reassignment');
          $rows = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.position_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
          $rows->execute([$position_id]);
          foreach ($rows->fetchAll() as $r)
            $employeeIds[] = $r['employee_id'];
        } else {
          if (is_array($employee_id) && count($employee_id) > 0) {
            $placeholders = implode(',', array_fill(0, count($employee_id), '?'));
            $sql = "SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employee_id IN ($placeholders) AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($employee_id);
            foreach ($stmt->fetchAll() as $r)
              $employeeIds[] = $r['employee_id'];
          } else {
            if (!$employee_id)
              throw new Exception('Employee is required for reassignment');
            $v = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employee_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
            $v->execute([$employee_id]);
            $found = $v->fetch();
            if ($found)
              $employeeIds[] = $found['employee_id'];
          }
        }

        // Insert employee_allowance rows for missing assignments
        if (!empty($employeeIds)) {
          $checkStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM employee_allowance WHERE employee_id = ? AND allowance_id = ?");
          $insStmt = $this->db->prepare("INSERT INTO employee_allowance (employee_id, allowance_id, allowance_amount) VALUES (?, ?, ?)");
          $amount = isset($data['amount']) ? floatval($data['amount']) : null;
          $inserted = 0;
          foreach ($employeeIds as $eid) {
            $checkStmt->execute([$eid, $id]);
            $c = $checkStmt->fetchColumn();
            if ($c == 0) {
              $insStmt->execute([$eid, $id, $amount]);
              $inserted++;
            }
          }
        }
      }

      $this->db->commit();
      try {
        if (!empty($userId))
          $this->logger->logOrganizationalAction($userId, 'UPDATED', 'Allowance', $name, "Updated allowance id {$id}");
      } catch (Exception $e) {
      }
      return ['success' => true, 'message' => 'Allowance updated'];
    } catch (Exception $e) {
      if ($this->db && $this->db->inTransaction())
        $this->db->rollBack();
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function deleteAllowance($id, $userId)
  {
    try {
      $this->db->beginTransaction();
      try {
        $stmt = $this->db->prepare("UPDATE allowance SET is_active = 0 WHERE allowance_id = ?");
        $stmt->execute([$id]);
      } catch (Exception $e) {
        $stmt = $this->db->prepare("DELETE FROM allowance WHERE allowance_id = ?");
        $stmt->execute([$id]);
      }
      $this->db->commit();
      try {
        if (!empty($userId))
          $this->logger->logOrganizationalAction($userId, 'DELETED', 'Allowance', "ID: {$id}");
      } catch (Exception $e) {
      }
      return ['success' => true, 'message' => 'Deleted'];
    } catch (Exception $e) {
      if ($this->db && $this->db->inTransaction())
        $this->db->rollBack();
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function deleteEmployeeAssignment($employeeAllowanceId, $userId = null)
  {
    $id = intval($employeeAllowanceId);
    if ($id <= 0)
      return ['success' => false, 'message' => 'Invalid assignment id'];

    try {
      $allowanceName = null;
      $employeeName = null;
      $employeeIdForLog = null;
      try {
        $infoSql = "SELECT a.allowance_type AS allowance_name, ea.employee_id, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS employee_name
                    FROM employee_allowance ea
                    LEFT JOIN allowance a ON ea.allowance_id = a.allowance_id
                    LEFT JOIN employees e ON ea.employee_id = e.employee_id
                    WHERE ea.employee_allowance_id = ? LIMIT 1";
        $infoStmt = $this->db->prepare($infoSql);
        $infoStmt->execute([$id]);
        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
        if ($info) {
          $allowanceName = $info['allowance_name'] ?? null;
          $employeeName = $info['employee_name'] ?? null;
          $employeeIdForLog = isset($info['employee_id']) ? intval($info['employee_id']) : null;
        }
      } catch (Exception $e) {
      }

      // try soft delete
      try {
        $u = $this->db->prepare("UPDATE employee_allowance SET is_active = 0 WHERE employee_allowance_id = ?");
        $u->execute([$id]);
        if ($u->rowCount() > 0) {
          $msg = 'Assignment removed';
          if ($allowanceName || $employeeName) {
            $msg = 'The allowance ' . ($allowanceName ? '"' . $allowanceName . '"' : 'has') . ' has been removed from ' . ($employeeName ? $employeeName : 'the employee');
          }
          try {
            if (!empty($userId)) {
              if (!empty($employeeIdForLog))
                $this->logger->logEmployeeAction($userId, 'DELETED', $employeeIdForLog, $msg);
              else
                $this->logger->logOrganizationalAction($userId, 'DELETED', 'Employee Allowance', "ID: {$id}", $msg);
            }
          } catch (Exception $e) {
          }
          return ['success' => true, 'message' => $msg, 'allowance_name' => $allowanceName, 'employee_name' => $employeeName];
        }
      } catch (PDOException $e) {
      }

      // hard delete fallback
      $d = $this->db->prepare("DELETE FROM employee_allowance WHERE employee_allowance_id = ?");
      $d->execute([$id]);
      if ($d->rowCount() > 0) {
        $msg = 'Assignment removed';
        if ($allowanceName || $employeeName) {
          $msg = 'The allowance ' . ($allowanceName ? '"' . $allowanceName . '"' : 'has') . ' has been removed from ' . ($employeeName ? $employeeName : 'the employee');
        }
        try {
          if (!empty($userId))
            $this->logger->logOrganizationalAction($userId, 'DELETED', 'Employee Allowance', "ID: {$id}", $msg);
        } catch (Exception $e) {
        }
        return ['success' => true, 'message' => $msg, 'allowance_name' => $allowanceName, 'employee_name' => $employeeName];
      }

      return ['success' => false, 'message' => 'Assignment not found or already removed'];
    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }
  }

  public function updateEmployeeAssignment($data, $userId = null)
  {
    // Expect: employee_allowance_id (required), amount (optional)
    $id = intval($data['employee_allowance_id'] ?? 0);
    if ($id <= 0)
      return ['success' => false, 'message' => 'Invalid assignment id'];

    try {
      // Build update dynamically: only update allowance_amount when 'amount' key exists
      $params = [];
      $sets = [];
      if (array_key_exists('amount', $data) && $data['amount'] !== '') {
        $sets[] = 'allowance_amount = ?';
        $params[] = floatval($data['amount']);
      }

      if (!empty($sets)) {
        $params[] = $id;
        $sql = "UPDATE employee_allowance SET " . implode(', ', $sets) . " WHERE employee_allowance_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
      }

      // Fetch the updated assignment with employee and allowance info
      $infoSql = "SELECT ea.employee_allowance_id, ea.allowance_id, ea.allowance_amount as amount, e.employee_id, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS name, a.allowance_type AS allowance_name
                  FROM employee_allowance ea
                  LEFT JOIN employees e ON ea.employee_id = e.employee_id
                  LEFT JOIN allowance a ON ea.allowance_id = a.allowance_id
                  WHERE ea.employee_allowance_id = ? LIMIT 1";
      $infoStmt = $this->db->prepare($infoSql);
      $infoStmt->execute([$id]);
      $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

      try {
        if (!empty($userId)) {
          $msg = 'Updated allowance assignment';
          if ($info) {
            $ename = $info['name'] ?? null;
            $aname = $info['allowance_name'] ?? null;
            $msg = 'Updated allowance ' . ($aname ? '"' . $aname . '"' : '') . ' for ' . ($ename ? $ename : 'employee');
          }
          // prefer employee-specific log when possible
          $empIdForLog = isset($info['employee_id']) ? intval($info['employee_id']) : null;
          if (!empty($empIdForLog))
            $this->logger->logEmployeeAction($userId, 'UPDATED', $empIdForLog, $msg);
          else
            $this->logger->logOrganizationalAction($userId, 'UPDATED', 'Employee Allowance', "ID: {$id}", $msg);
        }
      } catch (Exception $e) {
      }

      return ['success' => true, 'assignment' => $info];
    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }
  }

  public function getAllowanceEmployees($allowanceId)
  {
    $aid = intval($allowanceId);
    if ($aid <= 0)
      return ['success' => false, 'message' => 'Allowance ID required', 'employees' => []];
    try {
      $alInfoStmt = $this->db->prepare("SELECT allowance_id, allowance_type FROM allowance WHERE allowance_id = ?");
      $alInfoStmt->execute([$aid]);
      $alInfo = $alInfoStmt->fetch(PDO::FETCH_ASSOC);

      $sql = "SELECT ea.employee_allowance_id, e.employee_id, e.user_id, e.department_id, e.position_id, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS name, e.email, ea.allowance_amount as amount
              FROM employee_allowance ea
              JOIN employees e ON ea.employee_id = e.employee_id
              LEFT JOIN users u ON e.user_id = u.user_id
              WHERE ea.allowance_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)
              ORDER BY e.last_name, e.first_name";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([$aid]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $resp = ['success' => true, 'employees' => $rows];
      if ($alInfo) {
        $resp['allowance_id'] = $alInfo['allowance_id'];
        $resp['allowance_type_name'] = $alInfo['allowance_type'];
        foreach ($resp['employees'] as &$rr) {
          if (!isset($rr['employee_allowance_id']))
            $rr['employee_allowance_id'] = null;
          $rr['allowance_type_name'] = $alInfo['allowance_type'];
        }
        unset($rr);
      }
      return $resp;
    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'employees' => []];
    }
  }
}

?>

