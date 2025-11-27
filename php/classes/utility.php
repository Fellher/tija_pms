<?php 

/**
 * SBSL utility class
 * @author 		felix mauncho 
 * @email		felixmauncho@gmail.com
 * @copyright 	2023- 2024
 * 
 * */ 

class Utility {

	/**Period Classess
	 * Date Time 
	 * Days in month 
	 * Week 
	 * Time Date  conversions 
	 *  
	*/

	/*Week Array
	===================*/
public static function week_array($year, $week) {
	if ($year && $week) {
		$weekArray = array();
		$weekStart=new DateTime;
		$weekStart->setISODate($year, $week);	
		do {											
			$weekArray[] =$weekStart->format('Y-m-d');
			$weekStart->modify('+1 day');
		} while ($week == $weekStart->format('W'));

		return $weekArray;
	} else {
		return false;
	}


}

/**
 * Sanitize input from a form
 * @param string $input The input string to sanitize
 * @return string The sanitized input
 */
public static function sanitize_input($input) {
	// Trim whitespace
	$input = trim($input);
	// Remove HTML tags
	$input = strip_tags($input);
	// Escape special characters
	$input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
	return $input;
}

/**
 * Sanitize input from a rich text editor
 * @param string $input The input string to sanitize
 * @return string The sanitized input
 */
public static function sanitize_rich_text_input($input) {
	// Trim whitespace
	$input = trim($input);
	
	// Allow certain HTML tags (you can customize this list)
	$allowedTags = '<p><a><b><i><strong><em><ul><ol><li><br><blockquote><img>';
	
	// Strip unwanted tags and escape special characters
	$input = strip_tags($input, $allowedTags);
	$input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
	
	return $input;
}





// Format float to currency
public static function  formatToCurrency($number, $currencySymbol = '', $decimalPlaces = 2, $thousandsSeparator = ',', $decimalPoint = '.') {
	// Format the number with the specified settings
	$formattedNumber = number_format($number, $decimalPlaces, $decimalPoint, $thousandsSeparator);
	
	// Append the currency symbol
	return "{$currencySymbol} {$formattedNumber}";
}

// public static function array_compare($array1, $array2) {
// 	$diff = array();


public static function generate_timestamp() {
	$currentDateTime= new DateTime();
	$timezone = new DateTimeZone('Africa/Nairobi');
	$currentDateTime->setTimezone($timezone);
	$timestamp = $currentDateTime->format('Y-m-d H:i:s');
	return $timestamp;
}
public static function date_format($date, $formatType= "midDate") {
		$rowDate=date_create($date);
		// var_dump($formatType);
	
		if (!empty($formatType)) {
		
			switch ($formatType) {
				case 'long':
					$date=$rowDate->format('l\, d F Y');
					break;
				case 'midDate':
					$date=$rowDate->format('D\, d M Y');
					break;
				
				case 'short':
					$date=$rowDate->format('D\, d M');
					break;

				case "mini":
					$date=$rowDate->format('d M Y');
					break;
					
				case 'miniNoYear':
					$date=$rowDate->format('d M');
					break;

				case 'shortStr':
					$date=$rowDate->format('D\, d-m-Y');
					break;
				case 'miniDate':
					$date=$rowDate->format('d M Y');
					break;
				case 'miniDateTime':
					$date=$rowDate->format('d M Y H:i:s');
					break;
				case 'minidayMonth':
					$date=$rowDate->format('d M');
					break;
				case 'miniMonthYear':
					$date=$rowDate->format('M Y');
					break;
					
				case 'numeric':
					$date=$rowDate->format('d-m-Y');
					break;	
				case 'british':
					$date=$rowDate->format('d/m/Y');
					break;	
					
				default:
					$date=$rowDate->format('d/m/Y');
					break;		
				
			} 
			return $date ;
		}
		
	}

	public static function add_period_to_date ($dateString, $periodUnit, $unitNumber, $config) {
		if (preg_match($config['ISODateFormat'], Utility::clean_string($dateString)) ){
			$dt = date_create($dateString);
			var_dump($dt);
			$period = 'day';
			switch ($periodUnit) {
				case 'weekLy':
					$period= 'weeks';
					break;
				case 'monthly':
					$period = 'months';
					break;
				case 'quarterly':
					$period = 'months';
					$unitNumber = '+3';
					break;
				case 'semiannual':
					$period = 'months';
					$unitNumber = '+6';
					break;
				case 'annual':
					$period = 'years';
					$unitNumber = '+1';
					break;			
				
			}
			$dt->modify("{$unitNumber} {$period}");
			var_dump($dt);
			return $dt;			
		} 
			return false;
		
	}
	public static function calculate_days_in_month( $month, $year){
		if ($month && $year) {
			$day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			return $day_count;
		} 
		return false;
	}

	public static function returnURL ($sessLink, $defaultLink) {
		if (isset($sessLink) && !empty($sessLink)) {
				$returnURL= Utility::clean_string($sessLink);			
		} else {	
				$returnURL= $defaultLink;
		}
				return $returnURL;
	}

	public static function month_calendar_dates ($month, $year, $type ='' ){
		if ($month && $year) {
			$day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			for ($dayVal=1; $dayVal < $day_count ; $dayVal++) { 
				$date = $year.'-'.$month.'-'.$dayVal; //format date
				$dateObj = date_create($date);
				$date= $dateObj->format('Y-m-d');
				$get_name = date('l', strtotime($date)); //get week day
				$day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

				if ($type === 'weekDay') {
					if($day_name != 'Sun' && $day_name != 'Sat'){
								$workdays[] = array(
									'date'=> $date, 
									'day'=> $day_name, 
									'weekDayNumber'=> date('N', strtotime($date)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
									"weekNumber"=> date('W', strtotime($date)), // ISO-8601 week number of year, weeks starting on Monday
									'dateObject'=> date_create($date),
									

								);
							}
				} elseif ($type=='workWeek') {
					if($day_name != 'Sun'){
								$workdays[] = array(
									'date'=> $date, 
									'day'=> $day_name, 
									'weekDayNumber'=> date('N', strtotime($date)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
									"weekNumber"=> date('W', strtotime($date)), // ISO-8601 week number of year, weeks starting on Monday
									'dateObject'=> date_create($date),
								);
							}
				} else {
					 $workdays[] = array(
						'date'=> $date, 
						'day'=> $day_name, 
						'weekDayNumber'=> date('N', strtotime($date)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
						"weekNumber"=> date('W', strtotime($date)), // ISO-8601 week number of year, weeks starting on Monday
						'dateObject'=> date_create($date)
					);
				}
			}
			return is_array($workdays) ?  $workdays : false; 
		}
		return false;
	}
	//function to get the weeks in a month with array of all the days of the weeks
	public static function month_calendar_weeks ($month, $year, $type='weekDay') {
		if ($month && $year) {
			$day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$weeksArray = array();
			for ($dayVal=1; $dayVal <= $day_count ; $dayVal++) { 
				$date = $year.'-'.$month.'-'.$dayVal; //format date
				$dateObj = date_create($date);
				$date= $dateObj->format('Y-m-d');
				$get_name = date('l', strtotime($date)); //get week day
				$day_name = substr($get_name, 0, 3); // Trim day name to 3 chars
				$weekNumber= date('W', strtotime($date)); // ISO-8601 week number of year, weeks starting on Monday
				if ($type === 'weekDay') {
					if($day_name != 'Sun' && $day_name != 'Sat'){
						$weeksArray[$weekNumber][] = array(
							'date'=> $date, 
							'day'=> $day_name, 
							'weekDayNumber'=> date('N', strtotime($date)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
							"weekNumber"=> $weekNumber, // ISO-8601 week number of year, weeks starting on Monday
							'dateObject'=> date_create($date)
						);
					}
				} elseif ($type=='workWeek') {
					if($day_name != 'Sun'){
						$weeksArray[$weekNumber][] = array(
							'date'=> $date, 
							'day'=> $day_name, 
							'weekDayNumber'=> date('N', strtotime($date)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
							"weekNumber"=> $weekNumber, // ISO-8601 week number of year, weeks starting on Monday
							'dateObject'=> date_create($date)
						);
					}
				} else {
					$weeksArray[$weekNumber][] = array(
						'date'=> $date, 
						'day'=> $day_name, 
						'weekDayNumber'=> date('N', strtotime($date)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
						"weekNumber"=> $weekNumber, // ISO-8601 week number of year, weeks starting on Monday
						'dateObject'=> date_create($date)
					);
				} 
			}

			return is_array($weeksArray) ?  $weeksArray : false; 
		}
		return false;
	}

	public static function date_range( $startDate, $endDate, $type ='' ) {
		if ($startDate && $endDate) {
			$begin = new DateTime($startDate);
			$end = new DateTime($endDate);
			$end = $end->modify( '+1 day' ); 

			$interval = new DateInterval('P1D');
			$dateRange = new DatePeriod($begin, $interval ,$end);
			// var_dump($dateRange);
			//get all dates in the dateRange
			
			$dayCount = 0;
			foreach ($dateRange as $date) { 
				// echo "<br /> ".$dayCount++;
				// var_dump($date);
				$dateFormatted= $date->format("Y-m-d");
				// var_dump($dateFormatted);
				$get_name = date('l', strtotime($dateFormatted)); //get week day
				$day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

				if ($type === 'weekDay') {
					if($day_name != 'Sun' && $day_name != 'Sat'){
						$allDates[] = array(
											'date'=> $dateFormatted, 
											'day'=> $day_name, 
											'weekDayNumber'=> date('N', strtotime($dateFormatted)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
											"weekNumber"=> date('W', strtotime($dateFormatted)), // ISO-8601 week number of year, weeks starting on Monday
											'dateObject'=> date_create($dateFormatted),
											

										);
					}
				} elseif ($type=='workWeek') {
					if($day_name != 'Sun'){
						$allDates[] = array(
											'date'=> $dateFormatted, 
											'day'=> $day_name, 
											'weekDayNumber'=> date('N', strtotime($dateFormatted)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
											"weekNumber"=> date('W', strtotime($dateFormatted)), // ISO-8601 week number of year, weeks starting on Monday
											'dateObject'=> date_create($dateFormatted),
										);
					}
				} else {
					$allDates[] = array(
										'date'=> $dateFormatted, 
										'day'=> $day_name, 
										'weekDayNumber'=> date('N', strtotime($dateFormatted)), // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
										"weekNumber"=> date('W', strtotime($dateFormatted)), // ISO-8601 week number of year, weeks starting on Monday
										'dateObject'=> date_create($dateFormatted)
									);
				}
			}
			// var_dump('Total Days: ');
		
			// var_dump($allDates);
			return is_array($allDates) ?  $allDates : false;
		}	
		return false;
	}

	public static function date_diff_in_days($startDate, $endDate) {
		if ($startDate && $endDate) {
			$begin = new DateTime($startDate);
			$end = new DateTime($endDate);
			$end = $end->modify( '+1 day' ); 

			$interval = new DateInterval('P1D');
			$dateRange = new DatePeriod($begin, $interval ,$end);
			
			$dayCount = 0;
			foreach ($dateRange as $date) { 
				$dayCount++;
			}
			return $dayCount;
		}
		return false;
	}

	public static function time_to_sec($time){
		if ($time) {
			$timeSec=0;
			$timeArray = explode(":", $time);
			// var_dump($timeArray);
			if (count($timeArray) == 3) {
				$timeSec =($timeArray[0] * 3600) + ($timeArray[1] * 60)+$timeArray[2];	
			} elseif (count($timeArray) === 2) {
				$timeSec =($timeArray[0] * 3600) + ($timeArray[1] * 60);	
			}
			return $timeSec ? $timeSec : false;
		}
	}
	public static function time_to_seconds($time){
		if ($time) {
			$timeSec=0;
			$timeArray = explode(":", $time);
			// var_dump($timeArray);
			if (count($timeArray) == 3) {
				$timeSec =($timeArray[0] * 3600) + ($timeArray[1] * 60)+$timeArray[2];	
			} elseif (count($timeArray) === 2) {
				$timeSec =($timeArray[0] * 3600) + ($timeArray[1] * 60);	
			}
			return $timeSec ? $timeSec : false;
		}
	}

	public static function format_time($t,$f=':', $s )  {
		// t = seconds, f = separator
		
		if ($s=== true) {
			$time = sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
		} else {
			$time =sprintf("%02d%s%02d%", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
		}
	  return $time;
	}
	public static function Time_to_decimal($time, $separator=":"){
		 $hms = explode($separator, $time);
		 // var_dump($hms);
		 $decimalTime=0;
		 if (count($hms) === 3) {
			$decimalTime = ($hms[0] + ($hms[1]/60) + ($hms[2]/3600));
		 } elseif(count($hms) === 2) {
			$decimalTime = ($hms[0] + ($hms[1]/60));
		 }
		 return round($decimalTime, 2) ;
	}


	public static function transform_hour_to_decimal($time){
		$time       = strtoupper($time);

		// $time       = str_replace('H', ':', $time);

		$tab        = explode(':', trim($time) );

		$hour       = strVal( $tab[0] );

		$minute     = ( strVal ( ( intval($tab[1]) * 100) / 60 ) ) ; 

		if ( $minute < 10 ) $minute = '0' . $minute;

		return floatval(  $hour .'.'.$minute ) ;
	}

	public static function timestring_to_sec($time){		
	    $hms = explode(":", $time);
	    // var_dump($hms);
	    if (count($hms) === 3) {
	    	 $timeDecimal= ($hms[0] + ($hms[1]/60) + ($hms[2]/3600));
	    } elseif (count($hms) === 2) {
	    	 $timeDecimal= ($hms[0] + ($hms[1]/60));
	    } else {
	    	$timeDecimal = ($hms[0]/3600);
	    }
	    return $timeDecimal;
	}

	public static function timestring_to_decimal($time){		
	    $hms = explode(":", $time);
	    // var_dump($hms);
	    if (count($hms) === 3) {
	    	 $timeDecimal= ($hms[0] + ($hms[1]/60) + ($hms[2]/3600));
	    } elseif (count($hms) === 2) {
	    	 $timeDecimal= ($hms[0] + ($hms[1]/60));
	    } else {
	    	$timeDecimal = ($hms[0]/3600);
	    }
	    return $timeDecimal; 
	}

	

	/*Time difference within one day*/
	public static function day_time_difference($startTime, $endTime) {

		$startTimeArray= explode(':', $startTime);
											
		$startTimeSec= ($startTimeArray[0] * 3600) + ($startTimeArray[1] * 60);

		
		$endTimeArray= explode(':', $endTime);
										
		$endTimeSec= ($endTimeArray[0] * 3600) + ($endTimeArray[1] * 60);

		$diff= (int)$endTimeSec - (int)$startTimeSec;

		return $diff > 0 ? $diff : false;

	}
	
	public static function secToTime ($sec, $type="") {

		if ($type === 'short') {
			$timeString= sprintf("%02s:%'02s:%02s\n", intval($sec/60/60), abs(intval(($sec%3600) / 60)), abs($sec%60));
		} elseif($type === 'nosec'){
			$timeString= sprintf("%02shrs : %'02smins \n", intval($sec/60/60), abs(intval(($sec%3600) / 60)));
		}elseif($type ==="min_sec"){
				$timeString= sprintf("%'02smins : %'02ssec\n",  abs(intval(($sec%3600) / 60)), abs($sec%60));
		} elseif($type ==="hr_min"){
				$timeString= sprintf("%02s : %'02s hrs \n", intval($sec/60/60), abs(intval(($sec%3600) / 60)));
	}else {
			$timeString= sprintf("%02s : %'02s : %02s\n", intval($sec/60/60), abs(intval(($sec%3600) / 60)), abs($sec%60));
		}
		
		
		return $timeString;
	} 

	// echo $totalLogTimeFormated =sprintf("%02d:%02d:%02d", intval($totalLog/60/60), abs(intval(($totalLog%3600) / 60)), abs($totalLog%60))  ;
	
	public static function time_difference_full ($startTime, $endTime) {
			$startTimeArray= explode(':', $startTime);											
			$startTimeSec= ($startTimeArray[0] * 3600) + ($startTimeArray[1] * 60)+$startTimeArray[2];			
			$endTimeArray= explode(':', $endTime);											
			$endTimeSec= ($endTimeArray[0] * 3600) + ($endTimeArray[1] * 60) +$endTimeArray[2];
			$diff= (int)$endTimeSec - (int)$startTimeSec;
			return $diff > 0 ? $diff : false;
	}



	/**
	 * Random String & number Generation 
	 * ************************************/
	public static function genrateRandomInteger($len = 10){
		$last =-1; $code = '';
		for ($i=0;$i<$len;$i++)	{
			do {
				$next_digit=mt_rand(0,9);
			}
			while ($next_digit == $last);
			$last=$next_digit;
			$code.=$next_digit;
		}
		return $code;
	}

	public static function clientCode($clientName, $length=6) {
		$clientInit = explode(" ", Utility::clean_string($clientName));		
		$initials="";
		foreach ($clientInit as $key => $name) {
			$initials .=strtoupper($name[0]);
		}

		return "{$initials}-". Utility::genrateRandomInteger($length);
	}

	public static function generate_name_code($nameVar, $length=6) {
		$variableInit = explode(" ", Utility::clean_string($nameVar));		
		$initials="";
		foreach ($variableInit as $key => $name) {
			$initials .=strtoupper($name[0]);
		}

		return "{$initials}-". Utility::genrateRandomInteger($length);
	}

	public static function generateRandomString($length = 25) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public static function generate_unique_string($length = 24) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	/**
	 * Spllit file path using / or \
	 * **********************************/

	public static function file_path_split($fileFullUrl) {
		if ($fileFullUrl) {
			/*offline
			=========================*/
			$pathParts = explode("\\", $fileFullUrl);

			/*online
			=============================*/
			// $pathParts = explode("/", $fileFullUrl);

			
			return $pathParts ? $pathParts : false;
		}
		return false;
	}

	/**
	 * Validation functions and methods
	 * ************************************.*/
	public static function clean_string($str) {
		return trim(strip_tags($str));
	}

	public static function validate_email_address ($email) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
	 if (is_bool($atIndex) && !$atIndex) {
			$isValid = false;
	 } else {
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
				 // local part length exceeded
				 $isValid = false;
			} else if ($domainLen < 1 || $domainLen > 255) {
				 // domain part length exceeded
				 $isValid = false;
			} else if ($local[0] == '.' || $local[$localLen-1] == '.') {
				 // local part starts or ends with '.'
				 $isValid = false;
			} else if (preg_match('/\\.\\./', $local)) {
				 // local part has two consecutive dots
				 $isValid = false;
			} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
				 // character not valid in domain part
				 $isValid = false;
			} else if (preg_match('/\\.\\./', $domain)) {
				 // domain part has two consecutive dots
				 $isValid = false;
			} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
				 // character not valid in local part unless
				 // local part is quoted
				 if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
						$isValid = false;
				 }
			}
			/*if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
				 // domain not found in DNS
				 $isValid = false;
			}*/
	 }
	 return $isValid ? $email : false;
	}


	public static function check_post_edit ( $postArray, $postKey, $details, $postkeyType, $config='' ) {
		 $changes=array();
		 echo "<h4<> ${postKey} </h4>";
		 var_dump($postkeyType);
		 var_dump($postArray);
		 var_dump($postKey);
		 // var_dump($config);
		 switch (strtolower($postkeyType)) {

		 	case 'string':
		 		$postVal= Utility::clean_string($postArray);
		 		break;

	 		case 'email':
		 		if ($email = Form::validate_email($postArray) ) {
		 			$postVal= Utility::clean_string($postArray);
		 		}			 		
	 		break;
		 	
	 		case 'date':
		 		if (preg_match($config['ISODateFormat'], $postArray) ){
		 				$postVal= Utility::clean_string($postArray);
		 		} else {
		 			$errors[] = "invalid email Format";
		 		}
	 		break;

	 		default:
	 			$postVal= Utility::clean_string($postArray);
	 		break;
		 }
		var_dump($postVal);
		if ($details[$postKey] != $postVal)  {
			$changes= $postVal;
		} else {
			$changes=$details[$postKey];
		}
		return !empty($changes) ? $changes : false ;
	}


	public static function edit_array_validation ($postArray, $databaseArray){
		$changes=array();
		$errors= array();
		foreach ($postArray as $key => $post) {
			if ($post !=='' ) {
				// var_dump($userDets[$key]);
				if (array_key_exists($key, $databaseArray) ) {

					if ($databaseArray[$key] != $post) {
						$changes[$key]= $post;				
					}				
				} else {
					$errors[]= "The user element {$post} does not exist in the database. Please notify the admin";
				}


			}
		}
		$returnArray= array('changes'=> $changes, 'errors'=> $errors);
		return $returnArray;

	}

	public static function currencyToDecimal($currency) {
		// Remove commas and convert to float
		$decimal = (float) str_replace(',', '', $currency);
		return $decimal;
  }
  


	/**
	 * 	MODAL FUNCTIONS
	***********************************************/
	public static function form_modal_header ($id, $link, $title, $classes, $base) {
		$classStr= '';
		if (is_array($classes) && count($classes) !== 0) {
			
		$classStr=implode(' ', array_filter($classes, function($n){ return $n ? true : false; }));
		}
	 	$titleHeader ="";
		if ($title != '') {
			$titleHeader=" <div class='modal-header pb-1'>
									<h1 class='modal-title fs-6 t400 my-0' id='staticBackdropLabel'>{$title}</h1>
									<button class='btn-close' type='button'  data-bs-dismiss='modal' aria-label='Close'></button>
									
								</div>";
		}
	 	$modal = "<div class='modal fade' id='{$id}' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='{$id}Label' aria-hidden='true'>
							<div class='modal-dialog {$classStr}'>
								
								<div class='modal-content'>
								<form class='my-0 mx-0 {$id} clearfix'  action='{$base}php/scripts/{$link}' method='post' enctype='multipart/form-data' novalidate> 
									{$titleHeader}
								<div class='modal-body'>";	
			
		return $modal;
	}

	public static function form_modal_footer($save='Submit', $id="", $class='btn btn-primary btn-xs', $cancel=false){
		$cancelBtn = "";

		if ($cancel) {
			$size="";
			if (strpos($class, '-xs')) {
				$size= 'btn-xs';
			} 
			if (strpos($class, '-sm')) {
				$size= 'btn-sm';
			}

			$cancelBtn = " <button type='button' class='btn btn-secondary {$size} px-3' data-bs-dismiss='modal'>Close</button>";
		}
		
		$modalFooter ="</div>
								<div class='modal-footer'>  
								{$cancelBtn}     
									<button id='{$id}' type='submit' class='{$class}'>{$save}</button>
								</div>
							</form>
						</div>
					</div>
				</div>";
		return $modalFooter;
	}

	public static function form_modal_footer_no_buttons(){
		$modalFooter="</div>
							
						</form>
					</div>
				</div>";

		return $modalFooter;
	}

	public static function modal_general_top( $id, $title, $classes, $base="") {
		$classStr= '';
		if (is_array($classes) && count($classes) !== 0) {
			foreach ($classes as $key => $value) {
				$classStr .= $value .' ';
			}
		}
		$titleEmbed ='';
		if ($title != '') {
			$titleEmbed = "<div class='modal-header'>
										<h5 class='modal-title' id='{$id}Label'>{$title}</h5>
										<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
									</div>";
		}  
		$modal="<div class='modal fade' id='{$id}' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='{$id}Label' aria-hidden='true'>
							<div class='modal-dialog {$classStr}'>
							<div class=\" modal-content \"  > 
									{$titleEmbed}
									<div class='modal-body'>";								
		return $modal; 
	}

	public static function form_modal_general_footer($save='Submit', $id="submit", $class='btn btn-primary btn-sm'){
		$modal="</div>
							<div class='modal-footer'>
								<button type='button' class='btn btn-secondary btn-sm footer-close' data-bs-dismiss='modal'>Close</button>
								<button id='{$id}' type='submit' class='{$class}'>{$save}</button>
							</div>
						</div>
					</div>
				</div>";

				return $modal;
	}

	public static function form_modal_general_footer_no_buttons (){
		$modal="</div>
							
						</div>
					</div>
				</div>";

				return $modal;
	}


	/**
	 * Table functions & methods for 
	 * Array
	 * *********************************/
	public static function print_table ($array, $classes='', $tableClasses='', $align=array(), $widths=array()) {
		if(!$array or count($array) == 0) {
			Alert::info('No data in table.');
			return false;
		}
		// var_dump($align);
		// var_dump($widths);
		$hasMultipleRows = false;
		$resultString = '';
		// $alignStringKey = '';
		// $alignStringValue = '';
		foreach ($array as $key=>$row) {
			if (is_int($key) && is_array($row)) {
				$hasMultipleRows = true;
				break;
			}
		}

		if ($hasMultipleRows) { 
			$resultString .= "<div class='table-responsive ". $classes ." '>
								<table id='datatable1' class='table ". $tableClasses ."  table-stripped table-hover'>";
			$keys = array();
			foreach ($array as $i=>$row) {
				if ($i == 0) {
					$hasAlign = false;
					$hasAlign = (is_array($align) && count($align) == count($row));
					$resultString .= "<thead class='table-dark'><tr>";
					$hasWidths = is_array($widths) && count($widths) > 0 && count($widths) == count($row);
					$j = 0;
					foreach ($row as $key => $value) {
						$resultString .= '<th class="' . ($hasAlign ? " text-{$align[$j]} " : '') . ($hasWidths ? " {$widths[$j]} " : '') . '">' . ucwords($key) . '</th>';
						$keys[] = $key;
						$j++;
					}
					$resultString .= "</tr></thead><tbody>";
				}
				$resultString.="<tr>";
				$j = 0;
				foreach ($keys as $key) {
					if (isset($row[$key])) {
						$row[$key] = !is_a($row[$key], "DateTime") ? $row[$key] : $row[$key]->format('Y-m-d');
						$resultString .= '<td' . ($hasAlign ? " class='text-" . $align[$j] . "'" : '') . '>' . $row[$key] . '</td>';
					} else {
						$resultString .= '<td>&nbsp;</td>';
					}
					$j++;
				}
				$resultString .= "</tr>";
			}
			$resultString.="</tbody></table> </div>";
		} else {
			$hasAlign = (is_array($align) && count($align) == 2);
			$hasWidths = is_array($widths) && count($widths) > 0 && count($widths) >= 2;

			$resultString.="<div class='table-responsive'> <table class='table table-sm table-stripped table-hover'>";
			foreach ($array as $key=>$value) {
				$value = !is_a($value, "DateTime") ? $value : $value->format('Y-m-d');
				$resultString .= "<tr>" .
										 "<td class = '" . ($hasAlign ? " text-{$align[0]} " : '') . ($hasWidths ? " {$widths[0]} " : '') . "'><strong>{$key}</strong></td>" .
										 "<td class = '" . ($hasAlign ? " text-{$align[1]} " : '') . ($hasWidths ? " {$widths[1]} " : '') . "'>{$value}</td>" .
									 "</tr>";
			}
			$resultString .= "</table> </div>";
		}
		echo $resultString;
	}


	 /*
	 Print table Object
	 *******************************/
	public static function print_table_obj ($array, $classes='', $tableClasses='', $align=array(), $widths=array()) {
		if(!$array or count($array) == 0) {
			Alert::info('No data in table.');
			return false;
		}
		// var_dump($align);
		// var_dump($widths);
		$hasMultipleRows = false;
		$resultString = '';
		// $alignStringKey = '';
		// $alignStringValue = '';
		foreach ($array as $key=>$row) {
			
			if (is_int($key) && is_array($row)) {
				$hasMultipleRows = true;
				break;
			}
		}

		if ($hasMultipleRows) {   
			$resultString .= "<div class='table-responsive ". $classes ." '>
								<table id='datatable1' class='table ". $tableClasses ."  table-stripped table-hover'>";
			$keys = array();
			foreach ($array as $i=>$row) {
				if ($i == 0) {
					$hasAlign = false;
					$hasAlign = (is_array($align) && count($align) == count($row));
					$resultString .= "<thead class='table-dark'><tr>";
					$hasWidths = is_array($widths) && count($widths) > 0 && count($widths) == count($row);
					$j = 0;
					foreach ($row as $key => $value) {
						$resultString .= '<th class="' . ($hasAlign ? " text-{$align[$j]} " : '') . ($hasWidths ? " {$widths[$j]} " : '') . '">' . ucwords($key) . '</th>';
						$keys[] = $key;
						$j++;
					}
					$resultString .= "</tr></thead><tbody>";
				}
				$resultString.="<tr>";
				$j = 0;
				foreach ($keys as $key) {
					if (isset($row[$key])) {
						$row[$key] = !is_a($row[$key], "DateTime") ? $row[$key] : $row[$key]->format('Y-m-d');
						$resultString .= '<td' . ($hasAlign ? " class='text-" . $align[$j] . "'" : '') . '>' . $row[$key] . '</td>';
					} else {
						$resultString .= '<td>&nbsp;</td>';
					}
					$j++;
				}
				$resultString .= "</tr>";
			}
			$resultString.="</tbody></table> </div>";
		} else {
			$hasAlign = (is_array($align) && count($align) == 2);
			$hasWidths = is_array($widths) && count($widths) > 0 && count($widths) >= 2;

			$resultString.="<div class='table-responsive'> <table class='table table-sm table-stripped table-hover'>";
			foreach ($array as $key=>$value) {				
				$value = !is_a($value, "DateTime") ? $value : $value->format('Y-m-d');
				$resultString .= "<tr>" .
													 "<td class = '" . ($hasAlign ? " text-{$align[0]} " : '') . ($hasWidths ? " {$widths[0]} " : '') . "'><strong>{$key}</strong></td>" .
													 "<td class = '" . ($hasAlign ? " text-{$align[1]} " : '') . ($hasWidths ? " {$widths[1]} " : '') . "'>{$value}</td>" .
												 "</tr>";
			}
			$resultString .= "</table> </div>";
		}
		echo $resultString;
	}

	public static function print_array ($array) {
		if ($array && is_array($array) && count($array) > 0) {
			echo "<table class='table table-condensed table-hover'>\n";
			$keys = array_keys( $array );
			foreach( $keys as $key ) {
				echo "<tr valign=\"top\">
								<td><strong>".$key."</strong></td>\n";
				echo "<td>";
				if (is_array($array[$key])) {
					Utility::print_array($array[$key]);
				} else {
					echo !is_a($array[$key], "DateTime") ? $array[$key] : $array[$key]->format('j\<\s\u\p\>S\<\/\s\u\p\> F Y');
				}
				echo "</td>\n</tr>\n";
			}
			echo "</table>\n";
		}
	}

	public static function unit_code($entityName, $entityID, $DBConn) {
		if ($entityName) {
			$unitArr = explode(" ", $entityName); 	 				
			$abr = '';
			foreach ($unitArr as $ky => $str) {
				if (count($unitArr) === 1) {
					$abr .= strtoupper($str[0]);
					$abr .= strtoupper($str[1]);
					$abr .= strtoupper($str[2]);
				} else {
					$abr .= strtoupper($str[0]);
				}	 					
			}	
				
			$unitRan = Utility::genrateRandomInteger(6);	 				
			$unitCode = "{$unitRan}_{$abr}_{$entityID}";
			return $unitCode ? $unitCode : false;
		}
		
	}

	/*Unit assignment Utility functions
	=================================*/

	public static function lowest_Unit_Level($unitArr, $DBConn) {
		if ($unitArr) {
			$unitLevels = Data::unit_types_order(array("Suspended"=>"N"), false, $DBConn);

			// var_dump($unitLevels);

			if ($unitLevels) {

				for ($i=count($unitLevels)-1; $i >=0 ; $i--) { 
					var_dump($unitLevels[$i]);
					$lowestUnitID = $unitLevels[$i]->unitTypeID;

					foreach ($unitArr as $key => $Unit) {
						if ((int)$unit->unitTypeID === (int)$lowestUnitID) {
							return $subunitLevel = $unitLevels[$i]->unitTypeName;
						}
					}
				}
				return false;
			} 
			return false;
		} 
		return false;
		
	}
	public static function custom_string_length($x, $length) {
  		if(strlen($x)<=$length)  {
    		$count = $x;
  		}  else   {
			$x= Utility::clean_string($x);
    		$y=substr($x,0,$length) . '...';
    		$count =$y;
  		}
  		return "<p>{$count}</p>";
	}


	// Filter Through Array of objects  to retrieve matching filter 

	public static function filter_array_of_objects_by_value($arrayOfObjects, $objectKey, $filter_value){
		// var_dump($objectKey);
		// var_dump($arrayOfObjects);
		$filteredArray = array();
		if ($filter_value) {
			$filteredArray = array_filter($arrayOfObjects, function($obj) use ($filter_value, $objectKey){				
				return  $obj->$objectKey == $filter_value;
			});
		}
		$resArray= array();
		if ($filteredArray) {
			foreach ($filteredArray as $key => $filterObj) {
				$resArray[] = $filterObj;
			}
			// code...
		}
		return $resArray ? $resArray : null;

	}

	public static function back_url($getString, $returnUrl){
		$back_url = false;
		if ($returnUrl && $getString) {
			if ($returnUrl !== $getString) {
				$back_url =  Utility::clean_string($returnUrl);
			}
		}
		return $back_url ? $back_url : "";

	}
	public static function salutation ($whereArr, $single, $DBConn) {
	 $cols = array('salutationID', 'DateAdded', 'salutation',   'Lapsed', 'Suspended');   
	 $rows= $DBConn->retrieve_db_table_rows('sbsl_salutation', $cols, $whereArr);
	 return ($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  public static function remove_querystring_var($url, $key) {
		$parts = parse_url($url);
		$query = array();
		if (isset($parts['query'])) {
			parse_str($parts['query'], $query);
			unset($query[$key]);
		}
		$query = http_build_query($query);
		return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . ($query ? '?' . $query : '');

	}

	public static function generateDateTime ($DBConn){
		$dateTime = new DateTime();
		$dateTime->setTimezone(new DateTimeZone('Africa/Nairobi'));
		$dateTimeStr = $dateTime->format('Y-m-d H:i:s');
		$dateTimeArr = array('dateTime'=> $dateTimeStr);
		// $DBConn->insert_db_row('sbsl_datetime', $dateTimeArr);
		return $dateTimeStr;

	}
	public static function generate_account_code($accountName) {
		// Clean and format account name
		$cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $accountName); // Remove special chars
		$namePrefix = strtoupper(substr($cleanName, 0, 3)); // Take first 3 chars
		
		// Generate 5 random alphanumeric characters
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomStr = '';
		for ($i = 0; $i < 5; $i++) {
			$randomStr .= $characters[rand(0, strlen($characters) - 1)];
		}
		
		// Combine name prefix and random string
		$accountCode = $namePrefix . '_' . $randomStr;
		
		return $accountCode;
	}

	
	public static function generate_initials($fullName) {
		$nameParts = explode(" ", trim($fullName)); // Split the name into parts
		// var_dump($nameParts);
		if (count($nameParts) === 0) {
			 return ''; // Return empty if no name is provided
		}
  
		$firstInitial = strtoupper($nameParts[0][0]); // First letter of the first name
		$lastInitial = strtoupper($nameParts[count($nameParts) - 1][0]); // First letter of the last name
  
		return $firstInitial . $lastInitial; // Return the initials
  }
  /**
   * This method generates a DateTime object based on the provided month and year, 
   * and then modifies it according to the specified type (weekly, biweekly, monthly, 
   * bimonthly, quarterly, semiannual, or annual). The modification is done by 
   * adding a certain number of weeks, months, or years to the initial date.
   * 
   * @param string $month The month of the year (01-12).
   * @param string $year The year.
   * @param string $type The type of modification to apply (default is '').
   * @return DateTime|false Returns the modified DateTime object or false if $month or $year is not provided.
   */
  public static function month_variables($month, $year, $type='') {
    if ($month && $year) {
      $dt = new DateTime("{$year}-{$month}-01"); // Create a DateTime object for the first day of the month.
      $unitNumber = '+1'; // Default unit number for modification.
      $period = ''; // Default period for modification.
      
      // Determine the period and unit number based on the type.
      switch ($type) {
        case 'weekly':
          $period = 'weeks';
          break;
        case 'biweekly':
          $period = 'weeks';
          $unitNumber = '+2'; // Add 2 weeks.
          break;
        case 'monthly':
          $period = 'months';
          break;
        case 'bimonthly':
          $period = 'months';
          $unitNumber = '+2'; // Add 2 months.
          break;
        case 'quarterly':
          $period = 'months';
          $unitNumber = '+3'; // Add 3 months.
          break;
        case 'semiannual':
          $period = 'months';
          $unitNumber = '+6'; // Add 6 months.
          break;
        case 'annual':
          $period = 'years';
          $unitNumber = '+1'; // Add 1 year.
          break;
      }
      
      // Modify the DateTime object based on the determined period and unit number.
      $dt->modify("{$unitNumber} {$period}");
      return $dt; // Return the modified DateTime object.
    } else {
      return false; // Return false if $month or $year is not provided.
    }
  }

  public static function get_weekdays($startDate, $endDate) {
		if ($startDate && $endDate) {
			$start = new DateTime($startDate);
			$end = new DateTime($endDate);
			$workdays = array();
			$interval = new DateInterval('P1D');
			$period = new DatePeriod($start, $interval, $end->modify('+1 day'));
			foreach ($period as $date) {
				$day_name = $date->format('l');
				if ($day_name !== 'Saturday' && $day_name !== 'Sunday') {
					$workdays[] = (object)[
						'date'=> $date->format('Y-m-d'), 
						'day'=> $day_name, 
						'dateObject'=> date_create($date->format('Y-m-d'))
					];
				} else {
					$weekEnds[]= (object)[
						'date'=> $date->format('Y-m-d'),
						'day'=> $day_name,
						'dateObject'=> date_create($date->format('Y-m-d'))
					];
					$weekendStr = "Weekend: {$day_name} on {$date->format('Y-m-d')}\n";
				}
			}
			if (count($workdays) > 0) {
				$daysOutput['workdays'] = $workdays;
			} 
			if (count($weekEnds) > 0) {
				$daysOutput['weekends'] = $weekEnds;
			}
			return $daysOutput;
		} else {
			return false;
		}
	}

	public static function generate_month_time_variables($month, $year, $type='weekdays') {
		if ($month && $year) {
			$currentYear= $year;
			$currentMonth= $month;
			$dt = new DateTime("{$year}-{$month}-01");
			$totalDaysInMonth = date('t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
			
			// Initialize counters
			$totalWeekdaysInMonth = 0;
			$totalSaturdaysInMonth = 0;
			$totalHoursInMonth = 0;
			
			// Count days by type
			for ($i = 1; $i <= $totalDaysInMonth; $i++) {
				$dayOfWeek = date('w', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
				
				if ($dayOfWeek != 0) { // Skip Sundays (0)
					if ($dayOfWeek == 6) { // Saturday
						$totalSaturdaysInMonth++;
					} else { // Monday to Friday (1-5)
						$totalWeekdaysInMonth++;
					}
				}
			}
			
			// Calculate hours based on work week type
			switch (strtolower($type)) {
				case 'weekdays':
				case 'standard':
				case '40hour':
					// Standard 40-hour work week: Monday-Friday, 8 hours per day
					$totalHoursInMonth = $totalWeekdaysInMonth * 8;
					$workWeekType = 'Standard 40-hour work week (Mon-Fri)';
					break;
					
				case 'workweek':
				case 'extended':
				case '45hour':
					// Extended work week: Monday-Friday (8 hours) + Saturday (5 hours)
					$totalHoursInMonth = ($totalWeekdaysInMonth * 8) + ($totalSaturdaysInMonth * 5);
					$workWeekType = 'Extended 45-hour work week (Mon-Fri + Sat)';
					break;
					
				case 'custom':
					// Custom work week: Monday-Friday (8 hours) + Saturday (variable hours)
					$saturdayHours = 5; // Default 5 hours on Saturday, can be customized
					$totalHoursInMonth = ($totalWeekdaysInMonth * 8) + ($totalSaturdaysInMonth * $saturdayHours);
					$workWeekType = 'Custom work week (Mon-Fri + Sat)';
					break;
					
				default:
					// Default to standard 40-hour work week
					$totalHoursInMonth = $totalWeekdaysInMonth * 8;
					$workWeekType = 'Standard 40-hour work week (Mon-Fri)';
					break;
			}
			
			$totalMinutesInMonth = $totalHoursInMonth * 60; // Convert hours to minutes

			return array(
				'totalDaysInMonth' => $totalDaysInMonth,
				'totalWeekdaysInMonth' => $totalWeekdaysInMonth,
				'totalSaturdaysInMonth' => $totalSaturdaysInMonth,
				'totalHoursInMonth' => $totalHoursInMonth,
				'totalMinutesInMonth' => $totalMinutesInMonth,
				'workWeekType' => $workWeekType,
				'type' => $type,
				'hoursPerWeekday' => 8,
				'hoursPerSaturday' => ($type === 'workweek' || $type === 'extended' || $type === '45hour') ? 5 : 0
			);
		} else {
			return false;
		}
	}

	public static function getDaysBetweenDates($startDate, $endDate) {
		$start = strtotime($startDate);
		$end = strtotime($endDate);
		$days = ceil(abs($end - $start) / 86400);
		return $days;
  }

}?>

