<div class="row workHourClean">
	<div class="col-md-8">
		<div class="row">
			<fieldset class=" col-md-8">
				<div class="row">
					<div class="form-group col-12	">
						<button class="btn btn-outline-light dropdown-toggle text-wrap   w-100 text-start border-0 border-bottom bg-light-blue rounded-0 selection" type="button"
							id="dropdownMenuClickableInside" data-bs-toggle="dropdown"
							data-bs-auto-close="outside" aria-expanded="false" value="<?php echo $projectID; ?>" >

							Search Project
						</button>
						<div class="col-12  selectedTaskValues text-primary"></div>
						<div class="dropdown-menu w-50 p-3" aria-labelledby="dropdownMenuClickableInside" >
							<h5>Work Hour Select project
								<span class="text-primary font-16 t600"> (Click to select)</span>
								<span class="float-end">
									<button type="button" class="close btn  btn-icon rounded-pill btn-primary-light dropdownClose" aria-label="Close" >
										<i class="fa-solid fa-xmark"></i>
									</button>

								</span>
							</h5>
							<?php
							if($validUserProjects) {
								$validUserProjects= $validUserProjects;
								foreach ($validUserProjects as $projkey => $project) {
									var_dump($project);
										$nodeID.="node_{$project->projectID}";
										$project->projectName= htmlspecialchars($project->projectName);
										$projectPhases = Projects::project_phases(array('projectID'=>$project->projectID, 'Suspended'=>'N'), false, $DBConn);
										$client
										?>
										<div
											class="dropdown-item fs-14 t300  border-bottom pb-0 leafProject"
											data-bs-toggle="collapse"
											href="#projectID_<?php echo $nodeID ?>"
											data-project-id="<?php echo $project->projectID ?>"
											data-project-name= "<?php echo $project->projectName ?>"
											data-client-id="<?php echo $project->clientID ?>"
											data-client-name = "<?php echo $project->clientName ?>"
											>
												<?= "{$project->clientName}: {$project->projectName}" ?>
										</div>

										<div class="collapse" id="projectID_<?php echo $nodeID ?>">
											<?php
											if ($projectPhases) {
												foreach ($projectPhases as $key => $phase) {
													$nodeID .= "phase_{$phase->projectPhaseID}";
													$phase->projectPhaseName= htmlspecialchars($phase->projectPhaseName);?>
													<div
														class="dropdown-item ps-4 leafPhase"
														href="#<?php echo $nodeID ?>"
														data-bs-toggle="collapse"
														aria-expanded="false"
														aria-controls="<?php echo $nodeID ?>"
														data-project-id="<?php echo $project->projectID ?>"
														data-project-name= "<?php echo $project->projectName ?>"
														data-client-id="<?php echo $project->clientID ?>"
														data-client-name = "<?php echo $project->clientName ?>"
														data-project-phase-id="<?php echo $phase->projectPhaseID ?>"
														data-project-phase-name="<?php echo $phase->projectPhaseName ?>"
														>
														<?= "{$phase->projectPhaseName}" ?>
													</div>
													<div class="collapse" id="<?php echo $nodeID ?>">
														<?php
														$tasks= Projects::project_tasks(array('projectPhaseID'=>$phase->projectPhaseID, 'Suspended'=>'N'), false, $DBConn);
														if ($tasks) {
															foreach ($tasks as $key => $task) {
																$task->projectTaskName= htmlspecialchars($task->projectTaskName); ?>
																<div
																	class="dropdown-item ms-5 ps-4 w-75 leafTask "
																	href="javascript:void(0)"
																	data-project-id="<?php echo $project->projectID ?>"
																	data-project-name= "<?php echo $project->projectName ?>"
																	data-client-id="<?php echo $project->clientID ?>"
																	data-client-name = "<?php echo $project->clientName ?>"
																	data-project-phase-id="<?php echo $phase->projectPhaseID ?>"
																	data-project-phase-name="<?php echo $phase->projectPhaseName ?>"
																	data-project-task-id="<?php echo $task->projectTaskID ?>"
																	data-project-task-name="<?php echo $task->projectTaskName ?>">

																	<?= " ({$task->projectTaskName})" ?>
																</div>
																<?php
															}
														} ?>
													</div>
													<?php
												}
											}?>
										</div>
										<?php
								}
							} else {
								Alert::error("No project found for this user", true, array('fst-italic', 'text-center', 'font-18'));
								$validUserProjects= array();
							}?>
						</div>
					</div>
					<div class="form-group col-md d-none">
						<label for="taskDate" class="nott mb-0 t500 text-dark ">Task Date</label>
						<input type="text" id="date" value="<?php  echo date_format($dt,'Y-m-d') ?>"  name="taskDate"  class="form-control  form-control-sm form-control-plaintext bg-light-blue text-left component-datepicker past-enabled" placeholder="YYYY-MM-DD">
					</div>
					<div class="row m-0 p-0">
						<div class="form-group col-md-4 g-1">
							<label for="workType" class="nott mb-0 t500 text-dark "> Work Type</label>
							<select name="workTypeID" id="workTypeID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2">
									<?php echo Form:: populate_select_element_from_object($workType, 'workTypeID', 'workTypeName',   '', '', $blankText='Select:') ?>
								<option value="addNew"> New Work Type</option>
							</select>
						</div>

						<div class=" form-group col-md  mt-1 ">
							<label for="form1" class="col-md-12 nott mb-0 t500 text-dark  ">Work hours </label>
							<div class=" mt-0">
								<input type="text" class="form-control form-control-sm form-control-plaintext bg-light-blue center workHours px-2" name="taskDuration" value="" placeholder="HH:MM" >
								<span class="workHoursError text-danger text-center fs-6 fst-italic"></span>
							</div>
						</div>
						<div class="col-md form-group mt-1 ">
							<label for="" class="nott t400 mb-0"> Task Status</label>
							<select name="taskStatusID" id="" class="form-control form-control-sm form-control-plaintext bg-light-blue">
							<?php echo Form::populate_select_element_from_object($taskStatusList, 'taskStatusID', 'taskStatusName', '2', '', 'Select task Status') ?>
							</select>
						</div>
					</div>

				</div>

			</fieldset>
			<div class="col-md-4 text-center ">
				<!-- File Upload Section -->
				 <div class="form-group col-md-12 py-2">
					<div class="file-upload-container text-center">
						<label for="fileUpload" class="file-upload-label mx-auto" title="Upload File">
							<i class="bi bi-upload"></i>
						</label>
						<input type="file" id="fileUpload" name="fileAttachments[]" class="file-upload-input" multiple>
					</div>

					<span id="fileNameDisplay" class="fileNameDisplay">Upload Files</span>
				</div>
			</div>

		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group col-md-12 py-2">
			<!-- <label for="description" class="col-md-12 nott mb-0 t500 text-dark mb-2  "> Add Task Notes</label> -->
			<textarea class="form-control form-control-sm bg-light-blue border-2 border-info " name="taskNarrative"   rows="4" placeholder="Edit time Log log description"></textarea>
		</div>
	</div>
</div>
<?php
// var_dump($validUserProjects);
?>



