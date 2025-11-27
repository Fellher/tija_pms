<div class="row manage_work_hour_form" id="manage_work_hour_form">
   <div class="col-md">
      <div class="form-group my-2">
         <label for="projecttID" class="text-primary"> Project</label>
         <select name="projectID" id="projecttID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
            <?php echo Form:: populate_select_element_from_grouped_object($projectArray, 'projectID', 'projectName',  '', '', $blankText='Select:') ?>
         </select>
      </div>
   </div>
   <div class="col-md">
      <div class="form-group my-2">
         <label for="projectPhaseID" class="text-primary"> Project Phase</label>
         <select name="projectPhaseID" id="projectPhaseID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
            <?php echo Form:: populate_select_element_from_object($projectPhases, 'projectPhaseID', 'projectPhaseName',  '', '', $blankText='Select:') ?>
         </select>
      </div>
   </div>
   <div class="col-md"> 
      <div class="form-group my-2">
         <label for="projectTaskID" class="text-primary"> Project Task</label>
         <select name="projectTaskID" id="projectTaskID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
            <?php echo Form:: populate_select_element_from_object($tasks, 'projectTaskID', 'projectTaskName',  '', '', $blankText='Select:') ?>
         </select>
      </div>
   </div>
   <div class="col-md">
      <div class="form-group my-2">
         <label for="projectTaskActivityID" class="text-primary"> Project Task Activity</label>
         <select name="subtaskID" id="subtaskID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
            <?php echo Form:: populate_select_element_from_object($subTasks, 'subtaskID', 'subTaskName',  '', '', $blankText='Select:') ?>
         </select>
      </div>
   </div>
   <div class="row">
      <div class="form-group col-md-4 my-2">
         <label for="workType" class="nott mb-0 t500 text-primary "> Work Type</label>
         <select name="workTypeID" id="workTypeID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
               <?php echo Form:: populate_select_element_from_object($workType, 'workTypeID', 'workTypeName',  '', '', $blankText='Select:') ?>
            <option value="addNew"> New Work Type</option>
         </select>
      </div> 

      <div class="form-group col-md-4 my-2">
         <label for="taskDate" class="nott mb-0 t500 text-primary ">Task Date</label>
         <input type="text" id="date" value="<?php  echo date_format($dt,'Y-m-d') ?>"  name="taskDate"  class="form-control  form-control-sm form-control-plaintext bg-light-blue text-left component-datepicker past-enabled" placeholder="YYYY-MM-DD">
      </div>	

      <div class=" form-group col-md-4 my-2 ">	
         <label for="form1" class="d-block nott mb-0 t500 text-primary  ">Task Time Duration </label>
         <div class="row mt-0">
            <div class="col-md pl-0">
               <input type="text" id="hours"  name="hours" step="1" min="0" max="24" class="form-control  form-control-sm form-control-plaintext bg-light-blue center px-2" placeholder="hours"> 
            </div>	:
            <div class="col-md  ">
               <input type="number" id="minutes" name='minutes' step="1" min="0" max="60"  class="form-control form-control-sm form-control-plaintext bg-light-blue center px-2" placeholder="minutes">
            </div>												
         </div>
      </div>
               
      <div class="col-lg-8 bottommargin my-2">
         <label class="col-md-12 nott mb-0 t500 text-primary ">Attarch Supporting Files:</label><br>
         <input  id="formFileMultiple" type="file" class="form-control form-control-sm" name="fileAttachments[]" multiple data-show-preview="false">
      </div> 
      <?php
      // $selectedStatusID ="";
      // if(isset($task) && $task->projectID == 4){
         // var_dump($task);
      //    echo $selectedStatusID = 6;
      // var_dump($workType);

      //    echo '<input type="text" name="" value="'.$selectedStatusID.'">';
      // }
      $taskStatusListArr = Projects::task_status(array('Suspended'=>'N'), false, $DBConn);
      ?>  
      <div class="col-md-4 form-group my-2">
         <label for="" class="nott t400 mb-0 text-primary"> Task Status</label>
         <select name="taskStatusID" id="taskStatusID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
         <?php echo Form::populate_select_element_from_object($taskStatusListArr, 'taskStatusID', 'taskStatusName', '', '', 'Select task Status') ?>
         </select>				
      </div> 
   </div>

   <div class="col-md-12">
      <div class="form-group col-md-12 py-2 my-2">
         <label for="description" class="col-md-12 nott mb-0 t500 text-dark mb-2  "> Add Task Notes</label>
         <textarea class="form-control borderless-mini" name="taskNarrative"   rows="3" placeholder="Edit time Log log description"></textarea>
      </div>
   </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.submit_work_hour').forEach(button => {
            button.addEventListener('click', function() {
            form = document.querySelector('#manage_work_hour_form');
            if(!form) return;
               // Validate the form before submission
            
               // Get all data attributes from the button
               const data = this.dataset;

               console.dir(data, false);

               // Map form fields to their corresponding data attributes
               const fieldMappings = {
                  'projectID': 'projectid',
                  'projectPhaseID': 'projectphaseid',
                  'projectTaskID': 'projecttaskid',
                  'subtaskID': 'subtaskid',
                  'taskStatusID': 'taskstatusid',
                  
               };
              

               // Loop through the field mappings and set the values
               for (const [field, dataField] of Object.entries(fieldMappings)) {
                  const input = form.querySelector(`input[name="${field}"]`) || form.querySelector(`select[name="${field}"]`);
                  if (input) {
                     input.value = data[dataField];
                     // set the input to readonly
                     input.setAttribute('readonly', true);
                     // input.setAttribute('disabled', true);
                     input.classList.add('bg-light-orange');
                     input.classList.add('px-2');
                     console.log(field);
                     console.log(input.value);
                     console.log(data[dataField]);
                     // input.parentElement.classList.add('d-none');

                     // if (typeof data[dataField] === 'undefined') {
                     //    console.log(`Data field ${dataField} is undefined.`);
                     //    input.parentElement.parentElement.
                     //    continue;
                     // }
                     // if(input.value === ""){
                     //    input.parentElement.parentElement.remove();
                     //    // input.parentElement.classList.remove('bg-light-orange');
                     // }
                     

                  }
               }
         });
      });
   });
</script>