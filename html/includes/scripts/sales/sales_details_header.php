<div class="container-fluid ">
   <div class="card card-body col-md-12 my-4">
      <div class="card  bg-light alert  alert-dismissible fade show border-solid" role="alert">
         <div class="row">
            <div  class="col-md py-2" >               
               <div class="border-end ">
                  <h4 class="mb-0 t400 font-22">Sales  </h4>							
                  <div class="font-22 ">
                     <span class="font-14 me-3">KES</span>
                     <?php echo  $salesCaseDetails->salesCaseEstimate ? number_format($salesCaseDetails->salesCaseEstimate, 2, '.', ' ')  : 0 ?>
                     <br>
                     <span class="font-14"> Expected sales value</span>                      							
                  </div>                  
               </div>
            </div>
 
            <div class="col-md py-2">
               <div class="border-end ">
                  <div class=" row">
                     <div class="col-7 text-left">	
                        <h4 class="mb-0 t400">Project Plan </h4>
                        <span class="font-22 col-12">0%</span><br>
                        <span>Work not started yet</span>	
                     </div>
                                          
                  </div>
               </div>
            </div>

            <div class="col-md">
               <h4 class="mb-0 t300 font-22">Collaborations</h4>
               <div class="col-md border-end">
                  <div class="font-22 clientsVal ">No Project Tasks</div>
               </div>
            </div>

            <div class="col-md py-2">
               <div class="border-right ">
                  <div class=" row">
                     <div class="col-7 text-left">	
                        <h4 class="mb-0 t400">Financials </h4>
                        <span class="font-22 col-12">0%</span><br>
                        <span>Work not started yet</span>	
                     </div>                                          
                  </div>
               </div>
            </div>

         </div> 
          <button type="button"  class="btn-close nobg" >
            <a tabindex="0"  role="button" data-bs-toggle="popover" data-trigger="focus" title="Organize your work with projects" data-bg-content="Before you start tracking your work, create projects, assign clients, and add tasks. You can choose from advanced billing rates, set up approval workflow, add custom budgets, and set task estimates. Assign tasks to your team and monitor allocated time."><i class="icon-cog"></i></a>
         </button>

      </div>
   </div>				
</div>