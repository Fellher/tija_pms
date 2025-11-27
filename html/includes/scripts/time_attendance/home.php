<?php
if(!$isValidUser){
   Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
   return;
}
$activeTask = (isset($_GET['ptid']) && !empty($_GET['ptid'])) ? Utility::clean_string($_GET['ptid']) : "";
$initURL="s={$s}&ss={$ss}&p=$p";
$dt = new DateTime;
if (isset($_GET['d']) && !empty($_GET['d']) && preg_match($config['ISODateFormat'], $_GET['d'])) {
   $DOF= Utility::clean_string($_GET['d']);
   $dt=date_create($DOF);
} else {
   if (isset($_GET['year']) && isset($_GET['week'])) {
      $dt->setISODate($_GET['year'], $_GET['week']);
   } else {
      // $dt->setISODate($dt->format('o'), $dt->format('W'));
      $dt = new DateTime;
   }
}

$DOF= $dt->format('Y-m-d');
$year = $dt->format('o');
$week = $dt->format('W');
$month = $dt->format('m');
$userID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) :  $userDetails->ID;
$employeeID= $userID;
$getString = "{$initURL}&d=$DOF";

$userFullDetails= Data::users(['ID'=>$userID], true, $DBConn);
/*===================================
DATES FOR THE WEEK FOR CURRENT WEEK(Week Starts on Monday)
==========================================================*/
$weekArray = Utility::week_array($year, $week);

/*=====================================================================
CLIENT project ARRAY
=====================================================================*/
$filter = array("Suspended"=>"N");


// Retrieve projects that user/employee is a team member (single query instead of duplicate)
$userProjects = Projects::project_team_full(array('userID'=>$userID), false, $DBConn);

// Process user projects and filter valid ones
$projectIDs = array();
$projectsUser = array();
$validUserProjects = array();

if($userProjects){
	foreach ($userProjects as $key => $project) {
		// Check that project is not suspended and not closed
		if($project->Suspended == 'Y' || $project->projectStatus == 'Closed'){
			continue;
		}

		$projectID= $project->projectID;

		// Add to valid projects
		$validUserProjects[] = $project;

		// Add unique projects
		if(!in_array($projectID, $projectIDs)){
			$projectIDs[] = $projectID;
			$projectsUser[] = $project;
		}
	}
} else {
	Alert::error("No project found for this user", true, array('fst-italic', 'text-center', 'font-18'));
}

// Prepare project arrays for dropdowns
$projectClientArray = Workutils::client_projects_array($projectsUser, $DBConn);

// Load reference data in parallel
$allClients = Client::clients(array("Suspended"=>"N"), false, $DBConn);

// Set return URL for form submissions to come back to time_attendance page
$_SESSION['returnURL'] = "s=user&p=time_attendance";
$allCases = Projects::cases(array("Suspended"=>"N"), false, $DBConn);
$projectArray = Workutils::client_project_array($allClients, $DBConn);
$allProjects = Projects::projects_full(array('Suspended'=>'N'), false, $DBConn);
$allSalesCases = Sales::sales_case_mid(array('Suspended'=>'N'), false, $DBConn);
$allBusinessUnits = Data::business_units(array('Suspended'=>'N'), false, $DBConn);

// Variables required by schedule/modals/manage_activity.php (used in activity_display_script.php)
$clients = $allClients; // Alias for compatibility with schedule modal
$projects = $allProjects; // Alias for compatibility with schedule modal
$salesCases = $allSalesCases; // Alias for compatibility with schedule modal
$allEmployees = Employee::employees(array('Suspended'=>'N'), false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');

/* =============================
! ALL REQUIRED ARRAY ACCESSORIES
===============================*/
$workType = Data::work_types ($filter, false,$DBConn);
$tasks= Projects::project_tasks ($filter, false, $DBConn);
$expenseTypes= Expense::expense_types (array_merge($filter, array('isActive' => 'Y')), false, $DBConn);
$projectPhases= Projects::project_phases ($filter, false, $DBConn);


// retrieve projects that user/employee is a team member
// $projectTeamMembers = Projects::project_team_members(array('ID'=>$userID), false, $DBConn);

/*Phase Array with thasks*/
$taskPhase = Workutils::phase_task_array($projectPhases, $DBConn);
$subTasks = Projects::project_subtasks_full(array('Suspended'=>'N'), false, $DBConn);
$taskStatusList= Projects::task_status(array('Suspended'=>'N'), false, $DBConn);

// Build comprehensive task data for search (Client->Project->Phase->Task hierarchy)
$userAssignedTasksDetails = array();

if($validUserProjects) {
	// validUserProjects contains project-level data, we need to expand it with phases and tasks
	foreach($validUserProjects as $project) {
		// Get all phases for this project
		$projectPhases = Projects::project_phases(array('projectID'=>$project->projectID, 'Suspended'=>'N'), false, $DBConn);

		if($projectPhases) {
			foreach($projectPhases as $phase) {
				// Get all tasks for this phase
				$phaseTasks = Projects::project_tasks(array('projectPhaseID'=>$phase->projectPhaseID, 'Suspended'=>'N'), false, $DBConn);

				if($phaseTasks) {
					foreach($phaseTasks as $task) {
						// SIMPLIFIED: Include ALL tasks in phases of projects user is on
						// The project team membership is already filtered via $validUserProjects
						// Exclude only completed/cancelled tasks
						if(isset($task->taskStatusID) && !in_array($task->taskStatusID, array(4, 5))) {
							// Create comprehensive task object with full hierarchy
							$taskWithHierarchy = (object)[
								'clientID' => $project->clientID,
								'clientName' => $project->clientName,
								'projectID' => $project->projectID,
								'projectName' => $project->projectName,
								'projectCode' => isset($project->projectCode) ? $project->projectCode : '',
								'projectPhaseID' => $phase->projectPhaseID,
								'projectPhaseName' => $phase->projectPhaseName,
								'projectTaskID' => $task->projectTaskID,
								'projectTaskName' => $task->projectTaskName,
								'taskDue' => isset($task->taskDue) ? $task->taskDue : (isset($task->taskEnd) ? $task->taskEnd : null),
								'taskStatusID' => $task->taskStatusID,
								'taskStatusName' => isset($task->taskStatusName) ? $task->taskStatusName : '',
								'taskStart' => isset($task->taskStart) ? $task->taskStart : null,
								'hoursAllocated' => isset($task->hoursAllocated) ? $task->hoursAllocated : 0,
								'taskPriority' => isset($task->taskPriority) ? $task->taskPriority : null
							];

							$userAssignedTasksDetails[] = $taskWithHierarchy;
						}
					}
				}
			}
		}
	}
}

// If no tasks were found (likely because all are completed), include completed tasks as a fallback
if (empty($userAssignedTasksDetails) && $validUserProjects) {
    foreach ($validUserProjects as $project) {
        $projectPhasesFallback = Projects::project_phases(array('projectID'=>$project->projectID, 'Suspended'=>'N'), false, $DBConn);
        if ($projectPhasesFallback) {
            foreach ($projectPhasesFallback as $phase) {
                $phaseTasksFallback = Projects::project_tasks(array('projectPhaseID'=>$phase->projectPhaseID, 'Suspended'=>'N'), false, $DBConn);
                if ($phaseTasksFallback) {
                    foreach ($phaseTasksFallback as $task) {
                        // Include ALL tasks (including completed/cancelled) as a fallback so the UI has data
                        $taskWithHierarchy = (object)[
                            'clientID' => $project->clientID,
                            'clientName' => $project->clientName,
                            'projectID' => $project->projectID,
                            'projectName' => $project->projectName,
                            'projectCode' => isset($project->projectCode) ? $project->projectCode : '',
                            'projectPhaseID' => $phase->projectPhaseID,
                            'projectPhaseName' => $phase->projectPhaseName,
                            'projectTaskID' => $task->projectTaskID,
                            'projectTaskName' => $task->projectTaskName,
                            'taskDue' => isset($task->taskDue) ? $task->taskDue : (isset($task->taskEnd) ? $task->taskEnd : (isset($task->taskDeadline) ? $task->taskDeadline : null)),
                            'taskStatusID' => isset($task->taskStatusID) ? $task->taskStatusID : (isset($task->status) ? $task->status : null),
                            'taskStatusName' => isset($task->taskStatusName) ? $task->taskStatusName : '',
                            'taskStart' => isset($task->taskStart) ? $task->taskStart : null,
                            'hoursAllocated' => isset($task->hoursAllocated) ? $task->hoursAllocated : 0,
                            'taskPriority' => isset($task->taskPriority) ? $task->taskPriority : null
                        ];
                        $userAssignedTasksDetails[] = $taskWithHierarchy;
                    }
                }
            }
        }
    }
}
?>

<script type="text/javascript">
   let taskArray=<?php echo json_encode($tasks) ?>,
      phaseArray = <?php echo json_encode($projectPhases); ?>;
		taskActivityArray = <?php echo json_encode($subTasks); ?>;
		userAssignedTasks = <?php echo json_encode($userAssignedTasksDetails); ?>;
</script>
<div class="">
   <div class="bg-light col-12 border-top border-bottom ">
      <div class="row">
         <div class="col-md">
            <h3 class="titular-title font-weight-normal  font-primary font-26 mb-2 mt-1 mx-4 ">Work Management <span class="font-20">Project Task Hour Entry</span> </h3>
         </div>
			<div class="col-md">
				<div class="row">
					<?php
					if ($userID !== $userDetails->ID) {?>
						<div class="col mx-4 mt-2 text-end">
						<span class=" font-18 font-success px-5" > <?php echo Core::user_name($userID, $DBConn); ?> </span> <br>
							<span class=" font-14 font-success px-5" ><?php  echo date_format($dt,'l, d F Y ') ?></span>
						</div>
						<?php
					} else {?>
						<div class="col mx-4 mt-2 text-end">
						<span class=" font-18 font-success px-5" > <?php echo Core::user_name($userID, $DBConn); ?> </span> <br>
							<span class=" font-14 font-success px-5" ><?php  echo date_format($dt,'l, d F Y ') ?></span>
						</div>
						<?php
					} ?>
            </div>
         </div>
      </div>
   </div>
	<?php
	$linksArray = array(
		(object)[
			"title" => "Time & Expense",
			"link" => "time_expense.php",
			"id" => "time_expense",
			"adminlevel" => 4
		],
		(object)[
			"title" => "My Team",
			"link" => "my_team.php",
			"id" => "my_team",
			"adminlevel" => 6
		],
		// array("title" => "Finance Control", "link" => "dashboard.php", "id" => "finance_control", "adminlevel" => 1),
		// (object)[
		// 	"title" => "Finance Analysis",
		// 	"link" => "projects.php",
		// 	"id" => "finance_analysis",
		// 	"adminlevel" => 2
		// ],
		// (object)[
		// 	"title" => "Management",
		// 	"link" => "management.php",
		// 	"id" => "management",
		// 	"adminlevel" => 3
		// ],

		// (object)[
		// 	"title" => "Sales",
		// 	"link" => "reports.php",
		// 	"id" => "sales",
		// 	"adminlevel" => 5
		// ],
	);
	$page = (isset($_GET['page']) && !empty($_GET['page'])) ? Utility::clean_string($_GET['page']) : 'time_expense';
	$getString = str_replace("&uid={$userID}", "", $getString);
	$getString = str_replace("&page={$page}", "", $getString);
	$workTypes = Work::work_types(array('Suspended'=>'N'), false, $DBConn);	?>
	<div class="container-fluid">
		<div class="col-12 px-3">
			<div class="row border shadow-lg  g-0">
				<?php
				if ($linksArray){
					$textColor= '';
					foreach ($linksArray as $key => $link) {
						$active= $page == $link->id ? ' activeDay ' : '';
						$adminLevel= $link->adminlevel;?>
						<a class="font-primary  border-end col-sm dayover " href="<?php echo $base ."html/?{$getString}&page=".$link->id.'&uid='.$userID; ?>" >
							<div class="col-sm text-center py-4 <?php echo $active; ?> <?php echo $textColor ?>" id="<?php echo $link->id ?>">
								<span class=" t400 font-22 weekTot">
									<?php echo $link->title; ?>
								</span>
							</div>
						</a>
						<?php
					}
				}?>
			</div>
		</div>
	</div>
	<?php
	/*=========================================
	get the required page based on selection
	========================================*/
	$validPages = array('time_expense', 'my_team', 'finance_control', 'finance_analysis', 'management', 'sales');
	$employeeDetails = Employee::employees(array('ID'=>$userID), true, $DBConn);
	// update the getString to include userID and page
   $getString.= "&uid={$userID}";
   $getString .= "&page={$page}";

	// check if the page is valid and include the corresponding file
	if (in_array($page, $validPages)) {
		 include "includes/scripts/time_attendance/home/{$page}.php";
	} else {
		Alert::error("Invalid page selected", true, array('fst-italic', 'text-center', 'font-18'));
	}?>
</div>
<?php



echo Utility::form_modal_header("addWorkHours", "time_attendance/manage_task_time_log.php", "Manage Time Log", array('modal-xl', 'modal-dialog-centered'), $base);
//include "includes/scripts/time_attendance/modals/manage_work_hour.php";
include "includes/scripts/time_attendance/manage_work_hour_script_clean.php";
echo Utility::form_modal_footer("Save Task", "manageTsk", 'btn btn-primary btn-sm');

?>

