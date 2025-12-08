<div class="editContactPersonForm" id="editContactPersonForm">
	<input type="hidden" name="userID" id="userID" value="<?php echo $userDetails->ID; ?>">
	<input type="hidden" name="clientID" id="clientID" value="<?= isset($clientID) ? $clientID : (isset($clientDetails) ? $clientDetails->clientID : '') ?>">
	<input type="hidden" name="clientContactID" id="clientContactID" value="">
	<fieldset class="col-md-12 ">
		<div class=" pt-0">
			<legend class="fs-18 bg-light-blue px-2 my-2">Personal Information</legend>
			<div class="row">
				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">First Name</label>
					<input type="text" name="firstName" class="form-control form-control-xs form-control-plaintext  bg-light border ps-2" placeholder="First Name" value="">
				</div>

				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Last Name</label>
					<input type="text" name="lastName" class="form-control form-control-xs form-control-plaintext  border bg-light ps-2 py-0" placeholder="Last Name" value="">
				</div>

				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Title</label>
					<input type="text" name="title" class="form-control form-control-xs form-control-plaintext  border bg-light ps-2" placeholder="title" value="">
				</div>

				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Salutation</label>
					<select class="form-control form-control-xs form-control-plaintext border bg-light" name="salutationID">
						<?php echo Form::populate_select_element_from_object($prefixes, 'prefixID', 'prefixName', "", '', 'Select salutation')  ?>
					</select>
				</div>
			</div>
		</div>
	</fieldset>

	<fieldset class="col-md-12">
		<div class="">
			<legend class="fs-18 bg-light-blue my-2 border-bottom px-2">Contact Infomation</legend>
			<div class="row">

				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Email</label>
					<input type="email" name="email" class="form-control form-control-xs form-control-plaintext bg-light border-bottom ps-2" placeholder="email@domain.com" value=" ">
				</div>
				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Telephone</label>
					<input type="text" name="telephone" class="form-control form-control-xs form-control-plaintext bg-light border-bottom ps-2" placeholder="+254 00 000 0000" value="">
				</div>



				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Address</label>
					<select class="form-control form-control-xs form-control-plaintext border-bottom bg-light pl-2" name="clientAddressID" id="clientAddressID">
						<?= Form::populate_select_element_from_object($addresses, 'clientAddressID', 'address', '', '', 'Select Address')  ?>
					</select>
				</div>

				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Contact Role</label>
					<select class="form-control form-control-xs form-control-plaintext border-bottom bg-light pl-2" name="contactTypeID">
						<?php echo Form::populate_select_element_from_object($contactTypes, 'contactTypeID', 'contactType',  '', '', 'Select contact Role')  ?>
					</select>
				</div>
			</div>
		</div>
	</fieldset>
</div>