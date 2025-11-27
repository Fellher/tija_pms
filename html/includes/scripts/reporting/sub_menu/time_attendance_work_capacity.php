<?php
// This is a new sub-menu reporting file for 'time_attendance_work_capacity.php'
// Add your content here.


$entityUsers = Employee::employees(array('entityID'=>$entityID, 'Valid'=>'y'), false, $DBConn);
//get the weeks of the current month
$weeksInMonth = Utility::month_calendar_weeks($month, $year);
// var_dump($weeksInMonth);

$startOfYear = date('Y-01-01');
$today = date('Y-m-d');
$allDaysYear = Utility::date_range($startOfYear, $today, "weekDay");

// var_dump($allDaysYear);

// get the number of days between 2 dates
$daysBetween = Utility::date_diff_in_days($startOfYear, $today);
// var_dump($daysBetween);
?>

<h4 > Employee hours versus work contract</h4>
<div class="table-responsive">
   <table class="table table-bordered table-xs table-striped" id="dataTable">
      <thead >
         <tr class="table-th-info">
            <th>&nbsp;</th>           
            <th scope="col" colspan="<?= count($weeksInMonth)*3 ?>" class="ps-2 text-center" > Current Month </th>
            <th scope="col" colspan="2" class="text-center"> Year to Date </th>
         </tr>
         <tr class="table-th-primary" >
            <th scope="col" class="ps-2" > PerSon/Case  </th>
            <?php
            if($weeksInMonth && is_array($weeksInMonth)) {
               foreach($weeksInMonth as $key =>$week) {
                  $weekStartDate = $week[0]['date'];
                  $weekEndDate = $week[count($week)-1]['date'];
                 
                  echo "<th scope='col' colspan='3' class='text-center'>Week {$key}<br>{$weekStartDate} to {$weekEndDate}</th>";
               }
            }?>
            <th scope="col">Work Hours </th>
            <th scope="col"> Expected Work Hours </th>
         </tr>
         <tr>
            <th scope="col" class="ps-2" > &nbsp;  </th>
            <?php
            $n=0;
            if($weeksInMonth && is_array($weeksInMonth)) {
               foreach($weeksInMonth as $key =>$week) {   
                  $n++;
                  $n % 2 == 0 ? $bgClass = "bg-light" : ($n % 2 == 1 ? $bgClass = "bg-success-subtle" : $bgClass = "");
                  // 3 % 3 == 0 ? $bgClass = "table-info" : (3 % 2 == 1 ? $bgClass = "table-secondary" : $bgClass = "table-primary");              
                  echo "<th scope='col' class='text-end {$bgClass}'> Work Hrs   </th>";
                  echo "<th scope='col' class='text-end {$bgClass}'> Capacity  </th>";
                  echo "<th scope='col' class='text-end {$bgClass}'> Expected Hrs  </th>";
               }
            }?>
            <th scope="col" class="text-end"> &nbsp; </th>
            <th scope="col" class="text-end"> &nbsp; </th>
         </tr>
      </thead>
      <tbody>
         <?php
         if($entityUsers && is_array($entityUsers)) {  
            foreach($entityUsers as $emp) {               
               echo "<tr>";
               echo "<td class='ps-2'>{$emp->employeeName} </td>";
               $totalWorkHrsMonth = 0;
               $totalExpectedHrsMonth = 0;
               $k=0;
               if($weeksInMonth && is_array($weeksInMonth)) {
                  foreach($weeksInMonth as $key =>$week) {  
                     $k++;
                     $k % 2 == 0 ? $bgClass = "bg-light" : ($k % 2 == 1 ? $bgClass = "bg-success-subtle" : $bgClass = "");
                     // calculate work hours for the week
                     $weekStartDate = $week[0]['date'];
                     $weekEndDate = $week[count($week)-1]['date'];
                     $workHoursWeek = TimeAttendance::project_tasks_time_logs_between_dates(array('employeeID'=>$emp->ID), $weekStartDate, $weekEndDate, false, $DBConn);
                     if($workHoursWeek && is_array($workHoursWeek) && count($workHoursWeek) > 0) {
                        $workHoursWeek = array_reduce($workHoursWeek, function($carry, $item) {
                           $wkhour = $item->taskDuration ? Utility::time_to_sec($item->taskDuration) : 0;
                           return $carry + $wkhour;
                        }, 0);
                     }
                     $workHoursWeek = $workHoursWeek ? $workHoursWeek : 0;
                     $totalWorkHrsMonth += $workHoursWeek;

                     // calculate expected hours for the week
                     // only consider weekDays (Mon-Fri)
                     $filteredDays = array_filter($week, function($date) {
                        return in_array(date('N', strtotime($date['date'])), [1, 2, 3, 4, 5]);
                     });
                     $expectedHrsWeek = count($filteredDays) * 8; // assuming 8 hours per work day
                     $totalExpectedHrsMonth += $expectedHrsWeek;
                     $workHoursWeekFormatted = $workHoursWeek ? Utility::format_time($workHoursWeek, ":", false) : "--:--";

                     echo "<td class='text-end {$bgClass} '>{$workHoursWeekFormatted}</td>";
                     echo "<td class='text-end {$bgClass}'>
                     " . 
                     (
                        $expectedHrsWeek > 0 ? 
                        number_format(
                           ($workHoursWeek ? $workHoursWeek : 0)  / ($expectedHrsWeek*3600) * 100, 2
                        ) . " %" 
                        : "--"
                     ) 
                     . "</td>";
                     echo "<td class='text-end {$bgClass}'>{$expectedHrsWeek}</td>";
                  }
               }
               // Year to date calculations
               // Work hours from start of the year to date
           
               $workHoursYTD = TimeAttendance::project_tasks_time_logs_between_dates(array('employeeID'=>$emp->ID), $startOfYear, $today, false, $DBConn);

               // var_dump($workHoursYTD);
               //get the timelogs for the user from start of the year to date
               if($workHoursYTD && is_array($workHoursYTD) && count($workHoursYTD) > 0) {
                  $workHoursYTD = array_reduce($workHoursYTD, function($carry, $item) {

                     $wkhour = $item->taskDuration ? Utility::time_to_sec($item->taskDuration) : 0;
                     return $carry + $wkhour;
                  }, 0);
                  // var_dump($workHoursYTD);
                  
               } 
               $workHoursYTD = $workHoursYTD ? $workHoursYTD : 0;

               // Expected hours from start of the year to date
               // Get all weekdays from start of the year to today
               //$allDaysYear
    
               //convert $workHoursYTD to hours from seconds
               $workHoursYTD =$workHoursYTD ? Utility::format_time($workHoursYTD, ":", false) : "--:--";
               // var_dump($allDaysYear);
               $expectedHrsYTD = count($allDaysYear) * 8; // assuming 8 hours per work day
               echo "<td class='text-end'>{$workHoursYTD}</td>";
               echo "<td class='text-end'>{$expectedHrsYTD} hrs</td>";
               echo "</tr>";
            }
         } else {
            echo "<tr><td colspan='" . (count($weeksInMonth)*2 + 3) . "' class='text-center'> No users found for this entity. </td></tr>";
         }  
         ?>
      </tbody>
   </table>
</div>