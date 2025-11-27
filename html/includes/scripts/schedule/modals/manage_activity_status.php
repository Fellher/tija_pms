<div class="activityStatusForm" >
   <div class="row">
      <input type="hidden" name="activityStatusID" class="form-control form-control-sm">
      <div class="form-group">
         <label for="activityStatusName"> Activity Status Name</label>
         <input type="text" name="activityStatusName" id="activityStatusName" class="form-control form-control-sm">
      </div>
      <div class="form-group">
         <label for="activityStatusDescription"> Activity status description</label>
         <textarea name="activityStatusDescription" id="activityStatusDescription" class="form-control form-control-sm borderless-mini"></textarea>
      </div>
   </div>
</div>
<?php
$config['activityStatus'] = array(
    (object)[
        "activityStatusID" => "1",
        "statusCode" => "NS-001",
        "statusName" => "Not Started",
        "statusDescription" => "Activity has not been initiated."
    ],
    (object)[
        "activityStatusID" => "2",
        "statusCode" => "IP-002",
        "statusName" => "In Progress",
        "statusDescription" => "Activity is currently being worked on."
    ],
    (object)[
        "activityStatusID" => "3",
        "statusCode" => "IR-003",
        "statusName" => "In Review",
        "statusDescription" => "Activity is under review."
    ],
    (object)[
        "activityStatusID" => "4",
        "statusCode" => "C-004",
        "statusName" => "Completed",
        "statusDescription" => "Activity has been completed."
    ],
    (object)[
        "activityStatusID" => "5",
        "statusCode" => "NA-005",
        "statusName" => "Needs Attention",
        "statusDescription" => "Activity requires attention."
    ],
    (object)[
        "activityStatusID" => "6",
        "statusCode" => "S-006",
        "statusName" => "Stalled",
        "statusDescription" => "Activity has stalled."
    ]
);?>