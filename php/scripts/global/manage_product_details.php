
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
if ($isValidAdmin) {
	var_dump($_POST);
$productID = (isset($_POST['productID']) && !empty($_POST['productID'])) ?  Utility::clean_string($_POST['productID']): "";
$productName = (isset($_POST['productName']) && !empty($_POST['productName'])) ?  Utility::clean_string($_POST['productName']): "";
$productDescription = (isset($_POST['productDescription']) && !empty($_POST['productDescription'])) ?  Utility::clean_string($_POST['productDescription']): "";


if($productID) {
    $productDetails = Admin::tija_products(array('productID'=>$productID), true, $DBConn);
    ($productName  && $productDetails->productName != $productName) ? $changes['productName'] = $productName : '';
    ($productDescription  && $productDetails->productDescription != $productDescription) ? $changes['productDescription'] = $productDescription : '';

    if(count($errors) === 0){
        if(count($changes) > 0){
            $changes['LastUpdated'] = $dt->format('Y-m-d H:i:s');
            $changes['LastUpdatedByID'] = $adminID;          
            $update = $DBConn->update_table('tija_products', $changes, array('productID'=>$productID));
            if($update){
                $success = "Product updated successfully.";
            } else {
                $errors[] = "There was an error updating the product.";
            }
        } else {
            $errors[] = "No changes were made to the product details.";
        }
    }
} else {
    $productDetails = false;
    $productName ? $details['productName'] = $productName : $errors[] = 'Product name is required.';
    $productDescription ? $details['productDescription'] = $productDescription : $errors[] = 'Product description is required.';

    if(count($errors) ===0){
        $productID = $DBConn->insert_data('tija_products', $details);
        if($productID){
            $success = "Product added successfully.";
        } else {
            $errors[] = "There was an error adding the product.";
        }
    }
}




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