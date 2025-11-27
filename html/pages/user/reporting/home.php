<?php  
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
include "includes/scripts/utility_scripts/get_period.php";

$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeID ? $getString .= "&uid={$employeeID}" : null;
// var_dump($getString);
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$clients = Client::client_full(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$countries = Data::countries([], false, $DBConn);
$industrySectors = Data::tija_sectors([], false, $DBConn);
// var_dump($industrySectors);
$industries = Data::tija_industry([], false, $DBConn);
$clientContacts = Client::client_contacts(array('Suspended'=> 'N'), false, $DBConn);
$employeeCategorize = Employee::categorise_employee($allEmployees);
$clientContactTypes = Client::contact_types(['Suspended'=>'N'], false, $DBConn);?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Reporting Overview Dashboard </h1>
    <div class="ms-md-1 ms-0">
        <!-- <button type="button" class="btn btn-primary-light shadow btn-sm px-4 addNewSale" data-bs-toggle="modal" data-bs-target="#manageSale">
            <i class="ri-add-line"></i>
            Add sale
        </button> -->
      
    </div>
</div>
<?php 
$reportingLinkArray = array(
   (object)[
      "title"=>"Time & Expense",
      "icon"=>"ri-time-line",
      "link"=>"time_attendance.php",
      "active"=>$p=="time_attendance",
      "slug"=>"time_attendance",
      "description"=> " View and Manage Time, Attendance and Expense Reporting ",
      "adminLevel"=> 4,
      "subMenu"=>[
        (object)[
          "title"=>"Hours to payroll",
          "icon"=>"ri-users-line",
          "link"=>"time_attendance_users.php",
          "active"=>$p=="time_attendance_users",
          "slug"=>"time_attendance_users",
          "header"=> "Employee hours versus work contract for payroll"
        ], 
        (object)[
            "title"=>"Work Hour Analysis",
            "icon"=>"ri-timer-flash-line",
            "link"=>"time_attendance_work_hour_analysis.php",
            "active"=>$p=="time_attendance_work_hour_analysis",
            "Header"=> "Employee productivity grouped by projects and clients",
            "slug"=>"time_attendance_work_hour_analysis"
         ],
         (object)[
            "title"=>"Work Capacity",            
            "icon"=>"ri-timer-flash-line",
            "link"=>"time_attendance_work_capacity.php",
            "Header"=> "Employee hours versus work contract",
            "active"=>$p=="time_attendance_work_capacity",
            "slug"=>"time_attendance_work_capacity"
         ], 
         // (object) [
         //    "title"=>"Attendance & Punctuality",            
         //    "icon"=>"ri-calendar-check-line",
         //    "link"=>"time_attendance_attendance_punctuality.php",
         //    "active"=>$p=="time_attendance_attendance_punctuality",
         //    "slug"=>"time_attendance_attendance_punctuality"
         // ],
         // (object) [
         //    "title"=>"Leave Management",            
         //    "icon"=>"ri-calendar-event-line",
         //    "link"=>"time_attendance_leave_management.php",
         //    "active"=>$p=="time_attendance_leave_management",
         //    "slug"=>"time_attendance_leave_management"
         // ],
         (object) [
            "title"=>"Employee Expense Reports",            
            "icon"=>"ri-file-dollar-line",
            "link"=>"time_attendance_expense_reports.php",
            "active"=>$p=="time_attendance_expense_reports",
            "slug"=>"time_attendance_expense_reports"
         ], 
      ]      
   ],
   (object)[
      "title"=>"Projects",
      "icon"=>"ri-building-2-line",
      "link"=>"projects.php",
      "active"=>$p=="projects",
      "slug"=>"projects",
      "description"=> " View and Manage Projects Reporting ",
      "adminLevel"=> 4,
      "subMenu"=>[

         (object)[
           "title"=>"project Cases",
           "icon"=>"ri-building-2-line",
           "link"=>"projects_cases.php",
           "active"=>$p=="projects_cases",
           "slug"=>"projects_cases",
           "header"=>"Active Project analysis"
         ], 
         (object)[
             "title"=>"tasks & Activities",
             "icon"=>"ri-task-line",
             "link"=>"projects_tasks_activities.php",
             "active"=>$p=="projects_tasks_Activities",
             "slug"=>"projects_tasks_activities",
             "header"=>"Tasks & Activities for active projects"
          ],
          (object)[
             "title"=>"Financial Analysis",            
             "icon"=>"ri-trophy-line",
             "link"=>"projects_financial_analysis.php",
             "active"=>$p=="projects_financial_analysis",
             "slug"=>"projects_financial_analysis",
             "header"=>"Financial Analysis"
          ],
          (object) [
             "title"=>"Project Financials",            
             "icon"=>"ri-money-dollar-circle-line",
             "link"=>"projects_financials.php",
             "active"=>$p=="projects_financials",
             "slug"=>"projects_financials",
             "header"=>"Project Financials"
          ], 
         //  (object) [
         //     "title"=>"Project Resources(Work Hour Analysis)",            
         //     "icon"=>"ri-team-line",
         //     "link"=>"projects_resources.php",
         //     "active"=>$p=="projects_resources",
         //     "slug"=>"projects_resources"
         //  ],
         //  (object) [
         //     "title"=>"Project Invoices",            
         //     "icon"=>"ri-file-dollar-line",
         //     "link"=>"projects_invoices.php",
         //     "active"=>$p=="projects_invoices",
         //     "slug"=>"projects_invoices"
         //  ],
         //  (object) [
         //     "title"=>"Project Performance",            
         //     "icon"=>"ri-bar-chart-line",
         //     "link"=>"projects_performance.php",
         //     "active"=>$p=="projects_performance",
         //     "slug"=>"projects_performance"
         //  ],
      ]
   ],
   (object)[
      "title"=>"Sales",
      "icon"=> "ri-money-dollar-circle-line",
      "link"=>"sales.php",
      "active"=>$p=="sales",
      "slug"=>"sales",
      "description"=> " View and Manage Sales Reporting ",
      "adminLevel"=> 4,
      "subMenu"=>[
         (object)[
           "title"=>"sales Overview",
           "icon"=>"ri-money-dollar-circle-line",
           "link"=>"sales_overview.php",
           "active"=>$p=="sales_overview",
           "slug"=>"sales_overview"
         ], 
         (object)[
             "title"=>"leads & Opportunities",
             "icon"=>"ri-hand-coin-line",
             "link"=>"sales_leads_opportunities.php",
             "active"=>$p=="sales_leads_opportunities",
             "slug"=>"sales_leads_opportunities"
          ],
         //  (object)[
         //     "title"=>"Quotes & Proposals",            
         //     "icon"=>"ri-file-list-3-line",
         //     "link"=>"sales_quotes_proposals.php",
         //     "active"=>$p=="sales_quotes_proposals",
         //     "slug"=>"sales_quotes_proposals"
         //  ],
         //  (object) [
         //     "title"=>"Orders & Invoices",            
         //     "icon"=>"ri-file-dollar-line",
         //     "link"=>"sales_orders_invoices.php",
         //     "active"=>$p=="sales_orders_invoices",
         //     "slug"=>"sales_orders_invoices"
         //  ], 
         //  (object) [
         //     "title"=>"Payments & Collections",            
         //     "icon"=>"ri-money-dollar-circle-line",
         //     "link"=>"sales_payments_collections.php",
         //     "active"=>$p=="sales_payments_collections",
         //     "slug"=>"sales_payments_collections"
         //  ],
         //  (object) [
         //     "title"=>"Sales Performance",            
         //     "icon"=>"ri-bar-chart-line",
         //     "link"=>"sales_performance.php",
         //     "active"=>$p=="sales_performance",
         //     "slug"=>"sales_performance"
         //  ],
      ]
   ],
   (object)[
      "title"=>"Finance",
      "icon"=> "ri-money-dollar-circle-line",
      "link"=>"finance.php",
      "active"=>$p=="finance",
      "slug"=>"finance",
      "description"=> " View and Manage Finance Reporting ",
      "adminLevel"=> 4,
      "subMenu"=>[
         (object)[
           "title"=>"financial Overview",
           "icon"=>"ri-money-dollar-circle-line",
           "link"=>"finance_overview.php",
           "active"=>$p=="finance_overview",
           "slug"=>"finance_overview"
         ], 
         (object)[
             "title"=>"Employee Financials",
             "icon"=>"ri-file-list-3-line",
             "link"=>"employee_cost_benefit_analysis.php",
             "active"=>$p=="employee_cost_benefit_analysis",
             "slug"=>"employee_cost_benefit_analysis"
          ],
          (object)[
             "title"=>"Organisation Health",            
             "icon"=>"ri-hand-coin-line",
             "link"=>"organizational_financial_health.php",
             "active"=>$p=="organizational_financial_health",
             "slug"=>"organizational_financial_health"
          ],
          (object) [
             "title"=>"Business Unit Performance",            
             "icon"=>"ri-file-dollar-line",
             "link"=>"unit_segment_performance.php",
             "active"=>$p=="unit_segment_performance",
             "slug"=>"unit_segment_performance"
          ], 
          (object) [
             "title"=>"Cash Flow",            
             "icon"=>"ri-money-dollar-circle-line",
             "link"=>"invoicing_analysis_trends.php",
             "active"=>$p=="invoicing_analysis_trends",
             "slug"=>"invoicing_analysis_trends"
          ],
         //  (object) [
         //     "title"=>"Invoice Analysis",            
         //     "icon"=>"ri-bar-chart-line",
         //     "link"=>"invoicing_analysis_trends.php",
         //     "active"=>$p=="invoicing_analysis_trends",
         //     "slug"=>"invoicing_analysis_trends"
         //  ],
      ]
   ]);
   
   $page = (isset($_GET['page']) && !empty($_GET['page'])) ? Utility::clean_string($_GET['page']) : 'time_attendance';
   $getString = str_replace("&uid={$employeeID}", "", $getString); 
   $employeeID ? $getString .= "&uid={$employeeID}" : null;	
   $getString = str_replace("&page={$page}", "", $getString); 
   ?>
   <div class="container-fluid">
      <div class="col-md-12 px-3">
         <div class="row border shadow-lg  g-0"> 
            <?php
            if($reportingLinkArray && is_array($reportingLinkArray)  && count($reportingLinkArray) ){
               foreach($reportingLinkArray as $link){
                  // if($userDetails->adminLevel >= $link->adminlevel){
                  	$active = ($link->slug == $page) ? true : false;
                     $activeClass = ($link->active) ? "border-primary" : "border-white";
                     if (isset($link->slug) && $link->slug === $page) {
                        $activeClass = 'active bg-light-blue';
                    } else {
                        $activeClass = '';
                    }
                     echo '<div class="col-md-3 col-6 border-end border-bottom p-3 '.$activeClass.'">
                        <a href="'.$base.'html/'.$getString.'&uid='.$employeeID.'&page='.$link->slug.'" class="text-decoration-none">
                           <div class="d-flex align-items-center justify-content-center flex-column">
                              <div class="icon-box bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:60px; height:60px;">
                                 <i class="'.$link->icon.' fs-24 text-primary"></i>
                              </div>
                              <h5 class="mb-1 text-center">'.$link->title.'</h5>
                              <p class="mb-0 text-center fst-italic" style="font-size:0.85rem;">'.$link->description.'</p>
                           </div>
                        </a>
                     </div>';
                  // }
               }

            }?>
         </div>
      </div>
      <?php $getString .= "&page={$page}" ?>
      <div class="col-12 ">
        <div class="mt-4">           
				<?php
				$selected = array_filter($reportingLinkArray, function($link) use ($page) {
					return $link->slug === $page;
				});
				$selected = array_values($selected);
				$selected = count($selected) > 0 ? $selected[0] : null;
				// Include the specific page content based on the selected page?>
            <div class="container-fluid">
               <div class="card card-body">
                  <h5 class="card-title border-bottom pb-0 mb-3 d-flex align-items-center justify-content-between">
                     <span> <?= $selected->title ?> </span>                  
                     <a class=" float-end btn btn btn-sm  btn-icon rounded-pill btn-primary-light" href="#manageEntityAddress" data-bs-toggle="modal" role="button" aria-expanded="false" aria-controls="manageEntityAddress">
                        <i class="ri-add-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Add Contact and Address"></i>
                     </a>
                  </h5>
                 
                     <?php                  
                     if(isset($selected->subMenu) && !empty($selected->subMenu)) {   ?>
                     
                        <div class="col-12 d-flex flex-wrap justify-content-end mb-3">
                           <?php
                           // var_dump($selected->subMenu);
                           if(is_array($selected->subMenu)) {
                              $subMenuPage = $_GET['subMenu'] ?? $selected->subMenu[0]->slug;                        
                              foreach($selected->subMenu as $subMenuItem) {
                                 $isActive = ($subMenuItem->slug == $subMenuPage) ? ' active ' : ' ';
                                 echo "<a href='{$base}html/{$getString}&subMenu={$subMenuItem->slug}' class='btn btn-sm btn-outline-primary   mx-1  {$isActive}'> {$subMenuItem->title}</a>";  
                              }
                           } else {
                              $subMenuPage = $_GET['submenu'] ?? $selected->subMenu->slug;
                              $isActive = ($selected->subMenu->slug == $subMenuPage) ? 'active bg-light-blue' : '';                        
                              echo '<a href="'.$selected->subMenu->link.'" class="btn btn-sm btn-outline-primary mx-1">'.$selected->subMenu->name.'</a>';                     
                           }?>
                        </div>
                        
                        <?php
                        
                           $currentSubMenuItem = null;
                           if (isset($selected->subMenu) && is_array($selected->subMenu)) {
                              $filteredSubMenus = array_filter($selected->subMenu, function($item) use ($subMenuPage) {
                                 return $item->slug === $subMenuPage;
                              });
                              $filteredSubMenus = array_values($filteredSubMenus); // Re-index the array
                              if (count($filteredSubMenus) > 0) {
                                 $currentSubMenuItem = $filteredSubMenus[0];
                              }
                           }
                           echo isset($currentSubMenuItem->header) && !empty($currentSubMenuItem->header)    ? "<h4 >{$currentSubMenuItem->header}</h4>" : "";
                        $subMenuPageFile = "includes/scripts/reporting/sub_menu/{$subMenuPage}.php";
                        if (file_exists($subMenuPageFile)) {
                           include $subMenuPageFile;
                        } else {
                           // Alert::error("The requested page does not exist.", true);
                           // code to create the file can be added here
                           $subMenuDirPath = dirname($subMenuPageFile);
                           // check if the directory does not exist  and create directory and file
                           //create a directory if it does not exist and create files if a given path is provided
                           $fileCreate = File::create_directory_files($subMenuPageFile, false);

                          
                           if($fileCreate) {
                           // If we reached this point, the sub-menu file (and its directory) either existed
                           // or was successfully created. Now, include the file to display its content.
                           // Note: An Alert::error "The requested page does not exist." would have already been
                           // displayed before this block if the file was initially missing.
                           include $subMenuPageFile;
                           }
                        }
                     } else {
                        $pageFile = "includes/scripts/reporting/{$page}.php";
                        // Check if the file exists before including it
                        if (file_exists($pageFile)) {
                           include $pageFile;
                        } else {
                           Alert::error("The requested page does not exist.", true);
                        }
                     }?>
                  </div>
               </div>
            </div>           
        </div>
      </div>
   </div>