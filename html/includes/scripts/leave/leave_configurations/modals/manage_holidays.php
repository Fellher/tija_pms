<div id="holidayForm" class="holidayForm">
   <div class="row">
      <div class="col-md-12">
         <input type="hidden" id="holidayId" name="holidayId" value="" />
      </div>
      <?php $countryList = Data::countries([], false, $DBConn); ?>
      <div class="form-group col-md-6 my-2">
         <label for="holidayName" class="text-primary"> Holiday Name</label>
         <input type="text" id="holidayName" name="holidayName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Holiday Name" value="" required>
      </div>
      
      <div class="form-group  col-md-6 my-2">
         <label for="holidayDate" class="text-primary"> Holiday Date</label>
         <input type="text" id="holidayDate" name="holidayDate" class="form-control form-control-sm form-control-plaintext bg-light-blue date " placeholder="YYYY-MM-DD" value="" required>
      </div>
      <div class="form-group my-2 col-md-6">
         <label for="countryID" class="text-primary"> Holiday Country</label>
         <select id="countryID" name="countryID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" required>
            <option value="all" >All</option>
            <?= Form::populate_select_element_from_object($countryList, 'countryID', 'countryName',25, '', 'Select Country') ?>
         </select>
      </div>
      <div class="form-group col-md-6 my-2">
         <label for="holidayType" class="text-primary"> Holiday Type</label>
         <select id="holidayType" name="holidayType" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" required>
            <option value="" >Select Value</option>
            <option value="half_day" >Half day</option>
            <option value="full_day" >full day</option>             
         </select>
      </div>
     
      <div class="form-check form-check-lg d-flex align-items-center mx-3 col-md-6">
         <input class="form-check-input" type="checkbox"  name="repeatsAnnually" id="repeatsAnnually"  value="Y">
         <label class="form-check-label" for="repeatsAnnually">  Holiday Repeats Annually on the same date  </label>
      </div>
   </div>
</div>