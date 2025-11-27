<div class="card custom-card">
   <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
         <h2 class="t300 font-20">Calendar</h2>
         <div class=" m-0">
            <a  class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week-1).'&year='.$year.'&uid='.$userID; ?>">
               <i class="fa-solid fa-circle-chevron-left"></i></a> <!--Previous week-->
            <span>Week <?php echo $dt->format('W') ?></span>
            <a class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>"><i class="fa-solid fa-circle-chevron-right"></i></a> <!--Next week-->
            <a href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>" class="btn btn-white border"> Today</a>
         </div>
         <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
               <?php echo $dt->format('F Y') ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
               <?php
               for ($i=0; $i<12; $i++) {
                  $monthDate = date('F Y', strtotime("+$i month", strtotime($dt->format('Y-m-01'))));
                  $yearli = date('Y', strtotime($monthDate));
                  $monthli = date('m', strtotime($monthDate));
                  echo "<li><a class='dropdown-item' href='{$base}html/?s={$s}&ss={$ss}&p={$p}&month={$monthli}&year={$yearli}&uid={$userID}'>{$monthDate}</a></li>";
               }?>
            </ul>
         </div>
      </div>

   <div class="day-view">
      <div class="time-intervals" style="height: 100vh; overflow-y: scroll;">
         <?php 
         for ($i = 0; $i < 48; $i++) { // 48 intervals for 30 minutes each
            $intervalStart = strtotime($dt->format('Y-m-d') . ' + ' . ($i * 30) . ' minutes');
            $intervalEnd = $intervalStart + 1800; // 30 minutes * 60 seconds
            $activityFound = false;
            foreach ($activities as $activity) {
               // var_dump($activity);
                $activityStart = strtotime($activity->activityStartTime);
                $activityEnd = $activity->activityDurationEndTime ? strtotime($activity->activityDurationEndTime) : $activityStart + 1800;
                if ($activityStart >= $intervalStart && $activityStart < $intervalEnd) {
                  // var_dump($activityStart, $intervalStart, $activityEnd, $intervalEnd);
                    $activityStart = date('H:i', $activityStart);
                  //   echo "<div class='time-interval d-flex align-items-top justify-content-between border-bottom bg-light' style='height: 50px;'>"; // 50px height for each interval
                  //   echo "<span>" . date('g:i A', $intervalStart) . " - " . $activityStart . "</span>";
                    $activityFound = true;
                    break;
                }
            }
            
            if ($activityFound) {
                echo "<div class='time-interval d-flex align-items-top justify-content-between border-bottom bg-light-blue pe-3' style='height: 50px;'>
                        <div>
                           <span>" . date('g:i A', $intervalStart) . " </span>
                           <span class='text-primary w-100 px-2'>
                                 {$activity->activityName}
                                 <span class='mx-2 text-dark'> ({$activity->clientName } ) </span>
                           
                           </span>
                        </div>
                     </div>";
            } else{
                echo "<div class='time-interval d-flex align-items-top justify-content-between border-bottom' style='height: 50px;'>";
                echo "<span>" . date('g:i A', $intervalStart) . "</span>";
                echo "</div>";
            }
         } ?>
      </div>
      <div class="events">
         <?php 
        
         if($activities){
            foreach ($activities as $activity) { ?>
               <?php $startTime = strtotime($activity->activityStartTime); 
               $endTime = $activity->activityDurationEndTime ?  strtotime($activity->activityDurationEndTime): strtotime($activity->activityStartTime) + 1800  ; 
                $duration = ($endTime - $startTime) / 1800; // Convert seconds to 30-minute intervals 
                 $startInterval = date('G', $startTime) * 2 + (date('i', $startTime) / 30); ?>
               <div class="event" style="height: <?php echo $duration * 50; ?>px; top: <?php echo $startInterval * 50; ?>px;">
                  <a href="#activity-<?php echo $activity->activityID; ?>" data-bs-toggle="modal" data-bs-target="#activity-<?php echo $activity->activityID; ?>">
                     <span><?php echo $activity->activityName; ?></span>
                  </a>
               </div>
            <?php }
            
         }
         ?>
      </div>
   </div>
   <?php foreach ($activities as $activity) { ?>
      <div class="modal fade" id="activity-<?php echo $activity->ID; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel"><?php echo $activity->name; ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                  <p><?php echo $activity->description; ?></p>
                  <p>Start Time: <?php echo date('F j, Y g:i A', strtotime($activity->startTime)); ?></p>
                  <p>End Time: <?php echo date('F j, Y g:i A', strtotime($activity->endTime)); ?></p>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               </div>
            </div>
         </div>
      </div>
   <?php } ?>
   </div>
</div>