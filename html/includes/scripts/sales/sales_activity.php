<?php 
foreach ($activities as $activity) {

	// var_dump($activity);
	$activityTypeDetails = Sales::tija_activity_types(array('activityTypeID'=>$activity->activityTypeID), true, $DBConn);
	$activityOwner = Core::user_name($activity->activityOwnerID, $DBConn);
	$bgColor= ""; 
		// var_dump($activityTypeDetails); ?>
		<div class="row my-4">
			<span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
				<i class="uil uil-clipboard-alt fs-26"></i>
			</span>

			
			<div class="col-sm border border-2 shadow <?php echo $bgColor; ?> ">
				<div class="col-12 ">			
					<div class="d-flex align-items-stretch">
						<div class="col-md-10">
							<h4 class="row t500 font-20 mt-2 mx-3 "><?php echo $activity->activityName; ?></h4>
							<span class="fs-12 fst-italic mx-3">
								<span class="t600 me-3 ">Deadline Date:</span> <?php 
								if ($activity->activityCategory == 'one_off') {
									echo $activity->salesActivityDate;
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
									$initials = Utility::generate_initials($ownerName);
								?> 
								<a href="" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="<?php echo $ownerName ?>">
									<i class="border border-dark rounded-circle p-2 font-16 me-3 text-uppercase mt-3"><?php echo $initials;  ?></i>	
								</a>
								<?php 
								if ($activity->activityStatus !== 'completed')  { ?>
									<span class=""> 
										<a href="" 
											class="editSalesActivity btn btn-icon btn-primary-transparent rounded-pill btn-wave me-3"
											data-bs-toggle="modal" 
											data-bs-target="#manageActivityModal" 
											data-bs-backdrop="static" 
											data-bs-keyboard="false"
											data-salesActivityID="<?php echo $activity->salesActivityID; ?>"
											data-activityName="<?php echo $activity->activityName; ?>"
											data-activityTypeID="<?php echo $activity->activityTypeID; ?>"
											data-salesCaseID="<?php echo $activity->salesCaseID; ?>"
											data-orgDataID="<?php echo $activity->orgDataID; ?>"
											data-entityID="<?php echo $activity->entityID; ?>"
											data-clientID="<?php echo $activity->clientID; ?>"
											data-salesPersonID="<?php echo $activity->salesPersonID; ?>"
											data-salesActivityDate="<?php echo $activity->salesActivityDate; ?>"
											data-activityTime="<?php echo $activity->activityTime; ?>"
											data-activityDescription="<?php echo $activity->activityDescription; ?>"
											data-activityOwnerID="<?php echo $activity->activityOwnerID; ?>"
											data-activityStatus="<?php echo $activity->activityStatus; ?>"
											data-activityCategory="<?php echo $activity->activityCategory; ?>"
											data-activityDeadline="<?php echo $activity->activityDeadline; ?>"
											data-activityStartDate="<?php echo $activity->activityStartDate; ?>"
											data-activityCloseDate="<?php echo $activity->activityCloseDate; ?>"
											data-activityCloseStatus="<?php echo $activity->activityCloseStatus; ?>"
											data-activityNotes="<?php echo $activity->ActivityNotes; ?>"
										>
											<i class="ti ti-edit-circle"></i>
										</a>
										<!-- <a href=""><i class="bi-clock-history text-info"></i></a> -->
										<a href=""  data-bs-toggle="modal" data-bs-target="#<?php echo "deleteActivity{$activity->salesActivityID}" ?>" class="btn btn-icon btn-info-transparent rounded-pill btn-wave me-3"><i class="bi-trash3-fill text-danger"></i></a>
										<button  class="btn btn-icon btn-info-transparent rounded-pill btn-wave me-3" id="<?php echo "closeActivity{$activity->salesActivityID}" ?>" ><i class="bi-check2-circle  text-info"></i> </button>
									</span>
							
									<form id="closeActivityForm<?php echo $activity->salesActivityID; ?>" action="<?php echo $base ."php/scripts/sales/manage_sales_activity.php"; ?>" class= "d-none" method="POST" >
										<input type="text" name="salesActivityID" value="<?php echo $activity->salesActivityID ?>">
										<input type="text" name="activityStatus" value="completed">
										<input type="text" name="activityCloseStatus" value="closed">
									</form>			
									<?php 
								}?>			
							</div>				
						</div>
					</div>		
					<div class="col-12">	
						<div class="col-12 border-top p-3">
							<?php echo $activity->activityDescription; ?>
						</div>
					</div>
						
					<?php  $activityID= $activity->salesActivityID; 					
					 ?>
				</div>																			
			</div>
		</div>
		

		<?php	
}

echo Utility::form_modal_header (
	"deleteActivity", 
	"work/manage_sale_activity.php",  
	array( 'modal-dialog-centered'), 
	$base); 
	?>
	<div class="form-group">
		<input type="hidden" class="form-control" name="salesActivityID" value="">
		<input type="hidden" class="form-control" name="Suspended" value="Y">
	</div>
	<p class="font-18 mb-0"> Are you sure you want to delete To Do Activity <span class="activityName"> </span>?</p>
	<?php
	$class="btn btn-danger btn-xs ";									
echo Utility::form_modal_footer($save='Yes Delete', "deleteActivity", "btn btn-primary btn-xs" , true);
?>
<script>
			const closeActivity<?php echo $activityID; ?> = document.getElementById('closeActivity<?php echo $activityID; ?>');		
			closeActivity<?php echo $activityID; ?>.addEventListener('click', completeActivity<?php echo $activityID; ?>);
			
			function completeActivity<?php echo $activityID; ?> () {
				let closeActivityForm = document.getElementById('closeActivityForm<?php echo $activity->salesActivityID; ?>');
				closeActivityForm.submit();							 														 	
			}

			// New function to prepopulate the salesActivityForm
			function prepopulateSalesActivityForm(activity) {
				const form = document.querySelector('.salesActivityForm'); // Assuming the form has this class
					// Get all data attributes from the button
					const data = activity.dataset;
							console.log(data);

				const fields = {
						salesActivityID: 'salesactivityid',
						activityName: 'activityname',
						activityTypeID: 'activitytypeid',
						salesCaseID: 'salescaseid',
						orgDataID: 'orgdataid',
						entityID: 'entityid',
						clientID: 'clientid',
						salesPersonID: 'salespersonid',
						salesActivityDate: 'salesactivitydate',
						activityTime: 'activitytime',
						activityDescription: 'activitydescription',
						activityOwnerID: 'activityownerid',
						activityStatus: 'activitystatus',
						activityCategory: 'activitycategory',
						activityDeadline: 'activitydeadline',
						activityStartDate: 'activitystartdate',
						activityCloseDate: 'activityclosedate',
						activityCloseStatus: 'activityclosestatus',
						ActivityNotes: 'activitynotes'
				};
				console.log(fields);

				for (const [key, value] of Object.entries(fields)) {
						const input = form.querySelector(`input[name="${key}"]`) || form.querySelector(`textarea[name="${key}"]`);
						if (input) {
							input.value = activity.dataset[value];
						}
				}

				tinymce.init({
							selector: '#activitydescription'
						});
				//   hanlde tinyMCE
				const tinyMCE =  tinymce.get('activitydescription');
				if (tinyMCE) {
						tinyMCE.value = activity.dataset['activitydescription'];
				}


				// Handle select elements
				const selects = ['activityTypeID', 'clientID'];
				selects.forEach(selectName => {
						const select = form.querySelector(`select[name="${selectName}"]`);
						if (select && data[fields[selectName]]) {
							select.value = data[fields[selectName]];
						}
				});
			}

			// Attach event listener to edit link
			document.querySelectorAll('.editSalesActivity').forEach(activity => {
				activity.addEventListener('click', function() {
						prepopulateSalesActivityForm(this);
				});
			});
		</script> 
