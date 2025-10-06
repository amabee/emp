<?php
require_once '../shared/config.php';

class DTRController
{
  private $db;

  public function __construct()
  {
    $this->db = getDBConnection();
  }

  /**
   * Get DTR records for a specific user with date filtering
   */
  public function getUserDTRRecords($userId, $dateRange = 'week')
  {
    try {
      // Get employee_id from user_id
      $employeeId = $this->getEmployeeIdFromUserId($userId);
      if (!$employeeId) {
        return [
          'success' => false,
          'message' => 'Employee record not found for user ID: ' . $userId
        ];
      }

      $dateCondition = $this->getDateCondition($dateRange);

      $sql = "SELECT 
                        a.attendance_id,
                        a.employee_id,
                        a.date,
                        a.time_in,
                        a.time_out,
                        a.status,
                        a.remarks,
                        e.first_name,
                        e.last_name,
                        al.time_in as log_time_in,
                        al.time_out as log_time_out,
                        lr.leave_type,
                        COALESCE(lr.reason, lr.comments, 'No reason provided') as leave_reason,
                        lr.half_day as is_half_day
                    FROM attendance a
                    LEFT JOIN employees e ON a.employee_id = e.employee_id
                    LEFT JOIN attendance_log al ON a.attendance_id = al.attendance_id
                    LEFT JOIN leave_records lr ON a.employee_id = lr.employee_id 
                        AND a.date >= lr.start_date 
                        AND a.date <= lr.end_date 
                        AND lr.status = 'Approved'
                    WHERE a.employee_id = :employee_id 
                    {$dateCondition}
                    ORDER BY a.date DESC, a.time_in DESC";

      $stmt = $this->db->prepare($sql);
      $params = ['employee_id' => $employeeId];

      // Add date parameters based on range
      $this->addDateParams($params, $dateRange);

      $stmt->execute($params);
      $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Process records to calculate work hours
      $processedRecords = [];
      foreach ($records as $record) {
        $processedRecord = $this->processRecord($record);
        $processedRecords[] = $processedRecord;
      }

      return [
        'success' => true,
        'data' => $processedRecords
      ];

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Error fetching DTR records: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Create time in record
   */
  public function timeIn($userId)
  {
    try {
      $this->db->beginTransaction();

      $today = date('Y-m-d');
      $currentTime = date('H:i:s');

      // Get employee_id from user_id (in case they're different)
      $employeeId = $this->getEmployeeIdFromUserId($userId);
      if (!$employeeId) {
        $this->db->rollBack();

        // Debug: Let's see what employees exist
        $debugSql = "SELECT employee_id, first_name, last_name FROM employees LIMIT 5";
        $debugStmt = $this->db->prepare($debugSql);
        $debugStmt->execute();
        $employees = $debugStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
          'success' => false,
          'message' => 'Employee record not found for user ID: ' . $userId . '. Available employees: ' . json_encode($employees)
        ];
      }

      // Check current punch status for today
      $statusCheck = $this->getCurrentPunchStatus($employeeId, $today);

      if (!$statusCheck['can_time_in']) {
        $this->db->rollBack();
        return [
          'success' => false,
          'message' => $statusCheck['message']
        ];
      }

      // Check if employee is on approved leave today
      $leaveStatus = $this->checkLeaveStatus($employeeId, $today);
      if ($leaveStatus['on_leave']) {
        $this->db->rollBack();
        return [
          'success' => false,
          'message' => 'Cannot time in - You are on approved ' . $leaveStatus['leave_type'] . ' leave today. Reason: ' . $leaveStatus['reason']
        ];
      }

      // Validate work day and determine status
      $validation = $this->validateWorkDay($employeeId, $today);

      // Get employee's schedule and calendar info
      $scheduleInfo = $this->getEmployeeScheduleInfo($employeeId, $today);

      // Debug: Let's see what we found
      error_log("Debug - Employee ID: $employeeId, Date: $today");
      error_log("Debug - Schedule Info: " . json_encode($scheduleInfo));

      // Determine status based on validation
      $status = 'present';
      $remarks = 'Time in via DTR system';

      // Debug: Log validation results
      error_log("Debug - Validation results: " . json_encode($validation));

      if ($validation['is_holiday']) {
        $status = 'overtime';
        $remarks .= ' - Holiday work';
        error_log("Debug - Status set to overtime: Holiday work");
      } elseif (!$validation['has_schedule']) {
        // Check if it's a reasonable work time (6 AM - 6 PM) before marking as overtime
        $currentHour = (int) date('H');
        if ($currentHour >= 6 && $currentHour <= 18) {
          $status = 'present'; // Normal work hours, likely just missing schedule setup
          $remarks .= ' - Regular work time (schedule data missing)';
          error_log("Debug - Status kept as present: Regular work hours despite missing schedule");
        } else {
          $status = 'overtime';
          $remarks .= ' - No scheduled work day';
          error_log("Debug - Status set to overtime: No scheduled work day");
        }
      } elseif (!$validation['is_working_day']) {
        $status = 'overtime';
        $remarks .= ' - Non-working day';
        error_log("Debug - Status set to overtime: Non-working day");
      }

      // Check for late status (only if not already marked as overtime)
      if ($status === 'present' && $validation['has_schedule'] && $validation['schedule_data']) {
        $isLate = $this->checkLateStatus($currentTime, $statusCheck['session_type'], $validation['schedule_data']);
        if ($isLate['is_late']) {
          $status = 'late';
          $remarks .= ' - ' . $isLate['reason'];
          error_log("Debug - Status set to late: " . $isLate['reason']);
        }
      }

      // Insert new attendance record for each session (AM or PM)
      $sessionRemarks = $remarks . ' - ' . $statusCheck['session_type'] . ' session - ' . $statusCheck['action_description'];

      $sql = "INSERT INTO attendance (employee_id, schedule_id, calendar_id, date, time_in, status, remarks) 
                    VALUES (:user_id, :schedule_id, :calendar_id, :date, :time_in, :status, :remarks)";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        'user_id' => $employeeId,
        'schedule_id' => $scheduleInfo['schedule_id'],
        'calendar_id' => $scheduleInfo['calendar_id'],
        'date' => $today,
        'time_in' => $currentTime,
        'status' => $status,
        'remarks' => $sessionRemarks
      ]);
      $attendanceId = $this->db->lastInsertId();

      $attendanceId = $this->db->lastInsertId();

      // Insert attendance log
      $logSql = "INSERT INTO attendance_log (attendance_id, time_in) 
                      VALUES (:attendance_id, :time_in)";
      $logStmt = $this->db->prepare($logSql);
      $logStmt->execute([
        'attendance_id' => $attendanceId,
        'time_in' => $currentTime
      ]);

      $this->db->commit();

      // Prepare success message based on status
      $message = 'Time in successful';
      if ($status === 'overtime') {
        if ($validation['is_holiday']) {
          $message .= ' - Working on holiday';
        } elseif (!$validation['has_schedule']) {
          $message .= ' - No scheduled work today (marked as overtime)';
        } elseif (!$validation['is_working_day']) {
          $message .= ' - Non-working day (marked as overtime)';
        }
      }

      return [
        'success' => true,
        'message' => $message,
        'time_in' => $currentTime,
        'attendance_id' => $attendanceId,
        'status' => $status,
        'work_validation' => $validation
      ];

    } catch (Exception $e) {
      $this->db->rollBack();
      return [
        'success' => false,
        'message' => 'Error recording time in: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Create time out record
   */
  public function timeOut($userId)
  {
    try {
      $this->db->beginTransaction();

      $today = date('Y-m-d');
      $currentTime = date('H:i:s');

      // Get employee_id from user_id
      $employeeId = $this->getEmployeeIdFromUserId($userId);
      if (!$employeeId) {
        $this->db->rollBack();
        return [
          'success' => false,
          'message' => 'Employee record not found for user ID: ' . $userId
        ];
      }

      // Check if employee is on approved leave today
      $leaveStatus = $this->checkLeaveStatus($employeeId, $today);
      if ($leaveStatus['on_leave']) {
        $this->db->rollBack();
        return [
          'success' => false,
          'message' => 'Cannot time out - You are on approved ' . $leaveStatus['leave_type'] . ' leave today. Reason: ' . $leaveStatus['reason']
        ];
      }

      // Check current punch status for today
      $statusCheck = $this->getCurrentPunchStatus($employeeId, $today);

      if (!$statusCheck['can_time_out']) {
        $this->db->rollBack();
        return [
          'success' => false,
          'message' => $statusCheck['message']
        ];
      }

      // Get the attendance record to update
      $attendance = $statusCheck['attendance_record'];

      // Check for overtime (late time out)
      $overtimeData = $this->checkOvertimeStatus($employeeId, $currentTime, $statusCheck['session_type'], $today);

      // Debug overtime detection
      error_log("DEBUG - Overtime check for employee $employeeId:");
      error_log("DEBUG - Current time: $currentTime");
      error_log("DEBUG - Session type: " . $statusCheck['session_type']);
      error_log("DEBUG - Overtime data: " . json_encode($overtimeData));

      // Prepare the final remarks
      $finalRemarks = $statusCheck['action_description'];
      $overtimeRecordId = null;

      if ($overtimeData && $overtimeData['has_overtime']) {
        $finalRemarks .= ' - ' . $overtimeData['message'];
        
        error_log("DEBUG - Overtime detected! Attempting to insert record...");
        
        // Insert overtime record
        try {
          $overtimeRecordId = $this->insertOvertimeRecord(
            $employeeId,
            $attendance['attendance_id'],
            $today,
            $overtimeData['overtime_hours'],
            $overtimeData['overtime_minutes'],
            $overtimeData['overtime_type']
          );
          
          error_log("SUCCESS - Overtime record created with ID: $overtimeRecordId for employee $employeeId");
        } catch (Exception $overtimeEx) {
          error_log("ERROR - Failed to create overtime record: " . $overtimeEx->getMessage());
          error_log("ERROR - Stack trace: " . $overtimeEx->getTraceAsString());
          // Continue with time out even if overtime record fails
        }
      } else {
        error_log("DEBUG - No overtime detected or overtime data is null");
      }

      // Update attendance record with time out
      $updateSql = "UPDATE attendance 
                         SET time_out = :time_out, 
                             remarks = CONCAT(COALESCE(remarks, ''), ' - " . $finalRemarks . "')
                         WHERE attendance_id = :attendance_id";
      $updateStmt = $this->db->prepare($updateSql);
      $updateStmt->execute([
        'time_out' => $currentTime,
        'attendance_id' => $attendance['attendance_id']
      ]);

      // Update attendance log
      $logUpdateSql = "UPDATE attendance_log 
                           SET time_out = :time_out 
                           WHERE attendance_id = :attendance_id";
      $logUpdateStmt = $this->db->prepare($logUpdateSql);
      $logUpdateStmt->execute([
        'time_out' => $currentTime,
        'attendance_id' => $attendance['attendance_id']
      ]);

      $this->db->commit();

      // Calculate work hours
      $workHours = $this->calculateWorkHours($attendance['time_in'], $currentTime);

      $response = [
        'success' => true,
        'message' => 'Time out successful',
        'time_out' => $currentTime,
        'work_hours' => $workHours
      ];

      // Add overtime information to response if applicable
      if ($overtimeData && $overtimeData['has_overtime']) {
        $response['overtime'] = [
          'has_overtime' => true,
          'overtime_hours' => $overtimeData['overtime_hours'],
          'overtime_minutes' => $overtimeData['overtime_minutes'],
          'overtime_type' => $overtimeData['overtime_type'],
          'overtime_record_id' => $overtimeRecordId,
          'message' => $overtimeData['message']
        ];
      } else {
        $response['overtime'] = [
          'has_overtime' => false
        ];
      }

      return $response;

    } catch (Exception $e) {
      $this->db->rollBack();
      return [
        'success' => false,
        'message' => 'Error recording time out: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Get current user status for today
   */
  public function getCurrentStatus($userId)
  {
    try {
      $today = date('Y-m-d');

      // Get employee_id from user_id
      $employeeId = $this->getEmployeeIdFromUserId($userId);
      if (!$employeeId) {
        return [
          'success' => false,
          'message' => 'Employee record not found for user ID: ' . $userId
        ];
      }

      $sql = "SELECT attendance_id, time_in, time_out, status 
                   FROM attendance 
                   WHERE employee_id = :employee_id AND date = :date
                   ORDER BY attendance_id DESC LIMIT 1";

      $stmt = $this->db->prepare($sql);
      $stmt->execute(['employee_id' => $employeeId, 'date' => $today]);
      $record = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$record) {
        return [
          'success' => true,
          'status' => 'out',
          'time_in' => null,
          'time_out' => null
        ];
      }

      $status = 'out';
      if ($record['time_in'] && !$record['time_out']) {
        $status = 'in';
      }

      return [
        'success' => true,
        'status' => $status,
        'time_in' => $record['time_in'],
        'time_out' => $record['time_out'],
        'attendance_id' => $record['attendance_id']
      ];

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Error getting current status: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Get today's work summary
   */
  public function getTodaySummary($userId)
  {
    try {
      $today = date('Y-m-d');

      // Get employee_id from user_id
      $employeeId = $this->getEmployeeIdFromUserId($userId);
      if (!$employeeId) {
        return [
          'success' => false,
          'message' => 'Employee record not found for user ID: ' . $userId
        ];
      }

      $sql = "SELECT time_in, time_out FROM attendance 
                   WHERE employee_id = :employee_id AND date = :date
                   ORDER BY attendance_id DESC LIMIT 1";

      $stmt = $this->db->prepare($sql);
      $stmt->execute(['employee_id' => $employeeId, 'date' => $today]);
      $record = $stmt->fetch(PDO::FETCH_ASSOC);

      $summary = [
        'time_in' => $record ? $record['time_in'] : null,
        'time_out' => $record ? $record['time_out'] : null,
        'work_hours' => '0h 0m',
        'status' => 'Not Started'
      ];

      if ($record && $record['time_in']) {
        if ($record['time_out']) {
          $summary['work_hours'] = $this->calculateWorkHours($record['time_in'], $record['time_out']);
          $summary['status'] = 'Complete';
        } else {
          $summary['work_hours'] = $this->calculateWorkHours($record['time_in'], date('H:i:s'));
          $summary['status'] = 'In Progress';
        }
      }

      return [
        'success' => true,
        'data' => $summary
      ];

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Error getting today\'s summary: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Get detailed DTR information for a specific attendance record
   */
  public function getDTRDetails($attendanceId)
  {
    try {
      // Get the main attendance record with employee details
      $sql = "SELECT 
                        a.attendance_id,
                        a.employee_id,
                        a.schedule_id,
                        a.calendar_id,
                        a.date,
                        a.time_in,
                        a.time_out,
                        a.status,
                        a.remarks,
                        e.first_name,
                        e.last_name,
                        e.contact_number,
                        d.department_name,
                        p.position_name,
                        es.shift_in as schedule_time_in,
                        es.shift_out as schedule_time_out
                    FROM attendance a
                    LEFT JOIN employees e ON a.employee_id = e.employee_id
                    LEFT JOIN department d ON e.department_id = d.department_id
                    LEFT JOIN job_position p ON e.position_id = p.position_id
                    LEFT JOIN employee_schedule es ON a.employee_id = es.employee_id 
                        AND es.work_date = a.date
                    WHERE a.attendance_id = :attendance_id
                    LIMIT 1
                    ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute(['attendance_id' => $attendanceId]);
      $record = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$record) {
        return [
          'success' => false,
          'message' => 'DTR record not found'
        ];
      }

      // Get all attendance records for the same date (AM and PM sessions)
      $allSessionsSql = "SELECT attendance_id, time_in, time_out, remarks, status
                              FROM attendance 
                              WHERE employee_id = :employee_id AND date = :date
                              ORDER BY attendance_id ASC";

      $allSessionsStmt = $this->db->prepare($allSessionsSql);
      $allSessionsStmt->execute([
        'employee_id' => $record['employee_id'],
        'date' => $record['date']
      ]);
      $allSessions = $allSessionsStmt->fetchAll(PDO::FETCH_ASSOC);

      // Process the data
      $details = [
        'attendance_id' => $record['attendance_id'],
        'employee' => [
          'id' => $record['employee_id'],
          'name' => trim($record['first_name'] . ' ' . $record['last_name']),
          'contact_number' => $record['contact_number'],
          'department' => $record['department_name'] ?: 'N/A',
          'position' => $record['position_name'] ?: 'N/A'
        ],
        'date_info' => [
          'date' => $record['date'],
          'formatted_date' => date('F j, Y', strtotime($record['date'])),
          'day_name' => date('l', strtotime($record['date']))
        ],
        'schedule_info' => [
          'schedule_time_in' => $record['schedule_time_in'] ? date('h:i A', strtotime($record['schedule_time_in'])) : 'N/A',
          'schedule_time_out' => $record['schedule_time_out'] ? date('h:i A', strtotime($record['schedule_time_out'])) : 'N/A',
          'shift_name' => $this->getShiftName($record['schedule_time_in'], $record['schedule_time_out'])
        ],
        'time_summary' => $this->calculateDaySummary($allSessions),
        'sessions' => $this->formatSessions($allSessions),
        'timeline' => $this->generateTimeline($allSessions)
      ];

      return [
        'success' => true,
        'data' => $details
      ];

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Error fetching DTR details: ' . $e->getMessage()
      ];
    }
  }

  private function getDateCondition($dateRange)
  {
    switch ($dateRange) {
      case 'today':
        return 'AND a.date = CURDATE()';
      case 'week':
        return 'AND a.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
      case 'month':
        return 'AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
      default:
        return 'AND a.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
    }
  }

  private function addDateParams(&$params, $dateRange)
  {
    $today = date('Y-m-d');
    switch ($dateRange) {
      case 'today':
        // No additional parameters needed for CURDATE()
        break;
      case 'week':
        // No additional parameters needed for DATE_SUB with INTERVAL
        break;
      case 'month':
        // No additional parameters needed for DATE_SUB with INTERVAL
        break;
      case 'custom':
        // Add custom date range parameters if needed
        if (isset($_GET['start_date'])) {
          $params['start_date'] = $_GET['start_date'];
        }
        if (isset($_GET['end_date'])) {
          $params['end_date'] = $_GET['end_date'];
        }
        break;
      default:
        // Default to week range - no additional parameters needed
        break;
    }
  }

  private function processRecord($record)
  {
    $processed = $record;

    // Calculate work hours if both time_in and time_out exist
    if ($record['time_in'] && $record['time_out']) {
      $processed['work_hours'] = $this->calculateWorkHours($record['time_in'], $record['time_out']);
      $processed['work_status'] = 'Complete';
    } elseif ($record['time_in'] && !$record['time_out']) {
      $processed['work_hours'] = $this->calculateWorkHours($record['time_in'], date('H:i:s'));
      $processed['work_status'] = 'In Progress';
    } else {
      $processed['work_hours'] = '0h 0m';
      $processed['work_status'] = 'Not Started';
    }

    // Format display names
    $processed['employee_name'] = trim($record['first_name'] . ' ' . $record['last_name']);

    // Format date
    $processed['formatted_date'] = date('M j, Y', strtotime($record['date']));
    $processed['day_name'] = date('l', strtotime($record['date']));

    // Format times
    $processed['formatted_time_in'] = $record['time_in'] ? date('h:i A', strtotime($record['time_in'])) : '--:-- --';
    $processed['formatted_time_out'] = $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : '--:-- --';

    return $processed;
  }

  private function calculateWorkHours($timeIn, $timeOut)
  {
    $start = new DateTime($timeIn);
    $end = new DateTime($timeOut);
    $interval = $start->diff($end);

    $hours = $interval->h + ($interval->days * 24);
    $minutes = $interval->i;

    return $hours . 'h ' . $minutes . 'm';
  }

  /**
   * Get employee_id from user_id (handles cases where they might be different)
   */
  private function getEmployeeIdFromUserId($userId)
  {
    try {
      // First check if user_id exists directly in employees table
      $sql = "SELECT employee_id FROM employees WHERE user_id = :user_id LIMIT 1";
      $stmt = $this->db->prepare($sql);
      $stmt->execute(['user_id' => $userId]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($result) {
        return $result['employee_id'];
      }

      // If not found, check if there's a users table that links to employees
      // This handles cases where user_id and employee_id are different
      try {
        $userSql = "SELECT employee_id FROM users WHERE user_id = :user_id LIMIT 1";
        $userStmt = $this->db->prepare($userSql);
        $userStmt->execute(['user_id' => $userId]);
        $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($userResult && $userResult['employee_id']) {
          return $userResult['employee_id'];
        }
      } catch (Exception $userEx) {
        // Users table might not exist, continue
      }

      // Check if there might be a different column name or structure
      // Let's try to find any employee with a matching ID or username
      try {
        $altSql = "SELECT employee_id FROM employees WHERE employee_id = :user_id OR username = :username LIMIT 1";
        $altStmt = $this->db->prepare($altSql);
        $altStmt->execute(['user_id' => $userId, 'username' => $userId]);
        $altResult = $altStmt->fetch(PDO::FETCH_ASSOC);

        if ($altResult) {
          return $altResult['employee_id'];
        }
      } catch (Exception $altEx) {
        // Column might not exist
      }

      // If still not found, return null to trigger error with debugging info
      return null;

    } catch (Exception $e) {
      // Return null to show debug info
      return null;
    }
  }

  /**
   * Validate if employee should be working on this day and determine work status
   */
  private function validateWorkDay($userId, $date)
  {
    try {
      $dayOfWeek = strtolower(date('l', strtotime($date))); // monday, tuesday, etc.

      // Check if employee has a schedule (simplified approach)
      $scheduleSql = "SELECT schedule_id, shift_in, shift_out 
                           FROM employee_schedule 
                           WHERE employee_id = :user_id
                           AND work_date = :date
                           LIMIT 1";

      $scheduleStmt = $this->db->prepare($scheduleSql);
      $scheduleStmt->execute(['user_id' => $userId, 'date' => $date]);
      $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);


      // Check working calendar for this date
      $calendarSql = "SELECT is_working, is_holiday 
                           FROM working_calendar 
                           WHERE DATE(work_date) = :date 
                           LIMIT 1";

      $calendarStmt = $this->db->prepare($calendarSql);
      $calendarStmt->execute(['date' => $date]);
      $calendar = $calendarStmt->fetch(PDO::FETCH_ASSOC);


      $hasSchedule = $schedule && $schedule['schedule_id'];


      return [
        'has_schedule' => $hasSchedule,
        'is_working_day' => $calendar ? (bool) $calendar['is_working'] : true,
        'is_holiday' => $calendar ? (bool) $calendar['is_holiday'] : false,
        'schedule_data' => $schedule,
        'calendar_data' => $calendar
      ];

    } catch (Exception $e) {
      // Return safe defaults if there's an error
      return [
        'has_schedule' => false,
        'is_working_day' => true,
        'is_holiday' => false,
        'schedule_data' => null,
        'calendar_data' => null
      ];
    }
  }

  /**
   * Get current punch status for 4-punch system using existing time_in/time_out columns
   * Creates 2 records per day: AM session (08:00-12:00) and PM session (13:00-17:00)
   */
  private function getCurrentPunchStatus($employeeId, $date)
  {
    try {
      $sql = "SELECT attendance_id, time_in, time_out, remarks 
                   FROM attendance 
                   WHERE employee_id = :employee_id AND date = :date 
                   ORDER BY attendance_id ASC";

      $stmt = $this->db->prepare($sql);
      $stmt->execute(['employee_id' => $employeeId, 'date' => $date]);
      $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (empty($records)) {
        // No records exist - can do morning time in
        return [
          'can_time_in' => true,
          'can_time_out' => false,
          'needs_new_record' => true,
          'session_type' => 'AM',
          'action_description' => 'Morning time in',
          'message' => 'Ready for morning time in'
        ];
      }

      $amRecord = null;
      $pmRecord = null;

      // Separate AM and PM records based on remarks or time
      foreach ($records as $record) {
        if (
          strpos($record['remarks'], 'AM session') !== false ||
          ($record['time_in'] && strtotime($record['time_in']) < strtotime('13:00:00'))
        ) {
          $amRecord = $record;
        } else {
          $pmRecord = $record;
        }
      }

      // Check if we have a PM record without AM record (half day scenario)
      if (!$amRecord && $pmRecord) {
        // Half day - PM session exists without AM
        if ($pmRecord['time_in'] && !$pmRecord['time_out']) {
          return [
            'can_time_in' => false,
            'can_time_out' => true,
            'needs_new_record' => false,
            'session_type' => 'PM',
            'action_description' => 'Half day - End of day out',
            'attendance_record' => $pmRecord,
            'message' => 'Half day shift - Ready to time out'
          ];
        } else {
          return [
            'can_time_in' => false,
            'can_time_out' => false,
            'needs_new_record' => false,
            'message' => 'Half day shift completed'
          ];
        }
      }

      // Check AM session first
      if (!$amRecord) {
        // No AM record - can do morning time in OR afternoon time in (half day)
        $currentHour = (int) date('H');

        if ($currentHour >= 13) {
          // After 1 PM - offer half day (PM only)
          return [
            'can_time_in' => true,
            'can_time_out' => false,
            'needs_new_record' => true,
            'session_type' => 'PM',
            'action_description' => 'Half day - Afternoon time in',
            'message' => 'Ready for half day (afternoon only)'
          ];
        } else {
          // Before 1 PM - regular morning time in
          return [
            'can_time_in' => true,
            'can_time_out' => false,
            'needs_new_record' => true,
            'session_type' => 'AM',
            'action_description' => 'Morning time in',
            'message' => 'Ready for morning time in'
          ];
        }
      }

      if ($amRecord && $amRecord['time_in'] && !$amRecord['time_out']) {
        // AM in done, can do lunch out
        return [
          'can_time_in' => false,
          'can_time_out' => true,
          'needs_new_record' => false,
          'session_type' => 'AM',
          'action_description' => 'Lunch break out',
          'attendance_record' => $amRecord,
          'message' => 'Ready for lunch break'
        ];
      }

      if ($amRecord && $amRecord['time_in'] && $amRecord['time_out'] && !$pmRecord) {
        // AM session complete, can do afternoon in (full day continues)
        return [
          'can_time_in' => true,
          'can_time_out' => false,
          'needs_new_record' => true,
          'session_type' => 'PM',
          'action_description' => 'Afternoon time in',
          'message' => 'Ready for afternoon time in (full day)'
        ];
      }

      if ($pmRecord && $pmRecord['time_in'] && !$pmRecord['time_out']) {
        // PM in done, can do end of day out
        return [
          'can_time_in' => false,
          'can_time_out' => true,
          'needs_new_record' => false,
          'session_type' => 'PM',
          'action_description' => 'End of day out',
          'attendance_record' => $pmRecord,
          'message' => 'Ready for end of day out'
        ];
      }

      // All punches done for the day
      return [
        'can_time_in' => false,
        'can_time_out' => false,
        'needs_new_record' => false,
        'message' => 'All time punches completed for today'
      ];

    } catch (Exception $e) {
      return [
        'can_time_in' => false,
        'can_time_out' => false,
        'needs_new_record' => false,
        'message' => 'Error checking punch status: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Get employee's schedule and calendar information for a given date
   */
  private function getEmployeeScheduleInfo($userId, $date)
  {
    try {
      // Debug: Check what tables and data exist
      error_log("Debug - Looking for employee_id: $userId on date: $date");

      // Get employee's schedule with calendar_id from employee_schedule table
      $scheduleSql = "SELECT schedule_id, calendar_id FROM employee_schedule 
                           WHERE employee_id = :user_id 
                           ORDER BY schedule_id DESC 
                           LIMIT 1";
      $scheduleStmt = $this->db->prepare($scheduleSql);
      $scheduleStmt->execute(['user_id' => $userId]);
      $scheduleResult = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

      error_log("Debug - Schedule query result: " . json_encode($scheduleResult));

      return [
        'schedule_id' => $scheduleResult ? $scheduleResult['schedule_id'] : null,
        'calendar_id' => $scheduleResult ? $scheduleResult['calendar_id'] : null
      ];

    } catch (Exception $e) {
      error_log("Debug - Error in getEmployeeScheduleInfo: " . $e->getMessage());
      // Return nulls if there's an error
      return [
        'schedule_id' => null,
        'calendar_id' => null
      ];
    }
  }

  /**
   * Get shift name based on schedule times
   */
  private function getShiftName($timeIn, $timeOut)
  {
    if (!$timeIn || !$timeOut) {
      return 'No Schedule';
    }

    $start = date('H:i', strtotime($timeIn));
    $end = date('H:i', strtotime($timeOut));

    // Common Philippine work shifts
    if ($start == '08:00' && $end == '17:00') {
      return 'Regular Day Shift (8AM-5PM)';
    } elseif ($start == '09:00' && $end == '18:00') {
      return 'Regular Day Shift (9AM-6PM)';
    } elseif ($start == '22:00' || $start == '23:00') {
      return 'Night Shift';
    } elseif ($start >= '06:00' && $start <= '08:00') {
      return 'Morning Shift';
    } else {
      return "Custom Shift ({$start}-{$end})";
    }
  }

  /**
   * Calculate day summary from all sessions
   */
  private function calculateDaySummary($sessions)
  {
    $totalMinutes = 0;
    $amSession = null;
    $pmSession = null;
    $status = 'Absent';

    foreach ($sessions as $session) {
      if ($session['time_in']) {
        $timeIn = strtotime($session['time_in']);
        $timeOut = $session['time_out'] ? strtotime($session['time_out']) : time();

        // Determine if AM or PM session
        $hour = date('H', $timeIn);
        if ($hour < 13) { // Before 1 PM
          $amSession = $session;
        } else {
          $pmSession = $session;
        }

        if ($session['time_out']) {
          $totalMinutes += ($timeOut - $timeIn) / 60;
        }
      }
    }

    // Determine status
    if ($amSession && $pmSession) {
      if ($amSession['time_out'] && $pmSession['time_out']) {
        $status = 'Full Day';
      } else {
        $status = 'In Progress';
      }
    } elseif ($amSession || $pmSession) {
      $status = 'Half Day';
    }

    // Calculate hours and overtime
    $hours = floor($totalMinutes / 60);
    $minutes = floor($totalMinutes) % 60;
    $regularHours = 8; // Standard 8-hour workday
    $overtime = max(0, $hours - $regularHours);

    return [
      'total_work_time' => sprintf('%d hours %d minutes', $hours, $minutes),
      'regular_hours' => min($hours, $regularHours),
      'overtime_hours' => $overtime,
      'status' => $status,
      'break_time' => $this->calculateBreakTime($amSession, $pmSession)
    ];
  }

  /**
   * Format sessions for display
   */
  private function formatSessions($sessions)
  {
    $formatted = [];

    foreach ($sessions as $session) {
      if (!$session['time_in'])
        continue;

      $timeIn = strtotime($session['time_in']);
      $hour = date('H', $timeIn);
      $sessionType = $hour < 13 ? 'AM Session' : 'PM Session';

      $formatted[] = [
        'type' => $sessionType,
        'time_in' => date('h:i A', $timeIn),
        'time_out' => $session['time_out'] ? date('h:i A', strtotime($session['time_out'])) : 'Not yet',
        'duration' => $session['time_out'] ?
          $this->calculateWorkHours($session['time_in'], $session['time_out']) :
          'In progress',
        'status' => $session['status'] ?: 'Present',
        'remarks' => $session['remarks'] ?: ''
      ];
    }

    return $formatted;
  }

  /**
   * Generate timeline for the day
   */
  private function generateTimeline($sessions)
  {
    $timeline = [];

    foreach ($sessions as $session) {
      if ($session['time_in']) {
        $timeline[] = [
          'time' => date('h:i A', strtotime($session['time_in'])),
          'event' => 'Time In',
          'type' => 'time_in',
          'session' => date('H', strtotime($session['time_in'])) < 13 ? 'AM' : 'PM'
        ];

        if ($session['time_out']) {
          $timeline[] = [
            'time' => date('h:i A', strtotime($session['time_out'])),
            'event' => 'Time Out',
            'type' => 'time_out',
            'session' => date('H', strtotime($session['time_in'])) < 13 ? 'AM' : 'PM'
          ];
        }
      }
    }

    // Sort timeline by time
    usort($timeline, function ($a, $b) {
      return strtotime($a['time']) - strtotime($b['time']);
    });

    return $timeline;
  }

  /**
   * Calculate break time between AM and PM sessions
   */
  private function calculateBreakTime($amSession, $pmSession)
  {
    if (!$amSession || !$pmSession || !$amSession['time_out'] || !$pmSession['time_in']) {
      return 'N/A';
    }

    $amOut = strtotime($amSession['time_out']);
    $pmIn = strtotime($pmSession['time_in']);

    if ($pmIn <= $amOut) {
      return 'No break';
    }

    $breakMinutes = ($pmIn - $amOut) / 60;
    $hours = floor($breakMinutes / 60);
    $minutes = $breakMinutes % 60;

    if ($hours > 0) {
      return sprintf('%dh %dm', $hours, $minutes);
    } else {
      return sprintf('%dm', $minutes);
    }
  }

  /**
   * Check if employee is late based on session type and schedule
   */
  private function checkLateStatus($currentTime, $sessionType, $scheduleData): array
  {
    try {
      $currentTimeStamp = strtotime($currentTime);
      $currentHour = (int) date('H', $currentTimeStamp);

      if ($sessionType === 'AM') {
        if ($scheduleData && isset($scheduleData['shift_in'])) {

          $scheduledStartTime = strtotime($scheduleData['shift_in']);
          $graceTime = $scheduledStartTime + (5 * 60); // 5 minutes grace period

          if ($currentTimeStamp > $graceTime) {
            $minutesLate = ceil(($currentTimeStamp - $scheduledStartTime) / 60);
            return [
              'is_late' => true,
              'reason' => "Late by {$minutesLate} minutes (Morning shift)"
            ];
          }
        }
      }

      // For PM session: Check if clocking in after 1:00 PM (13:00)
      elseif ($sessionType === 'PM') {
        $lunchEndTime = strtotime('13:00:00'); // 1:00 PM

        if ($currentTimeStamp > $lunchEndTime) {
          $minutesLate = ceil(($currentTimeStamp - $lunchEndTime) / 60);
          return [
            'is_late' => true,
            'reason' => "Late by {$minutesLate} minutes (Afternoon session - expected back at 1:00 PM)"
          ];
        }
      }

      // Not late
      return [
        'is_late' => false,
        'reason' => ''
      ];

    } catch (Exception $e) {
      error_log("Error in checkLateStatus: " . $e->getMessage());
      // Default to not late if there's an error
      return [
        'is_late' => false,
        'reason' => ''
      ];
    }
  }

  /**
   * Check if employee is working overtime (staying beyond scheduled shift end time)
   */
  private function checkOvertimeStatus($employeeId, $currentTime, $sessionType, $date)
  {
    try {
      // Get employee's schedule information
      $scheduleSql = "SELECT shift_out 
                           FROM employee_schedule 
                           WHERE employee_id = :employee_id 
                            AND work_date = :date
                           LIMIT 1";

      $scheduleStmt = $this->db->prepare($scheduleSql);
      $scheduleStmt->execute(['employee_id' => $employeeId, 'date' => $date]);
      $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

      error_log("DEBUG - Schedule query for employee $employeeId on $date: " . json_encode($schedule));

      // Check if it's a day-off (Saturday or Sunday)
      $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 6 = Saturday
      $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);

      // If no schedule found AND it's a weekend, treat entire work time as overtime
      if ((!$schedule || !$schedule['shift_out']) && $isWeekend) {
        error_log("DEBUG - Weekend work detected for employee $employeeId on $date");
        
        // Calculate total work time as overtime for weekend work
        // We need to get the time_in from attendance to calculate total hours
        $attendanceSql = "SELECT time_in FROM attendance 
                         WHERE employee_id = :employee_id AND date = :date 
                         ORDER BY attendance_id DESC LIMIT 1";
        $attendanceStmt = $this->db->prepare($attendanceSql);
        $attendanceStmt->execute(['employee_id' => $employeeId, 'date' => $date]);
        $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attendance && $attendance['time_in']) {
          $timeInStamp = strtotime($attendance['time_in']);
          $currentTimeStamp = strtotime($currentTime);
          $totalWorkMinutes = ($currentTimeStamp - $timeInStamp) / 60;
          
          if ($totalWorkMinutes > 0) {
            $workHours = floor($totalWorkMinutes / 60);
            $workMinutes = floor($totalWorkMinutes) % 60;
            
            $dayType = ($dayOfWeek == 0) ? 'Sunday' : 'Saturday';
            $workText = ($workHours > 0) ? sprintf('%dh %dm', $workHours, $workMinutes) : sprintf('%dm', $workMinutes);
            
            return [
              'has_overtime' => true,
              'overtime_hours' => $workHours,
              'overtime_minutes' => $workMinutes,
              'total_overtime_minutes' => $totalWorkMinutes,
              'overtime_type' => $dayType,
              'message' => "Day-off overtime: {$workText} on {$dayType}",
              'scheduled_end' => 'No schedule',
              'actual_end' => date('H:i', $currentTimeStamp)
            ];
          }
        }
      }

      if (!$schedule || !$schedule['shift_out']) {
        error_log("DEBUG - No schedule data found for employee $employeeId on $date");
        return null; // No schedule data, can't determine overtime
      }

      $currentTimeStamp = strtotime($currentTime);
      $scheduledEndTime = strtotime($schedule['shift_out']);

      error_log("DEBUG - Time comparison:");
      error_log("DEBUG - Current time: $currentTime (" . date('H:i:s', $currentTimeStamp) . ")");
      error_log("DEBUG - Scheduled end: " . $schedule['shift_out'] . " (" . date('H:i:s', $scheduledEndTime) . ")");
      error_log("DEBUG - Current > Scheduled: " . ($currentTimeStamp > $scheduledEndTime ? 'YES' : 'NO'));

      if ($currentTimeStamp > $scheduledEndTime) {
        $overtimeMinutes = ceil(($currentTimeStamp - $scheduledEndTime) / 60);
        // Only consider it overtime if more than 30 minutes past scheduled end
        if ($overtimeMinutes > 30) {
          $overtimeHours = floor($overtimeMinutes / 60);
          $remainingMinutes = $overtimeMinutes % 60;

          $overtimeText = '';
          if ($overtimeHours > 0) {
            $overtimeText = sprintf('%dh %dm', $overtimeHours, $remainingMinutes);
          } else {
            $overtimeText = sprintf('%dm', $remainingMinutes);
          }

          // Determine overtime type based on day and time
          $overtimeType = 'Regular';
          $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 6 = Saturday
          
          if ($dayOfWeek == 0) { // Sunday
            $overtimeType = 'Sunday';
          } elseif ($dayOfWeek == 6) { // Saturday
            $overtimeType = 'Saturday';
          } elseif ($overtimeHours >= 2) { // More than 2 hours overtime on weekday
            $overtimeType = 'Extended';
          }

          return [
            'has_overtime' => true,
            'overtime_hours' => $overtimeHours,
            'overtime_minutes' => $remainingMinutes,
            'total_overtime_minutes' => $overtimeMinutes,
            'overtime_type' => $overtimeType,
            'message' => "Overtime: {$overtimeText} beyond scheduled end time",
            'scheduled_end' => date('H:i', $scheduledEndTime),
            'actual_end' => date('H:i', $currentTimeStamp)
          ];
        }
      } else {
        error_log("DEBUG - Current time is not greater than scheduled end time.");
        error_log("DEBUG - Scheduled end time: " . date('H:i', $scheduledEndTime));
        error_log("DEBUG - Current time: " . date('H:i', $currentTimeStamp));
      }

      return [
        'has_overtime' => false,
        'overtime_hours' => 0,
        'overtime_minutes' => 0,
        'total_overtime_minutes' => 0,
        'overtime_type' => null,
        'message' => null
      ];

    } catch (Exception $e) {
      error_log("Error in checkOvertimeStatus: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Check if employee is on approved leave for a specific date
   */
  private function checkLeaveStatus($employeeId, $date)
  {
    try {
      $sql = "SELECT leave_type, reason 
                   FROM leave_records 
                   WHERE employee_id = :employee_id 
                     AND status = 'Approved'
                     AND :date >= start_date 
                     AND :date <= end_date
                   LIMIT 1";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        'employee_id' => $employeeId,
        'date' => $date
      ]);

      $leave = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($leave) {
        return [
          'on_leave' => true,
          'leave_type' => $leave['leave_type'],
          'reason' => $leave['reason'] ?: 'No reason provided'
        ];
      }

      return [
        'on_leave' => false,
        'leave_type' => null,
        'reason' => null
      ];

    } catch (Exception $e) {
      error_log("Error checking leave status: " . $e->getMessage());
      return [
        'on_leave' => false,
        'leave_type' => null,
        'reason' => null
      ];
    }
  }

  /**
   * Insert overtime record into overtime_records table
   */
  private function insertOvertimeRecord($employeeId, $attendanceId, $date, $overtimeHours, $overtimeMinutes, $overtimeType = 'Regular')
  {
    try {
      // Convert overtime to decimal hours for calculation
      $totalOvertimeHours = $overtimeHours + ($overtimeMinutes / 60);
      
      // Basic overtime rate calculation (1.5x for regular overtime)
      $baseRate = $this->getEmployeeHourlyRate($employeeId);
      $overtimeRate = $baseRate * 1.5;
      
      // Calculate total overtime pay
      $totalOvertimePay = $totalOvertimeHours * $overtimeRate;
      
      $sql = "INSERT INTO overtime_records 
                (employee_id, date, hours, rate) 
              VALUES 
                (:employee_id, :date, :hours, :rate)";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        'employee_id' => $employeeId,
        'date' => $date,
        'hours' => $totalOvertimeHours, 
        'rate' => $totalOvertimePay
      ]);
      
      return $this->db->lastInsertId();
      
    } catch (Exception $e) {
      error_log("Error inserting overtime record: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Get employee's hourly rate based on basic salary and pay period
   * Pay period: Every 15th and 30th (13 working days excluding weekends)
   */
  private function getEmployeeHourlyRate($employeeId)
  {
    try {
      $sql = "SELECT basic_salary FROM employees WHERE employee_id = :employee_id LIMIT 1";
      $stmt = $this->db->prepare($sql);
      $stmt->execute(['employee_id' => $employeeId]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
      if ($result && $result['basic_salary']) {
        // Calculate hourly rate based on pay period
        // Monthly salary ÷ 2 (semi-monthly) ÷ 13 working days ÷ 8 hours
        $semiMonthlyPay = $result['basic_salary'] / 2;
        $dailyRate = $semiMonthlyPay / 13;
        $hourlyRate = $dailyRate / 8;
        
        return $hourlyRate;
      }
      
      // Default minimum wage calculation if no basic_salary found
      // Example: PHP 14,000 monthly ÷ 2 ÷ 13 ÷ 8 = PHP 67.31/hour
      return 67.31;
      
    } catch (Exception $e) {
      error_log("Error getting employee hourly rate: " . $e->getMessage());
      return 67.31; // Default rate
    }
  }
}
?>

