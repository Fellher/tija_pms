<div class="card d-none editAbsenceClass" id="<?php echo "editAbsenceClass{$absenceData->absenceID}" ?>">
	<form action="<?php echo $base ."php/scripts/work/manage_absence.php"; ?>" method="post" enctype="multipart/form-data">
		<h4 class="bs-gray-100 m-2 font-18 border-bottom">Edit Absence <span class="float-end"> <a href=""><i class="fa-solid fa-cancel"></i></a> </span> </h4>	
		<div class="row px-3">
			<input type="hidden" name="absenceID" value="<?php echo $absenceData->absenceID; ?>" class="form-control form-control-plaintext bg-light-blue">

			<div class="col-md-6 row">
				<div class="form-group mb-3 col-md-3">
					<label for="absenceDate"  class="mb-0 nott t500">Absence Date</label>
					<input type="text"  class="form-control form-control-sm form-control-plaintext bg-light-blue" name= 'absenceDate' value="<?php echo $dt->format('Y-m-d'); ?>">
				</div>
				<div class="form-group mb-3 col-md">

					<label for="absenceName"  class="mb-0 nott t500">Absence Name</label>
				  <input type="text" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" id="absenceName" name="absenceName" value="<?php echo "{$absenceData->absenceName}" ?>"  placeholder="absenceName">							  
				</div>
				
				<div class="form-group col-md">
					<label for="absenceTypeIDEdit"  class="mb-0 nott t500"> Absence Type</label>							
					<select name="absenceTypeID" id="absenceTypeIDEdit" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2">
						<?php echo Form::populate_select_element_from_object($absenceTypes, 'absenceTypeID', 'absenceTypeName', $absenceData->absenceTypeID, '', 'select absence Type') ?>
					</select>
											
				</div>
				<div class="form-group col-md-12 mb-0" >
					<label id="target_abs" for="grp_option_abs" class="mb-0 nott t500"> Absence - Select Project case</label>
					<select name="projectID" id="grp_option_abs" class="form-control form-control-xs form-control-plaintext bg-light-blue" >
						<?php echo Form:: populate_select_element_from_grouped_object($projectArray, 'projectID', 'projectName',  $absenceData->projectID, '', $blankText='Select:') ?>
					</select>
				</div>
					
				<fieldset class="row timeSet mb-0">
					<div class="form-group col-sm-6">
						<label for="startTimeEdit" class="nott mb-0 t400 ">Start Time</label>
						<div class="input-group text-start" >
							<input type="text" id="startTimeEdit" name="startTime" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2"  placeholder="00:00 hrs " value="<?php echo isset($absenceData->startTime) ? $absenceData->startTime: '' ?>" />
							<div class="invalid-feedback">
		               start Time must be in the format 00:00
		              </div>									
						</div>
					</div>
					<div class="form-group col-sm-6">
						<label for="endTimeEdit" class="nott mb-0 t400 text-primary"> End Time</label>
						<div class="input-group text-start" >
							<input type="text" id="endTimeEdit" name="endTime" class="form-control  form-control-sm form-control-plaintext bg-light-blue  px-2" placeholder="00:00 hrs "  value="<?php echo isset($absenceData->endTime) ? $absenceData->endTime: '' ?>" />		
							<div class="invalid-feedback">
		               End Time must be in the format 00:00
		              </div>								
						</div>
					</div>
				</fieldset>	
				<div class="form-check form-switch  ps-3" >
				  	<input class="form-check-input ms-5" name= "allday" <?php echo $absenceData->allday == "Y" ? "checked" : ''; ?> id="allDayEdit" value="Y" type="checkbox" id="allDay">
				  	<label class="form-check-label ml-3" for="allday">All Day</label>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group col-md-12 py-2">
					<label for="description" class="col-md-12 nott mb-0 t500 text-dark mb-2  "> Add Desription</label>
					<textarea class="form-control borderless-mini" name="absenceDescription"   rows="3" placeholder="Edit absence  description"><?php echo $absenceData->absenceDescription ? $absenceData->absenceDescription : '' ?></textarea>
				</div>
			</div>
			<div class="clearfix col-12 text-end">
				<button type="submit" class="btn btn-primary float-end"  >Submit</button>
			</div>
		</div>
	</form>
</div>

