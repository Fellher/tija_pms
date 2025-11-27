<?php
session_start();
$base = '../../../../../';
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

   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $productID = (isset($_POST['productID']) && !empty($_POST['productID'])) ?  Utility::clean_string($_POST['productID']): "";

   $usersFile = $_FILES['users_file'];
   $fileName = $usersFile['name'];
   $fileTmpName = $usersFile['tmp_name'];
   $fileType = $usersFile['type'];
   $fileError = $usersFile['error'];
   $fileSize = $usersFile['size'];
   $fileExt = explode('.', $fileName);
   $fileActualExt = strtolower(end($fileExt));
   $allowed = array('csv');
   // if (in_array($fileActualExt, $allowed)) {
   //     if ($fileError === 0) {
   //         if ($fileSize < 1000000) {
   //             $fileNameNew = uniqid('', true).".".$fileActualExt;
   //             $fileDestination = 'uploads/'.$fileNameNew;
   //             move_uploaded_file($fileTmpName, $fileDestination);
   //             echo "File uploaded successfully";
   //         } else {
   //             echo "Your file is too big!";
   //         }
   //     } else {
   //         echo "There was an error uploading your file!";
   //     }
   // } else {
   //     echo "You cannot upload files of this type!";
   // }

    $usersFile = $_FILES['users_file'];
    $fileName = $usersFile['name'];
    $fileTmpName = $usersFile['tmp_name'];
    $fileType = $usersFile['type'];
    $fileError = $usersFile['error'];
    $fileSize = $usersFile['size'];
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = array('csv');

    $allEmployees= Data::users([], false, $DBConn);
    var_dump($allEmployees);
    $people = Core::user([], false, $DBConn);
    // var_dump($people);
    
    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 1000000) {
                // Read the CSV file
                if (($handle = fopen($fileTmpName, 'r')) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        // Process each row of the CSV
                        // Example: Insert into database or perform other actions
                        var_dump($data);
                        $peopleDetails = array();
                        $peopleDetails['FirstName'] = $data[0] ? $data[0] : "";
                        $peopleDetails['Surname'] = $data[1] ? $data[1] : "";
                        $peopleDetails['OtherNames'] = $data[2] ? $data[2] : "";

                        if(  empty($peopleDetails['Surname'])  && empty($peopleDetails['OtherNames'])){
                            $peopleDetails['FirstName'] =explode(" ",  $data[0]);
                        $names = preg_split('/\s+|,/', $data[0]);
                    
                        $names = array_map('trim', $names);
                        $names = array_filter($names);
                        $names = array_values($names);

                            // var_dump($names);
                            $peopleDetails['FirstName'] = $names[0] ? $names[0] : "";
                            $peopleDetails['Surname'] = $names[1] ? $names[1] : "";
                            $peopleDetails['OtherNames'] = count($names) > 2 ? implode(" ", array_slice($names, 2)) : "";
                        }
                        $peopleDetails['Email'] = $data[3] ? $data[3] : "";


                        var_dump($peopleDetails);
                        $employeeDetails = array();
                        $employeeDetails['orgDataID'] = $orgDataID ? $orgDataID : "";
                        $employeeDetails['entityID'] = $entityID ? $entityID : "";
                        $employeeDetails['phoneNo'] = $data[4];
                        $employeeDetails['payrollNo'] = $data[5];
                        $employeeDetails['pin'] = $data[6];
                       
                        $employeeDetails['dateOfBirth'] = $data[7];
                        $employeeDetails['gender'] = $data[8];
                        $employeeDetails['basicSalary'] = floatval(str_replace(',', '', $data[12]));
                        // $employeeDetails['departmentName'] = $data[16]? $data[16] : "";

                        var_dump($employeeDetails);

                        if($peopleDetails['Email'] && $peopleDetails['FirstName'] && $peopleDetails['Surname'] ){
                            if(!$DBConn->insert_data('people', $peopleDetails)){
                                $errors[] = "Error inserting people data";
                            } else {
                              $personID = $DBConn->lastInsertId();
                              if($personID){
                                 $employeeDetails['ID'] = $personID;
                                 $employeeDetails['UID']=bin2hex(openssl_random_pseudo_bytes(32));
                                 if(!$DBConn->insert_data('user_details', $employeeDetails)){
                                       $errors[] = "Error inserting employee data";
                                 } else {
                                    $tokens = Core::add_registration_tokens($personID, $DBConn);
                                    if($tokens){
                                       $firstName = $peopleDetails['FirstName'];
                                       $surname = $peopleDetails['Surname'];
                                       $otherNames = $peopleDetails['OtherNames'];
                                        // $sendEmail=Applicants::send_registration_email($personDetails, $personID, $tokens, $DBConn);
                                       // $s='recruitment.racg.co.ke';
                                       
                                       $link = "http://{$config['siteRoot']}/html/?s=user&p=complete_registration&t1={$tokens[0]}&t2={$tokens[1]}&ID={$personID}";
                                       // print "<p> The link is <a target='_blank' href=". $link ."> Link</a>";
                                       $plink="<a target='_blank' href=". $link ."> complete registration</a>"; 			

                                       $name = $firstName . ($otherNames ? " {$otherNames}" : '') . ($surname ? " {$surname}" : '');
                                       $messageBody="<p> Hello {$name} </p>
                                                   <p>You have been successfully added to the {$config['siteName']} Portal<p> 
                                                   <p> Please click in the link below to complete your registration and verify your email </p>
                                                   <a style='display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center; text-decoration: none;vertical-align: middle;cursor: pointer;
                                                      -webkit-user-select: none;
                                                      -moz-user-select: none;
                                                      user-select: none;
                                                      background-color: blue;
                                                      border: 1px solid transparent;
                                                      padding: 0.375rem 0.75rem;
                                                      font-size: 1rem;
                                                      border-radius: 0.25rem;' 
                                                      href='".$link."'> Complete registration & Verify Email ID
                                                   </a>

                                                   <p> Regards</p>
                                                   <p> {$config['siteName']}</p>
                                                   ";


                           
                                       $toEmail= $email;
                                       
                                       $subject = $config['siteName']  ;
                                       $toName= $name;
                                       $bodyNohtml = 'Hello ' . PHP_EOL .
                                                ' Please  click on the link below/ copy paste it to your browser to set Up your Account & verify Your eamail Id' . PHP_EOL .

                                             "{$link}".PHP_EOL  .PHP_EOL  .
                                             'Regards';

                                       $send = true;
                                    } else {
                                        $errors[] = "Error inserting employee data";
                                    }
                                 }
                              }
                            }
                        }
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
} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
if (count($errors) == 0) {
	$success = "Statement uploaded successfully";
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
	
	
	var_dump($returnURL);
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
// header("location:{$base}html/{$returnURL}");?>