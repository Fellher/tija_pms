<?php 
if(!$isValidUser){
   Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
   include "includes/core/log_in_script.php";
   return;
}

include_once "includes/scripts/time_attendance/home.php";

?> 