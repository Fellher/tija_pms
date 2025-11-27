<div class="card custom-card my-3 shadow-lg">
   <div class="card-header justify-content-between">
      <h4 class="card-title "> Leave Calendar Configurations</h4>
      <div class="card-options">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#bradford_factor_threshold">Add Bradford Factor Threshold</button>        
      </div>
   </div>
   <?php
   $leavePeriod = Leave::leave_Periods([], false, $DBConn) ; ?>
   <div class="card-body">
      <form action="<?= "{$base}php/scripts/leave/leave_calendar_config.php" ?>" method="POST" id="leave_calendar_form">
         <input type="hidden" name="leaveCalendarConfigID" id="leaveCalendarID" value="">
         <div class="row">
            <div class="col-lg-6">
               <div class="form-group">
                  <label for="leave_period">Show Leave for Period</label>
                  <select class="form-control form-control-sm from-control-plaintext border-bottom bg-light" id="leave_period" name="leave_period">
                     <!-- <option value="">Select Leave Period</option> -->
                    <?= Form::populate_select_element_from_object($leavePeriod, 'leavePeriodID', 'leavePeriodName','', '', 'Select Leave Period') ?>
                  </select>
               </div>
            </div>
            <div class="col-lg-6">
               <div class="form-group">
                  <label for="leave_period">Show Leave for employees of        </label>
                  <select class="form-control form-control-sm from-control-plaintext border-bottom bg-light" id="leaveCalendarScope" name="leaveCalendarScope">
                     <option value="">Select Leave Calendar Scope</option>
                     <option value="all">All Employees</option>
                     <option value="department">Department</option>
                     <option value="location">Location</option>
                     <option value="employee">Employee</option>
                     <option value="team">Team</option>
                     <option value="unit">Unit</option>
                     <option value="Entity">Entity</option>
                     <option value="organisation">Organisation</option>                   
                  </select>
               </div>
            </div>

            <div class="col-lg-6">
               <div class="form-group">
                  <label for="leave_period">Leave Calendar Type</label>
                  <select class="form-control form-control-sm from-control-plaintext border-bottom bg-light" id="leaveCalendarType" name="leaveCalendarType">
                     <option value="">Select Leave Calendar Type</option>
                     <option value="calendar">Calendar</option>
                     <option value="list">List</option>
                  </select>
               </div>
            </div>

            <div class="col-lg-6">
               <div class="form-group">
                  <label for="leave_period">Leave Calendar View</label>
                  <select class="form-control form-control-sm from-control-plaintext border-bottom bg-light" id="leaveCalendarView" name="leaveCalendarView">
                     <option value="">Select Leave Calendar View</option>
                     <option value="month">Month</option>
                     <option value="week">Week</option>
                     <option value="day">Day</option>
                  </select>
               </div>
            </div>
            <?php $leaveStatus = Leave::leave_status([], false, $DBConn); ?>

            <div class="col-lg-6">
               <div class="form-group">
                  <label for="leaveStatusID">Hide Leave with status</label>
                  <select class="form-control form-control-sm from-control-plaintext border-bottom bg-light choices-multiple-default" data-trigger id="leaveStatusID" name="leaveStatusID " multiple>
                    <?= Form::populate_select_element_from_object($leaveStatus, 'leaveStatusID', 'leaveStatusName','', '', 'Select Leave Status') ?>
                  </select>
               </div>
            </div>         
         </div>
         <div class="col-lg-12 text-end mt-3">
            <button type="submit" class="btn btn-success  rounded-pill btn-wave px-4 py-0" id="saveLeaveCalendarConfig">Save Leave Calendar Configurations</button>
          </div>
      </form>
   </div>


</div>

