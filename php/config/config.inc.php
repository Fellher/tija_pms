<?php
// Prevent multiple includes
if (defined('CONFIG_INC_PHP_LOADED')) {
    return;
}
define('CONFIG_INC_PHP_LOADED', true);

$config = array();

/**
 * Date regEx Configuration Settings
 * =============================*/
$config['ISOYearMonthFormat'] = '/^[0-9]{4}\-[0-9]{2}$/';
$config['ISODateFormat'] = '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/';
$config['ISODateTimeFormat']= '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} (2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9]):([0-5][0-9])/';
$config['DateFormat']    = '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/';
$config['DateFormatBrit']    = '/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/';

$config['TimeFormat'] = '/(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9]):([0-5][0-9])/';
$config['TimeFormatMini'] = '/(2[0-2]|[0][0-9]|1[0-9]):([0-5][0-9])/';

/*Email Settings
**************************/

/**
 * Microsoft 365 SMTP Configuration
 */
$config['siteEmail'] = 'felix.mauncho@skm.co.ke';
$config['userName']= 'felix.mauncho@skm.co.ke';
$config['emailPWS']='hxjjgpbmxxqzpmjv';
$config['emailHost']='smtp.office365.com';
$config['secondaryEmail']= 'felix.mauncho@sbsl.co.ke';
$config['emailPort']=587;

/**
 * SendGrid SMTP Configuration
 */
$config['siteEmail'] = 'felix.mauncho@skm.co.ke';
$config['userName']= 'felix.mauncho@skm.co.ke';
$config['emailPWS']='hxjjgpbmxxqzpmjv';
$config['emailHost']='smtp.office365.com';
$config['secondaryEmail']= 'felix.mauncho@sbsl.co.ke';
$config['emailPort']=587;

/**
 * Gmail SMTP Configuration
 */
$config['siteEmail'] = 'felix.mauncho@skm.co.ke';
$config['userName']= 'felix.mauncho@skm.co.ke';
$config['emailPWS']='hxjjgpbmxxqzpmjv';
$config['emailHost']='smtp.office365.com';
$config['secondaryEmail']= 'felix.mauncho@sbsl.co.ke';
$config['emailPort']=587;

/**
 * Mailgun SMTP Configuration
 */
$config['siteEmail'] = 'felix.mauncho@skm.co.ke';
$config['userName']= 'felix.mauncho@skm.co.ke';
$config['emailPWS']='hxjjgpbmxxqzpmjv';
$config['emailHost']='smtp.office365.com';
$config['secondaryEmail']= 'felix.mauncho@sbsl.co.ke';
$config['emailPort']=587;

/**
 * Namecheap SMTP Configuration
 */
$config['siteEmail'] = 'admin@tija.sbsl.co.ke';
$config['userName']= 'admin@tija.sbsl.co.ke';
$config['emailPWS']='Okioma$@65852545W!U^8';
$config['emailHost']='tija.sbsl.co.ke';
$config['secondaryEmail']= 'felix.mauncho@sbsl.co.ke';
$config['emailPort']=587;

// For Microsoft 365: Ensure SMTP AUTH is enabled in Admin Center
// If MFA is enabled, use an App Password instead of regular password
// $config['siteName'] = "SBSL - Survey Platform";


/*File Upload Settings
************************************************/
// Calculate root directory first
$config['rootDIR'] = dirname(__FILE__, 3) . '/'; // Root directory of the application

// Calculate base path if not already defined
// $base is typically a relative path from HTML directory to project root (e.g., '../')
if (!isset($base)) {
    // Default to relative path from HTML to root
    $base = '../';
}

$config['DataDir'] = $base . "data/uploaded_files/";
$config['uploadDir'] ="data/uploaded_files/";

$config['MaxUploadedFileSize'] = 10 * 1024 * 1024;
$config['ValidFileTypes'] = array(
	'application/pdf',
	'application/msword',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'application/gzip',
	'image/gif',
	'image/jpeg',
	'image/png',
	'application/vnd.ms-powerpoint',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	'application/vnd.rar',
	'application/x-tar',
	'text/plain',
	'application/vnd.ms-excel',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'application/zip',
	'application/x-zip-compressed',
	'application/x-zip',
	'application/vnd.ms-excel',
	'text/csv',
);

// allowedFileExtensions: ["zip", "rar", "gz", "tgz", "txt", "xls", "jpg", "docx", "xlsx", "pdf", "doc"],

/*Time and timezone configuration Setttings
*********************************************/
$currentDateTime= new DateTime();
$timezone = new DateTimeZone('Africa/Nairobi');
$currentDateTime->setTimezone($timezone);
$dt=$currentDateTime;
$config['currentDateTime'] = $currentDateTime;
$config['today'] = $currentDateTime;
$config['timeUnits'] =   array(array('key'=>'mins', 'value'=>'Minutes'),array('key'=>'hrs', 'value'=>'Hours'),array('key'=>'day(s)', 'value'=>'Day(s)'),array('key'=>'wks', 'value'=>'weeks'),array('key'=>'months', 'value'=>'Months'));

$config['currentDateTimeFormated'] = $config['currentDateTime']->format("Y-m-d H:i:s");
$config['currentDateTimeFormatted'] = $config['currentDateTime']->format("Y-m-d H:i");
$config['currentDate'] = $config['currentDateTime']->format("Y-m-d");
/**
 * Time period configuration constants
 * ====================================*/
 $config['officialStartTime'] = 28000;
 $config['officialStopTime'] = 61200;
 $config['officialStopTimeSat'] = 50400;

 $config['stdWeekHours40']= 144000;
 $config['mandaySeconds']= (8*3600);
 $config['weekTime']=  162000;
 $config['workDayTimeSat'] = 18000;

/*Hour Setup */
$config['dayHour'] = 3600;
$config['weekHour'] = 3600*6;
$config['weekhour_sat'] = 3600*5;


/**
 * Site configuration Core Setings
 * **********************************/
//check if localhost, if so, use the offline root
if (preg_match('/localhost/', $_SERVER['HTTP_HOST'])) {
	$config['siteRoot'] ='sbsl.tija.sbsl.co.ke';
} else {
	$config['siteRoot'] ='pms.sbsl.co.ke';
}
$config['siteRoot'] ='sbsl.tija.sbsl.co.ke';
$config['siteName'] = "Tija Practice Management System";
$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
if ((preg_match('/localhost/', $_SERVER['HTTP_HOST'])) || (preg_match('/liveprojects/', $_SERVER['HTTP_HOST'])) ){
		$base_url.="/{$config['siteRoot']}";
}
// var_dump($base_url);

//if base_url does not end with /, add it
if (substr($base_url, -1) !== '/') {
    $base_url .= '/';
}
$config['siteURL']= $base_url;
$config['DataDirURL'] = $config['siteURL'] . 'data/uploaded_files/';

$config['selectionsNo'] = 3;


/*========================================
RECAPTCHA Configurations
=======================================*/

//check if localhost, if so, use the offline keys
if (preg_match('/localhost/', $_SERVER['HTTP_HOST'])) {
	$config['siteKey'] ="6LdbKeAUAAAAAOjo-17n853r38Uk-B9JzrQOHTRb";
	$config['secretKey'] ="6LdbKeAUAAAAAC1a3HyWOjMsIsqYIcX3UwS41QiJ";
} else {
// online
	$config['siteKey'] ='6LcjITsaAAAAAE0vQ2I83eRl7nYtQyxCQsHa-IkX';
	$config['secretKey']='6LcjITsaAAAAAIMxKHbe53PtIzO8BJrReweMLw-j';
}




/**
 * Licensing Configuration arrays
 * ***************************************/
$config['licenceNoArray'] = array( (object)[ 'index'=>10, 'value'=>10],
									(object)[ 'index'=>25, 'value'=>25],
									(object)[ 'index'=>50, 'value'=>50],
									(object)[ 'index'=>100, 'value'=>100],

								);

$config['licenceType'] = array( (object)['index'=>'paid', 'value'=>'Paid' ],
						(object)['index'=>'free', 'value'=>'Free' ],
						(object)['index'=>'extended', 'value'=>'Extended' ],

					 );


$config['licencePeriods'] = array( (object)['periodIndex'=>'weekLy', 'periodValue'=> "Weekly"],
							(object)['periodIndex'=>'monthly', 'periodValue'=> "Monthly"],
							(object)['periodIndex'=>'quarterly', 'periodValue'=> "Quarterly"],
							(object)['periodIndex'=>'semiannual', 'periodValue'=> "Semiannual"],
							(object)['periodIndex'=>'annual', 'periodValue'=> "Annual"]

						);
$config['permissionLevels'] = array( (object)['index'=>'readonly', 'value'=> "Read Only Privileges"],
							(object)['index'=>'update', 'value'=> "Update/Edit Privileges"],
							(object)['index'=>'create', 'value'=> "Create Privileges"],
							(object)['index'=>'all', 'value'=> "All Privileges"]


						);

$config['reviewPeriodTypes'] = array( (object)['index'=>'annual', 'value'=> "Annual Review"],
							(object)['index'=>'qualterly', 'value'=> "Quaterly Review"],
							(object)['semiannual'=>'create', 'value'=> "SemiAnnual Review"],
							(object)['custom'=>'all', 'value'=> "Custom PeriodReview"]
						);



$config['deadlineFilters'] =array((object)['key'=> "today", 'value'=> "Today"],
										(object)['key'=>'next7', 'value'=> "Next 7 days"],
										(object)['key'=>'next30', 'value'=> "Next 30 days"],
										(object)['key'=>'next90', 'value'=> "Next 90 days"],
										(object)['key'=>'next365', 'value'=> "Next 365 days"],
										(object)['key'=>'last7', 'value'=> "Last 7 days"],
										(object)['key'=>'last30', 'value'=> "Last 30 days"],
										(object)['key'=>'last90', 'value'=> "Last 90 days"],
										(object)['key'=>'last365', 'value'=> "Last 365 days"]
								);


$config['usersPermision'] = array( 	(object)['key'=>'noneUser', 'value'=> 'none'],
  										(object)['key'=>'editSubordinates', 'value'=> "Edit Subordinates"],
  										(object)['key'=>'addEditSubordinates', 'value'=>"Add and Edit Subordinates"],
  										(object)['key'=>'allUserPermisions', 'value'=>"All"],

  								);
$config['projectPermission'] = array( (object)['key'=>'projectMemberNoFinance', 'value'=> 'Project Member, No rights to own Financial Data'],
  										(object)['key'=>'projectMember', 'value'=>  'Project Member' ],
  										(object)['key'=>'projectManagerNoFinance', 'value'=> "project manager, No  rights to project's financial Data"],
  										(object)['key'=>'projectManager', 'value'=>"AProject Manager"],
  										(object)['key'=>'projetInOwnBusinessUnit', 'value'=>"Project in own business unit"],
  										(object)['key'=>'all', 'value'=>"All"]
  										);
 $config['customerPermission'] = array( (object)['key'=>'noneContacts', 'value'=> 'None'],
  										(object)['key'=>'readOnly', 'value'=>  'Read Only' ],
  										(object)['key'=>'edit', 'value'=> 'Edit'],
  										(object)['key'=>'all', 'value'=>"All"]
  										);


 $config['roundingOffParams'] = array(
									(object)["key"=>1, "value"=> '1 Minute'],
									(object)["key"=>5, "value"=> '5 Minute'],
									(object)["key"=>10, "value"=> '10 Minutes'],
									(object)["key"=>15, "value"=> '15 Minutes'],
									(object)["key"=>30, "value"=> '30 Minutes'],
									(object)["key"=>60, "value"=> '1 hour']

									);
$config['activitySegment'] = array(
									(object)["key"=>'sales', "value"=> 'Sales'],
									(object)["key"=>'project', "value"=> 'Project'],
									(object)["key"=>'task', "value"=> 'Task'],
									(object)["key"=>'activity', "value"=> 'Activity'],
									(object)["key"=>'businessDevelopment', "value"=> 'Business Development'],

									);
$config['activityStatus'] = array(
										(object)["key"=>'notStarted', "value"=> 'Not Started'],
										(object)["key"=>'inProgress', "value"=> 'in Progress'],
										(object)["key"=>'inReview', "value"=> 'In Review'],
										(object)["key"=>'completed', "value"=> 'Completed'],
										(object)["key"=>'needsAttention', "value"=> 'Needs Attention'],
										(object)["key"=>'stalled', "value"=> 'Stalled'],

										);



 $config['roundingOptions'] = array(
 									(object)["key"=>"no_rounding", "value"=>"No Rounding"],
									(object)["key"=>"round_up", "value"=>"Rounding Up"],
									(object)["key"=>"round_to_the_nearest", "value"=>" Rounding to the nearest"],
									(object)["key"=>"round_down", "value"=>"Rounding Down"]
									);

$config['personCategories'] = array(
									(object)["key"=>"men", "value"=>"Men"],
									(object)["key"=>"women", "value"=>"Women"],
									(object)["key"=>"children", "value"=>"Children"],
									(object)["key"=>"all", "value"=>"All"]
									);

$config['additionalDetails'] = '<h4 class="ls1 font-22 fw-medium">Additional Details</h4>
									<h4 class="text-primary">Continuous professional development</h4>
									<p>Upon completion of the course, CPD points will also be awarded to all trainees, which can then be redeemed from the Institute of Certified Public Accountants of Kenya (ICPAK).</p>

									<h4 class="text-primary">National Industrial Training Authority (NITA) Reimbursement</h4>
									<p>Please note that we are also registered with the National Industrial Training Authority (NITA). Our registration number is NITA/IT/IBTA/F/11.</p>
									<p>Participants who are registered levy contributors can apply to NITA for reimbursement of the fees. To qualify you should apply to NITA for approval prior to the date of the conference.</p>'
									;
$config['activityPriority'] =array(
								(object)["key"=>"high", "value"=>"High"],
								(object)["key"=>"medium", "value"=>"Medium"],
								(object)["key"=>"low", "value"=>"Low"],
								);

$config['checklistStatusType'] = array(
									(object)["key"=>"checkList", "value"=>"Checklist"],
									(object)["key"=>"checkListItem", "value"=>"Checklist Item"],
									(object)["key"=>"checkListItemGroup", "value"=>"Checklist Item Group"],

								);
$config['clientRelationshipTypes'] = array(
									(object)["key"=>"clientLiaisonPartner", "value"=>"Client Liaison Partner", "level"=>"1"],
									(object)["key"=>"engagementPartner", "value"=>"Engagement Partner", "level"=>"2"],
									(object)["key"=>"manager", "value"=>"Manager", "level"=>"3"],
									(object)['key'=>'AssociateSeniorAssociate', "value"=>"Associate 2 or Senior Associate", "level"=>"4"],
									(object)['key'=>'associateIntern', "value"=>"Associate Intern", "level"=>"5"],
									(object)['key'=>'all', "value"=>"All", "level"=>"6"]
								);

$config['notificationSegments'] = array(
									(object)["key"=>"system", "value"=>"System"],
									(object)["key"=>"project", "value"=>"Project"],
									(object)["key"=>"task", "value"=>"Task"],
									(object)["key"=>"meeting", "value"=>"Meeting"],
									(object)["key"=>"sales", "value"=>"Sales"],
									(object)["key"=>"support", "value"=>"Support"],
									(object)["key"=>"activity", "value"=>"Activity"],
									(object)["key"=>"client", "value"=>"Client"],
									(object)["key"=>"invoice", "value"=>"Invoice"],
									(object)["key"=>"estimate", "value"=>"Estimate"],
									(object)["key"=>"expense", "value"=>"Expense"],
									(object)["key"=>"time", "value"=>"Time"],
									(object)["key"=>"payment", "value"=>"Payment"],
									(object)["key"=>"userManagement", "value"=>"User Management"],
									(object)["key"=>"document", "value"=>"Document"],
									(object)["key"=>"leave", "value"=>"leave"],
									(object)["key"=>"absence", "value"=>"Absence"],
									(object)["key"=>"client_relationships", "value"=>"Client Relationships"],
								);

$config['assignmentStatus'] = array(
									(object)["key"=>"pending", "value"=>"Pending"],
									(object)["key"=>"accepted", "value"=>"Accepted"],
									(object)["key"=>"rejected", "value"=>"Rejected"],
									(object)["key"=>"assigned", "value"=>"Assigned"],
									(object)["key"=>"edit-required", "value"=>"Edit Required"],
									(object)["key"=>"suspended", "value"=>"Suspended"],
								);




include __DIR__ . '/db.config.inc.php';

/**
 * Configuration Merger Functions
 * ===============================
 * Functions to merge and manage multiple configuration arrays
 */

/**
 * Merge Configuration Arrays
 *
 * Recursively merges configuration arrays, with the second array taking precedence
 * over the first array for conflicting keys.
 *
 * @param array $config1 Base configuration array
 * @param array $config2 Configuration array to merge (takes precedence)
 * @return array Merged configuration array
 */
if (!function_exists('mergeConfig')) {
    function mergeConfig($config1, $config2) {
        $result = $config1;

        foreach ($config2 as $key => $value) {
            if (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                $result[$key] = mergeConfig($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

/**
 * Get Configuration Value with Dot Notation
 *
 * Retrieves a configuration value using dot notation from the global config.
 * Supports nested array access and default values.
 *
 * @param string $key Configuration key in dot notation (e.g., 'projectPlan.features.phaseManagement')
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value or default
 */
if (!function_exists('getConfig')) {
    function getConfig($key = null, $default = null) {
        global $config;

        if ($key === null) {
            return $config;
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

/**
 * Set Configuration Value with Dot Notation
 *
 * Sets a configuration value using dot notation in the global config.
 * Creates nested arrays as needed.
 *
 * @param string $key Configuration key in dot notation
 * @param mixed $value Value to set
 * @return bool True on success, false on failure
 */
if (!function_exists('setConfig')) {
    function setConfig($key, $value) {
        global $config;

        $keys = explode('.', $key);
        $configRef = &$config;

        foreach ($keys as $k) {
            if (!isset($configRef[$k]) || !is_array($configRef[$k])) {
                $configRef[$k] = [];
            }
            $configRef = &$configRef[$k];
        }

        $configRef = $value;
        return true;
    }
}

/**
 * Load Project Plan Configuration
 *
 * Loads and merges the project plan configuration into the global config.
 * This function should be called after the project plan config file is included.
 */
if (!function_exists('loadProjectPlanConfig')) {
    function loadProjectPlanConfig() {
        global $config, $projectPlanConfig;

        if (isset($projectPlanConfig)) {
            // Merge project plan config into global config under 'projectPlan' key
            $config['projectPlan'] = $projectPlanConfig;

            // Also merge some common settings at the root level for backward compatibility
            if (isset($projectPlanConfig['validation'])) {
                $config['projectPlanValidation'] = $projectPlanConfig['validation'];
            }

            if (isset($projectPlanConfig['performance'])) {
                $config['projectPlanPerformance'] = $projectPlanConfig['performance'];
            }

            if (isset($projectPlanConfig['security'])) {
                $config['projectPlanSecurity'] = $projectPlanConfig['security'];
            }
        }
    }
}

/**
 * Check if Project Plan Feature is Enabled
 *
 * Convenience function to check if a project plan feature is enabled.
 *
 * @param string $feature Feature name
 * @return bool True if enabled, false otherwise
 */
if (!function_exists('isProjectPlanFeatureEnabled')) {
    function isProjectPlanFeatureEnabled($feature) {
        return getConfig("projectPlan.features.{$feature}", false);
    }
}

/**
 * Get Project Plan UI Setting
 *
 * Convenience function to get a project plan UI setting value.
 *
 * @param string $setting UI setting name
 * @param mixed $default Default value
 * @return mixed UI setting value or default
 */
if (!function_exists('getProjectPlanUISetting')) {
    function getProjectPlanUISetting($setting, $default = null) {
        return getConfig("projectPlan.ui.{$setting}", $default);
    }
}

/**
 * Get Project Plan Display Setting
 *
 * Convenience function to get a project plan display setting value.
 *
 * @param string $setting Display setting name
 * @param mixed $default Default value
 * @return mixed Display setting value or default
 */
if (!function_exists('getProjectPlanDisplaySetting')) {
    function getProjectPlanDisplaySetting($setting, $default = null) {
        return getConfig("projectPlan.display.{$setting}", $default);
    }
}

/**
 * Validate Global Configuration
 *
 * Validates the global configuration array for required keys and valid values.
 *
 * @return array Validation results with errors and warnings
 */
if (!function_exists('validateGlobalConfig')) {
    function validateGlobalConfig() {
        global $config;

        $errors = [];
        $warnings = [];

        // Check required sections
        $requiredSections = ['siteName', 'siteURL', 'currentDateTime'];
        foreach ($requiredSections as $section) {
            if (!isset($config[$section])) {
                $errors[] = "Missing required configuration: {$section}";
            }
        }

        // Validate project plan config if it exists
        if (isset($config['projectPlan']) && function_exists('validateProjectPlanConfig')) {
            $projectPlanValidation = validateProjectPlanConfig();
            if (!$projectPlanValidation['valid']) {
                $errors = array_merge($errors, $projectPlanValidation['errors']);
            }
            $warnings = array_merge($warnings, $projectPlanValidation['warnings']);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
}

// Include project plan configuration
if (file_exists(__DIR__ . '/../../html/includes/scripts/projects/config/project_plan_config.php')) {
    include __DIR__ . '/../../html/includes/scripts/projects/config/project_plan_config.php';
    loadProjectPlanConfig();
}

// Validate global configuration
$globalConfigValidation = validateGlobalConfig();
if (!$globalConfigValidation['valid']) {
    error_log('Global Configuration Validation Failed: ' . implode(', ', $globalConfigValidation['errors']));
}

// Log warnings if any
if (!empty($globalConfigValidation['warnings'])) {
    error_log('Global Configuration Warnings: ' . implode(', ', $globalConfigValidation['warnings']));
}

?>