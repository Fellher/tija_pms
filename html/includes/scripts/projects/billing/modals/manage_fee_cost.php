<div id="manage_fee_cost_form">
   <div class="row">
      <div class="col-md ">
         <h5 class="bg-light py-1 px-2 mb-0 border-bottom border-primary mb-2">
            Product/Service Fee/Cost Information
         </h5>

         <div class="form-group">
            <label for="productTypeID"> Product typeID</label>
            <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="productTypeID" name="productTypeID" placeholder="Enter Product Type ID" value ="<?php echo $productTypeID; ?>" readonly>
            <label for="productTypeID"> Project ID</label>
            <input type="text" name="projectID" id="projectID"  class="form-control-plaintext form-control-sm border-bottom"  value="<?php echo $projectID; ?>" readonly>
         </div>
         <div class="form-group my-2">
            <label for="fee_cost_name">Fee/Cost Name</label>
            <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="feeCostName" name="feeCostName" placeholder="Enter Fee/Cost Name">
         </div>
         <div class="form-group my-2">
            <label for="fee_cost_description">Description</label>
            <textarea class="form-control borderless-mini" id="feeCostDescription" name="feeCostDescription" rows="3" placeholder="Enter Description"></textarea>
         </div>
         <?php $productTypes = Projects::product_types(array(), false, $DBConn); ?>
         <div class="form-group my-2">
            <label for="productTypeID">Product Type</label>
            <select class="form-control-plaintext form-control-sm border-bottom" id="productTypeID" name="productTypeID">
               <option value="">Select Product Type</option>
               <?php 
               if($productTypes) {
                  foreach ($productTypes as $productType): ?>
                     <option value="<?php echo $productType->productTypeID; ?>"><?php echo $productType->productTypeName; ?></option>
                  <?php endforeach;
               } else {
                  echo "<option value=''>No product types found</option>";
               }?>
               <option value="addProductTypes">Add Product Type</option>
            </select>
         </div>
         <div class="form-group">
            <label for="rosuctRateTypeID"> Work/Product Type</label>
            <select class="form-control-plaintext form-control-sm border-bottom" id="productRateTypeID" name="productRateTypeID">
               <option value="">Select Work/Product Type</option>
               <?php 
               if($productRateTypes) {
                  foreach ($productRateTypes as $productRateType): ?>
                     <option value="<?php echo $productRateType->productRateTypeID; ?>"><?php echo $productRateType->productRateTypeName; ?></option>
                  <?php endforeach;
               } else {
                  echo "<option value=''>No product rate types found</option>";
               }?>
               <option value="addProductRateTypes">Add Product Rate Type</option>
            </select>
         </div>
      </div>
      

      <div class="col-md ">
         <h5 class="bg-light py-1 px-2  border-bottom border-primary mb-2">
            Fee/Cost Rates
         </h5>
         <div class="row">
            <div class="form-group col-6 my-2">
               <label for="fee_cost_rate" class="text-primary">Quantity</label>
               <input type="text" class="form-control-plaintext form-control-sm border-bottom " id="productQuantity" name="productQuantity" placeholder="Enter Fee/Cost Quantity">
            </div>

            <div class="form-group col-6 my-2">
               <label for="productUnit" class="text-primary">Unit</label>
               <select class=" form-control-plaintext form-control-sm border-bottom" id="productUnit" name="productUnit">
                  <option value="">Select Unit</option>
                  <option value="hour">Hour</option>
                  <option value="day">Day</option>
                  <option value="week">Week</option>
                  <option value="month">Month</option>
                  <option value="year">Pieces</option>
                  <option value="unit">Unit</option>
               </select>
            </div>

            <div class="form-group col-6 my-2">
               <label for="unitPrice" class="text-primary">Unit Price</label>
               <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="unitPrice" name="unitPrice" placeholder="Enter Unit Price">
            </div>

            <div class="form-group col-6 my-2">
               <label for="unitCost" class="text-primary" >Unit Cost</label>
               <input type="text" class="form-control-plaintext form-control-sm border-bottom" id="unitCost" name="unitCost" placeholder="Enter unit Cost">
            </div>

            <div class="form-group col-6 my-2">
               <label for="vat" class="text-primary" > VAT</label>
               <select class="form-control-plaintext form-control-sm border-bottom" id="vat" name="vat">
                  <option value="">Select VAT</option>
                  <option value="0">0%</option>
                  <option value="5">5%</option>
                  <option value="10">10%</option>
                  <option value="15">15%</option>
                  <option value="20">20%</option>
               </select>
            </div>
       
            <div class="form-group col-6 my-2">
               <label for="dateOfCost" class="text-primary">Date of Cost</label>
               <input type="date" class="form-control-plaintext form-control-sm border-bottom date" id="dateOfCost" name="dateOfCost">
            </div>

            <div class="col-12">
               <h5 class="bg-light py-1 px-2  border-bottom border-primary mb-2">
                  Billing
               </h5>
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="billingType" class="text-primary">Billing Type</label>
               <!-- <label for="billable" class="text-primary">Billable</label> -->
               <select class="form-control-plaintext form-control-sm border-bottom" id="billableType" name="billable">
                  <option value="">Select Billable</option>
                  <option value="immediately">Immediately </option>
                  <option value="on_date">On date</option>
                  <option value="on_phase_completion">On completion of phase</option>
                  <option value="on_project_completion">On completion of project</option>
                  <option value="on_milestone_completion">On completion of milestone</option>
                  <option value="recurring">Recurring</option>
                  <option value="none_billable">Non Billable</option>
               </select>
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="billingDate" class="text-primary">Billing Date</label>
               <input type="date" class="form-control-plaintext form-control-sm border-bottom date" id="billingDate" placeholder="Select billing date" name="billingDate">
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="billingFrequency" class="text-primary">Billing Frequency(repeat Every)</label>
               <div class="row">
                  <div class="col-7">
                     <input type="number" class="form-control-plaintext form-control-sm border-bottom" id="billingFrequency" name="billingFrequency" placeholder="Enter billing frequency">
                  </div>
                  <div class="col-5">
                     <select class="form-control-plaintext form-control-sm border-bottom" id="billingFrequencyUnit" name="billingFrequencyUnit">
                        <option value="">Select Unit</option>                   
                        <option value="week">Week</option>
                        <option value="month" selected>Month</option>
                        <option value="year">Year</option>
                     </select>
                  </div>
               </div>
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="billingStartDate" class="text-primary">Billing Start Date</label>
               <input type="date" class="form-control-plaintext form-control-sm border-bottom date" id="billingStartDate" name="billingStartDate">
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="recurrenceEnd" class="text-primary">Recurrence End</label>
               <select class="form-control-plaintext form-control-sm border-bottom" id="recurrenceEnd" name="recurrenceEnd">
                  <option value="">Select Recurrence End</option>
                  <option value="never">Never</option>
                  <option value="number_of_times">Number of times</option>
                  <option value="on_date">On date</option>
               </select>
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="numberOFTimes" class="text-primary">Times</label>
               <input type="number" class="form-control-plaintext form-control-sm border-bottom " id="recurrencyTimes" name="recurrencyTimes" placeholder="Enter number of times">
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="billingEndDate" class="text-primary">Billing End Date</label>
               <input type="date" class="form-control-plaintext form-control-sm border-bottom date" id="billingEndDate" placeholder="recurrence end date" name="billingEndDate">
            </div>

            <?php $projectPhases = Projects::project_phases(array('projectID' => $projectID), false, $DBConn);?>

            <div class="form-group col-md-6 my-2">
               <label for="billingPhase" class="text-primary">Billing Phase</label>
               <select class="form-control-plaintext form-control-sm border-bottom" id="billingPhase" name="billingPhase">
                  <option value="">Select Billing Phase</option>
                  <?php foreach ($projectPhases as $projectPhase): ?>
                     <option value="<?php echo $projectPhase->projectPhaseID; ?>"><?php echo ucfirst(strtolower($projectPhase->projectPhaseName)); ?></option>
                  <?php endforeach; ?>
               </select>
            </div>

            <div class="form-group col-md-6 my-2">
               <label for="billingMilestone" class="text-primary">Billing Milestone</label>
               <select class="form-control-plaintext form-control-sm border-bottom" id="billingMilestone" name="billingMilestone">
                  <option value="">Select Billing Milestone</option>
                  <?php foreach ($projectPhases as $projectPhase): ?>
                     <option value="<?php echo $projectPhase->projectPhaseID; ?>"><?php echo ucfirst(strtolower($projectPhase->projectPhaseName)); ?></option>
                  <?php endforeach; ?>
               </select>
            </div>
                     
         </div>         
      </div>
   </div>
</div>