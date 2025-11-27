
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

   $orgDataID = (isset($_POST['orgSelect']) && !empty($_POST['orgSelect'])) ?  Utility::clean_string($_POST['orgSelect']): "";
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
      if ($fileError === 0) {
         if ($fileSize < 1000000) {
            $k=0;
            //   read the csv file
            if( ($handle = fopen($fileTmpName, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                  $k++;
                     if($k==1){
                        continue;
                     }
                  var_dump($data);
                    $details[] =array(
                        'entityID'=>$entityID,
                        'orgDataID'=>$orgDataID,
                        'clientName'=>$data[0],
                        'industry'=>$data[1],
                        'address'=>$data[2],
                        'postalCode'=>$data[3],
                        'city'=>$data[4],
                        'country'=>$data[5],
                        'vatNumber'=>$data[6],
                        'email'=>$data[7],
                        'phone'=>$data[8],
                        'clientContactName'=>$data[9],
                        'clientContactEmail'=>$data[10]
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

   if (count($errors) == 0) {
      if($details){
         // var_dump($details);
         $countryID = "";

         foreach ($details as $key => $value) {
            $clientArray= array(
               "clientName"=>$value['clientName'], 
               "orgDataID"=>$value['orgDataID'], 
               "entityID"=>$value['entityID'], 
               "vatNumber"=>$value['vatNumber'],
               "clientContactName"=>$value['clientContactName'],
               "clientContactEmail"=>$value['clientContactEmail'],
               'phoneNumber'=>$value['phone'],


            );
            $countryExists = false;
            foreach ($countries as $country) {
                if (strtolower($country->countryName) == strtolower($value['country'])) {
                    $countryExists = true;
                    $countryID = $country->countryID;
                    break;
                }
            }
            // if (!$countryExists) {
            //     $errors[] = "Country does not exist: " . $value['country'];
            // }
            
            $clientAddress = array(
                  "address"=>$value['address'], 
                  "postalCode"=>$value['postalCode'], 
                  "city"=>$value['city'],  
                  // "email"=>$value['email'], 
                  // "phone"=>$value['phone'], 
                  'countryID'=>$countryID ? $countryID : "",
                  "orgDataID"=>$value['orgDataID'], 
                  "entityID"=>$value['entityID'],
                  // "clientContactName"=>$value['clientContactName'], 
                  // "clientContactEmail"=>$value['clientContactEmail'],
                  'phoneNumber'=>$value['phone'],
                  'headquarters'=>"Y"
               );

           
               if($clientArray){
                  $clientArray['LastUpdateByID'] = $userDetails->ID;
                  $clientArray['DateAdded'] = date('Y-m-d H:i:s');
                  $clientArray['LastUpdate'] = date('Y-m-d H:i:s');
                  echo "<h4> Client Details</h4>";
                  var_dump($clientArray);

                  if(!$errors){
                     if(!$DBConn->insert_data('tija_clients', $clientArray)){
                        $errors[] = "Error inserting client data";

                     } else {

                      

                        $clientID = $DBConn->lastInsertId();
                        echo"<h4> Client Inserted with client ID {$clientID}</h4>";

                        if($clientID){
                           $clientAddress= array(
                              "orgDataID"=>$value['orgDataID'], 
                              "entityID"=>$value['entityID'],
                              "address"=>$value['address'], 
                              "postalCode"=>$value['postalCode'],
                               "city"=>$value['city'], 
                               'addressType'=>"OfficeAddress",
                               'headquarters'=>"Y",
                               
                              
                              );
                           $clientAddress['clientID'] = $clientID;
                           $clientAddress['LastUpdateByID'] = $userDetails->ID;
                           $clientAddress['DateAdded'] = date('Y-m-d H:i:s');
                           $clientAddress['LastUpdate'] = date('Y-m-d H:i:s');
                           echo
                           var_dump($clientAddress);
                           if(!$DBConn->insert_data('tija_client_addresses', $clientAddress)){
                              $errors[] = "Error inserting client address data";
                           } else {
                              $clientAddressID = $DBConn->lastInsertId();
                              echo"<h4> Client Address Inserted with client Address ID {$clientAddressID}</h4>";
                           } 
                        }

                        if($clientID && $clientAddressID){
                           $clientContact = array(
                              'clientID'=>$clientID,
                              'clientAddressID'=>$clientAddressID,
                             
                             
                              'contactName'=>$value['clientContactName'],
                              'contactEmail'=>$value['clientContactEmail'],
                              'contactPhone'=>$value['phone'],
                              'LastUpdateByID'=>$userDetails->ID,
                              'DateAdded'=>date('Y-m-d H:i:s'),
                              'LastUpdate'=>date('Y-m-d H:i:s')
                           );

                           var_dump($clientContact);
                           if(!$errors) {
                              if(!$DBConn->insert_data('tija_client_contacts', $clientContact)){
                                 $errors[] = "Error inserting client contact data";
                              } 
                           }

                        }
                     }
                  }
               }
                
               echo "<h4> Client Address</h4>";
               var_dump($clientAddress);
            # code...
         }
         $success = "Sales data uploaded successfully";
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