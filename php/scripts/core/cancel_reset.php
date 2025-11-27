<?php
/**
 *
 *
 */
session_start();
$base = '../../../';
set_include_path($base);


include 'php/class_autoload.php';

include 'php/config/config.inc.php';

include 'php/scripts/db_connect.php';

var_dump($_POST);
$success = false;
$errors=array();
$changes= array();
$DBConn->begin();

if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = trim(Form::validate_email($_POST['email']));
    if ($email) {
        $userDetails= Core::user(array('Email'=>$email), true, $DBConn);

        var_dump($userDetails);

        if ($userDetails) {
            if ($userDetails->NeedsToChangePassword === 'y') {
                $changes['needsToChangePassword']  ='n';
            }

            if ($userDetails->Valid === 'n') {
                $changes['Valid'] = 'y';
            }

            if ($userDetails->active === 'N') {
               $changes['active']= 'Y';
            }

            if (count($errors) ===0) {
                if ($changes) {
                    if (!$DBConn->update_table('people', $changes, array('ID'=> $userDetails->ID))) {
                        $errors[]= "Unable to upddate user details";
                    } else {
                        $success= true;
                        $returnURL = "p=home";
                    } 


                }               
            }
        } else {
            $returnURL = "p=home";
        }
    }
    
} else {
     $returnURL = "p=home";
}


if (count($errors) == 0) {
    $DBConn->commit();

} else {

    var_dump($errors);
    $DBConn->rollback();
    $messages = array_map(function ($error) { return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
    
   $_SESSION['FlashMessages'] = serialize($messages); 
}

header("location:{$base}html/?{$returnURL}");
?>


