<?php

class Image {

  private $fileName;
  private $fileType;
  private $width;
  private $height;


 function __construct  ($imageFileName, $imageType) {
    $this->fileName=$imageFileName;
    if ($imageType) $this->fileType = $imageType;
    list($this->width, $this->height) = getimagesize($this->fileName);
  }

  function __destruct () {

  }

  /**
   * Converts to png. Takes the filename which the png file will be saved as as argument.
   * returns true on success.
   * @param $PNGFileName: new file name.
   */
  function convert_to_png ($PNGFileName) {
    $imgLocation = $this->fileName;
    $imgType = mime_content_type($imgLocation); /* TODO: mime_content_type is deprecated, use something else here */
    switch ($this->fileType) {
      case 'image/png':
      case 'image/x-png':
        $source = imagecreatefrompng($this->fileName);
      break;

      case 'image/jpeg':
      case 'image/pjpeg':
        $source = imagecreatefromjpeg($this->fileName);
      break;

      case 'image/gif':
        $source = imagecreatefromgif($this->fileName);
      break;

    }
    $finalImage = imagecreatetruecolor($this->width,$this->height);
    imagecopy($finalImage, $source, 0, 0, 0, 0, $this->width, $this->height);
    return imagepng($finalImage, $PNGFileName);
  }

  /**
   *
   * Resize an image to a certain width without cropping any part of the image.
   * @param $newFileName: file name to save it to.
   * @param $newWidth: new width of the file.
   */
  function resize_image_w ($newFileName, $newWidth){
    $this->width = $this->width;
    $this->height = $this->height;
    if($this->width <= $newWidth){ //Width is already less than required width.
      $oldImage = imagecreatefrompng($this->fileName);
      $newImage = imagecreatetruecolor($this->width,$this->height);
      $finalImage = imagecopy($newImage, $oldImage, 0, 0, 0, 0, $this->width, $this->height);
      return imagepng($newImage, $newFileName);
    } else {
      $ratio = $newWidth / $this->width;
      $newHeight = $this->height * $ratio;
      $finalImage = imagecreatetruecolor($newWidth, $newHeight);
      $source = imagecreatefrompng($this->fileName);
      imagecopyresized($finalImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $this->width, $this->height);
      return (imagepng($finalImage, $newFileName) && imagedestroy($source));
    }
  }

  /**
   *
   * Resize an image to a certain height without cropping any part of the image.
   * @param $newFileName:
   * @param $newHeight
   */
  function resize_image_h($newFileName, $newHeight){
    if ($this->height <= $newHeight) {
      $oldImage = imagecreatefrompng($this->fileName);
      $newImage = imagecreatetruecolor($this->width,$this->height);
      $finalImage = imagecopy($newImage,$oldImage,0,0,0,0,$this->width,$this->height);
      return imagepng($newImage,$newFileName);
    } else {
      $ratio = $newHeight / $this->height;
      $newWidth = $this->width * $ratio;
      $finalImage = imagecreatetruecolor($newWidth,$newHeight);
      $source = imagecreatefrompng($this->fileName);
      imagecopyresized($finalImage,$source,0,0,0,0,$newWidth, $newHeight, $this->width, $this->height);
      return (imagepng($finalImage,$newFileName) && imagedestroy($source));
    }
  }//end of function resizeImageH()

  /**
   *
   * Resize to required width and height.
   * Crops equal lengths on left and right if the image is proportionally wider than required,
   * or top and bottom if it's proportionally taller than required.
   * @param $newFileName
   * @param $newWidth
   * @param $newHeight
   */
  function resize_image ($newFileName, $newWidth, $newHeight){
    if ($this->width <= $newWidth && $this->height <= $newHeight){
      $oldImage = imagecreatefrompng($this->fileName);
      $newImage = imagecreatetruecolor($this->width,$this->height);
      imagecopy($newImage,$oldImage,0,0,0,0,$this->width,$this->height);
      return imagepng($newImage, $newFileName);
    }elseif($this->width > $newWidth && $this->height > $newHeight){
      //Both the new width and new height are greater than the required measurements.
      $newRatio = $newWidth / $newHeight;
      $oldRatio = $this->width / $this->height;
      if ($newRatio > $oldRatio) {
        $uHeight = $this->height * ($newWidth/$this->width);
        $extra = $uHeight-$newHeight;
        $trim = $extra/2;
        $reduced = imagecreatetruecolor($newWidth, $uHeight);
        $source = imagecreatefrompng($this->fileName);
        imagecopyresized($reduced, $source, 0, 0, 0, 0, $newWidth, $uHeight, $this->width, $this->height);
        $final = imagecreatetruecolor($newWidth,$newHeight);
        imagecopy($final,$reduced,0,0,0,$trim,$newWidth,$newHeight);
        return imagepng($final,$newFileName);
      } else {
        $uWidth = $this->width * ($newHeight / $this->height);
        $extra = $uWidth - $newWidth;
        $trim = $extra / 2;
        $reduced = imagecreatetruecolor($uWidth, $newHeight);
        $source = imagecreatefrompng($this->fileName);
        imagecopyresized($reduced, $source, 0, 0, 0, 0, $uWidth, $newHeight, $this->width, $this->height);
        $final = imagecreatetruecolor($newWidth,$newHeight);
        imagecopy($final, $reduced, 0, 0, $trim, 0, $newWidth, $newHeight);
        return imagepng($final,$newFileName);
      }
    } elseif ($this->width > $newWidth && $this->height <= $newHeight) {
      //The height is less than the required height, but width is greater than newWidth.
      //Resize to newWidth without cropping any part.
      return $this->resize_image_w($this->fileName, $newFileName, $newWidth);
    } else {
      //The width is less than the required width, but height is greater than newWidth.
      //Resize to newHeight without cropping any part.
      return $this->resize_image_h($this->fileName, $newFileName, $newHeight);
    }
  }

  public static function upload_image($fileArray, $uploadDir, $width, $height, $allowedImageTypes, $maxUploadSize=1048576) {
    $uploadedFile = new UploadedFile($fileArray['name'],
                                     $fileArray['tmp_name'],
                                     $fileArray['type'],
                                     $fileArray['size'],
                                     $fileArray['error']);
    $errors = array(); $errorBool = false;
    if  (File::is_valid_dir($uploadDir)) {
      $errorBool =false;
      $uploadError = $uploadedFile->upload_error($maxUploadSize, $uploadDir, $allowedImageTypes);
      if ($uploadError) {
        $errorBool = true;
        $errors[] = $uploadError;
      } else {
        $tmpFilePath = $uploadedFile->newFileName;
        $image = new Image($tmpFilePath, $uploadedFile->fileType);
        $pathInfo = pathinfo($tmpFilePath);
        $pngFileName = $uploadDir . preg_replace("/.{$pathInfo['extension']}$/", '', $pathInfo['basename']) . '.png';
        if ($image->convert_to_png($pngFileName) && File::delete_file($tmpFilePath)) {
          $image = new Image($pngFileName, false);
          if ($width && $height) {
            $resizeImage = $image->resize_image($pngFileName, $width, $height);
          } elseif ($width) {
            $resizeImage = $image->resize_image_w($pngFileName, $width);
          } elseif ($height) {
            $resizeImage = $image->resize_image_h($pngFileName, $height);
          }
          if (!$resizeImage) {
            $errorBool = true;
            $errors[] = 'There was an error in resizing a file.';
          }
        } else {
          $errorBool = true;
          $errors[] = 'There was an error when the system tried to convert a file to png.';
        }
      }
    } else {
      $errorBool = true;
      $errors[] = 'The system cannot write to the upload directory. Please contact support for assistance.';
    }
    return $errorBool ? false : $pngFileName;
  }

  public static function upload_images ($filesArray, $uploadDir, $width, $height, $allowedImageTypes, $maxUploadSize = 1048576) {
    $i = 0;
    $errorBool = false;
    $errors = array();
    $uploadedFiles = array();
    foreach ($filesArray['name'] as $name) {
      if ($errorBool) break;
      $uploadedFile = new UploadedFile($filesArray['name'][$i],
                                       $filesArray['tmp_name'][$i],
                                       $filesArray['type'][$i],
                                       $filesArray['size'][$i],
                                       $filesArray['error'][$i]);

      if  (File::is_valid_dir($uploadDir)) {
        $uploadError = $uploadedFile->upload_error($maxUploadSize, $uploadDir, $allowedImageTypes);
        if ($uploadError) {
          $errorBool = true;
          $errors[] = $uploadError;
        } else {
          $tmpFilePath = $uploadedFile->newFileName;
          $image = new Image($tmpFilePath, $uploadedFile->fileType);
          $pathInfo = pathinfo($tmpFilePath);
          $pngFileName = $uploadDir . '/' . preg_replace("/.{$pathInfo['extension']}$/", '', $pathInfo['basename']) . '.png';
          if ($image->convert_to_png($pngFileName) && File::delete_file($tmpFilePath)) {
            $image = new Image($pngFileName, false);
            $resizeImage = $image->resize_image_w($pngFileName, 600);
            if (!$resizeImage) {
              $errorBool = true;
            } else {
              $uploadedFiles[] = $pngFileName;
            }
          } else {
            $errorBool = true;
          }
        }
      } else {
        $errorBool = true;
      }
      $i++;
    }
    return $errorBool ? false : $uploadedFiles;
  }

}
?>
