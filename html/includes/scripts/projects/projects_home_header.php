<div class="container-fluid ">
      <div class="card card-body col-md-12 my-4">
         <div class="card  bg-light alert  alert-dismissible fade show border-solid" role="alert">
            <div class="row">
               <div  class="col-md">
                  <h4 class="mb-0 t300 font-22">Projects </h4>
                  <div class="col-md border-end">							
                     <div class="font-22">
                        <?php echo ( $projects ) ? count($projects) : 0 ?>
                        <span class="font-14"> Projects</span> 
                        <span class="font-14"> ( <?php echo count($activeProjects) ." Active / ". count($overDueProjects) ." Overdue" ?> )</span>								
                     </div>                  
                  </div>
               </div>   
               <div class="col-md">
                  <h4 class="mb-0 t300 font-22">Projects Value </h4>
                  <div class="col-md border-end">
                     <div class="font-22">
                        <span class="font-14">KES</span>
                        <?php echo (isset($projectValue) && $projectValue !== '') ? number_format($projectValue, 2, '.', ' ') :'0.00'; ?>									
                     </div>
                  </div>
               </div>   
               <div class="col-md">
                  <h4 class="mb-0 t300 font-22">Clients</h4>
                  <div class="col-md border-end">
                     <div class="font-22 clientsVal "><?php echo (isset($clientsWithProjects) && !empty($clientsWithProjects)) ? count($clientsWithProjects) : 0; ?> Clients</div>
                  </div>
               </div>   
            </div>
            <button type="button"  class="btn-close nobg" >
               <a tabindex="0"  role="button" data-bs-toggle="popover" data-trigger="focus" title="Organize your work with projects" data-bg-content="Before you start tracking your work, create projects, assign clients, and add tasks. You can choose from advanced billing rates, set up approval workflow, add custom budgets, and set task estimates. Assign tasks to your team and monitor allocated time."><i class="icon-cog"></i></a>
            </button>				
         </div>
      </div>				
   </div>