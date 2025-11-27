<div class="row">
    <div class="form-group">
        <label for="permissionType" class="form-label mb-0 ">Permission Role Profile Name</label>
        <input type="text" class="form-control-xs form-control-plaintext border-bottom bg-light" id="permRoleTitle" name="permRoleTitle" placeholder="Enter Permission role Profile Name">
    </div>
    <div class= "form-control">
        <label for="permRoleDescription"> Permission Role Description</label>
        <textarea class="form-control-xs form-control-plaintext border-bottom bg-light" id="permRoleDescription" name="permRoleDescription" placeholder="Enter Permission Role Description"></textarea>
    </div>

    <div class="form-group">
        <?php $roleTypes = Admin::tija_role_types(array(), false, $DBConn);
        // var_dump($roleTypes); ?>
        <label for="roleTypeID" class="form-label">Permission Role Type</label>
        <?php 
        if($roleTypes){?>
        
            <select class="form-control form-control-sm form-control-plaintext border-bottom" id="roleTypeID" name="roleTypeID">
            <?php   echo  Form::populate_select_element_from_object($roleTypes, 'roleTypeID', 'roleTypeTitle', '', '', 'Select Permission Role type')?>
            </select>
            <?php
        } else {
            echo "<button type='button' class='btn btn-primary btn-sm w-100 addNewRoleType'>Add New Role Type</button>";
        }?>
        <script>
            let addNewRoleType = document.querySelector('.addNewRoleType');
            console.log(addNewRoleType);
            if(addNewRoleType) {
                addNewRoleType.addEventListener('click', function(){
                    let newRole = document.createElement('div');
                    newRole.classList.add('card','card-body', 'bg-light-blue', 'border', 'border-dark', "my-3", "p-3");
                    newRole.innerHTML = `
                    <h4 class="text-center border-bottom border-dark my-0 ">Add New Role Type</h4>
                        <div class="form-group">
                            <label for="roleTypeTitle" class="form-label mb-0 ">Role Type Title</label>
                            <input type="text" class="form-control-xs form-control-plaintext border-bottom bg-white " id="roleTypeTitle" name="roleTypeTitle" placeholder="Enter Role Type Title">
                        </div>
                        <div class="form-group ">
                            <label for="roleTypeDescription" class="form-label">Role Type Description</label>
                            <textarea class="form-control-xs form-control-plaintext border-bottom bg-white" id="roleTypeDescription" name="roleTypeDescription" placeholder="Enter Role Type Description"></textarea>
                        </div>`;

                        addNewRoleType.parentElement.appendChild(newRole);

                   /*  let roleType = document.querySelector('#roleTypeID');
                    let roleTypeTitle = document.querySelector('#roleTypeTitle');
                    let roleTypeDescription = document.querySelector('#roleTypeDescription');
                    let roleTypeData = {
                        roleTypeTitle: roleTypeTitle.value,
                        roleTypeDescription: roleTypeDescription.value
                    }
                    let roleTypeData = JSON.stringify(roleTypeData);
                    let xhr = new XMLHttpRequest();
                    xhr.open('POST', 'add_role_type.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.onload = function(){
                        if(this.status == 200){
                            let response = JSON.parse(this.responseText);
                            if(response.status == 'success'){
                                let roleType = document.querySelector('#roleTypeID');
                                let newRoleType = document.createElement('option');
                                newRoleType.value = response.data.roleTypeID;
                                newRoleType.textContent = response.data.roleTypeTitle;
                                roleType.appendChild(newRoleType);
                            }
                        }
                    }
                    xhr.send(roleTypeData);*/
                });

            }
            
        </script>
      
    </div>
    <div class="form-control">
        <label for="roleTypeDescription">  Permission profile</label>
      <?php $permissionProfiles = Admin::permission_profile_types(array('suspended'=>"N"), false, $DBConn); 
      if($permissionProfiles){?>
        <select class=" form-control-sm form-control-plaintext border-bottom bg-light" id="permissionProfileID" name="permissionProfileID">
            <?php echo Form::populate_select_element_from_object($permissionProfiles, 'permissionProfileID', 'permissionProfileTitle', '', '', 'Select Permission Profile'); ?>
        </select>
        <?php
      } else {
          echo "<button type='button' class='btn btn-primary btn-sm w-100 addNewPermissionProfile'>Add New Permission Profile</button>";
        }?>
        

      </div>
      <div class="form-group">
        <?php $permissionLevels = Admin::permission_scope(array(), false, $DBConn); 
        // var_dump($permissionLevels);?>
        <label for="permissionScopeID" class="form-label">Permission Role Level/scope</label>
        <select class="form-control-sm form-control-plaintext border-bottom bg-light" id="permissionScopeID" name="permissionScopeID">
            <?php echo Form::populate_select_element_from_object($permissionLevels, 'permissionScopeID', 'permissionScopeTitle', '', '', 'Select Permission Level/scope'); ?>
        </select>
        </div>
</div>