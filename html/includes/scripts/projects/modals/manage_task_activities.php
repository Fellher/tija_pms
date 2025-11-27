<!-- Modal Edit Activity-->
<div class="modal fade" id="editActivity_<?php echo $subTask->subtaskID ?>" tabindex="-1" aria-labelledby="editActivityLabel<?php echo $subTask->subtaskID ?>" aria-hidden="true">
  	<div class="modal-dialog modal-dialog-centered">
	 	<form class="modal-content" action="<?php echo $base ?>php/scripts/projects/manage_sub_task.php" method="post" enctype="multipart/form-data">
			<div class="modal-header">
		  		<h5 class="modal-title" id="editActivityLabel<?php echo $subTask->subtaskID ?>">  Edit <?php echo $subTask->subTaskName ?> Activity</h5>
		  		<button type="button"  class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" name="subtaskID" value="<?php echo $subTask->subtaskID ?>">
				<div class="form-group">
					<small class="text-primary">  Activity Name</small>
					<input type="text" name="subTaskName" class="form-control form-control-sm form-control-plaintext bg-light-blue" value="<?php echo $subTask->subTaskName ?>">
				</div>
				<div class="form-group col-md-12">
					<small class="text-primary" for="subtaskNarrative">Subtask Description/Notes </small>
					<textarea class="form-control borderless-mini" id="subtaskNarrative_<?php echo $subTask->subtaskID ?>" name="subTaskDescription"   rows="3" placeholder="Edit subtask Description/Notes"><?php echo $subTask->subTaskDescription ?></textarea>
				</div>
				<div class="form-group col-md-12">
					<small class="text-primary" for="assignedTo">Parent Task</small>
					<select class="form-control form-control-sm border-0 bg-light-blue form-control-plaintext" name="projectTaskID"   required>									
						<?php echo Form::populate_select_element_from_object($phaseTasks, 'projectTaskID', 'projectTaskName', $subTask->projectTaskID , '' , 'Select Task ') ?>
					</select>
				</div>
				<div class="form-group ">
					<small class="text-primary"> Deadline</small>
		  			<input type="text"  name="subtaskDueDate" class="form-control form-control-sm text-left component-datepicker form-control-plaintext border-bottom bg-light-blue past-enabled" placeholder="YYYY-MM-DD"  id="date" value="<?php echo isset($subTask->subtaskDueDate) ? $subTask->subtaskDueDate : '' ?>">
		  		</div>
		
				<div class="form-group ">
					<small class="text-primary">Allocated Work Hour Estimate</small>
					<input type="text" name="subTaskAllocatedWorkHours" class="form-control form-control-sm form-control-plaintext border-bottom bg-light-blue" placeholder="00:00" value="<?php echo isset($subTask->subTaskAllocatedWorkHours) ?  Utility::format_time($subTask->subTaskAllocatedWorkHours, ':', false) : '' ?>">
				</div>	
			</div>
			<div class="modal-footer">
		  		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		  		<button type="submit" class="btn btn-primary">Save changes</button>
			</div>
	 	</form>
  	</div>
</div>
