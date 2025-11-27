<div id="entityForm" class="needs-validation" novalidate>
    <div class="row g-3">
        <input type="hidden" name="entityID" id="entityID" value="">
        <!-- Entity Name -->
        <div class="col-md-4">
            <label for="entityName" class="form-label mb-0">Entity Name</label>
            <input type="text" class="form-control form-control-sm" id="entityName" name="entityName" required>
        </div>
        <div class="col-md-4">
            <label for="entityTypeID" class="form-label mb-0">Entity Type</label>
            <select class="form-select form-control-sm" id="entityTypeID" name="entityTypeID" required>
                <option value="">Choose...</option>
                <?php echo Form::populate_select_element_from_object($entityTypes, "entityTypeID", "entityTypeTitle", '', "", "Select Entity Type"); ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="entityParentID" class="form-label mb-0">Parent Entity</label>
            <select class="form-select  form-control-sm " id="entityParentID" name="entityParentID">
                <option value="0">No Parent Entity (Main Entity)</option>
                <option value="" disabled>──────────────────</option>
                <?php echo Form::populate_select_element_from_object($entities, "entityID", "entityName", '', "", "Select Parent Entity"); ?>
            </select>
        </div>

        <!-- Entity Description -->
        <div class="col-md-12">
            <label for="entityDescription" class="form-label mb-0">Description</label>
            <textarea class="borderless-mini" id="entityDescription" name="entityDescription" ></textarea>
        </div>

        <!-- Organization Data -->
        <div class="col-md-4 d-none">
            <label for="orgDataID" class="form-label mb-0">Organization Data</label>
            <input type="text" class="form-control form-control-sm " id="orgDataID" name="orgDataID" value="<?= $organisation->orgDataID ?>" required>

        </div>



        <!-- Industry Sector -->
        <div class="col-md-4">
            <label for="industrySectorID" class="form-label mb-0">Industry Sector</label>
            <select class="form-select  form-control-sm " id="industrySectorID" name="industrySectorID" required>
            <?php echo Form::populate_select_element_from_object($industrySectors, "industrySectorID", "industryTitle",'', "", "Select Industry Sector"); ?>
            </select>
        </div>

        <!-- Registration Number -->
        <div class="col-md-4">
            <label for="registrationNumber" class="form-label mb-0">Registration Number</label>
            <input type="text" class="form-control  form-control-sm " id="registrationNumber" name="registrationNumber" required>
        </div>

        <!-- Entity PIN -->
        <div class="col-md-4">
            <label for="entityPIN" class="form-label mb-0">Entity PIN</label>
            <input type="text" class="form-control  form-control-sm " id="entityPIN" name="entityPIN" required>
        </div>
         <!-- Entity Email -->
         <div class="col-md-3">
            <label for="entityEmail" class="form-label">Email</label>
            <input type="email" class="form-control  form-control-sm " id="entityEmail" name="entityEmail" required>
        </div>
          <!-- Entity Phone Number -->
        <div class="col-md-3">
            <label for="entityPhoneNumber" class="form-label">Phone Number</label>
            <input type="tel" class="form-control  form-control-sm " id="entityPhoneNumber" name="entityPhoneNumber" required>
        </div>

        <!-- Entity City -->
        <div class="col-md-3">
            <label for="entityCity" class="form-label mb-0">City</label>
            <input type="text" class="form-control  form-control-sm " id="entityCity" name="entityCity" required>
        </div>

        <!-- Entity Country -->
        <div class="col-md-3">
            <label for="entityCountry" class="form-label mb-0">Country</label>
            <select class="form-select form-control-sm" id="entityCountry" name="entityCountry" required>
                <?php
                // Use $countries if available, otherwise $african_countries
                $countryList = isset($countries) ? $countries : (isset($african_countries) ? $african_countries : array());
                if (!empty($countryList)) {
                    echo Form::populate_select_element_from_object($countryList, "countryID", "countryName", 25, "", "Select Country");
                } else {
                    echo '<option value="">Select Country</option>';
                }
                ?>
            </select>
        </div>




    </div>
</div>