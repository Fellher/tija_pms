<form class="p-3" action="<?php echo $base ?>php/scripts/time_attendance/manage_task_time_log.php" method="post" enctype="multipart/form-data">
	<?php include "includes/scripts/time_attendance/manage_work_hour_script_improved.php"; ?>
	<div class="col-12 modal-footer pb-0 mt-3">
		<button type="button" class="btn btn-secondary w-25" data-bs-toggle="collapse" data-bs-target="#add_work_hours">Cancel</button>
		<button type="submit" class="btn btn-primary float-end w-25">Save Task Log</button>
	</div>
</form>

