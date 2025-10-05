<?php
require_once '../shared/config.php';

class WorkingCalendarController {
    
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Create working calendar entries for auto-assign (bulk create)
     */
    public function createWorkingDays($data) {
        try {
            $this->pdo->beginTransaction();
            
            $month = $data['month'];
            $year = $data['year'];
            $created_by = $_SESSION['user_id'] ?? 1;
            
            // First, delete existing entries for this month/year
            $deleteStmt = $this->pdo->prepare("
                DELETE FROM working_calendar 
                WHERE MONTH(work_date) = ? AND YEAR(work_date) = ?
            ");
            $deleteStmt->execute([$month, $year]);
            
            // Also delete corresponding employee schedules
            $deleteScheduleStmt = $this->pdo->prepare("
                DELETE es FROM employee_schedule es
                INNER JOIN working_calendar wc ON es.calendar_id = wc.id
                WHERE MONTH(wc.work_date) = ? AND YEAR(wc.work_date) = ?
            ");
            $deleteScheduleStmt->execute([$month, $year]);
            
            // Prepare insert statements
            $insertCalendarStmt = $this->pdo->prepare("
                INSERT INTO working_calendar 
                (work_date, day_of_week, is_working, is_holiday, is_half_day, holiday_name, remarks, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $insertScheduleStmt = $this->pdo->prepare("
                INSERT INTO employee_schedule 
                (employee_id, work_date, shift_in, shift_out, is_rest_day, calendar_id, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            // Get all employees
            $employeesStmt = $this->pdo->query("SELECT employee_id FROM employees");
            $employees = $employeesStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $calendar_ids = [];
            
            // Process each day in the data
            foreach ($data['working_days'] as $dateStr) {
                $date = new DateTime($dateStr);
                $dayOfWeek = $date->format('N'); // 1=Monday, 7=Sunday
                
                // Insert working calendar entry
                $insertCalendarStmt->execute([
                    $dateStr,
                    $dayOfWeek,
                    1, // is_working = true
                    0, // is_holiday = false
                    0, // is_half_day = false (default full day for auto-assign)
                    null,
                    'Working day - Auto assigned',
                    $created_by
                ]);
                
                $calendar_id = $this->pdo->lastInsertId();
                $calendar_ids[] = $calendar_id;
                
                // Insert employee schedules for all employees
                foreach ($employees as $employee_id) {
                    $insertScheduleStmt->execute([
                        $employee_id,
                        $dateStr,
                        '08:00:00', // Default shift in
                        '17:00:00', // Default shift out
                        0, // is_rest_day = false
                        $calendar_id,
                        'Auto-assigned working day',
                        $created_by
                    ]);
                }
            }
            
            // Process non-working days
            foreach ($data['non_working_days'] as $dateStr) {
                $date = new DateTime($dateStr);
                $dayOfWeek = $date->format('N');
                
                // Insert working calendar entry
                $insertCalendarStmt->execute([
                    $dateStr,
                    $dayOfWeek,
                    0, // is_working = false
                    0, // is_holiday = false
                    0, // is_half_day = false (not applicable for non-working days)
                    null,
                    'Non-working day - Auto assigned',
                    $created_by
                ]);
                
                $calendar_id = $this->pdo->lastInsertId();
                
                // Insert employee schedules as rest days
                foreach ($employees as $employee_id) {
                    $insertScheduleStmt->execute([
                        $employee_id,
                        $dateStr,
                        null, // No shift times for rest days
                        null,
                        1, // is_rest_day = true
                        $calendar_id,
                        'Auto-assigned rest day',
                        $created_by
                    ]);
                }
            }
            
            // Process holidays
            foreach ($data['holidays'] as $dateStr => $holidayData) {
                $date = new DateTime($dateStr);
                $dayOfWeek = $date->format('N');
                
                // Insert working calendar entry
                $insertCalendarStmt->execute([
                    $dateStr,
                    $dayOfWeek,
                    0, // is_working = false for holidays
                    1, // is_holiday = true
                    0, // is_half_day = false (holidays are not half days)
                    $holidayData['name'],
                    $holidayData['type'] . ' holiday' . ($holidayData['recurring'] ? ' (recurring)' : ''),
                    $created_by
                ]);
                
                $calendar_id = $this->pdo->lastInsertId();
                
                // Insert employee schedules as rest days for holidays
                foreach ($employees as $employee_id) {
                    $insertScheduleStmt->execute([
                        $employee_id,
                        $dateStr,
                        null,
                        null,
                        1, // is_rest_day = true
                        $calendar_id,
                        'Holiday: ' . $holidayData['name'],
                        $created_by
                    ]);
                }
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Working calendar created successfully',
                'calendar_entries' => count($data['working_days']) + count($data['non_working_days']) + count($data['holidays']),
                'employee_schedules' => (count($data['working_days']) + count($data['non_working_days']) + count($data['holidays'])) * count($employees)
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error creating working calendar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update a single working day (from popover)
     */
    public function updateWorkingDay($data) {
        try {
            $this->pdo->beginTransaction();
            
            $date = $data['date'];
            $day_type = $data['day_type'];
            $holiday_name = $data['holiday_name'] ?? null;
            $note = $data['note'] ?? '';
            $is_half_day = isset($data['is_half_day']) ? (int)$data['is_half_day'] : 0;
            $updated_by = $_SESSION['user_id'] ?? 1;
            
            $date_obj = new DateTime($date);
            $day_of_week = $date_obj->format('N');
            
            // Determine values based on day_type
            $is_working = ($day_type === 'working') ? 1 : 0;
            $is_holiday = ($day_type === 'holiday') ? 1 : 0;
            $is_rest_day = ($day_type === 'working') ? 0 : 1;
            
            // Check if calendar entry exists
            $checkStmt = $this->pdo->prepare("
                SELECT id FROM working_calendar WHERE work_date = ?
            ");
            $checkStmt->execute([$date]);
            $existing = $checkStmt->fetch();
            
            $calendar_id = null;
            
            if ($existing) {
                // Update existing entry
                $updateStmt = $this->pdo->prepare("
                    UPDATE working_calendar 
                    SET day_of_week = ?, is_working = ?, is_holiday = ?, is_half_day = ?,
                        holiday_name = ?, remarks = ?, updated_by = ?, updated_at = NOW()
                    WHERE work_date = ?
                ");
                $updateStmt->execute([
                    $day_of_week, $is_working, $is_holiday, $is_half_day,
                    $holiday_name, $note, $updated_by, $date
                ]);
                $calendar_id = $existing['id'];
                
            } else {
                // Create new entry
                $insertStmt = $this->pdo->prepare("
                    INSERT INTO working_calendar 
                    (work_date, day_of_week, is_working, is_holiday, is_half_day, holiday_name, remarks, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $insertStmt->execute([
                    $date, $day_of_week, $is_working, $is_holiday, $is_half_day,
                    $holiday_name, $note, $updated_by
                ]);
                $calendar_id = $this->pdo->lastInsertId();
            }
            
            // Update employee schedules
            $employees = $this->pdo->query("SELECT employee_id FROM employees")->fetchAll(PDO::FETCH_COLUMN);
            
            // Delete existing schedules for this date
            $deleteScheduleStmt = $this->pdo->prepare("
                DELETE FROM employee_schedule WHERE work_date = ?
            ");
            $deleteScheduleStmt->execute([$date]);
            
            // Insert new schedules
            $insertScheduleStmt = $this->pdo->prepare("
                INSERT INTO employee_schedule 
                (employee_id, work_date, shift_in, shift_out, is_rest_day, calendar_id, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            foreach ($employees as $employee_id) {
                $shift_in = $is_working ? '08:00:00' : null;
                $shift_out = $is_working ? '17:00:00' : null;
                $half_day_note = $is_half_day ? ' (Half Day)' : '';
                $schedule_note = $is_holiday ? "Holiday: $holiday_name" : ($is_working ? "Working day$half_day_note" : 'Rest day');
                
                $insertScheduleStmt->execute([
                    $employee_id,
                    $date,
                    $shift_in,
                    $shift_out,
                    $is_rest_day,
                    $calendar_id,
                    $schedule_note,
                    $updated_by
                ]);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Working day updated successfully'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error updating working day: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get working calendar data for a specific month/year
     */
    public function getWorkingCalendar($month, $year) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM working_calendar 
                WHERE MONTH(work_date) = ? AND YEAR(work_date) = ?
                ORDER BY work_date
            ");
            $stmt->execute([$month, $year]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $results
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching working calendar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get employee schedules for a specific month/year
     */
    public function getEmployeeSchedules($month, $year) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT es.*, e.first_name, e.last_name, wc.is_working, wc.is_holiday, wc.is_half_day, wc.holiday_name
                FROM employee_schedule es
                INNER JOIN employees e ON es.employee_id = e.employee_id
                INNER JOIN working_calendar wc ON es.calendar_id = wc.id
                WHERE MONTH(es.work_date) = ? AND YEAR(es.work_date) = ?
                ORDER BY es.work_date, e.last_name, e.first_name
            ");
            $stmt->execute([$month, $year]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $results
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching employee schedules: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reset working calendar for a specific month/year (delete all entries)
     */
    public function resetMonth($month, $year) {
        try {
            $this->pdo->beginTransaction();
            
            // Debug logging
            error_log("Reset month called for: $month/$year");
            
            // FIRST: Get all calendar IDs for this month/year
            $getCalendarIdsStmt = $this->pdo->prepare("
                SELECT id FROM working_calendar 
                WHERE MONTH(work_date) = ? AND YEAR(work_date) = ?
            ");
            $getCalendarIdsStmt->execute([$month, $year]);
            $calendarIds = $getCalendarIdsStmt->fetchAll(PDO::FETCH_COLUMN);
            
            error_log("Found " . count($calendarIds) . " calendar IDs to delete");
            
            $scheduleRowsAffected = 0;
            
            // SECOND: Delete employee schedules using the specific calendar IDs
            if (!empty($calendarIds)) {
                $placeholders = str_repeat('?,', count($calendarIds) - 1) . '?';
                $deleteScheduleStmt = $this->pdo->prepare("
                    DELETE FROM employee_schedule 
                    WHERE calendar_id IN ($placeholders)
                ");
                $deleteScheduleStmt->execute($calendarIds);
                $scheduleRowsAffected = $deleteScheduleStmt->rowCount();
                error_log("Deleted $scheduleRowsAffected employee schedule rows");
            }
            
            // THIRD: Delete working calendar entries (parent records)
            $deleteCalendarStmt = $this->pdo->prepare("
                DELETE FROM working_calendar 
                WHERE MONTH(work_date) = ? AND YEAR(work_date) = ?
            ");
            $deleteCalendarStmt->execute([$month, $year]);
            $calendarRowsAffected = $deleteCalendarStmt->rowCount();
            error_log("Deleted $calendarRowsAffected working calendar rows");
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Calendar reset successfully',
                'deleted_schedules' => $scheduleRowsAffected,
                'deleted_calendar' => $calendarRowsAffected
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error resetting calendar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Export working calendar to Excel format
     */
    public function exportCalendarToExcel($month, $year) {
        try {
            // Get working calendar data
            $calendarData = $this->getWorkingCalendar($month, $year);
            if (!$calendarData['success']) {
                return $calendarData;
            }

            // Get employee schedules data
            $employeeData = $this->getEmployeeSchedules($month, $year);
            if (!$employeeData['success']) {
                return $employeeData;
            }

            // Create CSV content (Excel compatible)
            $monthNames = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];

            $filename = "working-calendar-{$monthNames[$month]}-{$year}.csv";
            
            // Prepare CSV data
            $csvData = [];
            
            // Header
            $csvData[] = ["{$monthNames[$month]} {$year} - Working Days Calendar"];
            $csvData[] = ["Generated on: " . date('Y-m-d H:i:s')];
            $csvData[] = []; // Empty row
            
            // Calendar overview
            $csvData[] = ["Date", "Day of Week", "Type", "Half Day", "Holiday Name", "Notes"];
            
            $dayNames = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            foreach ($calendarData['data'] as $entry) {
                $dayOfWeek = $dayNames[$entry['day_of_week']] ?? 'Unknown';
                $type = '';
                if ($entry['is_holiday']) {
                    $type = 'Holiday';
                } elseif ($entry['is_working']) {
                    $type = $entry['is_half_day'] ? 'Half Day' : 'Working Day';
                } else {
                    $type = 'Non-Working Day';
                }
                
                $csvData[] = [
                    $entry['work_date'],
                    $dayOfWeek,
                    $type,
                    $entry['is_half_day'] ? 'Yes' : 'No',
                    $entry['holiday_name'] ?? '',
                    $entry['remarks'] ?? ''
                ];
            }
            
            // Employee schedule summary
            if (!empty($employeeData['data'])) {
                $csvData[] = []; // Empty row
                $csvData[] = ["Employee Schedule Summary"];
                $csvData[] = ["Employee", "Date", "Shift In", "Shift Out", "Type", "Notes"];
                
                foreach ($employeeData['data'] as $schedule) {
                    $employeeName = trim($schedule['first_name'] . ' ' . $schedule['last_name']);
                    $type = '';
                    if ($schedule['is_holiday']) {
                        $type = 'Holiday';
                    } elseif ($schedule['is_working']) {
                        $type = $schedule['is_half_day'] ? 'Half Day' : 'Working Day';
                    } else {
                        $type = 'Rest Day';
                    }
                    
                    $csvData[] = [
                        $employeeName,
                        $schedule['work_date'],
                        $schedule['shift_in'] ?? '',
                        $schedule['shift_out'] ?? '',
                        $type,
                        $schedule['notes'] ?? ''
                    ];
                }
            }
            
            return [
                'success' => true,
                'filename' => $filename,
                'data' => $csvData
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error exporting calendar: ' . $e->getMessage()
            ];
        }
    }
}
?>
