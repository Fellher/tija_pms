<?php 

// var_dump($isValidAdmin);
if (!$isValidAdmin) {
   Alert::info("You need to be logged in as a valid administrator to access this page", true, 
       array('fst-italic', 'text-center', 'font-18'));
   exit;
}
$financialStatementID = (isset($_GET['finstmtID']) && !empty($_GET['finstmtID'])) ? Utility::clean_string($_GET['finstmtID']) : '';
$financialStatementDataID = (isset($_GET['fsdID']) && !empty($_GET['fsdID'])) ? Utility::clean_string($_GET['fsdID']) : '';
$organisations= Admin::org_data(array(), false, $DBConn);
if(!$organisations){
   Alert::info("No Organisations set up for this tax computation instance", true, 
         array('fst-italic', 'text-center', 'font-18'));
         exit;
} else {
   $entityID = (isset($_GET['eid']) && !empty($_GET['eid'])) ? Utility::clean_string($_GET['eid']): "";
   $entityDetails = Data::entities_full(array("entityID"=>$entityID), true, $DBConn);
   // var_dump($entityDetails);
   var_dump($userID);
   if($entityDetails) {
      $getString .= "&eid={$entityID}";
      $entityName = $entityDetails->entityName;
      $entityDescription = $entityDetails->entityDescription;
      $entityTypeID = $entityDetails->entityTypeID;
      $entityParentID = $entityDetails->entityParentID;
      $orgDataID = $entityDetails->orgDataID;
      $industrySectorID = $entityDetails->industrySectorID;
      $registrationNumber = $entityDetails->registrationNumber;
      $entityPIN = $entityDetails->entityPIN;
      $entityCity = $entityDetails->entityCity;
      $entityCountry = $entityDetails->entityCountry;
      $entityPhoneNumber = $entityDetails->entityPhoneNumber;
      $entityEmail = $entityDetails->entityEmail;      
      if($financialStatementID) {
         $financialStatementDetails = Tax::financial_statements(array("financialStatementID"=>$financialStatementID), true, $DBConn);
         $getString .= "&finstmtID={$financialStatementID}";?>
         <div class="container-fluid">
            <div class="row pt-2 bg-light px-lg-3 mb-4">
               <h2 class="mb-2 t300 fs-3 border-bottom border-primary"> <?php echo $financialStatementDetails->financialStatementTypeName; ?> <span class="float-end t600 fs-4">(<?php echo $entityDetails->entityName ?>)</span></h2>   
               <h4 class="mb-2 t300 fs-6">Fiscal Year: <?php echo $financialStatementDetails->fiscalYear; ?> <span class=" t600 fs-6">(<?php echo $financialStatementDetails->fiscalPeriod ?>)</span></h4>        
            </div>
            <div class="container">
               <div class="h2">
                  Manage Statement Account
                  <span class="float-end">
                     <a href="<?php echo "{$base}html/?s={$s}&ss={$ss}&p=data_upload&eid={$entityID}" ?>" class="btn btn-primary btn-sm"> <i class="uil-refresh"></i> Done</a>
               </div>
               <?php 
               if($financialStatementDataID) {?> 
                  <div class="card card-body">						
                     <form class="form" method="post" action="<?php echo $base; ?>php/scripts/tax/admin/manage_financial_statement_account.php">							
                        <div class="row">
                           <div class="form-group d-none">
                              <label for="instanceID"> Instanceid</label>
                              <input type="text" name="instanceID" id="instanceID" class="form-control" Value="<?php echo $instanceID?>">
                              <label for=""> financialStatementID</label>
                              <input type="text" name="financialStatementID" id="financialStatementID" class="form-control" Value="<?php echo $financialStatementID; ?>">
                              <label for="financialStatementDataID"> Financial Statement Account ID</label>
                              <input type="text" name="financialStatementDataID" id="financialStatementDataID" class="form-control" Value="<?php echo $financialStatementDataID; ?>">
                           </div>
                           <?php 
                           if($financialStatementDetails->financialStatementTypeID == 5) {									
                              // Modify statement of investment allowance 
                              $investmentAllowanceAccount = Tax::statement_of_investment_allowance_accounts(array("investmentAllowanceAccountID"=>$financialStatementDataID, "Suspended"=>'N'), true, $DBConn);
                              var_dump($investmentAllowanceAccount);?>
   
                              <h4 class="border-bottom" > <?php 	echo $investmentAllowanceAccount->accountName; ?> Account</h4>	
                              <div class="form-group col-md-8">
                                 <label for="AccountName"> Account Name</label>
                                 <input type="text" name="accountName" id="accountName" class="form-control form-control-sm" value="<?php echo $investmentAllowanceAccount->accountName; ?>" placeholder="Account Name"/>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="AccountCode"> Account Code</label>
                                 <input type="text" name="accountCode" id="accountCode" class="form-control form-control-sm" value="<?php echo $investmentAllowanceAccount->accountCode; ?>" placeholder="Account Code"/>
                              </div>                          
                              <?php            
                           } elseif ($financialStatementDetails->financialStatementTypeID == 7) {	
                              // modify trial balance data details                        
                              $financialStatementData = Tax::financial_statementData(array("financialStatementDataID"=>$financialStatementDataID, "Suspended"=>'N'), true, $DBConn);
                              $accountValue = ($financialStatementData->accountType === 'credit') ? $financialStatementData->creditValue : $financialStatementData->debitValue;
                              $accountType = $financialStatementData->accountType;
                              $accountName = $financialStatementData->accountName;
                              $accountCode = $financialStatementData->accountCode;
                              $accountCategory = $financialStatementData->accountCategory;
                              $accountDescription = $financialStatementData->accountDescription;
                              $accountNode = $financialStatementData->accountNode;
                              $accountType = $financialStatementData->accountType;
                              $financialStatementDataID = $financialStatementData->financialStatementDataID; ?>		
                              <h4 class="border-bottom" > <?php 	echo $accountName; ?></h4>	
                              <div class="form-group col-md-8">
                                 <label for="AccountName"> Account Name</label>
                                 <input type="text" name="accountName" id="accountName" class="form-control form-control-sm" value="<?php echo $accountName; ?>" placeholder="Account Name"/>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="AccountCode"> Account Code</label>
                                 <input type="text" name="accountCode" id="accountCode" class="form-control form-control-sm" value="<?php echo $accountCode; ?>" placeholder="Account Code"/>
                              </div>
                              <div class="form-group col-md">
                                 <label for="accountCategory">Account Category</label>
                                 <input type="text" name="accountCategory" id="accountCategory" class="form-control form-control-sm" value="<?php echo $accountCategory; ?>" placeholder="Account Description"/>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="accountType">Account Type</label>
                                 <select name="accountType" id="accountType" class="form-control form-control-sm">
                                    <option value="">Select Account Type</option>
                                    <option value="debit" <?php echo ($accountType === 'debit') ? 'selected' : ''; ?>>Debit</option>
                                    <option value="credit" <?php echo ($accountType === 'credit') ? 'selected' : ''; ?>>Credit</option>
                                 </select>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="accountValue">Account Value</label>
                                 <input type="text" name="accountValue" id="accountValue" class="form-control form-control-sm" value="<?php echo $accountValue; ?>" placeholder="Debit Value"/>
                              </div>
                              <div class="form-group">
                                 <label for="accountDescription">Account Description</label>
                                 <textarea name="accountDescription" id="accountDescription" class="form-control form-control-sm basic" placeholder="Account Description"><?php echo $accountDescription; ?></textarea>
                              </div>
                              <?php
                           }?>
                           <div class="col-12">
                              <div class="row">
                                 <div class="col-md-6">
                                    <input type="checkbox" class="btn-check" id="btn-check" autocomplete="off" name="delete" value="delete">
                                    <label class="btn btn-danger" for="btn-check">Delete Account</label>
                                 </div>
                                 <div class="col-md">
                                    <button type="submit" name="update_account" class="btn btn-primary float-end w-md-4">Update Account</button>
                                 </div>
                              </div>									
                           </div>
                        </div>
                     </form>
                  </div>   
                  <?php
               } else {?>
                  <h3>Statement Data
                     <span class="float-end">                      
                        <?php if($financialStatementDetails->financialStatementTypeID === 5) {?>
                           <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=map_investment&invID={$financialStatementID}&eid={$entityID}" ?>" data-bs-toggle="modal" class="btn btn-primary btn-sm"> <i class="fa-solid fa-add"></i> Map Accounts</a>
                           <?php
                        } elseif ($financialStatementDetails->financialStatementTypeID == 7) {	
                           ?>
                             <a href="#<?php echo "manage_account_{$financialStatementDetails->statementTypeNode}" ?>" data-bs-toggle="modal" class="btn btn-primary btn-sm"> <i class="fa-solid fa-add"></i> Add Statement Account</a>
                           <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=statement_mapping&finstmtID={$financialStatementID}&eid={$entityID}" ?>" data-bs-toggle="modal" class="btn btn-primary btn-sm"> <i class="fa-solid fa-add"></i> Map Accounts</a>
                        <?php
                        }?>
                     </span>
                  </h3>                  
                  <?php
                  echo Utility::form_modal_header("manage_account_{$financialStatementDetails->statementTypeNode}", "tax/admin/manage_financial_statement_account.php", "Manage Statement Accounts", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
                  include "includes/scripts/tax/modals/manage_financial_statement_accounts.php";
                  echo Utility::form_modal_footer( "Add Statement Account","add_account", 'btn  btn-secondary btn-lg', false);
                  $node .= "manage_account_{$financialStatementDetails->statementTypeNode}";
                  if($financialStatementDetails->financialStatementTypeID === 5) {
                     // Data from Statement of Investment Allowance                     
                     include "includes/scripts/tax/scripts/statement_of_investment_data.php";
                  } elseif ($financialStatementDetails->financialStatementTypeID == 7) {                     
                     // Data from Trial Balance 
                     include "includes/scripts/tax/scripts/trial_balance_data.php";
                  }
               }?>
            </div>
         </div>
         <?php
         // var_dump($financialStatementDetails);
      } else {?>
         <div class="card custom-card">
            <div class="card-header justify-content-between">
               <div class="card-title"> <?= $entityName ?> </div>
               <div>
               <?= $entityDetails->orgName ?>
                </div>
            </div>
        
             <h3 class="text-center">Select Statement To upload</h3>
            <div class="row">
               <?php
               $statementTypes = Tax::financial_statements_types(array("Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);
               if($statementTypes){
                  foreach ($statementTypes as $key => $stmtType) {?>
                     <div class="col-md-6">
                        <div class="card card-body mb-3 p-4">
                           <h4 class="text-center"><?php echo $stmtType->financialStatementTypeName ?></h4>
                           <?php echo $stmtType->financialStatementTypeDescription;
                           // var_dump($stmtType);
                           $financialStatements = Tax::financial_statements(array("orgDataID"=>$entityDetails->orgDataID, "financialStatementTypeID"=>$stmtType->financialStatementTypeID, 'entityID'=>$entityID,  "Suspended"=>'N'), false, $DBConn);
                           // var_dump($financialStatements);
                           if($financialStatements) {?>

                              <div class="list-group list-group-flush">
                                 <?php
                                 foreach ($financialStatements as $key => $financialStatement) {
                                    // var_dump($financialStatement);
                                    ?>
                                    <a href="<?php echo "{$getString}&finstmtID={$financialStatement->financialStatementID}" ?>" class="list-group-item list-group-item-action">
                                       <?php
                                       echo $key+1 . ". ";
                                          echo "{$financialStatement->financialStatementTypeName} - {$financialStatement->fiscalYear}"; ?> (<?php echo $financialStatement->fiscalPeriod ?>)
                                    </a>
                                    <?php
                                 }?>
                              </div>
                              <?php										
                           }?>
                           <div class="col-12 my-3">
                              <div class="col-lg-6 mx-auto text-center ">                                 
                                 <a class="btn btn-primary btn-lg" href="#<?php echo "upload_statement_{$stmtType->statementTypeNode}" ?>" data-bs-toggle="modal"> Upload <?php echo $stmtType->financialStatementTypeName ?></a>                                                            
                              </div>	
                              <?php 
                                 echo Utility::form_modal_header("upload_statement_{$stmtType->statementTypeNode}", "tax/admin/upload_statement.php", "Manage {$stmtType->financialStatementTypeName}", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
                                    include "includes/scripts/tax/modals/upload_statement.php";
                                 echo Utility::form_modal_footer( "Upload {$stmtType->financialStatementTypeName}","upload_{$stmtType->statementTypeNode}", 'btn  btn-secondary btn-lg', false); ?>											
                           </div>
                        </div>
                     </div>							
                     <?php
                  }
                  // var_dump($statementTypes);
               } else {
                  Alert::warning("No statement types Set up for this instance", true, array('fst-italic', 'text-center', 'font-18'));
               }?>
            </div>
         </div>
         <?php
      }
   } else {   // var_dump($organisations);
      foreach ($organisations as $key => $organisation) {?>
         <div class="card custom-card">
            <div class="card-header justify-content-between">
               <div class="card-title"> <?= $organisation->orgName ?> </div>
               
                     <button type="button"class="btn btn-sm btn-primary-light shadow-sm manageEntityOrganisation" data-bs-toggle="modal" data-organisationId="<?= $organisation->orgDataID ?>"  data-bs-target="#manageEntity">
                        <i class="fas fa-plus"></i>
                        Add New Entity
                     </button>
               </div>
            </div>
            <div class="card-body">
               <div class="row">
                  <div class="col-12 ">
                     <?php 
                     $entities = Data::entities_full(['orgDataID'=> $organisation->orgDataID, 'Suspended'=> 'N'], false, $DBConn);
                     // var_dump($entities);
                     if($entities) {?>
                        <div class="list-group">
                           <?php 
                           foreach ($entities as $key => $entity) {?>
                              <div class="list-group-item list-group-item-action" aria-current="true">
                                 <div class="d-sm-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-semibold">    
                                       <?php echo $entity->entityName ?> </h6>
                                    <small>
                                       <div class="btn-list">                                          
                                          <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo $entity->entityID ?>" data-bs-placement="top" data-bs-title="Edit" class="btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm entityEdit "><i class="ti ti-pencil"></i></a>
                                          <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" class="btn  btn-icon rounded-pill  btn-danger-light btn-wave btn-sm entityDelete "><i class="ti ti-trash"></i></a>
                                          <a aria-label="anchor" href="<?= "{$base}html/{$getString}&eid={$entity->entityID}"  ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Select Entity" class="btn  btn-icon rounded-pill  btn-primary-light btn-wave btn-sm entityDelete "><i class="ti ti-eye"></i></a>
                                       </div>
                                    </small>
                                 </div>
                                 <p class="mb-1"><?php echo $entity->entityDescription; ?></p>
                              </div>
                              <?php
                           } ?>
                        </div> 
                        <?php
                     }?>
                  </div>
               </div>
            </div>
         </div>
         <?php
      }
   }
}

?>