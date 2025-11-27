<?php
if (isset($_GET['d']) && !empty($_GET['d']) && preg_match($config['ISODateFormat'], $_GET['d'])) {
   $DOF= Utility::clean_string($_GET['d']);
   $dt=date_create($DOF);
} else {		
   if (isset($_GET['year']) && isset($_GET['week'])) {	 
      $dt->setISODate($_GET['year'], $_GET['week']);
   } else {
      // $dt->setISODate($dt->format('o'), $dt->format('W'));
      $dt = new DateTime;
   }		
}

$DOF= $dt->format('Y-m-d');
// var_dump($DOF);
$year = $dt->format('o');
$week = $dt->format('W');	
$month = $dt->format('m');
$day = $dt->format('d');
// echo "<p class=''> Date of the first day of the week: {$DOF} </p>";
// echo "<p class=''> Year: {$year}, Week: {$week}, Month: {$month}, Day: {$day} </p>";

?>