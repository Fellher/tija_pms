<?php 
if(!$isAdmin && !$isValidAdmin){ 
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
$orgDataID = (isset($_GET['orgDataID']) && !empty($_GET['orgDataID'])) ? Utility::clean_string($_GET['orgDataID']) : 0;
$entityID = (isset($_GET['entityID']) && !empty($_GET['entityID'])) ? Utility::clean_string($_GET['entityID']) : 0;
$organisations = Admin::organisation_data_mini([], false, $DBConn);

$linkList = array(
    (object)[
        "title" => "Sales Statuses",
        "link" => "sales_statuses.php",
        "id" => "sales_statuses",
        "adminlevel" => 3
    ],
    (object)[
        "title" => "Lead Sources",
        "link" => "sales_sources.php",
        "id" => "sales_sources",
        "adminlevel" => 3
    ],
    (object)[
        "title" => "Proposal Statuses",
        "link" => "sales_proposal_statuses.php",
         "id" => "sales_proposal_statuses",
         "adminlevel" => 3
    ],
    (object)[
        "title" => "Proposal Templates",
        "link" => "sales_proposal_templates.php",
        "id" => "sales_proposal_templates",
        "adminlevel" => 3
    ],
);

// //var_dump($getString);
$page = (isset($_GET['page']) && !empty($_GET['page'])) ? Utility::clean_string($_GET['page']) : 'sales_statuses';
// //var_dump($page);
// //var_dump($linkList);
// //var_dump($userID);
// //var_dump($getString);
//
// //var_dump($getString);
// //var_dump($linkList);
//var_dump($getString);

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom" >
    <h1 class="page-title fw-medium fs-24 mb-0">Sales Config Dashboard </h1>
    
</div>
<div class="container-fluid">
   <div class="card custom-card">
      <div class="card-header justify-content-between">
         <h3 class="card-title">
             <?php
             foreach ($linkList as $link) {
                 if ($link->id == $page) {
                     echo $link->title;
                     break;
                 }
             }
             ?>
         </h3>

         <div class="d-flex ">
         <?php        
         foreach ($linkList as $link):
            //   //var_dump($link);
            $active= $page == $link->id ? ' active ' : ''; ?>
            <a href="<?= "{$base}html/{$getString}&page={$link->id}" ?>" class="btn btn-primary-light shadow btn-sm px-4 me-3 <?= $active ?>">
               <?php echo $link->title; ?>
            </a>
         <?php endforeach; ?>
         </div>
      </div>
   </div>

</div>


<?php
$validPages = array('sales_statuses', 'sales_sources', 'sales_proposal_statuses', 'sales_proposal_templates');
if(in_array($page, $validPages)) {
   include_once("includes/scripts/sales/{$page}.php");
}

 $getString = str_replace("&uid={$userID}", "", $getString);
 $getString.= "&uid={$userID}";
 $getString = str_replace("&page={$page}", "", $getString);
 $getString .= "&page={$page}";
 ?>