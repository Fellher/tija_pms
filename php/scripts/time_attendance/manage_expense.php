<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";

/**
 * Sync expense file to tija_project_files table
 * This ensures files uploaded with expenses are also available in project file management
 *
 * @param string $filePath The relative file path from DataDir
 * @param int $projectID The project ID
 * @param int $userID The user ID who uploaded the file
 * @param string $description Optional description
 * @param object $DBConn Database connection object
 * @param array $config Configuration array
 * @param array $fileMetadata Optional metadata array with fileNames, fileSizes, fileTypes, fileExtensions
 * @return bool Success status
 */
function syncExpenseFileToProjectFiles($filePath, $projectID, $userID, $description, $DBConn, $config, $fileMetadata = null) {
	try {
		if (!$projectID || empty($filePath)) {
			return false;
		}

		// Check if file already exists in project_files (avoid duplicates)
		$existingFile = $DBConn->retrieve_db_table_rows(
			'tija_project_files',
			array('fileID'),
			array('fileURL' => $filePath, 'projectID' => $projectID, 'Suspended' => 'N')
		);

		if ($existingFile && !empty($existingFile)) {
			// File already synced, skip
			return true;
		}

		// Get file information - ensure all details are captured
		$fileInfo = pathinfo($filePath);

		// Extract filename from path (stored filename)
		$fileName = basename($filePath);

		// Get file extension
		$fileExtension = isset($fileInfo['extension']) ? strtolower($fileInfo['extension']) : '';

		// For expense files, try to get original filename from metadata if available
		$fileOriginalName = $fileName;
		if ($fileMetadata && isset($fileMetadata['fileNames']) && is_array($fileMetadata['fileNames'])) {
			// Find matching file in metadata array by comparing paths
			$fileIndex = array_search($filePath, $fileMetadata['filePaths'] ?? array());
			if ($fileIndex !== false && isset($fileMetadata['fileNames'][$fileIndex])) {
				$fileOriginalName = $fileMetadata['fileNames'][$fileIndex];
			}
		}

		// If not found in metadata, try to extract from stored filename
		if ($fileOriginalName === $fileName) {
			if (preg_match('/^\d+_(.+)$/', $fileName, $matches)) {
				// File has timestamp prefix, extract original name
				$fileOriginalName = $matches[1];
			} else {
				// Use basename as original name
				$fileOriginalName = isset($fileInfo['basename']) ? $fileInfo['basename'] : $fileName;
			}
		}

		// Get file size from metadata if available
		if ($fileMetadata && isset($fileMetadata['fileSizes']) && is_array($fileMetadata['fileSizes'])) {
			$fileIndex = array_search($filePath, $fileMetadata['filePaths'] ?? array());
			if ($fileIndex !== false && isset($fileMetadata['fileSizes'][$fileIndex]) && $fileMetadata['fileSizes'][$fileIndex] > 0) {
				$fileSize = intval($fileMetadata['fileSizes'][$fileIndex]);
			}
		}

		// Get MIME type from metadata if available
		if ($fileMetadata && isset($fileMetadata['fileTypes']) && is_array($fileMetadata['fileTypes'])) {
			$fileIndex = array_search($filePath, $fileMetadata['filePaths'] ?? array());
			if ($fileIndex !== false && isset($fileMetadata['fileTypes'][$fileIndex]) && !empty($fileMetadata['fileTypes'][$fileIndex])) {
				$fileMimeType = $fileMetadata['fileTypes'][$fileIndex];
			}
		}

		// Ensure fileURL is properly formatted (relative path from DataDir)
		$fileURL = $filePath;
		if (strpos($filePath, $config['DataDir']) === 0) {
			$fileURL = str_replace($config['DataDir'], '', $filePath);
		}

		// Get file size - try to get from actual file on disk
		$fullPath = $config['DataDir'] . $fileURL;
		$fileSize = 0;
		if (file_exists($fullPath)) {
			$fileSize = filesize($fullPath);
		} elseif (file_exists($config['DataDir'] . $filePath)) {
			// Try alternative path
			$fileSize = filesize($config['DataDir'] . $filePath);
		}

		// Determine MIME type based on extension (comprehensive list)
		$mimeTypes = array(
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xls' => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'ppt' => 'application/vnd.ms-powerpoint',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'csv' => 'text/csv',
			'txt' => 'text/plain',
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed'
		);
		$fileMimeType = isset($mimeTypes[$fileExtension]) ? $mimeTypes[$fileExtension] : 'application/octet-stream';

		// Validate required fields before inserting
		if (empty($fileName) || empty($fileOriginalName) || empty($fileURL) || empty($projectID) || empty($userID)) {
			error_log("Warning: Missing required fields for project file sync. File: {$fileOriginalName}, Project: {$projectID}");
			return false;
		}

		// Prepare data for tija_project_files - ensure all fields are properly set
		$projectFileData = array(
			'projectID' => intval($projectID), // Required: NOT NULL
			'taskID' => null, // Optional: Expenses are not linked to specific tasks
			'fileName' => $fileName, // Required: NOT NULL - stored filename
			'fileOriginalName' => $fileOriginalName, // Required: NOT NULL - user's original filename
			'fileURL' => $fileURL, // Required: NOT NULL - relative path from DataDir
			'fileType' => !empty($fileExtension) ? strtolower($fileExtension) : '', // Optional but recommended
			'fileSize' => intval($fileSize), // Optional but recommended - size in bytes
			'fileMimeType' => !empty($fileMimeType) ? $fileMimeType : 'application/octet-stream', // Optional but recommended
			'category' => 'expense', // Optional - Category to identify files from expenses
			'version' => '1.0', // Optional - Default version
			'uploadedBy' => intval($userID), // Required: NOT NULL - user who uploaded
			'description' => $description ? substr($description, 0, 500) : 'File uploaded with expense entry', // Optional - description
			'isPublic' => 'N', // Optional - Default 'N'
			'downloadCount' => 0, // Optional - Default 0
			'DateAdded' => 'NOW()', // Optional - Auto timestamp
			'Suspended' => 'N' // Optional - Default 'N'
		);

		// Insert into tija_project_files
		if ($DBConn->insert_data('tija_project_files', $projectFileData)) {
			error_log("Synced expense file to project_files: {$fileOriginalName} (Project: {$projectID})");
			return true;
		} else {
			error_log("Warning: Failed to sync expense file to project_files: {$fileOriginalName}");
			return false;
		}
	} catch (Exception $e) {
		error_log("Error syncing expense file to project_files: " . $e->getMessage());
		return false;
	}
}
var_dump($_POST);
if ($isValidUser) {
	var_dump($_POST);
	var_dump($_FILES);


	$userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ? Utility::clean_string($_POST['userID']) : "";
	$projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";

	$expenseTypeID = (isset($_POST['expenseTypeID']) && !empty($_POST['expenseTypeID'])) ? Utility::clean_string($_POST['expenseTypeID']) : "";
	$expenseDate = (isset($_POST['expenseDate']) && !empty($_POST['expenseDate']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['expenseDate']))) ? Utility::clean_string($_POST['expenseDate']) : "";
	$expenseAmount = (isset($_POST['expenseAmount']) && !empty($_POST['expenseAmount'])) ? Utility::clean_string($_POST['expenseAmount']) : "";
	$expenseDescription = (isset($_POST['expenseDescription']) && !empty($_POST['expenseDescription'])) ? $_POST['expenseDescription']: "";
	$expenseID = (isset($_POST['expenseID']) && !empty($_POST['expenseID'])) ? Utility::clean_string($_POST['expenseID']) : "";

	$expenseDocuments = (isset($_FILES['expenseDocuments']) && !empty($_FILES['expenseDocuments'])) ? $_FILES['expenseDocuments'] : null;


	// upload expense documents multiple format using FileUpload class
	if ($expenseDocuments && is_array($expenseDocuments) && count($expenseDocuments) > 0) {
		// Define allowed file extensions for expense documents (File class expects extensions, not MIME types)
		$allowedExtensions = [
			'pdf',      // PDF files
			'jpg',      // JPEG images
			'jpeg',     // JPEG images
			'png',      // PNG images
			'gif',      // GIF images
			'webp',     // WebP images
			'doc',      // Word documents (old)
			'docx',     // Word documents (new)
			'xls',      // Excel spreadsheets (old)
			'xlsx',     // Excel spreadsheets (new)
			'csv'       // CSV files
		];

		$fileUpload = File::multiple_file_upload($_FILES, "expense_files", $allowedExtensions, $config['MaxUploadedFileSize'], $config, $DBConn);
		if ($fileUpload['success'] && !empty($fileUpload['uploadedFilePaths'])) {
			// Store both file paths and metadata for better syncing
			$expenseDocuments = $fileUpload['uploadedFilePaths'];
			// Store file metadata in session or pass along for syncing
			$_SESSION['expense_file_metadata'] = array(
				'fileNames' => isset($fileUpload['fileNames']) ? $fileUpload['fileNames'] : array(),
				'fileSizes' => isset($fileUpload['fileSizes']) ? $fileUpload['fileSizes'] : array(),
				'fileTypes' => isset($fileUpload['fileTypes']) ? $fileUpload['fileTypes'] : array(),
				'fileExtensions' => isset($fileUpload['fileExtensions']) ? $fileUpload['fileExtensions'] : array()
			);
		} elseif ($fileUpload['success'] && !empty($fileUpload['filePaths'])) {
			// Fallback for older format
			$expenseDocuments = $fileUpload['filePaths'];
		} else {
			$errors = array_merge($errors, $fileUpload['errors']);
		}
	} else {
		$expenseDocuments = null;
	}



	if ($expenseID) {
		$expenseDetails = Work::project_expenses(array("expenseID"=>$expenseID), true, $DBConn);
		($expenseTypeID && $expenseTypeID !== $expenseDetails->expenseTypeID) ? $changes['expenseTypeID'] = $expenseTypeID : "";
		($expenseDate && $expenseDate !== $expenseDetails->expenseDate) ? $changes['expenseDate'] = $expenseDate : "";
		($expenseAmount && $expenseAmount !== $expenseDetails->expenseAmount) ? $changes['expenseAmount'] = $expenseAmount : "";
		($expenseDescription && $expenseDescription !== $expenseDetails->expenseDescription) ? $changes['expenseDescription'] = $expenseDescription : "";
		($userID && $userID !== $expenseDetails->userID) ? $changes['userID'] = $userID : "";
		$expenseDocuments && $expenseDocuments !== $expenseDetails->expenseDocuments ? $changes['expenseDocuments'] = $expenseDocuments : "";
		$expenseDocuments ? $details['expenseDocuments'] = $expenseDocuments : "";




		if (count($errors) === 0 ) {
			if ($changes) {
				$changes['LastUpdate'] = $config['currentDateTimeFormated'];
				if (!$DBConn->update_table('tija_project_expenses', $changes, array("expenseID"=>$expenseID))) {
					$errors[] = "Error saving the expense updates to the database";
				} else {
					$success = "Successfully updates expense to the database";

					// Sync new expense files to tija_project_files if projectID exists
					if (isset($changes['expenseDocuments']) && $expenseDocuments) {
						$expenseDetails = Work::project_expenses(array("expenseID"=>$expenseID), true, $DBConn);
						if ($expenseDetails && $expenseDetails->projectID) {
							// Handle both array and JSON string formats
							$filePaths = array();
							if (is_array($expenseDocuments)) {
								$filePaths = $expenseDocuments;
							} elseif (is_string($expenseDocuments)) {
								// Try to decode as JSON first
								$decoded = json_decode($expenseDocuments, true);
								if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
									$filePaths = $decoded;
								} else {
									// Try comma-separated string
									$filePaths = array_filter(array_map('trim', explode(',', $expenseDocuments)));
								}
							}

							// Get file metadata from session if available
							$fileMetadata = isset($_SESSION['expense_file_metadata']) ? $_SESSION['expense_file_metadata'] : null;
							if ($fileMetadata) {
								$fileMetadata['filePaths'] = $filePaths; // Add file paths to metadata
							}

							foreach ($filePaths as $filePath) {
								if (!empty($filePath)) {
									syncExpenseFileToProjectFiles($filePath, $expenseDetails->projectID, $userID, $expenseDescription, $DBConn, $config, $fileMetadata);
								}
							}

							// Clear metadata from session after use
							if (isset($_SESSION['expense_file_metadata'])) {
								unset($_SESSION['expense_file_metadata']);
							}
						}
					}
				}
			}
		}
	} else {
		$projectID ? $details['projectID'] = $projectID : $errors[] = "Please submit valid project for the expense";
		$expenseTypeID ? $details['expenseTypeID'] = $expenseTypeID : $errors[] = "Please submit valid expense type for the expense";
		$expenseAmount ? $details['expenseAmount'] = $expenseAmount : $errors[] = "Please submit valid expense amount";
		$expenseDescription ? $details['expenseDescription'] = $expenseDescription : $errors[] = "Please submit valid expense Description /notes";
		$expenseDate ? $details['expenseDate'] = $expenseDate : $errors[] = "Please submit valid date the expense  /notes";
		$userID ? $details['userID'] = $userID : $errors[] = "Please submit valid user the expense  /notes";

		var_dump($details);

		if (count($errors) === 0) {
			if ($details) {
				if (!$DBConn->insert_data("tija_project_expenses", $details)) {
					$errors[]=" error saving the expense details to the database";
				} else {
					$expenseID= $DBConn->lastInsertID();
					$success= "Successfully saved expense to the database";

					// Sync expense files to tija_project_files if projectID exists
					if ($projectID && $expenseDocuments) {
						// Handle both array and JSON string formats
						$filePaths = array();
						if (is_array($expenseDocuments)) {
							$filePaths = $expenseDocuments;
						} elseif (is_string($expenseDocuments)) {
							// Try to decode as JSON first
							$decoded = json_decode($expenseDocuments, true);
							if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
								$filePaths = $decoded;
							} else {
								// Try comma-separated string
								$filePaths = array_filter(array_map('trim', explode(',', $expenseDocuments)));
							}
						}

						// Get file metadata from session if available
						$fileMetadata = isset($_SESSION['expense_file_metadata']) ? $_SESSION['expense_file_metadata'] : null;
						if ($fileMetadata) {
							$fileMetadata['filePaths'] = $filePaths; // Add file paths to metadata
						}

						foreach ($filePaths as $filePath) {
							if (!empty($filePath)) {
								syncExpenseFileToProjectFiles($filePath, $projectID, $userID, $expenseDescription, $DBConn, $config, $fileMetadata);
							}
						}

						// Clear metadata from session after use
						if (isset($_SESSION['expense_file_metadata'])) {
							unset($_SESSION['expense_file_metadata']);
						}
					}
				}
			}
		}
	}

	var_dump($errors);

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/?{$returnURL}");
?>