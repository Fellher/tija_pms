<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidUser) {
	var_dump($_POST);

   $projectFeeExpenseID = (isset($_POST['projectFeeExpenseID']) && !empty($_POST['projectFeeExpenseID'])) ? Utility::clean_string($_POST['projectFeeExpenseID']) : false;
   $projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : false;
   // $projectFeeExpenseType = (isset($_POST['projectFeeExpenseType']) && !empty($_POST['projectFeeExpenseType'])) ? Utility::clean_string($_POST['projectFeeExpenseType']) : false;
   $feeCostName = (isset($_POST['feeCostName']) && !empty($_POST['feeCostName'])) ? Utility::clean_string($_POST['feeCostName']) : false;
   $feeCostDescription = (isset($_POST['feeCostDescription']) && !empty($_POST['feeCostDescription'])) ? Utility::clean_string($_POST['feeCostDescription']) : false;
   $productQuantity = (isset($_POST['productQuantity']) && !empty($_POST['productQuantity'])) ? Utility::clean_string($_POST['productQuantity']) : false;
   $productUnit = (isset($_POST['productUnit']) && !empty($_POST['productUnit'])) ? Utility::clean_string($_POST['productUnit']) : false;
   $unitPrice = (isset($_POST['unitPrice']) && !empty($_POST['unitPrice'])) ? Utility::clean_string($_POST['unitPrice']) : false;
   $unitCost = (isset($_POST['unitCost']) && !empty($_POST['unitCost'])) ? Utility::clean_string($_POST['unitCost']) : false;
   $vat = (isset($_POST['vat']) && !empty($_POST['vat'])) ? Utility::clean_string($_POST['vat']) : false;
   $suspended = (isset($_POST['suspended']) && !empty($_POST['suspended'])) ? Utility::clean_string($_POST['suspended']) : false;
   $productTypeID = (isset($_POST['productTypeID']) && !empty($_POST['productTypeID'])) ? Utility::clean_string($_POST['productTypeID']) : false;

   $dateOfCost = (isset($_POST['dateOfCost']) && !empty($_POST['dateOfCost'])) ? Utility::clean_string($_POST['dateOfCost']) : false;
   $billable = (isset($_POST['billable']) && !empty($_POST['billable'])) ? Utility::clean_string($_POST['billable']) : false;
   $billingDate = (isset($_POST['billingDate']) && !empty($_POST['billingDate'])) ? Utility::clean_string($_POST['billingDate']) : false;
   $billingFrequency = (isset($_POST['billingFrequency']) && !empty($_POST['billingFrequency'])) ? Utility::clean_string($_POST['billingFrequency']) : false;
   $billingFrequencyUnit = (isset($_POST['billingFrequencyUnit']) && !empty($_POST['billingFrequencyUnit'])) ? Utility::clean_string($_POST['billingFrequencyUnit']) : false;
   $billingStartDate = (isset($_POST['billingStartDate']) && !empty($_POST['billingStartDate'])) ? Utility::clean_string($_POST['billingStartDate']) : false;
   $recurrenceEnd = (isset($_POST['recurrenceEnd']) && !empty($_POST['recurrenceEnd'])) ? Utility::clean_string($_POST['recurrenceEnd']) : false;
   $recurrencyTimes = (isset($_POST['recurrencyTimes']) && !empty($_POST['recurrencyTimes'])) ? Utility::clean_string($_POST['recurrencyTimes']) : false;
   $billingEndDate = (isset($_POST['billingEndDate']) && !empty($_POST['billingEndDate'])) ? Utility::clean_string($_POST['billingEndDate']) : false;
   $billingPhase = (isset($_POST['billingPhase']) && !empty($_POST['billingPhase'])) ? Utility::clean_string($_POST['billingPhase']) : false;
   $billingRateTypeID = (isset($_POST['billingRateTypeID']) && !empty($_POST['billingRateTypeID'])) ? Utility::clean_string($_POST['billingRateTypeID']) : false;
   $billingMilestone = (isset($_POST['billingMilestone']) && !empty($_POST['billingMilestone'])) ? Utility::clean_string($_POST['billingMilestone']) : "N";



   if(!$projectFeeExpenseID){

      $projectID ? $details['projectID'] = $projectID : $errors[] = 'Project ID is required';
      $projectDetails = Projects::projects_full(array('projectID' => $projectID), true, $DBConn);
      var_dump($projectDetails);
      // $projectFeeExpenseType ? $details['projectFeeExpenseType'] = $projectFeeExpenseType : $errors[] = 'Project Fee Expense Type is required';
      $feeCostName ? $details['feeCostName'] = $feeCostName : $errors[] = 'Fee Cost Name is required';
      $feeCostDescription ? $details['feeCostDescription'] = $feeCostDescription : $errors[] = 'Fee Cost Description is required';
      $productQuantity ? $details['productQuantity'] = $productQuantity : $errors[] = 'Product Quantity is required';
      $productUnit ? $details['productUnit'] = $productUnit : $errors[] = 'Product Unit is required';
      $unitPrice ? $details['unitPrice'] = $unitPrice : $errors[] = 'Unit Price is required';
      $unitCost ? $details['unitCost'] = $unitCost :"";
      $vat ? $details['vat'] = $vat : "";
      $suspended ? $details['suspended'] = $suspended : $details['suspended'] = 'N';
      $productTypeID ? $details['productTypeID'] = $productTypeID : $errors[] = 'Product Type ID is required';
      if($unitCost){
         $details['unitCost'] = $unitCost;
         $dateOfCost ? $details['dateOfCost'] = $dateOfCost : "";
      } else {
         $details['unitCost'] = 0;
      }

      $billable ? $details['billable'] = $billable : $errors[] = 'Billable is required';

      if($billable == 'immediately') {
         $billingDate ? $details['billingDate'] = $billingDate : $details['billingDate'] = date('Y-m-d');
      } elseif($billable == 'recurring') {
         $billingFrequency ? $details['billingFrequency'] = $billingFrequency : $errors[] = 'Billing Frequency is required';
         $billingFrequencyUnit ? $details['billingFrequencyUnit'] = $billingFrequencyUnit : $errors[] = 'Billing Frequency Unit is required';
         $billingStartDate ? $details['billingStartDate'] = $billingStartDate : $errors[] = 'Billing Start Date is required';
         $recurrenceEnd ? $details['recurrenceEnd'] = $recurrenceEnd : $errors[] = 'Recurrence End is required';
         if($recurrenceEnd == 'number_of_times') {
            $recurrencyTimes ? $details['recurrencyTimes'] = $recurrencyTimes : $errors[] = 'Recurrence Times is required';
         } elseif($recurrenceEnd == 'on_date') {
            $billingEndDate ? $details['billingEndDate'] = $billingEndDate : $errors[] = 'Billing End Date is required';
         }
      }elseif($billable ==='on_phase_completion'){
         $billingPhase ? $details['billingPhase'] = $billingPhase : $errors[] = 'Billing Phase is required';
      } elseif($billable ==='on_milestone_completion'){
         $billingMilestone ? $details['billingMilestone'] = $billingMilestone : $errors[] = 'Billing Milestone is required';
      } elseif($billable == 'on_date') {
         $billingDate ? $details['billingDate'] = $billingDate : $errors[] = 'Billing Date is required';
         if($billingRateTypeID) {
            $details['billingRateTypeID'] = $billingRateTypeID;
         } else {
            $errors[] = 'Billing Rate Type ID is required';
         }

      } elseif($billable == 'on_project_completion') {

         $details['billingDate'] = $projectDetails->projectClose;
      } elseif($billable == 'on_milestone_completion') {
         $billingMilestone ? $details['billingMilestone'] = $billingMilestone : $errors[] = 'Billing Milestone is required';
      } else {
         $errors[] = 'Invalid billing type';
      }

      echo "<h5>Details</h5>";
      var_dump($details);

      if(!$errors){
         if($details){
            $details['LastUpdateByID']= $userDetails->ID;
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_project_fee_expenses', $details)){
               $errors[] = 'Error inserting project fee expense';
            } else {
               $projectFeeExpenseID = $DBConn->lastInsertId();
               $success = 'Project Fee Expense added successfully';
            }
         }
      }




   } else {
      $projectFeeExpenseDetailed = Projects::project_fee_expense(array('projectFeeExpenseID' => $projectFeeExpenseID), true, $DBConn);
      var_dump($projectFeeExpenseDetailed);
   }
} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>