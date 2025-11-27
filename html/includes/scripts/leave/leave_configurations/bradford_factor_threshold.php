<div class="card custom-card my-3 shadow-lg">
   <div class="card-header justify-content-between">
      <h4 class="card-title "> Bradford Factor Threshold</h4>
      <div class="card-options">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#bradford_factor_threshold">Add Bradford Factor Threshold</button>        
      </div>
   </div>
   <?php
   $bradfordFactorThreshold = Leave::bradford_threshold([], false, $DBConn); ;?>

   <div class="card-body">
      <div class="table-responsive">
         <table id="bradford_factor_threshold_table" class="table table-bordered table-striped table-vcenter js-dataTable-full table-sm" style="width: 100%;">
            <thead>
               <tr>
                  <th class="text-center">#</th>
                  <th> Name</th>
                  <th>Threshold</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
               <?php
               if($bradfordFactorThreshold) {
                  foreach ($bradfordFactorThreshold as $key  => $threshold) {?>
                  <tr>
                     <td class="text-center"><?php echo $key + 1; ?></td>
                     <td><?php echo $threshold->bradfordFactorName; ?></td>
                     <td><?php echo $threshold->bradfordFactorValue; ?></td>
                     <td class="text-center">
                        <button 
                        type="button" 
                        class="btn btn-sm btn-primary editBradfordFactorBtn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#bradford_factor_threshold" 
                        data-bradford-factor-id="<?php echo $threshold->bradfordFactorID; ?>" 
                        data-bradford-factor-name="<?php echo $threshold->bradfordFactorName; ?>" 
                        data-bradford-factor-value="<?php echo $threshold->bradfordFactorValue; ?>" >
                        <i class="fas fa-edit"></i>
                        Edit
                        </button>
                     </td>
                  </tr>
                  <?php
                  }
               } else {
                  ?>
                  <tr>
                     <td colspan="4" class="text-center">No Bradford Factor Thresholds found</td>
                  </tr>
                  <?php
               } ?>
            </tbody>
         </table>
      </div>
     
   </div>
</div>
 
<!-- Add Holidays Modal -->
 <?php   
  
   echo Utility::form_modal_header('bradford_factor_threshold', 'leave/bradford_factor_threshold.php', 'Manage Working Weekend', array("modal-dialog-centered", "modal-lg"), $base, true );
   include 'includes/scripts/leave/leave_configurations/modals/bradford_factor_threshold.php';
   echo Utility::form_modal_footer("Save ", "bradford_factor_threshold_details", 'btn btn-primary btn-sm'); ?>
   
<!-- Edit Holidays Modal -->
   

<script>
  
      document.addEventListener('DOMContentLoaded', function() {
         var editButtons = document.querySelectorAll('.editBradfordFactorBtn');
         editButtons.forEach(function(button) {
            console.log(button);
            button.addEventListener('click', function() {

               // check form exists
               form = document.querySelector('#bradford_threshold_form');
               if(!form) return;
            

               // Get all data attributes from the button
               const data = this.dataset;
               console.log(data);

                // Map form fields to their corresponding data attributes
               const fieldMappings = {
                  'bradfordFactorID': 'bradfordFactorId',
                  'bradfordFactorName': 'bradfordFactorName',
                  'bradfordFactorValue': 'bradfordFactorValue'
               };

               // Loop through the field mappings and set the values
               for (const [field, dataField] of Object.entries(fieldMappings)) {
                  const input = form.querySelector(`input[name="${field}"]`) || form.querySelector(`select[name="${field}"]`);
                  if (input) {
                     input.value = data[dataField];
                     // set the input to readonly
                     // input.setAttribute('readonly', true);
                     // input.setAttribute('disabled', true);
                     input.classList.add('bg-light-orange');
                     input.classList.add('px-2');
                     console.log(field);
                     console.log(input.value);
                     console.log(data[dataField]);
                  }
               }

               // var bradfordFactorID = this.getAttribute('data-bradford-factor-id');
               // var bradfordFactorName = this.getAttribute('data-bradford-factor-name');
               // var bradfordFactorValue = this.getAttribute('data-bradford-factor-value');

               // document.getElementById('bradfordFactorID').value = bradfordFactorID;
               // document.getElementById('bradfordFactorName').value = bradfordFactorName;
               // document.getElementById('bradfordFactorValue').value = bradfordFactorValue;
            });
         });
      });
   


//    $(document).ready(function() {
//       $('#bradford_factor_threshold_table').DataTable({
//          "order": [[ 0, "asc" ]],
//          "columnDefs": [
//             { "orderable": false, "targets": [3] }
//          ]
//       });
//    });
  
//    $(document).on('click', '#bradford_factor_threshold_details', function() {
//       var bradfordFactorID = $('#bradfordFactorID').val();
//       var bradfordFactorName = $('#bradfordFactorName').val();
//       var bradfordFactorValue = $('#bradfordFactorValue').val();

//       if (bradfordFactorName === '' || bradfordFactorValue === '') {
//          alert('Please fill in all fields');
//          return false;
//       }

//       $.ajax({
//          url: 'leave/bradford_factor_threshold.php',
//          type: 'POST',
//          data: {
//             bradfordFactorID: bradfordFactorID,
//             bradfordFactorName: bradfordFactorName,
//             bradfordFactorValue: bradfordFactorValue
//          },
//          success: function(response) {
//             // Handle the response from the server
//             console.log(response);
//             location.reload();
//          },
//          error: function(xhr, status, error) {
//             console.error(error);
//          }
//       });
//    });
// });
</script>
