<?php
require_once __DIR__ . '/EmployeeManagementController.php';
require_once __DIR__ . '/SystemLogger.php';

class DeductionController
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

  /**
   * Update a single employee_deduction assignment. Payload: ['employee_deduction_id'=>int, 'amount'=>float?]
   * If amount is omitted, keep existing amount unchanged.
   */
  public function updateEmployeeAssignment($data, $userId = null)
  {
    $id = isset($data['employee_deduction_id']) ? intval($data['employee_deduction_id']) : 0;
    if ($id <= 0)
      return ['success' => false, 'message' => 'Invalid assignment id'];
    try {
      // only update amount if provided in payload
      if (array_key_exists('amount', $data)) {
        $amount = $data['amount'] === null || $data['amount'] === '' ? null : floatval($data['amount']);
        // perform update
        $stmt = $this->db->prepare("UPDATE employee_deduction SET amount = ? WHERE employee_deduction_id = ?");
        $stmt->execute([$amount, $id]);
      }

      // try to fetch for response
      $q = "SELECT ed.employee_deduction_id, ed.employee_id, ed.amount, ed.deduction_type_id, dt.amount_type AS deduction_amount_type, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS employee_name FROM employee_deduction ed JOIN employees e ON ed.employee_id = e.employee_id LEFT JOIN deduction_type dt ON ed.deduction_type_id = dt.deduction_type_id WHERE ed.employee_deduction_id = ? LIMIT 1";
      $stmt2 = $this->db->prepare($q);
      $stmt2->execute([$id]);
      $row = $stmt2->fetch(PDO::FETCH_ASSOC);
      // provide a friendly label for the deduction amount type if present
      if ($row && isset($row['deduction_amount_type'])) {
        $raw = strtoupper(trim($row['deduction_amount_type'] ?? ''));
        $label = null;
        if ($raw === 'PERCENTAGE' || strpos($raw, 'PERCENT') !== false || strpos($raw, '%') !== false)
          $label = 'Percentage';
        elseif ($raw === 'FIXED')
          $label = 'Fixed';
        $row['deduction_amount_type_label'] = $label;
      }
      // log
      try {
        if (!empty($userId)) {
          $this->logger->logEmployeeAction($userId, 'UPDATED', isset($row['employee_id']) ? intval($row['employee_id']) : null, 'Updated assignment id ' . $id);
        }
      } catch (Exception $e) {
      }
      return ['success' => true, 'assignment' => $row];
    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }
  }

  public function getDeductionTypes()
  {
    try {
      $em = new EmployeeManagementController();
      return $em->getDeductionTypes();
    } catch (Exception $e) {
      try {
        $sql = "SELECT * FROM deduction_type ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $ex) {
        return [];
      }
    }
  }

  public function addDeduction($data, $userId)
  {
    try {
      $this->db->beginTransaction();

      $name = trim($data['name'] ?? '');
      if ($name === '')
        throw new Exception('Deduction name required');

      $isDynamic = isset($data['is_dynamic']) && ($data['is_dynamic'] == '1' || $data['is_dynamic'] === true);

      // prepare helpers for handling amount_type mapping (available in catch scope too)
      $providedAmountType = isset($data['amount_type']) ? $data['amount_type'] : null;
      $mapToEnum = function ($raw) {
        if ($raw === null)
          return null;
        $r = strtolower(trim($raw));
        if (strpos($r, 'percent') !== false || strpos($r, '%') !== false)
          return 'PERCENTAGE';
        return 'FIXED';
      };
      $dbAmountType = null;

      // Insert minimal required value first
      try {
        $dbAmountType = $mapToEnum($providedAmountType);

        if ($dbAmountType !== null) {
          $stmt = $this->db->prepare("INSERT INTO deduction_type (type_name, amount_type, is_dynamic) VALUES (?, ?, ?)");
          $stmt->execute([$name, $dbAmountType, $isDynamic ? 1 : 0]);
        } else {
          $stmt = $this->db->prepare("INSERT INTO deduction_type (type_name, is_dynamic) VALUES (?, ?)");
          $stmt->execute([$name, $isDynamic ? 1 : 0]);
        }
        $deductionId = $this->db->lastInsertId();
      } catch (PDOException $pe) {
        // Retry with created_by/updated_by if schema requires it
        $msg = $pe->getMessage();
        if ((strpos($msg, '1364') !== false || stripos($msg, 'created_by') !== false || stripos($msg, "doesn't have a default value") !== false)) {
          if (empty($userId))
            throw new Exception('Missing session user id');
          // try to include amount_type when retrying as well
          if (!empty($dbAmountType)) {
            $stmt = $this->db->prepare("INSERT INTO deduction_type (type_name, amount_type, is_dynamic, created_by, updated_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $dbAmountType, $isDynamic ? 1 : 0, $userId, $userId]);
          } else {
            $stmt = $this->db->prepare("INSERT INTO deduction_type (type_name, is_dynamic, created_by, updated_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $isDynamic ? 1 : 0, $userId, $userId]);
          }
          $deductionId = $this->db->lastInsertId();
        } else {
          throw $pe;
        }
      }

      // optional metadata updates: timestamps
      try {
        if (!empty($userId)) {
          $now = date('Y-m-d H:i:s');
          $this->db->prepare("UPDATE deduction_type SET updated_at = ? WHERE deduction_type_id = ?")->execute([$now, $deductionId]);
          $this->db->prepare("UPDATE deduction_type SET created_at = ? WHERE deduction_type_id = ?")->execute([$now, $deductionId]);
        }
      } catch (Exception $ignore) {
      }

      // assign to employees if requested
      $applyTo = $data['apply_to'] ?? 'all';
      $department_id = !empty($data['department_id']) ? intval($data['department_id']) : null;
      $position_id = !empty($data['position_id']) ? intval($data['position_id']) : null;
      // Handle multiple employee IDs (employee_id can be array or single value)
      $employee_ids = [];
      if (isset($data['employee_id'])) {
        if (is_array($data['employee_id'])) {
          $employee_ids = array_map('intval', $data['employee_id']);
        } else {
          $employee_ids = [intval($data['employee_id'])];
        }
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
        // Handle multiple specific employees
        if (empty($employee_ids))
          throw new Exception('Employee is required');
        foreach ($employee_ids as $emp_id) {
          $v = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employee_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
          $v->execute([$emp_id]);
          $found = $v->fetch();
          if ($found)
            $employeeIds[] = $found['employee_id'];
        }
      }

      // Insert employee_deduction rows (skip if dynamic calculation is enabled)
      $inserted = 0;
      if (!$isDynamic && !empty($employeeIds)) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM employee_deduction WHERE employee_id = ? AND deduction_type_id = ?");
        // ensure we insert amount_type into employee_deduction as well
        try {
          $insStmt = $this->db->prepare("INSERT INTO employee_deduction (employee_id, deduction_type_id, amount, amount_type) VALUES (?, ?, ?, ?)");
        } catch (Exception $e) {
          // fallback if schema missing amount_type column
          $insStmt = $this->db->prepare("INSERT INTO employee_deduction (employee_id, deduction_type_id, amount) VALUES (?, ?, ?)");
        }
        $amount = isset($data['amount']) ? floatval($data['amount']) : 0.0;
        $amount_type = isset($data['amount_type']) ? $mapToEnum($data['amount_type']) : 'FIXED';
        foreach ($employeeIds as $eid) {
          $checkStmt->execute([$eid, $deductionId]);
          $c = $checkStmt->fetchColumn();
          if ($c == 0) {
            // execute according to prepared statement (4 or 3 params)
            try {
              $insStmt->execute([$eid, $deductionId, $amount, $amount_type]);
            } catch (Exception $e) {
              // try without amount_type
              $insStmt = $this->db->prepare("INSERT INTO employee_deduction (employee_id, deduction_type_id, amount) VALUES (?, ?, ?)");
              $insStmt->execute([$eid, $deductionId, $amount]);
            }
            $inserted++;
          }
        }
      }

      $this->db->commit();

      // log
      try {
        if (!empty($userId))
          $this->logger->logOrganizationalAction($userId, 'CREATED', 'Deduction Type', $name, "Created deduction id {$deductionId}");
      } catch (Exception $e) {
      }

      return ['success' => true, 'deduction_type_id' => $deductionId, 'inserted' => $inserted];
    } catch (Exception $e) {
      if ($this->db && $this->db->inTransaction())
        $this->db->rollBack();
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function updateDeduction($data, $userId)
  {
    try {
      $id = intval($data['deduction_type_id'] ?? 0);
      $name = trim($data['name'] ?? '');
      $description = trim($data['description'] ?? '');
      $providedAmountType = isset($data['amount_type']) ? $data['amount_type'] : null;
      $isDynamic = filter_var($data['is_dynamic'] ?? false, FILTER_VALIDATE_BOOLEAN);
      $isStatutory = filter_var($data['is_statutory'] ?? false, FILTER_VALIDATE_BOOLEAN);

      $mapToEnum = function ($raw) {
        if ($raw === null)
          return null;
        $r = strtolower(trim($raw));
        if (strpos($r, 'percent') !== false || strpos($r, '%') !== false)
          return 'PERCENTAGE';
        return 'FIXED';
      };
      $dbAmountType = $mapToEnum($providedAmountType);
      if ($id <= 0 || $name === '')
        throw new Exception('Invalid input');

      $this->db->beginTransaction();
      $this->db->prepare("UPDATE deduction_type SET type_name = ? WHERE deduction_type_id = ?")->execute([$name, $id]);
      try {
        if ($description !== '') {
          $this->db->prepare("UPDATE deduction_type SET description = ? WHERE deduction_type_id = ?")->execute([$description, $id]);
        }

        if (!empty($dbAmountType)) {
          $this->db->prepare("UPDATE deduction_type SET amount_type = ? WHERE deduction_type_id = ?")->execute([$dbAmountType, $id]);
        }

        // update is_dynamic flag
        try {
          $this->db->prepare("UPDATE deduction_type SET is_dynamic = ? WHERE deduction_type_id = ?")
            ->execute([$isDynamic ? 1 : 0, $id]);
        } catch (Exception $ignoreDynamic) {
          // ignore if schema doesn't support is_dynamic
        }

        // update is_statutory flag
        try {
          $this->db->prepare("UPDATE deduction_type SET statutory = ? WHERE deduction_type_id = ?")
            ->execute([$isStatutory ? 1 : 0, $id]);

        } catch (Exception $ignoreStatutory) {
          // ignore if schema doesn't support statutory
        }
        if (!empty($userId))
          $this->db->prepare("UPDATE deduction_type SET updated_by = ?, updated_at = ? WHERE deduction_type_id = ?")->execute([$userId, date('Y-m-d H:i:s'), $id]);
      } catch (Exception $ignore) {
        error_log("Partial update failed: " . $ignore->getMessage());
      }

      // Handle employee assignments if apply_to is provided
      if (isset($data['apply_to']) && !$isDynamic) {
        $applyTo = $data['apply_to'];
        $department_id = !empty($data['department_id']) ? intval($data['department_id']) : null;
        $position_id = !empty($data['position_id']) ? intval($data['position_id']) : null;
        // Handle multiple employee IDs (employee_id can be array or single value)
        $employee_ids = [];
        if (isset($data['employee_id'])) {
          if (is_array($data['employee_id'])) {
            $employee_ids = array_map('intval', $data['employee_id']);
          } else {
            $employee_ids = [intval($data['employee_id'])];
          }
        }
        $amount = isset($data['default_amount']) ? floatval($data['default_amount']) : 0.0;

        // Get employees to assign to based on apply_to type
        $employeeIds = [];
        if ($applyTo === 'all') {
          $q = "SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)";
          $rows = $this->db->query($q)->fetchAll();
          foreach ($rows as $r)
            $employeeIds[] = $r['employee_id'];
        } elseif ($applyTo === 'department') {
          if ($department_id) {
            $rows = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.department_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
            $rows->execute([$department_id]);
            foreach ($rows->fetchAll() as $r)
              $employeeIds[] = $r['employee_id'];
          }
        } elseif ($applyTo === 'position') {
          if ($position_id) {
            $rows = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.position_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
            $rows->execute([$position_id]);
            foreach ($rows->fetchAll() as $r)
              $employeeIds[] = $r['employee_id'];
          }
        } else {
          // Handle multiple specific employees
          if (!empty($employee_ids)) {
            foreach ($employee_ids as $emp_id) {
              $v = $this->db->prepare("SELECT e.employee_id FROM employees e LEFT JOIN users u ON e.user_id = u.user_id WHERE e.employee_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)");
              $v->execute([$emp_id]);
              $found = $v->fetch();
              if ($found)
                $employeeIds[] = $found['employee_id'];
            }
          }
        }

        // Remove existing assignments for this deduction
        $this->db->prepare("DELETE FROM employee_deduction WHERE deduction_type_id = ?")->execute([$id]);

        // Insert new assignments
        if (!empty($employeeIds)) {
          $checkStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM employee_deduction WHERE employee_id = ? AND deduction_type_id = ?");
          try {
            $insStmt = $this->db->prepare("INSERT INTO employee_deduction (employee_id, deduction_type_id, amount, amount_type) VALUES (?, ?, ?, ?)");
          } catch (Exception $e) {
            $insStmt = $this->db->prepare("INSERT INTO employee_deduction (employee_id, deduction_type_id, amount) VALUES (?, ?, ?)");
          }
          
          foreach ($employeeIds as $eid) {
            $checkStmt->execute([$eid, $id]);
            $c = $checkStmt->fetchColumn();
            if ($c == 0) {
              try {
                $insStmt->execute([$eid, $id, $amount, $dbAmountType]);
              } catch (Exception $e) {
                $insStmt = $this->db->prepare("INSERT INTO employee_deduction (employee_id, deduction_type_id, amount) VALUES (?, ?, ?)");
                $insStmt->execute([$eid, $id, $amount]);
              }
            }
          }
        }
      }

      $this->db->commit();
      try {
        if (!empty($userId))
          $this->logger->logOrganizationalAction($userId, 'UPDATED', 'Deduction Type', $name, "Updated deduction id {$id}");
      } catch (Exception $e) {

      }
      return [
        'success' => true,
        'message' => 'Deduction updated',
        'data'
        => ['deduction_type_id' => $id, 
        'type_name' => $name, 
        'description' => $description, 'amount_type' => $dbAmountType, 'is_dynamic' => $isDynamic, 'statutory' => $isStatutory]
      ];
    } catch (Exception $e) {
      if ($this->db && $this->db->inTransaction())
        $this->db->rollBack();
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function deleteDeduction($id, $userId)
  {
    try {
      $this->db->beginTransaction();
      try {
        $stmt = $this->db->prepare("UPDATE deduction_type SET is_active = 0 WHERE deduction_type_id = ?");
        $stmt->execute([$id]);
      } catch (Exception $e) {
        $stmt = $this->db->prepare("DELETE FROM deduction_type WHERE deduction_type_id = ?");
        $stmt->execute([$id]);
      }
      $this->db->commit();
      try {
        if (!empty($userId))
          $this->logger->logOrganizationalAction($userId, 'DELETED', 'Deduction Type', "ID: {$id}");
      } catch (Exception $e) {
      }
      return ['success' => true, 'message' => 'Deleted'];
    } catch (Exception $e) {
      if ($this->db && $this->db->inTransaction())
        $this->db->rollBack();
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function deleteEmployeeAssignment($employeeDeductionId, $userId = null)
  {
    $id = intval($employeeDeductionId);
    if ($id <= 0)
      return ['success' => false, 'message' => 'Invalid assignment id'];

    try {
      // best-effort lookup for names
      $deductionName = null;
      $employeeName = null;
      try {
        $infoSql = "SELECT dt.type_name AS deduction_name, ed.employee_id, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS employee_name
                    FROM employee_deduction ed
                    LEFT JOIN deduction_type dt ON ed.deduction_type_id = dt.deduction_type_id
                    LEFT JOIN employees e ON ed.employee_id = e.employee_id
                    WHERE ed.employee_deduction_id = ? LIMIT 1";
        $infoStmt = $this->db->prepare($infoSql);
        $infoStmt->execute([$id]);
        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
        if ($info) {
          $deductionName = $info['deduction_name'] ?? null;
          $employeeName = $info['employee_name'] ?? null;
          $employeeIdForLog = isset($info['employee_id']) ? intval($info['employee_id']) : null;
        }
      } catch (Exception $e) {
        // ignore
      }

      // try soft delete
      try {
        $u = $this->db->prepare("UPDATE employee_deduction SET is_active = 0 WHERE employee_deduction_id = ?");
        $u->execute([$id]);
        if ($u->rowCount() > 0) {
          $msg = 'Assignment removed';
          if ($deductionName || $employeeName) {
            $msg = 'The deduction ' . ($deductionName ? '"' . $deductionName . '"' : 'has') . ' has been removed from ' . ($employeeName ? $employeeName : 'the employee');
          }
          // log
          try {
            if (!empty($userId)) {
              if (!empty($employeeIdForLog)) {
                $this->logger->logEmployeeAction($userId, 'DELETED', $employeeIdForLog, $msg);
              } else {
                $this->logger->logOrganizationalAction($userId, 'DELETED', 'Employee Deduction', "ID: {$id}", $msg);
              }
            }
          } catch (Exception $e) {
          }

          return ['success' => true, 'message' => $msg, 'deduction_name' => $deductionName, 'employee_name' => $employeeName];
        }
      } catch (PDOException $e) {
        // continue to hard delete fallback
      }

      // hard delete fallback
      $d = $this->db->prepare("DELETE FROM employee_deduction WHERE employee_deduction_id = ?");
      $d->execute([$id]);
      if ($d->rowCount() > 0) {
        $msg = 'Assignment removed';
        if ($deductionName || $employeeName) {
          $msg = 'The deduction ' . ($deductionName ? '"' . $deductionName . '"' : 'has') . ' has been removed from ' . ($employeeName ? $employeeName : 'the employee');
        }
        try {
          if (!empty($userId))
            $this->logger->logOrganizationalAction($userId, 'DELETED', 'Employee Deduction', "ID: {$id}", $msg);
        } catch (Exception $e) {
        }
        return ['success' => true, 'message' => $msg, 'deduction_name' => $deductionName, 'employee_name' => $employeeName];
      }

      return ['success' => false, 'message' => 'Assignment not found or already removed'];

    } catch (Exception $e) {
      return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }
  }

  public function getDeductionEmployees($deductionTypeId)
  {
    $deductionId = intval($deductionTypeId);
    if ($deductionId <= 0)
      return ['success' => false, 'message' => 'Deduction ID required', 'employees' => []];
    try {
      // fetch deduction info
      $dedInfoStmt = $this->db->prepare("SELECT deduction_type_id, type_name, amount_type FROM deduction_type WHERE deduction_type_id = ?");
      $dedInfoStmt->execute([$deductionId]);
      $dedInfo = $dedInfoStmt->fetch(PDO::FETCH_ASSOC);

      // try to include assignment amount_type, fallback when not present
      try {
        $sql = "SELECT ed.employee_deduction_id, e.employee_id, e.user_id, e.department_id, e.position_id, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS name, e.email, ed.amount, ed.amount_type
                FROM employee_deduction ed
                JOIN employees e ON ed.employee_id = e.employee_id
                LEFT JOIN users u ON e.user_id = u.user_id
                WHERE ed.deduction_type_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)
                ORDER BY e.last_name, e.first_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deductionId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $inner) {
        $sql = "SELECT ed.employee_deduction_id, e.employee_id, e.user_id, e.department_id, e.position_id, CONCAT(e.first_name, ' ', COALESCE(CONCAT(e.middle_name, ' '), ''), e.last_name) AS name, e.email, ed.amount, NULL as amount_type
                FROM employee_deduction ed
                JOIN employees e ON ed.employee_id = e.employee_id
                LEFT JOIN users u ON e.user_id = u.user_id
                WHERE ed.deduction_type_id = ? AND e.employment_status = 1 AND (u.user_type_id IS NULL OR u.user_type_id != 1)
                ORDER BY e.last_name, e.first_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deductionId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
          if (!array_key_exists('amount_type', $r))
            $r['amount_type'] = null;
        }
        unset($r);
      }

      $resp = ['success' => true, 'employees' => $rows];
      if ($dedInfo) {
        $resp['deduction_type_id'] = $dedInfo['deduction_type_id'];
        $resp['deduction_type_name'] = $dedInfo['type_name'];
        $resp['deduction_type_amount_type'] = $dedInfo['amount_type'];
        foreach ($resp['employees'] as &$rr) {
          if (!isset($rr['employee_deduction_id']))
            $rr['employee_deduction_id'] = null;
          $rr['deduction_type_name'] = $dedInfo['type_name'];
          $rr['deduction_type_amount_type'] = $dedInfo['amount_type'];
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

