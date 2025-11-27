<!DOCTYPE HTML>
<?php
session_start();
$base = '../';
set_include_path($base);

include "php/includes.php";

$styles = array('main');

$hasScripts = true;
$scripts = array();
$keywords = array();
$Description='';

$s = '';
$getString = '';
if (isset($_GET['s'])) {
	$s = Utility::clean_string($_GET['s']);
	// Ensure $s is a string (safety check)
	$s = is_string($s) ? $s : '';
	$getString .= "?s={$s}";
}
$ss = '';
if (isset($_GET['ss'])) {
	$ss = Utility::clean_string($_GET['ss']);
	// Ensure $ss is a string (safety check)
	$ss = is_string($ss) ? $ss : '';
	$getString .= "&ss={$ss}";
}
$sss = '';
if (isset($_GET['sss'])) {
	$sss = Utility::clean_string($_GET['sss']);
	// Ensure $sss is a string (safety check)
	$sss = is_string($sss) ? $sss : '';
	$getString .= "&sss={$sss}";
}
if (isset($_GET['p'])) {
	$p = Utility::clean_string($_GET['p']);
	// Ensure $p is a string (safety check)
	$p = is_string($p) ? $p : 'home';
} else {
	$p = 'home';
}

if (!$getString) {
	$getString .= "?p={$p}";
} else {
	$getString .= "&p={$p}";
}
$baseUrl = $getString;

date_default_timezone_set('Africa/Nairobi');
switch ($s . '_' . $p) {
	default:
		// Handle config as either array or object
		$title = (is_array($config) && isset($config['siteName'])) ? $config['siteName'] : ((is_object($config) && isset($config->siteName)) ? $config->siteName : 'Site');
	break;
}


$orgDataID= (isset($_GET['orgDataID']) && !empty($_GET['orgDataID'])) ? Utility::clean_string($_GET['orgDataID']) : '';
$entityID = (isset($_GET['entityID']) && !empty($_GET['entityID'])) ? Utility::clean_string($_GET['entityID']) : '';

$orgDataID ?  $_SESSION['orgDataID'] = $orgDataID : '';
$entityID ? $_SESSION['entityID'] = $entityID : '';
/*if (!$isValidUser && $s != '' && $p != 'complete_registration') {
	header("location:{$base}html/?");*/

if (isset($_GET['insID']) && !empty($_GET['insID']) ) {
	$instanceID= Utility::clean_string($_GET['insID']);
}
$node= '';
$nodeID="";
?>
<html dir="ltr" lang="en-US" <?= $isValidUser ? "data-nav-layout='vertical' data-vertical-style='overlay' data-theme-mode='light' data-header-styles='light' data-menu-styles='light' data-toggled='close'" : "";?>>
	<?php
	include "includes/core/header_scripts.php";
	$CSSFilePath = File::path_join($base, 'assets', 'css', 'src', 'pages', $s, $ss, $sss, "{$p}.php");
	if (file_exists($CSSFilePath)) {
		include $CSSFilePath;
	} else {
		//create a file in the given path
		$fileCreate = File::create_directory_files($CSSFilePath, true);
		if($fileCreate){
			include $CSSFilePath;
		}
	} ?>
	<body <?= $isValidUser ? " class='stretched' data-menu-breakpoint='1200' " : "" ?>>
	<?php include "includes/core/switcher.php"; ?>
		<div id="page-spinner">
			<div class="spinner-border text-primary spinner-icon" role="status">
					<span class="visually-hidden">Loading...</span>
			</div>
		</div>

		<?php
		if($isValidUser) {?>
		 <!-- Loader -->
		 	<div id="loader" class="d-none" >
         	<img src="<?php echo $base ?>assets/img/media/loader.svg" alt="">
     		</div>
			<?php
		} ?>
		<!-- Document Wrapper
		============================================= -->
		<div id="wrapper" class="page">
			<script>
				let siteUrl = '<?php
					$siteURL = (is_array($config) && isset($config['siteURL'])) ? $config['siteURL'] : ((is_object($config) && isset($config->siteURL)) ? $config->siteURL : '');
					echo htmlspecialchars((string)$siteURL, ENT_QUOTES, 'UTF-8');
				?>';
			</script>
			<?php
			define('LOGIN_ALERT', "You need to be logged in as a valid user to access this page");
			function handleAlert($message) {
				Alert::info($message, true, array('fst-italic', 'text-center', 'font-18'));
		  }

			// ! Load the header here
			if($isValidUser) {
				// check if user is an employee
				$employeeDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
				if($employeeDetails  ){
					if(!isset($_SESSION['orgDataID']) || is_null($_SESSION['orgDataID'])) {
						$employeeDetails && $employeeDetails->orgDataID ? $orgDataID = $employeeDetails->orgDataID : '';
						$_SESSION['orgDataID'] = $employeeDetails->orgDataID ? $employeeDetails->orgDataID : null;
					}
					if(!isset($_SESSION['entityID']) || is_null($_SESSION['entityID'])) {
						$employeeDetails && $employeeDetails->entityID ? $entityID = $employeeDetails->entityID : '';
						$_SESSION['entityID'] = $employeeDetails->entityID ? $employeeDetails->entityID: null;
					}

				}
				// ! Load the header here
				include 	"includes/nav/header.php";
				// ! Load the side navigation here
				include "includes/nav/side_nav.php";
				$userID = $userDetails->ID;
				// ! Load the page contents here


				?>
				<div class="main-content app-content">
					<div class="container-fluid">
						<?php
			}
						include 'includes/flash_messages.php';
						//! Load Page contents here
						$pageURI = File::path_join('pages/', $s, $ss, $sss, $p . '.php') ;
						if (file_exists($pageURI)) {
							require_once $pageURI;
						} else {
							Alert::error('The page you are looking for cannot be found.');
						}
						$_SESSION['returnURL']= $getString;
						if (!isset($_SESSION['backUrl'])) {
							$_SESSION['backUrl'] = $getString;
						}

							// if($isValidUser) {
							// 	echo '<div class="container-fluid my-3">
							//  			<div class="card custom-card session card-body">';
							// 			var_dump($userDetails);
							// 	echo '</div>
							// 	</div>';

							// // // 	// ! Load the footer here

							// }
							//
						if($isValidUser) {?>

					</div>
				</div>
			<?php
				// ! Load General Footer here
						include "includes/nav/footer.php";

					} ?>
			</div>
			<?php
		// if (!$isValidUser) {
		// 	// header("location:{$base}html/");

		// }?>

		<!-- JavaScripts
		============================================= -->
      <?php // ! Load the footer scripts jQUERY  ?>
		<script src="https://code.jquery.com/jquery-3.6.4.js" integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E=" crossorigin="anonymous"></script>

		<?php
		if (!$isValidUser) {?>
			<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"  async defer></script>
			<?php
		}
		// ! Load the general javascript scripts
		include "includes/core/footer_scripts.php";

		// ! Load the page specific scripts
		// Ensure all variables are strings before passing to path_join (prevents stdClass conversion errors)
		$s = (string)$s;
		$ss = (string)$ss;
		$sss = (string)$sss;
		$p = (string)$p;
		$JSFilePath = File::path_join($base, 'assets', 'js', 'src', 'pages', $s, $ss, $sss, "{$p}.js");

		if (file_exists($JSFilePath)) {
			echo "<script src=\"{$JSFilePath}?t=".time()."\" ></script>";
		}
		$phpFilepath = File::path_join($base, 'assets', 'php', 'src', 'pages', $s, $ss, $sss, "{$p}.php");
		if (file_exists($phpFilepath)) {
			include $phpFilepath;
		}else{
			//create a file in the given path
			$fileCreate = File::create_directory_files($phpFilepath, true);
			if($fileCreate){
				include $phpFilepath;
			}
		}?>

<script>
			// JavaScript for handling the page spinner
			// This script will show a spinner when the page is loading and hide it once the page is fully loaded
			// and also when a form is submitted
			// This is a simple spinner implementation that can be used to indicate loading states
			// Ensure the spinner element is present in the DOM

			const spinnerElement = document.getElementById('page-spinner'); // Renamed for clarity

			function showSpinner() {
				if (spinnerElement) {
					spinnerElement.classList.add('show');
				}
			}

			function hideSpinner() {
				if (spinnerElement) {
					spinnerElement.classList.remove('show');
				}
			}

			// --- Scenario 1: Spinner on page load ---
			// Show spinner immediately if it exists
			showSpinner();

			// Hide spinner once the window is fully loaded
			window.addEventListener('load', () => {
				setTimeout(hideSpinner, 10); // Simulate a slight delay
			});

			// Fallback for DOMContentLoaded (optional, 'load' is generally better)
			document.addEventListener('DOMContentLoaded', () => {
				// Could call hideSpinner() here for an earlier hide, but 'load' ensures all resources.
			});

			// --- Scenario 2: Spinner on form submission ---
			document.querySelectorAll('form').forEach(form => {
				form.addEventListener('submit', showSpinner);
			});
		</script>
	</body>
</html>
