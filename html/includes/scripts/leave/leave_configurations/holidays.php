<div class="card custom-card my-3 shadow-lg">
   <div class="card-header justify-content-between">
      <h4 class="card-title "> Holidays</h4>
      <div class="card-options">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#manageHolidays">Add Holidays</button>        
      </div>
   </div>
   <?php
   $holidaysList =Data::holidays([], false, $DBConn);
   $holidaysListJson = json_encode($holidaysList);
   // var_dump($holidaysList);

   ?>
   <div class="card-body">
      <div class="table-responsive">
         <table id="holidays_table" class="table table-bordered table-striped table-vcenter js-dataTable-full" style="width: 100%;">
            <thead>
               <tr>
                  <th class="text-center">#</th>
                  <th>Holiday Name</th>
                  <th>Holiday Date</th>
                  <th>Holiday Type</th>
                  <th>Country</th>
                  <th>Repeats Annually</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
               <?php 
               if($holidaysList) {
                  foreach ($holidaysList as $key  => $holiday) {
                     $holidayDate = date_format(date_create($holiday->holidayDate), 'D\, d M Y');?>
                     <tr>
                        <td class="text-center"><?php echo $key + 1; ?></td>
                        <td><?php echo $holiday->holidayName; ?></td>
                        <td><?php echo $holidayDate; ?></td>
                        <td><?php echo $holiday->holidayType; ?></td>
                        <td><?php echo $holiday->countryName; ?></td>
                        <td><?php echo ($holiday->repeatsAnnually == 'Y') ? 'Yes' : 'No'; ?></td>
                        <td class="text-center">
                           <button 
                           type="button" 
                           class="btn btn-sm btn-primary editHolidayBtn" 
                           data-bs-toggle="modal" 
                           data-bs-target="#manageHolidays" 
                           data-holiday-id="<?php echo $holiday->holidayID; ?>" 
                           data-holiday-name="<?php echo $holiday->holidayName; ?>" 
                           data-holiday-date="<?php echo $holiday->holidayDate; ?>" 
                           data-holiday-type="<?php echo $holiday->holidayType; ?>"
                            data-country-id="<?php echo $holiday->countryID; ?>" 
                            data-repeats-annually="<?php echo $holiday->repeatsAnnually; ?>"
                            >
                            <i class="fas fa-edit"></i>
                            Edit
                           </button>
                           <button type="button" class="btn btn-sm btn-danger delete-holiday" data-holiday-id="<?php echo $holiday->holidayID; ?>">Delete</button>
                        </td>
                     </tr>
                     <?php
                     # code...
                  }

               } else {
                  echo '<tr><td colspan="7" class="text-center">No Holidays Found</td></tr>';
               }?>
              
            </tbody>
         </table>
      </div>
   </div>
</div>
<!-- Add Holidays Modal -->
 <?php   
   $holidayName = '';
   $holidayDate = '';
   $holidayId = '';
   $holidayAction = 'add';
   $holidayButtonText = 'Add Holidays';
   $holidayModalTitle = 'Add Holidays';
   echo Utility::form_modal_header('manageHolidays', 'leave/manage_holidays.php', 'Manage Holidays', array("modal-dialog-centered", "modal-lg"), $base, true );
   include 'includes/scripts/leave/leave_configurations/modals/manage_holidays.php';
   echo Utility::form_modal_footer("Save Work Type", "manage_rate_type_details", 'btn btn-primary btn-sm'); ?>   
<!-- Edit Holidays Modal -->


<script>
   document.addEventListener('DOMContentLoaded', function() {
      const editHolidayBtns = document.querySelectorAll('.editHolidayBtn');

      editHolidayBtns.forEach(function(button) {
         button.addEventListener('click', function() {
            // check that form exists
            const holidayForm = document.getElementById('holidayForm');
            if (!holidayForm) {
               console.error('Holiday form not found!');
               return;
            }

            // get all data attributes
            const data = this.dataset;
            console.log(data);
         
            // map form fields to their corresponding data attributes
            const fieldMappings = {
               
               'holidayName': 'holidayName',
               'holidayDate': 'holidayDate',
               'holidayType': 'holidayType',
               'countryID': 'countryId',
               'repeatsAnnually': 'repeatsAnnually',
               'holidayID': 'holidayId'
            };

            // fill regular inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               const input = holidayForm.querySelector(`[name="${fieldName}"]`);
               if (input) {
                  input.value = data[dataAttribute] || '';
               }
            }

            // handle checkbox
            const checkbox = holidayForm.querySelector(`[name="repeatsAnnually"]`);
            if (checkbox) {
               checkbox.checked = data['repeatsAnnually'] === 'Y';
            }
            // handle select elements
            const selects = ['holidayType', 'countryID'];
            selects.forEach(selectName => {
               const select = holidayForm.querySelector(`[name="${selectName}"]`);
               if (select && data[fieldMappings[selectName]]) {
                  select.value = data[fieldMappings[selectName]];
               }
            });



          
         });
      });
   });
</script>


   