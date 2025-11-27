<?php  
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$clients = Client::client_full(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$allOrgs = Admin::organisation_data_mini([], false, $DBConn);
$countries = Data::countries([], false, $DBConn);
$industrySectors = Data::tija_sectors([], false, $DBConn);
// var_dump($industrySectors);
$industries = Data::tija_industry([], false, $DBConn);
$clientContacts = Client::client_contacts(array('Suspended'=> 'N'), false, $DBConn);
$employeeCategorize = Employee::categorise_employee($allEmployees);
$clientContactTypes = Client::contact_types(['Suspended'=>'N'], false, $DBConn);

// var_dump($clientContactTypes);
?>
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Sales Dashboard </h1>
    <div class="ms-md-1 ms-0">
        <!-- <button type="button" class="btn btn-primary-light shadow btn-sm px-4 addNewSale" data-bs-toggle="modal" data-bs-target="#manageSale">
            <i class="ri-add-line"></i>
            Add sale
        </button> -->
        <?= ($isValidAdmin ||  $isAdmin) ? '<button type="button" class="btn btn-primary-light shadow btn-sm px-4 bulkUpload" data-bs-toggle="modal" data-bs-target="#bulkUploadModal"><i class="ri-upload-line"></i> Bulk Upload</button>' : ""; ?>
    </div>
</div>
<?php
echo Utility::form_modal_header("manageSale", "sales/manage_sale.php", "Manage Sale", array('modal-md', 'modal-dialog-centered'), $base); 
include "includes/scripts/sales/modals/manage_sale.php";
echo Utility::form_modal_footer('Save Sale', 'saveSale',  ' btn btn-success btn-sm', true);

echo Utility::form_modal_header("bulkUploadModal", "sales/manage_sale_upload.php", "Manage Sales Upload", array('modal-md', 'modal-dialog-centered'), $base); 
    include "includes/scripts/sales/modals/manage_sale_upload.php";
echo Utility::form_modal_footer('Save Sale', 'saveSale',  ' btn btn-success btn-sm', true);

include "includes/scripts/check_org_entity.php";

$getString .= "&orgDataID={$orgDataID}&entityID={$entityID}";
$salesCases = Sales::sales_case_mid(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'Suspended'=>'N'), false, $DBConn);
// var_dump($salesCases);
// //var_dump($salesCases);
$estimateThisMonth = 0;
$saleValue = 0;
if($salesCases) {
    foreach($salesCases as $case) {
        if($case->closeStatus == 'won') {
            $saleValue += $case->salesCaseEstimate;
        }
        $estimateThisMonth += $case->salesCaseEstimate;
       
    }
}?>
<div class="container-fluid ">
   <div class="card card-body col-md-12 my-4">
      <div class="card  bg-light alert  alert-dismissible fade show border-solid" role="alert">
         <div class="row">
            <div  class="col-md">
               <h4 class="mb-0 t300 font-22">Sales This Month </h4>
               <div class="col-md border-end">							
                  <div class="font-22">
                  <span class="font-14">KES</span>
                     <?php echo  $saleValue ? number_format($saleValue, 2, '.', ' ')  : 0 ?>
                     <!-- <span class="font-14"> Sales</span>  -->                     							
                  </div>                  
               </div>
            </div>

            <div class="col-md">
               <h4 class="mb-0 t300 font-22">Estimated This Month </h4>
               <div class="col-md border-end">
                  <div class="font-22">
                     <span class="font-14">KES</span>
                     <?php echo (isset($estimateThisMonth) && $estimateThisMonth !== '') ? number_format($estimateThisMonth, 2, '.', ' ') :'0.00'; ?>									
                  </div>
               </div>
            </div>

            <div class="col-md">
               <h4 class="mb-0 t300 font-22">Cases In Need of Attention</h4>
               <div class="col-md border-end">
                  <div class="font-22 clientsVal "><?php echo (isset($clientsWithProjects) && !empty($clientsWithProjects)) ? count($clientsWithProjects) : 0; ?> Cases</div>
               </div>
            </div>

            <div class="col-md">
               <h4 class="mb-0 t300 font-22">Hit-rate This month</h4>
               <div class="col-md border-end">
                  <div class="font-22 clientsVal "><?php echo (isset($clientsWithProjects) && !empty($clientsWithProjects)) ? count($clientsWithProjects) : 0; ?> %</div>
               </div>
            </div>
         </div>

         <button type="button"  class="btn-close nobg" >
            <a tabindex="0"  role="button" data-bs-toggle="popover" data-trigger="focus" title="Organize your work with projects" data-bg-content="Before you start tracking your work, create projects, assign clients, and add tasks. You can choose from advanced billing rates, set up approval workflow, add custom budgets, and set task estimates. Assign tasks to your team and monitor allocated time."><i class="icon-cog"></i></a>
         </button>
      </div>
   </div>				
</div>

<?php 

// var_dump($userDetails);
// array of different pages for different stages businessDevelopment, opportunities, sales, etc
$salesLinkArray = array(
    'business_development' => array(
        'name' => 'Business Development',
        'link' => "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=business_development"
    ),
    'opportunities' => array(
        'name' => 'Opportunities',
        'link' => "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=opportunities"
    ),
    'won' => array(
        'name' => 'Sales',
        'link' => "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=won"
    ),
    // 'closed_won' => array(
    //     'name' => 'Closed Won',
    //     'link' => "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=closed_won"
    // ),
    'lost' => array(
        'name' => 'Closed Lost',
        'link' => "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=lost"
    ),
);
$state = isset($_GET['state']) ? Utility::clean_string($_GET['state']) : 'opportunities';
if (!array_key_exists($state, $salesLinkArray)) {
    $state = 'opportunities'; // default state if not found
}
// add state to the $getString
// remove any existing state parameter if it exists
$getString = str_replace("&state={$state}", "", $getString); 
$allClientContacts = Client::client_contact_full(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'Suspended'=>'N'), false, $DBConn);
// var_dump($allClientContacts[1]);
$getString .= "&state={$state}";
?>
<script>
      let clientContacts = <?= json_encode($allClientContacts) ?>;
      let clientContactTypes = <?= json_encode($clientContactTypes) ?>;
      console.log(clientContacts);
</script>

<div class="container-fluid">
   <div class="row">
      <div class="col-md-12">
         <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between ">

                    <ul class="nav nav-tabs nav-tabs-custom mb-3" id="salesTab" role="tablist">
                         <?php 
                         foreach ($salesLinkArray as $key => $value) { ?>
                             <li class="nav-item" role="presentation">
                                 <a class="nav-link <?php echo ($state == $key) ? 'active' : ''; ?>" href="<?php echo $value['link']; ?>" role="tab"><?php echo $value['name']; ?></a>
                             </li>
                             <?php 
                         } ?>
                    </ul>
                    <div class=" text-end ">
                        <?php 
                        $saleFilter = isset($_GET['saleFilter']) ? Utility::clean_string($_GET['saleFilter']) : 'mySales';
                        $getString = str_replace("&saleFilter={$saleFilter}", "", $getString);
                        if($saleFilter == 'mySales') { 
                            $filterDisplayArray = array('index'=>'allSales', 'name' =>' All Sales');
                        } else {
                            $filterDisplayArray = array('index'=>'mySales', 'name' =>' My Sales');
                        }?>
                        <input type="hidden" id="filter" value="<?= $saleFilter ?>">
                        <a href="<?= "{$base}html/{$getString}&saleFilter={$filterDisplayArray['index']}" ?>" id="toggleFilter" class="btn btn-primary"><?= $filterDisplayArray['name'] ?> </a>
                       
                    </div>
                </div>
                <?php
                $getString .= "&saleFilter={$saleFilter}"; ?>

                <div class="tab-content">
                    <div class="tab-pane fade show active border-0 shadow-lg bg-light" id="salesTabContent" role="tabpanel">
                        <?php
                        if (array_key_exists($state, $salesLinkArray)) {
                            if (isset($salesLinkArray[$state]['name']) && !empty($salesLinkArray[$state]['name'])) {
                                $titlePage = $salesLinkArray[$state]['name'];
                             } else {
                                $titlePage = "Sales";
                             }
                             if($saleFilter == 'mySales') {
                                $titlePage .= " - My Sales";
                                $salesCaseFilter = array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'saleStage'=>$state, 'salesPersonID'=>$employeeID, 'Suspended'=>'N');
                                $salesCases = Sales::sales_case_mid($salesCaseFilter, false, $DBConn);
                             } else {
                                $titlePage .= " - All Sales";
                                $salesCases= Sales::sales_case_mid(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'saleStage'=>$state, 'Suspended'=>'N'), false, $DBConn); 
                             }
                            

                            include "includes/scripts/sales/{$state}.php";
                        }?>
                    </div>
                </div>
            </div>
         </div>
      </div>
   </div>
</div>


<script>
   document.addEventListener('DOMContentLoaded', function() {

    let clientContacts = <?= json_encode($allClientContacts) ?>;
    let clientContactTypes = <?= json_encode($clientContactTypes) ?>;
    

      // Function to reset the sales case form
      function resetSalesCaseForm() {
           const form = document.querySelector('.salesCaseForm');
         console.log(form);
           if (form) {
               // form.reset();
               const selects = form.querySelectorAll('select');
               selects.forEach(select => {
                   select.value = '';
               });

           form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="url"], textarea, select').forEach(element => {
               element.value = '';
           });
           }
       }

       // Clear form fields when "Add Sale" button is clicked
       let newSaleAdd =  document.querySelector('.addNewSale')
       if( newSaleAdd) {
           newSaleAdd.addEventListener('click', resetSalesCaseForm);
           document.querySelectorAll('.editSalesButton').forEach(button => {
               button.addEventListener('click', function() {
                console.log('Edit button clicked');
                   const form = document.querySelector('.salesCaseForm');
                   if (!form) return;
    
                   console.log(form);
    
                   // Get all data attributes from the button
                   const data = this.dataset;
                   console.log(data);
    
                   // Map form fields to their corresponding data attributes
                   const fieldMappings = {
                      'salesCaseID': 'salescaseid',
                       'salesCaseName': 'salescasename',
                       'clientID': 'clientid',
                       'contactPerson': 'contactperson',
                       'businessUnitID': 'businessunitid',
                       'saleStatusLevelID': 'saleStatusLevelID',
                       'salesCaseEstimate': 'salescaseestimate',
                       'probability': 'probability',
                       'expectedCloseDate': 'expectedclosedate',
                       'leadSourceID': 'leadsourceid',
                       'saleStatusLevelID': 'salestatuslevelid',
                   };
    
                   console.log(fieldMappings);
    
                   // Fill regular form inputs
                   for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                       const input = form.querySelector(`[name="${fieldName}"]`);
                      //  console.log(input);
                       if (input) {
                           input.value = data[dataAttribute] || '';
                       }
                   }
                   // Fill select inputs
                   const selects = ['clientID', 'businessUnitID', 'saleStatusLevelID', 'leadSourceID'];
                   selects.forEach(selectName => {
                       const select = form.querySelector(`[name="${selectName}"]`);
                       if (select) {
                           select.value = data[fieldMappings[selectName]] || '';
                       }
                   });
               });
           });
       }
      

   });
   document.addEventListener('DOMContentLoaded', function() {
      const base = '<?php echo $base; ?>';
       document.querySelectorAll('.deleteSalesCaseButton').forEach(button => {
           button.addEventListener('click', function() {
               const salesCaseID = this.getAttribute('data-salescaseid');
               const modal = document.createElement('div');
               modal.innerHTML = `
                   <div class="modal fade" id="deleteSalesCaseModal" tabindex="-1" aria-labelledby="deleteSalesCaseModalLabel" aria-hidden="true">
                       <div class="modal-dialog">
                           <div class="modal-content">
                               <div class="modal-header">
                                   <h5 class="modal-title" id="deleteSalesCaseModalLabel">Delete Sales Case</h5>
                                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                               </div>
                               <div class="modal-body">
                                   Are you sure you want to delete this sales case?
                                   <form action="${base}php/scripts/sales/manage_sale.php" method="post">
                                       <input type="hidden" name="deleteSalesCase" value="1">
                                       <input type="hidden" name="salesCaseID" value="${salesCaseID}">
                                       <button type="submit" class="btn btn-danger">Delete</button>
                                   </form>
                               </div>
                           </div>
                       </div>
                   </div>
               `;
               document.body.appendChild(modal);
               const modalInstance = new bootstrap.Modal(modal.querySelector('.modal'), {
                   keyboard: false
               });
               modalInstance.show();
           });
       });
   });
</script>
            
</div>

