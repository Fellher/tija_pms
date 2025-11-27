<div class="comnplete_activity_form row" id='completeActivityForm'>
   <div class="d-none">
      <div class="form-group col-md-6 ">
         <label class="form-label">Complete Activity ID</label>
         <input type="text" name="activityID" value="" class="form-control form-control-sm">
      </div>
      <div class="form-group col-md-6">
         <label class="form-label">Complete Activity Value</label>
         <input type="text" name="complete" value="Y" class="form-control  form-control-sm">
      </div>
      <div class="form-group col-md-6">
         <label class="form-label">EmployeeID</label>
      <input type="text" name="employeeID" value="" class="form-control form-control-sm">
      </div>
      <div class="form-group col-md-6">
         <label class="form-label">Activity Segment</label>
         <input type="text" name="workSegmentID" value="" class="form-control form-control-sm">
      </div>
      <div class="form-group col-md-6">
         <label class="form-label">Task Type</label>
         <input type="text" name="taskType" value="" class="form-control form-control-sm">
      </div>

      <div class="form-group col-md-6">
         <label class="form-label">Instance</label>
         <input type="text" name="instance" value="" class="form-control form-control-sm">
      </div>
      <div class="form-group col-md-6">
         <label class="form-label">Recuring Instance ID</label>
         <input type="text" name="recurringInstanceID" value="" class="form-control form-control-sm">
      </div>

      <div class="form-group col-md-6">
         <label class="form-label">Activity Date</label>
         <input type="text" name="activityDate" id="date" value="" class="form-control form-control-sm" placeholder="yyyy-mm-dd" required>
      </div>
   </div>

   <div class="form-group col-md-6">
      <label class="form-label">Work Type</label>
      <select name="workTypeID" class="form-select form-select-sm">
         <option value="">Select Work Type</option>
         <?= Form::populate_select_element_from_object($workTypes, "workTypeID", 'workTypeName', '','','Select Work Type'); ?>
      </select>
   </div>
   <div class="form-group my-2 col-md-6">
      <label class="form-label" class="mb-0">Time Duration</label>
      <input type="text" name="activityDuration" id="inlinetimePreTime" value="" class="form-control-sm form-control-plaintext border-bottom  ">
   </div>
    
   <div class="form-group col-md-12 py-2">
      <label for="description" class="col-md-12 nott mb-0 t500 text-dark mb-2  "> Add Task Notes</label>
      <textarea class="form-control borderless-mini" name="taskNarrative"   rows="3" placeholder="Edit time Log log description"></textarea>
   </div>
   <div class="col-lg-12 bottommargin">
      <label class="col-md-12 nott mb-0 t500 text-dark  ">Attarch Supporting Files:</label><br>
      <input  id="formFileMultiple" type="file" class="form-control form-control-sm" name="fileAttachments[]" multiple data-show-preview="false">
   </div> 

   <div class="m-3">
      <p class="fs-16">  Are you sure you want to mak the activity <span class= "fw-bolder text-dark fst-italic activityName"> </span>  as complete </p>                     
   </div> 
   
   

</div>
