<!-- Edit Phase Details Collapse 
================================================= -->
<div class="collapse my-3 bg-light" id="<?php echo "editPhase{$phase->projectPhaseID}" ?>">
	<form action="<?php echo "{$base}php/scripts/projects/manage_phase.php" ?>" method="POST" class="my-0 managePhaseCollapseForm">	
		<div class="row">	
		<input type="hidden" name="projectPhaseID" value="<?php echo $phase->projectPhaseID ?>" >	
		<input type="hidden" name="projectID" value="<?php echo $phase->projectID ?>" >							
			<div class="form-group col-md">
				<label for="" class="nott t400 mb-0 text-primary"> Phase Name</label>
				<input type="text" name="projectPhaseName"  class=" form-control-xs form-control-plaintext border-bottom bg-light-blue" value=" <?php echo $phase->projectPhaseName ?>">
			</div>
			<div class="form-group col-md">
				<label for="" class="nott t400 mb-0 text-primary"> Phase hours Estimate</label>
				<input type="text" name="phaseWorkHrs" id="" class="form-control-xs form-control-plaintext border-bottom bg-light-blue " value=" <?php echo $phase->phaseWorkHrs ?>">
			</div>
			<div class="form-group col-md">
				<label for="" class="nott t400 mb-0 text-primary"> Phase Weighting</label>
				<input type="text" name="phaseWeighting" id="" class=" form-control-xs form-control-plaintext border-bottom bg-light-blue" value="<?php echo $phase->phaseWeighting; ?>">
			</div>
			<div class="col-md phaseDates" id="phaseDatesEdit">
				<div class="row">
					<div class="col-md-6 form-group">
						<label for="phaseStartDate">Phase Start Date</label>
						<input type="text" id="phaseStartDate" name="phaseStartDate" class="form-control-xs border-bottom form-control-plaintext bg-light-blue px-2 date" value="<?= $phase->phaseStartDate ?>">
					</div>
					<div class="col-md-6 form-group">
						<label for="phaseEndDate">Phase End Date</label>
						<input type="text" id="phaseEndDate" name="phaseEndDate" class="form-control-xs border-bottom form-control-plaintext bg-light-blue px-2 date" value="<?= $phase->phaseEndDate ?>">
					</div>
				</div>            
         </div>
			<div class="form-check col-md pt-3">
			  	<input class="form-check-input" type="checkbox" name="billingMilestone" value="Y" id="<?php echo "billingMilestone{$phase->projectPhaseID}" ?>" <?= (isset($phase->billingMilestone) && $phase->billingMilestone == 'Y') ? "checked" : '' ?>>
			  	<label class="form-check-label" for="<?php echo "billingMilestone{$phase->projectPhaseID}" ?>"> Billing Milestone </label>
			</div>	
			<div class="col-md-1">
				<button type="submit" class="btn btn-primary btn-sm mt-3"> Submit Edit</button>
			</div>								
		</div>										
	</form> 
</div>


				