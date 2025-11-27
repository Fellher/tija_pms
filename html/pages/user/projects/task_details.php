<?php 
if ($isValidUser) {
	if (isset($_GET['ptid']) && $_GET['ptid']) {
		$projecttaskID = Utility::clean_string($_GET['ptid']);
		$getString = "s={$s}&ss={$ss}&p={$p}&tid={$projecttaskID}";

		// Fetch task details and related data
		$taskDetails = Projects::projects_tasks(['projectTaskID' => $projecttaskID], true, $DBConn);
		$taskUserAssignments = Projects::task_user_assignment(['projectTaskID' => $projecttaskID, 'Suspended' => 'N'], false, $DBConn);
		$taskLogs = TimeAttendance::project_tasks_time_logs_full(['projectTaskID' => $projecttaskID, 'Suspended' => 'N'], false, $DBConn);
		$workType = Data::work_types(['Suspended' => 'N'], false, $DBConn);
		$taskStatusList = Projects::task_status([], false, $DBConn);

		// var_dump($taskStatusList);

		// Date formatting
		$taskStart = date_create($taskDetails->taskStart);
		$taskEnd = date_create($taskDetails->taskDeadline);
		$startDate = date_create($taskDetails->taskStart);
		$allEmployees = Data::users([], false, $DBConn);
		// var_dump($allEmployees);

		// Render the task details
		renderTaskDetails($taskDetails, $taskUserAssignments, $taskStart, $taskEnd, $taskLogs, $workType, $taskStatusList, $startDate, $DBConn, $base, $s, $allEmployees, $ss);
	} else {
		Alert::danger('Please select a project task to view project details.');
	}
} else {
	Alert::warning("You need to be logged in as a valid administrator to view this page.");
}

function renderTaskDetails($taskDetails, $taskUserAssignments, $taskStart, $taskEnd, $taskLogs, $workType, $taskStatusList, $startDate, $DBConn, $base, $s, $allEmployees, $ss) {
	$dt = date_create($taskDetails->taskStart);
	?>
	<div class="bg-light col-12 border-top border-bottom mt-3">
		<div class="container-fluid">
			<div class="row nogutters d-flex align-items-center py-3 justify-content-between">
				<div class=" d-flex align-items-center"> 
					<div class="row">
					<span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
						<i class="uil uil-clipboard-alt fs-22"></i>
					</span>							
						<div class="col-sm">
							<h3 class="mb-0 t500"><?= $taskDetails->projectPhaseName ?><span class="ms-5 font-16">
								(Project : <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&id={$taskDetails->projectID}" ?>"><?= $taskDetails->projectName; ?></a>)
							</span><br><span class="font-20"><?= $taskDetails->projectTaskName ?></span></h3>
						</div>
					</div>						
				</div>
				<div class="d-flex align-items-center justify-content-end">
					<span class="float-end font-22 font-secondary px-5">
						<?= date_format($dt, 'l, d F Y ') ?>
					</span>
					<span> 
						<a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&pid={$taskDetails->projectID}" ?>" class="btn btn-primary btn-sm">
							<i class="icon-arrow-left2"></i> Back to Project
						</a>
					</span>
				</div>
			</div>
		</div>
	</div>
	<!-- s=user&ss=projects&p=project&pid=1 -->
	
	<div class="container-fluid pt-5">
		<div class="card shadow-lg">
			<div class="card-header">
				<span class="me-4"><?= $taskDetails->projectTaskCode ?></span>
				<span class="fs-18"><?= $taskDetails->projectTaskName ?></span>
				<span class="ms-5 font-16"> (Project : <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&pid={$taskDetails->projectID}" ?>"><?= $taskDetails->projectName; ?></a>)</span>
				<span class="ms-5 font-16"> (Phase : <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&pid={$taskDetails->projectID}" ?>"><?= $taskDetails->projectPhaseName; ?></a>)</span> 
				<span class="float-end"><?= $taskStart->format('D, d M Y') . ' to ' . $taskEnd->format('D, d M Y') ?></span>
			</div>		
			<div class="card-body">
				<div class="row bg-light-blue py-2 px-3">
					<div class="col-md-2">Status: <span class="font-primary"><?= $taskDetails->taskStatusName ?></span></div>
					<div class="col-md-2">Hours Allocated: <span class="font-primary"><?= $taskDetails->hoursAllocated; ?> hrs </span></div>
					<div class="col-md-4">
						<span class="text-dark t600 me-4">Assigned Users: </span>
						<?= renderAssignedUsers($taskUserAssignments, $DBConn); ?>
					</div>
					<div class="col-md-4">
						<span class="text-dark t600 me-4">Assigned Weight: </span>
						<?= $taskDetails->taskWeighting; ?> %
					</div>
				</div>
				<div class="row mt-3">
					<div class="fancy-title title-bottom-border">
						<h4 class="pb-0 t400"> Task Logs</h4>
					</div>
					<?php if ($taskLogs) { ?>
						<div class="table-responsive">
							<table id="datatable1" class="table table-striped table-hover table-sm">
								<thead>
									<tr>
										<th>Task Code</th>
										<th> Task Date</th>
										<th>Task Name</th>
										<th>Name</th>
										<th>Work Type</th>
										<th class="text-end">Time Duration</th>
										<th class="text-end">Expenses</th>
										<th class="text-end">Files</th>
										<th>&nbsp;</th>
									</tr>
								</thead>
								<tbody>
									<?= renderTaskLogs($taskLogs); ?>
								</tbody>
								<tfoot>
									<tr class="table-primary">
										<th>Total</th>
										<th></th>
										<th></th>
										<th></th>
										<th class="text-end"><?= Utility::format_time(array_sum(array_map(fn($log) => Utility::time_to_sec($log->taskDuration), $taskLogs)), ":", true) ?></th>
										<th class="text-end"><?= number_format(array_sum(array_column($taskLogs, 'expenses')), 2, '.', ' ') ?></th>
										<th></th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
					<?php } ?>
					<div class="col-md-12">
						<a href="" data-bs-toggle="modal" data-bs-target="#addTaskLog" class="btn btn-primary float-end w-25"><i class="icon-plus mx-3"></i>Add task Time log</a>
					</div>
					<?= renderTaskLogModal($taskDetails, $workType, $startDate, $allEmployees, $taskStatusList, $base); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function renderAssignedUsers($taskUserAssignments, $DBConn) {
	$output = '';
	if ($taskUserAssignments) {
		foreach ($taskUserAssignments as $AssData) {															
			$user_names = Core::user_name($AssData->userID, $DBConn);
			$initials = Utility::generate_initials($user_names);
			$output .= '<a href="#" class="py-0 mx-2" data-bs-toggle="tooltip" data-bs-html="true" title="<em><u>' . $user_names . '</u></em> ">
						<span class="border border-secondary rounded-circle p-1 font-12 mr-3 text-uppercase">' . $initials . '</span>
						</a>';
		}
	}
	return $output;
}

function renderTaskLogs($taskLogs) {
	$output = '';
	foreach ($taskLogs as $logData) {
		$taskDuration = Utility::time_to_sec($logData->taskDuration);
		$output .= '<tr>
						<td>' . $logData->projectTaskCode . '</td>
						<td>' . date_format(date_create($logData->taskDate), 'D\, d M Y') . '</td>
						<td>' . $logData->projectTaskName . '</td>
						<td>' . $logData->logOwnerName . '</td>
						<td>' . $logData->workTypeName . '</td>
						<td class="text-end">' . Utility::format_time($taskDuration, ":", true) . '</td>
						<td class="text-end">' . (isset($logData->expenses) && !empty($logData->expenses) ? $logData->expenses : 0) . '</td>
						<td class="text-end"></td>
						<td class="text-end"><a href=""><i class="icon-cogs float-end"></i></a></td>
					</tr>';
	}
	return $output;
}

function renderTaskLogModal($taskDetails, $workType, $startDate, $allEmployees, $taskStatusList, $base) {
	?>
	<div class="modal fade" id="addTaskLog" tabindex="-1" role="dialog" aria-labelledby="addTaskLogLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addTaskLogLabel">Add Task Time log to <?= $taskDetails->projectTaskName ?></h5>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="<?= "{$base}php/scripts/time_attendance/manage_task_time_log.php" ?>" method="post">
						<input type="hidden" name="projectID" value="<?= $taskDetails->projectID ?>">
						<input type="hidden" name="projectPhaseID" value="<?= $taskDetails->projectPhaseID ?>">
						<input type="hidden" name="projectTaskID" value="<?= $taskDetails->projectTaskID ?>">
						<div class="row">
							<div class="form-group col-md-6">
								<?php //var_dump($allEmployees[4]); ?>
								<label for="user" class="nott mb-0 t500 text-dark">Employee </label>
								<select name="userID" class="form-control form-control-sm form-control-plaintext border-bottom bg-light">
									<?= Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName',  "" , '', "Select User") ?>
								</select>
							</div>
							<div class="form-group col-md-6">
								<label for="workType" class="nott mb-0 t500 text-dark">Work Type</label>
								<select name="workTypeID" id="workTypeID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
									<?= Form::populate_select_element_from_object($workType, 'workTypeID', 'workTypeName', '', '', 'Select:') ?>
									<option value="addNew">New Work Type</option>
								</select>
							</div>
							<div class="form-group col-md-6">
								<label for="taskDate" class="nott mb-0 t500 text-dark">Task Date</label>
								<input type="text" id="date" name="taskDate" value="<?= date_format($startDate, 'Y-m-d') ?>" class="form-control form-control-sm form-control-plaintext bg-light-blue text-left component-datepicker past-enabled" placeholder="YYYY-MM-DD">								
							</div>
							<div class="form-group col-md-6">	
								<label for="form1" class="col-md-12 nott mb-0 t500 text-dark">Task Time Duration</label>
								<input type="text" class="form-control form-control-sm form-control-plaintext bg-light-blue" name="taskDuration" id="taskDuration" placeholder="HH:MM:SS" value="">
								<span id="errorMessage" class="text-red-500 text-xs mt-2 h-4 d-block"></span>
							</div>
							<div class="col-lg-6">
								<label class="col-md-12 nott mb-0 t500 text-dark">Attach Supporting Files:</label><br>
								<input id="input-1" type="file" class="form-control" name="fileAttachments[]" multiple data-show-preview="false">
							</div>
							<div class="col-md-6 form-group">
								<label for="" class="nott t400 mb-0">Task Status</label>
								<select name="taskStatusID" id="taskStatusID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
									<?= Form::populate_select_element_from_object($taskStatusList, 'taskStatusID', 'taskStatusName', '', '', 'Select Task Status'); ?>
								</select>								
							</div>
							<div class="col-md-12">
								<div class="form-group col-md-12">
									<textarea class="form-control borderless_mini" name="taskNarrative" rows="3" placeholder="Task Notes & description"></textarea>
								</div>			
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-success btn-sm">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
}?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	// set the default value for userID to the current user ID
	const userID = <?= isset($userDetails->ID) ? $userDetails->ID : 'null' ?>;
	if (userID) {
		const userSelect = document.querySelector('select[name="userID"]');
		if (userSelect) {
			userSelect.value = userID;
		}
	}

	// set the default value for tasksStatus to 'not started'
	const taskStatusSelect = document.querySelector('select[name="taskStatusID"]');
	if (taskStatusSelect) {
		// Check if the select element has options
		if (taskStatusSelect.options.length > 0) {
			// Set the value to 'not started' if it exists where the text is 'Not Started'
			const notStartedOption = Array.from(taskStatusSelect.options).find(option => option.text.toLowerCase() === 'not started');
			// taskStatusSelect.value = 'not started';
		} else {
			// If no options, log a warning
			console.warn('No options available in taskStatusID select element.');
		}
	}

	// get the input timeduration element and error message element
	const taskDurationInput = document.getElementById('taskDuration');
	const errorMessage = document.getElementById('errorMessage');

	/**
	* Attaches a 'blur' event listener to the time input field.
	* A 'blur' event fires when the user clicks away from the input.
	* This function validates the input and converts it if necessary.
	*/
	taskDurationInput.addEventListener('blur', function () {
		const inputValue = taskDurationInput.value.trim();
		// Get the raw value and trim whitespace
		const value = this.value.trim();

		// clear any previous error message
		errorMessage.textContent = '';

		// Check if the input is empty
		if (value === '') {
			errorMessage.textContent = 'Please enter a time duration.';
			return;
		}

		// Regular expression to check for hh:mm format (e.g., 8:30, 08:30)
		const timeRegex = /^(\d{1,2}):(\d{2})$/;
            const timeMatch = value.match(timeRegex);

            if (timeMatch) {
                // --- Part 1: Handle 'hh:mm' format ---
                const hours = parseInt(timeMatch[1], 10);
                const minutes = parseInt(timeMatch[2], 10);

                // Validate the time ranges
                if (hours >= 24 || minutes >= 60) {
                    errorMessage.textContent = 'Invalid time. Hours must be < 24 and minutes < 60.';
                    this.value = ''; // Clear the invalid input
                } else {
                    // Standardize the format to HH:MM with leading zeros
                    const formattedHours = hours.toString().padStart(2, '0');
                    const formattedMinutes = minutes.toString().padStart(2, '0');
                    this.value = `${formattedHours}:${formattedMinutes}`;
                }

            } else if (!isNaN(parseFloat(value)) && isFinite(value)) {
                // --- Part 2: Handle decimal format ---
                const decimalTime = parseFloat(value);

                // Validate the decimal value range
                if (decimalTime < 0 || decimalTime >= 24) {
                    errorMessage.textContent = 'Decimal value must be between 0 and 23.99.';
                    this.value = ''; // Clear the invalid input
                    return;
                }

                // Convert decimal to hours and minutes
                let hours = Math.floor(decimalTime);
                // Multiply the fractional part by 60 and round to get minutes
                let minutes = Math.round((decimalTime - hours) * 60);

                // Edge case: if rounding minutes results in 60, adjust hours and minutes
                if (minutes === 60) {
                    hours += 1;
                    minutes = 0;
                }
                
                // Final check to ensure calculated hours are not 24 or more
                if (hours >= 24) {
                    errorMessage.textContent = 'Calculated time exceeds 23:59.';
                    this.value = '';
                } else {
                    // Format to HH:MM and update the input field's value
                    const formattedHours = hours.toString().padStart(2, '0');
                    const formattedMinutes = minutes.toString().padStart(2, '0');
                    this.value = `${formattedHours}:${formattedMinutes}`;
                }

            } else {
                // --- Part 3: Handle invalid format ---
                errorMessage.textContent = 'Invalid format. Use hh:mm or a decimal number.';
                this.value = ''; // Clear the invalid input
            }

            
	});

});
</script>