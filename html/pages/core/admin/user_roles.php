<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
    <h1 class="page-title fw-medium fs-24 mb-0">Tija Users  Admin Roles/permission Profiles</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">User Roles</li>
            </ol>
        </nav>
    </div>
</div>

<?php echo $stID = (isset($_GET['stID']) && !empty($_GET['stID'])) ?  Utility::clean_string($_GET['stID']) : "roles"; ?>

<!-- Start::row-1 -->
<div class="row d-flex align-items-stretch ">
    <div class="col-xxl-3 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-header">
                    <div class="card-title d-block col-12 ">
                        <i class="ri ri-shield-user-line me-2"></i>Roles Menu
                    </div>
                </div>  
                <nav class="nav nav-tabs flex-column nav-style-5" role="tablist">
                    <a class="nav-link  bg-light border <?php echo $stID === "roles" ? "active" : "" ?>"  href="<?php echo "{$base}html/{$getString}&stID=roles"?>"  aria-selected="<?php echo $stID === "roles" ? "true" : "false" ?>"><i class="ri ri-shield-user-line me-2 fs-18 align-middle"></i> Assigned Roles</a>
                    <a class="nav-link  bg-light border mt-3 <?php echo $stID === "type" ? "active" : "" ?> "  href="<?php echo "{$base}html/{$getString}&stID=type" ?>" aria-selected="false"><i class="ri ri-global-line me-2 fs-18 align-middle"></i>Permission Types</a>
                    <a class="nav-link  bg-light border mt-3 <?php echo $stID === "profiles" ? "active" : "" ?>"  href="<?php echo "{$base}html/{$getString}&stID=profiles" ?>" aria-selected="true" tabindex="-1"><i class="ri ri-lock-line me-2 fs-18 align-middle"></i>Permission Profiles</a>
                    <a class="nav-link  bg-light border mt-3 <?php echo $stID === "scope" ? "active" : "" ?>"  href="<?php echo "{$base}html/{$getString}&stID=scope" ?>" aria-selected="false" tabindex="-1"><i class="ri ri-account-circle-line me-2 fs-18 align-middle"></i> Permission Scope</a>
                    <a class="nav-link  bg-light border mt-3 <?php echo $stID === "adminSettings" ? "active" : "" ?>"  href="<?php echo "{$base}html/{$getString}&stID=adminSettings" ?>" aria-selected="false" tabindex="-1"><i class="ri ri-notification-4-line me-2 fs-18 align-middle"></i>Administrative Settings</a>
                </nav>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-9 col-lg-8 d-flex align-items-stretch">
        <div class="card custom-card border-0">
            <div class="card-body p-0">
                <?php 
                if($stID == "roles") {?>
                    <div class="card-header"> 
                        <div class="card-title d-block col-12 "><i class="ri ri-shield-user-line me-2"></i>Tija Roles 
                            <span class="float-end"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Permission role Profile Details">
                                <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end permissionRoleProfileModal"  href="#permissionRoleProfileModal" data-bs-toggle="modal"  > 
                                    <i class="ti ti-plus"></i> 
                                </a> 
                            </span>
                    </div>
                    <?php 
                     echo Utility::form_modal_header("permissionRoleProfileModal",  "global/admin/manage_permision_role_profiles.php","Add New Permission Role Profile", array('modal-lg', "modal-dialog-centered"), $base);
                     include("includes/core/admin/manage_permission_role_profiles.php");
                     echo Utility::form_modal_footer("Add Permission Role", "Add Permission Type"); 
                     ?>
                    <div class="card-body">
                        <?php  
                        $roles= array();;
                        $roles = Admin::tija_permission_roles_details(array("suspended"=>'N'), false, $DBConn);
                        // var_dump($roles);
                        if($roles) {?>
                            <div class="table-responsive">
                                <table class="table text-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Roles</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Scope</th>
                                            <th scope="col">View</th>
                                            <th scope="col">Edit</th>
                                            <th scope="col">Add</th>
                                            <th scope="col">Del</th>
                                            <th scope="col">Imp</th>
                                            <th scope="col">Exp</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                        
                                    <?php                                 
                                        foreach ($roles as $key => $role) {?>
                                            <tr>
                                                <td><?php echo $role->permRoleTitle ?></td>
                                                <td><?php echo $role->roleTypeTitle ?></td>
                                                <td><?php echo $role->permissionScopeTitle  ?></td>
                                                <td><?php echo $role->viewPermission ? $role->viewPermission : "N" ?></td>
                                                <td><?php echo $role->editPermission ?  $role->editPermission: "N" ?></td>
                                                <td><?php echo $role->addPermission ? $role->addPermission: 'N'; ?></td>
                                                <td><?php echo $role->deletePermission ? $role->deletePermission : 'N' ?></td>
                                                <td><?php echo $role->importPermission ? $role->importPermission : 'N' ?></td>
                                                <td><?php echo $role->exportPermission ? $role->exportPermission : 'N';  ?></td>
                                                <td>
                                                    <div class="btn-list">                                          
                                                        <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo $role->permissionRoleID ?>" data-bs-placement="top" data-bs-title="Edit" class="btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm roleEdit "><i class="ti ti-pencil"></i></a>
                                                        <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo $role->permissionRoleID ?>" data-bs-placement="top" data-bs-title="Delete" class="btn  btn-icon rounded-pill  btn-danger-light btn-wave btn-sm roleDelete "><i class="ti ti-trash"></i></a>
                                                    </div>
                                                </td>
                                            <?php
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php 
                        } else {
                            Alert::info("No roles found in the system. Please add a role to continue", false, array("fst-italic", "text-center", "fs-6"));
                        }
                    ?>
                    </div>
                <?php
                } elseif( $stID ==="type") { 
                    $node = "permisionType"?>
                    <div class="card-header"> 
                        <div class="card-title d-block col-12 ">
                            <i class="ri ri-shield-user-line me-2"></i>Permission Types 
                            <span class="float-end"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Company Details">
                                <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end editOrganisation"  href="#managePermissionTypes" data-bs-toggle="modal"  > 
                                    <i class="ti ti-plus"></i> 
                                </a> 
                            </span>
                        </div>
                    </div>
                    <?php 
                    echo Utility::form_modal_header("managePermissionTypes",  "global/admin/manage_permission_types.php","Add New Permission Type", array('modal-lg', "modal-dialog-centered"), $base);
                    include("includes/core/admin/manage_permission_types.php");
                    echo Utility::form_modal_footer("addPermissionType", "Add Permission Type"); 
                    $permissionTypes = Admin::tija_permission_types(array("suspended"=>'N'), false, $DBConn);
                    if($permissionTypes) {?>
                        <div class="list-group list-group-flush">

                            <?php
                            foreach($permissionTypes as $key => $permissionType) {?>
                                <div  class="list-group-item list-group-item-action " ria-current="true">
                                    <div class="d-sm-flex w-100 justify-content-between">
                                        <input type="hidden" name="productID" value="<?php echo $permissionType->permissionTypeID ?>">
                                        <h6 class="mb-1 fw-semibold">    
                                        <?php echo $permissionType->permissionTypeTitle ?> </h6>
                                        <small>
                                            <div class="btn-list">                                          
                                                <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo $permissionType->permissionTypeID ?>" data-bs-placement="top" data-bs-title="Edit" class="btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm productEdit "><i class="ti ti-pencil"></i></a>
                                                <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" class="btn  btn-icon rounded-pill  btn-danger-light btn-wave btn-sm productDelete "><i class="ti ti-trash"></i></a>
                                            </div>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo $permissionType->permissionTypeDescription ?></p>                                
                                </div>
                                <?php                           
                            }?>
                        </div>
                            <?php  
                    } else {
                        Alert::info("No roles found in the system. Please add a role to continue", false, array("fst-italic", "text-center", "fs-6"));
                    }
                } elseif($stID=== "profiles") {
                    $nodeID .= "Profiles";?>
                        <div class="card-header"> 
                            <div class="card-title d-block col-12 ">
                                <i class="ri ri-shield-user-line me-2"></i>Permission Profies
                                <span class="float-end"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Company Details">
                                    <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end editOrganisation"  href="#managePermissionProfiles" data-bs-toggle="modal"  > 
                                        <i class="ti ti-plus"></i> 
                                    </a> 
                                </span>
                            </div>
                        </div>
                    <?php
                     echo Utility::form_modal_header("managePermissionProfiles",  "global/admin/manage_permission_profiles.php","Add New Permission Profile", array('modal-lg', "modal-dialog-centered"), $base);
                     include("includes/core/admin/manage_permission_profile.php");
                     echo Utility::form_modal_footer("Add Permission Profile", "AddPermission_profile"); 
                     $permissionProfiles = Admin::permission_profile_types(array("suspended"=>'N'), false, $DBConn);
                     if($permissionProfiles) {?>
                        <div class="list-group list-group-flush">
                            <?php
                            foreach($permissionProfiles as $key => $permissionProfile) {?>
                                <div  class="list-group-item list-group-item-action " ria-current="true">
                                    <div class="d-sm-flex w-100 justify-content-between">
                                        <input type="hidden" name="productID" value="<?php echo $permissionProfile->permissionProfileID ?>">
                                        <h6 class="mb-1 fw-semibold">    
                                        <?php echo $permissionProfile->permissionProfileTitle ?> </h6>
                                        <small>
                                            <div class="btn-list">                                          
                                                <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo $permissionProfile->permissionProfileID ?>" data-bs-placement="top" data-bs-title="Edit" class="btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm productEdit "><i class="ti ti-pencil"></i></a>
                                                <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" class="btn  btn-icon rounded-pill  btn-danger-light btn-wave btn-sm productDelete "><i class="ti ti-trash"></i></a>
                                            </div>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo $permissionProfile->permissionProfileDescription ?></p>

                                </div>
                                <?php
                            }
                         } else {
                            Alert::info("No permission Profiles found in the system. Please add a profile  to continue", false, array("fst-italic", "text-center", "fs-6"));
                        }
                    
                } elseif ($stID === "scope") {
                    $nodeID .= "scope";?>
                        <div class="card-header"> 
                            <div class="card-title d-block col-12 ">
                                <i class="ri ri-shield-user-line me-2"></i>Permission Scope
                                <span class="float-end"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Permission Scope">
                                    <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end editOrganisation"  href="#managePermissionScope" data-bs-toggle="collapse"  role="button" aria-expanded="false" aria-controls="managePermissionScope">  
                                        <i class="ti ti-plus"></i> 
                                    </a> 
                                </span>
                            </div>
                        </div>
                
                      <form action="<?php echo "{$base}php/scripts/global/admin/manage_permission_scope.php" ?>" method="post" class="collapse" id="managePermissionScope">
                            <div class="card my-0 pt-0 bg-light-blue" >
                                <div class="border-bottom border-dark"> 
                                    <div class="card-title d-block col-12 my-0 h5">
                                        <i class="ri ri-shield-user-line me-2"></i>Permission Scope </div>
                                    </div>
                                </div>
                                <div class=" bg-none m-3">
                                    <div class="form-group">
                                        <label for="permissionScopeTitle"> Permission Scope</label>
                                        <input type="text" class="form-control-xs form-control-plaintext border-bottom bg-light" id="permissionScopeTitle" name="permissionScopeTitle" placeholder="Enter Permission Scope">
                                    </div>
                                    <div class="form-group">
                                        <label for="permissionScopeDescription"> Description</label>
                                        <textarea class="form-control-xs form-control-plaintext border-bottom bg-light" id="permissionScopeDescription" name="permissionScopeDescription" placeholder="Enter Permission Scope Description"></textarea>
                                    </div>
                                    
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary btn-sm float-end">Save</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <?php $scopes = Admin::permission_scope(array("suspended"=>'N'), false, $DBConn); 
                            if($scopes) {?>
                              <div class="list-group list-group-flush">
                            <?php
                                foreach ($scopes as $key => $scope) {?>                                  
                                    <div  class="list-group-item list-group-item-action " ria-current="true">
                                        <div class="d-sm-flex w-100 justify-content-between">
                                            <input type="hidden" name="productID" value="<?php echo $scope->permissionScopeID ?>">
                                            <h6 class="mb-1 fw-semibold">    
                                            <?php echo $scope->permissionScopeTitle ?> </h6>
                                            <small>
                                                <div class="btn-list">                                          
                                                    <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo  $scope->permissionScopeID  ?>" data-bs-placement="top" data-bs-title="Edit" class="btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm productEdit "><i class="ti ti-pencil"></i></a>
                                                    <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" class="btn  btn-icon rounded-pill  btn-danger-light btn-wave btn-sm productDelete "><i class="ti ti-trash"></i></a>
                                                </div>
                                            </small>
                                        </div>
                                        <p class="mb-0"><?php echo $scope->permissionScopeDescription ?></p>                                
                                    </div>
                                    <?php
                                }
                            } else {
                                Alert::info("No roles scope settings found in the system. Please add a role scope settings to continue", false, array("fst-italic", "text-center", "fs-6"));
                            }
                               
                    # code...
                } ?>
                <!-- <div class="card-header"> 
                    <div class="card-title d-block col-12 "><i class="ri ri-shield-user-line me-2"></i>General Details <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end editOrganisation"  href="" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Company Details" > <i class="ti ti-edit"></i> </a> </div>
                </div>
                <div class="card-body">

                </div> -->
            </div>
        </div>
    </div>


</div>

<?php 
$getString.="&stID={$stID}";
