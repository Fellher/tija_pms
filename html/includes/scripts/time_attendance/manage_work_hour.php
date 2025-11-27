<div class="card card-body d-none   editTimeLogClass" id="editTimelogDiv<?php echo $timelogID;  ?>">
	<form action="<?php echo $base ."php/scripts/time_attendance/manage_task_time_log.php"; ?>" method="post" class="editTimelogForm" enctype="multipart/form-data">
		<!-- <h4 class="gray-heading">Work Hour</h4> -->
		<h4 class="bs-gray-300 mt-2 px-3 py-2">Edit Work Hour  <span class="float-end"> <a href=""><i class="fa-solid fa-close"></i></a></span> </h4>	
		<input type="hidden" name="timelogID" value="<?php echo $timelogID; ?>" class="form-control form-control-xs">
		<?php 
		// var_dump($timelog);
		include "includes/scripts/time_attendance/manage_work_hour_script.php"; ?>
		<div class="col-sm-6 clearfix float-end">
			<button type="submit" class="btn btn-primary btn-sm w-50 float-end">Save</button>
		</div>
	</form>
</div> 