<div class="container-fluid">
	<div class="card card-body">
		<div class="row">						
			<h2 class="t400 border-bottom mb-0">Contact & Address</h2>
			<div class="col-lg-4 col-sm-12 px-0">
				<h5 class="bs-gray-300 px-2"> Address 
					<a href="" class="nott float-end t400"  data-bs-toggle="modal" data-bs-target="#addAddress" > <i class=" uil-map-marker-plus font-16"></i>add Address</a>
				</h5>
				<?php 
				echo Utility::form_modal_header("addAddress", 'work/admin/manage_address.php', "Add New Main Address", array('modal-lg', 'modal-dialog-centered'), $base);
					include "includes/work/modals/manage_clients_address.php";
				echo Utility::form_modal_footer();

				if ($clientAddresses) {
					foreach ($clientAddresses as $k => $address) {

						if ($address->addressTypeID) {
							$unseRaddressTypeIDArr = unserialize(base64_decode($address->addressTypeID)) ;
							$addressTypeIDArr = !is_array($unseRaddressTypeIDArr) ? array($address->addressTypeID): $unseRaddressTypeIDArr;
							$addressTypeNameArr= array();							
							if ($addressTypeIDArr) {
								foreach ($addressTypeIDArr as $key => $addTypeID) {
									$addDetails = Work::address_types(array("addressTypeID"=> $addTypeID), true, $DBConn);
									$addressTypeNameArr[]=  "{$addDetails->addressTypeName} Address";
								}
							}							
						}

						$addressTypes = "";
						if ($addressTypeNameArr) {
							$addressTypes = implode("/ ", array_filter($addressTypeNameArr));
						}
						$addressType= Work::address_types (array("addressTypeID"=> $address->addressTypeID, "Suspended"=> "N"), true, $DBConn);?>
						<div class="col feature-box  fbox-center fbox-border fbox-dark fbox-effect border-0">
							<div class="fbox-icon center mx-auto col-12">
								<a href="">
									<i class="uil-location-pin-alt i-alt"></i>
								</a>
							</div>
							<div class="fbox-content text-start">
								<h4 class="text-capitalize t500 border-bottom"><?php  echo $address->addressTypeID ? $addressTypes : ""; ?>
									<a href="" class="float-end font-22"  data-bs-toggle="modal" data-bs-target="#editAddress<?php echo $address->addressID;  ?>" >
										<i class="uil-map-marker-edit"></i>
									</a>
								</h4>
								<div class="font-16 font-primary bg-light p-2 text-black">
									<?php echo $address->address; ?>
								</div>
								<div >
									<p class="row">
										<span class="col-sm-4 t600"> City</span>
										<span class="col-sm-8 t400"> <?php echo $address->city; ?></span> 
										<span class="col-sm-4 t600"> Country</span>
										<span class="col-sm-8 t400"> <?php echo $address->country; ?></span>
										<span class="col-sm-4 t600"> Phone</span>
										<span class="col-sm-8 t400"> <?php echo $address->telephone; ?></span>
										<span class="col-sm-4 t600"> Email</span>
										<span class="col-sm t400"> <?php echo $address->clientEmail; ?></span>
									</p>
								</div>
						
							</div>	
						</div>
						<?php
						echo Utility::form_modal_header("editAddress{$address->addressID}", "work/admin/manage_address.php", "Manage Address Details {$addressTypes}", array('modal-lg', "modal-dialog-centered"), $base);?>
							<input type="text" name="addressID" value="<?php echo $address->addressID; ?>">
							<?php								
							include "includes/work/modals/manage_clients_address.php";
						echo Utility::form_modal_footer();
					}					
				} else {
					Alert::info("<h4 class='text-center mb-0'> There are no addresses set up for this client</h4>", false, array("text-center", "mb-0"));
				} ?>
			</div>
			<div class="col-8">
				<h4 class="gray-heading"> Contacts <a href="" class="nott float-end "  data-bs-toggle="modal" data-bs-target="#addContact" > <i class=" icon-plus"></i>add Contact</a></h4>  
				<?php
				echo Utility::form_modal_header("addContact", "work/admin/manage_client_contact.php", "Add Contact ", array("modal-lg","modal-dialog-centered"), $base);
					include "includes/work/modals/manage_client_contact.php";
				echo Utility::form_modal_footer();
				if ($clientContacts) {?>
					<div class="row">
						<?php 
						foreach ($clientContacts as $key => $clientContact) {
							$salutation=Utility::salutation (array($clientContact->salutationID), true, $DBConn);							
							$name=implode(' ', array_filter(array($salutation ? $salutation->salutation: '', $clientContact->firstName, $clientContact->lastName), function($p){return $p?true : false;})); ?>
							<div class="col-md-6">
								<div class="col feature-box  fbox-center fbox-border fbox-dark fbox-effect border-0">
									<div class="fbox-icon center mx-auto col-12">
										<a href="">
											<i class="fa-solid fa-user i-alt"></i>
										</a>
									</div>
									<div class="fbox-content ">
										<h4 class="text-capitalize t500 border-bottom mb-0"><?php  echo $name; ?> 
											<a href="" class="float-end font-12"  data-bs-toggle="modal" data-bs-target="#editContact<?php echo $clientContact->contactID ?>" ><i class="fa-solid fa-user-edit"></i></a>
										</h4>
										<div class="font-16 font-secondary bg-light px-2 text-dark">
											<?php echo $clientContact->title; ?>
										</div>
										<div >
											<p class="row">											
												<span class="col-sm-4 t600"> Email</span>
												<span class="col-sm-8 t400"> <?php echo $clientContact->email; ?></span>
												<span class="col-sm-4 t600"> Phone</span>
												<span class="col-sm t400"> <?php echo $clientContact->telephone; ?></span>
											</p>
										</div>														
									</div>
								</div>
							</div>
							<?php 
							echo Utility::form_modal_header("editContact{$clientContact->contactID}", "work/admin/manage_client_contact.php", "Edit Contact {$name}", array("modal-lg", "modal-dialog-centered"), $base);?>
								<input type="hidden" name="contactID" class="form-control form-control-sm form-control-plaintext border-bottom bg-light-blue" value="<?php echo $clientContact->contactID ?>">
								<?php
								include "includes/work/modals/manage_client_contact.php";
							echo Utility::form_modal_footer();	
						}?>
					</div>
					<?php							
				} else {
					Alert::warning("No contact has been set up for this client");
				}?>						
			</div>
		</div>
	</div>
</div>