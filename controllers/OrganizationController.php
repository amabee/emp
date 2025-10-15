<?php

class OrganizationController
{
  private $db;

  public function __construct()
  {
    $this->db = getDBConnection();
    if (!$this->db) {
      throw new Exception("Database connection failed");
    }
  }

  // DEPARTMENT METHODS
  public function getAllDepartments()
  {
    try {
      $stmt = $this->db->prepare("
                SELECT 
                    d.department_id as id,
                    d.department_name as name,
                    NULL as department_head_id,
                    'Active' as status,
                    NULL as head_name,
                    COUNT(emp.employee_id) as employee_count
                FROM department d
                LEFT JOIN employees emp ON d.department_id = emp.department_id AND emp.employment_status = 1
                GROUP BY d.department_id, d.department_name
                ORDER BY d.department_name
            ");
      $stmt->execute();
      $departments = $stmt->fetchAll();

      return $departments;

    } catch (Exception $e) {
      throw new Exception('Failed to get departments: ' . $e->getMessage());
    }
  }

  public function addDepartment($data)
  {
    try {
      $this->db->beginTransaction();

      $stmt = $this->db->prepare("
                INSERT INTO department (department_name) 
                VALUES (?)
            ");

      $result = $stmt->execute([
        $data['department_name']
      ]);

      if ($result) {
        $departmentId = $this->db->lastInsertId();
        $this->db->commit();

        return [
          'success' => true,
          'message' => 'Department added successfully',
          'department_id' => $departmentId
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to add department'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function updateDepartment($id, $data)
  {
    try {
      $this->db->beginTransaction();

      $stmt = $this->db->prepare("
                UPDATE department 
                SET department_name = ?
                WHERE department_id = ?
            ");

      $result = $stmt->execute([
        $data['department_name'],
        $id
      ]);

      if ($result) {
        $this->db->commit();
        return [
          'success' => true,
          'message' => 'Department updated successfully'
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to update department'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function deleteDepartment($id)
  {
    try {
      $this->db->beginTransaction();

      // Check if department has employees
      $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employees WHERE department_id = ? AND employment_status = 1");
      $stmt->execute([$id]);
      $employeeCount = $stmt->fetch()['count'];

      if ($employeeCount > 0) {
        return [
          'success' => false,
          'message' => "Cannot delete department. It has {$employeeCount} active employees."
        ];
      }

      // Hard delete the department since there's no active_status column
      $stmt = $this->db->prepare("DELETE FROM department WHERE department_id = ?");
      $result = $stmt->execute([$id]);

      if ($result) {
        $this->db->commit();
        return [
          'success' => true,
          'message' => 'Department deleted successfully'
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to delete department'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function getDepartment($id)
  {
    try {
      $stmt = $this->db->prepare("
                SELECT 
                    department_id as id,
                    department_name as name
                FROM department 
                WHERE department_id = ?
            ");
      $stmt->execute([$id]);
      return $stmt->fetch();

    } catch (Exception $e) {
      throw new Exception('Failed to get department: ' . $e->getMessage());
    }
  }

  // POSITION METHODS
  public function getAllPositions()
  {
    try {
      $stmt = $this->db->prepare("
                SELECT 
                    jp.position_id as id,
                    jp.position_name as title,
                    NULL as department_id,
                    NULL as department_name,
                    'Active' as status,
                    COUNT(e.employee_id) as employee_count
                FROM job_position jp
                LEFT JOIN employees e ON jp.position_id = e.position_id AND e.employment_status = 1
                GROUP BY jp.position_id, jp.position_name
                ORDER BY jp.position_name
            ");
      $stmt->execute();
      $positions = $stmt->fetchAll();

      return $positions;

    } catch (Exception $e) {
      throw new Exception('Failed to get positions: ' . $e->getMessage());
    }
  }

  public function addPosition($data)
  {
    try {
      $this->db->beginTransaction();

      $stmt = $this->db->prepare("
                INSERT INTO job_position (position_name) 
                VALUES (?)
            ");

      $result = $stmt->execute([
        $data['position_name']
      ]);

      if ($result) {
        $positionId = $this->db->lastInsertId();
        $this->db->commit();

        return [
          'success' => true,
          'message' => 'Position added successfully',
          'position_id' => $positionId
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to add position'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function updatePosition($id, $data)
  {
    try {
      $this->db->beginTransaction();

      $stmt = $this->db->prepare("
                UPDATE job_position 
                SET position_name = ?
                WHERE position_id = ?
            ");

      $result = $stmt->execute([
        $data['position_name'],
        $id
      ]);

      if ($result) {
        $this->db->commit();
        return [
          'success' => true,
          'message' => 'Position updated successfully'
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to update position'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function deletePosition($id)
  {
    try {
      $this->db->beginTransaction();

      // Check if position has employees
      $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employees WHERE position_id = ? AND employment_status = 1");
      $stmt->execute([$id]);
      $employeeCount = $stmt->fetch()['count'];

      if ($employeeCount > 0) {
        return [
          'success' => false,
          'message' => "Cannot delete position. It has {$employeeCount} active employees."
        ];
      }

      // Hard delete the position since there's no active_status column
      $stmt = $this->db->prepare("DELETE FROM job_position WHERE position_id = ?");
      $result = $stmt->execute([$id]);

      if ($result) {
        $this->db->commit();
        return [
          'success' => true,
          'message' => 'Position deleted successfully'
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to delete position'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function getPosition($id)
  {
    try {
      $stmt = $this->db->prepare("
                SELECT 
                    position_id as id,
                    position_name as title
                FROM job_position 
                WHERE position_id = ?
            ");
      $stmt->execute([$id]);
      return $stmt->fetch();

    } catch (Exception $e) {
      throw new Exception('Failed to get position: ' . $e->getMessage());
    }
  }

  // HELPER METHODS
  public function getEmployeesForDepartmentHead()
  {
    try {
      $stmt = $this->db->prepare("
                SELECT 
                    e.employee_id as id,
                    CONCAT(e.first_name, ' ', e.last_name) as name
                FROM employees e
                WHERE e.employment_status = 1
                ORDER BY e.first_name, e.last_name
            ");
      $stmt->execute();
      return $stmt->fetchAll();

    } catch (Exception $e) {
      throw new Exception('Failed to get employees: ' . $e->getMessage());
    }
  }

  public function getAvailableBranchManagers($excludeBranchId = null)
  {
    try {
      // Get employees who are NOT already branch managers (except for the current branch being edited)
      $sql = "
                SELECT 
                    e.employee_id as id,
                    CONCAT(e.first_name, ' ', e.last_name) as name,
                    e.first_name,
                    e.last_name,
                    d.department_name,
                    p.position_name
                FROM employees e
                LEFT JOIN department d ON e.department_id = d.department_id
                LEFT JOIN job_position p ON e.position_id = p.position_id
                LEFT JOIN users u ON e.user_id = u.user_id
                WHERE e.employment_status = 1
                AND u.user_id != 1
                AND (
                    e.employee_id NOT IN (
                        SELECT manager_id 
                        FROM branches 
                        WHERE manager_id IS NOT NULL
                        " . ($excludeBranchId ? "AND branch_id != ?" : "") . "
                    )
                    OR e.employee_id IS NULL
                )
                ORDER BY e.first_name, e.last_name
            ";
      
      $stmt = $this->db->prepare($sql);
      
      if ($excludeBranchId) {
        $stmt->execute([$excludeBranchId]);
      } else {
        $stmt->execute();
      }
      
      return $stmt->fetchAll();

    } catch (Exception $e) {
      throw new Exception('Failed to get available branch managers: ' . $e->getMessage());
    }
  }

  // BRANCH METHODS
  public function getAllBranches()
  {
    try {
      $stmt = $this->db->prepare("
                SELECT 
                    b.branch_id as id,
                    b.branch_name as name,
                    b.branch_code as code,
                    b.address,
                    b.contact_number,
                    b.email,
                    b.manager_id,
                    b.is_active,
                    CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as manager_name,
                    COUNT(emp.employee_id) as employee_count,
                    b.created_at,
                    b.updated_at
                FROM branches b
                LEFT JOIN employees e ON b.manager_id = e.employee_id
                LEFT JOIN employees emp ON b.branch_id = emp.branch_id AND emp.employment_status = 1
                GROUP BY b.branch_id, b.branch_name, b.branch_code, b.address, b.contact_number, 
                         b.email, b.manager_id, b.is_active, e.first_name, e.last_name, 
                         b.created_at, b.updated_at
                ORDER BY b.branch_name
            ");
      $stmt->execute();
      return $stmt->fetchAll();

    } catch (Exception $e) {
      throw new Exception('Failed to get branches: ' . $e->getMessage());
    }
  }

  public function getBranch($id)
  {
    try {
      $stmt = $this->db->prepare("
                SELECT 
                    b.branch_id as id,
                    b.branch_name as name,
                    b.branch_code as code,
                    b.address,
                    b.contact_number,
                    b.email,
                    b.manager_id,
                    b.is_active,
                    CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as manager_name,
                    COUNT(emp.employee_id) as employee_count
                FROM branches b
                LEFT JOIN employees e ON b.manager_id = e.employee_id
                LEFT JOIN employees emp ON b.branch_id = emp.branch_id AND emp.employment_status = 1
                WHERE b.branch_id = ?
                GROUP BY b.branch_id, b.branch_name, b.branch_code, b.address, b.contact_number,
                         b.email, b.manager_id, b.is_active, e.first_name, e.last_name
            ");
      $stmt->execute([$id]);
      return $stmt->fetch();

    } catch (Exception $e) {
      throw new Exception('Failed to get branch: ' . $e->getMessage());
    }
  }

  public function addBranch($data)
  {
    try {
      $this->db->beginTransaction();

      // Check if branch code already exists
      if (!empty($data['branch_code'])) {
        $stmt = $this->db->prepare("SELECT branch_id FROM branches WHERE branch_code = ?");
        $stmt->execute([$data['branch_code']]);
        if ($stmt->fetch()) {
          $this->db->rollback();
          return [
            'success' => false,
            'message' => 'Branch code already exists'
          ];
        }
      }

      $stmt = $this->db->prepare("
                INSERT INTO branches (branch_name, branch_code, address, contact_number, email, manager_id, is_active, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

      $result = $stmt->execute([
        $data['branch_name'],
        $data['branch_code'] ?? null,
        $data['address'] ?? null,
        $data['contact_number'] ?? null,
        $data['email'] ?? null,
        $data['manager_id'] ?? null,
        $data['is_active'] ?? 1,
        $data['created_by'] ?? null
      ]);

      if ($result) {
        $branchId = $this->db->lastInsertId();
        $this->db->commit();

        return [
          'success' => true,
          'message' => 'Branch added successfully',
          'branch_id' => $branchId
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to add branch'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function updateBranch($id, $data)
  {
    try {
      $this->db->beginTransaction();

      // Check if branch code already exists (excluding current branch)
      if (!empty($data['branch_code'])) {
        $stmt = $this->db->prepare("SELECT branch_id FROM branches WHERE branch_code = ? AND branch_id != ?");
        $stmt->execute([$data['branch_code'], $id]);
        if ($stmt->fetch()) {
          $this->db->rollback();
          return [
            'success' => false,
            'message' => 'Branch code already exists'
          ];
        }
      }

      $stmt = $this->db->prepare("
                UPDATE branches 
                SET branch_name = ?, 
                    branch_code = ?, 
                    address = ?, 
                    contact_number = ?, 
                    email = ?, 
                    manager_id = ?, 
                    is_active = ?,
                    updated_by = ?
                WHERE branch_id = ?
            ");

      $result = $stmt->execute([
        $data['branch_name'],
        $data['branch_code'] ?? null,
        $data['address'] ?? null,
        $data['contact_number'] ?? null,
        $data['email'] ?? null,
        $data['manager_id'] ?? null,
        $data['is_active'] ?? 1,
        $data['updated_by'] ?? null,
        $id
      ]);

      if ($result) {
        $this->db->commit();
        return [
          'success' => true,
          'message' => 'Branch updated successfully'
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to update branch'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  public function deleteBranch($id)
  {
    try {
      $this->db->beginTransaction();

      // Check if branch has employees
      $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employees WHERE branch_id = ? AND employment_status = 1");
      $stmt->execute([$id]);
      $result = $stmt->fetch();

      if ($result['count'] > 0) {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Cannot delete branch with active employees. Please reassign employees first.'
        ];
      }

      // Delete branch
      $stmt = $this->db->prepare("DELETE FROM branches WHERE branch_id = ?");
      $result = $stmt->execute([$id]);

      if ($result) {
        $this->db->commit();
        return [
          'success' => true,
          'message' => 'Branch deleted successfully'
        ];
      } else {
        $this->db->rollback();
        return [
          'success' => false,
          'message' => 'Failed to delete branch'
        ];
      }

    } catch (Exception $e) {
      $this->db->rollback();
      return [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }
}
?>

