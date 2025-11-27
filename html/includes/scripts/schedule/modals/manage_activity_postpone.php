<div class="manage_activity_postpone_form" id="postponeActivityForm">
   <div class="row">   
      <input type="hidden" name="activityID" id="activityID" value="" />
      <input type="hidden" name="activityName" id="activityName" value="" />
      <div class="col-md-6 col-12">
         <div class="form-group  " >
            <label for="activityName" class="text-primary"> Activity Start Date & Time</label>          
            <input type="text" name="activityDate" class="form-control form-control" id="date" placeholder="Choose date with time">     
         </div>
         <div class="form-group mb-0">
            <input type="text"  name="activityStartTime" class="form-control" id="inlinetime" placeholder="Choose time">
         </div>
      </div>
      <div class="col-md-6 col-12 d-none">
         <div class="form-group " >
            <label for="activityName" class="text-primary"> Activity End Date & Time</label>          
            <input type="text" name="activityDurationEndDate" class="form-control form-control date" id="" placeholder="Choose date with time">     
         </div>
         <div class="form-group mb-0">
            <input type="text"  name="activityDurationEndTime" class="form-control" id="inlinetime" placeholder="Choose time">
         </div>
      </div>
      <div class="error"></div>
      
   </div>
</div>
<script>
   document.addEventListener('DOMContentLoaded', function() {
      let activityDate = document.querySelector('[name="activityDate"]');
         let activityStartTime = document.querySelector('[name="activityStartTime"]');
         let activityDurationEndDate = document.querySelector('[name="activityDurationEndDate"]');
         let activityDurationEndTime = document.querySelector('[name="activityDurationEndTime"]');



      function getActivityDates() {
         let activityDate = document.querySelector('[name="activityDate"]');
         let activityStartTime = document.querySelector('[name="activityStartTime"]');
         let activityDurationEndDate = document.querySelector('[name="activityDurationEndDate"]');
         let activityDurationEndTime = document.querySelector('[name="activityDurationEndTime"]');
         const activityStartDate = `${activityDate.value} ${activityStartTime.value}`;
         const activityEndDate = `${activityDurationEndDate.value} ${activityDurationEndTime.value}`;
         const startDate = new Date(activityStartDate).getTime();
         const endDate = new Date(activityEndDate).getTime();
         if (startDate > endDate) {
            console.error('Error: Start date is greater than end date');
            // activityEndDate.value = activityEndDate;
            document.querySelector('.error').innerHTML = '<div class="alert alert-danger">Error: Start date is greater than end date</div>';
         } else {
            console.log('Start date:', activityStartDate);
            console.log('End date:', activityEndDate);
            document.querySelector('.error').innerHTML = '';
         }
         
      }

      

      
      activityDate.addEventListener('change', function() {
         console.log('Activity Date changed to:', this.value);
         getActivityDates();
         // console.log('Activity Start Date:', activityStartDate);
         // console.log('Activity End Date:', activityEndDate);
         
      });

      activityStartTime.addEventListener('change', function() {
         console.log('Activity Start Time changed to:', this.value);
         getActivityDates();
      });

      activityDurationEndDate.addEventListener('change', function() {
         console.log('Activity Duration End Date changed to:', this.value);
         getActivityDates();
      });

      activityDurationEndTime.addEventListener('change', function() {
         console.log('Activity Duration End Time changed to:', this.value);
         getActivityDates();
      });
     
      // console.log('Activity Start Date:', activityStartDate);
      // console.log('Activity End Date:', activityEndDate);

      });

</script>