<?php 
if($isAdmin) {   
    $organisationDetails = Admin::org_data(array(), false, $DBConn);
    if(!$organisationDetails){
        Alert::info("No Organisations set up for this tax computation instance", true, 
              array('fst-italic', 'text-center', 'font-18'));
              exit;
    }
    $entityTypes = Data::entity_types(array(), false, $DBConn);
    $african_countries = Data::countries([], false, $DBConn);
    $industrySectors = Data::industry_sectors(["Suspended"=> 'N'], false, $DBConn);  
    foreach ($organisationDetails as $key => $organisation) {?>
		<div class="card custom-card">
			<div class="card-header justify-content-between">
				<div class="card-title"> <?= $organisation->orgName ?> </div>                
					<button type="button"class="btn btn-sm btn-primary-light shadow-sm manageEntityOrganisation" data-bs-toggle="modal" data-organisationId="<?= $organisation->orgDataID ?>"  data-bs-target="#manageEntity">
						<i class="fas fa-plus"></i>
						Add New Entity
					</button>
				</div>
			</div>
         <?php 
         $entities = Data::entities_full(['orgDataID'=> $organisation->orgDataID, 'Suspended'=> 'N'], false, $DBConn);
			if($entities) {?>
					<div class="table-responsive">
						<table class="table text-nowrap mb-0">
							<thead class="table-light">
									<tr>
										<th class="text-start">Entity Name</th>
										<th class="text-start">Entity Type</th>
										<th class="text-start">Parent Entity</th>
										<th class="text-start">Industry Sector</th>
										<th class="text-start">Registration Number</th>
										<th class="text-start">Entity PIN</th>
										<th class="text-start">Countyry/City</th>
										<th class="text-end">Actions</th>
									</tr>
							</thead>
							<tbody>
									<?php
									foreach ($entities as $key => $entity) {
										$parentEntity="";
										$entity->entityParentID !== 0 ? $parentEntity= Data::entities(['entityID'=>$entity->entityParentID], true, $DBConn) : ""; 
										?>
										<tr>
											<td class="text-start"><?= $entity->entityName ?></td>
											<td class="text-start"><?= $entity->entityTypeTitle ?></td>
											<td class="text-start"><?= $entity->entityParentID !==0 ? $parentEntity->entityName : "" ?>
											
										</td>
											<td class="text-start"><?= $entity->industryTitle ?></td>
											<td class="text-start"><?= $entity->registrationNumber ?></td>
											<td class="text-start"><?= $entity->entityPIN ?></td>
											<td class="text-start"><?= "{$entity->countryName} / {$entity->entityCity}" ?></td>
											<td class="text-end">
													<button  
														class="btn btn-sm btn-primary-light shadow-sm editEntity" 
														data-bs-toggle="modal" 
														data-bs-target="#manageEntity" 
														data-id="<?= $entity->entityID ?>" 
														data-entity-name="<?= htmlspecialchars($entity->entityName) ?>"
														data-entity-description="<?= htmlspecialchars($entity->entityDescription) ?>"
														data-entity-type-id="<?= $entity->entityTypeID ?>"
														data-org-data-id="<?= $entity->orgDataID ?>"
														data-entity-parent-id="<?= $entity->entityParentID ?>"
														data-industry-sector-id="<?= $entity->industrySectorID ?>"
														data-registration-number="<?= htmlspecialchars($entity->registrationNumber) ?>"
														data-entity-pin="<?= htmlspecialchars($entity->entityPIN) ?>"
														data-entity-city="<?= htmlspecialchars($entity->entityCity) ?>"
														data-entity-country="<?= $entity->entityCountry ?>"
														data-entity-phone-number="<?= htmlspecialchars($entity->entityPhoneNumber) ?>"
														data-entity-email="<?= htmlspecialchars($entity->entityEmail) ?>">
														Edit
													</button>
													<button 
														class="btn btn-sm btn-danger-light shadow-sm deleteEntity"
														data-bs-toggle="modal"
														data-id="<?= $entity->entityID ?>"
														data-entity-name="<?= htmlspecialchars($entity->entityName) ?>"
														data-bs-target="#deleteEntityModal"  >
													
														<i class="fas fa-trash"></i>
														Delete
													</button>
											</td>
										</tr>
										<?php
									}
									?>
							</tbody>
						</table>
					</div>                
					<?php                    
			} else {
					Alert::info("There are no Entities set up for {$organisation->orgName}", array('fst-italic', 'text-center', 'font-18'));                    
			} ?>
      </div>   
      <?php 
    }
    echo Utility::form_modal_header("manageEntity", "tax/admin/manage_entity.php", "Add Organization Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base); 
        include "includes/scripts/global/modals/manage_entity.php";
    echo Utility::form_modal_footer('Save Entity');
    
    // Delete Confirmation Modal
    echo Utility::form_modal_header(
        "deleteEntityModal", 
        "tax/admin/manage_entity.php", 
        "Delete Organisation Entity", 
        array('modal-lg', 'modal-dialog-centered'), 
        $base
    ); 
    ?>     
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="entityID" id="entityID">         
            <p class="font-18"> Are you sure you want to delete the entity "<span id="entityNameToDelete" class="fw-bold text-danger"></span>"?
            This action cannot be undone.  </p> 
    <?php 
    echo Utility::form_modal_footer('Yes, Delete Entity', 'deleteConfirmed',  ' btn btn-danger', true); 
   
} else{
    Alert::error("You are not logged in. Please log in to continue", false, array("fst-italic", "text-center", "fs-6"));
}

?>