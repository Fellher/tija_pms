<div class="form-group">
   <div class="row">
      <div class="col-12">
         <div class="card card-body">
            <div class="row">
               <div class="col-12">
                  <h4 class="text-center">Manage Organisation Admin</h4>
                  <p class="text-center">You can add or remove organisation admin from here.</p>
               </div>
            </div>
            <div class="row">
               <div class="col-12 mb-3">
                  <label for="org_id">Organisation</label>
                  <select name="orgDataID" id="orgDataID" class="form-select form-select-sm rounded-pill border-0 shadow-sm px-4 text-primary" required>
                     <option value="">Select Organisation</option>
                     <?php 
                     foreach ($organisations as $org) : ?>
                        <option value="<?php echo $org->orgDataID; ?>"><?php echo $org->orgName; ?></option>
                        <?php 
                     endforeach; ?>
                  </select>
               </div>
               <div class="col-12">
                  <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                     <input type="radio" class="btn-check" name="adminSelect" id="currentUsers" autocomplete="off" value="currentUsers" checked>
                     <label class="btn btn-outline-primary" for="currentUsers">Select From Current Users</label>
                     <input type="radio" class="btn-check" name="adminSelect" id="newUser" autocomplete="off" value="newUser"> 
                     <label class="btn btn-outline-primary" for="newUser">Create New User</label>
                  </div>
               </div>

               <div class="card card-body bg-light-blue shadow-md mb-3  my-2 " >
                  <label for="user_id">Select User</label>
                  <select name="userID" id="userID" class="form-select form-select-sm rounded-pill border-0 shadow-sm px-4 text-primary" required>
                     <option value="">Select User</option>
                     <?php foreach ($users as $user) : 
                        if($user->adminID !== null) {
                           continue;
                        }
                        ?>
                           <option value="<?php echo $user->ID; ?>"><?= $user->userFullNameEmail ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class=" new_user_add d-none">
                  <div class="card card-body bg-light">
                     <h4>Add New Admin User</h4>
                     <div class="row">
                        <div class="col-md-4 mb-3">
                              <label for="FirstName">First Name</label>
                              <input type="text" name="FirstName" id="FirstName" class="form-control form-control-sm rounded-pill border-0 shadow-sm px-4 text-primary" placeholder="" required>
                        </div>
                        <div class="col-md-4 mb-3">
                              <label for="Surname">Last Name</label>
                              <input type="text" name="Surname" id="Surname" class="form-control form-control-sm rounded-pill border-0 shadow-sm px-4 text-primary" placeholder="" required>
                        </div>
                        <div class="col-md-4 mb-3">
                              <label for="OtherNames">Other Names</label>
                              <input type="text" name="OtherNames" id="OtherNames" class="form-control form-control-sm rounded-pill border-0 shadow-sm px-4 text-primary" placeholder="" required>
                        </div>
                        <div class="col-md-12 mb-3">
                           <label for="Email">Email</label>
                           <input type="email" name="Email" id="Email" class="form-control form-control-sm rounded-pill border-0 shadow-sm px-4 text-primary" placeholder="" required>
                        </div>
                     </div>                        
                  </div>
               </div>
               <script>
                  const newUserButton = document.getElementById('newUser');
                  const selectFromListButton = document.getElementById('currentUsers');
                  const newUserAdd = document.querySelector('.new_user_add');
                  const selectFromList = document.querySelector('.select_from_list');

                  newUserButton.addEventListener('click', () => {
                     newUserAdd.classList.remove('d-none');
                     selectFromList.classList.add('d-none');
                  });

                  selectFromListButton.addEventListener('click', () => {
                     selectFromList.classList.remove('d-none');
                     newUserAdd.classList.add('d-none');
                  });
               </script>                    
               <div class="col-12 mb-3">
                  <label for="admin_role">Admin Role/Type</label>
                  <select name="adminTypeID" id="adminTypeID" class="form-select form-select-sm rounded-pill border-0 shadow-sm px-4 text-primary" required>
                     <option value="">Select Role</option>
                     <?php
                     if($adminTypes){
                        foreach ($adminTypes as $role) : ?>
                           <option value="<?php echo $role->adminTypeID; ?>"><?php echo $role->adminTypeName; ?></option>
                        <?php 
                        endforeach; 
                     } ?>
                  </select>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>