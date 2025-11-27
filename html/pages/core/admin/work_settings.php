<!-- Page Header -->
<?php
//  var_dump($isValidAdmin);
if (!$isValidAdmin && !$isAdmin ) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
        include "includes/core/log_in_script.php";
    exit;
}?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-18 mb-0">Work Settings Dashboard</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">Setup Dashboard</li>
            </ol>
        </nav>
    </div>
</div>
<?php
$linksArray = array(
    (object)[
        "title" => "Work Categories, Types & Statuses",
        "link" => "work_categories.php",
        "id" => "work_categories",
        "adminlevel" => 3
    ],    
      (object)[
         "title" => "Work Status",
         "link" => "work_status.php",
         "id" => "work_status",
         "adminlevel" => 3
      ],
      // (object)[
      //    "title" => "Work Stages",
      //    "link" => "work_stages.php",
      //    "id" => "work_stages",
      //    "adminlevel" => 3
      // ],
      // (object)[
      //    "title" => "Overtime Types",
      //    "link" => "overtime_types.php",
      //    "id" => "overtime_types",
      //    "adminlevel" => 3
      // ],
      (object)[
         "title" => "Leave Status",
         "link" => "leave_status.php",
         "id" => "leave_status",
         "adminlevel" => 3
      ],
      (object)[
         "title" => "Activity Types",
         "link" => "activity_types.php",
         "id" => "activity_types",
         "adminlevel" => 3
      ],


);

// var_dump($getString);
$page = (isset($_GET['page']) && !empty($_GET['page'])) ? Utility::clean_string($_GET['page']) : 'work_categories';

?>

<div class="container-fluid"> 
   <div class="col-12 bg-light-blue py-2 text-end  border-primary border-bottom border-2 px-3"> 
      <?php
      foreach ($linksArray as $key => $link) { ?>
         <a href="<?php echo "{$base}html/{$getString}&page=".$link->id.'&uid='.$userID; ?>"
         class="btn  <?php echo  $page == $link->id ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0"  > 
            <?php echo $link->title; ?>
         </a>
      <?php
         # code...
      } ?>
   </div>
</div>
<?php
$getString = str_replace("&uid={$userID}", "", $getString); 
$getString.= "&uid={$userID}";
$getString = str_replace("&page={$page}", "", $getString); 
$getString .= "&page={$page}";

$vakidPages = array(
   'work_categories',
   'work_status',
   // 'work_stages',
   // 'overtime_types',
   'leave_status',
   'activity_types',
  
);

if (in_array($page, $vakidPages)) {
   include "includes/scripts/work/home/{$page}.php";
  
} else {
   Alert::info("You need to be logged in as a valid administrator to access this page", true,
    array('fst-italic', 'text-center', 'font-18'));
exit;
}

?>




