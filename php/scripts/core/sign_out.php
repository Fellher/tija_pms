<?php
/**
 *
 *
 */
session_start();
$base = "../../../";
set_include_path($base);

include 'php/includes.php';

if ($isValidUser) {
  if (Core::end_session($sessionID, $DBConn)) {
    $messages[] = array('Text'=>'You were successfully logged out.',
                        'Type'=>'success');
    unset($_SESSION['SessionID']);
  } else {
    $messages[] = array('Text'=>'There was an error. Please contact customer support.',
                        'Type'=>'danger');
  }
} else {
  if (isset($_SESSION['SessionID'])) {
    unset($_SESSION['SessionID']);
    $messages[] = array('Text'=>'You were successfully logged out.',
                        'Type'=>'success');
  }
}
session_destroy();
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/");
?>