<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
    <h1 class="page-title fw-medium fs-24 mb-0">Company Details</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">Company Details</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header Close -->
<?php 
$OrganisationDetails = Admin::org_data(array("suspended"=>'N'), true, $DBConn);
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
                                    <input type="file" name="backgroundImage" name="backgroundImage" class="" id="profile-change2">
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
                    <?php  echo  "{$config['DataDir']}{$OrganisationDetails->orgLogo}" ?>
                    
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
                    <?php include_once "includes/core/admin/organisation_details.php "  ?>
                    
                </div>
            </div>
        </div>
    </div>

</div>