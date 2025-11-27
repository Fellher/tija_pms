<?php
class TimeAttendance {
    private $attendanceRecords = [];

    public function recordAttendance($employeeId, $time) {
        $this->attendanceRecords[] = [
            'employeeId' => $employeeId,
            'time' => $time
        ];
    }

    public function getAttendanceRecords() {
        return $this->attendanceRecords;
    }

    	/*=========================================================
 	Time Sheets Time lOgs
	==========================================================*/
	public static function project_tasks_time_logs ($whereArr, $single, $DBConn) {
		$cols = array('timelogID',
							'DateAdded',
							'taskDate',
							'employeeID',
							'clientID',
							'projectID',
							'projectPhaseID',
							'projectTaskID',
							'subtaskID',
							'workTypeID',
							'taskNarrative',
							'startTime',
							'endTime',
							'taskDuration',
							'dailyComplete',
							'taskStatusID',
							'taskType',
							'taskActivityID',
							'workSegmentID',
							'recurringInstanceID',
							'billingCycleID',
							'LastUpdate',
							'Lapsed',
							'Suspended');

		$rows = $DBConn->retrieve_db_table_rows ('tija_tasks_time_logs', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function project_tasks_time_logs_full($whereArr, $single, $DBConn){
		$where = '';
		$params = array();
		$rows = array();
		$timelogs = array(
			'timelogID',
			'DateAdded',
			'taskDate',
			'employeeID',
			'clientID',
			'projectID',
			'projectPhaseID',
			'projectTaskID',
			'subtaskID',
			'workTypeID',
			'taskNarrative',
			'startTime',
			'endTime',
			'taskDuration',
			"workHours",

			'Lapsed',
			'Suspended'
		);
		$tasks = array(
			'projectTaskCode',
			'projectTaskName',
			'taskStart',
			'taskDeadline',
			'progress',
			'status',
			'taskDescription',
			'hoursAllocated'
		);
		$phases = array(
			'projectPhaseName',
			'projectID',
			'phaseWorkHrs',
			'phaseWeighting',
			'billingMilestone'
		);
		$projects = array(
			'projectCode',
			'projectName',
			'projectStart',
			'projectClose',
			'projectDeadline',
			'projectOwnerID',
			'projectManagersIDs',
			'billable',
			'billingRateID',
			'billableRateValue',
			'roundingoff',
			'roundingInterval',
			'businessUnitID',
			'projectValue',
			'approval',
			'projectStatus',
			'allocatedWorkHours',
			'orderDate'
		);
		$clients = array(
			'clientCode',
			'clientName',
			'accountOwnerID'
		);
		$workTypes = array(
			'workTypeName',
			'workTypeDescription'
		);
		$activity = array(

				'activityID',
				'clientID',
				'activityName',
				'activityDescription',
				'activityTypeID',


		);

		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
                $column = ($col === 'userID') ? 'employeeID' : $col;
                $where .= "l.{$column} = ?";
                $params[] = array($val, is_int($val) ? 'i' : 's');
				$i++;
			}
		}

		$query = "SELECT l.timelogID, l.DateAdded, l.taskDate, l.employeeID, l.projectTaskID, l.projectPhaseID, l.projectID, l.clientID, l.workTypeID, l.taskNarrative, l.startTime, l.subtaskID, l.endTime, l.taskDuration, l.Lapsed, l.Suspended, l.taskActivityID,
		t.projectTaskCode, t.projectTaskName, t.taskStart, t.taskDeadline, t.progress, t.status, t.taskDescription, t.hoursAllocated,
		ph.projectPhaseName, ph.projectID, ph.phaseWorkHrs, ph.phaseWeighting, ph.billingMilestone,
		pr.projectCode, pr.projectName, pr.projectStart, pr.projectClose, pr.projectDeadline, pr.projectOwnerID, pr.projectManagersIDs, pr.billable, pr.billingRateID, pr.billableRateValue, pr.roundingoff, pr.roundingInterval, pr.businessUnitID, pr.projectValue, pr.approval, pr.projectStatus, pr.allocatedWorkHours, pr.orderDate,
		c.clientCode, c.clientName, c.accountOwnerID,
		w.workTypeName, w.workTypeDescription,
		a.activityName, a.activityDescription, a.activityTypeID,
		CONCAT(p.FirstName, ' ', p.Surname) AS logOwnerName, p.Email AS logOwnerEmail

		FROM tija_tasks_time_logs l
		LEFT JOIN tija_project_tasks t ON l.projectTaskID = t.projectTaskID
		LEFT JOIN tija_project_phases ph ON l.projectPhaseID = ph.projectPhaseID
		LEFT JOIN tija_projects pr ON l.projectID = pr.projectID
		LEFT JOIN tija_clients c ON l.clientID = c.clientID
		LEFT JOIN tija_work_types w ON l.workTypeID = w.workTypeID
		LEFT JOIN people p ON l.employeeID = p.ID
		LEFT JOIN tija_activities a ON l.taskActivityID = a.activityID

		{$where}

		ORDER BY l.taskDate Desc, l.startTime ASC";
      // echo($query);
   // var_dump($where);
   // var_dump($params);

		$rows = $DBConn->fetch_all_rows($query,$params);

		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	/**
	 * Tasks timelog between two dates
	 *
	 */

	 public static function project_tasks_time_logs_between_dates($whereArr, $startDate, $endDate, $single, $DBConn){
		$where = '';
		$params = array();
		$rows = array();
		$timelogs = array(
			'timelogID',
			'DateAdded',
			'taskDate',
			'employeeID',
			'clientID',
			'projectID',
			'projectPhaseID',
			'projectTaskID',
			'subtaskID',
			'workTypeID',
			'taskNarrative',
			'startTime',
			'endTime',
			'taskDuration',
			"workHours",

			'Lapsed',
			'Suspended'
		);
		$tasks = array(
			'projectTaskCode',
			'projectTaskName',
			'taskStart',
			'taskDeadline',
			'progress',
			'status',
			'taskDescription',
			'hoursAllocated'
		);
		$phases = array(
			'projectPhaseName',
			'projectID',
			'phaseWorkHrs',
			'phaseWeighting',
			'billingMilestone'
		);
		$projects = array(
			'projectCode',
			'projectName',
			'projectStart',
			'projectClose',
			'projectDeadline',
			'projectOwnerID',
			'projectManagersIDs',
			'billable',
			'billingRateID',
			'billableRateValue',
			'roundingoff',
			'roundingInterval',
			'businessUnitID',
			'projectValue',
			'approval',
			'projectStatus',
			'allocatedWorkHours',
			'orderDate'
		);
		$clients = array(
			'clientCode',
			'clientName',
			'accountOwnerID'
		);
		$workTypes = array(
			'workTypeName',
			'workTypeDescription'
		);
		$activity = array(

				'activityID',
				'clientID',
				'activityName',
				'activityDescription',
				'activityTypeID',


		);
		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				$where .= "l.{$col} = ?";
				$params[] = array($val, 'l');
				$i++;
			}
		}
		if ($where == '') {
			$where = "WHERE ";
		} else {
			$where .= " AND ";
		}
		$where .= " (l.taskDate BETWEEN ? AND ? ) ";
		$params[] = array($startDate, 'l');
		$params[] = array($endDate, 'l');
		$query = "SELECT l.timelogID, l.DateAdded, l.taskDate, l.employeeID, l.projectTaskID, l.projectPhaseID, l.projectID, l.clientID, l.workTypeID, l.taskNarrative, l.startTime, l.subtaskID, l.endTime, l.taskDuration, l.Lapsed, l.Suspended, l.taskActivityID,
		t.projectTaskCode, t.projectTaskName, t.taskStart, t.taskDeadline, t.progress, t.status, t.taskDescription, t.hoursAllocated,
		ph.projectPhaseName, ph.projectID, ph.phaseWorkHrs, ph.phaseWeighting, ph.billingMilestone,
		pr.projectCode, pr.projectName, pr.projectStart, pr.projectClose, pr.projectDeadline, pr.projectOwnerID, pr.projectManagersIDs, pr.billable, pr.billingRateID, pr.billableRateValue, pr.roundingoff, pr.roundingInterval, pr.businessUnitID, pr.projectValue, pr.approval, pr.projectStatus, pr.allocatedWorkHours, pr.orderDate,
		c.clientCode, c.clientName, c.accountOwnerID,
		w.workTypeName, w.workTypeDescription,
		a.activityName, a.activityDescription, a.activityTypeID,
		CONCAT(p.FirstName, ' ', p.Surname) AS logOwnerName, p.Email AS logOwnerEmail
		FROM tija_tasks_time_logs l
		LEFT JOIN tija_project_tasks t ON l.projectTaskID = t.projectTaskID
		LEFT JOIN tija_project_phases ph ON l.projectPhaseID = ph.projectPhaseID
		LEFT JOIN tija_projects pr ON l.projectID = pr.projectID
		LEFT JOIN tija_clients c ON l.clientID = c.clientID
		LEFT JOIN tija_work_types w ON l.workTypeID = w.workTypeID
		LEFT JOIN people p ON l.employeeID = p.ID
		LEFT JOIN tija_activities a ON l.taskActivityID = a.activityID
		{$where}
		ORDER BY l.taskDate Desc, l.startTime ASC";
		// echo($query);
	// var_dump($where);
	// var_dump($params);

		$rows = $DBConn->fetch_all_rows($query,$params);

		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}



	/*Task Timelog Utility Functions*/

	public static function total_task_timelogs ($taskID, $DBConn) {
		$allTaskTimelogs = Timeattendance::project_tasks_time_logs(array("projectTaskID"=>$taskID, "Suspended"=>"N"), false, $DBConn);
		$totalTaskLogged=0;
		if ($allTaskTimelogs) {
			foreach ($allTaskTimelogs as $key => $timelog) {
				$digitalTime = Utility::transform_hour_to_decimal($timelog->taskDuration);
				$totalTaskLogged +=$digitalTime;
			}
			$totalLogSec = ($totalTaskLogged*3600);
			return $totalLogSec;
		}
		return false;
	}


	public static function messages ($whereArr, $single, $DBConn) {
		$cols = array('messageID', 'DateAdded', 'senderID', 'receipientID', 'subject', 'message', 'forward', 'motherMesageID', 'messageRead', 'LastUpdated', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_messages_inbox', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}
	/*Activity types*/
	public static function activity_types ($whereArr, $single, $DBConn) {
		$cols = array('activityTypeID ', 'DateAdded', 'activityTypeName','iconlink', 'description', 'Lapsed', 'Suspended');

		$rows= $DBConn->retrieve_db_table_rows('tija_activity_types', $cols, $whereArr);
	 return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

   /*================================================
	Absence Data
	===================================================*/
	public static function absence ( $whereArr, $single, $DBConn) {
		$cols = array('absenceID', 'DateAdded',  'userID', 'absenceName', 'absenceTypeID', 'projectID', 'absenceDate', 'startTime', 'endTime', 'allday', 'absenceDescription', "LastUpdate", 'Suspended', 'Lapsed');
		$rows = $DBConn->retrieve_db_table_rows ('sbsl_absence_data', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function absence_full ( $whereArr, $single, $DBConn) {
		$where = '';
		$params = array();
		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				$where .= "a.{$col} = ?";
				$params[] = array($val, 'a');
				$i++;
			}
		}
		$query = "SELECT a.absenceID, a.DateAdded, a.userID, a.absenceName, a.absenceTypeID, at.absenceTypeName, a.projectID, p.projectName, p.clientID,  a.absenceDate, a.startTime, a.endTime, a.allday, a.absenceHrs, a.absenceDescription, a.LastUpdate, a.Suspended, a.Lapsed
		FROM tija_absence_data a
		 LEFT JOIN  tija_absence_type at ON a.absenceTypeID = at.absenceTypeID
		 LEFT JOIN tija_projects p ON a.projectID = p.projectID


		 {$where}
		 ORDER BY  a.absenceID ASC";
		 $rows = $DBConn->fetch_all_rows($query,$params);
		 if ($rows) {
			foreach ($rows as $key => $row) {
				$rows[$key]->userName = Core::user_name($row->userID, $DBConn);

			}
		}
	 	return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	// task status
	public static function task_status ($whereArr, $single, $DBConn) {
		$cols = array('taskStatusID', 'DateAdded', 'taskStatusName', 'taskStatusCode', 'taskStatusDescription', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_task_status', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	/*=======================================
	Absence Type
	==========================================*/
	public static function absence_types ( $whereArr, $single, $DBConn) {
		$cols = array('absenceTypeID', 'DateAdded', 'absenceTypeName', 'Suspended', 'Lapsed');
		$rows = $DBConn->retrieve_db_table_rows ('tija_absence_type', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function daily_task_status_change_log($whereArr, $single, $DBConn) {
		$cols = array('taskStatusChangeID', 'DateAdded', 'projectID', 'projectTaskID', 'projectPhaseID', 'changeDateTime', 'employeeID', 'taskChangeNotes', 'taskDate',   'subtaskID', 'taskStatusID', 'LastUpdate',  'LastUpdateByID', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_task_status_change_log', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function daily_task_status_change_log_full($whereArr, $single, $DBConn){
		$where = '';
		$params = array();
		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				$where .= "l.{$col} = ?";
				$params[] = array($val, 'l');
				$i++;
			}
		}

		$query = "SELECT l.taskStatusChangeID, l.DateAdded, l.projectID, l.projectTaskID, l.projectPhaseID, l.changeDateTime, l.employeeID, l.taskChangeNotes, l.taskDate, l.subtaskID, l.taskStatusID, l.LastUpdate, l.LastUpdateByID, l.Lapsed, l.Suspended,
			t.projectTaskCode, t.projectTaskName, t.taskStart, t.taskDeadline, t.progress, t.status, t.taskDescription,
			p.projectCode, p.projectName, p.projectStart, p.projectClose, p.projectDeadline,
			s.taskStatusName,
			u.FirstName AS employeeFirstName,
			u.Surname AS employeeSurname
			FROM tija_task_status_change_log l
			LEFT JOIN tija_project_tasks t ON l.projectTaskID = t.projectTaskID
			LEFT JOIN tija_projects p ON l.projectID = p.projectID
			LEFT JOIN tija_task_status s ON s.taskStatusID = l.taskStatusID
			LEFT JOIN people u ON u.ID = l.employeeID

			{$where}

			ORDER BY l.changeDateTime ASC";
		// echo($query);
	// var_dump($where);
	// var_dump($params);

		$rows = $DBConn->fetch_all_rows($query,$params);

	 	return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function timelog_files($whereArr, $single, $DBConn) {
		$cols = array('taskFileID', 'DateAdded', 'fileURL', 'timelogID', 'userID', 'fileSize', 'fileType',   'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_task_files', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	/**
	 * Calculate employee total timelog for current month
	 * @param int $employeeID - Employee ID
	 * @param object $DBConn - Database connection object
	 * @param string $month - Month in Y-m format (optional, defaults to current month)
	 * @return array - Array containing formatted time, total seconds, hours decimal, and breakdown
	 */
	public static function calculate_employee_monthly_timelog($employeeID, $DBConn, $month = null) {
		// Default to current month if not specified
		if ($month === null) {
			$month = date('Y-m');
		}

		// Get first and last day of the month
		$firstDay = $month . '-01';
		$lastDay = date('Y-m-t', strtotime($firstDay));

		// Get timelogs for the employee for the specified month
		$whereArr = array(
			'employeeID' => $employeeID,
			'Suspended' => 'N'
		);

		$timelogs = self::project_tasks_time_logs_between_dates($whereArr, $firstDay, $lastDay, false, $DBConn);

		$totalSeconds = 0;
		$timelogBreakdown = array();

		if ($timelogs && is_array($timelogs)) {
			foreach ($timelogs as $timelog) {
				if ($timelog->employeeID == $employeeID) {
					$taskDuration = $timelog->taskDuration;

					// Handle null or empty duration
					if ($taskDuration == null || $taskDuration == "" || $taskDuration == "0") {
						$taskDuration = "00:00:00";
					}

					// Convert different time formats to standard format
					if (strpos($taskDuration, 'h') !== false) {
						$taskDuration = str_replace('h', ':', $taskDuration);
					} elseif (strpos($taskDuration, 'm') !== false) {
						$taskDuration = str_replace('m', ':', $taskDuration);
					} elseif (strpos($taskDuration, 's') !== false) {
						$taskDuration = str_replace('s', '', $taskDuration);
					}

					// Parse time parts
					$timeParts = explode(':', $taskDuration);
					$hours = isset($timeParts[0]) ? (int)$timeParts[0] : 0;
					$minutes = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
					$seconds = isset($timeParts[2]) ? (int)$timeParts[2] : 0;

					$taskSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
					$totalSeconds += $taskSeconds;

					// Store breakdown for detailed analysis
					$timelogBreakdown[] = array(
						'timelogID' => $timelog->timelogID,
						'taskDate' => $timelog->taskDate,
						'projectTaskName' => $timelog->projectTaskName,
						'projectName' => $timelog->projectName,
						'taskDuration' => $taskDuration,
						'taskSeconds' => $taskSeconds,
						'status' => $timelog->status
					);
				}
			}
		}

		// Convert total seconds to hours, minutes, seconds
		$hours = floor($totalSeconds / 3600);
		$minutes = floor(($totalSeconds % 3600) / 60);
		$seconds = $totalSeconds % 60;

		// Format time as HH:MM:SS
		$formattedTime = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

		// Calculate decimal hours
		$totalHoursDecimal = $totalSeconds / 3600;

		return array(
			'formattedTime' => $formattedTime,
			'totalSeconds' => $totalSeconds,
			'totalHoursDecimal' => $totalHoursDecimal,
			'hours' => $hours,
			'minutes' => $minutes,
			'seconds' => $seconds,
			'timelogBreakdown' => $timelogBreakdown,
			'month' => $month,
			'firstDay' => $firstDay,
			'lastDay' => $lastDay,
			'totalTimelogs' => count($timelogBreakdown)
		);
	}

	public static function task_statuses($whereArr, $single, $DBConn) {
		$cols = array('taskStatusID', 'DateAdded', 'taskStatusName', 'taskStatusDescription',  'colorVariableID', 'LastUpdate', 'LastUpdateByID', 'Lapsed',  'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_task_status', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function notification($whereArr, $single, $DBConn) {
		$cols = array('notificationID', 'DateAdded', 'employeeID', 'notificationTypeID', 'notificationTitle', 'notificationMessage', 'notificationLink', 'notificationStatus', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_notifications', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function notification_type($whereArr, $single, $DBConn) {
		$cols = array('notificationTypeID', 'DateAdded', 'notificationTypeName', 'notificationTypeDescription', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_notification_types', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}




}