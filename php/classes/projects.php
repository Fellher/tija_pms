<?php
// Prokects classes

class Projects {
   /**
    * functions fro project module
    */
    public static function project_billing_rates($whereArr, $single, $DBConn) {
      $cols = array('billingRateID', 'DateAdded', 'billingRate', 'billingRateDescription', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_billing_rate', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function projects_mini ($whereArr, $single, $DBConn) {
		$cols = array('projectID', 'DateAdded', 'DateLastUpdated', 'projectCode', 'projectName','entityID', 'orgDataID', 'caseID', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'projectOwnerID', 'projectTypeID', 'projectManagersIDs', 'billable', 'billingRateID', 'billableRateValue', 'roundingoff', 'roundingInterval', 'businessUnitID', 'projectValue', 'approval', 'projectStatus','allocatedWorkHours', 'orderDate', "LastUpdate", 'Lapsed', 'Suspended', 'isRecurring', 'recurrenceType', 'recurrenceInterval', 'billingCycleAmount');

		$rows = $DBConn->retrieve_db_table_rows ('tija_projects', $cols, $whereArr);
	   return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

   public static function projects_full ($whereArr, $single, $DBConn) {      $params= array();
      $where= '';
      $rows=array();
		$projectArray = array('projectID', 'DateAdded', 'DateLastUpdated', 'projectCode', 'projectName','entityID', 'orgDataID', 'caseID', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'projectOwnerID', 'projectTypeID', 'projectManagersIDs', 'billable', 'billingRateID', 'billableRateValue', 'roundingoff', 'roundingInterval', 'businessUnitID', 'projectValue', 'approval', 'projectStatus','allocatedWorkHours', 'orderDate',"LastUpdate",  'Lapsed', 'Suspended');
		$clientArray = array('clientID', 'clientName', 'clientCode', 'accountOwnerID', 'vatNumber', 'clientDescription', 'clientIndustryID', 'clientSectorID', 'clientLevelID');
		$peopleArray = array('ID', 'FirstName', 'Surname');
		$businessUnitArray = array('businessUnitID', 'businessUnitName', 'businessUnitDescription');
		if (count($whereArr) === 0) {
			$where = '';
		} else {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				if (in_array($col, $projectArray)) {
					$where .= "p.{$col} = ?";
					$params[] = array($val, 'p');
				} elseif (in_array($col, $clientArray)) {
					$where .= "c.{$col} = ?";
					$params[] = array($val, 'c');
				} elseif (in_array($col, $peopleArray)) {
					$where .= "u.{$col} = ?";
					$params[] = array($val, 'u');
				} elseif (in_array($col, $businessUnitArray)) {
					$where .= "b.{$col} = ?";
					$params[] = array($val, 'b');
				}
				// $where .= "p.{$col} = ?";
				// $params[] = array($val, 'p');
				$i++;
			}
		}

      $sql = "SELECT
         p.projectID, p.DateAdded, p.DateLastUpdated, p.projectCode, p.projectName, p.caseID, p.clientID, p.projectStart, p.projectClose, p.projectDeadline, p.projectOwnerID, p.projectManagersIDs, p.billable, p.billingRateID, p.billableRateValue, p.roundingoff, p.roundingInterval, p.businessUnitID, p.projectValue, p.approval, p.projectStatus,p.allocatedWorkHours, p.orderDate, p.LastUpdate, p.Lapsed, p.Suspended, p.orgDataID, p.entityID, p.projectTypeID, p.projectManagersIDs, p.LastUpdate,
			c.clientID, c.clientName, c.clientCode, c.accountOwnerID, c.vatNumber, c.clientDescription, c.clientIndustryID, c.clientSectorID, c.clientLevelID,
         c.clientName,
         u.FirstName as projectOwnerFirstName,
         u.Surname as projectOwnerLastName,
          CONCAT(u.FirstName, ' ', u.Surname) as projectOwnerName,

         b.businessUnitName,
         b.businessUnitDescription
      FROM tija_projects p
      LEFT JOIN tija_clients c ON p.clientID = c.clientID
      LEFT JOIN people u ON p.projectOwnerID = u.ID
      LEFT JOIN tija_business_units b ON p.businessUnitID = b.businessUnitID
      {$where}
		ORDER BY p.projectID DESC";
      $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   		/* ===============================
	Cases
	=================================*/
	public static function cases( $whereArr, $single, $DBConn)  {
		$cols = array('caseID', 'DateAdded', 'caseName', 'caseOwner', 'clientID', 'orgDataID', 'entityID', 'caseType', 'saleID', 'projectID', 'Lapsed', 'Suspended');

		$rows = $DBConn->retrieve_db_table_rows ('tija_cases', $cols, $whereArr);
	return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

	}

   	/*=========================================================
	Project Phases
	==========================================================*/
	public static function project_phases_mini ($whereArr, $single, $DBConn) {
		$cols = array('projectPhaseID', 'DateAdded', 'projectID', 'projectPhaseName', 'phaseStartDate', 'phaseEndDate', 'phaseDescription', 'phaseWorkHrs', 'phaseWeighting', 'billingMilestone', 'LastUpdate', 'LastUpdatedByID',  'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_project_phases', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function project_phases ($whereArr, $single, $DBConn) {
		$where = '';
		$params = array();
		$phaseCols = array('projectPhaseID', 'projectID', 'projectPhaseName', 'phaseStartDate', 'phaseEndDate', 'phaseWorkHrs', 'phaseWeighting', 'billingMilestone', 'LastUpdate', 'LastUpdatedByID',  'Lapsed', 'Suspended');
		$projectCols = array('projectID', 'projectCode', 'projectName', 'clientID', 'projectStart', 'projectClose');
		$userCols = array('ID', 'FirstName', 'Surname', 'userInitials');
		$clientCols = array('clientID', 'clientName', 'clientCode');

		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				$where .= "ph.{$col} = ?";
				$params[] = array($val, 't');
				$i++;
			}
		}

		$query = "SELECT ph.projectPhaseID, ph.DateAdded, ph.projectID, ph.projectPhaseName, ph.phaseStartDate, ph.phaseEndDate, ph.phaseWorkHrs, ph.phaseWeighting, ph.billingMilestone, ph.LastUpdate, ph.LastUpdatedByID, ph.Lapsed, ph.Suspended,
		p.projectCode, p.projectName, p.clientID, p.projectStart, p.projectClose,
		u.FirstName, u.Surname, CONCAT(u.FirstName, ' ', u.Surname) as projectOwnerName, u.userInitials,
		c.clientName, c.clientCode

		FROM tija_project_phases ph
		LEFT JOIN tija_projects p ON ph.projectID = p.projectID
		LEFT JOIN people u ON ph.LastUpdatedByID = u.ID
		LEFT JOIN tija_clients c ON p.clientID = c.clientID

		{$where}
		ORDER BY p.projectID ASC, ph.projectPhaseID ASC";

		 $rows = $DBConn->fetch_all_rows($query,$params);
		 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

	}

   /*=========================================================
	Projects Task
	==========================================================*/
	public static function project_tasks ($whereArr, $single, $DBConn) {
		$cols = array('projectTaskID',
							'DateAdded',
							'DateLastUpdated',
							'projectTaskCode',
							'projectTaskName',
							'projectID',
							'taskStart',
							'taskDeadline',
							'projectPhaseID',
							'progress',
							'status',
							'taskStatusID',
							'projectTaskTypeID',
							'taskDescription',
							'hoursAllocated',
							'assigneeID',
							'taskWeighting',
							'needsDocuments',
							'Lapsed',
							'Suspended'
						);

		$rows = $DBConn->retrieve_db_table_rows ('tija_project_tasks', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function project_tasks_full ($whereArr, $single, $DBConn) {
		$where = '';
		$params = array();
		$projectTasks = array('projectTaskID', 'DateAdded', 'DateLastUpdated', 'projectTaskCode', 'projectTaskName', 'projectID', 'taskStart', 'taskDeadline', 'projectPhaseID', 'progress', 'status', 'taskStatusID', 'projectTaskTypeID', 'taskDescription', 'hoursAllocated', 'assigneeID', 'taskWeighting', 'Lapsed', 'Suspended');
		$projectPhases = array('projectPhaseID', 'projectPhaseName',  'phaseWorkHrs', 'phaseWeighting', 'billingMilestone');
		$projects = array('projectID', 'projectCode', 'projectName', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'projectOwnerID');
		$taskStatus = array('taskStatusName');
		$clients = array('clientID', 'clientName', 'clientCode');
		$people = array('ID', 'FirstName', 'Surname', 'userInitials');

		if( count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				if (in_array($col, $projectTasks)) {
					$where .= "t.{$col} = ?";
					$params[] = array($val, 't');
				} elseif (in_array($col, $projectPhases)) {
					$where .= "p.{$col} = ?";
					$params[] = array($val, 'p');
				} elseif (in_array($col, $projects)) {
					$where .= "pr.{$col} = ?";
					$params[] = array($val, 'pr');
				} elseif (in_array($col, $taskStatus)) {
					$where .= "st.{$col} = ?";
					$params[] = array($val, 'st');
				} elseif (in_array($col, $clients)) {
					$where .= "c.{$col} = ?";
					$params[] = array($val, 'c');
				} elseif (in_array($col, $people)) {
					$where .= "u.{$col} = ?";
					$params[] = array($val, 'u');
				}
				$i++;
			}
		}

		$query = "SELECT t.projectTaskID, t.DateAdded, t.DateLastUpdated, t.projectTaskCode, t.projectTaskName, t.projectID, t.taskStart, t.taskDeadline, t.projectPhaseID, t.progress, t.status, t.taskStatusID, t.projectTaskTypeID, t.taskDescription, t.hoursAllocated, t.assigneeID, t.taskWeighting, t.Lapsed, t.Suspended,t.needsDocuments,
			st.taskStatusName,
			p.projectPhaseName,
			p.phaseWorkHrs,
			p.phaseWeighting,
			p.billingMilestone,
			pr.projectCode,
			pr.projectName,
			pr.clientID,
			pr.projectStart,
			pr.projectClose,
			pr.projectDeadline,
			pr.projectOwnerID,
			c.clientName,
			c.clientCode,
			u.FirstName,
			u.Surname,
			CONCAT(u.FirstName, ' ', u.Surname) as assigneeName,
			u.userInitials
		FROM tija_project_tasks t
		LEFT JOIN tija_project_phases p ON  t.projectPhaseID = p.projectPhaseID
		LEFT JOIN tija_projects pr ON t.projectID = pr.projectID
		LEFT JOIN tija_task_status st ON  t.status = st.taskStatusID
		LEFT JOIN tija_clients c ON pr.clientID = c.clientID
		LEFT JOIN people u ON t.assigneeID = u.ID
		{$where}
		ORDER BY  p.projectID ASC";

		 $rows = $DBConn->fetch_all_rows($query,$params);
		 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function projects_tasks_full ($whereArr, $single, $DBConn) {
		$where = '';
		$params = array();
		$projectTasks = array('projectTaskID', 'DateAdded', 'DateLastUpdated', 'projectTaskCode', 'projectTaskName', 'projectID', 'taskStart', 'taskDeadline', 'projectPhaseID', 'progress', 'status', 'taskStatusID', 'projectTaskTypeID', 'taskDescription', 'hoursAllocated', 'assigneeID', 'taskWeighting', 'Lapsed', 'Suspended');
		$projectPhases = array('projectPhaseID', 'projectPhaseName',  'phaseWorkHrs', 'phaseWeighting', 'billingMilestone');
		$projects = array('projectID', 'projectCode', 'projectName', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'projectOwnerID');
		$taskStatus = array('taskStatusName');
		$clients = array('clientID', 'clientName', 'clientCode');
		$people = array('ID', 'FirstName', 'Surname', 'userInitials');

		if( count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				if (in_array($col, $projectTasks)) {
					$where .= "t.{$col} = ?";
					$params[] = array($val, 't');
				} elseif (in_array($col, $projectPhases)) {
					$where .= "p.{$col} = ?";
					$params[] = array($val, 'p');
				} elseif (in_array($col, $projects)) {
					$where .= "pr.{$col} = ?";
					$params[] = array($val, 'pr');
				} elseif (in_array($col, $taskStatus)) {
					$where .= "st.{$col} = ?";
					$params[] = array($val, 'st');
				} elseif (in_array($col, $clients)) {
					$where .= "c.{$col} = ?";
					$params[] = array($val, 'c');
				} elseif (in_array($col, $people)) {
					$where .= "u.{$col} = ?";
					$params[] = array($val, 'u');
				}
				$i++;
			}
		}

		$query = "SELECT t.projectTaskID, t.DateAdded, t.DateLastUpdated, t.projectTaskCode, t.projectTaskName, t.projectID, t.taskStart, t.taskDeadline, t.projectPhaseID, t.progress, t.status, t.taskStatusID, t.projectTaskTypeID, t.taskDescription, t.hoursAllocated, t.assigneeID, t.taskWeighting, t.Lapsed, t.Suspended,t.needsDocuments,
			st.taskStatusName,
			p.projectPhaseName,
			p.phaseWorkHrs,
			p.phaseWeighting,
			p.billingMilestone,
			pr.projectCode,
			pr.projectName,
			pr.clientID,
			pr.projectStart,
			pr.projectClose,
			pr.projectDeadline,
			pr.projectOwnerID,
			c.clientName,
			c.clientCode,
			u.FirstName,
			u.Surname,
			CONCAT(u.FirstName, ' ', u.Surname) as assigneeName,
			u.userInitials
		FROM tija_project_tasks t
		LEFT JOIN tija_project_phases p ON  t.projectPhaseID = p.projectPhaseID
		LEFT JOIN tija_projects pr ON t.projectID = pr.projectID
		LEFT JOIN tija_task_status st ON  t.status = st.taskStatusID
		LEFT JOIN tija_clients c ON pr.clientID = c.clientID
		LEFT JOIN people u ON t.assigneeID = u.ID
		{$where}
		ORDER BY  p.projectID ASC";

		 $rows = $DBConn->fetch_all_rows($query,$params);
		 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	/*========================================================= */

	public static function projects_tasks($whereArr, $single, $DBConn) {
		$where = '';
		$params = array();

		$projectTasks = array('projectTaskID', 'DateAdded', 'DateLastUpdated', 'projectTaskCode', 'projectTaskName', 'projectID', 'taskStart', 'taskDeadline', 'projectPhaseID', 'progress', 'status', 'taskStatusID', 'projectTaskTypeID', 'taskDescription', 'hoursAllocated', 'assigneeID', 'taskWeighting', 'Lapsed', 'Suspended');
		$projectPhases = array('projectPhaseID', 'projectPhaseName',  'phaseWorkHrs', 'phaseWeighting', 'billingMilestone');
		$projects = array('projectID', 'projectCode', 'projectName', 'clientID', 'projectStart', 'projectClose');
		$taskStatus = array('taskStatusName');
		$clients = array('clientID', 'clientName', 'clientCode');



		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				if (in_array($col, $projectTasks)) {
					$where .= "t.{$col} = ?";
					$params[] = array($val, 't');
				} elseif (in_array($col, $projectPhases)) {
					$where .= "p.{$col} = ?";
					$params[] = array($val, 'p');
				} elseif (in_array($col, $projects)) {
					$where .= "pr.{$col} = ?";
					$params[] = array($val, 'pr');
				} elseif (in_array($col, $taskStatus)) {
					$where .= "st.{$col} = ?";
					$params[] = array($val, 'st');
				} elseif (in_array($col, $clients)) {
					$where .= "c.{$col} = ?";
					$params[] = array($val, 'c');
				}
				$i++;
			}
		}

		$query = "SELECT t.projectTaskID, t.DateAdded, t.DateLastUpdated, t.projectTaskCode, t.projectTaskName, t.projectID, t.taskStart, t.taskDeadline, t.projectPhaseID, t.progress, t.status, t.taskStatusID, t.projectTaskTypeID,
		st.taskStatusName, t.Lapsed, t.taskDescription, t.hoursAllocated, t.taskWeighting, t.assigneeID, t.Suspended,
		p.projectPhaseName, p.phaseWorkHrs, p.phaseWeighting, p.billingMilestone,
		pr.projectCode, pr.projectName, pr.caseID, pr.clientID, pr.projectStart, pr.projectClose, pr.projectDeadline, pr.projectOwnerID,
		c.clientName, c.clientCode,
		u.FirstName, u.Surname, CONCAT(u.FirstName, ' ', u.Surname) as assigneeName, u.userInitials

			FROM tija_project_tasks t
			LEFT JOIN tija_project_phases p ON  t.projectPhaseID = p.projectPhaseID
			LEFT JOIN tija_projects pr ON t.projectID = pr.projectID
			LEFT JOIN tija_task_status st ON  t.status = st.taskStatusID
			LEFT JOIN tija_clients c ON pr.clientID = c.clientID
			LEFT JOIN people u ON t.assigneeID = u.ID


			{$where}
			 ORDER BY  p.projectID ASC";

		 $rows = $DBConn->fetch_all_rows($query,$params);
		 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

   /*Task Status
	=================================*/
	public static function task_status ($whereArr, $single, $DBConn) {
		$cols = array('taskStatusID', 'DateAdded', 'taskStatusName', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_task_status', $cols, $whereArr);
	return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}


   /*==========================================
	 To Do Sales Activities
	  ===========================================*/

	public static function sale_activities($whereArr, $single, $DBConn) {
		$cols = array('saleActivityID ', 'DateAdded', 'saleID','activityName','activityTypeID','deadlineDate', 'startDate', 'activityCategory', 'activityOwnerID', 'description', 'activityStatus', 'closeDate', 'Lapsed', 'Suspended');

		$rows= $DBConn->retrieve_db_table_rows('sbsl_sale_activities', $cols, $whereArr);
	 return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

		/*=====================================
	TASK ASSIGNMENT AND DELEGATION
	======================================*/

	public static function assigned_task( $whereArr, $single, $DBConn) {
		$cols = array('assignmentTaskID', 'DateAdded', 'userID','projectID', 'projectTaskID', 'assignmentStatus', 'Suspended', 'Lapsed');
		$rows = $DBConn->retrieve_db_table_rows_eval ('tija_assigned_project_tasks', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}


	public static function task_user_assignment($whereArr, $single, $DBConn){
		$where = '';
		$params = array();
		$assignmentCols = array(
			'assignmentTaskID',
			'DateAdded',
			'userID',
			'projectID',
			'projectTaskID',
			'assignmentStatus',
			'Suspended',
			'Lapsed');
		$projectTasksCols = array('projectTaskID', 'projectTaskCode', 'projectTaskName', 'taskStart', 'taskDeadline', 'projectPhaseID', 'progress', 'status', 'taskStatusID', 'taskDescription', 'hoursAllocated', 'taskWeighting');
		$projectsCols = array('projectID', 'projectCode', 'projectName', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'projectOwnerID', 'billable', 'billingRateID', 'billableRateValue', 'roundingoff', 'roundingInterval', 'businessUnitID', 'projectValue', 'approval', 'projectStatus', 'allocatedWorkHours');
		$clientsCols = array('clientID', 'clientName', 'clientCode', 'accountOwnerID', 'vatNumber', 'clientDescription', 'clientIndustryID', 'clientSectorID', 'clientLevelID');
		$peopleCols = array('ID', 'FirstName', 'Surname', 'userInitials');
		$taskStatusCols = array('taskStatusID', 'taskStatusName');

		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
			if (in_array($col, $assignmentCols)) {
					$where .= "a.{$col} = ?";
					$params[] = array($val, 'a');
				} elseif (in_array($col, $projectTasksCols)) {
					$where .= "t.{$col} = ?";
					$params[] = array($val, 't');
				} elseif (in_array($col, $projectsCols)) {
					$where .= "pr.{$col} = ?";
					$params[] = array($val, 'pr');
				} elseif (in_array($col, $clientsCols)) {
					$where .= "c.{$col} = ?";
					$params[] = array($val, 'c');
				} elseif (in_array($col, $peopleCols)) {
					$where .= "p.{$col} = ?";
					$params[] = array($val, 'p');
				} elseif (in_array($col, $taskStatusCols)) {
					$where .= "ts.{$col} = ?";
					$params[] = array($val, 'ts');
				}
				// $where .= "a.{$col} = ?";
				// $params[] = array($val, 'a');
				$i++;
			}
		}

		$query = "SELECT a.*,
		p.FirstName, p.Surname, CONCAT(p.FirstName, ' ', p.Surname) AS assigneeName, p.userInitials,
		t.projectTaskCode, t.projectTaskName,  t.taskStart, t.taskDeadline, t.projectPhaseID, t.progress, t.status, t.taskStatusID, t.taskDescription, t.hoursAllocated, t.taskWeighting,
		pr.projectCode, pr.projectName, pr.clientID, pr.projectStart, pr.projectClose, pr.projectDeadline, pr.projectOwnerID, pr.billable, pr.billingRateID, pr.billableRateValue, pr.roundingoff, pr.roundingInterval, pr.businessUnitID, pr.projectValue, pr.approval, pr.projectStatus, pr.allocatedWorkHours, c.clientName, c.clientCode, ph.projectPhaseName, ph.phaseWorkHrs, ph.phaseWeighting, ph.billingMilestone,
		c.clientID, c.clientName, c.clientCode, c.accountOwnerID, c.vatNumber, c.clientDescription, c.clientIndustryID, c.clientSectorID, c.clientLevelID,
		ts.taskStatusName,
		u.jobTitleID, jt.jobTitle



		FROM tija_assigned_project_tasks a

		LEFT JOIN  tija_project_tasks t ON a.projectTaskID = t.projectTaskID
		LEFT JOIN people p ON a.userID = p.ID
		LEFT JOIN  tija_projects pr ON a.projectID = pr.projectID
		LEFT JOIN  tija_clients c ON pr.clientID = c.clientID
		LEFT JOIN  tija_project_phases ph ON t.projectPhaseID = ph.projectPhaseID
		LEFT JOIN tija_task_status ts ON  t.taskStatusID = ts.taskStatusID
		LEFT JOIN 	user_details u ON a.userID = u.ID
		LEFT JOIN tija_job_titles jt ON u.jobTitleID = jt.jobTitleID


		{$where}

		ORDER BY a.userID ASC";
		 $rows = $DBConn->fetch_all_rows($query,$params);
		 if ($rows) {
			foreach ($rows as $key => $row) {
				$rows[$key]->taskUser = Core::user_name($row->userID, $DBConn);

			}
		}
	 	return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}
	public static function project_task_assignees($whereArr, $single, $DBConn){
		$where = '';
		$params = array();
		$assignmentCols = array('assignmentTaskID', 'DateAdded', 'userID', 'projectID', 'projectTaskID', 'assignmentStatus', 'Suspended', 'Lapsed');
		$projectTasksCols = array('projectTaskID', 'projectTaskCode', 'projectTaskName', 'taskStart', 'taskDeadline', 'projectPhaseID', 'progress', 'status', 'taskStatusID', 'taskDescription', 'hoursAllocated', 'taskWeighting');
		$projectsCols = array('projectID', 'projectCode', 'projectName', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'projectOwnerID', 'billable', 'billingRateID', 'billableRateValue', 'roundingoff', 'roundingInterval', 'businessUnitID', 'projectValue', 'approval', 'projectStatus', 'allocatedWorkHours');
		$clientsCols = array('clientID', 'clientName', 'clientCode', 'accountOwnerID', 'vatNumber', 'clientDescription', 'clientIndustryID', 'clientSectorID', 'clientLevelID');
		$peopleCols = array('ID', 'FirstName', 'Surname', 'userInitials');
		$taskStatusCols = array('taskStatusID', 'taskStatusName');

		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
			if (in_array($col, $assignmentCols)) {
					$where .= "a.{$col} = ?";
					$params[] = array($val, 'a');
				} elseif (in_array($col, $projectTasksCols)) {
					$where .= "t.{$col} = ?";
					$params[] = array($val, 't');
				} elseif (in_array($col, $projectsCols)) {
					$where .= "pr.{$col} = ?";
					$params[] = array($val, 'pr');
				} elseif (in_array($col, $clientsCols)) {
					$where .= "c.{$col} = ?";
					$params[] = array($val, 'c');
				} elseif (in_array($col, $peopleCols)) {
					$where .= "p.{$col} = ?";
					$params[] = array($val, 'p');
				} elseif (in_array($col, $taskStatusCols)) {
					$where .= "ts.{$col} = ?";
					$params[] = array($val, 'ts');
				}
				// $where .= "a.{$col} = ?";
				// $params[] = array($val, 'a');
				$i++;
			}
		}

		$query = "SELECT a.assignmentTaskID, a.DateAdded, a.userID, a.projectID, a.projectTaskID,  a.assignmentStatus, a.Suspended, a.Lapsed,
		p.FirstName, p.Surname, CONCAT(p.FirstName, ' ', p.Surname) AS assigneeName, p.userInitials,
		t.projectTaskCode, t.projectTaskName,  t.taskStart, t.taskDeadline, t.projectPhaseID, t.progress, t.status, t.taskStatusID, t.taskDescription, t.hoursAllocated, t.taskWeighting,
		pr.projectCode, pr.projectName, pr.clientID, pr.projectStart, pr.projectClose, pr.projectDeadline, pr.projectOwnerID, pr.billable, pr.billingRateID, pr.billableRateValue, pr.roundingoff, pr.roundingInterval, pr.businessUnitID, pr.projectValue, pr.approval, pr.projectStatus, pr.allocatedWorkHours, c.clientName, c.clientCode, ph.projectPhaseName, ph.phaseWorkHrs, ph.phaseWeighting, ph.billingMilestone,
		c.clientID, c.clientName, c.clientCode, c.accountOwnerID, c.vatNumber, c.clientDescription, c.clientIndustryID, c.clientSectorID, c.clientLevelID,
		ts.taskStatusName,
		u.jobTitleID, jt.jobTitle



		FROM tija_assigned_project_tasks a

		LEFT JOIN  tija_project_tasks t ON a.projectTaskID = t.projectTaskID
		LEFT JOIN people p ON a.userID = p.ID
		LEFT JOIN  tija_projects pr ON a.projectID = pr.projectID
		LEFT JOIN  tija_clients c ON pr.clientID = c.clientID
		LEFT JOIN  tija_project_phases ph ON t.projectPhaseID = ph.projectPhaseID
		LEFT JOIN tija_task_status ts ON  t.taskStatusID = ts.taskStatusID
		LEFT JOIN 	user_details u ON a.userID = u.ID
		LEFT JOIN tija_job_titles jt ON u.jobTitleID = jt.jobTitleID


		{$where}

		ORDER BY a.userID ASC";
		 $rows = $DBConn->fetch_all_rows($query,$params);
		 if ($rows) {
			foreach ($rows as $key => $row) {
				$rows[$key]->taskUser = Core::user_name($row->userID, $DBConn);

			}
		}
	 	return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}
	public static function project_subtasks ($whereArr, $single,$DBConn) {
		$cols = array('subtaskID', 'DateAdded', 'projectTaskID', 'subTaskName', 'subTaskStatus', 'subTaskStatusID', 'assignee', 'subtaskDueDate', 'dependencies', 'subTaskDescription', 'subTaskAllocatedWorkHours', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows_eval ('tija_subtasks', $cols, $whereArr);
		return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function project_subtasks_full ($whereArr, $single,$DBConn) {
		$where = '';
		$params = array();
		$tija_subtasks=array('subtaskID', 'DateAdded', 'projectTaskID', 'subTaskName', 'subTaskStatus', 'assignee', 'subtaskDueDate', 'dependencies', 'subTaskDescription', 'subTaskAllocatedWorkHours', 'needsDocuments','Lapsed', 'Suspended');
		$tija_project_tasks=array('projectTaskID', 'DateAdded', 'DateLastUpdated', 'projectTaskCode', 'projectTaskName', 'projectID', 'taskStart', 'taskDeadline', 'taskStatusID', 'projectPhaseID', 'progress', 'status',  'taskDescription', 'hoursAllocated', 'taskWeighting');
		$tija_projects=array('projectID', 'DateAdded', 'DateLastUpdated', 'projectCode', 'projectName','caseID', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'projectOwnerID', 'billable', 'billingRateID', 'billableRateValue', 'roundingoff', 'roundingInterval', 'businessUnitID', 'projectValue', 'approval', 'status','allocatedWorkHours');
		$phaseArray = array('projectPhaseID', 'projectPhaseName', 'phaseWorkHrs', 'phaseWeighting', 'billingMilestone');
		$peopleArray = array('ID', 'FirstName', 'Surname', 'userInitials');
		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				// var_dump($col);
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				if (in_array($col, $tija_subtasks)) {
					$where .= "s.{$col} = ?";
				} elseif (in_array($col, $tija_project_tasks)) {
					$where .= "t.{$col} = ?";
				} elseif (in_array($col, $tija_projects)) {
					$where .= "pr.{$col} = ?";
				} else {
					// Handle unknown columns
					continue;
				}
				// $where .= "t.{$col} = ?";
				$params[] = array($val, 's');
				$i++;
			}
		}
		$query = "SELECT s.subtaskID, s.DateAdded, s.projectTaskID, s.subTaskName, s.subTaskStatus, s.subTaskStatusID, s.assignee, s.subtaskDueDate, s.dependencies, s.subTaskDescription, s.subTaskAllocatedWorkHours, s.needsDocuments, s.Lapsed, s.Suspended, t.projectTaskCode, t.projectTaskName, t.projectID, t.taskStart, t.taskDeadline, t.projectPhaseID, t.progress, t.taskStatusID,  t.status, t.taskDescription, t.hoursAllocated, t.taskWeighting, pr.projectCode, pr.projectName, pr.caseID, pr.clientID, pr.projectStart, pr.projectClose, pr.projectDeadline, pr.projectOwnerID,
		pr.billable, pr.billingRateID, pr.billableRateValue, pr.roundingoff, pr.roundingInterval, pr.businessUnitID, pr.projectValue, pr.approval, pr.allocatedWorkHours,
		ph.projectPhaseID, ph.projectPhaseName, ph.phaseWorkHrs, ph.phaseWeighting, ph.billingMilestone,
		u.ID, u.FirstName, u.Surname, CONCAT(u.FirstName, ' ', u.Surname) AS assigneeName, u.userInitials
		FROM tija_subtasks s
		LEFT JOIN tija_project_tasks t ON s.projectTaskID = t.projectTaskID
		LEFT JOIN tija_projects pr ON t.projectID = pr.projectID
		LEFT JOIN tija_project_phases ph ON t.projectPhaseID = ph.projectPhaseID
		LEFT JOIN people u ON s.assignee = u.ID

		{$where}
		ORDER BY s.assignee ASC";
		$rows = $DBConn->fetch_all_rows($query,$params);

		// var_dump($rows);
		if ($rows) {
			// var_dump($rows);
			foreach ($rows as $key => $row) {
				$rows[$key]->taskUser = Core::user_name($row->assignee, $DBConn);

			}
		}
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}


   /*==========================================
 	PROJECT  EXPENSES
  	===========================================*/

  	public static function project_expenses_mini ($whereArr, $single,$DBConn) {
      $cols = array('expenseID', 'DateAdded', 'expenseTypeID', 'expenseAmount', 'expenseDescription', 'expenseDocuments', 'expenseDate', 'expenseStatus', 'projectID', "userID", 'LastUpdate',  'Lapsed', 'Suspended');

      $rows= $DBConn->retrieve_db_table_rows_eval('tija_project_expenses', $cols, $whereArr);
      return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function project_expenses ($whereArr, $single, $DBConn) {
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
           $where .= "e.{$col} = ?";
           $params[] = array($val, 'e');
           $i++;
        }
     }

     $query= "SELECT  e.expenseID, e.DateAdded, e.expenseTypeID, e.expenseAmount, e.expenseDescription, e.expenseDate, e.expenseStatus, e.expenseDocuments, e.projectID, e.userID As expenseOwnerID, e.LastUpdate, e.Lapsed, e.Suspended, p.projectName, p.clientID, p.projectOwnerID, p.projectValue, et.typeName as expenseTypeName
     FROM tija_project_expenses e

     LEFT JOIN tija_projects p ON  e.projectID = p.projectID
     LEFT JOIN tija_expense_types et ON e.expenseTypeID = et.expenseTypeID
      {$where}
     ORDER BY expenseDate ASC";
     $rows = $DBConn->fetch_all_rows($query, $params);

      if ($rows) {
        foreach ($rows as $key => $row) {
           $rows[$key]->expenseOwner = Core::user_name($row->expenseOwnerID, $DBConn);
           $clientDetails = Client::clients(array("clientID"=> $row->clientID), true, $DBConn);
           // var_dump($clientDetails);
           $rows[$key]->clientName	= $clientDetails->clientName;
        }
     }
     return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /*==========================================
    TASK FILES
    ===========================================*/

    public static function task_files  ($whereArr, $single,$DBConn) {
      $cols = array('taskFileID', 'DateAdded', 'fileURL', 'timelogID', 'userID', 'fileSize', 'fileType',  'Lapsed', 'Suspended');
      $rows= $DBConn->retrieve_db_table_rows('tija_task_files', $cols, $whereArr);
      return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /*==========================================
    PROJECT FILES
    ===========================================*/

    public static function project_files($whereArr, $single, $DBConn) {
        $params = array();
        $where = '';

        // Build WHERE clause
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }

                if ($col == 'projectID') {
                    $where .= "pf.projectID = ?";
                    $params[] = array($val, 'i');
                } elseif ($col == 'taskID') {
                    $where .= "pf.taskID = ?";
                    $params[] = array($val, 'i');
                } elseif ($col == 'Suspended') {
                    $where .= "pf.Suspended = ?";
                    $params[] = array($val, 's');
                } elseif ($col == 'category') {
                    $where .= "pf.category = ?";
                    $params[] = array($val, 's');
                } elseif ($col == 'isPublic') {
                    $where .= "pf.isPublic = ?";
                    $params[] = array($val, 's');
                } elseif ($col == 'fileID') {
                    $where .= "pf.fileID = ?";
                    $params[] = array($val, 'i');
                }
                $i++;
            }
        }

        $sql = "SELECT
                    pf.fileID, pf.projectID, pf.taskID, pf.fileName, pf.fileOriginalName, pf.fileURL,
                    pf.fileType, pf.fileSize, pf.fileMimeType, pf.category, pf.version, pf.uploadedBy,
                    pf.description, pf.isPublic, pf.downloadCount, pf.DateAdded, pf.LastUpdate, pf.Suspended,
                    CONCAT(u.FirstName, ' ', u.Surname) AS uploaderName,
                    t.projectTaskCode, t.projectTaskName,
                    CONCAT(t.projectTaskCode, ' - ', t.projectTaskName) AS taskName
                FROM tija_project_files pf
                LEFT JOIN people u ON pf.uploadedBy = u.ID
                LEFT JOIN tija_project_tasks t ON pf.taskID = t.projectTaskID
                {$where}
                ORDER BY pf.DateAdded DESC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        if ($rows && is_array($rows)) {
            // Add uploader name to each row
            foreach ($rows as $key => $row) {
                if (!isset($row->uploaderName) || empty($row->uploaderName)) {
                    $rows[$key]->uploaderName = Core::user_name($row->uploadedBy, $DBConn);
                }
            }
        }

        return ($single === true)
            ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false)
            : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

	//  Project progress function
	public static function project_progress($whereArr, $single, $DBConn) {
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
				$where .= "p.{$col} = ?";
				$params[] = array($val, 'p');
				$i++;
			}
		}

		$query = "SELECT p.projectID, p.DateAdded, p.DateLastUpdated, p.projectCode, p.projectName, p.caseID, p.clientID, p.projectStart, p.projectClose, p.projectDeadline, p.projectOwnerID, p.billable, p.billingRateID, p.billableRateValue, p.roundingoff, p.roundingInterval, p.businessUnitID, p.projectValue, p.approval, p.projectStatus,p.allocatedWorkHours, p.orderDate,
			c.clientName,
			u.FirstName as projectOwnerFirstName,
			u.Surname as projectOwnerLastName,
			CONCAT(u.FirstName, ' ', u.Surname) as projectOwnerName,
			b.businessUnitName,
			b.businessUnitDescription,
			SUM(t.hoursAllocated) AS totalHoursAllocated,
			SUM(t.progress) AS totalProgress
			FROM tija_projects p
			LEFT JOIN tija_clients c ON p.clientID = c.clientID
			LEFT JOIN people u ON p.projectOwnerID = u.ID
			LEFT JOIN tija_business_units b ON p.businessUnitID = b.businessUnitID
			LEFT JOIN tija_project_tasks t ON  t.projectID = p.projectID
			GROUP BY 	p.projectID
			HAVING SUM(t.hoursAllocated) > 0
			AND SUM(t.progress) > 0
			AND SUM(t.progress) < 100
			AND SUM(t.hoursAllocated) < (SUM(t.hoursAllocated)*SUM(t.progress)/100)

			{$where}";

			 $rows = $DBConn->fetch_all_rows($query,$params);
			 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function project_total_hours_worked($whereArr, $single, $DBConn) {
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
				$where .= "p.{$col} = ?";
				$params[] = array($val, 'p');
				$i++;
			}
		}

		$query = "SELECT p.projectID, p.DateAdded, p.DateLastUpdated, p.projectCode, p.projectName, p.caseID, p.clientID, p.projectStart, p.projectClose, p.projectDeadline, p.projectOwnerID, p.billable, p.billingRateID, p.billableRateValue, p.roundingoff, p.roundingInterval, p.businessUnitID, p.projectValue, p.approval, p.projectStatus,p.allocatedWorkHours, p.orderDate,
			c.clientName,
			u.FirstName as projectOwnerFirstName,
			u.Surname as projectOwnerLastName,
			CONCAT(u.FirstName, ' ', u.Surname) as projectOwnerName,
			b.businessUnitName,
			b.businessUnitDescription,
			SUM(t.hoursWorked) AS totalHoursWorked
			FROM tija_projects p
			LEFT JOIN tija_clients c ON p.clientID = c.clientID
			LEFT JOIN people u ON p.projectOwnerID = u.ID
			LEFT JOIN tija_business_units b ON p.businessUnitID = b.businessUnitID
			LEFT JOIN tija_timelogs t ON  t.projectID = p.projectID
			GROUP BY 	p.projectID
			HAVING SUM(t.hoursWorked) > 0

			{$where}";

			 $rows = $DBConn->fetch_all_rows($query,$params);
			 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function project_total_hours_allocated($whereArr, $single, $DBConn) {
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
				$where .= "p.{$col} = ?";
				$params[] = array($val, 'p');
				$i++;
			}
		}

		$query = "SELECT p.projectID, p.DateAdded, p.DateLastUpdated, p.projectCode, p.projectName, p.caseID, p.clientID, p.projectStart, p.projectClose, p.projectDeadline, p.projectOwnerID, p.billable, p.billingRateID, p.billableRateValue, p.roundingoff, p.roundingInterval, p.businessUnitID, p.projectValue, p.approval, p.projectStatus,p.allocatedWorkHours, p.orderDate,
			c.clientName,
			u.FirstName as projectOwnerFirstName,
			u.Surname as projectOwnerLastName,
			CONCAT(u.FirstName, ' ', u.Surname) as projectOwnerName,
			b.businessUnitName,
			b.businessUnitDescription,
			SUM(t.hoursAllocated) AS totalHoursAllocated
			FROM tija_projects p
			LEFT JOIN tija_clients c ON p.clientID = c.clientID
			LEFT JOIN people u ON p.projectOwnerID = u.ID
			LEFT JOIN tija_business_units b ON p.businessUnitID = b.businessUnitID
			LEFT JOIN tija_project_tasks t ON  t.projectID = p.projectID
			GROUP BY 	p.projectID
			HAVING SUM(t.hoursAllocated) > 0

			{$where}";

			 $rows = $DBConn->fetch_all_rows($query,$params);
			 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

	}

	public static function billing_rate_type($whereArr, $single, $DBConn) {
		$cols= array(
			'billingRateTypeID',
			'DateAdded',
			'billingRateTypeName',
			'billingRateTypeDescription',
			'LastUpdateByID',
			'LastUpdate',
			'Lapsed',
			'Suspended'
		);
		$rows= $DBConn->retrieve_db_table_rows('tija_billing_rate_types', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}


	public static function billing_rates($whereArr, $single, $DBConn) {
		$cols= array(
			'billingRateID',
			'DateAdded',
			'billingRateName',
			'workTypeID',
			'workCategory',
			'billingRateDescription',
			'doneByID',
			'hourlyRate',
			'billingRateTypeID',
			'LastUpdateByID',
			'LastUpdate',
			'Lapsed',
			'Suspended'
		);
		$rows= $DBConn->retrieve_db_table_rows('tija_billing_rates', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function billing_rate_full($whereArr, $single, $DBConn) {
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
				$where .= "b.{$col} = ?";
				$params[] = array($val, 'b');
				$i++;
			}
		}

		$query= "SELECT b.billingRateID, b.DateAdded, b.billingRateName, b.workTypeID, b.workCategory, b.billingRateDescription, b.doneByID, b.hourlyRate, b.billingRateTypeID, b.bill, b.LastUpdateByID, b.LastUpdate, b.Lapsed, b.Suspended,
			bt.billingRateTypeName,
			w.workTypeName,
			w.workTypeDescription
			FROM tija_billing_rates b
			LEFT JOIN tija_billing_rate_types bt ON  b.billingRateTypeID = bt.billingRateTypeID
			LEFT JOIN tija_work_types w ON  b.workTypeID = w.workTypeID

			{$where}";

			 $rows = $DBConn->fetch_all_rows($query,$params);
			 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

	}


	public static function overtime_multiplier($whereArr, $single, $DBConn) {
		$cols = array('overtimeMultiplierID', 'DateAdded', 'overtimeMultiplierName', "projectID", 'multiplierRate', 'workTypeID', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_overtime_multiplier', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function travel_rate_types ($whereArr, $single, $DBConn) {
		$cols = array('travelRateTypeID', 'DateAdded', 'travelRateTypeName', 'travelRateTypeDescription', 'LastUpdate', 'lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_travel_rate_types', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  public static function product_rate_types ($whereArr, $single, $DBConn) {
		$cols = array('productRateTypeID', 'DateAdded', 'productRateTypeName', 'productRateTypeDescription', 'LastUpdate', 'LastUpdateByID', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_product_rate_types', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  public static function product_rates ($whereArr, $single, $DBConn) {
		$cols = array('productRateID', 'DateAdded', 'productRateName', 'productRateTypeID', 'priceRate', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_product_rates', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }


  public static function product_types($whereArr, $single, $DBConn) {
		$cols = array('productTypeID', 'DateAdded', 'productTypeName', 'productTypeDescription', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_product_types', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  	public static function project_fee_expenses($whereArr, $single, $DBConn) {
		$cols = array(
			'projectFeeExpenseID',
			'DateAdded',
			'productTypeID',
			'projectID',
			'feeCostName',
			'feeCostDescription',
			'productQuantity',
			'productUnit',
			'unitPrice',
			'unitCost',
			'vat',
			'dateOfCost',
			'billable',
			'billingDate',
			'billingFrequency',
			'billingFrequencyUnit',
			'billingStartDate',
			'recurrenceEnd',
			'recurrencyTimes',
			'billingEndDate',
			'billingPhaseID',
			'billingMilestone',
			'billed',
			'LastUpdateByID',
			'LastUpdate',
			'Lapsed',
			'Suspended'
		);
		$rows = $DBConn->retrieve_db_table_rows ('tija_project_fee_expenses', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  	}

  	public static function product_billing_period_levels ($whereArr, $single, $DBConn) {
		$cols = array('productBillingPeriodLevelID', 'DateAdded', 'productBillingPeriodLevelName', 'productBillingPeriodLevelDescription', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_product_billing_period_levels', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  	}

  	public static function project_team($whereArr, $single, $DBConn) {
		$cols = array('projectTeamMemberID ', 'DateAdded', 'projectID', 'userID', 'projectTeamRoleID', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_project_team', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  	}

	public static function project_team_mini($whereArr,  $single=false, $DBConn){
		$params = array();
		$where = '';
		$teamMemberArr = array('projectTeamMemberID', 'DateAdded', 'projectID', 'userID', 'projectTeamRoleID', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');

		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}

				var_dump($col);
				 // Check if the column is in the people table
				if (in_array($col, $teamMemberArr)) {
					$where .= "pt.{$col} = ?";
					$params[] = array($val, 'pt');
				}
				else {
					continue;
				}


				$i++;
			}
		}
		var_dump($whereArr);

		var_dump($where);
		$query= "SELECT pt.projectTeamMemberID , pt.DateAdded, pt.projectID, pt.userID, pt.projectTeamRoleID, pt.LastUpdateByID, pt.LastUpdate, pt.Lapsed, pt.Suspended,
			u.FirstName as teamMemberFirstName,
			u.Surname as teamMemberLastName,
			CONCAT(u.FirstName, ' ', u.Surname) as teamMemberName,
			u.userInitials,
			tr.projectTeamRoleName,
			tr.projectTeamRoleDescription
			FROM tija_project_team pt
			LEFT JOIN people u ON  pt.userID = u.ID
			LEFT JOIN tija_project_team_roles tr ON  pt.projectTeamRoleID = tr.projectTeamRoleID
			{$where}
			ORDER BY u.Surname ASC, u.FirstName ASC";
			echo $query;

			 $rows = $DBConn->fetch_all_rows($query,$params);
			 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	public static function project_team_full($whereArr, $single=false, $DBConn ){
		$where = '';
		$params = array();
		$teamMember = array('projectTeamMemberID', 'DateAdded', 'projectID', 'userID', 'projectTeamRoleID', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$userDetails = array('FirstName', 'Surname', 'ID');
		$projectTeamRoles = array('projectTeamRoleID', 'DateAdded', 'projectTeamRoleName',  'projectTeamRoleDescription');
		$employeeDetails = array('jobTitleID','ID');
		$jobTitles = array('jobTitleID', 'jobTitle');
		$businessUnits = array('businessUnitID', 'businessUnitName', 'businessUnitDescription');
		$projectDetails = array('projectID',  'projectCode', 'projectName', 'clientID', 'projectStart', 'projectClose', 'projectDeadline', 'businessUnitID', 'status');
		$clientDetails = array('clientID', 'clientName', 'clientCode');
		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
				 // Check if the column is in the people table
				if (in_array($col, $teamMember)) {
					$where .= "pt.{$col} = ?";
				} elseif (in_array($col, $userDetails)) {
					$where .= "u.{$col} = ?";
				} elseif (in_array($col, $projectTeamRoles)) {
					$where .= "tr.{$col} = ?";
				} elseif (in_array($col, $employeeDetails)) {
					$where .= "e.{$col} = ?";
				} elseif (in_array($col, $jobTitles)) {
					$where .= "jt.{$col} = ?";
				} elseif (in_array($col, $businessUnits)) {
					$where .= "bu.{$col} = ?";
				} elseif (in_array($col, $projectDetails)) {
					$where .= "p.{$col} = ?";
				} elseif (in_array($col, $clientDetails)) {
					$where .= "c.{$col} = ?";
				}
				else {
					continue;
				}

				$params[] = array($val, 'pt');
				$i++;
			}
		}

		$query= "SELECT pt.projectTeamMemberID , pt.DateAdded, pt.projectID, pt.userID, pt.projectTeamRoleID, pt.LastUpdateByID, pt.LastUpdate, pt.Lapsed, pt.Suspended,
			u.FirstName as teamMemberFirstName,
			u.Surname as teamMemberLastName,
			CONCAT(u.FirstName, ' ', u.Surname) as teamMemberName,
			u.userInitials,
			tr.projectTeamRoleName,
			tr.projectTeamRoleDescription,
			e.jobTitleID,
			jt.jobTitle,
			bu.businessUnitID,
			bu.businessUnitName,
			bu.businessUnitDescription,
			p.projectID, p.projectCode, p.projectName, p.clientID, p.projectStart, p.projectClose, p.projectDeadline, p.businessUnitID, p.projectStatus,
			c.clientID, c.clientName, c.clientCode

			FROM tija_project_team pt
			LEFT JOIN people u ON  pt.userID = u.ID
			LEFT JOIN tija_project_team_roles tr ON  pt.projectTeamRoleID = tr.projectTeamRoleID
			LEFT JOIN user_details e ON  pt.userID = e.ID
			LEFT JOIN tija_job_titles jt ON  e.jobTitleID = jt.jobTitleID
			LEFT JOIN tija_business_units bu ON  e.businessUnitID = bu.businessUnitID
			LEFT JOIN tija_projects p ON  pt.projectID = p.projectID
			LEFT JOIN tija_clients c ON  p.clientID = c.clientID

			{$where}";

			 $rows = $DBConn->fetch_all_rows($query,$params);
			 return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

	}


	public static function project_team_roles($whereArr, $single, $DBConn) {
		$cols = array('projectTeamRoleID', 'DateAdded', 'projectTeamRoleName',  'projectTeamRoleDescription', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_project_team_roles', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  	}

	public static function project_types($whereArr, $single, $DBConn) {
		$cols = array('projectTypeID', 'DateAdded', 'projectTypeName', 'projectTypeDescription', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_project_types', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  	}
	public static function project_task_types($whereArr, $single, $DBConn) {
		$cols = array('projectTaskTypeID', 'DateAdded', 'projectTaskTypeName', 'projectTaskTypeDescription', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_project_task_types', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  	}

	/* =========================================================
	Project Billing
	========================================================== */

	/**
	 * Get comprehensive project billing information including invoices, time logs, and billing analysis
	 *
	 * @param array $whereArr - Filter conditions
	 * @param boolean $single - Return single record or array
	 * @param object $DBConn - Database connection
	 * @return mixed - Project billing data with invoices, time logs, and analysis
	 */
	public static function project_billings($whereArr, $single, $DBConn) {
		$query = "
			SELECT
				p.projectID,
				p.projectCode,
				p.projectName,
				p.clientID,
				p.projectValue,
				p.billable,
				p.billingRateID,
				p.billableRateValue,
				p.projectStart,
				p.projectClose,
				p.projectDeadline,
				p.projectStatus,
				c.clientName,
				c.clientCode,
				br.billingRateName,
				br.hourlyRate,
				-- Invoice Summary
				COALESCE(inv_stats.total_invoices, 0) as total_invoices,
				COALESCE(inv_stats.total_billed, 0) as total_billed,
				COALESCE(inv_stats.paid_amount, 0) as paid_amount,
				COALESCE(inv_stats.outstanding_amount, 0) as outstanding_amount,
				COALESCE(inv_stats.last_invoice_date, NULL) as last_invoice_date,
				-- Time Log Summary
				COALESCE(time_stats.total_hours, 0) as total_hours_logged,
				COALESCE(time_stats.billable_hours, 0) as billable_hours_logged,
				COALESCE(time_stats.non_billable_hours, 0) as non_billable_hours_logged,
				COALESCE(time_stats.total_time_value, 0) as total_time_value,
				-- Billing Analysis
				CASE
					WHEN p.projectValue > 0 THEN
						ROUND((COALESCE(inv_stats.total_billed, 0) / p.projectValue) * 100, 2)
					ELSE 0
				END as billing_percentage,
				CASE
					WHEN COALESCE(time_stats.billable_hours, 0) > 0 THEN
						ROUND(COALESCE(inv_stats.total_billed, 0) / time_stats.billable_hours, 2)
					ELSE 0
				END as effective_billing_rate,
				-- Project Health
				CASE
					WHEN p.projectValue > 0 AND COALESCE(inv_stats.total_billed, 0) >= p.projectValue THEN 'Fully Billed'
					WHEN p.projectValue > 0 AND COALESCE(inv_stats.total_billed, 0) >= (p.projectValue * 0.8) THEN 'Well Billed'
					WHEN p.projectValue > 0 AND COALESCE(inv_stats.total_billed, 0) >= (p.projectValue * 0.5) THEN 'Partially Billed'
					ELSE 'Under Billed'
				END as billing_status,
				-- Overdue Analysis
				CASE
					WHEN inv_stats.overdue_amount > 0 THEN 'Has Overdue'
					WHEN inv_stats.outstanding_amount > 0 THEN 'Has Outstanding'
					ELSE 'Up to Date'
				END as payment_status
			FROM tija_projects p
			LEFT JOIN tija_clients c ON p.clientID = c.clientID
			LEFT JOIN tija_billing_rates br ON p.billingRateID = br.billingRateID
			-- Invoice Statistics Subquery
			LEFT JOIN (
				SELECT
					i.projectID,
					COUNT(*) as total_invoices,
					SUM(i.totalAmount) as total_billed,
					SUM(CASE WHEN i.invoiceStatusID = 3 THEN i.totalAmount ELSE 0 END) as paid_amount,
					SUM(CASE WHEN i.invoiceStatusID IN (1,2,4,5,7) THEN i.totalAmount ELSE 0 END) as outstanding_amount,
					SUM(CASE WHEN i.invoiceStatusID = 5 THEN i.totalAmount ELSE 0 END) as overdue_amount,
					MAX(i.invoiceDate) as last_invoice_date
				FROM tija_invoices i
				WHERE i.Suspended = 'N'
				GROUP BY i.projectID
			) inv_stats ON p.projectID = inv_stats.projectID
			-- Time Log Statistics Subquery
			LEFT JOIN (
				SELECT
					tl.projectID,
					SUM(tl.taskDurationSeconds / 3600) as total_hours,
					SUM(CASE WHEN tl.billable = 'Y' THEN tl.taskDurationSeconds / 3600 ELSE 0 END) as billable_hours,
					SUM(CASE WHEN tl.billable = 'N' THEN tl.taskDurationSeconds / 3600 ELSE 0 END) as non_billable_hours,
					SUM(CASE WHEN tl.billable = 'Y' THEN (tl.taskDurationSeconds / 3600) * COALESCE(tl.billableRateValue, 100) ELSE 0 END) as total_time_value
				FROM tija_tasks_time_logs tl
				WHERE tl.Suspended = 'N'
				GROUP BY tl.projectID
			) time_stats ON p.projectID = time_stats.projectID
			WHERE 1=1
		";

		$params = array();

		// Add where conditions
		if(isset($whereArr['projectID'])) {
			$query .= " AND p.projectID = ?";
			$params[] = array($whereArr['projectID'], 'p');
		}

		if(isset($whereArr['clientID'])) {
			$query .= " AND p.clientID = ?";
			$params[] = array($whereArr['clientID'], 'p');
		}

		if(isset($whereArr['orgDataID'])) {
			$query .= " AND p.orgDataID = ?";
			$params[] = array($whereArr['orgDataID'], 'p');
		}

		if(isset($whereArr['entityID'])) {
			$query .= " AND p.entityID = ?";
			$params[] = array($whereArr['entityID'], 'p');
		}

		if(isset($whereArr['billable'])) {
			$query .= " AND p.billable = ?";
			$params[] = array($whereArr['billable'], 'p');
		}

		if(isset($whereArr['projectStatus'])) {
			$query .= " AND p.projectStatus = ?";
			$params[] = array($whereArr['projectStatus'], 'p');
		}

		if(isset($whereArr['billing_status'])) {
			$query .= " AND CASE
				WHEN p.projectValue > 0 AND COALESCE(inv_stats.total_billed, 0) >= p.projectValue THEN 'Fully Billed'
				WHEN p.projectValue > 0 AND COALESCE(inv_stats.total_billed, 0) >= (p.projectValue * 0.8) THEN 'Well Billed'
				WHEN p.projectValue > 0 AND COALESCE(inv_stats.total_billed, 0) >= (p.projectValue * 0.5) THEN 'Partially Billed'
				ELSE 'Under Billed'
			END = ?";
			$params[] = array($whereArr['billing_status'], 'p');
		}

		if(isset($whereArr['payment_status'])) {
			$query .= " AND CASE
				WHEN inv_stats.overdue_amount > 0 THEN 'Has Overdue'
				WHEN inv_stats.outstanding_amount > 0 THEN 'Has Outstanding'
				ELSE 'Up to Date'
			END = ?";
			$params[] = array($whereArr['payment_status'], 'p');
		}

		if(isset($whereArr['startDate'])) {
			$query .= " AND p.projectStart >= ?";
			$params[] = array($whereArr['startDate'], 'p');
		}

		if(isset($whereArr['endDate'])) {
			$query .= " AND p.projectClose <= ?";
			$params[] = array($whereArr['endDate'], 'p');
		}

		if(isset($whereArr['Suspended'])) {
			$query .= " AND p.Suspended = ?";
			$params[] = array($whereArr['Suspended'], 'p');
		}

		if(isset($whereArr['billingDate'])) {
			$query .= " AND EXISTS (
				SELECT 1 FROM tija_invoices i
				WHERE i.projectID = p.projectID
				AND i.invoiceDate >= ?
				AND i.Suspended = 'N'
			)";
			$params[] = array($whereArr['billingDate'], 'i');
		}

		// Add ordering
		if(isset($whereArr['orderBy'])) {
			switch($whereArr['orderBy']) {
				case 'billing_percentage':
					$query .= " ORDER BY billing_percentage DESC";
					break;
				case 'total_billed':
					$query .= " ORDER BY total_billed DESC";
					break;
				case 'project_value':
					$query .= " ORDER BY p.projectValue DESC";
					break;
				case 'last_invoice':
					$query .= " ORDER BY last_invoice_date DESC";
					break;
				default:
					$query .= " ORDER BY p.projectName ASC";
			}
		} else {
			$query .= " ORDER BY p.projectName ASC";
		}

		$rows = $DBConn->fetch_all_rows($query, $params);

		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	/* =========================================================
	Error Logging Methods
	========================================================== */

	/**
	 * Log Error
	 *
	 * Logs an error message to the error log with context information.
	 *
	 * @param string $message Error message
	 * @param string $context Context where error occurred (e.g., 'project_plan_data')
	 * @param array $data Additional data to include in log
	 * @param int $level Error level (1=INFO, 2=WARNING, 3=ERROR, 4=CRITICAL)
	 * @since 3.0.0
	 */
	public static function logError($message, $context = 'projects', $data = [], $level = 3) {
		$timestamp = date('Y-m-d H:i:s');
		$levelText = self::getErrorLevelText($level);
		$logMessage = "[{$timestamp}] [{$levelText}] [{$context}] {$message}";

		// Add additional data if provided
		if (!empty($data)) {
			$logMessage .= " | Data: " . json_encode($data);
		}

		// Add stack trace for errors and critical issues
		if ($level >= 3) {
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
			$logMessage .= " | Trace: " . json_encode($trace);
		}

		// Log to error log
		error_log($logMessage);

		// Also log to custom log file if configured
		self::logToCustomFile($logMessage, $context);
	}

	/**
	 * Log Info
	 *
	 * Logs an informational message.
	 *
	 * @param string $message Info message
	 * @param string $context Context where message occurred
	 * @param array $data Additional data to include in log
	 * @since 3.0.0
	 */
	public static function logInfo($message, $context = 'projects', $data = []) {
		self::logError($message, $context, $data, 1);
	}

	/**
	 * Log Warning
	 *
	 * Logs a warning message.
	 *
	 * @param string $message Warning message
	 * @param string $context Context where warning occurred
	 * @param array $data Additional data to include in log
	 * @since 3.0.0
	 */
	public static function logWarning($message, $context = 'projects', $data = []) {
		self::logError($message, $context, $data, 2);
	}

	/**
	 * Log Critical Error
	 *
	 * Logs a critical error message.
	 *
	 * @param string $message Critical error message
	 * @param string $context Context where critical error occurred
	 * @param array $data Additional data to include in log
	 * @since 3.0.0
	 */
	public static function logCritical($message, $context = 'projects', $data = []) {
		self::logError($message, $context, $data, 4);
	}

	/**
	 * Get Error Level Text
	 *
	 * Converts error level number to text.
	 *
	 * @param int $level Error level
	 * @return string Error level text
	 * @since 3.0.0
	 */
	private static function getErrorLevelText($level) {
		$levels = [
			1 => 'INFO',
			2 => 'WARNING',
			3 => 'ERROR',
			4 => 'CRITICAL'
		];

		return isset($levels[$level]) ? $levels[$level] : 'UNKNOWN';
	}

	/**
	 * Log to Custom File
	 *
	 * Logs message to a custom log file if configured.
	 *
	 * @param string $message Log message
	 * @param string $context Context for file naming
	 * @since 3.0.0
	 */
	private static function logToCustomFile($message, $context) {
		// Define log directory (adjust path as needed)
		$logDir = __DIR__ . '/../logs/';

		// Create log directory if it doesn't exist
		if (!is_dir($logDir)) {
			mkdir($logDir, 0755, true);
		}

		// Create log file name based on context and date
		$logFile = $logDir . $context . '_' . date('Y-m-d') . '.log';

		// Write to log file
		file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
	}

	/**
	 * Log Database Error
	 *
	 * Logs database-related errors with specific context.
	 *
	 * @param string $message Error message
	 * @param string $query SQL query that caused error
	 * @param array $params Query parameters
	 * @param string $context Context where error occurred
	 * @since 3.0.0
	 */
	public static function logDatabaseError($message, $query = '', $params = [], $context = 'projects') {
		$data = [
			'query' => $query,
			'params' => $params,
			'type' => 'database_error'
		];

		self::logError($message, $context, $data, 3);
	}

	/**
	 * Log Validation Error
	 *
	 * Logs validation-related errors.
	 *
	 * @param string $message Validation error message
	 * @param array $validationData Data that failed validation
	 * @param string $context Context where validation occurred
	 * @since 3.0.0
	 */
	public static function logValidationError($message, $validationData = [], $context = 'projects') {
		$data = [
			'validation_data' => $validationData,
			'type' => 'validation_error'
		];

		self::logError($message, $context, $data, 2);
	}

	/**
	 * Log Performance Issue
	 *
	 * Logs performance-related issues.
	 *
	 * @param string $message Performance issue message
	 * @param float $executionTime Execution time in seconds
	 * @param string $operation Operation that was slow
	 * @param string $context Context where performance issue occurred
	 * @since 3.0.0
	 */
	public static function logPerformanceIssue($message, $executionTime, $operation = '', $context = 'projects') {
		$data = [
			'execution_time' => $executionTime,
			'operation' => $operation,
			'type' => 'performance_issue'
		];

		self::logError($message, $context, $data, 2);
	}

	/**
	 * Log Security Event
	 *
	 * Logs security-related events.
	 *
	 * @param string $message Security event message
	 * @param array $securityData Security-related data
	 * @param string $context Context where security event occurred
	 * @since 3.0.0
	 */
	public static function logSecurityEvent($message, $securityData = [], $context = 'projects') {
		$data = [
			'security_data' => $securityData,
			'type' => 'security_event'
		];

		self::logError($message, $context, $data, 3);
	}

	/**
	 * Get Log Statistics
	 *
	 * Gets statistics about logged errors for a specific context and date range.
	 *
	 * @param string $context Context to get statistics for
	 * @param string $startDate Start date (Y-m-d format)
	 * @param string $endDate End date (Y-m-d format)
	 * @return array Log statistics
	 * @since 3.0.0
	 */
	public static function getLogStatistics($context = 'projects', $startDate = null, $endDate = null) {
		$logDir = __DIR__ . '/../logs/';
		$stats = [
			'total_entries' => 0,
			'info_count' => 0,
			'warning_count' => 0,
			'error_count' => 0,
			'critical_count' => 0,
			'by_date' => []
		];

		if (!is_dir($logDir)) {
			return $stats;
		}

		// Set default date range if not provided
		if (!$startDate) {
			$startDate = date('Y-m-d', strtotime('-7 days'));
		}
		if (!$endDate) {
			$endDate = date('Y-m-d');
		}

		// Process log files in date range
		$currentDate = $startDate;
		while ($currentDate <= $endDate) {
			$logFile = $logDir . $context . '_' . $currentDate . '.log';

			if (file_exists($logFile)) {
				$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$dayStats = [
					'date' => $currentDate,
					'total' => count($lines),
					'info' => 0,
					'warning' => 0,
					'error' => 0,
					'critical' => 0
				];

				foreach ($lines as $line) {
					$stats['total_entries']++;
					$dayStats['total']++;

					if (strpos($line, '[INFO]') !== false) {
						$stats['info_count']++;
						$dayStats['info']++;
					} elseif (strpos($line, '[WARNING]') !== false) {
						$stats['warning_count']++;
						$dayStats['warning']++;
					} elseif (strpos($line, '[ERROR]') !== false) {
						$stats['error_count']++;
						$dayStats['error']++;
					} elseif (strpos($line, '[CRITICAL]') !== false) {
						$stats['critical_count']++;
						$dayStats['critical']++;
					}
				}

				$stats['by_date'][] = $dayStats;
			}

			$currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
		}

		return $stats;
	}

	/**
	 * Clear Old Logs
	 *
	 * Clears log files older than specified days.
	 *
	 * @param int $days Number of days to keep logs
	 * @param string $context Context to clear logs for (null for all)
	 * @return int Number of files deleted
	 * @since 3.0.0
	 */
	public static function clearOldLogs($days = 30, $context = null) {
		$logDir = __DIR__ . '/../logs/';
		$deletedCount = 0;
		$cutoffDate = date('Y-m-d', strtotime("-{$days} days"));

		if (!is_dir($logDir)) {
			return $deletedCount;
		}

		$files = glob($logDir . '*.log');

		foreach ($files as $file) {
			$fileName = basename($file);

			// Skip if context is specified and file doesn't match
			if ($context && strpos($fileName, $context . '_') !== 0) {
				continue;
			}

			// Extract date from filename
			if (preg_match('/(\d{4}-\d{2}-\d{2})\.log$/', $fileName, $matches)) {
				$fileDate = $matches[1];

				if ($fileDate < $cutoffDate) {
					if (unlink($file)) {
						$deletedCount++;
					}
				}
			}
		}

		return $deletedCount;
	}

	// ============================================================================
	// ENHANCED LEAVE MANAGEMENT METHODS
	// ============================================================================

	/**
	 * Get project manager for a specific project
	 *
	 * @param int $projectID Project ID
	 * @param object $DBConn Database connection object
	 * @return mixed Project manager details or false on failure
	 */
	public static function get_project_manager($projectID, $DBConn) {
		$sql = "SELECT p.projectManagerID,
				u.ID, u.FirstName, u.Surname, u.Email,
				ud.jobTitleID, jt.jobTitle
				FROM tija_projects p
				LEFT JOIN people u ON p.projectManagerID = u.ID
				LEFT JOIN user_details ud ON u.ID = ud.ID
				LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
				WHERE p.projectID = ?
				AND p.Lapsed = 'N'
				AND p.Suspended = 'N'
				AND ud.Lapsed = 'N'
				AND ud.Suspended = 'N'";

		$params = array(array($projectID, 'i'));
		$rows = $DBConn->fetch_all_rows($sql, $params);

		return ($rows && count($rows) > 0) ? $rows[0] : false;
	}

	/**
	 * Get all project managers for an organization
	 *
	 * @param int $orgDataID Organization data ID
	 * @param int $entityID Entity ID
	 * @param object $DBConn Database connection object
	 * @return array Project managers
	 */
	public static function get_all_project_managers($orgDataID, $entityID, $DBConn) {
		$sql = "SELECT DISTINCT p.projectManagerID,
				u.ID, u.FirstName, u.Surname, u.Email,
				ud.jobTitleID, jt.jobTitle
				FROM tija_projects p
				LEFT JOIN people u ON p.projectManagerID = u.ID
				LEFT JOIN user_details ud ON u.ID = ud.ID
				LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
				WHERE ud.orgDataID = ?
				AND ud.entityID = ?
				AND p.projectStatus IN ('Active', 'In Progress', 'Planning')
				AND p.Lapsed = 'N'
				AND p.Suspended = 'N'
				AND ud.Lapsed = 'N'
				AND ud.Suspended = 'N'
				AND p.projectManagerID IS NOT NULL
				ORDER BY u.FirstName, u.Surname ASC";

		$params = array(
			array($orgDataID, 'i'),
			array($entityID, 'i')
		);

		$rows = $DBConn->fetch_all_rows($sql, $params);

		return $rows ? $rows : array();
	}

	/**
	 * Get projects requiring clearance for an employee
	 *
	 * @param int $employeeID Employee ID
	 * @param object $DBConn Database connection object
	 * @return array Projects requiring clearance
	 */
	public static function get_projects_requiring_clearance($employeeID, $DBConn) {
		$sql = "SELECT p.projectID, p.projectName, p.projectCode,
				p.projectManagerID, p.projectStatus,
				CONCAT(pm.FirstName, ' ', pm.Surname) as projectManagerName,
				pa.assignmentID, pa.roleID, pa.startDate, pa.endDate,
				pr.roleName
				FROM tija_projects p
				LEFT JOIN people pm ON p.projectManagerID = pm.ID
				LEFT JOIN tija_project_assignments pa ON p.projectID = pa.projectID
				LEFT JOIN tija_project_roles pr ON pa.roleID = pr.roleID
				WHERE pa.employeeID = ?
				AND p.projectStatus IN ('Active', 'In Progress', 'Planning')
				AND p.Lapsed = 'N'
				AND p.Suspended = 'N'
				AND pa.Lapsed = 'N'
				AND pa.Suspended = 'N'
				AND (pa.endDate IS NULL OR pa.endDate >= CURDATE())
				ORDER BY p.projectName ASC";

		$params = array(array($employeeID, 'i'));
		$rows = $DBConn->fetch_all_rows($sql, $params);

		return $rows ? $rows : array();
	}

	/**
	 * Check if employee has active project assignments
	 *
	 * @param int $employeeID Employee ID
	 * @param object $DBConn Database connection object
	 * @return bool True if employee has active assignments
	 */
	public static function has_active_assignments($employeeID, $DBConn) {
		$sql = "SELECT COUNT(*) as assignmentCount
				FROM tija_project_assignments pa
				LEFT JOIN tija_projects p ON pa.projectID = p.projectID
				WHERE pa.employeeID = ?
				AND p.projectStatus IN ('Active', 'In Progress', 'Planning')
				AND p.Lapsed = 'N'
				AND p.Suspended = 'N'
				AND pa.Lapsed = 'N'
				AND pa.Suspended = 'N'
				AND (pa.endDate IS NULL OR pa.endDate >= CURDATE())";

		$params = array(array($employeeID, 'i'));
		$rows = $DBConn->fetch_all_rows($sql, $params);

		return ($rows && count($rows) > 0) ? ($rows[0]->assignmentCount > 0) : false;
	}

	/**
	 * Get project clearance status for a leave application
	 *
	 * @param int $leaveApplicationID Leave application ID
	 * @param object $DBConn Database connection object
	 * @return array Project clearance statuses
	 */
	public static function get_leave_project_clearances($leaveApplicationID, $DBConn) {
		// Check if the table exists first
		$tableCheck = "SHOW TABLES LIKE 'tija_leave_project_clearances'";
		$tableExists = $DBConn->fetch_all_rows($tableCheck, array());

		if (!$tableExists || count($tableExists) == 0) {
			// Table doesn't exist, return empty array
			return array();
		}

		$sql = "SELECT pc.clearanceID, pc.projectID, pc.projectManagerID,
				p.projectName, p.projectCode,
				CONCAT(pm.FirstName, ' ', pm.Surname) as projectManagerName,
				pc.clearanceStatus, pc.clearanceDate, pc.remarks,
				pc.LastUpdate, pc.LastUpdateByID
				FROM tija_leave_project_clearances pc
				LEFT JOIN tija_projects p ON pc.projectID = p.projectID
				LEFT JOIN people pm ON pc.projectManagerID = pm.ID
				WHERE pc.leaveApplicationID = ?
				ORDER BY p.projectName ASC";

		$params = array(array($leaveApplicationID, 'i'));
		$rows = $DBConn->fetch_all_rows($sql, $params);

		return $rows ? $rows : array();
	}

	/**
	 * Get pending project clearances for a project manager
	 *
	 * @param int $projectManagerID Project manager ID
	 * @param object $DBConn Database connection object
	 * @return array Pending clearances
	 */
	public static function get_pending_project_clearances($projectManagerID, $DBConn) {
		// Check if the table exists first
		$tableCheck = "SHOW TABLES LIKE 'tija_leave_project_clearances'";
		$tableExists = $DBConn->fetch_all_rows($tableCheck, array());

		if (!$tableExists || count($tableExists) == 0) {
			// Table doesn't exist, return empty array
			return array();
		}

		$sql = "SELECT pc.clearanceID, pc.leaveApplicationID, pc.projectID,
				p.projectName, p.projectCode,
				la.employeeID, la.startDate, la.endDate, la.leaveTypeID,
				CONCAT(u.FirstName, ' ', u.Surname) as employeeName,
				lt.leaveTypeName,
				pc.clearanceStatus, pc.clearanceDate, pc.remarks,
				pc.LastUpdate, pc.LastUpdateByID
				FROM tija_leave_project_clearances pc
				LEFT JOIN tija_projects p ON pc.projectID = p.projectID
				LEFT JOIN tija_leave_applications la ON pc.leaveApplicationID = la.leaveApplicationID
				LEFT JOIN people u ON la.employeeID = u.ID
				LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
				WHERE pc.projectManagerID = ?
				AND pc.clearanceStatus = 'Pending'
				AND la.applicationStatus IN ('Pending', 'Approved by Direct Report', 'Approved by Department')
				ORDER BY pc.LastUpdate ASC";

		$params = array(array($projectManagerID, 'i'));
		$rows = $DBConn->fetch_all_rows($sql, $params);

		return $rows ? $rows : array();
	}

	/**
	 * Update project clearance status
	 *
	 * @param int $clearanceID Clearance ID
	 * @param string $status New status
	 * @param string $remarks Remarks
	 * @param int $updatedByID User ID of updater
	 * @param object $DBConn Database connection object
	 * @return bool Success status
	 */
	public static function update_project_clearance($clearanceID, $status, $remarks, $updatedByID, $DBConn) {
		// Check if the table exists first
		$tableCheck = "SHOW TABLES LIKE 'tija_leave_project_clearances'";
		$tableExists = $DBConn->fetch_all_rows($tableCheck, array());

		if (!$tableExists || count($tableExists) == 0) {
			// Table doesn't exist, return false
			return false;
		}

		$updateArr = array(
			'clearanceStatus' => $status,
			'remarks' => $remarks,
			'LastUpdate' => date('Y-m-d H:i:s'),
			'LastUpdateByID' => $updatedByID
		);

		$whereArr = array('clearanceID' => $clearanceID);

		return $DBConn->update_data('tija_leave_project_clearances', $updateArr, $whereArr);
	}

	/* =========================================================
	Recurring Projects
	========================================================== */

	/**
	 * Create a recurring project with recurrence settings
	 *
	 * @param array $projectData - Project data including recurrence settings
	 * @param object $DBConn - Database connection
	 * @return mixed - Project ID on success, false on failure
	 */
	public static function create_recurring_project($projectData, $DBConn) {
		if (!$DBConn) {
			return false;
		}

		// Ensure isRecurring is set
		$projectData['isRecurring'] = 'Y';

		// Insert project
		if (!$DBConn->insert_data('tija_projects', $projectData)) {
			return false;
		}

		$projectID = $DBConn->lastInsertId();

		// Generate initial billing cycles if recurrence settings are provided
		if (isset($projectData['recurrenceType']) && !empty($projectData['recurrenceType'])) {
			self::generate_billing_cycles($projectID, $projectData, $DBConn);
		}

		return $projectID;
	}

	/**
	 * Generate billing cycles for a recurring project
	 *
	 * @param int $projectID - Project ID
	 * @param array $recurrenceData - Recurrence settings
	 * @param object $DBConn - Database connection
	 * @return bool - Success status
	 */
	public static function generate_billing_cycles($projectID, $recurrenceData, $DBConn) {
		if (!$DBConn || !$projectID) {
			return false;
		}

		// Get project details
		$project = self::projects_mini(['projectID' => $projectID], true, $DBConn);
		if (!$project) {
			return false;
		}

		$recurrenceType = $recurrenceData['recurrenceType'] ?? null;
		$recurrenceInterval = intval($recurrenceData['recurrenceInterval'] ?? 1);
		$recurrenceStartDate = $recurrenceData['recurrenceStartDate'] ?? $project->projectStart;
		$recurrenceEndDate = $recurrenceData['recurrenceEndDate'] ?? null;
		$recurrenceCount = isset($recurrenceData['recurrenceCount']) ? intval($recurrenceData['recurrenceCount']) : null;
		$billingCycleAmount = $recurrenceData['billingCycleAmount'] ?? $project->projectValue ?? 0;
		$invoiceDaysBeforeDue = intval($recurrenceData['invoiceDaysBeforeDue'] ?? 7);

		if (!$recurrenceStartDate) {
			$recurrenceStartDate = date('Y-m-d');
		}

		// Get existing cycles to determine next cycle number
		$existingCycles = self::get_billing_cycles(['projectID' => $projectID], false, $DBConn);
		$nextCycleNumber = 1;
		$existingCycleCount = 0;
		if ($existingCycles && is_array($existingCycles)) {
			$maxCycle = 0;
			foreach ($existingCycles as $cycle) {
				if ($cycle->cycleNumber > $maxCycle) {
					$maxCycle = $cycle->cycleNumber;
				}
			}
			$nextCycleNumber = $maxCycle + 1;
			$existingCycleCount = count($existingCycles);
		}

		// If no end date and no recurrence count specified, limit to maximum 12 cycles total
		// This prevents creating unlimited cycles for indefinite recurring projects
		$maxTotalCycles = null;
		if (!$recurrenceEndDate && !$recurrenceCount) {
			$maxTotalCycles = 12;
		}

		$cycles = [];
		$currentDate = new DateTime($recurrenceStartDate);
		$endDate = $recurrenceEndDate ? new DateTime($recurrenceEndDate) : null;
		$cycleCount = 0;

		while (true) {
			// Check if we've reached the end date
			if ($endDate && $currentDate > $endDate) {
				break;
			}

			// Check if we've reached the cycle count limit
			if ($recurrenceCount && $cycleCount >= $recurrenceCount) {
				break;
			}

			// Check if we've reached the maximum total cycles limit (12 when no end date)
			// This ensures we don't exceed 12 total cycles (existing + new) for projects without end dates
			if ($maxTotalCycles !== null && ($existingCycleCount + $cycleCount) >= $maxTotalCycles) {
				break;
			}

			// Calculate cycle dates based on recurrence type
			$cycleStartDate = clone $currentDate;
			$cycleEndDate = clone $currentDate;

			switch ($recurrenceType) {
				case 'weekly':
					$dayOfWeek = intval($recurrenceData['recurrenceDayOfWeek'] ?? $currentDate->format('N'));
					// Adjust to the specified day of week
					$daysToAdd = ($dayOfWeek - $currentDate->format('N') + 7) % 7;
					$cycleStartDate->modify("+{$daysToAdd} days");
					$cycleEndDate = clone $cycleStartDate;
					$cycleEndDate->modify("+" . ($recurrenceInterval * 7 - 1) . " days");
					$currentDate->modify("+" . ($recurrenceInterval * 7) . " days");
					break;

				case 'monthly':
					$dayOfMonth = intval($recurrenceData['recurrenceDayOfMonth'] ?? $currentDate->format('j'));
					$cycleStartDate->setDate($currentDate->format('Y'), $currentDate->format('m'), $dayOfMonth);
					$cycleEndDate = clone $cycleStartDate;
					$cycleEndDate->modify("+" . ($recurrenceInterval) . " months");
					$cycleEndDate->modify("-1 day");
					$currentDate = clone $cycleEndDate;
					$currentDate->modify("+1 day");
					break;

				case 'quarterly':
					$dayOfMonth = intval($recurrenceData['recurrenceDayOfMonth'] ?? $currentDate->format('j'));
					$cycleStartDate->setDate($currentDate->format('Y'), $currentDate->format('m'), $dayOfMonth);
					$cycleEndDate = clone $cycleStartDate;
					$cycleEndDate->modify("+" . ($recurrenceInterval * 3) . " months");
					$cycleEndDate->modify("-1 day");
					$currentDate = clone $cycleEndDate;
					$currentDate->modify("+1 day");
					break;

				case 'annually':
					$dayOfMonth = intval($recurrenceData['recurrenceDayOfMonth'] ?? $currentDate->format('j'));
					$monthOfYear = intval($recurrenceData['recurrenceMonthOfYear'] ?? $currentDate->format('n'));
					$cycleStartDate->setDate($currentDate->format('Y'), $monthOfYear, $dayOfMonth);
					$cycleEndDate = clone $cycleStartDate;
					$cycleEndDate->modify("+" . ($recurrenceInterval) . " years");
					$cycleEndDate->modify("-1 day");
					$currentDate = clone $cycleEndDate;
					$currentDate->modify("+1 day");
					break;

				default:
					// Default to monthly if type not recognized
					$cycleStartDate->setDate($currentDate->format('Y'), $currentDate->format('m'), 1);
					$cycleEndDate = clone $cycleStartDate;
					$cycleEndDate->modify("+1 month");
					$cycleEndDate->modify("-1 day");
					$currentDate = clone $cycleEndDate;
					$currentDate->modify("+1 day");
					break;
			}

			// Calculate billing date and due date
			$billingDate = clone $cycleEndDate;
			$billingDate->modify("-{$invoiceDaysBeforeDue} days");
			$dueDate = clone $cycleEndDate;
			$dueDate->modify("+7 days"); // Default 7 days after cycle end

			// Determine status
			$today = new DateTime();
			$status = 'upcoming';
			if ($cycleStartDate <= $today && $cycleEndDate >= $today) {
				$status = 'active';
			} elseif ($billingDate <= $today && $cycleEndDate >= $today) {
				$status = 'billing_due';
			} elseif ($cycleEndDate < $today) {
				$status = 'overdue';
			}

			$cycles[] = [
				'projectID' => $projectID,
				'cycleNumber' => $nextCycleNumber,
				'cycleStartDate' => $cycleStartDate->format('Y-m-d'),
				'cycleEndDate' => $cycleEndDate->format('Y-m-d'),
				'billingDate' => $billingDate->format('Y-m-d'),
				'dueDate' => $dueDate->format('Y-m-d'),
				'status' => $status,
				'amount' => $billingCycleAmount,
				'hoursLogged' => 0,
				'DateAdded' => date('Y-m-d H:i:s'),
				'Suspended' => 'N'
			];

			$nextCycleNumber++;
			$cycleCount++;

			// Safety limit to prevent infinite loops
			if ($cycleCount > 1000) {
				break;
			}
		}

		// Insert cycles into database
		$cyclesInserted = 0;
		foreach ($cycles as $cycleData) {
			if (!$DBConn->insert_data('tija_recurring_project_billing_cycles', $cycleData)) {
				error_log("Failed to create billing cycle for project {$projectID}: " . print_r($cycleData, true));
				continue;
			}
			$cyclesInserted++;
		}

		error_log("Generated {$cyclesInserted} billing cycles for project {$projectID}");

		// Automatically replicate plan for the first cycle if it's active or upcoming
		if ($cyclesInserted > 0) {
			// Get the first cycle
			$firstCycle = self::get_billing_cycles(
				['projectID' => $projectID, 'cycleNumber' => 1],
				true,
				$DBConn
			);

			if ($firstCycle) {
				// Check if cycle is active or upcoming (within 7 days)
				$cycleStart = new DateTime($firstCycle->cycleStartDate);
				$today = new DateTime();
				$daysUntilStart = $today->diff($cycleStart)->days;

				if ($cycleStart <= $today || $daysUntilStart <= 7) {
					// Replicate plan for first cycle
					error_log("Auto-replicating plan for first billing cycle (ID: {$firstCycle->billingCycleID})");
					try {
						$replicated = self::replicate_plan_for_cycle($projectID, $firstCycle->billingCycleID, $DBConn);
						if ($replicated) {
							error_log("Plan replicated successfully for first cycle");
						} else {
							error_log("Warning: Failed to replicate plan for first cycle - function returned false");
						}
					} catch (Exception $e) {
						error_log("ERROR: Exception while replicating plan for first cycle: " . $e->getMessage());
						error_log("Stack trace: " . $e->getTraceAsString());
						// Continue - don't fail the entire operation
					} catch (Error $e) {
						error_log("ERROR: Fatal error while replicating plan for first cycle: " . $e->getMessage());
						error_log("Stack trace: " . $e->getTraceAsString());
						// Continue - don't fail the entire operation
					}
				}
			}
		}

		return $cyclesInserted > 0;
	}

	/**
	 * Get billing cycles for a project
	 *
	 * @param array $whereArr - Filter conditions
	 * @param boolean $single - Return single record or array
	 * @param object $DBConn - Database connection
	 * @return mixed - Billing cycles data
	 */
	public static function get_billing_cycles($whereArr, $single, $DBConn) {
		if (!$DBConn) {
			return false;
		}

		$cols = array(
			'billingCycleID', 'projectID', 'cycleNumber', 'cycleStartDate', 'cycleEndDate',
			'billingDate', 'dueDate', 'status', 'invoiceDraftID', 'invoiceID',
			'amount', 'hoursLogged', 'notes', 'DateAdded', 'LastUpdate', 'Suspended'
		);

		$rows = $DBConn->retrieve_db_table_rows('tija_recurring_project_billing_cycles', $cols, $whereArr);

		return ($single === true)
			? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false)
			: ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

	/**
	 * Get upcoming billing cycles that are due for billing
	 *
	 * @param array $whereArr - Additional filter conditions
	 * @param object $DBConn - Database connection
	 * @return mixed - Array of billing cycles due for billing
	 */
	public static function get_upcoming_billing_cycles($whereArr = [], $DBConn = null) {
		if (!$DBConn) {
			return false;
		}

		$today = date('Y-m-d');
		$whereArr['billingDate'] = $today;
		$whereArr['status'] = 'billing_due';
		$whereArr['Suspended'] = 'N';

		return self::get_billing_cycles($whereArr, false, $DBConn);
	}

	/**
	 * Get the billing cycle for a project on a specific date
	 *
	 * @param int $projectID - Project ID
	 * @param string $date - Date (Y-m-d format)
	 * @param object $DBConn - Database connection
	 * @return mixed - Billing cycle object or false if not found
	 */
	public static function get_billing_cycle_for_date($projectID, $date, $DBConn) {
		if (!$DBConn || !$projectID || !$date) {
			return false;
		}

		// Check if project is recurring
		$project = self::projects_mini(['projectID' => $projectID], true, $DBConn);
		if (!$project) {
			return false;
		}

		// Check if project has isRecurring column and it's set to 'Y'
		$isRecurring = false;
		if (isset($project->isRecurring) && $project->isRecurring === 'Y') {
			$isRecurring = true;
		} elseif (isset($project->projectType) && $project->projectType === 'recurrent') {
			$isRecurring = true;
		}

		if (!$isRecurring) {
			return false;
		}

		// Find the billing cycle that contains this date
		$sql = "SELECT * FROM tija_recurring_project_billing_cycles
				WHERE projectID = ?
				AND ? BETWEEN cycleStartDate AND cycleEndDate
				AND Suspended = 'N'
				ORDER BY cycleNumber ASC
				LIMIT 1";

		$params = array(
			array($projectID, 'i'),
			array($date, 's')
		);

		$rows = $DBConn->fetch_all_rows($sql, $params);

		if ($rows && count($rows) > 0) {
			return is_object($rows[0]) ? $rows[0] : (object)$rows[0];
		}

		return false;
	}

	/**
	 * Create a single billing cycle
	 *
	 * @param array $cycleData - Billing cycle data
	 * @param object $DBConn - Database connection
	 * @return mixed - Cycle ID on success, false on failure
	 */
	public static function create_billing_cycle($cycleData, $DBConn) {
		if (!$DBConn) {
			return false;
		}

		if (!isset($cycleData['DateAdded'])) {
			$cycleData['DateAdded'] = date('Y-m-d H:i:s');
		}

		if (!isset($cycleData['Suspended'])) {
			$cycleData['Suspended'] = 'N';
		}

		if (!$DBConn->insert_data('tija_recurring_project_billing_cycles', $cycleData)) {
			return false;
		}

		return $DBConn->lastInsertId();
	}

	/**
	 * Update billing cycle status
	 *
	 * @param int $billingCycleID - Billing cycle ID
	 * @param string $status - New status
	 * @param array $additionalData - Additional fields to update
	 * @param object $DBConn - Database connection
	 * @return bool - Success status
	 */
	public static function update_billing_cycle_status($billingCycleID, $status, $additionalData = [], $DBConn = null) {
		if (!$DBConn || !$billingCycleID) {
			return false;
		}

		$updateArr = array_merge([
			'status' => $status,
			'LastUpdate' => date('Y-m-d H:i:s')
		], $additionalData);

		$whereArr = ['billingCycleID' => $billingCycleID];

		return $DBConn->update_data('tija_recurring_project_billing_cycles', $updateArr, $whereArr);
	}

	/**
	 * Get time logs for a specific billing cycle
	 *
	 * @param int $billingCycleID - Billing cycle ID
	 * @param object $DBConn - Database connection
	 * @return mixed - Array of time logs
	 */
	public static function get_cycle_time_logs($billingCycleID, $DBConn) {
		if (!$DBConn || !$billingCycleID) {
			return false;
		}

		// Use TimeAttendance class if available
		if (class_exists('TimeAttendance')) {
			return TimeAttendance::project_tasks_time_logs_full(
				['billingCycleID' => $billingCycleID, 'Suspended' => 'N'],
				false,
				$DBConn
			);
		}

		// Fallback to direct query
		$sql = "SELECT * FROM tija_tasks_time_logs
				WHERE billingCycleID = ? AND Suspended = 'N'
				ORDER BY taskDate DESC, startTime ASC";
		$params = [array($billingCycleID, 'i')];
		$rows = $DBConn->fetch_all_rows($sql, $params);

		return $rows ? $rows : false;
	}

	/**
	 * Replicate project plan for a billing cycle
	 *
	 * @param int $projectID - Project ID
	 * @param int $billingCycleID - Billing cycle ID
	 * @param object $DBConn - Database connection
	 * @return bool - Success status
	 */
    public static function replicate_plan_for_cycle($projectID, $billingCycleID, $DBConn) {
        if (!$projectID || !$billingCycleID || !$DBConn) {
            return false;
        }

        // Include the plan manager script
        $planManagerPath = __DIR__ . '/../scripts/projects/recurring_project_plan_manager.php';
        if (file_exists($planManagerPath)) {
            // Store current DBConn in global scope temporarily for the included file
            $GLOBALS['DBConn'] = $DBConn;
            // Define constant to prevent re-including files
            if (!defined('TIJA_INCLUDES_LOADED')) {
                define('TIJA_INCLUDES_LOADED', true);
            }
            require_once $planManagerPath;
            if (function_exists('replicate_plan_for_cycle')) {
                try {
                    $result = replicate_plan_for_cycle($projectID, $billingCycleID, $DBConn);
                    // Don't unset - keep it for the calling script
                    // unset($GLOBALS['DBConn']);
                    return $result;
                } catch (Exception $e) {
                    error_log("Error replicating plan for cycle: " . $e->getMessage());
                    // Don't unset - keep it for the calling script
                    // unset($GLOBALS['DBConn']);
                    return false;
                }
            }
            // Don't unset - keep it for the calling script
            // unset($GLOBALS['DBConn']);
        }

        return false;
    }

	/**
	 * Store project plan as template for recurring project
	 *
	 * @param int $projectID - Project ID
	 * @param object $DBConn - Database connection
	 * @return bool - Success status
	 */
	public static function store_recurring_project_plan_template($projectID, $DBConn) {
		if (!$projectID || !$DBConn) {
			return false;
		}

		// Include the plan manager script
		$planManagerPath = __DIR__ . '/../scripts/projects/recurring_project_plan_manager.php';
		if (file_exists($planManagerPath)) {
			// Store DBConn in global scope for the included file
			$GLOBALS['DBConn'] = $DBConn;
			// Define constant to prevent re-including files
			if (!defined('TIJA_INCLUDES_LOADED')) {
				define('TIJA_INCLUDES_LOADED', true);
			}
			require_once $planManagerPath;
			if (function_exists('store_recurring_project_plan_template')) {
				return store_recurring_project_plan_template($projectID, $DBConn);
			}
		}

		return false;
	}

	/**
	 * Activate billing cycle and replicate plan
	 *
	 * @param int $billingCycleID - Billing cycle ID
	 * @param object $DBConn - Database connection
	 * @return array - Result with success status and details
	 */
	public static function activate_billing_cycle($billingCycleID, $DBConn) {
		if (!$billingCycleID || !$DBConn) {
			return array('success' => false, 'message' => 'Invalid parameters');
		}

		// Include the plan manager script
		$planManagerPath = __DIR__ . '/../scripts/projects/recurring_project_plan_manager.php';
		if (file_exists($planManagerPath)) {
			require_once $planManagerPath;
			if (function_exists('activate_billing_cycle')) {
				return activate_billing_cycle($billingCycleID, $DBConn);
			}
		}

		return array('success' => false, 'message' => 'Plan manager not available');
	}

	/**
	 * Calculate billable amount for a billing cycle
	 *
	 * @param int $billingCycleID - Billing cycle ID
	 * @param object $DBConn - Database connection
	 * @return array - Calculation results
	 */
	public static function calculate_cycle_billing($billingCycleID, $DBConn) {
		if (!$DBConn || !$billingCycleID) {
			return false;
		}

		// Get cycle details
		$cycle = self::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
		if (!$cycle) {
			return false;
		}

		// Get time logs for this cycle
		$timeLogs = self::get_cycle_time_logs($billingCycleID, $DBConn);

		$totalHours = 0;
		$billableHours = 0;
		$totalAmount = 0;

		if ($timeLogs && is_array($timeLogs)) {
			foreach ($timeLogs as $log) {
				$hours = isset($log->taskDuration) ? floatval($log->taskDuration) : 0;
				if (isset($log->workHours)) {
					$hours = floatval($log->workHours);
				} elseif (isset($log->startTime) && isset($log->endTime)) {
					// Calculate hours from start/end time
					$start = strtotime($log->startTime);
					$end = strtotime($log->endTime);
					$hours = ($end - $start) / 3600;
				}

				$totalHours += $hours;

				if (isset($log->billable) && $log->billable == 'Y') {
					$billableHours += $hours;
					$rate = isset($log->billableRateValue) ? floatval($log->billableRateValue) : 0;
					$totalAmount += $hours * $rate;
				}
			}
		}

		// Use cycle amount if set, otherwise use calculated amount
		$finalAmount = floatval($cycle->amount) > 0 ? floatval($cycle->amount) : $totalAmount;

		return [
			'billingCycleID' => $billingCycleID,
			'totalHours' => round($totalHours, 2),
			'billableHours' => round($billableHours, 2),
			'calculatedAmount' => round($totalAmount, 2),
			'cycleAmount' => round($finalAmount, 2),
			'timeLogCount' => is_array($timeLogs) ? count($timeLogs) : 0
		];
	}

}
