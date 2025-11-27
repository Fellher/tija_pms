
<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidAdmin || $isAdmin  ) {
	var_dump($_POST);
    var_dump($_FILES);

    $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
    $orgName = (isset($_POST['orgName']) && !empty($_POST['orgName'])) ?  Utility::clean_string($_POST['orgName']): "";
    $numberOfEmployees = (isset($_POST['numberOfEmployees']) && !empty($_POST['numberOfEmployees'])) ?  Utility::clean_string($_POST['numberOfEmployees']): "";
    $registrationNumber = (isset($_POST['registrationNumber']) && !empty($_POST['registrationNumber'])) ?  Utility::clean_string($_POST['registrationNumber']): "";
    $costCenterEnabled = (isset($_POST['costCenterEnabled']) && !empty($_POST['costCenterEnabled'])) ?  Utility::clean_string($_POST['costCenterEnabled']): "";
    $orgAddress = (isset($_POST['orgAddress']) && !empty($_POST['orgAddress'])) ?  Utility::clean_string($_POST['orgAddress']): "";
    $orgPostalCode = (isset($_POST['orgPostalCode']) && !empty($_POST['orgPostalCode'])) ?  Utility::clean_string($_POST['orgPostalCode']): "";
    $orgCity = (isset($_POST['orgCity']) && !empty($_POST['orgCity'])) ?  Utility::clean_string($_POST['orgCity']): "";
    $countryID = (isset($_POST['countryID']) && !empty($_POST['countryID'])) ?  Utility::clean_string($_POST['countryID']): "";
    $orgPhoneNumber1 = (isset($_POST['orgPhoneNumber1']) && !empty($_POST['orgPhoneNumber1'])) ?  Utility::clean_string($_POST['orgPhoneNumber1']): "";
    $orgPhoneNUmber2 = (isset($_POST['orgPhoneNUmber2']) && !empty($_POST['orgPhoneNUmber2'])) ?  Utility::clean_string($_POST['orgPhoneNUmber2']): "";
    $orgEmail = (isset($_POST['orgEmail']) && !empty($_POST['orgEmail'])) ?  Utility::clean_string($_POST['orgEmail']): "";
    $industrySectorID = (isset($_POST['industrySectorID']) && !empty($_POST['industrySectorID'])) ?  Utility::clean_string($_POST['industrySectorID']): "";
    $orgPIN = (isset($_POST['orgPIN']) && !empty($_POST['orgPIN'])) ?  Utility::clean_string($_POST['orgPIN']): "";
    $orgLogo = "";

    if(isset($_FILES['orgLogo']) && !empty($_FILES['orgLogo']['name']) && $_FILES['orgLogo']['error'] == 0){
        $logoImage = Utility::clean_string($_FILES['orgLogo']['name']);
        $logoImageTmp = $_FILES['orgLogo']['tmp_name'];
        $logoImageSize = $_FILES['orgLogo']['size'];
        $logoImagExt = strtolower(pathinfo($logoImage, PATHINFO_EXTENSION));
        $logoImageNewName = Utility::generateRandomString(10) . '.' . $logoImagExt;
        $logoImageDir = $config['DataDir'] . 'org_logos/' . $logoImageNewName;
        $logoImageValid= true;
        $logoImageError = false;
        $logoImageErrorText = '';
        $logoImageValidTypes = array('jpg', 'jpeg', 'png', 'gif');
        $logoImageValidSize = 2*1024*1024;
        if (!in_array($logoImagExt, $logoImageValidTypes)) {
            $logoImageValid = false;
            $logoImageError = true;
            $logoImageErrorText = 'The logo image is not a valid image file.';
        }
        if ($logoImageSize > $logoImageValidSize) {
            $logoImageValid = false;
            $logoImageError = true;
            $logoImageErrorText = 'The logo image is too large. It should be less than 2MB.';
        }
        if ($logoImageValid) {
            if (move_uploaded_file($logoImageTmp, $logoImageDir)) {
                $orgLogo = $logoImageNewName;
            } else {
                $logoImageError = true;
                $logoImageErrorText = 'There was an error uploading the logo image.';
            }
        } 
        if ($logoImageError) {
            $errors[] = $logoImageErrorText;
        }
    }

    if($orgDataID) {
        $orgDetails = Admin::org_data(array("orgDataID"=>$orgDataID), true, $DBConn);

        var_dump($orgDetails);
       
        ($orgName && $orgDetails->orgName !== $orgName) ? $changes['orgName'] = $orgName: "";
        ($numberOfEmployees && $orgDetails->numberOfEmployees !==(int) $numberOfEmployees) ? $changes['numberOfEmployees'] = (int)$numberOfEmployees: "";
        ($registrationNumber && $orgDetails->registrationNumber !== $registrationNumber) ? $changes['registrationNumber'] = $registrationNumber: "";
        ($costCenterEnabled && $orgDetails->costCenterEnabled !== $costCenterEnabled) ? $changes['costCenterEnabled'] = $costCenterEnabled: "";
        ($orgAddress && $orgDetails->orgAddress !== $orgAddress) ? $changes['orgAddress'] = $orgAddress: "";
        ($orgPostalCode && $orgDetails->orgPostalCode !== $orgPostalCode) ? $changes['orgPostalCode'] = $orgPostalCode: "";
        ($orgCity && $orgDetails->orgCity !== $orgCity) ? $changes['orgCity'] = $orgCity: "";
        ($countryID && $orgDetails->countryID !== $countryID) ? $changes['countryID'] = $countryID: "";
        ($orgPhoneNumber1 && $orgDetails->orgPhoneNumber1 !== $orgPhoneNumber1) ? $changes['orgPhoneNumber1'] = $orgPhoneNumber1: "";
        ($orgPhoneNUmber2 && $orgDetails->orgPhoneNUmber2 !== $orgPhoneNUmber2) ? $changes['orgPhoneNUmber2'] = $orgPhoneNUmber2: "";
        ($orgEmail && $orgDetails->orgEmail !== $orgEmail) ? (Form::validate_email($orgEmail) ? $changes['orgEmail'] = $orgEmail : $errors[]="Email submited is not in correct Format") : "";
        ($industrySectorID && $orgDetails->industrySectorID !== (int)$industrySectorID) ? $changes['industrySectorID'] = (int)$industrySectorID: "";
        ($orgLogo && $orgDetails->orgLogo !== $orgLogo) ? $changes['orgLogo'] = $orgLogo: "";
        ($orgPIN && $orgDetails->orgPIN !== $orgPIN) ? $changes['orgPIN'] = $orgPIN: "";

        if(count($errors) === 0) {
            if(count($changes) >0){
                $changes['LastUpdate'] = $config['currentDateTimeFormated'];
               
                $changes['LastUpdateByID'] = $userDetails->ID;

                if(!$DBConn->update_table("tija_organisation_data", $changes, array("orgDataID"=>$orgDataID))){
                    $errors[] = "There was an error updating the organisation details.";
                } else {
                    $success = "The organisation details have been updated successfully.";
                }
            }
        }
      
    } else {
        $orgName ? $details['orgName'] = $orgName : $errors[] = 'Company Name is required';
        $numberOfEmployees ? $details['numberOfEmployees'] = $numberOfEmployees : "";
        $registrationNumber ? $details['registrationNumber'] = $registrationNumber : $errors[] = 'Registration Number is required';
        $costCenterEnabled ? $details['costCenterEnabled'] = $costCenterEnabled :"";
        $orgAddress ? $details['orgAddress'] = $orgAddress : $errors[] = 'Company Address is required';
        $orgPostalCode ? $details['orgPostalCode'] = $orgPostalCode : $errors[] = 'Postal Code is required';
        $orgCity ? $details['orgCity'] = $orgCity : $errors[] = 'City is required';
        $countryID ? $details['countryID'] = $countryID : $errors[] = 'Country is required';
        $orgPhoneNumber1 ? $details['orgPhoneNumber1'] = $orgPhoneNumber1 : $errors[] = 'Telephone number is required';
        $orgPhoneNUmber2 ? $details['orgPhoneNUmber2'] = $orgPhoneNUmber2 : '';
        $orgEmail  ? (Form::validate_email($orgEmail) ? $details['orgEmail'] = $orgEmail : $errors[]="Email submited is not in correct Format") : $errors[] = 'Company Email is required';
        $industrySectorID ? $details['industrySectorID'] = $industrySectorID : $errors[] = 'Industry Sector is required';
        $orgPIN ? $details['orgPIN'] = $orgPIN : $errors[] = 'PIN is required';

        var_dump($details);
        if(count($errors) === 0) {
            $details['orgLogo'] = $orgLogo;
            $details['LastUpdateByID'] = $userDetails->ID;
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data("tija_organisation_data", $details)){
                $errors[] = "There was an error creating the organisation details.";
            } else {
                $success = "The organisation details have been created successfully.";
            }
        }

    }

    var_dump($errors);
    var_dump($success);

    $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");