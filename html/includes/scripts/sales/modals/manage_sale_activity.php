<!--
   Activity Wizard - Multi-Step Form
   Dependencies: Flatpickr (date/time), Tom Select (multi-select), Bootstrap 5
-->
<div class="activity-wizard-container">
   <!-- Hidden Fields -->
   <input type="hidden" name="salesCaseID" id="salesCaseID" value="<?= $salesCaseDetails->salesCaseID; ?>">
   <input type="hidden" name="orgDataID" value="<?php echo $orgDataID; ?>">
   <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">
   <input type="hidden" name="salesPersonID" value="<?php echo $userID; ?>">
   <input type="hidden" name="activityID" id="activityID" value="">
   <input type="hidden" name="activitySegment" value="sales">


   <!-- Wizard Progress Steps -->
   <div class="wizard-progress mb-4">
      <div class="wizard-steps d-flex justify-content-between">
         <div class="wizard-step active" data-step="1">
            <div class="step-circle">
               <i class="ri-information-line"></i>
               <span class="step-number">1</span>
            </div>
            <div class="step-label">Activity Details</div>
         </div>
         <div class="wizard-step-connector"></div>
         <div class="wizard-step" data-step="2">
            <div class="step-circle">
               <i class="ri-calendar-line"></i>
               <span class="step-number">2</span>
            </div>
            <div class="step-label">Schedule & Timeline</div>
         </div>
         <div class="wizard-step-connector"></div>
         <div class="wizard-step" data-step="3">
            <div class="step-circle">
               <i class="ri-list-check-2"></i>
               <span class="step-number">3</span>
            </div>
            <div class="step-label">Additional Details</div>
         </div>
         <div class="wizard-step-connector"></div>
         <div class="wizard-step" data-step="4">
            <div class="step-circle">
               <i class="ri-money-dollar-circle-line"></i>
               <span class="step-number">4</span>
            </div>
            <div class="step-label">Outcome & Cost</div>
         </div>
         <div class="wizard-step-connector"></div>
         <div class="wizard-step" data-step="5">
            <div class="step-circle">
               <i class="ri-file-list-3-line"></i>
               <span class="step-number">5</span>
            </div>
            <div class="step-label">Review Summary</div>
         </div>
      </div>
   </div>

   <!-- Wizard Content -->
   <div class="wizard-content">
      <!-- Step 1: Activity Details -->
      <div class="wizard-pane active" id="wizard-step-1">
         <h6 class="mb-3 fw-semibold text-primary">
            <i class="ri-information-line me-2"></i>Basic Activity Information
         </h6>

         <div class="row g-3">
            <div class="col-12">
               <label for="activityName" class="form-label">Activity Name <span class="text-danger">*</span></label>
               <input type="text" id="activityName" name="activityName" class="form-control" placeholder="e.g., Client Meeting, Follow-up Call" required>
            </div>

            <div class="col-md-6">
               <label for="activityCategoryID" class="form-label">Activity Category <span class="text-danger">*</span></label>
               <select id="activityCategoryID" name="activityCategoryID" class="form-select" required>
                  <option value="">Select Category</option>
                  <?php if($activityCategories): ?>
                     <?php foreach ($activityCategories as $category): ?>
                        <option value="<?= $category->activityCategoryID ?>"><?= htmlspecialchars($category->activityCategoryName) ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
      </select>
               <small class="text-muted">Select category first to filter types</small>
   </div>

            <div class="col-md-6">
               <label for="activityTypeID" class="form-label">Activity Type <span class="text-danger">*</span></label>
               <select id="activityTypeID" name="activityTypeID" class="form-select" required>
                  <option value="">Select Category First</option>
                  <?php if($activityTypes): ?>
                     <?php foreach ($activityTypes as $activityType): ?>
                        <option value="<?= $activityType->activityTypeID ?>"
                                data-category-id="<?= $activityType->activityCategoryID ?? '' ?>"
                                style="display: none;">
                           <?= htmlspecialchars($activityType->activityTypeName) ?>
                        </option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
               <small class="text-muted">
                  <span id="typeFilterInfo" class="text-primary" style="display: none;">
                     <i class="ri-filter-line"></i> Showing types for selected category
                  </span>
                  <span id="typeSelectInfo">Types will appear after selecting category</span>
               </small>
            </div>

            <div class="col-12">
               <label for="clientID" class="form-label">Client</label>
               <select name="clientID" id="clientID" class="form-select">
                  <option value="<?= $salesCaseDetails->clientID ?>" selected><?= htmlspecialchars($salesCaseDetails->clientName) ?></option>
                  <?php if($clients): ?>
                     <?php foreach ($clients as $client): ?>
                        <?php if($client->clientID != $salesCaseDetails->clientID): ?>
                           <option value="<?= $client->clientID ?>"><?= htmlspecialchars($client->clientName) ?></option>
                        <?php endif; ?>
                     <?php endforeach; ?>
                  <?php endif; ?>
      </select>
   </div>

            <div class="col-md-6">
               <label for="activityPriority" class="form-label">Priority</label>
               <select id="activityPriority" name="activityPriority" class="form-select">
                  <option value="Low">Low</option>
                  <option value="Medium" selected>Medium</option>
                  <option value="High">High</option>
                  <option value="Urgent">Urgent</option>
               </select>
   </div>

      <div class="col-md-6">
               <label for="activityStatus" class="form-label">Status</label>
               <select id="activityStatus" name="activityStatus" class="form-select">
                  <option value="notStarted" selected>Not Started</option>
                  <option value="inProgress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="needsAttention">Needs Attention</option>
                  <option value="stalled">Stalled</option>
               </select>
         </div>

            <div class="col-12">
               <label for="activityDescription" class="form-label">Description</label>
               <textarea id="activityDescription" name="activityDescription" class="form-control" rows="3" placeholder="Describe the purpose and details of this activity..."></textarea>
      </div>

            <!-- Context Display -->
            <div class="col-12">
               <div class="card bg-light border-0">
                  <div class="card-body py-2">
                     <div class="row g-2 small">
      <div class="col-md-6">
                           <strong class="text-muted">Sales Case:</strong>
                           <span class="ms-1"><?= htmlspecialchars($salesCaseDetails->salesCaseName) ?></span>
                        </div>
                        <div class="col-md-6">
                           <strong class="text-muted">Client:</strong>
                           <span class="ms-1"><?= htmlspecialchars($salesCaseDetails->clientName) ?></span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Step 2: Schedule & Timeline -->
      <div class="wizard-pane" id="wizard-step-2">
         <h6 class="mb-3 fw-semibold text-primary">
            <i class="ri-calendar-line me-2"></i>Schedule & Timeline Settings
         </h6>

         <div class="row g-3">
            <div class="col-md-6">
               <label for="activityDate" class="form-label">Activity Date <span class="text-danger">*</span></label>
               <input type="text" id="activityDate" name="activityDate" class="form-control" placeholder="Select Activity Date" required>
               <small class="text-muted">When will this activity take place?</small>
            </div>

            <div class="col-md-6">
               <label for="durationType" class="form-label">Duration Type</label>
               <select id="durationType" name="durationType" class="form-select">
                  <option value="oneOff" selected>One-time Activity</option>
                  <option value="duration">Duration-based</option>
                  <option value="recurring">Recurring Activity</option>
               </select>
            </div>

            <!-- One-off/Duration Time Fields -->
            <div id="timeFields">
               <div class="row g-3">
                  <div class="col-md-6">
                     <label for="activityStartTime" class="form-label">Start Time</label>
                     <input type="text" id="activityStartTime" name="activityStartTime" class="form-control" placeholder="Select start time">
                     <small class="text-muted">24-hour format (e.g., 14:00)</small>
                  </div>
                  <div class="col-md-6">
                     <label for="activityEndTime" class="form-label">End Time</label>
                     <input type="text" id="activityEndTime" name="activityEndTime" class="form-control" placeholder="Select end time">
                     <small class="text-muted">Activity duration will be calculated</small>
            <div id="endTimeError" class="text-danger small mt-1" style="display: none;">
                        <i class="ri-error-warning-line me-1"></i>End time must be after start time
                     </div>
                  </div>
                  <div class="col-12">
                     <div id="durationDisplay" class="alert alert-info py-2 small" style="display: none;">
                        <i class="ri-time-line me-1"></i>
                        <strong>Duration:</strong> <span id="calculatedDuration">-</span>
            </div>
         </div>
      </div>
   </div>

            <!-- Duration-based Fields -->
            <div id="durationFields" style="display: none;">
               <div class="row g-3">
                  <div class="col-md-6">
                     <label for="activityDurationEndDate" class="form-label">End Date</label>
                     <input type="text" id="activityDurationEndDate" name="activityDurationEndDate" class="form-control" placeholder="Select end date">
                  </div>
                  <div class="col-md-6">
                     <label for="activityDurationEndTime" class="form-label">End Time</label>
                     <input type="text" id="activityDurationEndTime" name="activityDurationEndTime" class="form-control" placeholder="Select end time">
                  </div>
               </div>
            </div>

            <!-- Recurring Fields -->
            <div id="recurringFields" style="display: none;">
               <div class="row g-3">
                  <div class="col-md-6">
                     <label for="recurrenceType" class="form-label">Recurrence Pattern</label>
                     <select id="recurrenceType" name="recurrenceType" class="form-select">
                        <option value="">Select Pattern</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="custom">Custom</option>
                     </select>
                  </div>
                  <div class="col-md-6">
                     <label for="recurringInterval" class="form-label">Repeat Every</label>
                     <div class="input-group">
                        <input type="number" id="recurringInterval" name="recurringInterval" class="form-control" value="1" min="1">
                        <select id="recurringIntervalUnit" name="recurringIntervalUnit" class="form-select">
                           <option value="day">Day(s)</option>
                           <option value="week">Week(s)</option>
                           <option value="month">Month(s)</option>
                        </select>
                     </div>
                  </div>
                  <div class="col-12">
                     <label for="recurrenceEndType" class="form-label">End Recurrence</label>
                     <select id="recurrenceEndType" name="recurrenceEndType" class="form-select">
                        <option value="never">Never</option>
                        <option value="after">After number of occurrences</option>
                        <option value="on">On specific date</option>
                     </select>
                  </div>
                  <div class="col-12" id="recurrenceEndFields" style="display: none;">
                     <input type="number" id="numberOfOccurrencesToEnd" name="numberOfOccurrencesToEnd" class="form-control" placeholder="Number of occurrences" style="display: none;">
                     <input type="text" id="recurringEndDate" name="recurringEndDate" class="form-control" placeholder="Select end date" style="display: none;">
                  </div>
               </div>
            </div>

            <!-- All-day Event Option -->
            <div class="col-12">
               <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="allDayEvent" name="allDayEvent">
                  <label class="form-check-label" for="allDayEvent">
                     All-day event (no specific time)
                  </label>
               </div>
            </div>
         </div>
   </div>

      <!-- Step 3: Additional Details -->
      <div class="wizard-pane" id="wizard-step-3">
         <h6 class="mb-3 fw-semibold text-primary">
            <i class="ri-list-check-2 me-2"></i>Additional Information
         </h6>

         <div class="row g-3">
            <div class="col-12">
               <label for="activityOwnerID" class="form-label">Activity Owner <span class="text-danger">*</span></label>
               <select class="form-select" name="activityOwnerID" id="activityOwnerID" required>
                  <?php echo Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', $salesCaseDetails->salesPersonID, '', 'Select Owner') ?>
               </select>
               <small class="text-muted">Person responsible for this activity</small>
   </div>

            <div class="col-12">
               <label for="activityParticipants" class="form-label">Participants/Attendees</label>
               <select class="form-select" name="activityParticipants[]" id="activityParticipants" multiple>
                  <?php if($allEmployees): ?>
                     <?php foreach ($allEmployees as $employee): ?>
                        <option value="<?= $employee->ID ?>"><?= htmlspecialchars($employee->FirstName . ' ' . $employee->Surname) ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
               <small class="text-muted">Search and select multiple participants</small>
   </div>

            <div class="col-md-6">
               <label for="activityLocation" class="form-label">Location</label>
               <input type="text" id="activityLocation" name="activityLocation" class="form-control" placeholder="e.g., Client Office, Zoom, Phone">
               <small class="text-muted">Physical or virtual location</small>
            </div>

            <div class="col-md-6">
               <label for="meetingLink" class="form-label">Meeting Link</label>
               <input type="url" id="meetingLink" name="meetingLink" class="form-control" placeholder="https://zoom.us/j/...">
               <small class="text-muted">Zoom, Teams, etc.</small>
            </div>

            <div class="col-12">
               <label for="activityNotes" class="form-label">Notes & Agenda</label>
               <textarea id="activityNotes" name="activityNotes" class="form-control" rows="4" placeholder="Meeting agenda, discussion points, preparation notes..."></textarea>
            </div>

            <div class="col-12">
               <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="sendReminder" name="sendReminder">
                  <label class="form-check-label" for="sendReminder">
                     Send reminder notification before activity
                  </label>
               </div>
            </div>

            <div class="col-md-6" id="reminderTimeField" style="display: none;">
               <label for="reminderTime" class="form-label">Reminder Time</label>
               <select id="reminderTime" name="reminderTime" class="form-select">
                  <option value="15">15 minutes before</option>
                  <option value="30">30 minutes before</option>
                  <option value="60">1 hour before</option>
                  <option value="1440">1 day before</option>
      </select>
            </div>
   </div>
</div>

      <!-- Step 4: Outcome & Cost -->
      <div class="wizard-pane" id="wizard-step-4">
         <h6 class="mb-3 fw-semibold text-primary">
            <i class="ri-checkbox-circle-line me-2"></i>Outcomes & Expenses
         </h6>

         <div class="row g-3">
            <div class="col-12">
               <div class="alert alert-info py-2 small">
                  <i class="ri-information-line me-1"></i>
                  Complete these fields after the activity or as it progresses to track results and costs.
               </div>
            </div>

            <div class="col-12">
               <label for="activityOutcome" class="form-label">Activity Outcome</label>
               <select id="activityOutcome" name="activityOutcome" class="form-select">
                  <option value="">Not completed yet</option>
                  <option value="Successful">Successful - Objectives Met</option>
                  <option value="Partial">Partially Successful</option>
                  <option value="Unsuccessful">Unsuccessful</option>
                  <option value="Rescheduled">Rescheduled</option>
                  <option value="Cancelled">Cancelled</option>
               </select>
            </div>

            <div class="col-12">
               <label for="activityResult" class="form-label">Results & Key Takeaways</label>
               <textarea id="activityResult" name="activityResult" class="form-control" rows="3" placeholder="What were the main outcomes? Key decisions made? Action items?"></textarea>
            </div>

            <!-- Multiple Expenses Section -->
            <div class="col-12">
               <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label mb-0">Activity Expenses</label>
                  <button type="button" class="btn btn-sm btn-outline-primary" id="addExpenseBtn">
                     <i class="ri-add-line me-1"></i>Add Expense
                  </button>
               </div>

               <div id="expensesContainer" class="expenses-container">
                  <!-- Expense items will be added here dynamically -->
                  <div class="text-muted text-center py-3" id="noExpensesMessage">
                     <i class="ri-money-dollar-circle-line fs-24 d-block mb-2"></i>
                     <small>No expenses added yet. Click "Add Expense" to track costs.</small>
                  </div>
               </div>

               <!-- Total Display -->
               <div class="card border-primary mt-3" id="expenseTotalCard" style="display: none;">
                  <div class="card-body py-2">
                     <div class="row align-items-center">
                        <div class="col-6">
                           <strong class="text-muted">Total Expenses:</strong>
                        </div>
                        <div class="col-6 text-end">
                           <strong class="text-danger fs-18">KES <span id="totalExpenseAmount">0.00</span></strong>
                        </div>
                     </div>
                     <div class="row mt-1 small">
                        <div class="col-12 text-muted">
                           <span id="expenseItemCount">0</span> expense item(s)
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Hidden template for expense row -->
            <template id="expenseRowTemplate">
               <div class="expense-item card mb-2" data-expense-index="">
                  <div class="card-body p-3">
                     <div class="row g-2">
                        <div class="col-md-4">
                           <label class="form-label small mb-1">Category <span class="text-danger">*</span></label>
                           <select class="form-select form-select-sm expense-category" name="expenses[INDEX][category]" required>
                              <option value="">Select Category</option>
                              <option value="Travel" data-icon="ri-taxi-line" data-color="#007bff">🚕 Travel</option>
                              <option value="Meals" data-icon="ri-restaurant-line" data-color="#28a745">🍽️ Meals & Entertainment</option>
                              <option value="Materials" data-icon="ri-file-copy-line" data-color="#6c757d">📋 Materials/Collateral</option>
                              <option value="Accommodation" data-icon="ri-hotel-line" data-color="#17a2b8">🏨 Accommodation</option>
                              <option value="Technology" data-icon="ri-macbook-line" data-color="#6f42c1">💻 Technology/Tools</option>
                              <option value="Communication" data-icon="ri-phone-line" data-color="#fd7e14">📞 Communication</option>
                              <option value="Parking" data-icon="ri-parking-box-line" data-color="#20c997">🅿️ Parking</option>
                              <option value="Fuel" data-icon="ri-gas-station-line" data-color="#ffc107">⛽ Fuel</option>
                              <option value="Gifts" data-icon="ri-gift-line" data-color="#e83e8c">🎁 Client Gifts</option>
                              <option value="Other" data-icon="ri-more-line" data-color="#6c757d">📌 Other</option>
                           </select>
                        </div>
                        <div class="col-md-3">
                           <label class="form-label small mb-1">Amount (KES) <span class="text-danger">*</span></label>
                           <input type="number" class="form-control form-control-sm expense-amount" name="expenses[INDEX][amount]" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                           <label class="form-label small mb-1">Description</label>
                           <input type="text" class="form-control form-control-sm expense-description" name="expenses[INDEX][description]" placeholder="Brief description">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                           <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-expense-btn" title="Remove expense">
                              <i class="ri-delete-bin-line"></i>
                           </button>
                        </div>

                        <!-- Additional Fields (Collapsed) -->
                        <div class="col-12">
                           <button type="button" class="btn btn-sm btn-link p-0 text-muted toggle-expense-details" data-bs-toggle="collapse" data-bs-target="#expenseDetails_INDEX">
                              <i class="ri-arrow-down-s-line"></i> More details
                           </button>
                           <div class="collapse mt-2" id="expenseDetails_INDEX">
                              <div class="row g-2">
                                 <div class="col-md-4">
                                    <label class="form-label small mb-1">Payment Method</label>
                                    <select class="form-select form-select-sm" name="expenses[INDEX][paymentMethod]">
                                       <option value="">Not specified</option>
                                       <option value="Cash">Cash</option>
                                       <option value="Card">Card</option>
                                       <option value="Mpesa">M-Pesa</option>
                                       <option value="Company Card">Company Card</option>
                                       <option value="Bank Transfer">Bank Transfer</option>
                                    </select>
                                 </div>
                                 <div class="col-md-4">
                                    <label class="form-label small mb-1">Receipt Number</label>
                                    <input type="text" class="form-control form-control-sm" name="expenses[INDEX][receiptNumber]" placeholder="Receipt/invoice #">
                                 </div>
                                 <div class="col-md-4">
                                    <label class="form-label small mb-1">Reimbursable</label>
                                    <select class="form-select form-select-sm" name="expenses[INDEX][reimbursable]">
                                       <option value="Y" selected>Yes</option>
                                       <option value="N">No</option>
                                    </select>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </template>

            <div class="col-12">
               <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="requiresFollowUp" name="requiresFollowUp">
                  <label class="form-check-label" for="requiresFollowUp">
                     Requires follow-up activity
                  </label>
               </div>
            </div>

            <div class="col-12" id="followUpFields" style="display: none;">
               <label for="followUpNotes" class="form-label">Follow-up Notes</label>
               <textarea id="followUpNotes" name="followUpNotes" class="form-control" rows="2" placeholder="What follow-up actions are needed?"></textarea>
            </div>

            <!-- Summary Card -->
            <div class="col-12 mt-4">
               <div class="card border-primary">
                  <div class="card-header bg-primary text-white py-2">
                     <h6 class="mb-0"><i class="ri-file-list-line me-2"></i>Activity Summary</h6>
                  </div>
                  <div class="card-body">
                     <div class="row g-2 small">
                        <div class="col-md-6">
                           <strong>Activity:</strong> <span id="summaryName">-</span>
                        </div>
                        <div class="col-md-6">
                           <strong>Type:</strong> <span id="summaryType">-</span>
                        </div>
                        <div class="col-md-6">
                           <strong>Date:</strong> <span id="summaryDate">-</span>
                        </div>
                        <div class="col-md-6">
                           <strong>Time:</strong> <span id="summaryTime">-</span>
                        </div>
                        <div class="col-md-6">
                           <strong>Owner:</strong> <span id="summaryOwner">-</span>
                        </div>
                        <div class="col-md-6">
                           <strong>Priority:</strong> <span id="summaryPriority">-</span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Step 5: Comprehensive Summary -->
      <div class="wizard-pane" id="wizard-step-5">
         <h6 class="mb-3 fw-semibold text-primary">
            <i class="ri-file-list-3-line me-2"></i>Complete Activity Summary
         </h6>

         <div class="alert alert-success mb-4">
            <div class="d-flex align-items-center">
               <i class="ri-checkbox-circle-line fs-24 me-3"></i>
               <div>
                  <strong>Review Your Activity</strong>
                  <p class="mb-0 small">Please review all the details before saving. You can go back to any step to make changes.</p>
               </div>
            </div>
         </div>

         <!-- Section 1: Basic Information -->
         <div class="summary-section mb-4">
            <div class="summary-header">
               <h6 class="fw-semibold mb-3"><i class="ri-information-line me-2 text-primary"></i>Basic Information</h6>
            </div>
            <div class="card border-0 shadow-sm">
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Activity Name</label>
                           <div class="fw-semibold fs-16" id="summaryActivityName">-</div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Category</label>
                           <div class="fw-semibold" id="summaryCategory">-</div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Type</label>
                           <div class="fw-semibold" id="summaryActivityType">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Priority</label>
                           <div id="summaryActivityPriority">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Status</label>
                           <div id="summaryActivityStatus">-</div>
                        </div>
                     </div>
                     <div class="col-12">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Description</label>
                           <div id="summaryDescription" class="text-secondary">-</div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Section 2: Schedule & Timeline -->
         <div class="summary-section mb-4">
            <div class="summary-header">
               <h6 class="fw-semibold mb-3"><i class="ri-calendar-line me-2 text-primary"></i>Schedule & Timeline</h6>
            </div>
            <div class="card border-0 shadow-sm">
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-4">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Activity Date</label>
                           <div class="fw-semibold" id="summaryActivityDate">-</div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Start Time</label>
                           <div class="fw-semibold" id="summaryStartTime">-</div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">End Time</label>
                           <div class="fw-semibold" id="summaryEndTime">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Duration Type</label>
                           <div id="summaryDurationType">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Calculated Duration</label>
                           <div id="summaryCalculatedDuration">-</div>
                        </div>
                     </div>
                     <div class="col-12" id="summaryRecurrenceSection" style="display: none;">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Recurrence Pattern</label>
                           <div id="summaryRecurrence">-</div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Section 3: Participants & Details -->
         <div class="summary-section mb-4">
            <div class="summary-header">
               <h6 class="fw-semibold mb-3"><i class="ri-team-line me-2 text-primary"></i>Participants & Details</h6>
            </div>
            <div class="card border-0 shadow-sm">
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Activity Owner</label>
                           <div class="fw-semibold" id="summaryActivityOwner">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Client</label>
                           <div class="fw-semibold" id="summaryClient">-</div>
                        </div>
                     </div>
                     <div class="col-12">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Participants/Attendees</label>
                           <div id="summaryParticipants">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Location</label>
                           <div id="summaryLocation">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Meeting Link</label>
                           <div id="summaryMeetingLink">-</div>
                        </div>
                     </div>
                     <div class="col-12" id="summaryNotesSection" style="display: none;">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Notes & Agenda</label>
                           <div id="summaryNotes" class="text-secondary">-</div>
                        </div>
                     </div>
                     <div class="col-md-6" id="summaryReminderSection" style="display: none;">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Reminder</label>
                           <div id="summaryReminder">-</div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Section 4: Outcomes & Expenses -->
         <div class="summary-section mb-4" id="summaryOutcomesSection">
            <div class="summary-header">
               <h6 class="fw-semibold mb-3"><i class="ri-checkbox-circle-line me-2 text-primary"></i>Outcomes & Expenses</h6>
            </div>
            <div class="card border-0 shadow-sm">
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Activity Outcome</label>
                           <div id="summaryOutcome">-</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Total Expenses</label>
                           <div class="fw-semibold text-danger fs-18" id="summaryCost">KES 0.00</div>
                        </div>
                     </div>
                     <div class="col-12" id="summaryExpensesListSection" style="display: none;">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Expense Breakdown</label>
                           <div class="table-responsive">
                              <table class="table table-sm table-borderless mb-0" id="summaryExpensesTable">
                                 <thead class="table-light">
                                    <tr>
                                       <th>Category</th>
                                       <th>Description</th>
                                       <th class="text-end">Amount</th>
                                    </tr>
                                 </thead>
                                 <tbody id="summaryExpensesList">
                                    <!-- Expenses will be listed here -->
                                 </tbody>
                                 <tfoot class="table-light">
                                    <tr>
                                       <th colspan="2" class="text-end">Total:</th>
                                       <th class="text-end text-danger" id="summaryExpensesTotal">KES 0.00</th>
                                    </tr>
                                 </tfoot>
                              </table>
                           </div>
                        </div>
                     </div>
                     <div class="col-12" id="summaryResultSection" style="display: none;">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Results & Key Takeaways</label>
                           <div id="summaryResult" class="text-secondary">-</div>
                        </div>
                     </div>
                     <div class="col-md-6" id="summaryCostCategorySection" style="display: none;">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Cost Category</label>
                           <div id="summaryCostCategory">-</div>
                        </div>
                     </div>
                     <div class="col-12" id="summaryCostNotesSection" style="display: none;">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Expense Notes</label>
                           <div id="summaryCostNotes" class="text-secondary">-</div>
                        </div>
                     </div>
                     <div class="col-12" id="summaryFollowUpSection" style="display: none;">
                        <div class="alert alert-warning mb-0">
                           <strong><i class="ri-alarm-warning-line me-1"></i>Follow-up Required</strong>
                           <div id="summaryFollowUpNotes" class="mt-1">-</div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Context Section -->
         <div class="summary-section mb-4">
            <div class="summary-header">
               <h6 class="fw-semibold mb-3"><i class="ri-briefcase-line me-2 text-primary"></i>Sales Context</h6>
            </div>
            <div class="card border-0 shadow-sm bg-light">
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Sales Case</label>
                           <div class="fw-semibold"><?= htmlspecialchars($salesCaseDetails->salesCaseName) ?></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="summary-item">
                           <label class="text-muted small mb-1">Client</label>
                           <div class="fw-semibold"><?= htmlspecialchars($salesCaseDetails->clientName) ?></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="row g-3">
            <div class="col-md-6">
               <button type="button" class="btn btn-outline-primary w-100" onclick="window.activityWizard.goToStep(1)">
                  <i class="ri-edit-line me-1"></i>Edit Activity Details
               </button>
            </div>
            <div class="col-md-6">
               <button type="button" class="btn btn-outline-info w-100" onclick="printActivitySummary()">
                  <i class="ri-printer-line me-1"></i>Print Summary
               </button>
            </div>
         </div>
      </div>
   </div>

   <!-- Wizard Navigation -->
   <div class="wizard-navigation mt-4 d-flex justify-content-between align-items-center">
      <button type="button" class="btn btn-outline-secondary" id="wizardPrevBtn" style="display: none;">
         <i class="ri-arrow-left-line me-1"></i>Previous
      </button>
      <div class="flex-grow-1 text-center">
         <small class="text-muted" id="stepIndicator">Step <span id="currentStepNum">1</span> of 5</small>
      </div>
      <button type="button" class="btn btn-primary" id="wizardNextBtn">
         Next<i class="ri-arrow-right-line ms-1"></i>
      </button>
      <button type="button" class="btn btn-success" id="wizardSubmitBtn" style="display: none;">
         <i class="ri-save-line me-1"></i>Save Activity
      </button>
   </div>

   <!-- Scroll to Top Button -->
   <button type="button" class="btn btn-sm btn-primary scroll-to-top-btn" id="scrollToTopBtn" style="display: none;">
      <i class="ri-arrow-up-line"></i>
   </button>
</div>

<!-- Activity Wizard Scripts -->
<script>
(function() {
   'use strict';

   let currentStep = 1;
   const totalSteps = 5;
   let datePicker, startTimePicker, endTimePicker, durationEndDatePicker, durationEndTimePicker, recurringEndDatePicker;
   let participantsSelect = null;
   let expenseIndex = 0;
   let expenses = [];

   // Initialize Activity Wizard
   function initializeActivityWizard() {
      if (typeof flatpickr === 'undefined') {
         console.warn('Flatpickr is not loaded. Retrying in 100ms...');
         setTimeout(initializeActivityWizard, 100);
         return;
      }

      initializeDateTimePickers();
      initializeWizardNavigation();
      initializeDurationTypeHandlers();
      initializeConditionalFields();
      initializeSummaryUpdates();
      initializeCategoryTypeFilter();
      initializeTomSelect();
      initializeExpenseManagement();
   }

   // Initialize Date and Time Pickers
   function initializeDateTimePickers() {
      const activityDateInput = document.getElementById('activityDate');
      const startTimeInput = document.getElementById('activityStartTime');
      const endTimeInput = document.getElementById('activityEndTime');
      const durationEndDateInput = document.getElementById('activityDurationEndDate');
      const durationEndTimeInput = document.getElementById('activityDurationEndTime');
      const recurringEndDateInput = document.getElementById('recurringEndDate');
      const legacyTimeInput = document.getElementById('timepickr1');

      if (!activityDateInput) return;

      // Activity Date Picker
      datePicker = flatpickr(activityDateInput, {
         dateFormat: 'Y-m-d',
         altInput: true,
         altFormat: 'F j, Y',
         allowInput: true,
         defaultDate: 'today',
         onChange: updateSummary
      });

      // Start Time Picker
      if (startTimeInput) {
         startTimePicker = flatpickr(startTimeInput, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            allowInput: true,
            minuteIncrement: 15,
         onChange: function(selectedDates, dateStr) {
               if (legacyTimeInput) legacyTimeInput.value = dateStr;
               calculateDuration();
               updateSummary();
         }
      });
      }

      // End Time Picker
      if (endTimeInput) {
         endTimePicker = flatpickr(endTimeInput, {
         enableTime: true,
         noCalendar: true,
         dateFormat: 'H:i',
         time_24hr: true,
         allowInput: true,
         minuteIncrement: 15,
            onChange: function() {
            validateTimeRange();
               calculateDuration();
               updateSummary();
            }
         });
      }

      // Duration End Date Picker
      if (durationEndDateInput) {
         durationEndDatePicker = flatpickr(durationEndDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            allowInput: true
         });
      }

      // Duration End Time Picker
      if (durationEndTimeInput) {
         durationEndTimePicker = flatpickr(durationEndTimeInput, {
         enableTime: true,
         noCalendar: true,
         dateFormat: 'H:i',
         time_24hr: true,
         allowInput: true,
            minuteIncrement: 15
         });
      }

      // Recurring End Date Picker
      if (recurringEndDateInput) {
         recurringEndDatePicker = flatpickr(recurringEndDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            allowInput: true
         });
      }
   }

   // Wizard Navigation
   function initializeWizardNavigation() {
      const prevBtn = document.getElementById('wizardPrevBtn');
      const nextBtn = document.getElementById('wizardNextBtn');
      const submitBtn = document.getElementById('wizardSubmitBtn');

      if (nextBtn) {
         nextBtn.addEventListener('click', function() {
            if (validateCurrentStep()) {
               goToStep(currentStep + 1);
            }
         });
      }

      if (prevBtn) {
         prevBtn.addEventListener('click', function() {
            goToStep(currentStep - 1);
         });
      }

      if (submitBtn) {
         submitBtn.addEventListener('click', function() {
            if (validateCurrentStep()) {
               submitActivity();
            }
         });
      }

      // Allow clicking on wizard steps
      document.querySelectorAll('.wizard-step').forEach(step => {
         step.addEventListener('click', function() {
            const stepNum = parseInt(this.dataset.step);
            if (stepNum < currentStep || validateCurrentStep()) {
               goToStep(stepNum);
            }
         });
      });
   }

   // Navigate to specific step
   function goToStep(stepNum) {
      if (stepNum < 1 || stepNum > totalSteps) return;

      // Hide current step
      document.querySelectorAll('.wizard-pane').forEach(pane => {
         pane.classList.remove('active');
      });
      document.querySelectorAll('.wizard-step').forEach(step => {
         step.classList.remove('active', 'completed');
      });

      // Show new step
      const newPane = document.getElementById(`wizard-step-${stepNum}`);
      if (newPane) newPane.classList.add('active');

      // Update step indicators
      for (let i = 1; i <= totalSteps; i++) {
         const stepEl = document.querySelector(`.wizard-step[data-step="${i}"]`);
         if (!stepEl) continue;

         if (i < stepNum) {
            stepEl.classList.add('completed');
         } else if (i === stepNum) {
            stepEl.classList.add('active');
         }
      }

      currentStep = stepNum;
      updateNavigationButtons();
      updateSummary();

      // Scroll to top of modal content smoothly
      const modalBody = document.querySelector('#manageActivityModal .modal-body');
      if (modalBody) {
         modalBody.scrollTo({
            top: 0,
            behavior: 'smooth'
         });
      }
   }

   // Update navigation button visibility
   function updateNavigationButtons() {
      const prevBtn = document.getElementById('wizardPrevBtn');
      const nextBtn = document.getElementById('wizardNextBtn');
      const submitBtn = document.getElementById('wizardSubmitBtn');
      const currentStepNum = document.getElementById('currentStepNum');

      if (prevBtn) prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
      if (nextBtn) nextBtn.style.display = currentStep === totalSteps ? 'none' : 'inline-block';
      if (submitBtn) submitBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
      if (currentStepNum) currentStepNum.textContent = currentStep;
   }

   // Validate current step
   function validateCurrentStep() {
      const currentPane = document.getElementById(`wizard-step-${currentStep}`);
      if (!currentPane) return true;

      const requiredFields = currentPane.querySelectorAll('[required]');
      let isValid = true;

      requiredFields.forEach(field => {
         if (!field.value || field.value.trim() === '') {
            field.classList.add('is-invalid');
            isValid = false;
         } else {
            field.classList.remove('is-invalid');
         }
      });

      // Additional validation for step 2 (time range)
      if (currentStep === 2 && !validateTimeRange()) {
         isValid = false;
      }

      if (!isValid) {
         showNotification('Please fill in all required fields', 'warning');
      }

      return isValid;
   }

   // Time range validation
      function validateTimeRange() {
      const startTimeInput = document.getElementById('activityStartTime');
      const endTimeInput = document.getElementById('activityEndTime');
      const endTimeError = document.getElementById('endTimeError');
      const allDayEvent = document.getElementById('allDayEvent');

      if (!startTimeInput || !endTimeInput) return true;
      if (allDayEvent && allDayEvent.checked) return true;

         const startTime = startTimeInput.value;
         const endTime = endTimeInput.value;

         if (!startTime || !endTime) {
         if (endTimeError) endTimeError.style.display = 'none';
               endTimeInput.classList.remove('is-invalid');
         return true;
      }

      const [startH, startM] = startTime.split(':').map(Number);
      const [endH, endM] = endTime.split(':').map(Number);
      const startTotal = startH * 60 + startM;
      const endTotal = endH * 60 + endM;

      if (endTotal <= startTotal) {
         if (endTimeError) endTimeError.style.display = 'block';
         endTimeInput.classList.add('is-invalid');
         return false;
      } else {
         if (endTimeError) endTimeError.style.display = 'none';
         endTimeInput.classList.remove('is-invalid');
            return true;
      }
   }

   // Calculate and display duration
   function calculateDuration() {
      const startTime = document.getElementById('activityStartTime')?.value;
      const endTime = document.getElementById('activityEndTime')?.value;
      const durationDisplay = document.getElementById('durationDisplay');
      const calculatedDuration = document.getElementById('calculatedDuration');

      if (!startTime || !endTime || !durationDisplay || !calculatedDuration) return;

      const [startH, startM] = startTime.split(':').map(Number);
      const [endH, endM] = endTime.split(':').map(Number);
      const startTotal = startH * 60 + startM;
      const endTotal = endH * 60 + endM;
      const diffMinutes = endTotal - startTotal;

      if (diffMinutes > 0) {
         const hours = Math.floor(diffMinutes / 60);
         const minutes = diffMinutes % 60;
         let durationText = '';

         if (hours > 0) durationText += `${hours} hour${hours > 1 ? 's' : ''}`;
         if (minutes > 0) durationText += ` ${minutes} minute${minutes > 1 ? 's' : ''}`;

         calculatedDuration.textContent = durationText.trim();
         durationDisplay.style.display = 'block';
      } else {
         durationDisplay.style.display = 'none';
      }
   }

   // Duration type handlers
   function initializeDurationTypeHandlers() {
      const durationType = document.getElementById('durationType');
      const timeFields = document.getElementById('timeFields');
      const durationFields = document.getElementById('durationFields');
      const recurringFields = document.getElementById('recurringFields');
      const allDayEvent = document.getElementById('allDayEvent');

      if (durationType) {
         durationType.addEventListener('change', function() {
            const selectedType = this.value;

            if (timeFields) timeFields.style.display = 'none';
            if (durationFields) durationFields.style.display = 'none';
            if (recurringFields) recurringFields.style.display = 'none';

            if (selectedType === 'oneOff') {
               if (timeFields) timeFields.style.display = 'block';
            } else if (selectedType === 'duration') {
               if (timeFields) timeFields.style.display = 'block';
               if (durationFields) durationFields.style.display = 'block';
            } else if (selectedType === 'recurring') {
               if (timeFields) timeFields.style.display = 'block';
               if (recurringFields) recurringFields.style.display = 'block';
            }
         });
      }

      if (allDayEvent) {
         allDayEvent.addEventListener('change', function() {
            const startTimeInput = document.getElementById('activityStartTime');
            const endTimeInput = document.getElementById('activityEndTime');

            if (this.checked) {
               if (startTimeInput) startTimeInput.disabled = true;
               if (endTimeInput) endTimeInput.disabled = true;
         } else {
               if (startTimeInput) startTimeInput.disabled = false;
               if (endTimeInput) endTimeInput.disabled = false;
            }
         });
      }
   }

   // Conditional field handlers
   function initializeConditionalFields() {
      // Recurrence end type
      const recurrenceEndType = document.getElementById('recurrenceEndType');
      const numberOfOccurrences = document.getElementById('numberOfOccurrencesToEnd');
      const recurringEndDate = document.getElementById('recurringEndDate');
      const recurrenceEndFields = document.getElementById('recurrenceEndFields');

      if (recurrenceEndType) {
         recurrenceEndType.addEventListener('change', function() {
            if (!recurrenceEndFields) return;

            if (this.value === 'after') {
               recurrenceEndFields.style.display = 'block';
               if (numberOfOccurrences) numberOfOccurrences.style.display = 'block';
               if (recurringEndDate) recurringEndDate.style.display = 'none';
            } else if (this.value === 'on') {
               recurrenceEndFields.style.display = 'block';
               if (numberOfOccurrences) numberOfOccurrences.style.display = 'none';
               if (recurringEndDate) recurringEndDate.style.display = 'block';
            } else {
               recurrenceEndFields.style.display = 'none';
            }
         });
      }

      // Send reminder
      const sendReminder = document.getElementById('sendReminder');
      const reminderTimeField = document.getElementById('reminderTimeField');

      if (sendReminder && reminderTimeField) {
         sendReminder.addEventListener('change', function() {
            reminderTimeField.style.display = this.checked ? 'block' : 'none';
         });
      }

      // Requires follow-up
      const requiresFollowUp = document.getElementById('requiresFollowUp');
      const followUpFields = document.getElementById('followUpFields');

      if (requiresFollowUp && followUpFields) {
         requiresFollowUp.addEventListener('change', function() {
            followUpFields.style.display = this.checked ? 'block' : 'none';
         });
      }
   }

   // Update summary display
   function initializeSummaryUpdates() {
      // Listen to changes on key fields
      ['activityName', 'activityTypeID', 'activityDate', 'activityStartTime', 'activityOwnerID', 'activityPriority'].forEach(fieldId => {
         const field = document.getElementById(fieldId);
         if (field) {
            field.addEventListener('change', updateSummary);
            field.addEventListener('input', updateSummary);
         }
      });
   }

   // Initialize Category-Type Filtering Relationship
   function initializeCategoryTypeFilter() {
      const categorySelect = document.getElementById('activityCategoryID');
      const typeSelect = document.getElementById('activityTypeID');
      const typeFilterInfo = document.getElementById('typeFilterInfo');
      const typeSelectInfo = document.getElementById('typeSelectInfo');

      if (!categorySelect || !typeSelect) {
         console.warn('Category or Type select not found');
         return;
      }

      // Store all type options for filtering
      const allTypeOptions = Array.from(typeSelect.options).filter(opt => opt.value !== '');

      // Category change handler
      categorySelect.addEventListener('change', function() {
         const selectedCategoryID = this.value;

         // Clear current type selection
         typeSelect.value = '';

         // Hide all type options first
         Array.from(typeSelect.options).forEach(option => {
            if (option.value === '') {
               option.style.display = 'block';
            } else {
               option.style.display = 'none';
            }
         });

         if (selectedCategoryID) {
            // Show only types for selected category
            let visibleCount = 0;
            allTypeOptions.forEach(option => {
               const optionCategoryID = option.getAttribute('data-category-id');
               if (optionCategoryID === selectedCategoryID) {
                  option.style.display = 'block';
                  visibleCount++;
               }
            });

            // Update placeholder text
            const placeholderOption = typeSelect.options[0];
            if (placeholderOption && placeholderOption.value === '') {
               if (visibleCount > 0) {
                  placeholderOption.textContent = 'Select Type';
                  typeSelect.disabled = false;
               } else {
                  placeholderOption.textContent = 'No types available for this category';
                  typeSelect.disabled = true;
               }
            }

            // Update info text
            if (typeFilterInfo) {
               typeFilterInfo.style.display = visibleCount > 0 ? 'inline' : 'none';
            }
            if (typeSelectInfo) {
               typeSelectInfo.style.display = visibleCount > 0 ? 'none' : 'inline';
               if (visibleCount === 0) {
                  typeSelectInfo.textContent = 'No types available for this category';
                  typeSelectInfo.classList.add('text-warning');
               }
            }

            // Auto-select if only one option available
            if (visibleCount === 1) {
               allTypeOptions.forEach(option => {
                  const optionCategoryID = option.getAttribute('data-category-id');
                  if (optionCategoryID === selectedCategoryID) {
                     typeSelect.value = option.value;
                     updateSummary();
                  }
               });
            }
         } else {
            // No category selected - reset
            const placeholderOption = typeSelect.options[0];
            if (placeholderOption && placeholderOption.value === '') {
               placeholderOption.textContent = 'Select Category First';
            }
            typeSelect.disabled = true;

            if (typeFilterInfo) typeFilterInfo.style.display = 'none';
            if (typeSelectInfo) {
               typeSelectInfo.style.display = 'inline';
               typeSelectInfo.textContent = 'Types will appear after selecting category';
               typeSelectInfo.classList.remove('text-warning');
            }
         }

         // Update summary
         updateSummary();
      });

      // Type change handler - update summary
      typeSelect.addEventListener('change', function() {
         updateSummary();
      });

      // Initial state - disable type select until category is chosen
      typeSelect.disabled = true;
   }

   // Initialize Expense Management System
   function initializeExpenseManagement() {
      const addExpenseBtn = document.getElementById('addExpenseBtn');
      const expensesContainer = document.getElementById('expensesContainer');
      const expenseRowTemplate = document.getElementById('expenseRowTemplate');

      if (!addExpenseBtn || !expensesContainer || !expenseRowTemplate) {
         console.warn('Expense management elements not found');
         return;
      }

      // Add Expense Button Click
      addExpenseBtn.addEventListener('click', function() {
         addExpenseRow();
      });

      // Initialize with one expense row (optional)
      // addExpenseRow();
   }

   function addExpenseRow() {
      const expensesContainer = document.getElementById('expensesContainer');
      const expenseRowTemplate = document.getElementById('expenseRowTemplate');
      const noExpensesMessage = document.getElementById('noExpensesMessage');

      if (!expensesContainer || !expenseRowTemplate) return;

      // Hide "no expenses" message
      if (noExpensesMessage) {
         noExpensesMessage.style.display = 'none';
      }

      // Clone template
      const template = expenseRowTemplate.content.cloneNode(true);
      const expenseItem = template.querySelector('.expense-item');

      // Set unique index
      expenseIndex++;
      expenseItem.setAttribute('data-expense-index', expenseIndex);

      // Update all name attributes and IDs to use unique index
      expenseItem.querySelectorAll('[name], [id], [data-bs-target]').forEach(element => {
         if (element.hasAttribute('name')) {
            element.setAttribute('name', element.getAttribute('name').replace('INDEX', expenseIndex));
         }
         if (element.hasAttribute('id')) {
            element.setAttribute('id', element.getAttribute('id').replace('INDEX', expenseIndex));
         }
         if (element.hasAttribute('data-bs-target')) {
            element.setAttribute('data-bs-target', element.getAttribute('data-bs-target').replace('INDEX', expenseIndex));
         }
      });

      // Add to container
      expensesContainer.appendChild(template);

      // Add event listeners
      const newExpenseItem = expensesContainer.querySelector(`[data-expense-index="${expenseIndex}"]`);

      // Remove button
      const removeBtn = newExpenseItem.querySelector('.remove-expense-btn');
      if (removeBtn) {
         removeBtn.addEventListener('click', function() {
            removeExpenseRow(expenseIndex);
         });
      }

      // Amount input - calculate total on change
      const amountInput = newExpenseItem.querySelector('.expense-amount');
      if (amountInput) {
         amountInput.addEventListener('input', calculateExpenseTotal);
      }

      // Category select - visual feedback
      const categorySelect = newExpenseItem.querySelector('.expense-category');
      if (categorySelect) {
         categorySelect.addEventListener('change', function() {
            updateExpenseCategoryColor(this, newExpenseItem);
         });
      }

      // Add animation
      newExpenseItem.style.animation = 'fadeInUp 0.3s ease';

      // Calculate total
      calculateExpenseTotal();
   }

   function removeExpenseRow(index) {
      const expenseItem = document.querySelector(`[data-expense-index="${index}"]`);
      if (!expenseItem) return;

      // Add fade out animation
      expenseItem.style.animation = 'fadeOut 0.3s ease';

      setTimeout(() => {
         expenseItem.remove();
         calculateExpenseTotal();

         // Show "no expenses" message if no items left
         const expensesContainer = document.getElementById('expensesContainer');
         const noExpensesMessage = document.getElementById('noExpensesMessage');
         const remainingItems = expensesContainer.querySelectorAll('.expense-item');

         if (remainingItems.length === 0 && noExpensesMessage) {
            noExpensesMessage.style.display = 'block';
         }
      }, 300);
   }

   function calculateExpenseTotal() {
      const expenseItems = document.querySelectorAll('.expense-item');
      let total = 0;
      let count = 0;

      expenseItems.forEach(item => {
         const amountInput = item.querySelector('.expense-amount');
         if (amountInput && amountInput.value) {
            total += parseFloat(amountInput.value) || 0;
            count++;
         }
      });

      // Update total display
      const totalElement = document.getElementById('totalExpenseAmount');
      const countElement = document.getElementById('expenseItemCount');
      const totalCard = document.getElementById('expenseTotalCard');

      if (totalElement) {
         totalElement.textContent = total.toFixed(2);
      }

      if (countElement) {
         countElement.textContent = count;
      }

      if (totalCard) {
         totalCard.style.display = count > 0 ? 'block' : 'none';
      }

      // Update summary
      updateSummary();

      return total;
   }

   function updateExpenseCategoryColor(selectElement, expenseItem) {
      const selectedOption = selectElement.options[selectElement.selectedIndex];
      const color = selectedOption.getAttribute('data-color') || '#6c757d';

      // Update card border color
      const card = expenseItem.querySelector('.card');
      if (card) {
         card.style.borderLeft = `4px solid ${color}`;
      }
   }

   // Load existing expenses (for edit mode)
   function loadExpenses(expensesData) {
      if (!expensesData || !Array.isArray(expensesData)) return;

      expensesData.forEach(expense => {
         addExpenseRow();

         // Get the last added expense item
         const expensesContainer = document.getElementById('expensesContainer');
         const lastExpenseItem = expensesContainer.querySelector('.expense-item:last-of-type');

         if (lastExpenseItem) {
            // Populate fields
            const categorySelect = lastExpenseItem.querySelector('.expense-category');
            const amountInput = lastExpenseItem.querySelector('.expense-amount');
            const descriptionInput = lastExpenseItem.querySelector('.expense-description');

            if (categorySelect && expense.category) categorySelect.value = expense.category;
            if (amountInput && expense.amount) amountInput.value = expense.amount;
            if (descriptionInput && expense.description) descriptionInput.value = expense.description;

            // Additional fields
            if (expense.paymentMethod) {
               const paymentInput = lastExpenseItem.querySelector('[name*="paymentMethod"]');
               if (paymentInput) paymentInput.value = expense.paymentMethod;
            }
            if (expense.receiptNumber) {
               const receiptInput = lastExpenseItem.querySelector('[name*="receiptNumber"]');
               if (receiptInput) receiptInput.value = expense.receiptNumber;
            }
            if (expense.reimbursable) {
               const reimbursableInput = lastExpenseItem.querySelector('[name*="reimbursable"]');
               if (reimbursableInput) reimbursableInput.value = expense.reimbursable;
            }

            // Update color
            if (categorySelect) {
               updateExpenseCategoryColor(categorySelect, lastExpenseItem);
            }
         }
      });

      calculateExpenseTotal();
   }

   // Initialize Tom Select for Participants
   function initializeTomSelect() {
      // Check if Tom Select is available
      if (typeof TomSelect === 'undefined') {
         console.warn('TomSelect is not loaded. Attempting to load from CDN...');

         // Check if already loading
         if (document.getElementById('tom-select-cdn-css') || document.getElementById('tom-select-cdn-js')) {
            console.log('Tom Select is already being loaded...');
            setTimeout(initializeTomSelect, 200);
            return;
         }

         // Try to load Tom Select dynamically
         const link = document.createElement('link');
         link.id = 'tom-select-cdn-css';
         link.rel = 'stylesheet';
         link.href = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css';
         document.head.appendChild(link);

         const script = document.createElement('script');
         script.id = 'tom-select-cdn-js';
         script.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js';
         script.onload = function() {
            console.log('Tom Select loaded from CDN successfully');
            initializeParticipantsSelect();
         };
         script.onerror = function() {
            console.error('Failed to load Tom Select from CDN. Falling back to standard multi-select.');
            // Fallback - the standard multi-select will still work
         };
         document.head.appendChild(script);
         return;
      }

      initializeParticipantsSelect();
   }

   function initializeParticipantsSelect() {
      const participantsElement = document.getElementById('activityParticipants');
      if (!participantsElement) {
         console.warn('Participants select element not found');
         return;
      }

      // Destroy existing Tom Select instance if it exists
      if (participantsSelect) {
         try {
            participantsSelect.destroy();
         } catch (e) {
            console.warn('Error destroying existing Tom Select:', e);
         }
      }

      // Initialize Tom Select
      try {
         participantsSelect = new TomSelect(participantsElement, {
            plugins: {
               'remove_button': {
                  title: 'Remove this participant',
               },
               'clear_button': {
                  title: 'Clear all participants',
               }
            },
            maxItems: null,
            maxOptions: 100,
            placeholder: 'Search and select participants...',
            searchField: ['text'],
            sortField: {
               field: 'text',
               direction: 'asc'
            },
            closeAfterSelect: false,
            hidePlaceholder: false,
            openOnFocus: true,
            highlightClassName: 'active',
            render: {
               option: function(data, escape) {
                  // Generate initials from name
                  const nameParts = data.text.trim().split(' ');
                  const initials = nameParts.length >= 2
                     ? nameParts[0][0] + nameParts[nameParts.length - 1][0]
                     : data.text.substring(0, 2);

                  return '<div class="d-flex align-items-center py-1">' +
                     '<div class="avatar avatar-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.7rem;">' +
                     '<span>' + escape(initials.toUpperCase()) + '</span>' +
                     '</div>' +
                     '<span>' + escape(data.text) + '</span>' +
                     '</div>';
               },
               item: function(data, escape) {
                  // Generate initials from name
                  const nameParts = data.text.trim().split(' ');
                  const initials = nameParts.length >= 2
                     ? nameParts[0][0] + nameParts[nameParts.length - 1][0]
                     : data.text.substring(0, 2);

                  return '<div class="d-flex align-items-center">' +
                     '<div class="avatar avatar-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-1" style="width: 22px; height: 22px; font-size: 0.65rem;">' +
                     '<span>' + escape(initials.toUpperCase()) + '</span>' +
                     '</div>' +
                     '<span class="ms-1">' + escape(data.text) + '</span>' +
                     '</div>';
               },
               no_results: function(data, escape) {
                  return '<div class="no-results">No participants found matching "' + escape(data.input) + '"</div>';
               }
            },
            onInitialize: function() {
               console.log('Tom Select initialized for participants');
            },
            onChange: function(value) {
               console.log('Participants changed:', value);
            }
         });
      } catch (e) {
         console.error('Error initializing Tom Select:', e);
         // Show user-friendly message
         if (typeof showToast === 'function') {
            showToast('Note: Using standard multi-select for participants', 'info');
         }
      }
   }

   function updateSummary() {
      // Update Step 4 mini summary (kept for backward compatibility)
      updateMiniSummary();

      // Update comprehensive Step 5 summary
      updateComprehensiveSummary();
   }

   // Update mini summary in Step 4
   function updateMiniSummary() {
      const summaryName = document.getElementById('summaryName');
      const summaryType = document.getElementById('summaryType');
      const summaryDate = document.getElementById('summaryDate');
      const summaryTime = document.getElementById('summaryTime');
      const summaryOwner = document.getElementById('summaryOwner');
      const summaryPriority = document.getElementById('summaryPriority');

      if (summaryName) {
         summaryName.textContent = document.getElementById('activityName')?.value || '-';
      }

      if (summaryType) {
         const typeSelect = document.getElementById('activityTypeID');
         summaryType.textContent = typeSelect?.options[typeSelect.selectedIndex]?.text || '-';
      }

      if (summaryDate) {
         const dateInput = document.getElementById('activityDate');
         summaryDate.textContent = dateInput?._flatpickr?.formatDate(dateInput._flatpickr.selectedDates[0], 'F j, Y') || '-';
      }

      if (summaryTime) {
         const startTime = document.getElementById('activityStartTime')?.value;
         const endTime = document.getElementById('activityEndTime')?.value;
         if (startTime && endTime) {
            summaryTime.textContent = `${startTime} - ${endTime}`;
         } else if (startTime) {
            summaryTime.textContent = startTime;
         } else {
            summaryTime.textContent = '-';
         }
      }

      if (summaryOwner) {
         const ownerSelect = document.getElementById('activityOwnerID');
         summaryOwner.textContent = ownerSelect?.options[ownerSelect.selectedIndex]?.text || '-';
      }

      if (summaryPriority) {
         summaryPriority.textContent = document.getElementById('activityPriority')?.value || '-';
      }
   }

   // Update comprehensive summary in Step 5
   function updateComprehensiveSummary() {
      // Section 1: Basic Information
      updateElement('summaryActivityName', 'activityName');

      const categorySelect = document.getElementById('activityCategoryID');
      updateElement('summaryCategory', categorySelect?.options[categorySelect.selectedIndex]?.text);

      const typeSelect = document.getElementById('activityTypeID');
      updateElement('summaryActivityType', typeSelect?.options[typeSelect.selectedIndex]?.text);

      const priority = document.getElementById('activityPriority')?.value || '-';
      const priorityEl = document.getElementById('summaryActivityPriority');
      if (priorityEl) {
         const priorityColors = {
            'Low': 'badge bg-secondary',
            'Medium': 'badge bg-info',
            'High': 'badge bg-warning',
            'Urgent': 'badge bg-danger'
         };
         priorityEl.innerHTML = `<span class="${priorityColors[priority] || 'badge bg-secondary'}">${priority}</span>`;
      }

      const status = document.getElementById('activityStatus')?.value || '-';
      const statusEl = document.getElementById('summaryActivityStatus');
      if (statusEl) {
         const statusColors = {
            'notStarted': 'badge bg-secondary',
            'inProgress': 'badge bg-primary',
            'completed': 'badge bg-success',
            'needsAttention': 'badge bg-warning',
            'stalled': 'badge bg-danger'
         };
         const statusLabels = {
            'notStarted': 'Not Started',
            'inProgress': 'In Progress',
            'completed': 'Completed',
            'needsAttention': 'Needs Attention',
            'stalled': 'Stalled'
         };
         statusEl.innerHTML = `<span class="${statusColors[status] || 'badge bg-secondary'}">${statusLabels[status] || status}</span>`;
      }

      updateElement('summaryDescription', 'activityDescription', 'No description provided');

      // Section 2: Schedule & Timeline
      const dateInput = document.getElementById('activityDate');
      const formattedDate = dateInput?._flatpickr?.formatDate(dateInput._flatpickr.selectedDates[0], 'F j, Y') || '-';
      updateElement('summaryActivityDate', formattedDate);

      updateElement('summaryStartTime', 'activityStartTime', '-');
      updateElement('summaryEndTime', 'activityEndTime', '-');

      const durationType = document.getElementById('durationType')?.value || 'oneOff';
      const durationLabels = {
         'oneOff': 'One-time Activity',
         'duration': 'Duration-based',
         'recurring': 'Recurring Activity'
      };
      updateElement('summaryDurationType', durationLabels[durationType]);

      // Calculate duration
      const startTime = document.getElementById('activityStartTime')?.value;
      const endTime = document.getElementById('activityEndTime')?.value;
      if (startTime && endTime) {
         const [startH, startM] = startTime.split(':').map(Number);
         const [endH, endM] = endTime.split(':').map(Number);
         const diffMinutes = (endH * 60 + endM) - (startH * 60 + startM);
         if (diffMinutes > 0) {
            const hours = Math.floor(diffMinutes / 60);
            const minutes = diffMinutes % 60;
            let durationText = '';
            if (hours > 0) durationText += `${hours} hour${hours > 1 ? 's' : ''}`;
            if (minutes > 0) durationText += ` ${minutes} minute${minutes > 1 ? 's' : ''}`;
            updateElement('summaryCalculatedDuration', durationText.trim());
         }
      }

      // Recurrence info
      const recurrenceSection = document.getElementById('summaryRecurrenceSection');
      if (durationType === 'recurring') {
         if (recurrenceSection) recurrenceSection.style.display = 'block';
         const recurrenceType = document.getElementById('recurrenceType')?.value;
         const recurringInterval = document.getElementById('recurringInterval')?.value;
         const recurringIntervalUnit = document.getElementById('recurringIntervalUnit')?.value;
         if (recurrenceType) {
            updateElement('summaryRecurrence', `${recurrenceType.charAt(0).toUpperCase() + recurrenceType.slice(1)} - Every ${recurringInterval} ${recurringIntervalUnit}(s)`);
         }
      } else {
         if (recurrenceSection) recurrenceSection.style.display = 'none';
      }

      // Section 3: Participants & Details
      const ownerSelect = document.getElementById('activityOwnerID');
      updateElement('summaryActivityOwner', ownerSelect?.options[ownerSelect.selectedIndex]?.text);

      const clientSelect = document.getElementById('clientID');
      updateElement('summaryClient', clientSelect?.options[clientSelect.selectedIndex]?.text);

      // Participants
      if (participantsSelect && participantsSelect.items.length > 0) {
         const participantNames = participantsSelect.items.map(id => {
            const option = participantsSelect.options[id];
            return option ? option.text : '';
         }).filter(name => name).join(', ');
         updateElement('summaryParticipants', participantNames);
      } else {
         updateElement('summaryParticipants', 'No participants selected');
      }

      updateElement('summaryLocation', 'activityLocation', 'Not specified');

      const meetingLink = document.getElementById('meetingLink')?.value;
      if (meetingLink) {
         const meetingLinkEl = document.getElementById('summaryMeetingLink');
         if (meetingLinkEl) {
            meetingLinkEl.innerHTML = `<a href="${meetingLink}" target="_blank" class="text-primary"><i class="ri-external-link-line me-1"></i>Join Meeting</a>`;
         }
      } else {
         updateElement('summaryMeetingLink', 'No meeting link');
      }

      const notes = document.getElementById('activityNotes')?.value;
      const notesSection = document.getElementById('summaryNotesSection');
      if (notes) {
         if (notesSection) notesSection.style.display = 'block';
         updateElement('summaryNotes', notes);
      } else {
         if (notesSection) notesSection.style.display = 'none';
      }

      const sendReminder = document.getElementById('sendReminder')?.checked;
      const reminderSection = document.getElementById('summaryReminderSection');
      if (sendReminder) {
         if (reminderSection) reminderSection.style.display = 'block';
         const reminderTime = document.getElementById('reminderTime')?.value;
         const reminderLabels = { '15': '15 minutes', '30': '30 minutes', '60': '1 hour', '1440': '1 day' };
         updateElement('summaryReminder', `<i class="ri-alarm-line me-1"></i>${reminderLabels[reminderTime] || reminderTime + ' minutes'} before`);
      } else {
         if (reminderSection) reminderSection.style.display = 'none';
      }

      // Section 4: Outcomes & Expenses
      const outcome = document.getElementById('activityOutcome')?.value;
      updateElement('summaryOutcome', outcome || 'Not completed yet');

      // Update expense breakdown
      const expenseItems = document.querySelectorAll('.expense-item');
      const summaryExpensesList = document.getElementById('summaryExpensesList');
      const summaryExpensesListSection = document.getElementById('summaryExpensesListSection');
      const summaryCost = document.getElementById('summaryCost');
      const summaryExpensesTotal = document.getElementById('summaryExpensesTotal');

      let totalExpenses = 0;

      if (summaryExpensesList) {
         summaryExpensesList.innerHTML = '';

         if (expenseItems.length > 0) {
            expenseItems.forEach(item => {
               const categorySelect = item.querySelector('.expense-category');
               const amountInput = item.querySelector('.expense-amount');
               const descriptionInput = item.querySelector('.expense-description');

               const category = categorySelect?.options[categorySelect.selectedIndex]?.text || '-';
               const amount = parseFloat(amountInput?.value || 0);
               const description = descriptionInput?.value || '-';

               if (amount > 0) {
                  totalExpenses += amount;

                  const row = document.createElement('tr');
                  row.innerHTML = `
                     <td>${category}</td>
                     <td class="text-secondary small">${description}</td>
                     <td class="text-end">KES ${amount.toFixed(2)}</td>
                  `;
                  summaryExpensesList.appendChild(row);
               }
            });

            if (summaryExpensesListSection) {
               summaryExpensesListSection.style.display = totalExpenses > 0 ? 'block' : 'none';
            }
         } else {
            if (summaryExpensesListSection) {
               summaryExpensesListSection.style.display = 'none';
            }
         }
      }

      // Update total expense display
      if (summaryCost) {
         summaryCost.textContent = `KES ${totalExpenses.toFixed(2)}`;
         summaryCost.className = totalExpenses > 0 ? 'fw-semibold text-danger fs-18' : 'fw-semibold text-muted fs-18';
      }

      if (summaryExpensesTotal) {
         summaryExpensesTotal.textContent = `KES ${totalExpenses.toFixed(2)}`;
      }

      const result = document.getElementById('activityResult')?.value;
      const resultSection = document.getElementById('summaryResultSection');
      if (result) {
         if (resultSection) resultSection.style.display = 'block';
         updateElement('summaryResult', result);
      } else {
         if (resultSection) resultSection.style.display = 'none';
      }

      const costCategory = document.getElementById('costCategory')?.value;
      const costCategorySection = document.getElementById('summaryCostCategorySection');
      if (costCategory) {
         if (costCategorySection) costCategorySection.style.display = 'block';
         updateElement('summaryCostCategory', costCategory);
      } else {
         if (costCategorySection) costCategorySection.style.display = 'none';
      }

      const costNotes = document.getElementById('costNotes')?.value;
      const costNotesSection = document.getElementById('summaryCostNotesSection');
      if (costNotes) {
         if (costNotesSection) costNotesSection.style.display = 'block';
         updateElement('summaryCostNotes', costNotes);
      } else {
         if (costNotesSection) costNotesSection.style.display = 'none';
      }

      const requiresFollowUp = document.getElementById('requiresFollowUp')?.checked;
      const followUpSection = document.getElementById('summaryFollowUpSection');
      if (requiresFollowUp) {
         if (followUpSection) followUpSection.style.display = 'block';
         const followUpNotes = document.getElementById('followUpNotes')?.value || 'Follow-up action required';
         updateElement('summaryFollowUpNotes', followUpNotes);
      } else {
         if (followUpSection) followUpSection.style.display = 'none';
      }
   }

   // Helper function to update element content
   function updateElement(elementId, valueOrId, defaultValue = '-') {
      const element = document.getElementById(elementId);
      if (!element) return;

      let value;
      if (typeof valueOrId === 'string' && document.getElementById(valueOrId)) {
         // It's an element ID
         value = document.getElementById(valueOrId).value;
      } else {
         // It's a direct value
         value = valueOrId;
      }

      element.textContent = value || defaultValue;
   }

   // Print summary function
   window.printActivitySummary = function() {
      window.print();
   };

   // Submit activity
   function submitActivity() {
      const form = document.querySelector('#manageActivityModal form');
      if (form) {
         // Trigger the existing save handler
         const saveButton = document.getElementById('saveActivity');
         if (saveButton) {
            saveButton.click();
         } else {
            form.submit();
         }
      }
   }

   // Show notification
   function showNotification(message, type = 'info') {
               if (typeof showToast === 'function') {
         showToast(message, type);
               } else {
         alert(message);
      }
   }

   // Store functions globally for external access
   window.activityWizard = {
      goToStep,
      validateTimeRange,
      updateSummary
   };

   // Scroll Detection for Visual Indicators
   function initializeScrollDetection() {
      const modalBody = document.querySelector('#manageActivityModal .modal-body');
      const scrollToTopBtn = document.getElementById('scrollToTopBtn');
      if (!modalBody) return;

      function checkScroll() {
         const hasScroll = modalBody.scrollHeight > modalBody.clientHeight;
         const isScrolled = modalBody.scrollTop > 10;
         const hasMoreContent = modalBody.scrollTop < (modalBody.scrollHeight - modalBody.clientHeight - 10);

         if (hasScroll) {
            modalBody.classList.add('has-scroll');
         } else {
            modalBody.classList.remove('has-scroll');
         }

         if (isScrolled) {
            modalBody.classList.add('scrolled');
         } else {
            modalBody.classList.remove('scrolled');
         }

         if (hasMoreContent && hasScroll) {
            modalBody.classList.add('has-more-content');
         } else {
            modalBody.classList.remove('has-more-content');
         }

         // Show/hide scroll to top button
         if (scrollToTopBtn) {
            if (modalBody.scrollTop > 200) {
               scrollToTopBtn.style.display = 'flex';
            } else {
               scrollToTopBtn.style.display = 'none';
            }
         }
      }

      modalBody.addEventListener('scroll', checkScroll);

      // Scroll to top button click handler
      if (scrollToTopBtn) {
         scrollToTopBtn.addEventListener('click', function() {
            modalBody.scrollTo({
               top: 0,
               behavior: 'smooth'
            });
         });
      }

      // Check on resize
      const resizeObserver = new ResizeObserver(checkScroll);
      resizeObserver.observe(modalBody);

      // Initial check
      setTimeout(checkScroll, 100);

      return { checkScroll, resizeObserver };
   }

   // Initialize when modal is shown
   const modal = document.getElementById('manageActivityModal');
   if (modal) {
      let scrollDetection = null;

         modal.addEventListener('shown.bs.modal', function() {
         setTimeout(() => {
            initializeActivityWizard();
            goToStep(1); // Reset to first step
            scrollDetection = initializeScrollDetection();
         }, 100);
      });

      // Reset wizard when modal is hidden
      modal.addEventListener('hidden.bs.modal', function() {
         currentStep = 1;
         goToStep(1);

         // Clear form fields
         const form = modal.querySelector('form');
         if (form) form.reset();

         // Reset category-type filter
         const typeSelect = document.getElementById('activityTypeID');
         if (typeSelect) {
            typeSelect.disabled = true;
            Array.from(typeSelect.options).forEach(option => {
               if (option.value !== '') {
                  option.style.display = 'none';
               }
            });
            if (typeSelect.options[0]) {
               typeSelect.options[0].textContent = 'Select Category First';
            }
         }

         const typeFilterInfo = document.getElementById('typeFilterInfo');
         const typeSelectInfo = document.getElementById('typeSelectInfo');
         if (typeFilterInfo) typeFilterInfo.style.display = 'none';
         if (typeSelectInfo) {
            typeSelectInfo.style.display = 'inline';
            typeSelectInfo.textContent = 'Types will appear after selecting category';
            typeSelectInfo.classList.remove('text-warning');
         }

         // Clean up scroll detection
         if (scrollDetection && scrollDetection.resizeObserver) {
            scrollDetection.resizeObserver.disconnect();
         }

         // Reset scroll indicators
         const modalBody = modal.querySelector('.modal-body');
         if (modalBody) {
            modalBody.classList.remove('has-scroll', 'scrolled', 'has-more-content');
            modalBody.scrollTop = 0;
         }

         // Clean up Tom Select
         if (participantsSelect) {
            try {
               participantsSelect.clear();
            } catch (e) {
               console.warn('Error clearing Tom Select:', e);
            }
         }
      });
   }

   // Function to load activity data for editing
   window.loadActivityForEdit = function(activityData) {
      if (!activityData) return;

      // Populate form fields
      if (activityData.activityID) document.getElementById('activityID').value = activityData.activityID;
      if (activityData.activityName) document.getElementById('activityName').value = activityData.activityName;
      if (activityData.activityDescription) document.getElementById('activityDescription').value = activityData.activityDescription;
      if (activityData.activityPriority) document.getElementById('activityPriority').value = activityData.activityPriority;
      if (activityData.activityStatus) document.getElementById('activityStatus').value = activityData.activityStatus;

      // Handle category and type with filtering
      if (activityData.activityCategoryID) {
         const categorySelect = document.getElementById('activityCategoryID');
         if (categorySelect) {
            categorySelect.value = activityData.activityCategoryID;
            // Trigger change to filter types
            categorySelect.dispatchEvent(new Event('change'));

            // Then set the type after filtering
            setTimeout(() => {
               if (activityData.activityTypeID) {
                  const typeSelect = document.getElementById('activityTypeID');
                  if (typeSelect) {
                     typeSelect.value = activityData.activityTypeID;
                     typeSelect.dispatchEvent(new Event('change'));
                  }
               }
            }, 100);
         }
      }

      // Date and time fields
      if (activityData.activityDate && datePicker) {
         datePicker.setDate(activityData.activityDate);
      }
      if (activityData.activityStartTime && startTimePicker) {
         startTimePicker.setDate(activityData.activityStartTime, false, 'H:i');
      }
      if (activityData.activityEndTime && endTimePicker) {
         endTimePicker.setDate(activityData.activityEndTime, false, 'H:i');
      }

      // Other fields
      if (activityData.activityOwnerID) document.getElementById('activityOwnerID').value = activityData.activityOwnerID;
      if (activityData.activityLocation) document.getElementById('activityLocation').value = activityData.activityLocation;
      if (activityData.durationType) document.getElementById('durationType').value = activityData.durationType;
      if (activityData.activityOutcome) document.getElementById('activityOutcome').value = activityData.activityOutcome;
      if (activityData.activityResult) document.getElementById('activityResult').value = activityData.activityResult;

      // Handle participants with Tom Select
      if (activityData.activityParticipants && participantsSelect) {
         try {
            // Clear existing selections
            participantsSelect.clear();

            // Parse participants (could be JSON array or comma-separated string)
            let participants = [];
            if (typeof activityData.activityParticipants === 'string') {
               try {
                  participants = JSON.parse(activityData.activityParticipants);
               } catch (e) {
                  // If not JSON, try comma-separated
                  participants = activityData.activityParticipants.split(',').map(p => p.trim()).filter(p => p);
               }
            } else if (Array.isArray(activityData.activityParticipants)) {
               participants = activityData.activityParticipants;
            }

            // Set selected values
            participants.forEach(participantId => {
               if (participantId) {
                  participantsSelect.addItem(participantId, true);
               }
            });
         } catch (e) {
            console.warn('Error loading participants:', e);
         }
      }

      // Load expenses
      if (activityData.expenses && Array.isArray(activityData.expenses)) {
         loadExpenses(activityData.expenses);
      }

      // Update summary
      updateSummary();
   };


   // Initialize if already shown
      if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
         if (modal && modal.classList.contains('show')) {
            initializeActivityWizard();
         }
      });
      } else {
      if (modal && modal.classList.contains('show')) {
         initializeActivityWizard();
      }
   }
})();
</script>

<style>
/* ============================================================
   ACTIVITY WIZARD STYLES
   ============================================================ */

/* Modal Scrolling Fix */
#manageActivityModal .modal-dialog {
   max-height: 90vh;
   display: flex;
   flex-direction: column;
   margin-top: 2vh;
   margin-bottom: 2vh;
}

#manageActivityModal.modal.fade .modal-dialog {
   transition: transform 0.3s ease-out;
}

#manageActivityModal .modal-content {
   max-height: 90vh;
   display: flex;
   flex-direction: column;
   overflow: hidden;
   border-radius: 0.5rem;
}

#manageActivityModal .modal-body {
   overflow-y: auto;
   overflow-x: hidden;
   max-height: calc(90vh - 120px);
   flex: 1 1 auto;
   padding: 1.5rem;
   scroll-behavior: smooth;
}

/* Ensure content doesn't get cut off */
#manageActivityModal .modal-body > * {
   flex-shrink: 0;
}

#manageActivityModal .modal-header {
   flex-shrink: 0;
   border-bottom: 1px solid #dee2e6;
}

/* Custom Scrollbar */
#manageActivityModal .modal-body::-webkit-scrollbar {
   width: 8px;
}

#manageActivityModal .modal-body::-webkit-scrollbar-track {
   background: #f1f1f1;
   border-radius: 4px;
}

#manageActivityModal .modal-body::-webkit-scrollbar-thumb {
   background: #c1c1c1;
   border-radius: 4px;
}

#manageActivityModal .modal-body::-webkit-scrollbar-thumb:hover {
   background: #a8a8a8;
}

/* Wizard Container */
.activity-wizard-container {
   padding: 0;
   min-height: 400px;
}

/* Wizard Progress Steps */
.wizard-progress {
   background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
   padding: 1rem 1.5rem;
   border-radius: 0.5rem;
   margin-bottom: 1.5rem;
   position: sticky;
   top: 0;
   z-index: 5;
   box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.wizard-steps {
   display: flex;
   align-items: center;
   justify-content: space-between;
   position: relative;
}

.wizard-step {
   flex: 0 0 auto;
   display: flex;
   flex-direction: column;
   align-items: center;
   gap: 0.5rem;
   position: relative;
   z-index: 2;
   cursor: pointer;
   transition: all 0.3s ease;
}

.wizard-step:hover .step-circle {
   transform: scale(1.1);
}

.step-circle {
   width: 3rem;
   height: 3rem;
   border-radius: 50%;
   background: #e9ecef;
   border: 3px solid #dee2e6;
   display: flex;
   align-items: center;
   justify-content: center;
   position: relative;
   transition: all 0.3s ease;
}

.step-circle i {
   font-size: 1.25rem;
   color: #6c757d;
   display: block;
}

.step-circle .step-number {
   font-size: 1rem;
   font-weight: 600;
   color: #6c757d;
   display: none;
}

.step-label {
   font-size: 0.75rem;
   font-weight: 500;
   color: #6c757d;
   text-align: center;
   max-width: 100px;
   transition: all 0.3s ease;
}

/* Active Step */
.wizard-step.active .step-circle {
   background: #007bff;
   border-color: #007bff;
   box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
}

.wizard-step.active .step-circle i {
   color: #fff;
}

.wizard-step.active .step-label {
   color: #007bff;
   font-weight: 600;
}

/* Completed Step */
.wizard-step.completed .step-circle {
   background: #28a745;
   border-color: #28a745;
}

.wizard-step.completed .step-circle i {
   color: #fff;
}

.wizard-step.completed .step-circle::after {
   content: '\f00c'; /* Checkmark icon */
   font-family: 'remixicon';
   position: absolute;
   color: #fff;
   font-size: 1rem;
}

.wizard-step.completed .step-label {
   color: #28a745;
}

/* Step Connector Lines */
.wizard-step-connector {
   flex: 1 1 auto;
   height: 3px;
   background: #dee2e6;
   margin: 0 0.5rem;
   position: relative;
   top: -1.5rem;
}

.wizard-step.completed + .wizard-step-connector {
   background: #28a745;
}

.wizard-step.active + .wizard-step-connector {
   background: linear-gradient(to right, #007bff 0%, #dee2e6 100%);
}

/* Wizard Content */
.wizard-content {
   min-height: 300px;
   max-height: none;
   position: relative;
   padding-bottom: 1rem;
}

.wizard-pane {
   display: none;
   animation: fadeInUp 0.4s ease;
   padding-bottom: 1rem;
}

.wizard-pane.active {
   display: block;
}

@keyframes fadeInUp {
   from {
      opacity: 0;
      transform: translateY(20px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}

/* Form Controls */
#manageActivityModal .form-label {
   font-weight: 500;
   color: #495057;
   margin-bottom: 0.375rem;
}

#manageActivityModal .form-control,
#manageActivityModal .form-select {
   border-radius: 0.375rem;
   border: 1px solid #ced4da;
   transition: all 0.2s ease;
}

#manageActivityModal .form-control:focus,
#manageActivityModal .form-select:focus {
   border-color: #007bff;
   box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

/* Disabled select styling */
#manageActivityModal .form-select:disabled {
   background-color: #f8f9fa;
   cursor: not-allowed;
   opacity: 0.65;
}

/* Activity Type Select - Enhanced feedback */
#activityTypeID:disabled {
   background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23adb5bd' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}

#activityCategoryID {
   border-left: 3px solid #007bff;
   position: relative;
}

#activityCategoryID::after {
   content: '1';
   position: absolute;
   right: 2.5rem;
   top: 50%;
   transform: translateY(-50%);
   background: #007bff;
   color: white;
   width: 20px;
   height: 20px;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 0.75rem;
   font-weight: 600;
}

#activityTypeID:not(:disabled) {
   border-left: 3px solid #28a745;
   position: relative;
   animation: highlight 0.5s ease;
}

#activityTypeID:not(:disabled)::after {
   content: '2';
   position: absolute;
   right: 2.5rem;
   top: 50%;
   transform: translateY(-50%);
   background: #28a745;
   color: white;
   width: 20px;
   height: 20px;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 0.75rem;
   font-weight: 600;
}

@keyframes highlight {
   0%, 100% {
      box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
   }
   50% {
      box-shadow: 0 0 0 10px rgba(40, 167, 69, 0.3);
   }
}

/* Filter info styling */
#typeFilterInfo {
   animation: fadeIn 0.3s ease;
}

#typeSelectInfo {
   transition: all 0.3s ease;
}

#manageActivityModal .flatpickr-input {
   cursor: pointer;
   background-color: #fff;
}

#manageActivityModal .is-invalid {
   border-color: #dc3545;
   animation: shake 0.3s ease;
}

#manageActivityModal .is-invalid:focus {
   border-color: #dc3545;
   box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

@keyframes shake {
   0%, 100% { transform: translateX(0); }
   25% { transform: translateX(-5px); }
   75% { transform: translateX(5px); }
}

/* Required Field Indicator */
.text-danger {
   color: #dc3545 !important;
}

/* Section Headers */
.wizard-pane h6 {
   border-bottom: 2px solid #e9ecef;
   padding-bottom: 0.75rem;
   margin-bottom: 1.5rem;
}

/* Wizard Navigation */
.wizard-navigation {
   padding-top: 1.5rem;
   padding-bottom: 1rem;
   border-top: 2px solid #e9ecef;
   margin-top: 2rem;
   background: #fff;
   position: sticky;
   bottom: 0;
   z-index: 10;
   margin-left: -1.5rem;
   margin-right: -1.5rem;
   padding-left: 1.5rem;
   padding-right: 1.5rem;
   box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.wizard-navigation .btn {
   min-width: 120px;
   font-weight: 500;
   padding: 0.5rem 1.5rem;
   transition: all 0.2s ease;
}

.wizard-navigation .btn:hover {
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Alert Boxes */
.wizard-pane .alert {
   border-radius: 0.375rem;
   border: none;
}

.alert-info {
   background-color: rgba(23, 162, 184, 0.1);
   color: #17a2b8;
}

/* Summary Card */
.wizard-pane .card {
   border-radius: 0.5rem;
   transition: all 0.3s ease;
}

.wizard-pane .card:hover {
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card-header {
   border-top-left-radius: 0.5rem !important;
   border-top-right-radius: 0.5rem !important;
}

/* Step 5: Summary Styles */
.summary-section {
   animation: fadeInUp 0.4s ease;
}

.summary-header h6 {
   color: #495057;
   font-size: 1rem;
   text-transform: uppercase;
   letter-spacing: 0.5px;
   border-bottom: none;
   padding-bottom: 0;
   margin-bottom: 1rem !important;
}

.summary-item {
   padding: 0.5rem 0;
}

.summary-item label {
   font-weight: 500;
   text-transform: uppercase;
   letter-spacing: 0.3px;
   font-size: 0.75rem;
}

.summary-item .fw-semibold {
   color: #212529;
   font-size: 0.95rem;
}

#wizard-step-5 .card {
   border: 1px solid #e9ecef;
   transition: all 0.3s ease;
}

#wizard-step-5 .card:hover {
   border-color: #007bff;
   box-shadow: 0 4px 16px rgba(0, 123, 255, 0.15);
}

#wizard-step-5 .alert-success {
   background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
   border: none;
   border-left: 4px solid #28a745;
}

#wizard-step-5 .badge {
   padding: 0.35rem 0.65rem;
   font-weight: 600;
   font-size: 0.75rem;
}

/* Summary Section Icons */
#wizard-step-5 .summary-header i {
   font-size: 1.2rem;
}

/* Badges in summary */
.bg-primary-transparent {
   background-color: rgba(0, 123, 255, 0.1);
   color: #007bff;
}

/* Expense Management Styles */
.expenses-container {
   max-height: 400px;
   overflow-y: auto;
   padding: 0.5rem 0;
}

.expense-item {
   border-left: 4px solid #6c757d;
   transition: all 0.3s ease;
   animation: fadeInUp 0.3s ease;
}

.expense-item:hover {
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
   transform: translateX(2px);
}

.expense-item .card-body {
   background: #fff;
}

.expense-category {
   font-weight: 500;
}

.expense-amount {
   font-weight: 600;
   color: #dc3545;
}

.remove-expense-btn {
   height: calc(1.5em + 0.5rem + 2px);
}

.remove-expense-btn:hover {
   transform: scale(1.05);
}

.toggle-expense-details {
   font-size: 0.875rem;
   text-decoration: none;
}

.toggle-expense-details:hover {
   text-decoration: underline;
}

.toggle-expense-details i {
   transition: transform 0.3s ease;
}

.toggle-expense-details[aria-expanded="true"] i {
   transform: rotate(180deg);
}

#expenseTotalCard {
   animation: fadeIn 0.3s ease;
   border-width: 2px;
}

#expenseTotalCard .card-body {
   background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
}

@keyframes fadeOut {
   from {
      opacity: 1;
      transform: translateX(0);
   }
   to {
      opacity: 0;
      transform: translateX(-20px);
   }
}

/* Expense summary table */
#summaryExpensesTable {
   font-size: 0.9rem;
}

#summaryExpensesTable thead th {
   font-weight: 600;
   text-transform: uppercase;
   font-size: 0.75rem;
   letter-spacing: 0.5px;
}

#summaryExpensesTable tbody tr:hover {
   background-color: #f8f9fa;
}

#summaryExpensesTable tfoot {
   border-top: 2px solid #dee2e6;
}

/* Responsive expense management */
@media (max-width: 768px) {
   .expenses-container {
      max-height: 300px;
   }

   .expense-item .col-md-4,
   .expense-item .col-md-3,
   .expense-item .col-md-1 {
      flex: 0 0 100%;
      max-width: 100%;
   }

   .expense-item .remove-expense-btn {
      width: auto;
      margin-top: 0.5rem;
   }
}

/* Print styles for summary */
@media print {
   #wizard-step-5 .alert-success {
      display: none;
   }

   #wizard-step-5 .row.g-3:last-child {
      display: none;
   }

   #wizard-step-5 .summary-section {
      page-break-inside: avoid;
   }

   .expenses-container {
      max-height: none;
      overflow: visible;
   }
}

/* Duration Display */
#durationDisplay {
   animation: fadeIn 0.4s ease;
}

#endTimeError {
   animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
   from {
      opacity: 0;
      transform: translateY(-5px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}

/* Conditional Fields */
#timeFields,
#durationFields,
#recurringFields,
#recurrenceEndFields,
#reminderTimeField,
#followUpFields {
   animation: fadeIn 0.3s ease;
}

/* Context Display Card */
.wizard-pane .bg-light {
   background-color: #f8f9fa !important;
   border-radius: 0.375rem;
}

/* Multi-select Styling */
#manageActivityModal select[multiple] {
   min-height: 120px;
   padding: 0.5rem;
}

#manageActivityModal select[multiple] option {
   padding: 0.5rem;
   border-radius: 0.25rem;
   margin-bottom: 0.25rem;
}

#manageActivityModal select[multiple] option:hover {
   background-color: #007bff;
   color: #fff;
}

/* Tom Select Styling */
#manageActivityModal .ts-wrapper {
   width: 100% !important;
}

#manageActivityModal .ts-control {
   border: 1px solid #ced4da;
   border-radius: 0.375rem;
   padding: 0.375rem 0.75rem;
   min-height: 48px;
   background: #fff;
   transition: all 0.2s ease;
}

#manageActivityModal .ts-control:focus,
#manageActivityModal .ts-wrapper.focus .ts-control {
   border-color: #007bff;
   box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
   outline: none;
}

#manageActivityModal .ts-control input {
   border: none !important;
   box-shadow: none !important;
   padding: 0 !important;
   font-size: 0.9rem;
}

#manageActivityModal .ts-dropdown {
   border: 1px solid #ced4da;
   border-radius: 0.375rem;
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
   margin-top: 4px;
   z-index: 1050;
}

#manageActivityModal .ts-dropdown .option {
   padding: 0.5rem 0.75rem;
   border-radius: 0.25rem;
   margin: 0.25rem;
   transition: all 0.2s ease;
}

#manageActivityModal .ts-dropdown .option:hover,
#manageActivityModal .ts-dropdown .option.active {
   background-color: #007bff;
   color: #fff;
}

#manageActivityModal .ts-dropdown .option .avatar {
   flex-shrink: 0;
}

#manageActivityModal .item {
   background-color: #007bff;
   color: #fff;
   border: none;
   border-radius: 0.25rem;
   padding: 0.25rem 0.5rem;
   margin: 0.125rem;
   font-size: 0.875rem;
   display: inline-flex;
   align-items: center;
   gap: 0.25rem;
}

#manageActivityModal .item .avatar {
   flex-shrink: 0;
}

#manageActivityModal .item .remove {
   color: #fff;
   opacity: 0.7;
   margin-left: 0.25rem;
   border: none;
   background: transparent;
   cursor: pointer;
   padding: 0;
   width: 16px;
   height: 16px;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   transition: opacity 0.2s ease;
}

#manageActivityModal .item .remove:hover {
   opacity: 1;
}

#manageActivityModal .ts-wrapper.plugin-remove_button .item .remove::before {
   content: '×';
   font-size: 1.2rem;
   line-height: 1;
}

/* Clear button styling */
#manageActivityModal .ts-wrapper .clear-button {
   cursor: pointer;
   opacity: 0.7;
   position: absolute;
   right: 2rem;
   top: 50%;
   transform: translateY(-50%);
   font-size: 1.2rem;
   color: #6c757d;
   transition: opacity 0.2s ease;
}

#manageActivityModal .ts-wrapper .clear-button:hover {
   opacity: 1;
   color: #dc3545;
}

/* Avatar Styling in Tom Select */
#manageActivityModal .avatar-xs {
   width: 22px;
   height: 22px;
   font-size: 0.65rem;
   display: inline-flex;
   align-items: center;
   justify-content: center;
}

/* Loading state */
#manageActivityModal .ts-control.loading {
   position: relative;
}

#manageActivityModal .ts-control.loading::after {
   content: '';
   position: absolute;
   right: 10px;
   top: 50%;
   transform: translateY(-50%);
   width: 16px;
   height: 16px;
   border: 2px solid #007bff;
   border-radius: 50%;
   border-top-color: transparent;
   animation: spin 0.6s linear infinite;
}

/* Empty message */
#manageActivityModal .ts-dropdown .no-results {
   padding: 1rem;
   text-align: center;
   color: #6c757d;
   font-style: italic;
}

/* Dropdown header */
#manageActivityModal .ts-dropdown-content {
   max-height: 250px;
   overflow-y: auto;
}

#manageActivityModal .ts-dropdown-content::-webkit-scrollbar {
   width: 6px;
}

#manageActivityModal .ts-dropdown-content::-webkit-scrollbar-track {
   background: #f1f1f1;
}

#manageActivityModal .ts-dropdown-content::-webkit-scrollbar-thumb {
   background: #c1c1c1;
   border-radius: 3px;
}

#manageActivityModal .ts-dropdown-content::-webkit-scrollbar-thumb:hover {
   background: #a8a8a8;
}

/* Responsive Design */
@media (max-width: 768px) {
   #manageActivityModal .modal-dialog {
      max-height: 95vh;
      margin: 0.5rem;
   }

   #manageActivityModal .modal-body {
      max-height: calc(95vh - 100px);
      padding: 1rem;
   }

   .wizard-steps {
      flex-wrap: wrap;
      gap: 1rem;
   }

   .wizard-step {
      flex: 0 0 calc(50% - 0.5rem);
   }

   .wizard-step-connector {
      display: none;
   }

   .step-label {
      font-size: 0.7rem;
      max-width: 80px;
   }

   .step-circle {
      width: 2.5rem;
      height: 2.5rem;
   }

   .step-circle i {
      font-size: 1rem;
   }

   .wizard-navigation {
      margin-left: -1rem;
      margin-right: -1rem;
      padding-left: 1rem;
      padding-right: 1rem;
   }

   .wizard-navigation .btn {
      min-width: 100px;
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
   }
}

@media (max-width: 576px) {
   #manageActivityModal .modal-dialog {
      max-height: 98vh;
      margin: 0.25rem;
   }

   #manageActivityModal .modal-body {
      max-height: calc(98vh - 80px);
      padding: 0.75rem;
   }

   .wizard-progress {
      padding: 1rem;
   }

   .wizard-step {
      flex: 0 0 calc(50% - 0.5rem);
   }

   .wizard-content {
      min-height: 250px;
   }

   .wizard-pane h6 {
      font-size: 0.95rem;
   }

   .wizard-navigation {
      margin-left: -0.75rem;
      margin-right: -0.75rem;
      padding-left: 0.75rem;
      padding-right: 0.75rem;
      padding-top: 1rem;
   }

   .scroll-to-top-btn {
      bottom: 80px;
      right: 15px;
      width: 36px;
      height: 36px;
   }

   .scroll-to-top-btn i {
      font-size: 1.1rem;
   }

   #stepIndicator {
      font-size: 0.85rem;
   }

   /* Tom Select Mobile Adjustments */
   #manageActivityModal .ts-control {
      min-height: 44px;
      font-size: 0.9rem;
   }

   #manageActivityModal .ts-dropdown {
      max-height: 200px;
   }

   #manageActivityModal .item {
      font-size: 0.8rem;
      padding: 0.2rem 0.4rem;
   }
}

/* Loading State */
.wizard-navigation .btn.loading {
   position: relative;
   color: transparent;
}

.wizard-navigation .btn.loading::after {
   content: '';
   position: absolute;
   width: 1rem;
   height: 1rem;
   top: 50%;
   left: 50%;
   margin-left: -0.5rem;
   margin-top: -0.5rem;
   border: 2px solid #fff;
   border-radius: 50%;
   border-top-color: transparent;
   animation: spin 0.6s linear infinite;
}

@keyframes spin {
   to { transform: rotate(360deg); }
}

/* Input Group Enhancements */
.input-group-text {
   background-color: #e9ecef;
   border: 1px solid #ced4da;
   font-weight: 500;
}

/* Small Text */
small.text-muted {
   font-size: 0.8rem;
   margin-top: 0.25rem;
   display: inline-block;
}

/* Form Check (Checkbox) */
.form-check {
   padding-left: 1.5rem;
}

.form-check-input {
   margin-top: 0.3rem;
}

.form-check-label {
   font-weight: 400;
   color: #495057;
}

/* Transitions for smooth UX */
* {
   transition: opacity 0.2s ease, visibility 0.2s ease;
}

/* Scroll Indicator Shadow */
#manageActivityModal .modal-body.has-scroll::before {
   content: '';
   position: sticky;
   top: 0;
   left: 0;
   right: 0;
   height: 10px;
   background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, transparent 100%);
   z-index: 4;
   pointer-events: none;
   opacity: 0;
   transition: opacity 0.3s ease;
}

#manageActivityModal .modal-body.scrolled::before {
   opacity: 1;
}

/* Scroll Indicator at Bottom */
#manageActivityModal .modal-body.has-more-content::after {
   content: '';
   position: sticky;
   bottom: 0;
   left: 0;
   right: 0;
   height: 20px;
   background: linear-gradient(to top, rgba(0,0,0,0.05) 0%, transparent 100%);
   z-index: 4;
   pointer-events: none;
   margin-top: -20px;
}

/* Scroll to Top Button */
.scroll-to-top-btn {
   position: fixed;
   bottom: 120px;
   right: 30px;
   z-index: 1000;
   width: 40px;
   height: 40px;
   border-radius: 50%;
   padding: 0;
   display: flex;
   align-items: center;
   justify-content: center;
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
   transition: all 0.3s ease;
   animation: fadeInUp 0.3s ease;
}

.scroll-to-top-btn:hover {
   transform: translateY(-3px);
   box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.scroll-to-top-btn i {
   font-size: 1.25rem;
}

/* Step Indicator in Navigation */
#stepIndicator {
   display: inline-block;
   font-weight: 500;
}

#currentStepNum {
   font-weight: 700;
   color: #007bff;
   font-size: 1.1em;
}

/* Print Styles */
@media print {
   .wizard-progress,
   .wizard-navigation {
      display: none;
   }

   .wizard-pane {
      display: block !important;
   }

   #manageActivityModal .modal-body {
      max-height: none !important;
      overflow: visible !important;
   }
}
</style>