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
    $uploadArray = (isset($_POST['uploadArray']) && !empty($_POST['uploadArray'])) ? substr($_POST['uploadArray'],  1, -1): array();
    var_dump($uploadArray);

// initial import script 
// include 'php/include_initial_statement_accounts.php';

    











// Var_dump($income_statement_data);

// function replaceKeys($data) {
//     $result = [];
//     foreach ($data as $key => $value) {
//         // Replace underscores with spaces in the key
//         $newKey = str_replace('_', ' ', $key);

//         // Recursively handle nested arrays
//         if (is_array($value)) {
//             $result[$newKey] = replaceKeys($value);
//         } else {
//             $result[$newKey] = $value;
//         }
//     }
//     return $result;
// }



// Apply the transformation
// $convertedData = replaceKeys($income_statement_data);
// var_dump($convertedData);


// Now you have the data in a PHP associative array format.




    
 
// var_dump($convertedData);

    $accountName= (isset($_POST['accountName']) && !empty($_POST['accountName'])) ? Utility::clean_string($_POST['accountName']): "";
    $accountCode = (isset($_POST['accountCode']) && !empty($_POST['accountCode'])) ? Utility::clean_string($_POST['accountCode']): Utility::clientCode($accountName);
    $accountDescription = (isset($_POST['accountDescription']) && !empty($_POST['accountDescription'])) ? Utility::clean_string($_POST['accountDescription']): "";
    $parentAccountID = (isset($_POST['parentAccountID']) && !empty($_POST['parentAccountID'])) ? Utility::clean_string($_POST['parentAccountID']): "";
    $accountType = (isset($_POST['accountType']) && !empty($_POST['accountType'])) ? Utility::clean_string($_POST['accountType']): "";
    $incomeStatementAccountID = (isset($_POST['incomeStatementAccountID']) && !empty($_POST['incomeStatementAccountID'])) ? Utility::clean_string($_POST['incomeStatementAccountID']): "";
    
    $accountName ? $accountNode = Tax::nodes($accountName): "";

    if(!$incomeStatementAccountID) {
        $accountName ? $details['accountName'] = $accountName : $errors[] = "Please submit valid account name";
        $accountCode ? $details['accountCode'] = $accountCode : $errors[] = "Please submit valid account code";
        $accountDescription ? $details['accountDescription'] = $accountDescription : "";
        $parentAccountID ? $details['parentAccountID'] = $parentAccountID : "";
        $accountType ? $details['accountType'] = $accountType : $errors[] = "Please submit valid account type";
        $accountNode ? $details['accountNode'] = $accountNode : $errors[] = "Please submit valid account node";
        echo "<h5> Details </h5>";
        var_dump($details);
        if(count($errors) === 0) {
            if($details) {
                $details['DateAdded'] = $config['currentDateTimeFormated'];
                if(!$DBConn->insert_data("sbsl_Tax::income_statement_accounts", $details)) {
                    $errors[] = "<span class't600'> ERROR!</span> Failed to update account to the database";
                }
            }
        }
    } else {
        $accountDetails = Tax::income_statement_accounts(array("incomeStatementAccountID"=>$incomeStatementAccountID), true, $DBConn);
        (isset($accountName) && $accountName !== $accountDetails->accountName) ? $changes['accountName'] = $accountName : "";
        (isset($accountCode) && $accountCode !== $accountDetails->accountCode) ? $changes['accountCode'] = $accountCode : "";
        (isset($accountDescription) && $accountDescription !== $accountDetails->accountDescription) ? $changes['accountDescription'] = $accountDescription : "";
        (isset($parentAccountID) && $parentAccountID !== $accountDetails->parentAccountID) ? $changes['parentAccountID'] = $parentAccountID : "";
        (isset($accountType) && $accountType !== $accountDetails->accountType) ? $changes['accountType'] = $accountType : "";
        (isset($changes['accountName']) && $changes['accountName']) ? $changes['accountNode'] = Tax::nodes($changes['accountName']) : "";

        var_dump($changes);
        if(count($errors) === 0) {
            if($changes) {
                $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                if(!$DBConn->update_table("sbsl_Tax::income_statement_accounts", $changes, array('incomeStatementAccountID'=>$incomeStatementAccountID))) {
                    $errors[] = "<span class't600'> ERROR!</span> Failed to update account to the database";
                }
            }
        }
        
    }



} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/?{$returnURL}");?>