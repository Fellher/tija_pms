<?php
if(!$orgDataID ) {
   Alert::info("You need to select an organisation and entity to view clients", true, array('fst-italic', 'text-center', 'font-18'));?>
   <div class="col-6 mx-auto">         
      <div class="card custom-card">
         <div class="card-header jsustify-content-between">
            <h4 class="card-title">Select Organisation and Entity</h4>
         </div>
         <div class="card-body">                        
            <div class="list-group list-group-flush"> 
               <?php foreach ($allOrgs as $org) { ?>
                  <div class="list-group-item list-group-item-action">
                     <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home&orgDataID={$org->orgDataID}" ?>">
                     <?php echo $org->orgName; ?>
                     </a>
                  </div>
               <?php } ?>
            </div> 
         </div>              
      </div>
   </div>
   <?php
   return;
} else if(!$entityID) {
   $entities = Data::entities(array('orgDataID'=>$orgDataID), false, $DBConn); 
   Alert::info("You need to select an entity to view clients", true, array('fst-italic', 'text-center', 'font-18'));?>
      <div class="col-6 mx-auto">
         <div class="card custom-card">
            <div class="card-header justify-content-between">
               <h4 class="card-title">Select Entity</h4>
            </div>
            <div class="card-body">
               <div class="list-group list-group-flush">
                  <?php foreach ($entities as $entity) { ?>
                     <div class="list-group-item list-group-item-action">
                        <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home&orgDataID={$orgDataID}&entityID={$entity->entityID}" ?>">
                        <?php echo $entity->entityName; ?>
                        </a>
                     
                     </div>
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>            
   <?php
   return;
} ?>