<?php

class UploadedFile {

  public $originalFileName;
  public $tmpName;
  public $newFileName;
  public $fileSize;
  public $fileType;
  public $error;
  private $size;

function  __construct ($originalFileName, $tmpName, $fileType, $fileSize, $error) {
    $this->originalFileName = $originalFileName;
    $this->tmpName = $tmpName;
    $this->fileType = $fileType;
    $this->size = $fileSize;
    $this->error = $error;
  }

 function check_file_size ($maxSize = 10485760){
    return ($this->size <= $maxSize);
  }

  /**
   *
   * Attempts to upload file to server and save it.
   * Returns false if no error occurs and a string describing the error if it does.
   * @param $maxSize: in bytes
   * @param $uploadDir: string path
   * @param $allowedTypes: array of mimetypes
   */
  function upload_error ($maxSize = 10485760, $uploadDir, $allowedTypes) {
    if ($this->error == 0) {
      $baseName = basename($this->originalFileName);
      $fileType = $this->fileType;
      $fileSize = $this->fileSize;
      if ($fileSize <= $maxSize) {
        if (in_array($fileType, $allowedTypes)) {
          if (is_uploaded_file($this->tmpName)) {
            $time=time();
            $uniqueBaseName = tempnam($uploadDir, 'theDay');
            var_dump($uniqueBaseName);
            $this->newFileName = $uniqueBaseName . '.' . File::get_extension($baseName);

          /*  $_SESSION['fileOutPut']=array('uniqueBaseName'=> $uniqueBaseName,
                                          'newFileName'=> $this->newFileName,
                                          'theTime'=> $time
                                    );*/
            if ($uniqueBaseName) {
              unlink($uniqueBaseName);
              move_uploaded_file($this->tmpName, $this->newFileName);
              if ($this->newFileName = File::move_file_to_directory($this->newFileName, $uploadDir, $baseName)) {
                return false;
              }
              return 'The file was not saved to disk. Please try again.';
            }
          }
          return "The file was not uploaded. Please try again.";
        }
        return "The file was not a valid type. Please try again with a proper type.";
      }
      return "The file you tried to upload is too large. Please try again with a file that is smaller than $maxSize Bytes.";
    }
    switch ($this->error) {
      case 1:
        return "The file you tried to upload is too large. Please try again with a smaller file."; break;
      case 2:
        return "The file you tried to upload is too large. Please try again with a smaller file."; break;
      case 3:
        return "The file was not fully uploaded. Please try again."; break;
      case 4:
        return "There was an error, and no file was uploaded."; break;
      case 6:
        return "There was an error writing the file to disk."; break;
      case 7:
        return "The file was not written to disk."; break;
      default:
        return "There was an error uploading the file."; break;
    }
  }

}

?>
