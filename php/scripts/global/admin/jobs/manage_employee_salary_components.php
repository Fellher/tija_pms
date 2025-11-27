
<?php
session_start();
$base = '../../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$categoryDetils=array();
$details= array();
$changes= array();
$success="";
if ($isValidAdmin) {
	var_dump($_POST);

    $salaryComponentID = (isset($_POST['salaryComponentID']) && !empty($_POST['salaryComponentID'])) ? Utility::clean_string($_POST['salaryComponentID']) : null;
    $salaryComponentTitle = (isset($_POST['salaryComponentTitle']) && !empty($_POST['salaryComponentTitle'])) ? Utility::clean_string($_POST['salaryComponentTitle']) : null;
    $salaryComponentDescription = (isset($_POST['salaryComponentDescription']) && !empty($_POST['salaryComponentDescription'])) ? Utility::clean_string($_POST['salaryComponentDescription']) : null;
    $salaryComponentType = (isset($_POST['salaryComponentType']) && !empty($_POST['salaryComponentType'])) ? Utility::clean_string($_POST['salaryComponentType']) : null;
    $salaryComponentCategoryID = (isset($_POST['salaryComponentCategoryID']) && !empty($_POST['salaryComponentCategoryID'])) ? Utility::clean_string($_POST['salaryComponentCategoryID']) : null;
    $salaryComponentValue = (isset($_POST['salaryComponentValue']) && !empty($_POST['salaryComponentValue'])) ? Utility::clean_string($_POST['salaryComponentValue']) : null;
    $salaryComponentValueType = (isset($_POST['salaryComponentValueType']) && !empty($_POST['salaryComponentValueType'])) ? Utility::clean_string($_POST['salaryComponentValueType']) : null;
     $applyTo = (isset($_POST['applyTo']) && !empty($_POST['applyTo'])) ? Utility::clean_string($_POST['applyTo']) : "";
    var_dump($salaryComponentCategoryID);
    if(!$salaryComponentCategoryID || $salaryComponentCategoryID == "addNew"){
        echo "<h1>Adding new category</h1>";
        $salaryComponentCategoryTitle = (isset($_POST['salaryComponentCategoryTitle']) && !empty($_POST['salaryComponentCategoryTitle'])) ? Utility::clean_string($_POST['salaryComponentCategoryTitle']) : null;
        $salaryComponentCategoryDescription = (isset($_POST['salaryComponentCategoryDescription']) && !empty($_POST['salaryComponentCategoryDescription'])) ? Utility::clean_string($_POST['salaryComponentCategoryDescription']) : null;
        echo "<h1>Adding new category with  title {$salaryComponentCategoryTitle} and  {$salaryComponentCategoryDescription} </h1>";
        if($salaryComponentCategoryTitle && $salaryComponentCategoryDescription){
            echo "<h1>Adding new category with  title {$salaryComponentCategoryTitle} and  {$salaryComponentCategoryDescription} </h1>";
            $categoryDetils['salaryComponentCategoryTitle'] = $salaryComponentCategoryTitle;
            $categoryDetils['salaryComponentCategoryDescription'] = $salaryComponentCategoryDescription;
            $categoryDetils['LastUpdatedByID'] = $userDetails->ID;
            $categoryDetils['LastUpdated'] = $config['currentDateTimeFormated'];
            if($categoryDetils){
                var_dump($categoryDetils);
                if(!$DBConn->insert_data('tija_salary_component_category', $categoryDetils)){
                    $errors[] = "Error adding Salary Component Category";
                } else {
                    $salaryComponentCategoryID = $DBConn->lastInsertId();
                }
            }
            
        } else {
            $errors[] = "Category Title and Description are required";
        }

    } 
   

    if($salaryComponentID){
        $salaryComponent = Admin::tija_salary_components(array('salaryComponentID'=>$salaryComponentID), true, $DBConn);
        if($salaryComponent){
            (isset($salaryComponentTitle) && !empty($salaryComponentTitle) && $salaryComponent->salaryComponentTitle !== $salaryComponentTitle) ? $changes['salaryComponentTitle'] = $salaryComponentTitle : "";

            ($salaryComponentDescription && $salaryComponent->salaryComponentDescription !==  $salaryComponentDescription) ? $changes['salaryComponentDescription'] = $salaryComponentDescription : "";

            ($salaryComponentType && $salaryComponent->salaryComponentType !==  $salaryComponentType) ? $changes['salaryComponentType'] = $salaryComponentType : "";

            ($salaryComponentCategoryID && $salaryComponent->salaryComponentCategoryID !==  $salaryComponentCategoryID) ? $changes['salaryComponentCategoryID'] = $salaryComponentCategoryID : "";

            // ($salaryComponentValue && $salaryComponent->salaryComponentValue !==  $salaryComponentValue) ? $changes['salaryComponentValue'] = $salaryComponentValue : "";

            ($salaryComponentValueType && $salaryComponent->salaryComponentValueType !== $salaryComponentValueType)  ? $changes['salaryComponentValueType'] = $salaryComponentValueType: "";

            ($applyTo && $salaryComponent->applyTo !== $applyTo ) ? $changes['applyTo'] = $applyTo: "";


            

            if(count($errors) === 0){
                if(count($changes) > 0){
                    if(!$DBConn->update_table('tija_salary_components', $changes, array('salaryComponentID'=>$salaryComponentID))){
                        $errors[] = "Error updating Salary Component";
                    } else {
                        $success = "Salary Component updated successfully";
                    }           
                } else {
                    $errors[] = "No changes made";
                }
            }
        } else {
            $errors[] = "Salary Component not found";
        }
    }else {
        (isset($salaryComponentTitle) && !empty($salaryComponentTitle)) ? $details['salaryComponentTitle'] = $salaryComponentTitle : $errors[] = "Salary Component Title is required";
        (isset($salaryComponentDescription) && !empty($salaryComponentDescription)) ? $details['salaryComponentDescription'] = $salaryComponentDescription : $errors[] = "Salary Component Description is required";
        (isset($salaryComponentType) && !empty($salaryComponentType)) ? $details['salaryComponentType'] = $salaryComponentType : $errors[] = "Salary Component Type is required";
        (isset($salaryComponentCategoryID) && !empty($salaryComponentCategoryID)) ? $details['salaryComponentCategoryID'] = $salaryComponentCategoryID : $errors[] = "Contribution Category is required";
        // (isset($salaryComponentValue) && !empty($salaryComponentValue)) ? $details['salaryComponentValue'] = $salaryComponentValue : $errors[] = "Salary Component Value is required";
        (isset($salaryComponentValueType) && !empty($salaryComponentValueType)) ? $details['salaryComponentValueType'] = $salaryComponentValueType : $errors[] = "Salary Component Value Type is required";
        (isset($applyTo) && !empty($applyTo)) ? $details['applyTo'] = $applyTo : $errors[] = "Apply To is required";
        var_dump($details);
        if(count($errors) === 0){
            $details['LastUpdatedByID'] = $userDetails->ID;
            $details['LastUpdated'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_salary_components', $details)){
                $errors[] = "Error adding Salary Component";
            } else {
                $success = "Salary Component added successfully";
            }
        }
    }

    $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
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