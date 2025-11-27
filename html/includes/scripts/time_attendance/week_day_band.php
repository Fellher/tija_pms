	<div class="card shadow-lg">
<?php
$today = date('Y-m-d');
$weekStartDate = !empty($weekArray) ? min($weekArray) : $today;
$weekEndDate   = !empty($weekArray) ? max($weekArray) : $today;
$nextWeekStart = date('Y-m-d', strtotime($weekEndDate . ' +1 day'));
$canViewNextWeek = $nextWeekStart <= $today;
$currentWeekNumber = date('W');
$currentWeekYear   = date('Y');
?>
					<div class="card-body">
						<div class="row">
							<div class="col-md-8"><h3 class="t300 my-0 text-dark"><?php echo $dt->format('l j\/n') ?></h3></div>
							<div class="col-md-4">
								<div class=" m-0">
									<a  class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week-1).'&year='.$year.'&uid='.$userID; ?>">
										<i class="fa-solid fa-circle-chevron-left"></i></a> <!--Previous week-->
									<span>Week <?php echo $dt->format('W') ?></span>
									<?php if ($canViewNextWeek): ?>
										<a class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>"><i class="fa-solid fa-circle-chevron-right"></i></a> <!--Next week-->
									<?php else: ?>
										<span class="btn btn-link m-0 py-0 disabled" style="pointer-events: none; opacity: 0.5;">
											<i class="fa-solid fa-circle-chevron-right"></i>
										</span>
									<?php endif; ?>
									<a href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".$currentWeekNumber.'&year='.$currentWeekYear.'&uid='.$userID;?>" class="btn btn-white border"> Today</a>
								</div>
							</div>
						</div>
						<div class="row border">
							<?php
							$bgColor='';
							$textColor='';
							$converted= '00:00';
							$totalWeek=0;
							$totalWeekAbs = 0;
							foreach ($weekArray as $key => $dayVal) {
								$activeDay ='';
								$activeDay = $dayVal == $DOF ? 'activeDay' : '';
								$dayDate= date_create($dayVal);

								// Check if date is in the future
								$today = date('Y-m-d');
								$isFutureDate = $dayVal > $today;

								$absence= TimeAttendance:: absence_full (array('userID'=>$userID, 'absenceDate'=>$dayVal, 'Suspended'=>"N"), false, $DBConn);
								$totalAbsTimeSec=0;
								$leaveActive='';
								if ($absence) {
									foreach ($absence as $abs => $absValue) {
										$startTime= $absValue->startTime;
										$endTime= $absValue->endTime;
										$timeDiffSec= Utility::day_time_difference($startTime, $endTime);
										$totalAbsTimeSec+= $timeDiffSec;
										$totalWeekAbs+=$timeDiffSec;
									}
								}
								$allTimelogs[$dayVal] = TimeAttendance::project_tasks_time_logs(array('taskDate'=> $dayVal, "employeeID"=>$userID,  'Suspended'=> 'N'), false, $DBConn);
								$totalTimeSec=array();
								$totalDay=0;
								if ($allTimelogs[$dayVal]) {
									$totalTaskDuration=0;

									// var_dump($allTimelogs[$dayVal]);
									foreach ($allTimelogs[$dayVal] as $ky => $dayTimeLog) {
										$totalTime[$ky]=explode(':',$dayTimeLog->taskDuration);
										$hours =0;
										$mins=0;
										// var_dump($totalTime[$ky]);
										$totalTimeSec[$ky]= ($totalTime[$ky][0] * 3600) + ($totalTime[$ky][1] * 60);
										$totalDay+=$totalTimeSec[$ky];
									}
								}

								if ($totalAbsTimeSec) {
									$totalDay+=$totalAbsTimeSec;
									$leaveActive= 'border_cs_top_3';
								}
								$totalWeek+= $totalDay;
								$time_output_day= sprintf("%02s : %'02s\n", intval($totalDay/60/60), abs(intval(($totalDay%3600) / 60)), abs($totalDay%60));

								// Determine if link should be active or disabled
								if ($isFutureDate) {
									// Future date - show as disabled div
									?>
									<div class="font-primary border-end col-sm dayover <?php echo "{$bgColor} {$activeDay} {$leaveActive} "  ?> <?php echo $textColor ?>"
										 style="opacity: 0.5; cursor: not-allowed; background-color: #f0f0f0; pointer-events: none;"
										 title="Cannot add timesheets for future dates">
										<div class="col-sm center py-1">
											<span class="col t400 mb-2 <?php echo $textColor; ?>">
												<?php echo '<span class="font-14 '.$textColor.'">'.$dayDate->format('D') .' '. $dayDate->format('j\/m').'</span>';	 ?>
											</span> <br>
											<span class="<?php echo $textColor; ?> t400 font-22"> <?php echo $time_output_day ? $time_output_day : $converted ; ?> </span>
										</div>
									</div>
									<?php
								} else {
									// Past or current date - show as active link
									?>
									<a class="font-primary border-end col-sm dayover <?php echo "{$bgColor} {$activeDay} {$leaveActive} "  ?> <?php echo $textColor ?>"
									   href="<?php echo $base .'html/?'. $initURL .'&d='. $dayDate->format('Y-m-d')?>" >
										<div class="col-sm center py-1">
											<span class="col t400 mb-2 <?php echo $textColor; ?>">
												<?php echo '<span class="font-14 '.$textColor.'">'.$dayDate->format('D') .' '. $dayDate->format('j\/m').'</span>';	 ?>
											</span> <br>
											<span class="<?php echo $textColor; ?> t400 font-22"> <?php echo $time_output_day ? $time_output_day : $converted ; ?> </span>
										</div>
									</a>
									<?php
								}
							}
							$time_output_week= sprintf("%02shrs : %'02smins\n", intval($totalWeek/60/60), abs(intval(($totalWeek%3600) / 60)), abs($totalWeek%60)); ?>
							<div class="font-primary  border-end col-sm dayover "  >
								<div class="col-sm center py-1   ">
									<span class="col t400 mb-2  ">
										<?php echo '<span class="font-14 '.$textColor.'"> Week Total </span>';	 ?>
									</span> <br>
									<span class="<?php echo $textColor; ?> t400 font-22 weekTot">
										<?php echo $weekTot =  $time_output_week ? $time_output_week : $converted ; ?>
									</span>
								</div>
							</div>
						</div>
						<?php
						// var_dump($month);
						$monthVariables = Utility::generate_month_time_variables($month, $year);
						// var_dump($monthVariables);

						// var_dump($userFullDetails);
						if($userFullDetails->basicSalary >0 && $userFullDetails->basicSalary != '') {
							$costPerHour = $userFullDetails->basicSalary/$monthVariables['totalHoursInMonth'];
							$weekTimeDecimal = $totalWeek/3600;
							$employeeBilling = $costPerHour*$weekTimeDecimal;
						} else if (isset($userFullDetails->costPerHour) && $userFullDetails->costPerHour > 0 && $userFullDetails->costPerHour != '') {
							$weekTimeDecimal = $totalWeek/3600;
							$employeeBilling = $userFullDetails->costPerHour*$weekTimeDecimal;
						}

						// if (isset($userFullDetails->costPerHour) && !empty($userFullDetails->costPerHour) ) {
						// 	$weekTimeDecimal = $totalWeek/3600;
						// 	$employeeBilling = $userFullDetails->costPerHour*$weekTimeDecimal;
						// }

						$productivity =round(($totalWeek/$config['stdWeekHours40'])*100, 2); ?>
						<script>
							let weekSec = <?php echo $totalWeek ?>;
							const weekTime = new Date(weekSec * 1000)
							.toISOString()
							.slice(11, 16);
							document.querySelector('.wkTime').innerHTML= weekTime;
							let weekAbs = <?php echo $totalWeekAbs; ?>;
							const weekAbsTime = new Date(weekAbs * 1000)
							.toISOString()
							.slice(11, 16);
							document.querySelector('.wkAbsTime').innerHTML= weekAbsTime;
							bill = <?php echo (isset($employeeBilling) && !empty($employeeBilling) ) ? $employeeBilling : 0;  ?>;
							let billCurrency = bill.toLocaleString('en-US', {
								style:'currency',
								currency: 'KES'
							});
							document.querySelector('.wkBillable').innerHTML= billCurrency;
							document.querySelector('.wkProd').innerHTML=<?php echo  $productivity ?>;
						</script>
						<?php
						$myTaskAssignnments= Projects::assigned_task(array('userID'=>$userID, 'Suspended'=> 'N'), false, $DBConn);
						$allProjects = Projects::projects_full(array('Suspended'=>'N'), false, $DBConn);
						$myProjects= array();
						$myProjectsArr = array();
					 	if ($allProjects) {
					 		foreach ($allProjects as $key => $project) {
					 			if ($myTaskAssignnments) {
					 				foreach ($myTaskAssignnments as $key => $taskAss) {
						 				if ($taskAss->projectID === $project->projectID) {
							 				$clientData= Client::clients(array('clientID'=>$project->clientID), true, $DBConn);
							 				$taskDetailsAss = Projects::project_tasks(array('projectTaskID'=>$taskAss->projectTaskID), true, $DBConn);
							 				$projectClient = "{$project->projectName} - ({$clientData->clientName})";
							 				$myProjects[$projectClient][] = $taskDetailsAss;
							 				$myProjectsArr[$clientData->clientName][]=$project;
						 				}
						 			}
					 			}
					 		}
					 	}?>
					</div>
				</div>
