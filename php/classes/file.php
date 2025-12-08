<?php

class File {
  //! function to upload multiple files
  public static function multiple_file_upload($files, $subDir="site_files", $validFileTypesArr, $allowed_fileSize = 1024 * 1024 * 5, $config, $DBConn) {
    $errors = [];
    $success =[];
     // Split each file into individual variables
     $fileVariables = [];
     	if($files && count($files)>0){
        	foreach ($files as $key => $file) {
              // Check if the file is an array (multiple files)
           	if (is_array($file['name'])) {
               foreach ($file['name'] as $index => $name) {
                  $fileVariables[$key][$index] = [
                    'name' => $file['name'][$index],
                    'type' => $file['type'][$index],
                    'tmp_name' => $file['tmp_name'][$index],
                    'error' => $file['error'][$index],
                    'size' => $file['size'][$index],
                  ];
               }
           	} else {
               $fileVariables[$key] = $file;
           	}
       	}
     	}

     	$uploadedFilePaths = [];
     	if($fileVariables) {
        	foreach ($fileVariables as $key => $files) {
           	if (is_array($files)) {
           	  foreach ($files as $index => $file) {
                // Check if the file is uploaded
                if (!isset($file['name']) || empty($file['name'])) {
                  continue;
                }
                $uploadDir = $config['DataDir'] . '/'.$subDir.'/';
                if (!file_exists($uploadDir)) {
                  mkdir($uploadDir, 0777, true);
                }
                $fileName = $file['name'];
                $tempName = $file['tmp_name'];
                $fileType = $file['type'];
                $fileSize = $file['size'];
                $fileError = $file['error'];

                // Check if there's an error with the file
                if ($fileError) {
                  $errors[] = "Error uploading file: " . $fileError;
                  continue;
                }

                // Check file size
                if ($fileSize > $allowed_fileSize) { // 5MB
                  $errors[] = "File size exceeds the maximum limit.";
                  continue;
                }

                // Check file type
                $allowedTypes = $validFileTypesArr ? $validFileTypesArr :$config['ValidFileTypes'];
                // $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                // $fileType = mime_content_type($tempName);

                $fileExtension = File::get_extension($fileName);
                if($validFileTypesArr){
                  // get file extension
                  if(!in_array($fileExtension, $validFileTypesArr)) {
                    $errors[] = "{$fileType} File type not allowed.";
                    // continue;
                  }
                } else {
                  // get file extension
                    if (!in_array($fileType, $allowedTypes)) {
                      $errors[] = "{$fileType} File type not allowed.";
                    }
                }
                // // $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
                // if (!in_array($fileType, $allowedTypes)) {
                //   $errors[] = "{$fileType} File type not allowed.";
                //   continue;
                // }

                // File upload logic
                $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName); // Sanitize file name
                $fileName = time() . '_' . $fileName; // Add timestamp to avoid overwriting
                $newFileName = $uploadDir . $fileName;
                if (move_uploaded_file($tempName, $newFileName)) {
                  $newFileName = str_replace($config['DataDir'] , '', $newFileName);
                  $uploadedFilePaths[] = $newFileName; // Store the file path in the array
                  $fileNames[] = $fileName; // Store the file name in the array
                  $fileTypes[] = $fileType; // Store the file type in the array
                  $fileSizes[] = $fileSize; // Store the file size in the array
                  $fileExtensions[] = $fileExtension; // Store the file extension in the array


                  $success = "File uploaded successfully.";
                } else {
                  $errors[] = "Failed to upload file.";
                }
              }

			  	} else {
					$errors[] = "Upload not multiple.";
			  	}
        }
     }


	  $fileUploads = array(
				'uploadedFilePaths' => $uploadedFilePaths,
        'fileNames' => isset($fileNames) ? $fileNames : [],
        'fileTypes' => isset($fileTypes) ? $fileTypes : [],
        'fileSizes' => isset($fileSizes) ? $fileSizes : [],
        'fileExtensions' => isset($fileExtensions) ? $fileExtensions : [],

				'errors' => $errors,
				'success' => $success
			);

	  return $fileUploads;

  }

  public static function upload_file($file, $subDir="site_files", $validFileTypesArr, $allowed_fileSize = 1024 * 1024 * 5, $config, $DBConn) {
    $errors = [];
    $success =[];

      // Check if the file is uploaded
      if (!isset($file['name']) || empty($file['name'])) {
        $errors[] = "No file uploaded.";
        exit;
      }

      $uploadDir = $config['DataDir'] .$subDir.'/';
      if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        //  check that directory has been created
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
          $errors[] = "Upload directory is not writable.";
          exit;
        }
      }
      $fileName = $file['name'];
      $tempName = $file['tmp_name'];
      $fileType = $file['type'];
      $fileSize = $file['size'];
      $fileError = $file['error'];

      // Check if there's an error with the file
      if ($fileError) {
        $errors[] = "Error uploading file: " . $fileError;
      }

      // Check file size
      if ($fileSize > $allowed_fileSize) {
        $errors[] = "File size exceeds the maximum limit.";
        exit;
      }

      // Check file type
      $allowedTypes = $validFileTypesArr ? $validFileTypesArr :$config['ValidFileTypes'];
      $fileExtension = File::get_extension($fileName);

      if($validFileTypesArr){
        // check if file extension is in the array
        if(!in_array($fileExtension, $validFileTypesArr)) {
          $errors[] = "{$fileType} File type not allowed.";
        }
      } else {
        if (!in_array($fileType, $allowedTypes)) {
          $errors[] = "{$fileType} File type not allowed.";
        }
      }

      // File upload logic
      $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName); // Sanitize file name
      $fileName = time() . '_' . $fileName; // Add timestamp to avoid overwriting
      $newFileName = $uploadDir . $fileName;

      if (move_uploaded_file($tempName, $newFileName)) {
        $newFileName = str_replace($config['DataDir'] , '', $newFileName);
        $uploadedFilePaths = $newFileName; // Store the file path in the array
        $success = "File uploaded successfully.";
      } else {
        $errors[] = "Failed to upload file.";
      }

      $fileDetails = array(
        'uploadedFilePaths' => $uploadedFilePaths,
        'fileName' => $fileName,
        'fileType' => $fileType,
        'fileSize' => $fileSize,
        'fileExtension' => $fileExtension,
        'fileDestination' => $uploadDir,
        'errors' => $errors,
        'success' => $success,
        'status' => count($errors) > 0 ? 'error' : 'success'
      );

      return $fileDetails;

  }




  public static function path_join() {
    $args = func_get_args();
    $paths = array();
    $absolute = false;
    foreach ($args as $i=>$arg) {
      if (substr($args[0], 0, 1) == '/') {
        $absolute = true;
      }
      $paths = array_merge($paths, (array)$arg);
    }
       $paths = array_map(function($p){return trim($p, "/"); } , $paths);
    // Utility::print_array($paths);
    $paths = array_filter($paths);
    return ($absolute ? '/' : '') . join('/', $paths);
  }

  public static function move_file_to_directory ($oldLocation, $newDirectory, $baseName) {
    $errorBool = false;
    // $_SESSION['fileOutPut']['newFileName2']= $oldLocation;

    if (File::is_valid_dir($newDirectory)) {
      $dateToday = time();
      $uniqueBaseName = tempnam($newDirectory, $dateToday);
      // $_SESSION['fileOutPut']['uniqueBaseName2']= $uniqueBaseName;
      $uniqueName=explode('.', $uniqueBaseName);
     $path= $uniqueName[0];
       // $_SESSION['fileOutPut']['path']= $path;
      if ($uniqueBaseName) {
        $newFileName = $path.'_'. $baseName;// . '.' . File::get_extension($oldLocation);
        unlink($uniqueBaseName);
      }
      if (!copy($oldLocation, $newFileName)) {
        $errorBool = true;
      } else {
        unlink($oldLocation);
      }
    } else {
      $errorBool = true;
    }
    return $errorBool ? false : $newFileName;
  }

  public static function get_extension ($filename) {
    $pathInfo = pathinfo($filename);
    return $pathInfo['extension'];
  }

  public static function strip_extension ($fileName) {
    if (File::get_extension($fileName)) {
      $fileName = preg_replace(File::get_extension($fileName) . '$', '', $fileName);
      return rtrim($fileName, '.');
    } else {
      return $fileName;
    }
  }

  public static function is_valid_dir ($path) {
    if (is_dir($path)) {
      return is_writable($path);
    } else {
      return mkdir($path, 0777, true);
    }
  }

  public static function delete_file ($filename) {
    return unlink($filename);
  }

  //! function to upload file

  public static function uploadFile($fileArr, $validFileTypesArr, $fileSize ,$config, $DBConn) {

    $fileName = Utility::clean_string($fileArr['name']);
    $fileNameTemp = $fileArr['tmp_name'];

    $fileType = $fileArr['type'];
    $fileSize = $fileArr['size'];
    $fileExtension = File::get_extension($fileName);
    $fileNewName = Utility::generate_unique_string(20).'.'.$fileExtension;
    $fileDestination = '';
    if(File::is_valid_dir($config['DataDir'])) {
      $fileDestination = File::path_join($config['DataDir'], $fileName);
    } else {
      $fileError = true;
      $fileErrorArr['fileDestination'] = 'Invalid file destination';
    }
    $fileValid = true;
    $fileError = false;
    $fileErrorArr =array();
    if(!in_array($fileExtension, $validFileTypesArr)) {
      $fileValid = false;
      $fileError = true;
      $fileErrorArr['fileType'] = 'Invalid file type';
    }

    if($fileArr['size'] > $fileSize) {
      $fileValid = false;
      $fileError = true;
      $fileErrorArr['fileSize'] = 'File size too large';
    }
    if($fileValid && $fileDestination ) {
      if(move_uploaded_file($fileNameTemp, $fileDestination)) {
        return $fileNewName;
      } else {
        $fileError = true;
        $fileErrorArr['fileUpload'] = 'File upload failed';
      }
    }
    return $fileErrorArr ?fileErrorArr : "File upload failed"; ;


  }
  // function to create a directory if it does not exist and create files if a given path is provided
  public static function create_directory_files ($path, $isFile = false) {
    // $dirs = explode('/', $path);
    // $currentPath = '';
    // foreach ($dirs as $dir) {
    //   $currentPath .= $dir . '/';
    //   if (!file_exists($currentPath)) {
    //     mkdir($currentPath, 0777, true);
    //   }
    // }
    // if ($isFile) {
    //   $filePath = rtrim($currentPath, '/') ;
    //   if (!file_exists($filePath)) {
    //     file_put_contents($filePath, '');
    //   }
    // }
    // return true;

    $subMenuDirPath = dirname($path); // Get the directory path
    $subMenuPageFile = $path; // Full path to the sub-menu file
    $subMenuPage = basename($path); // Get the file name from the path
    // var_dump($subMenuDirPath);

    // Check if the directory for sub-menu files exists, if not, create it
    if (!is_dir($subMenuDirPath)) {
       // Attempt to create the directory recursively with 0755 permissions
       if (!mkdir($subMenuDirPath, 0755, true)) {
          // If directory creation fails, display an error and stop further processing
          // Note: The initial "The requested page does not exist." error would have already been shown.
          Alert::error("Failed to create directory for sub-menu page: {$subMenuDirPath}", true);
          return; // Exit to prevent attempting to create the file in a non-existent directory
       }
    }

    // Now that the directory is ensured to exist, check if the specific sub-menu file exists
    if (!file_exists($subMenuPageFile)) {
       // Define default content for the new file
       $defaultContent = "<?php\n// This is a new sub-menu reporting file for '{$subMenuPage}'\n// Add your content here.\n?>";

       // Attempt to create the file with the default content
       if (file_put_contents($subMenuPageFile, $defaultContent) === false) {
          // If file creation fails, display an error and stop further processing
          // Note: The initial "The requested page does not exist." error would have already been shown.
          Alert::error("Failed to create sub-menu page file: {$subMenuPageFile}", true);
          return; // Exit as the file could not be created
       }
    }
    return true;
  }
}

?>