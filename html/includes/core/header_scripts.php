<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="keywords" content="<?php echo implode(', ', $keywords); ?>" />
    <meta name="robots" content="noarchive" />
    <meta name="description" content="" />
    <meta name="google-site-verification" content="C2Ue3aV6s_zNLc4_W4IQ2m3_9TAaK7czJ6hLrXDy_wU" />
    <title><?php echo $title; ?></title>

    <!-- Font Imports -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

        <!-- Favicon
    ======================================= -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo "{$base}assets/img/favicon_io" ?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo "{$base}assets/img/favicon_io" ?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo "{$base}assets/img/favicon_io" ?>/favicon-16x16.png">
    <link rel="manifest" href="<?php echo "{$base}assets/img/favicon_io" ?>/site.webmanifest">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo "{$base}assets/css/custom.css" ?>?v=<?=time();?>">
    <?php
    if($isValidUser){?>

      <!-- Choices JS -->
      <!-- <script src="<?php  echo "{$base}" ?>assets/libs/choices.js/public/assets/scripts/choices.min.js"></script> -->

        <!-- Main Theme Js -->
      <script src="<?php  echo "{$base}" ?>assets/js/main.js"></script>

        <!-- Bootstrap Css -->
      <link id="style" href="<?php echo $base ?>assets/libs/bootstrap/css/bootstrap.css?v=<?= time() ?>" rel="stylesheet" >

        <!-- Style Css -->
      <link href="<?php echo $base ?>assets/css/styles.css" rel="stylesheet" >

          <!-- Icons Css -->
      <link href="<?php echo $base ?>assets/css/icons.css" rel="stylesheet" >

          <!-- Node Waves Css -->
      <link href="<?php echo $base ?>assets/libs/node-waves/waves.min.css" rel="stylesheet" >

        <!-- Simplebar Css -->
      <link href="<?php echo $base ?>assets/libs/simplebar/simplebar.min.css" rel="stylesheet" >

          <!-- Color Picker Css -->
      <link rel="stylesheet" href="<?php echo $base ?>assets/libs/flatpickr/flatpickr.min.css">
      <link rel="stylesheet" href="<?php echo $base ?>assets/libs/@simonwep/pickr/themes/nano.min.css">


          <!-- Choices Css -->
      <!-- <link rel="stylesheet" href="<?php echo $base ?>assets/libs/choices.js/public/assets/styles/choices.min.css"> -->

            <!-- javascript vector map -->
      <link rel="stylesheet" href="<?php echo $base ?>assets/libs/jsvectormap/css/jsvectormap.min.css">

          <!-- Swiper Css -->
      <link rel="stylesheet" href="<?php echo $base ?>assets/libs/swiper/swiper-bundle.min.css">

          <!-- Font Icons -->
      <link rel="stylesheet" href="<?php echo $base ?>assets/css/font-icons.css">
    <?php
    //!incoporate these later
    ?>
    <!-- Tom Select Css -->

    <link rel="stylesheet" href="<?= $base ?>assets/libs/tom-select/css/tom-select.bootstrap5.min.css?v=<?=time();?>">

    <!-- Time Attendance Enhanced CSS -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/time_attendance_enhanced.css?v=<?=time();?>">

    <script src="<?php echo $base ?>assets/js/tinymce/tinymce.min.js"></script>
    <?php
  } else {

    // If the user is logged in, load the user-specific CSS?>

      <!-- Main Theme Js -->
      <script src="<?php echo $base ?>assets/js/authentication-main.js"></script>

      <!-- Bootstrap Css -->
      <link id="style" href="<?php echo $base ?>assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" >

      <!-- Style Css -->
      <link href="<?php echo $base ?>assets/css/styles.min.css" rel="stylesheet" >

      <!-- Icons Css -->
      <link href="<?php echo $base ?>assets/css/icons.min.css" rel="stylesheet" >

  <?php
  }?>


</head>