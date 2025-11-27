<div class="row my-4">
	<div class="fbox-icon fbox-outline ">
		<i class="<?php echo $activityTypeDetails->iconlink ?>"></i>
	</div>
	<div class="col-sm border border-2 shadow <?php echo $bgColor; ?> ">
		<div class="col-12 ">			
			<div class="d-flex align-items-stretch">
				<div class="col-md-10">
					<h4 class="row t500 font-18 mt-2 mx-3 "><?php echo $activity->activityName; ?></h4>
					<span class="fs-7 fst-italic mx-3">
						<span class="t600 me-3">Deadline:</span> <?php 
						if ($activity->activityCategory == 'deadline') {
							 echo $activity->deadlineDate;
						} elseif ($activity->activityCategory == 'duration') {
							 echo $activity->deadlineDate .' - '.$activity->deadlineDate;
						} ?>
					</span>	
				</div>
				<div class="col-md-2 align-self-center">
			
					<div class=" float-end">					
						<?php 
							$ownerName = Core::user_name($activity->activityOwnerID, $DBConn); 
							$ownerNameArr = explode(" ", $ownerName);
						?> 
						<a href="" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="<?php echo $ownerName ?>">
							<i class="border border-dark rounded-circle p-2 font-16 me-3 text-uppercase mt-3"><?php echo $ownerNameArr[0][0].  $ownerNameArr[1][0]  ?></i>	
						</a>
						<?php 
						if ($activity->activityStatus !== 'closed')  { ?>
							<span class=""> 
								<a href="" data-bs-toggle="modal" data-bs-target="#<?php echo "editActivity{$activity->saleActivityID}" ?>">
									<i class="uil-edit-alt text-info"></i>
								</a>
								<!-- <a href=""><i class="bi-clock-history text-info"></i></a> -->
								<a href=""  data-bs-toggle="modal" data-bs-target="#<?php echo "deleteActivity{$activity->saleActivityID}" ?>"><i class="bi-trash3-fill text-danger"></i></a>
								<button  class="btn btn-link" id="<?php echo "closeActivity{$activity->saleActivityID}" ?>" ><i class="bi-check2-circle  text-info"></i> </button>
							</span>
					
							<form id="closeActivityForm<?php echo $activity->saleActivityID; ?>" action="<?php echo $base ."php/scripts/work/manage_sale_activity.php"; ?>" class= "d-none" method="POST" >
								<input type="text" name="saleActivityID" value="<?php echo $activity->saleActivityID ?>">
								<input type="text" name="activityStatus" value="closed">
							</form>			
							<?php 
						} ?>			
					</div>
				
				</div>
			</div>		
			<div class="col-12">				
				
				<div class="col-12 border-top p-3">
					<?php echo $activity->description; ?>
				</div>
			</div>	
				
			<?php  $activityID= $activity->saleActivityID; ?>
			<script>
				const closeActivity<?php echo $activityID; ?> = document.getElementById('closeActivity<?php echo $activityID; ?>');		
					closeActivity<?php echo $activityID; ?>.addEventListener('click', completeActivity<?php echo $activityID; ?>);
					 function completeActivity<?php echo $activityID; ?> () {
					 	let closeActivityForm= document.getElementById('closeActivityForm<?php echo $activity->saleActivityID; ?>');
					 	closeActivityForm.submit();							 														 	
					 }
			</script> 
			<!-- Delete activity Modal -->
			<?php 
			echo Utility::form_modal_header("editActivity{$activity->saleActivityID}", "work/manage_sale_activity.php", "Edit {$activity->activityName}", array('modal-lg', 'modal-dialog-centered'), $base); 
				include 'includes/work/modals/edit_sale_activity.php';
			echo Utility::form_modal_footer($save='Submit', "", "", true); 
			
			echo Utility::form_modal_header ("deleteActivity{$activity->saleActivityID}", "work/manage_sale_activity.php", "", array( 'modal-dialog-centered'), $base); ?>
				<div class="form-group">
					<input type="hidden" class="form-control" name="saleActivityID" value="<?php echo $activity->saleActivityID; ?>">
					<input type="hidden" class="form-control" name="Suspended" value="Y">
				</div>
				<p class="font-18 mb-0"> Are you sure you want to delete To Do Activity <?php echo $activity->activityName; ?> ?</p>
				<?php
			 	$class="btn btn-danger btn-xs ";									
			echo Utility::form_modal_footer($save='Yes Delete', "deleteActivity{$activity->saleActivityID}", "btn btn-primary btn-xs" , true); ?>
		</div>																			
	</div>
</div> 