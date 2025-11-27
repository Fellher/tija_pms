
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
if ($isValidUser) {
	var_dump($_POST);

   var_dump($_FILES);

   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $salesUpload = $_FILES['salesUpload'];
   $fileName = $salesUpload['name'];
   $fileTmpName = $salesUpload['tmp_name'];
   $fileType = $salesUpload['type'];
   $fileError = $salesUpload['error'];
   $fileSize = $salesUpload['size'];
   $fileExt = explode('.', $fileName);
   $fileActualExt = strtolower(end($fileExt));
   $allowed = array('csv');
   $allEmployees= Data::users([], false, $DBConn);

   $people = Core::user([], false, $DBConn);
   // var_dump($people);

   if (in_array($fileActualExt, $allowed)) {
      $k=0;
      if ($fileError === 0) {
         if ($fileSize < 1000000) {
            //   read the csv file
            if( ($handle = fopen($fileTmpName, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                  $k++;
                     if($k==1){
                        continue;
                     }
                  // var_dump($data);
                     $details[] =array(
                           'salesCaseName' =>$data[0],
                           'clientName'=>$data[1],
                           'product'=>$data[2],
                           'contactPerson'=>$data[3],
                           'contactEmail'=>$data[4],
                           'businessUnit'=>$data[5],
                           'saleStatusLevel'=>$data[6],
                           'salesCaseEstimate'=>$data[7],
                           'expectedCloseDate'=>$data[8]


                     );
                  //   process each row of csv skipping the first row
                  //   var_dump($data);
                }
                fclose($handle);
            }
         } else {
               echo "Your file is too big!";
         }
      } else {
         echo "There was an error uploading your file!";
      }
   } else {
       echo "You cannot upload files of this type!";
   }
   $countries = Data::countries([], false, $DBConn);

   $clients = Client::clients([], false, $DBConn);
   // var_dump($clients);

   if (count($errors) == 0) {
      if($details){
         var_dump($details);


         // foreach ($details as $key => $value) {
         //    $clientArray= array("clientName"=>$value['clientName'], "orgDataID"=>$value['orgDataID'], "entityID"=>$value['entityID']);
         //    $countryExists = false;
         //    foreach ($countries as $country) {
         //        if (strtolower($country->countryName) == strtolower($value['country'])) {
         //            $countryExists = true;
         //            $countryID = $country->countryID;
         //            break;
         //        }
         //    }
         //    if (!$countryExists) {
         //        $errors[] = "Country does not exist: " . $value['country'];
         //    }
            
         //    $clientAddress= array("address"=>$value['address'], "postalCode"=>$value['postalCode'], "city"=>$value['city'], "vatNumber"=>$value['vatNumber'], "email"=>$value['email'], "phone"=>$value['phone'], "clientContactName"=>$value['clientContactName'], "clientContactEmail"=>$value['clientContactEmail']);
         //    # code...
         // }
         // $success = "Sales data uploaded successfully";
      }
   } else {
      $errors[] = 'There was an error uploading your file.';
   }
    
   var_dump($errors);

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