<?php
if(!$isValidUser){
   Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
   // redirect to login page`
   include "includes/core/log_in_script.php";
   return;
}
if($isValidAdmin || ($isAdmin && (!$userDetails->isEmployee || $userDetails->isEmployee == 'N')) ){
	header("location:{$base}html/?s=core&ss=admin&p=home");
}
include_once "includes/scripts/time_attendance/home.php";