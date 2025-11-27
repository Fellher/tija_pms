<?php 
if ($todaysTimelogs) {
	foreach ($todaysTimelogs as $key => $timelog) {
		$projectPhaseDetails= Work::project_phases (array('projectPhaseID'=>$timelog->projectPhaseID), true, $DBConn);
		$allPhaseLogs = Work::project_tasks_time_logs(array('projectPhaseID'=>$timelog->projectPhaseID), false, $DBConn);
		if ($allPhaseLogs) {
			$phaseTime = 0;
			foreach ($allPhaseLogs as $key => $phaseLog) {
				$decimalTime = (isSet($phaseLog->taskDuration) && !empty($phaseLog->taskDuration)) ?Utility::Time_to_decimal($phaseLog->taskDuration): 0;
				 $phaseTime += $decimalTime ;
			}
		}
		$phaseEstimate = (isset($projectPhaseDetails->phaseWorkHrs) && !empty($projectPhaseDetails->phaseWorkHrs)) ? Utility::Time_to_decimal($projectPhaseDetails->phaseWorkHrs,".") : 0;
		$percentage =  ($phaseTime/$phaseEstimate)*100;
		$timeLeft = $phaseEstimate-$phaseTime;
		
		/*project edit*/
		$projectFilter= array('projectID'=> $timelog->projectID, 'Suspended'=> 'N');
		$projectTasks=Work::project_tasks ($projectFilter, false, $DBConn);
		$timelogID= $timelog->timelogID;
		$durationArray[]=$timelog->taskDuration;?>
		<div class="col-12 border shadow my-3 timelogBrief" id="<?php echo "timelogDiv{$timelog->timelogID}" ?>">
			<div class="row" >
				<div class="fbox-icon fbox-outline m-2 ">
					<i class="icon-user-clock text-secondary nobg border border-dark"></i>
				</div>
				<button class="col-md p-2 btn btn-white" id="editTimelogBtn<?php echo $timelogID  ?>">
					<div class="row">
						<div class="col-md-5 text-start">
							<h4 class="mb-0"><?php echo $clientDetails->clientName  ?> : <small><?php echo $projectDetails->projectName  ?></small><span class= 'text-primary font-14 t300 '><?php  echo !empty($projectPhaseDetails) ? "({$projectPhaseDetails->projectPhaseName})" : ''  ?></span></h4>
							<p class=" mb-0 "> <?php echo "{$taskDetails->projectTaskName} <span class= 'text-primary'>({$workTypeDetails->workTypeName})</span>"  ?></p>
						</div>
						<div class="col-md-1">
							<span class="font-26"><?php echo "{$timelog->taskDuration} h"; ?></span>
						</div>
						<div class="col-sm nomargins_p text-limit-2 text-start ">
							<span class="font-18 t600"><?php echo $timeLeft  ?> hours</span> Left on Phase
							<div class="col-md-11">
								<div class="progress ">
								  	<div class="progress-bar progress-bar-striped" role="progressbar" aria-label="Example with label" style="width: <?php echo round($percentage,2) ?>%;" aria-valuenow="<?php echo round($percentage,2) ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $phaseTime; ?> %</div>
								</div>
							</div>														
						</div>
					</div>
				</button>
			</div>
		</div>									
							<?php // include 'includes/work/scripts/manage_work_hour.php'; ?>									
							<script>
								let timelogBtn<?php echo $timelogID  ?> = document.getElementById('editTimelogBtn<?php echo $timelogID  ?>');
								timelogBtn<?php echo $timelogID  ?>.addEventListener('click', displayEdit );
								function displayEdit() {
									let timelogDiv= document.getElementById('timelogDiv<?php echo $timelogID  ?>'),
										editTimelogDiv= document.getElementById('editTimelogDiv<?php echo $timelogID;  ?>'),
										editTimeLogClass = document.querySelectorAll(".editTimeLogClass"),
										timelogBrief= document.querySelectorAll(".timelogBrief");
										for (let i = editTimeLogClass.length - 1; i >= 0; i--) {
											if(editTimeLogClass[i].classList.contains("d-none") !== true){
												editTimeLogClass[i].classList.add("d-none");
											}
											if(timelogBrief[i].classList.contains("d-none") == true){
												timelogBrief[i].classList.remove("d-none");
											}													
										}
									timelogDiv.classList.add("d-none");
									editTimelogDiv.classList.remove("d-none");
								}
							</script>								
							<?php
						}
						$allTime=0;
						if ($durationArray) {
							foreach ($durationArray as $ts => $duration) {
								$totalTime[$ts]=explode(':',$duration);
								$hours =0;
								$mins=0;										
								$totalTimeSec[$ts]= ($totalTime[$ts][0] * 3600) + ($totalTime[$ts][1] * 60);
								$allTime +=$totalTimeSec[$ts];
							}
							$time_output= sprintf("%02s:%'02s\n", intval($allTime/60/60), abs(intval(($allTime%3600) / 60)), abs($allTime%60));
						}?>
						<div class="col-12 " >
							<div class="row" >
								<div class="fbox-icon fbox-outline "></div>
								<div class="col-md " >
									<div class="row">
										<div class="col-sm-5 text-start">
											<h4 class="mb-0 float-end font-26">Total Time</h4>
										</div>
										<div class="col-sm-2 center">
											<span id="totaltime" class="font-26 text-primary"><?php echo "{$time_output} h"; ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
					} else {
						echo "<div class='font-24 center'>";
						Alert::info("<i class='icon-business-time font-26 p-3 mx-5'></i>There are no entries Saved for {$dt->format('l')}  {$dt->format('jS \o\f F')}");
						echo "</div>";
					}

					if ($absence) {
						foreach ($absence as $key => $absenceData) {
							$absenceTypeDetails= Work::absence_types(array("absenceTypeID"=>$absenceData->absenceTypeID), true ,$DBConn);
							$tasksAffected = Work::project_tasks(array('projectTaskID'=> $absenceData->affectedTasks), true, $DBConn);
							$projectDetailsAbs= Work::projects (array('projectID'=>$tasksAffected->projectID), true, $DBConn);
							$startTime= $absenceData->startTime;
							$endTime= $absenceData->endTime;
							$absenceDuration= Utility::day_time_difference($startTime, $endTime);
							$diffConverted= sprintf("%02s : %'02s\n", intval($absenceDuration/60/60), abs(intval(($absenceDuration%3600) / 60)), abs($absenceDuration%60));
							$absenceID= $absenceData->absenceID; ?>
							<div class="col-12 border shadow my-3 timelogBrief" id="<?php echo "absence{$absenceData->absenceID}" ?>">
								<div class="row" >
									<div class="fbox-icon fbox-outline m-2 ">
										<i class="icon-user-alt-slash text-danger  nobg border border-danger"></i>
									</div>
									<button class="col-md p-2 btn btn-white " id="editAbsence<?php echo $absenceID  ?>">
										<div class="row">
											<div class="col-sm-5 text-start">
												<h4 class="mb-0"><?php echo $absenceData->absenceName  ?> :  <small><?php echo $absenceTypeDetails->absenceTypeName  ?></small></h4>
												<p class=" mb-0 "> <?php echo "<span class='font-16 t600 text-dark'>{$projectDetailsAbs->projectName}</span> - {$tasksAffected->projectTaskName} "  ?></p>
											</div>
											<div class="col-sm-2">
												<span class="font-26"><?php echo "{$diffConverted} h"; ?></span>
											</div>
											<div class="col-sm nomargins_p text-limit-2 text-start ">
												<?php echo "{$absenceData->absenceDescription}" ?>
											</div>
										</div>
									</button>
								</div>
							</div>
							<?php
						}
					} ?>