<!-- Project Phase Management Modal Form -->
<!-- Form action is handled by the outer modal form created by Utility::form_modal_header() -->
 <div class="form-group d-none">
    <label for="projectID" class="nott t400 mb-0 text-primary">Project ID</label>
    <input type="text" class ="form-control form-control-sm" name="projectID" value="<?php echo $projectID ?? '' ?>">
 </div>
 <div class="form-group d-none">
   <label for="projectPhaseID" class="nott t400 mb-0 text-primary">Project Phase ID</label>
   <input type="text" class ="form-control form-control-sm" name="projectPhaseID" value="">
 </div>
 <div class="row">

    <div class="form-group col-md-6 ">
      <label for="projectStartDate" class="nott t400 mb-0 text-primary">Project Start Date</label>
      <!-- Text  class ="form-control form-control-sm"fields for project dates validation -->
      <input type="text" class ="form-control-xs form-control-plaintext border-bottom px-2" id="projectStartDate" name="projectStartDate" value="" readonly>
    </div>
    <div class="form-group col-md-6 ">
      <label for="projectEndDate" class="nott t400 mb-0 text-primary">Project End Date</label>
      <input type="text" class =" form-control-xs form-control-plaintext border-bottom px-2" id="projectEndDate" name="projectEndDate" value="" readonly>
    </div>
 </div>

   <div class="row">
      <div class="col-md-6">
         <div class="form-group">
            <label for="projectPhaseName">Project Phase Name</label>
            <input type="text" name="projectPhaseName" class="form-control-xs border-bottom form-control-plaintext bg-light-blue px-2" value="" required>
         </div>
      </div>
      <div class="col-md-6 ">
         <div class="row phaseDates" id="phaseDates">
            <div class="col-md-6 form-group">
               <label for="phaseStartDate">Phase Start Date</label>
               <input type="date" id="phaseStartDate" name="phaseStartDate" class="form-control-xs border-bottom form-control-plaintext bg-light-blue px-2 date" value="" placeholder="Phase start date" required>
            </div>
            <div class="col-md-6 form-group">
               <label for="phaseEndDate">Phase End Date</label>
               <input type="date" id="phaseEndDate" name="phaseEndDate" class="form-control-xs border-bottom form-control-plaintext bg-light-blue px-2 date" value="" placeholder="Phase end date" required>
            </div>
            <!-- Error display container -->
            <div id="phaseDateValidationErrors" class="col-12" style="display: none;">
               <div id="phaseDateErrorMessage" class="alert alert-danger mt-2 mb-0"></div>
            </div>
         </div>
         <!-- JavaScript functionality moved to project_plan_manager.js -->
      </div>
      <div class="col-md-6 form-group my-2">
         <label for="phaseWorkHrs">Phase Work Hrs</label>
         <input type="text" name="phaseWorkHrs" class="form-control-xs border-bottom form-control-plaintext bg-light-blue px-2" value="" required>
      </div>
      <div class="col-md-6 form-group my-2">
         <label for="phaseWeighting">Phase Weighting</label>
         <input type="text" name="phaseWeighting" class="form-control-xs border-bottom form-control-plaintext bg-light-blue px-2" value="" required>
      </div>
      <div class="col-md-6 form-group d-flex align-items-center my-2">        
         <div class="form-check form-switch ">
            <input class="form-check-input" type="checkbox" id="billingMilestone" name="billingMilestone" value="1">
            <label for="billingMilestone" class="form-check-label">Billing Milestone</label>
         </div>
      </div>  
   </div>
<?php
// var_dump($projectData['timeline']);
?>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      const phaseStartDateInput = document.getElementById('phaseStartDate');
      const phaseEndDateInput = document.getElementById('phaseEndDate');
      const projectStartDateInput = document.getElementById('projectStartDate');
      const projectEndDateInput = document.getElementById('projectEndDate');
      
      if (!projectStartDateInput || !projectEndDateInput) {
         return;
      }
      
      const startDate = '<?= $projectData['timeline']['setProjectStartDate'] ?>';
      const endDate = '<?= $projectData['timeline']['setProjectEndDate'] ?>';
      projectStartDateInput.value = startDate;
      projectEndDateInput.value = endDate;
      const phaseDates = document.getElementById('phaseDates');
      // console.log(phaseDates);
      // console.log(projectStartDateInput, projectEndDateInput);

      // Function to clear all error messages
      const clearErrorMessages = () => {
         const existingErrors = phaseDates.querySelectorAll('.error-message');
         existingErrors.forEach(error => error.remove());
         
         // Reset input styling
         phaseStartDateInput.classList.remove('is-invalid', 'border-danger');
         phaseEndDateInput.classList.remove('is-invalid', 'border-danger');
         phaseStartDateInput.classList.add('form-control-plaintext');
         phaseEndDateInput.classList.add('form-control-plaintext');
      };

      // Function to show error message
      const showErrorMessage = (message) => {
         const errorMessage = document.createElement('div');
         errorMessage.textContent = message;
         errorMessage.classList.add('error-message');
         errorMessage.classList.add('text-danger');
         errorMessage.classList.add('fst-italic');
         errorMessage.classList.add('font-12');
         errorMessage.classList.add('text-center');
         errorMessage.classList.add('mb-2');
         errorMessage.classList.add('border-bottom');
         errorMessage.classList.add('border-danger');
         phaseDates.appendChild(errorMessage);
      };

      // Function to validate phase dates against project boundaries
      const validatePhaseProjectBoundaries = () => {
         const phaseStartDate = new Date(phaseStartDateInput.value);
         const phaseEndDate = new Date(phaseEndDateInput.value);
         const projectStartDate = new Date(projectStartDateInput.value);
         const projectEndDate = new Date(projectEndDateInput.value);
         
         // Check if dates are valid
         if (isNaN(phaseStartDate.getTime()) || isNaN(phaseEndDate.getTime()) || 
             isNaN(projectStartDate.getTime()) || isNaN(projectEndDate.getTime())) {
            return { isValid: true }; // Skip validation if dates are invalid
         }

         const errors = [];

         // Check if phase start date is before project start date
         if (phaseStartDate < projectStartDate) {
            errors.push('Phase start date cannot be before project start date');
         }

         // Check if phase end date is after project end date
         if (phaseEndDate > projectEndDate) {
            errors.push('Phase end date cannot be after project end date');
         }

         return {
            isValid: errors.length === 0,
            errors: errors
         };
      };

      // Function to validate phase date logic (end date not before start date)
      const validatePhaseDateLogic = () => {
         const phaseStartDate = new Date(phaseStartDateInput.value);
         const phaseEndDate = new Date(phaseEndDateInput.value);
         
         // Check if dates are valid
         if (isNaN(phaseStartDate.getTime()) || isNaN(phaseEndDate.getTime())) {
            return { isValid: true }; // Skip validation if dates are invalid
         }

         if (phaseEndDate < phaseStartDate) {
            return {
               isValid: false,
               errors: ['Phase end date cannot be before phase start date']
            };
         }


         return { isValid: true };
      };

      // Main validation function that combines both validations
      const validatePhaseDates = () => {
         // Clear previous errors
         clearErrorMessages();

         // Validate phase date logic first
         const dateLogicValidation = validatePhaseDateLogic();
         if (!dateLogicValidation.isValid) {
            showErrorMessage(dateLogicValidation.errors[0]);
            phaseEndDateInput.value = phaseStartDateInput.value; // Auto-correct
            phaseEndDateInput.classList.add('is-invalid', 'border-danger');
            phaseEndDateInput.classList.remove('form-control-plaintext');
            phaseEndDateInput.classList.add('form-control');
            return false;
         }

         // Validate project boundaries
         const boundaryValidation = validatePhaseProjectBoundaries();
         if (!boundaryValidation.isValid) {
            showErrorMessage(boundaryValidation.errors.join('. '));
            phaseStartDateInput.classList.add('is-invalid', 'border-danger');
            phaseEndDateInput.classList.add('is-invalid', 'border-danger');
            phaseStartDateInput.classList.remove('form-control-plaintext');
            phaseEndDateInput.classList.remove('form-control-plaintext');
            phaseStartDateInput.classList.add('form-control');
            phaseEndDateInput.classList.add('form-control');
            return false;
         }

         // If all validations pass, show success styling
         phaseStartDateInput.classList.add('is-valid');
         phaseEndDateInput.classList.add('is-valid');
         return true;
      };

      // Add event listeners
      projectStartDateInput.addEventListener('change', validatePhaseDates);
      projectEndDateInput.addEventListener('change', validatePhaseDates);
      phaseStartDateInput.addEventListener('change', validatePhaseDates);
      phaseEndDateInput.addEventListener('change', validatePhaseDates);

      // Edit phase functions
      const editPhase = document.querySelectorAll('.editPhaseBtn');
      editPhase.forEach(phase => {
         phase.addEventListener('click', () => {
            const phaseID = phase.dataset.projectPhaseID;
            const phaseName = phase.dataset.projectPhaseName;
            const phaseWorkHrs = phase.dataset.phaseWorkHrs;
            const phaseWeighting = phase.dataset.phaseWeighting;
             //get the phase form
             const phaseForm = document.querySelector('#manage_project_phase form');
             if (!phaseForm) {
                return;
             }

             //data from button 
             const data = phase.dataset;
             // console.log(data);

            // Map form fields to their corresponding data attributes
             const fieldMapping = {
                'projectPhaseID': 'projectPhaseId',
                'projectID': 'projectId',
                'projectPhaseName': 'projectPhaseName',
                'phaseWorkHrs': 'phaseWorkHrs',
                'phaseWeighting': 'phaseWeighting',
                'phaseStartDate': 'phaseStartDate',
                'phaseEndDate': 'phaseEndDate',
                'billingMilestone': 'billingMilestone'
             }
             // fill regular fields
             for (const [field, dataKey] of Object.entries(fieldMapping)) {
                const input = phaseForm.querySelector(`input[name="${field}"]`);
                if (input) {
                  // console.log('input', input, 'data[dataKey]', data[dataKey], 'dataKey', dataKey);
                   input.value = data[dataKey] || '';
                }
             }             
         });
      });
   });
</script>