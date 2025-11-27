<?php 
if ($absence) {
	foreach ($absence as $key => $absenceData) {
		$startTime= $absenceData->startTime;
		$endTime= $absenceData->endTime;
		$absenceDuration= Utility::day_time_difference($startTime, $endTime);

		// var_dump($allTime);
		// convert allTime to HH:MM:SS
		// echo $allTimeConverted= sprintf("%02s : %'02s\n", intval($allTime/60/60), abs(intval(($allTime%3600) / 60)), abs($allTime%60));
		isset($allTime)? $allTime+=$absenceDuration: $allTime=0;
		// $allTime+=$absenceDuration;
		// var_dump($allTime);
		// echo $allTimeConverted= sprintf("%02s : %'02s\n", intval($allTime/60/60), abs(intval(($allTime%3600) / 60)), abs($allTime%60));
		// var_dump($absenceDuration);
		$diffConverted= sprintf("%02s : %'02s\n", intval($absenceDuration/60/60), abs(intval(($absenceDuration%3600) / 60)), abs($absenceDuration%60));
		$absenceID= $absenceData->absenceID; ?>
		<div class="col-12 border shadow my-3 timelogBrief" id="<?php echo "absence{$absenceData->absenceID}" ?>">
			<div class="row" >
				<button class="col-md py-2  ps-3 btn btn-white " data-id="e<?php echo $absenceID  ?>" id="editAbsence<?php echo $absenceID  ?>">
					<div class="row">
						<div class="col-sm-5 text-start ps-3">
							<h4 class="mb-0"><?php echo $absenceData->absenceName  ?> :  <span>(<?php echo $absenceData->absenceTypeName  ?>)</span> 
								<span class="float-end"> 
									<span class="text-dark t600 me-3">Duration:  </span>  
									<?php  echo "{$startTime} - {$endTime}"  ?>
								</span>
							</h4>
							<p class=" mb-0 "> <?php echo "<span class='font-16 t600 text-dark'>{$absenceData->projectName}</span> "  ?></p>
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
		<?php include 'includes/scripts/time_attendance/collapse/edit_absence.php'; ?>
		<script>
			let editAbsence<?php echo $absenceID ?>= document.getElementById('editAbsence<?php echo $absenceID  ?>');
			editAbsence<?php echo $absenceID  ?>.addEventListener('click', absenceEdit);
			function absenceEdit(){
		
				let editAbsence = document.getElementById('<?php echo "editAbsenceClass{$absenceData->absenceID}" ?>');

				console.log(editAbsence);
				let absenceDiv = document.getElementById('<?php echo "absence{$absenceData->absenceID}" ?>');
				
				if(editAbsence.classList.contains("d-none") == true){
					editAbsence.classList.remove("d-none");
				}

				let startTimeEdit=editAbsence.querySelector('#startTimeEdit');
				let endTimeEdit=editAbsence.querySelector('#endTimeEdit');
				let allDayEdit= editAbsence.querySelector('#allDayEdit');


				startTimeEdit.addEventListener('blur', validateStartEdit);
				endTimeEdit.addEventListener('blur', validateEndEdit);
				allDayEdit.addEventListener('change', setAllDayEdit);

					function validateStartEdit() {												
						const re= /^[0-2]{1}[0-9]{1}\:[0-5][0-9]$/;
						if (!re.test(startTimeEdit.value)) {
							startTimeEdit.classList.add('is-invalid');
						} else {
							startTimeEdit.classList.remove('is-invalid');
						}
					}
					function validateEndEdit() {												
						const re= /^[0-2]{1}[0-9]{1}\:[0-5][0-9]$/;
						if (!re.test(endTimeEdit.value)) {
							endTimeEdit.classList.add('is-invalid');
						} else {
							endTimeEdit.classList.remove('is-invalid');
						}
					}
					function setAllDayEdit() {
						if (allDayEdit.checked==true) {
							startTimeEdit.value= '08:00';
							endTimeEdit.value= '17:00';
							startTimeEdit.setAttribute('readonly', true);
							endTimeEdit.setAttribute('readonly', true);
						}
					}

					editAbsence.querySelector(".fa-cancel").addEventListener("click", (e)=>{
						editAbsence.classList.add('d-none');
						e.preventDefault();
						e.stopPropagation();

					});

			}
		</script>
		<?php
	}
} ?>