<?php
$activityCategories = Schedule::activity_categories([], false, $DBConn);
$activityTypes = Schedule::tija_activity_types([], false, $DBConn);?>
<div id="manage_activity_form" class="bg-light">
   <div class=" shadow bg-white border-0 rounded-3  p-3 my-2 activityNameDiv">
      <div class="row">
         <div class="form-group my-2  d-none">
            <label for="orgDataID"> Org Data ID</label>]
            <input type="text" id="orgDataID" name="orgDataID" class="form-control form-control-sm form-control-plaintext " placeholder="Org Data ID" value="<?php echo (isset($orgDataID) && $orgDataID) ? $orgDataID  : $employeeDetails->orgDataID; ?>" readonly>
            <label for="entityID"> EntityID</label>
            <input type="text" id="entityID" name="entityID" class="form-control form-control-sm form-control-plaintext " placeholder="Entity ID" value="<?php echo (isset($entityID) && $entityID) ? $entityID : $employeeDetails->entityID; ?>" readonly>
            <label for="workSegmentID"> WorkSegment</label>
         </div>

         <div class="form-group my-2 col-md-6 ">
            <label for="activityCategoryID"> Activity Category</label>
            <select name="activityCategoryID" id="activityCategoryID" class="form-control form-control-sm form-control-plaintext bg-light-blue   ps-2">
               <option value="">Select Activity Category</option>
               <?php foreach($activityCategories as $category): ?>
                  <option value="<?php echo $category->activityCategoryID; ?>"><?php echo $category->activityCategoryName; ?></option>
               <?php endforeach; ?>
            </select>
         </div>
         <div class="form-group my-2 col-md-6 d-none activityTypeDiv ">
            <label for="activityType" class="col-md-12 nott mb-0 t500 text-dark">Activity Type</label>
            <select name="activityTypeID" id="activityType" class="form-control form-control-sm form-control-plaintext  bg-light-blue  ps-2">
               <option value="">Select Activity Type</option>
               <?php
               foreach($activityTypes as $type): ?>
                  <option value="<?php echo $type->activityTypeID; ?>"><?php echo $type->activityTypeName; ?></option>
               <?php endforeach; ?>
            </select>
         </div>

         <div class="form-group col-md-6 my-2">
            <label for="activityName" class="col-md-12 nott mb-0 t500 text-dark">Activity Name</label>
            <input type="text" id="activityName" name="activityName" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2 " placeholder="Enter Activity Name">
         </div>
         <div class="col-md-6 row my-2">
            <div class="form-group col-md-6  activityStartDateDiv ">
               <label for="activityDate" class="col-md-12 nott mb-0 t500 text-dark">Activity  Date</label>
               <input type="date" id="date" name="activityDate" class=" form-control-sm form-control-plaintext border-bottom  pb-0  bg-light-blue ps-2 border-top-0 activityStartDate" placeholder="YYYY-MM-DD" value="<?php echo date_format($dt,'Y-m-d') ?>">
            </div>
            <div class=" form-group  col-md ">
               <label for="form1" class="col-md-12 nott mb-0 t500 text-dark  ">Work hours </label>
               <div class="row mt-0">
                  <input type="text" class="form-control-sm form-control-plaintext border-bottom  pb-0  border-top-0  center workHours bg-light-blue ps-2" name="taskDuration" value="<?php echo (isset($timelog->taskDuration) && !empty($timelog->taskDuration)) ? $timelog->taskDuration : "" ?>" placeholder="HH:MM" >
                  <span class="workHoursError text-danger text-center fs-6 fst-italic"></span>
               </div>
            </div>

         </div>

         <input type="hidden" id="durationType" name="durationType" value="single" class="form-control form-control-sm form-control-plaintext " placeholder="">

         <div class="form-group col-12 my-2">
            <label for="activityDescription" class="col-md-12 nott mb-0 t500 text-dark">Activity Description</label>
            <textarea id="activityDescription" name="activityDescription" class="form-control borderless-mini" rows="3" placeholder="Enter Activity Description"></textarea>
         </div>
         <div class="form-group my-2 col-md-6">
            <label for="workType">Work Type</label>
            <select name="workTypeID" id="workType" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
            <?php echo Form:: populate_select_element_from_object($workType, 'workTypeID', 'workTypeName',  (isset($timelog->workTypeID) && !empty($timelog->workTypeID)) ? $timelog->workTypeID : '', '', $blankText='Select:') ?>
            </select>
         </div>
         <div class="form-group my-2 col-md-6">
            <label for="businessUnit">Business Unit</label>
            <select name="businessUnitID" id="businessUnit" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
               <option value="">Select Business Unit</option>
               <?php foreach($allBusinessUnits as $unit): ?>
                  <option value="<?php echo $unit->businessUnitID; ?>"><?php echo $unit->businessUnitName; ?></option>
               <?php endforeach; ?>
            </select>
         </div>

         <div class="form-group my-2 col-md-6">
            <label for="activitySegment"> Activity Segment</label>
            <select name="activitySegment" id="activitySegment" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
               <option value="">Select Activity Segment</option>
               <?php foreach($config['activitySegment'] as $segment): ?>
                  <option value="<?php echo $segment->key; ?>"><?php echo $segment->value; ?></option>
               <?php endforeach; ?>
            </select>
         </div>

         <div class="form-group my-2 col-md-6">
            <label for="clientID">Client</label>
            <select name="clientID" id="clientID" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
               <?= Form::populate_select_element_from_object($allClients, 'clientID', 'clientName', (isset($clientID) && !empty($clientID) )? $clientID : "", '' , 'Select Client') ?>
            </select>
         </div>

         <div class="col-md-6 projectCaseDiv d-none">
            <label for="projectID" class="col-md-12 nott mb-0 t500 text-dark">Project</label>
            <select name="projectID" id="projectID" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
               <option value="">Select Project</option>
               <?php foreach($allProjects as $project): ?>
                  <option value="<?php echo $project->projectID; ?>" <?= (isset($projectID) && $projectID === $project->projectID) ? 'selected': "";?> ><?php echo $project->projectName; ?></option>
               <?php endforeach; ?>
            </select>
         </div>

         <div class="form-group my-2 col-md-6  salesCaseDiv">
            <label for="salesCase" class="col-md-12 nott mb-0 t500 text-dark">Sales Case</label>
            <select name="salesCaseID" id="salesCase" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
               <option value="">Select Sales Case</option>
                        <?= Form::populate_select_element_from_object($allSalesCases, 'salesCaseID', 'salesCaseName', (isset($salesCaseID) && !empty($salesCaseID) )? $salesCaseID : "", '' , 'Select Sales Case') ?>
            </select>
         </div>
      </div>
   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   let allSalesCases = <?= json_encode($allSalesCases) ?>;
   let allProjects = <?= json_encode($allProjects) ?>;
   let allActivityTypes = <?= json_encode($activityTypes) ?>;
   let form = document.getElementById('manage_activity_form');
   let activitySegment = form.querySelector('#activitySegment');
   let clientID = form.querySelector('#clientID');
   let activityCategoryID = document.getElementById('activityCategoryID');
   let activityTypeSelect = document.getElementById('activityType');
   let activityTypeDiv = document.querySelector('.activityTypeDiv');

   clientID.addEventListener('change', function() {
      console.log(allSalesCases);
      let selectedClientID = clientID.value;
     let clientSalesCases = allSalesCases.filter(salesCase => salesCase.clientID == selectedClientID);
     let clientProjects = allProjects.filter(project => project.clientID == selectedClientID);
     console.log(clientSalesCases);
      console.log(clientProjects);
      let salesCaseSelect = form.querySelector('#salesCase');
      let projectSelect = form.querySelector('#projectID');
      salesCaseSelect.innerHTML = '<option value="">Select Sales Case</option>'; // Reset options
      allSalesCases.forEach(function(salesCase) {
         if (salesCase.clientID == selectedClientID) {
            let option = document.createElement('option');
            option.value = salesCase.salesCaseID;
            option.textContent = salesCase.salesCaseName;
            salesCaseSelect.appendChild(option);
         }
      });

      // check if salesCases == 0 then show error message
      if (clientSalesCases.length === 0) {
         salesCaseSelect.innerHTML = '<option value="">No Sales Cases Available for this client</option>';
      }

      projectSelect.innerHTML = '<option value="">Select Project</option>'; // Reset options
      allProjects.forEach(function(project) {
         if (project.clientID == selectedClientID) {
            let option = document.createElement('option');
            option.value = project.projectID;
            option.textContent = project.projectName;
            projectSelect.appendChild(option);
         }
      });
      // check if projects == 0 then show error message
      if (clientProjects.length === 0) {
         projectSelect.innerHTML = '<option value="">No Projects Available for this client</option>';
      }
   });





   // Handle Activity Category change - show/hide and filter Activity Types
   if (activityCategoryID && activityTypeSelect && activityTypeDiv) {
      activityCategoryID.addEventListener('change', function() {
         let selectedCategoryID = activityCategoryID.value;

         // Reset activity type select
         activityTypeSelect.innerHTML = '<option value="">Select Activity Type</option>';

         if (selectedCategoryID && selectedCategoryID !== '') {
            // Show the activity type div
            activityTypeDiv.classList.remove('d-none');

            // Filter activity types by selected category
            let filteredActivityTypes = allActivityTypes.filter(function(type) {
               return type.activityCategoryID == selectedCategoryID && (type.Suspended !== 'Y' && type.Lapsed !== 'Y');
            });

            // Populate activity type select with filtered options
            if (filteredActivityTypes.length > 0) {
               filteredActivityTypes.forEach(function(type) {
                  let option = document.createElement('option');
                  option.value = type.activityTypeID;
                  option.textContent = type.activityTypeName;
                  activityTypeSelect.appendChild(option);
               });
            } else {
               // No activity types available for this category
               activityTypeSelect.innerHTML = '<option value="">No Activity Types Available for this Category</option>';
            }
         } else {
            // Hide the activity type div if no category is selected
            activityTypeDiv.classList.add('d-none');
            activityTypeSelect.value = ''; // Reset selection
         }
      });
   }

   console.log('DOMContentLoaded on manage_activity_form');
});
</script>