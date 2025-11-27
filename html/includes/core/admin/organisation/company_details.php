<!-- Page Header Close -->
<?php
$OrganisationDetails = Admin::org_data(array("suspended"=>'N'), true, $DBConn);

// Check if organisation details exist
if (!$OrganisationDetails || !is_object($OrganisationDetails)) {
    Alert::danger("Unable to load organisation details. Please ensure your organisation is properly configured.", true, array('fst-italic', 'text-center', 'font-18'));
    return;
}

$industrySectors = Data::industry_sectors(array("Suspended"=>'N'), false, $DBConn);?>
<!-- Start::row-1 -->
<div class="row d-flex align-items-stretch ">
    <div class="col-xxl-3 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="profile-container mb-4">
                    <form action="<?php echo "{$base}php/scripts/global/manage_organisation_details.php" ?>" method="POST"   enctype='multipart/form-data' class="m-0" id="organisationForm">
                        <div class="profile-setting-cover">
                        <?php
                            if ($OrganisationDetails) {?>
                                <input type="hidden" name="orgDataID" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo "$OrganisationDetails->orgDataID"; ?>">
                                <?php
                            } ?>
                            <img src="<?php  echo  ($OrganisationDetails && !empty($organisationDetails->orgLogo)) ? "{$config['DataDir']}{$OrganisationDetails->orgLogo}" : "{$base}assets/img/png-images/2.png" ?>" alt="" class="" id="profile-img2">
                            <div class="main-profile-cover-content">
                                <div class="text-center">
                                    <span class="avatar avatar-rounded chatstatusperson">
                                        <img class="chatimageperson"  <?php echo $OrganisationDetails ? "src='{$config['DataDir']}org_logos/{$OrganisationDetails->orgLogo}'" : ""; ?>" alt="img" id="profile-img">
                                        <span class="profile-upload">
                                            <i class="ri ri-pencil-line cursor-pointer"></i>
                                            <input type="file" name="orgLogo" class="absolute inset-0 w-full h-full opacity-0 " id="profile-change">
                                            </span>
                                    </span>
                                </div>
                                <span class="background-upload">
                                    <i class="ri ri-pencil-line"></i>
                                    <input type="file" name="photo" name="backgroundImage" class="" id="profile-change2">
                                </span>
                            </div>
                        </div>
                        <div class="text-center mt-3 saveLogo d-none">
                            <button type="submit" class="btn btn-primary-light btn-sm rounded-pill" id="saveProfile">Save new Logo</button>
                        </div>
                    </form>

                    <script>
                        document.getElementById('profile-change').addEventListener('change', function() {
                            var file = this.files[0];
                            if (file) {
                                var reader = new FileReader();
                                reader.onload = function() {
                                    document.getElementById('profile-img').setAttribute('src', this.result);
                                }
                                reader.readAsDataURL(file);
                            }
                            document.querySelector('.saveLogo').classList.remove("d-none");
                        });
                    </script>
                </div>

                <div class="flex-column nav-style-5" role="tablist">
                    <div class="t300 fst-italic fs-18 mb-3 d-block border-bottom">
                        <?php echo ($OrganisationDetails && !empty($OrganisationDetails->orgName)) ? $OrganisationDetails->orgName : "Company Name"; ?>
                    </div>
                    <div class="t300 fst-italic fs-18 mb-3 d-block border-bottom">
                        <?php echo ($OrganisationDetails && !empty($OrganisationDetails->orgAddress)) ? $OrganisationDetails->orgAddress : "Company Address"; ?>
                    </div>
                    <div class="t300 fst-italic fs-18 mb-3 d-block border-bottom">
                        <?php echo ($OrganisationDetails && !empty($OrganisationDetails->industryTitle)) ? $OrganisationDetails->industryTitle : "Company Postal Code"; ?>
                    </div>
                    <div class="t300 fst-italic fs-18 mb-3 d-block border-bottom">
                        <?php echo ($OrganisationDetails && !empty($OrganisationDetails->orgPhoneNumber1)) ? $OrganisationDetails->orgPhoneNumber1 : "Company City"; ?>
                    </div>
                    <div class="t300 fst-italic fs-18 mb-3 d-block border-bottom">
                        <?php echo ($OrganisationDetails && !empty($OrganisationDetails->orgEmail)) ? $OrganisationDetails->orgEmail : "Company Country"; ?>
                    </div>
                    <?php  echo $OrganisationDetails->orgLogo ? "{$config['DataDir']}{$OrganisationDetails->orgLogo}"  : ""?>

                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-9 col-lg-8 d-flex align-items-stretch">
        <div class="card custom-card border-0">
            <div class="card-body p-0">
                <div class="card-header">
                    <div class="card-title d-block col-12 ">
                        <i class="ri ri-shield-user-line me-2"></i>
                        General Details
                        <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end editOrganisation"  href="" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Company Details" >
                            <i class="ti ti-edit"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php include_once "includes/core/admin/organisation_details.php"  ?>

                </div>
            </div>
        </div>
    </div>


</div>
<?php
$entities = Data::entities(array("suspended"=>'N'), false, $DBConn);
// var_dump($entities);
if ($OrganisationDetails) {
    $organisation = $OrganisationDetails;
    ?>
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
        // var_dump($entities);
        $entityTypes = Data::entity_types(array(), false, $DBConn);
        $african_countries = Data::countries([], false, $DBConn);
        $industrySectors = Data::industry_sectors(["Suspended"=> 'N'], false, $DBConn);
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
                            // var_dump($entity);
                            $parentEntity="";
                            $entity->entityParentID ? $parentEntity= Data::entities(['entityID'=>$entity->entityParentID], true, $DBConn) : "";
                            // var_dump($parentEntity);
                            // var_dump($entity->entityParentID);
                            // var_dump($parentEntity) ?>
                            <tr>
                                <td class="text-start"><?= $entity->entityName ?></td>
                                <td class="text-start"><?= $entity->entityTypeTitle ?></td>
                                <td class="text-start"><?= $parentEntity && $entity->entityParentID != 0 ?   $parentEntity->entityName : "" ?></td>
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
        <p class="font-18">
             Are you sure you want to delete the entity "<span id="entityNameToDelete" class="fw-bold text-danger"></span>"?
            This action cannot be undone.  </p>
    <?php
    echo Utility::form_modal_footer('Yes, Delete Entity', 'deleteConfirmed',  ' btn btn-danger', true);

} else {
    Alert::info("No Organisation Data Found", true, array('fst-italic', 'text-center', 'font-18'));
} ?>
