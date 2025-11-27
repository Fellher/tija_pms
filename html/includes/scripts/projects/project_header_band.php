<div class="container-fluid py-3">
	<div class="col-md-12 card card-body shadow-sm"> 
		<div class="row  alert-dismissible fade show border-solid d-flex justify-content-between align-items-stretch" role="alert" >
			<div class="col-md-2 pt-2 ">
				<div class="border-right">
					<h4 class="mb-0 t400 font-16">Project Value </h4>
					<div class="col-md  pl-0">
						<div class="font-22">
							<?php 
							$projectValue= number_format($projectDetails->projectValue, '2', '.', ' ');
							echo "<span class='font-14'>KES </span>{$projectValue}  "; ?>
						</div>
					</div>
				</div>						
			</div>

			<div class="col-md py-2">
				<div class="border-right ">
					<div class=" row">
						<div class="col-7 text-left">	
							<h4 class="mb-0 t400">Project Plan </h4>
							<span class="font-22 col-12">100%</span><br>
							<span>Billable Rate</span>	
						</div>
													
					</div>
				</div>
			</div>

			<div class="col-md py-2">
				<div class="border-right ">
					<div class=" row">
						<div class="col-7 text-left">	
							<h4 class="mb-0 t400">Project Financials </h4>
							Kes <span class="font-22 "><?php echo isset($projectDetails->billableRateValue) ? number_format($projectDetails->billableRateValue, 2,'.', ' ') : 0; ?></span><br>
							<span class="font-12">Billed Per Hour</span>	
						</div>														
					</div>
				</div>
			</div>
			<div class="col-md py-2">
				<div class="border-right ">
					<div class=" row">
						<div class="col-7 text-left">	
							<h4 class="mb-0 t400">Collaboration </h4>
							<span class="font-22 ">
								<?php echo (isset($projectTasks) && $projectTasks &&  count($projectTasks) !==0) ? count($projectTasks) : 0; ?>

							</span><br>
							<span class="font-12">Project Tasks</span>	
						</div>														
					</div>
				</div>
			</div>
			<!-- <button type="button"  class="btn-close nobg" >
				<a class="nobg" tabindex="0"  role="button" data-bs-toggle="popover" data-trigger="focus" title="Organize your work with projects" data-bg-content="Before you start tracking your work, create projects, assign clients, and add tasks. You can choose from advanced billing rates, set up approval workflow, add custom budgets, and set task estimates. Assign tasks to your team and monitor allocated time."><i class="icon-cog"></i></a>
				
			</button> -->
		</div>				
	</div>
</div>