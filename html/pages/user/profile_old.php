<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
// var_dump($userDetails);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);

$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

// var_dump($employeeDetails);
?>
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Dashboard</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo  $p ?></a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page"><?php echo $s ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container-fluid">
   <div class="row">
      <div class="col-12">
         <div class="card custom-card">
            <div class="card-header justify-content-between">
               <h4 class="card-title">Employee Details</h4>
               <div class="card-options">
                  <button class="btn btn-sm btn-primary-light shadow-sm" id="edit_employee">Edit</button>
               </div>
            </div>
            <div class="card-body">
               <form class="row" action="<?= "{$base}php/scripts/global/admin/manage_users.php" ?>" method="post" enctype="multipart/form-data" id="employee_form">
                  <input type="hidden"  class="form-control-sm" value="<?= $employeeDetails->ID ?>" name="ID" >
                  <div class="col-xl-4">
                     <div class="profile-container mb-4">
                        <div class="profile-setting-cover">
                           <input type="hidden" name="ID" class="form-control-xs form-control-plaintext border-bottom " value="<?= (isset($employeeDetails) && $employeeDetails->ID) ? $employeeDetails->ID : "" ?>">

                              <img src="<?php  echo  ($employeeDetails && !empty($employeeDetails->profile_image)) ? "{$config['DataDir']}{$employeeDetails->profile_image}" : "{$base}assets/img/png-images/background.jpg" ?>" alt="" class="" id="profile-img2">
                              <div class="main-profile-cover-content">
                                 <div class="text-center">
                                    <span class="avatar avatar-rounded chatstatusperson">
                                       <img class="chatimageperson"  <?php echo $employeeDetails ? "src='{$config['DataDir']}{$employeeDetails->profile_image}'" : "{$base}assets/img/png-images/2.jpg"; ?>" alt="img" id="profile-img">
                                       <span class="profile-upload">
                                          <i class="ri ri-pencil-line cursor-pointer"></i>
                                          <input type="file" name="profile_image" class="absolute inset-0 w-full h-full opacity-0 " id="profile-change">
                                       </span>
                                    </span>
                                 </div>
                                 <span class="background-upload">
                                    <i class="ri ri-pencil-line"></i>
                                    <!-- <input type="file" name="backgroundImage" name="backgroundImage" class="" id="profile-change2"> -->
                                 </span>
                              </div>
                        </div>
                        <div class="text-center mt-3 saveLogo d-none">
                           <button type="submit" class="btn btn-primary-light btn-sm rounded-pill" id="saveProfile">Save Profile</button>
                        </div>
                     </div>
                     <script>
                  document.getElementById('profile-change').addEventListener('change', function() {
                     var file = this.files[0];
                     if (file) {
                        var reader = new FileReader();
                        reader.onload = function() {
                           document.getElementById('profile-img').setAttribute('src', this.result);
                        }
                        reader.readAsDataURL(file);
                     }
                     document.querySelector('.saveLogo').classList.remove("d-none");
                  });
               </script>
                     <div class="form-group col-12 d-none ">
                        <label for="employeeID">Employee ID</label>
                        <input type="text" class="form-control" id="employeeID" value="<?= $employeeDetails->ID ?>" readonly>
                     </div>
                     <div class="form-group col-12 my-2 ">
                        <label for="employeeID">Salutation</label>
                       <?php $salutation = Data::prefixes(['Suspended'=>'N'], false, $DBConn); ?>
                        <select class="form-control-plaintext form-control-sm border-bottom" id="salutation" name="prefixID" >
                           <option value="">Select Salutation</option>
                           <?php foreach($salutation as $sal) { ?>
                           <option value="<?= $sal->prefixID ?>" <?= $employeeDetails->prefixID == $sal->prefixID ? 'selected' : '' ?>><?= $sal->prefixName ?></option>
                           <?php } ?>
                        </select>
                     </div>
                     <div class="form-group col-12 my-2 ">
                        <label for="FirstName" class="text-primary">First Name</label>
                        <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="FirstName" name="FirstName" value="<?= $employeeDetails->FirstName ?>" readonly>
                     </div>

                     <div class="form-group col-12 my-2 ">
                        <label for="Surname" class="text-primary">Surname Name</label>
                        <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="Surname" name="Surname" value="<?= $employeeDetails->Surname ?>" readonly>
                     </div>
                     <div class="form-group col-12 my-2 ">
                        <label for="OtherNames" class="text-primary">Other Name</label>
                        <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="OtherNames" name="otherNames" value="<?= $employeeDetails->OtherNames ?>" readonly>
                     </div>

                     <div class="form-group col-12 my-2 ">
                        <label for="Email" class="text-primary">Email</label>
                        <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="Email" name="Email" value="<?= $employeeDetails->Email ?>" readonly readonly>
                     </div>
                  </div>
                  <div class="col-md">
                     <div class="row">
                        <div class="form-group col-md-6 mb-2">
                           <small class="text-primary"> Date Of Birth <span class="text-danger">* </span> </small>
                           <div class="input-group">
                              <input type="text" name="dateOfBirth" class="form-control-sm form-control-plaintext border-bottom  text-left dateOfBirth" id="dateOfBirth" value="<?=$employeeDetails->dateOfBirth &&  $employeeDetails->dateOfBirth !== "0000-00-00" ? $employeeDetails->dateOfBirth : "";  ?>" placeholder="Choose date" readonly>
                           </div>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                           <small class="text-primary"> Gender<span class="text-danger">*</span></small>
                           <select class="form-control-sm form-control-plaintext border-bottom" name="gender"  required readonly>
                              <option value=""> Select Gender</option>
                              <option value="male" <?= (isset($employeeDetails->gender) && $employeeDetails->gender=='male') ? 'selected' : "" ?>  > Male</option>
                              <option value="female" <?= (isset($employeeDetails->gender) && $employeeDetails->gender=='female') ? 'selected' : "" ?>  >Female</option>
                           </select>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                           <small class="text-primary"> Phone Number <small class="text-secondary">(254 720 000 000)</small><span class="text-danger">*</span></small>
                           <input type="text" class=" form-control-sm form-control-plaintext border-bottom" name="phoneNumber" placeholder="Input Phone Number" value="<?= $employeeDetails->phoneNo ?>" required readonly readonly>
                        </div>

                        <div class="form-group col-md-6 mb-2">
                           <small class="text-primary">Payroll Number<span class="text-danger">*</span></small>
                           <input type="text" name="payrollNumber" class=" form-control-sm form-control-plaintext border-bottom" value="<?= $employeeDetails->payrollNo ?>" placeholder="input Payroll number" required readonly>
                        </div>

                        <div class="form-group col-md-6 mb-2">
                           <small class="text-primary"> Employee KRA PiN<span class="text-danger">*</span></small>
                           <input type="text" name="pin" class=" form-control-sm form-control-plaintext border-bottom" value="<?= $employeeDetails->pin ?>" placeholder="input Employee PIN " required readonly>
                        </div>

                        <div class="h4 fs-18 bg-primary-subtle px-3 py-1">
                           Employee Settings
                        </div>
                        <?php
                        $organisations = Admin::organisation_data_mini(['Suspended'=>'N'], false, $DBConn);
                        $entities=Data::entities_full(['Suspended'=> 'N' ], false, $DBConn); ?>
                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> Organisation <span class="text-danger">*</span></small>
                           <select class=" form-control-sm form-control-plaintext border-bottom" name="organisationID" required readonly>
                              <?php echo Form::populate_select_element_from_object($organisations, "orgDataID", "orgName", $employeeDetails->orgDataID, "", "Select Organisation"); ?>
                           </select>
                        </div>
                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> Entity <span class="text-danger">*</span></small>
                           <select class=" form-control-sm form-control-plaintext border-bottom" name="entityID" required readonly>
                              <?php echo Form::populate_select_element_from_object($entities, "entityID", "entityName", $employeeDetails->entityID, "", "Select Entity"); ?>
                           </select>
                        </div>
                        <?php
                        $unitTypes = Data::unit_types(array("Suspended"=>'N'), false, $DBConn);
                        // var_dump($unitTypes);
                        if($unitTypes){
                           foreach ($unitTypes as $unitType) {
                              $units = Data::units(array("unitTypeID"=>$unitType->unitTypeID, "Suspended"=>'N'), false, $DBConn);
                              if(!$units) continue;
                              $userUnitAssignment = Data::unit_user_assignments(array("unitTypeID"=>$unitType->unitTypeID, "userID"=>$employeeDetails->ID), true, $DBConn);


                              // if(!$userUnitAssignment) continue; // Skip if no unit assignment found
                              ?>
                              <div class="form-group col-md-4 mb-2">
                                 <?php
                                 // var_dump($userUnitAssignment);?>
                                 <small class="text-primary"> <?= $unitType->unitTypeName ?> <span class="text-danger">*</span></small>
                                 <div class="input-group">
                                    <select class=" form-control-sm form-control-plaintext border-bottom" name="unitType[<?= $unitType->unitTypeID ?>]"  readonly>
                                       <?php echo Form::populate_select_element_from_object($units, "unitID", "unitName", (isset($userUnitAssignment->unitID) && !empty($userUnitAssignment->unitID)) ? $userUnitAssignment->unitID :"", "", "Select {$unitType->unitTypeName}"); ?>
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

                              <select class=" form-control-sm form-control-plaintext border-bottom ps-2" name="jobTitleID" required readonly>

                                 <?php echo Form::populate_select_element_from_object($jobTitles, "jobTitleID", "jobTitle", $employeeDetails->jobTitleID, "", "Select Job Title"); ?>
                              </select>
                           </div>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> Employee Type <span class="text-danger">*</span></small>
                           <div class="input-group">

                              <select class=" form-control-sm form-control-plaintext border-bottom ps-2" name="employeeTypeID" required readonly>
                                 <option value="">Select Employee Type</option>
                                 <?php echo Form::populate_select_element_from_object($employmentStatus, "employmentStatusID", "employmentStatusTitle", $employeeDetails->employmentStatusID, "", "Select Employee Type/status"); ?>
                              </select>
                           </div>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> National ID Number<span class="text-danger">*</span></small>
                           <input type="text" name="nationalID" class=" form-control-sm form-control-plaintext border-bottom" value="<?= $employeeDetails->nationalID ?>" placeholder="input National ID number" required readonly>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> NHIF Number<span class="text-danger">*</span></small>
                           <input type="text" name="nhifNumber" class="form-control-sm form-control-plaintext border-bottom" value="<?= $employeeDetails->nhifNumber ?>" placeholder="input NHIF number" required readonly>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> NSSF Number<span class="text-danger">*</span></small>
                           <input type="text" name="nssfNumber" class=" form-control-sm form-control-plaintext border-bottom" value="<?= $employeeDetails->nssfNumber ?>" placeholder="input NSSF number" required readonly>
                        </div>

                     <?php
                     if($isAdmin || $isValidAdmin) { ?>
                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> Basic Salary<span class="text-danger">*</span></small>
                           <div class="input-group">
                              <input type="text" name="basicSalary" class=" form-control-sm form-control-plaintext border-bottom  text-left" id="BasicSalary" value="<?= $employeeDetails->basicSalary ?>" placeholder="Input Basic Salary" readonly>
                           </div>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary">Daily Work Hours<span class="text-danger">*</span></small>
                           <div class="input-group">
                              <input type="number" name="dailyWorkHours" class=" form-control-sm form-control-plaintext border-bottom  text-left" id="dailyWorkHours" value="<?= $employeeDetails->dailyHours; ?>" placeholder="Input Daily Work Hours" readonly>
                           </div>
                        </div>

                        <?php $rounding = $config['roundingOffParams']; ?>

                        <div class="form-group col-md-4">
                           <small class="text-primary"> Work Hour Rounding </small>
                           <select class=" form-control-sm form-control-plaintext py-0 px-2 border-bottom   " name="workHourRounding" id="workHourRounding" readonly >
                              <option value=""> Select nearest Value</option>
                              <?php
                              foreach ($rounding as $key => $value) {?>
                                 <option value="<?= $value->key ?>" <?= $employeeDetails->workHourRoundingID === $value->key ? 'selected' : ''; ?> > <?= $value->value ?> </option>
                                 <?php
                              }?>
                           </select>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> Date Of Employment<span class="text-danger">*</span></small>
                           <div class="input-group">
                              <input type="text" name="dateOfEmployment" class="form-control  form-control-sm form-control-plaintext border-bottom border-top-0  text-left component-datepicker past-enabled" id="date" placeholder="Choose date" value="<?= $employeeDetails->employmentStartDate ?>" readonly>
                           </div>
                        </div>

                        <div class="form-group col-md-4 mb-2">
                           <small class="text-primary"> Date Of Exit</small>
                           <div class="input-group">
                              <input type="text" name="dateOfTermination" class="form-control  form-control-sm form-control-plaintext border-bottom  border-top-0 text-left component-datepicker past-enabled" id="date" placeholder="Choose date" value= "<?= $employeeDetails->employmentEndDate ?>" readonly>
                           </div>
                        </div>
                     </div>
                     <?php
                     }
                     ?>

                     <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg float-end submit-button d-noe"> Submit</button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('edit_employee').addEventListener('click', function() {
         // Remove readonly attribute from all input elements in the form
         const inputs = document.querySelectorAll('#employee_form input[readonly]');
         inputs.forEach(input => {
               input.removeAttribute('readonly');
               input.classList.add('bg-light'); // Add bg-light class
         });

         // Remove readonly attribute from all select elements in the form
         const selects = document.querySelectorAll('#employee_form select[readonly]');
         selects.forEach(select => {
               select.removeAttribute('readonly');
               select.classList.add('bg-light'); // Add bg-light class
         });

         // Remove d-none class from the submit button
         const submitButton = document.querySelector('.submit-button');
         if (submitButton) {
               submitButton.classList.remove('d-none');
         }
         // inistate the dateOfBirth to ensure it does not allow input less than 18 years

      });
      // Initialize flatpickr for datepicker
        // Initialize datepicker for dateOfBirth
        const dateOfBirthInput = document.querySelector('input[name="dateOfBirth"]');
         console.log(dateOfBirthInput);
         if (dateOfBirthInput) {
               flatpickr(dateOfBirthInput, {
                   maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)),
                   minDate: '1900-01-01',
                   altInput: true,
                  altFormat: 'F j, Y',
                  dateFormat: 'Y-m-d',
                  //  defaultDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)),
                   onChange: function(selectedDates, dateStr, instance) {
                       instance.set('maxDate', selectedDates[0]);
                   }
               });
         }
    });
</script>




