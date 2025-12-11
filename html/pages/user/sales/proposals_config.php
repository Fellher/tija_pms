<?php
if(!$isAdmin) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
$entityFilter  = array('entityID'=>$_SESSION['entityID'], 'orgDataID'=>$_SESSION['orgDataID']);
$proposalStatuses = Sales::proposal_statuses($entityFilter, false, $DBConn);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$entityID= $_SESSION['entityID'] ? $_SESSION['entityID'] :null;
$orgDataID = $_SESSION['orgDataID'] ? $_SESSION['orgDataID']  : null;

$checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkListItem'], false, $DBConn);
$checklistItems = Proposal::proposal_checklist_items([], false, $DBConn);
$checklistItemCategories = Proposal::proposal_checklist_items_categories([], false, $DBConn);?>
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
   <h1 class="page-title fw-medium fs-24 mb-0"> Manage Proposal Configuration </h1>

</div>

<div class="container-fluid">
   <div class="card card-body col-md-12 my-4 shadow-lg">
      <?php
      if($isAdmin || $isValidAdmin) {?>
         <div class="row">
            <?php
            $proposalChecklistPages = array(
               (object)[
                  "title" => "checklist Status",
                  "link" => "checklist_status.php",
                  "id" => "checklist_status",
                  "adminlevel" => 4
               ],
               (object)[
                  "title" => "checklist Items",
                  "link" => "checklist_items.php",
                  "id" => "checklist_items",
                  "adminlevel" => 4
               ],
               (object)[
                  "title" => "checklist Item Categories",
                  "link" => "checklist_item_categories.php",
                  "id" => "checklist_item_categories",
                  "adminlevel" => 4
               ],
            );
            // //var_dump($getString);
            $page = (isset($_GET['page']) && !empty($_GET['page'])) ? Utility::clean_string($_GET['page']) : 'checklist_status';
            $getString = str_replace("&page={$page}", "", $getString);?>

            <div class="d-flex align-items-center justify-content-between mb-3 " >
               <h4 class="mb-0 t300 font-22">Proposal Checklist Settings</h4>
               <div class=" border-end">

               </div>
            </div>
            <div class="col-12 bg-light-blue py-2 text-end ">
               <?php foreach($proposalChecklistPages as $pageItem): ?>
                  <a href="<?= "{$base}html/{$getString}&page={$pageItem->id}" ?>" class="btn <?php echo $page === $pageItem->id ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0">
                  <?php echo $pageItem->title ?>
                  </a>
               <?php endforeach; ?>
            </div>
            <?php
            $validPages= array(
               'checklist_status',
               'checklist_items',
               'checklist_item_categories'
            );

            if(in_array($page, $validPages)) {
               $getString = str_replace("&page={$page}", "", $getString);
               include_once("html/includes/scripts/sales/proposal_checklist/{$page}.php");
               $getString.= "&page={$page}";
            } else {
               Alert::info("Invalid page", true, array('fst-italic', 'text-center', 'font-18'));
            }
            ?>
         </div>
         <?php
      }?>
   </div>
</div>