<div class="row">
  	<div class="col-xxl-3 col-lg-4">
      <div class="card custom-card">
          <div class="card-body">
              <div class="profile-container mb-4">
                  <div class="profile-setting-cover">
                      <img src="../assets/img/png-images/2.png" alt="" class="" id="profile-img2">
                      <div class="main-profile-cover-content">
                          <div class="text-center">
                              <span class="avatar avatar-rounded chatstatusperson">
                                  <img class="chatimageperson" src="../assets/img/users/1.jpg" alt="img" id="profile-img">
                                  <span class="profile-upload">
                                      <i class="ri ri-pencil-line cursor-pointer"></i>
                                      <input type="file" class="absolute inset-0 w-full h-full opacity-0 " id="profile-change">
                                      </span>
                              </span>
                          </div>
                          <span class="background-upload">
                              <i class="ri ri-pencil-line"></i>
                              <input type="file" name="photo" class="" id="profile-change2">
                          </span>
                      </div>
                  </div>
              </div>

          </div>
      </div>
  	</div>
	<div class="col-xxl-9 col-lg-8">
		<div class="card custom-card border-0">
			<div class="card-body p-0">
				<div class="col-md-12">
					<div class="row gx-1">
						<div class="form-group col-md-4 mb-2 d-none">
							<small class="text-primary"> Employee ID<span class="text-danger">*</span></small>
							<input type="text" name="ID" value="" class="form-control-sm form-control-plaintext border-bottom">
						</div>
						<?php $prefixes = Data::prefixes(array("Suspended"=>'N'), false, $DBConn); ?>
						<div class="form-group col-md mb-2">
							<small class="text-primary">Title/Prefix<span class="text-danger">*</span></small>
							<select class="bg-light-blue border-0 border-bottom form-control-xs form-control-plaintext border-bottom" name="prefixID" required>
								<?php echo Form::populate_select_element_from_object($prefixes, "prefixID", "prefixName", "", "", "Select Prefix"); ?>
							</select>
						</div>
						<div class="form-group col-md-3 mb-2 ">

							<small class="text-primary"> First Name <span class="text-danger">*</span></small>
							<input type="text" name="FirstName" class="bg-light-blue border-0 border-bottom form-control-plaintext border-bottom form-control-xs " value="" placeholder="First Name" required>
						</div>

						<div class="form-group col-md-3 mb-2">
							<small class="text-primary"> Surname<span class="text-danger">*</span></small>
							<input type="text" name="Surname" class="bg-light-blue border-0 border-bottom form-control-plaintext border-bottom form-control-xs" value="" placeholder="Input Surname" required>
						</div>

						<div class="form-group col-md-3 mb-2">
							<small class="text-primary"> OtherName</small>
							<input type="text" name="OtherNames" class="bg-light-blue border-0 border-bottom form-control-plaintext border-bottom form-control-xs" value="" placeholder="input Other name">
						</div>

						<div class="form-group col-md mb-2">
							<small class="text-primary"> Initials</small>
							<input type="text" name="userInitials" class="bg-light-blue border-0 border-bottom form-control-plaintext border-bottom form-control-xs" value="" placeholder="input User Initials">
						</div>
					</div>
					<div class="row gx-2">
						<div class="form-group col-md-6 mb-2">
							<small class="text-primary"> Phone Number <small class="text-secondary">(254 720 000 000)</small><span class="text-danger">*</span></small>
							<input type="text" class="form-control form-control-sm form-control-plaintext border-bottom" name="phoneNo" placeholder="Input Phone Number" value="" required>
						</div>

						<div class="form-group col-md-6 mb-2">
							<small class="text-primary"> Email here<span class="text-danger">*</span> </small>
							<input type="text" class="form-control form-control-sm form-control-plaintext border-bottom " name="Email" placeholder="input email ID" value=""   required>
						</div>
						<div class="form-group col-md-6 mb-2">
							<small class="text-primary">Payroll Number<span class="text-danger">*</span></small>
							<input type="text" name="payrollNo" class="form-control form-control-sm form-control-plaintext border-bottom" value="" placeholder="input Payroll number" required>
						</div>
						<div class="form-group col-md-6 mb-2">
							<small class="text-primary"> Employee KRA PiN<span class="text-danger">*</span></small>
							<input type="text" name="pin" class="form-control form-control-sm form-control-plaintext border-bottom" value="" placeholder="input Employee PIN " required>
						</div>

						<div class="form-group col-md-6 mb-2">
							<small class="text-primary"> Gender<span class="text-danger">*</span></small>
							<select class="form-control form-control-sm form-control-plaintext border-bottom" name="gender"  required>
							<option value=""> Select Gender</option>
							<option value="male"  > Male</option>
							<option value="female"  >Female</option>
							</select>
						</div>
						<div class="form-group col-md-6 mb-2">
							<small class="text-primary"> Date Of Birth <span class="text-danger">*</span> </small>
							<div class="input-group">
								<div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
								<input type="text" name="dateOfBirth" class="form-control  form-control-sm form-control-plaintext border-bottom  text-left component-datepicker past-enabled" id="date" placeholder="Choose date">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $organisations = Admin::organisation_data_mini(array("Suspended"=>'N'), false, $DBConn);
$entities=Data::entities_full(['Suspended'=> 'N' ], false, $DBConn);
// var_dump($entities);   ?>

<div class='col-12'>
  	<div class="card custom-card">
    	<div class="card-header border-bottom-0">
      	<h6 class="main-content-label mb-1">Employee Details</h6>
    	</div>
    	<div class="card-body">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Organisation <span class="text-danger">*</span></small>
							<select class="form-control form-control-sm form-control-plaintext border-bottom" name="organisationID" required>
								<?php echo Form::populate_select_element_from_object($organisations, "orgDataID", "orgName", $orgDataID, "", "Select Organisation"); ?>
							</select>
						</div>
						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Entity <span class="text-danger">*</span></small>
							<select class="form-control form-control-sm form-control-plaintext border-bottom" name="entityID" required>
								<?php echo Form::populate_select_element_from_object($entities, "entityID", "entityName", $entityID, "", "Select Entity"); ?>
							</select>
						</div>
						<?php
						$unitTypes = Data::unit_types(array("Suspended"=>'N'), false, $DBConn);
						// var_dump($unitTypes);
						if($unitTypes){
							foreach ($unitTypes as $unitType) {
								$units = Data::units(array("unitTypeID"=>$unitType->unitTypeID, "Suspended"=>'N'), false, $DBConn);
								if(!$units) continue;
								?>
								<div class="form-group col-md-4 mb-2">
									<small class="text-primary"> <?= $unitType->unitTypeName ?> <span class="text-danger">*</span></small>
									<div class="input-group">
										<div class="input-group-text text-muted"> <i class="ri-user-2-line"></i></div>
										<select class="form-control form-control-sm form-control-plaintext border-bottom" name="unitType[<?= $unitType->unitTypeID ?>]" required>
											<?php echo Form::populate_select_element_from_object($units, "unitID", "unitName", "", "", "Select {$unitType->unitTypeName}"); ?>
										</select>
									</div>
								</div>
								<?php
							}
						}
						$employmentStatus = Admin::tija_employment_status(array(), false, $DBConn);
						// var_dump($employmentStatus);
						$jobTitles = Admin::tija_job_titles(array("Suspended"=>'N'), false, $DBConn);
						// var_dump($jobTitles);
						?>
						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Job Title<span class="text-danger">*</span></small>
							<div class="input-group">
								<div class="input-group-text text-muted"> <i class="ri-user-2-line"></i></div>
								<select class="form-control form-control-sm form-control-plaintext border-bottom ps-2" name="jobTitleID" required>

									<?php echo Form::populate_select_element_from_object($jobTitles, "jobTitleID", "jobTitle", "", "", "Select Job Title"); ?>
								</select>
							</div>
						</div>
						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Direct Reporting To <span class="text-danger">*</span></small>
							<select class="form-control form-control-sm form-control-plaintext border-bottom" name="supervisorID" required>
								<option value="">Select Direct Reporting To</option>
								<?php echo Form::populate_select_element_from_object($employees, "ID", "employeeName", "", "", "Select Direct Reporting To"); ?>
							</select>
						</div>

						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Employee Type <span class="text-danger">*</span></small>
							<div class="input-group">
								<div class="input-group-text text-muted"> <i class="ri-user-2-line"></i></div>
								<select class="form-control form-control-sm form-control-plaintext border-bottom ps-2" name="employeeTypeID" required>
									<option value="">Select Employee Type</option>
									<?php echo Form::populate_select_element_from_object($employmentStatus, "employmentStatusID", "employmentStatusTitle", "", "", "Select Employee Type/status"); ?>
								</select>
							</div>
						</div>

						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> National ID Number<span class="text-danger">*</span></small>
							<input type="text" name="nationalID" class="form-control form-control-sm form-control-plaintext border-bottom" value="" placeholder="input National ID number" required>
						</div>

						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> NHIF Number<span class="text-danger">*</span></small>
							<input type="text" name="nhifNumber" class="form-control form-control-sm form-control-plaintext border-bottom" value="" placeholder="input NHIF number" required>
						</div>

						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> NSSF Number<span class="text-danger">*</span></small>
							<input type="text" name="nssfNumber" class="form-control form-control-sm form-control-plaintext border-bottom" value="" placeholder="input NSSF number" required>
						</div>


						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Basic Salary<span class="text-danger">*</span></small>
							<div class="input-group">
								<input type="text" name="basicSalary" class="form-control form-control-sm form-control-plaintext border-bottom  text-left" id="BasicSalary" placeholder="Input Basic Salary">
							</div>
						</div>

						<div class="form-group col-md-4 mb-2">
							<small class="text-primary">Daily Work Hours<span class="text-danger">*</span></small>
							<div class="input-group">
								<input type="number" name="dailyWorkHours" class="form-control  form-control-sm form-control-plaintext border-bottom  text-left" id="dailyWorkHours" placeholder="Input Daily Work Hours">
							</div>
						</div>

						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Date Of Employment<span class="text-danger">*</span></small>
							<div class="input-group">
								<div class="input-group-text input-group-text-sm text-muted"> <i class="ri-calendar-line"></i> </div>
								<input type="text" name="dateOfEmployment" class="form-control  form-control-sm form-control-plaintext border-bottom  text-left component-datepicker past-enabled" id="date" placeholder="Choose date">
							</div>
						</div>

						<div class="form-group col-md-4 mb-2">
							<small class="text-primary"> Date Of Exit</small>
							<div class="input-group">
									<div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
									<input type="text" name="dateOfTermination" class="form-control  form-control-sm form-control-plaintext border-bottom  text-left component-datepicker past-enabled" id="date" placeholder="Choose date">
							</div>
						</div>

						<div class="form-group col-md-4">
							<small class="text-primary"> Work Hour Rounding </small>
							<select class="form-control form-control-sm form-control-plaintext py-0 px-2 border-bottom   " name="workHourRounding" id="workHourRounding" >
								<option value=""> Select nearest Value</option>
								<option value="5" > 5 mins</option>
								<option value="15">10 mins</option>
								<option value="30" >30 Mins</option>
								<option value="60" > 60 mins (nearest hour)</option>
							</select>
						</div>


					</div>
				</div>
			</div>
    	</div>
  	</div>
</div>

