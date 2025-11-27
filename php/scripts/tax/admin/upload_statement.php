<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidAdmin || $isAdmin) {
	var_dump($_POST);
    var_dump($_FILES);

	$fiscalYear= (isset($_POST['fiscalYear']) && !empty($_POST['fiscalYear'])) ? Utility::clean_string($_POST['fiscalYear']) : '';
	// $fiscalPeriod = (isset($_POST['fiscalPeriod']) && !empty($_POST['fiscalPeriod'])) ? Utility::clean_string($_POST['fiscalPeriod']) : '';
	$orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : '';
	$userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ? Utility::clean_string($_POST['userID']) : '';
	$statementType = (isset($_POST['statementType']) && !empty($_POST['statementType'])) ? Utility::clean_string($_POST['statementType']) : '';
	$statementTypeNode = (isset($_POST['statementTypeNode']) && !empty($_POST['statementTypeNode'])) ? Utility::clean_string($_POST['statementTypeNode']) : '';
	$statementTypeID = (isset($_POST['statementTypeID']) && !empty($_POST['statementTypeID'])) ? Utility::clean_string($_POST['statementTypeID']) : '';
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : '';
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : '';
   $period = (isset($_POST['period']) && !empty($_POST['period'])) ? Utility::clean_string($_POST['period']) : '';
   if($period) {
      $periodArr = explode("to", $period);
      $periodStartDate = $periodArr[0];
      $periodEndDate = $periodArr[1];
      if($periodStartDate  &&  preg_match($config['ISODateFormat'], Utility::clean_string($periodStartDate)) ) {
         $start = utility::clean_string($periodStartDate);

         $dt = date_create($start);
         var_dump($dt);
         $periodStartDate = date_format($dt, "Y-m-d");
      }
      if($periodEndDate  &&  preg_match($config['ISODateFormat'], Utility::clean_string($periodEndDate)) ) {
         $end = utility::clean_string($periodEndDate);

         $dtEnd = date_create($end);
         var_dump($dtEnd);
         $periodEndDate = date_format($dtEnd, "Y-m-d");
      }
   }


   var_dump($periodEndDate);

	$statementTypeDetails = Tax::financial_statements_types(array("financialStatementTypeID"=>$statementTypeID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);

	var_dump($statementTypeDetails);
	$fiscalYear ? $details['fiscalYear'] = $fiscalYear : $errors[] = 'Fiscal Year is required';
	// $fiscalPeriod ? $details['fiscalPeriod'] = $fiscalPeriod : "";
	// $instanceID ? $details['instanceID'] = $instanceID : $errors[] = 'Instance ID is required';
	$statementTypeID ? $details['statementTypeID'] = $statementTypeID : $errors[] = 'Statement Type ID is required';
	$statementTypeNode ? $details['statementTypeNode'] = $statementTypeNode : $errors[] = 'Statement Type Node is required';
   $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity ID is required';
   $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Org Data ID is required';
   $periodStartDate ? $details['periodStartDate'] = $periodStartDate : $errors[] = 'Period Start Date is required';
   $periodEndDate ? $details['periodEndDate'] = $periodEndDate : $errors[] = 'Period End Date is required';

	$orgDetails = Admin::organisation_data_mini(array("orgDataID"=>$orgDataID, "Suspended"=>'N'), true, $DBConn);
	var_dump($orgDetails);
	$uploadedFile = "";
	if(isset($_FILES)) {
		$uploadedFile = $_FILES["upload_{$statementTypeNode}"];

		var_dump($uploadedFile);
	}
	
	if($statementTypeID == 5) {
		$fileName = $uploadedFile["name"];
		if($uploadedFile["size"] > 0) {
			$filename=$uploadedFile["tmp_name"];
			$file = fopen($filename, "r");
			$i=0;
			$investmentAllowanceArr= array();
			while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)  {
				$i++;
				if($i != 1) {
					$emapData = array_map('trim', $emapData);
					$emapData = array_map('strtolower', $emapData);
					$emapData = array_map('ucwords', $emapData);
					$emapData = array_map('ucfirst', $emapData);
					// $emapData = array_map('strtoupper', $emapData);
					

					$startDate = (isset($emapData[3]) && !empty($emapData[3]) && (preg_match($config['ISODateFormat'], $emapData[3])) )? Utility::clean_string($emapData[3]): "";
					$investmentAllowanceArr[] = (object)[
						"investmentName" => $emapData[0],
						"rate" =>  trim($emapData[1], "%")/100,
						"initialWriteDownValue"=> (isset($emapData[2]) && !empty($emapData[2])) ? Utility::currencyToDecimal($emapData[2]): 0,
						"beginDate"=> (isset($emapData[3]) && !empty($emapData[3]) && (preg_match($config['ISODateFormat'], $emapData[3])) )? Utility::clean_string($emapData[3]): "",
						'additions'=> (isset($emapData[4]) && !empty($emapData[4])) ? Utility::currencyToDecimal($emapData[4]): 0,
						'disposals'=> (isset($emapData[5]) && !empty($emapData[5])) ? Utility::currencyToDecimal($emapData[5]): 0,
						'endWriteDownValue'=> (isset($emapData[8]) && !empty($emapData[8])) ? Utility::currencyToDecimal($emapData[8]): 0,
						'endDate'=> (isset($emapData[9]) && !empty($emapData[9]) && (preg_match($config['ISODateFormat'], $emapData[9]))) ? Utility::clean_string($emapData[9]): 0,
						'wearAndTearAllowance'=> (isset($emapData[7]) && !empty($emapData[7])) ? Utility::currencyToDecimal($emapData[7]): 0,
					];
				}
				var_dump($emapData);
				echo "<h3> Investment Allowance </h3>";
				var_dump($investmentAllowanceArr);
			}
			fclose($file);
			$investmentAllowanceData= array(
            "DateAdded"=>$config['currentDateTimeFormated'], 
         "orgDataID"=>$orgDataID,
								"fiscalYear"=>$fiscalYear, 
																
								"financialStatementTypeName"=>$statementTypeDetails->financialStatementTypeName, 
								"financialStatementTypeID"=>$details['statementTypeID'],
								"statementTypeNode"=>$details['statementTypeNode'],
                        "periodStartDate"=>$details['periodStartDate'],
                        "periodEndDate"=>$details['periodEndDate'],
                        "entityID"=>$details['entityID'],
                        "orgDataID"=>$details['orgDataID'],
								"Lapsed"=>'N', 
								"Suspended"=>'N');
			var_dump($investmentAllowanceData);

			if(count($errors)==0) {
				if($investmentAllowanceData) {
					if(!$DBConn->insert_data("tija_financial_statements", $investmentAllowanceData)) {
						$errors[] = "Failed to create balance sheet";
					} else {
						$financialStatementID = $DBConn->lastInsertID();
					}
				}
			};

         var_dump($errors);

			if($financialStatementID) {
				foreach ($investmentAllowanceArr as $key => $investmentData) {
					// var_dump($investmentData);
					$investmentAllowanceData = array(
						  'orgDataID'=> $details['orgDataID'], 
                    'entityID'=> $details['entityID'],
						  'financialStatementID'=>$financialStatementID , 
						  'investmentName'=> $investmentData->investmentName, 
						  'rate'=> $investmentData->rate, 
						  'initialWriteDownValue'=> $investmentData->initialWriteDownValue, 
						  'beginDate'=> $investmentData->beginDate, 
						  'additions'=> $investmentData->additions, 
						  'disposals'=> $investmentData->disposals, 
						  'wearAndTearAllowance'=> $investmentData->wearAndTearAllowance, 
						  'endWriteDownValue'=> $investmentData->endWriteDownValue, 
						  'endDate'=> $investmentData->endDate, 
					);
					if(count($errors)==0) {
						if($investmentAllowanceData) {
							$investmentAllowanceData['DateAdded'] = $config['currentDateTimeFormated'];
							if(!$DBConn->insert_data("tija_statement_of_investment_allowance_data", $investmentAllowanceData)) {
								$errors[] = "Failed to create balance sheet";
							}
						}
					}
					var_dump($investmentAllowanceData);
				}
			}
		}


	} else  {
		
		$filename=$uploadedFile["tmp_name"];
		if($uploadedFile["size"] > 0) {
			
			$file = fopen($filename, "r");
			// var_dump($file);
			$i=0;
			while (($emapData = fgetcsv($file, 100000, ",")) !== FALSE)  {
				$i++;

				if($i != 1) {
					var_dump($emapData);
					$emapData = array_map('trim', $emapData);
					$emapData = array_map('strtolower', $emapData);
					$emapData = array_map('ucwords', $emapData);
					$emapData = array_map('ucfirst', $emapData);
					// $emapData = array_map('strtoupper', $emapData);

					$flag = "";
					if($emapData[2] == 0 && $emapData[3] == 0) {
						$flag = "zero";
					} else if($emapData[2] > 0 && $emapData[3] > 0) {
						$flag = "Error, You can't have both debit and credit values";
					} else if($emapData[2] != 0) {
						$flag = "debit";
					} else if($emapData[3] != 0) {
						$flag = "credit";
					}

					// if($flag == "zero") {
					// 	$errors[] = "Error, You can't have both debit and credit values as zero";
					// } else if($flag == "Error, You can't have both debit and credit values") {
					// 	$errors[] = "Error, You can't have both debit and credit values";
					// } else 
					if($flag == "debit") {
						$incomeStatementArr[] = (object)[
							"accountName" => $emapData[0],
							"accountType" => $emapData[1],
							"debitValue"=> (isset($emapData[2]) && !empty($emapData[2])) ? Utility::currencyToDecimal($emapData[2]): 0,
							"creditValue"=> 0,
							"flag"=> $flag
						];
					} else if($flag == "credit") {
						$incomeStatementArr[] = (object)[
							"accountName" => $emapData[0],
							"accountType" => $emapData[1],
							"debitValue"=> 0,
							"creditValue"=> (isset($emapData[3]) && !empty($emapData[3])) ? Utility::currencyToDecimal($emapData[3]): 0,
							"flag"=> $flag
						];
					};	
				}	
			}
			// var_dump($incomeStatementArr);
			fclose($file);
			// Create the balance sheet in the database to to get the balanncesheet ID

			$incomeStatementData= array("DateAdded"=>$config['currentDateTimeFormated'], 
									"orgDataID"=>$orgDataID, 
                           'entityID'=> $details['entityID'],
									"fiscalYear"=>$fiscalYear, 
															
									"financialStatementTypeName"=>$statementTypeDetails->financialStatementTypeName, 
									"financialStatementTypeID"=>$details['statementTypeID'],
									"statementTypeNode"=>$details['statementTypeNode'],
									"Lapsed"=>'N', 
									"Suspended"=>'N');
									var_dump($incomeStatementData);

			if(count($errors)==0) {
				if($incomeStatementData) {
					if(!$DBConn->insert_data("tija_financial_statements", $incomeStatementData)) {
						$errors[] = "Failed to create balance sheet";
					} else {
						$financialStatementID = $DBConn->lastInsertID();					
					}
				}
			}

			var_dump($errors);

			if($financialStatementID) {
				foreach ($incomeStatementArr as $key => $incomeData) {
					// var_dump($incomeData);
					$fiancialStetementData = array(
						'orgDataID'=> $details['orgDataID'], 
                  'entityID'=> $details['entityID'],
						'financialStatementID'=>$financialStatementID , 
						'accountNode'=> Tax::account_node_short($incomeData->accountName), 
						'accountName'=> $incomeData->accountName, 
						'accountCode'=> Utility::clientCode($incomeData->accountName), 
						'accountDescription'=> '', 
						'accountType'=>$incomeData->flag, 
						'debitValue'=>$incomeData->debitValue, 
						'creditValue'=>$incomeData->creditValue,
						'accountCategory'=> $incomeData->accountType,
						'financialStatementTypeID'=> $details['statementTypeID'],
					);
					if(count($errors)==0) {
						if($fiancialStetementData) {
							$fiancialStetementData['DateAdded'] = $config['currentDateTimeFormated'];
							if(!$DBConn->insert_data("tija_financial_statement_data", $fiancialStetementData)) {
								$errors[] = "Failed to create balance sheet";
							}
						}
					}
					var_dump($fiancialStetementData);
				}
			}					
		} else {
			$errors[] = 'No file uploaded';
		}    
	}    
} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
if (count($errors) == 0) {
	$success = "Statement uploaded successfully";
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
	if($statementTypeID == 5) {
		$returnURL .="&invID={$financialStatementID}";
	} else {
		$returnURL .="&finstmtID={$financialStatementID}";
	}
	
	var_dump($returnURL);
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");?>