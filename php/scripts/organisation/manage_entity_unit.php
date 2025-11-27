
<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$categoryDetils=array();
$details= array();
$changes= array();
$success="";
if ($isValidAdmin || $isAdmin) {
	var_dump($_POST);
    $unitID = (isset($_POST['unitID']) && !empty($_POST['unitID'])) ? Utility::clean_string($_POST['unitID']) : null;

    $unitCode = (isset($_POST['unitCode']) && !empty($_POST['unitCode'])) ? Utility::clean_string($_POST['unitCode']) : Utility::generate_name_code($_POST['unitName']);
    $unitName = (isset($_POST['unitName']) && !empty($_POST['unitName'])) ? Utility::clean_string($_POST['unitName']) : null;
    $unitType = (isset($_POST['unitTypeID']) && !empty($_POST['unitTypeID'])) ? Utility::clean_string($_POST['unitTypeID']) : null;
    $headOfUnitID = (isset($_POST['headOfUnitID']) && !empty($_POST['headOfUnitID']) && $_POST['headOfUnitID'] !== '0') ? Utility::clean_string($_POST['headOfUnitID']) : null;
    $parentUnitID = (isset($_POST['parentUnitID']) && !empty($_POST['parentUnitID'])) ? Utility::clean_string($_POST['parentUnitID']) : '0';
    $unitDescription = (isset($_POST['unitDescription']) && !empty($_POST['unitDescription'])) ? Utility::clean_string($_POST['unitDescription']) : null;
    $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : null;
    $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : null;

    if($unitType === 'addNew') {
        $unitType = (isset($_POST['newUnitType']) && !empty($_POST['newUnitType'])) ? Utility::clean_string($_POST['newUnitType']) : null;
        if($unitType){
            $unitTypeDetails['unitTypeName'] = $unitType;
            $unitTypeDetails['LastUpdate'] = $config['currentDateTimeFormated'];
            if($unitTypeDetails){
                if(!$DBConn->insert_data('tija_unit_types', $unitTypeDetails)){
                    $errors[] = "Error adding Organisation Unit Type";
                } else {
                    $unitType = $DBConn->lastInsertId();
                }
            }
        } else {
            $errors[] = "Unit Type and Description are required";
        }
    }

    if($unitID) {
        $unitDetails =  Data::units(array('unitID'=>$unitID), true, $DBConn);
        var_dump($unitDetails);
        $unitCode  && $unitDetails->unitCode != $unitCode ? $changes['unitCode'] = $unitCode : '';
        $unitName  && $unitDetails->unitName != $unitName ? $changes['unitName'] = $unitName : '';
        $unitType  && $unitDetails->unitTypeID != $unitType ? $changes['unitTypeID'] = $unitType : '';

        // Handle headOfUnitID - allow setting to NULL when '0' is passed
        if (isset($_POST['headOfUnitID'])) {
            $newHeadOfUnitID = ($_POST['headOfUnitID'] === '0' || empty($_POST['headOfUnitID'])) ? null : $headOfUnitID;
            $currentHeadOfUnitID = $unitDetails->headOfUnitID ?? null;
            if ($newHeadOfUnitID !== $currentHeadOfUnitID) {
                $changes['headOfUnitID'] = $newHeadOfUnitID;
            }
        }

        $unitDescription  && $unitDetails->unitDescription != $unitDescription ? $changes['unitDescription'] = $unitDescription : '';
        $orgDataID  && $unitDetails->orgDataID != $orgDataID ? $changes['orgDataID'] = $orgDataID : '';
        $parentUnitID  && $unitDetails->parentUnitID != $parentUnitID ? $changes['parentUnitID'] = $parentUnitID : '';
        $entityID  && $unitDetails->entityID != $entityID ? $changes['entityID'] = $entityID : '';

        var_dump($changes);
        if(count($changes) > 0  && count($errors) == 0){
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_units', $changes, array('unitID'=>$unitID))){
                $errors[] = "Error updating Organisation Unit";
            } else {
                $success = "Organisation Unit updated successfully";
            }
        }

    } else {
        $unitCode ? $unitDetails['unitCode'] = $unitCode: $errors[] = "Unit Code is required";
        $unitName ? $unitDetails['unitName'] = $unitName: $errors[] = "Unit Name is required";
        $unitType ? $unitDetails['unitTypeID'] = $unitType: $errors[] = "Unit Type is required";
        $headOfUnitID ? $unitDetails['headOfUnitID'] = $headOfUnitID: "";
        $unitDescription ? $unitDetails['unitDescription'] = $unitDescription: "";
        $orgDataID ? $unitDetails['orgDataID'] = $orgDataID: $errors[] = "Organisation Data is required";
        $parentUnitID ? $unitDetails['parentUnitID'] = $parentUnitID: $unitDetails['parentUnitID'] = '0';
        $entityID ? $unitDetails['entityID'] = $entityID: $errors[] = "Entity is required";


        $unitDetails['LastUpdate'] = $config['currentDateTimeFormated'];
        var_dump($unitDetails);
        if($unitDetails){
            if(!$DBConn->insert_data('tija_units', $unitDetails)){
                $errors[] = "Error adding Organisation Unit";
            } else {
                $unitTypeID = $DBConn->lastInsertId();
            }
        }
    }
    $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	// var_dump($returnURL);
} else {
    $errors[] = "You are not authorized to perform this action.";
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);

}
var_dump($messages);
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");