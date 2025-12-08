<?php
  global $config;

  //check if localhost, if so, use the offline database
  if (preg_match('/localhost/', $_SERVER['HTTP_HOST'])) {
    $config['DB'] = 'sbsl_pms_tija_re';
    $config['DBHost'] = 'localhost';
    $config['DBUser'] = 'sbsl_user';
    $config['DBPassword'] = '$@alfr0nzE6585';

  } else {
    $config['DB'] = 'sbsl_pms';
    $config['DBHost'] = 'localhost';
    $config['DBUser'] = 'sbsl_tija';
    $config['DBPassword'] = '$@alfr0nzE6585';
  }
  // //demo
  // $config['DB'] = 'pms_sbsl_deploy';
  // //live
  // $config['DB'] = 'sbsl_pms_tija_re';

?>