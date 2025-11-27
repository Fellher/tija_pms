<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'data' => null
);

try {
    // Check if user is logged in
    if (!$isValidUser) {
        throw new Exception('User not authenticated');
    }

    $userID = $userDetails->ID;
    $action = $_POST['action'] ?? 'upload';
    switch ($action) {
        case 'upload':
            handleFileUpload($userDetails->ID, $DBConn);
            break;
        case 'update':
            handleFileUpdate($userDetails->ID, $DBConn);
            break;
        case 'delete':
            handleFileDelete($userDetails->ID, $DBConn);
            break;
        case 'get':
            handleGetFile($userDetails->ID, $DBConn);
            break;
        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}

/**
 * Handle file upload
 */
function handleFileUpload($userID, $DBConn) {
    global $response;

    try {
        // Validate required fields
        if (!isset($_POST['projectID']) || empty($_POST['projectID'])) {
            throw new Exception('Unable to upload file: Project information is missing. Please refresh the page and try again.');
        }

        if (!isset($_FILES['projectFile']) || $_FILES['projectFile']['error'] !== UPLOAD_ERR_OK) {
            $uploadError = $_FILES['projectFile']['error'] ?? UPLOAD_ERR_NO_FILE;
            $errorMessages = array(
                UPLOAD_ERR_INI_SIZE => 'The file is too large. Maximum file size is 50MB.',
                UPLOAD_ERR_FORM_SIZE => 'The file exceeds the maximum allowed size of 50MB.',
                UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was selected. Please choose a file to upload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Temporary folder is missing. Please contact support.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Unable to save file. Please contact support.',
                UPLOAD_ERR_EXTENSION => 'File upload was blocked by server security settings. Please contact support.'
            );
            $errorMsg = $errorMessages[$uploadError] ?? 'File upload failed. Please select a valid file and try again.';
            throw new Exception($errorMsg);
        }

        $projectID = intval($_POST['projectID']);
        $file = $_FILES['projectFile'];

        // Validate file size (50MB max)
        $maxSize = 50 * 1024 * 1024; // 50MB in bytes
        if ($file['size'] > $maxSize) {
            $fileSizeMB = round($file['size'] / 1024 / 1024, 2);
            throw new Exception("File is too large ({$fileSizeMB} MB). Maximum allowed size is 50 MB. Please compress or use a smaller file.");
        }

        // Validate file type
        $allowedTypes = array(
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'rar'
        );
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $allowedTypesList = implode(', ', array_map('strtoupper', $allowedTypes));
            throw new Exception("File type '{$fileExtension}' is not supported. Allowed file types: {$allowedTypesList}");
        }

        // Create upload directory if it doesn't exist
        $uploadDir = '../../../uploads/project_files/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        $fileURL = 'uploads/project_files/' . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Unable to save file to server. Please check file permissions or contact your system administrator.');
        }

        // Get file information
        $fileOriginalName = $_POST['fileOriginalName'] ?? $file['name'];
        $category = $_POST['category'] ?? null;
        $taskID = !empty($_POST['taskID']) ? intval($_POST['taskID']) : null;
        $description = $_POST['description'] ?? null;
        $isPublic = isset($_POST['isPublic']) && $_POST['isPublic'] === 'Y' ? 'Y' : 'N';
        $fileSize = $file['size'];
        $fileMimeType = $file['type'];

        // Prepare data for insertion using mysqlConnect class method
        $fileData = array(
            'projectID' => $projectID,
            'taskID' => $taskID,
            'fileName' => $fileName,
            'fileOriginalName' => $fileOriginalName,
            'fileURL' => $fileURL,
            'fileType' => $fileExtension,
            'fileSize' => $fileSize,
            'fileMimeType' => $fileMimeType,
            'category' => $category,
            'version' => '1.0',
            'uploadedBy' => $userID,
            'description' => $description,
            'isPublic' => $isPublic,
            'downloadCount' => 0,
            'DateAdded' => 'NOW()',
            'Suspended' => 'N'
        );

        // Insert into database using insert_data method
        $result = $DBConn->insert_data('tija_project_files', $fileData);

        if ($result) {
            $fileID = $DBConn->lastInsertId();
            $response['success'] = true;
            $response['message'] = 'File uploaded successfully';
            $response['data'] = array(
                'fileID' => $fileID,
                'fileName' => $fileName,
                'fileURL' => $fileURL
            );
        } else {
            // Delete uploaded file if database insert fails
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw new Exception('File was uploaded but could not be saved to the database. Please try again or contact support if the problem persists.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        // Log error for developers
        error_log("File Upload Error [User: {$userID}, Project: " . ($_POST['projectID'] ?? 'N/A') . "]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle file update
 */
function handleFileUpdate($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['fileID']) || empty($_POST['fileID'])) {
            throw new Exception('Unable to update file: File information is missing. Please refresh the page and try again.');
        }

        $fileID = intval($_POST['fileID']);

        // Check if user has permission to update this file using retrieve_db_table_rows
        $file = $DBConn->retrieve_db_table_rows(
            'tija_project_files',
            array('uploadedBy'),
            array('fileID' => $fileID, 'Suspended' => 'N')
        );

        if (!$file || empty($file)) {
            throw new Exception('File not found. It may have been deleted or you may not have permission to access it.');
        }

        // Build update data array
        $updateData = array();

        if (isset($_POST['fileOriginalName'])) {
            $updateData['fileOriginalName'] = $_POST['fileOriginalName'];
        }

        if (isset($_POST['category'])) {
            $updateData['category'] = $_POST['category'];
        }

        if (isset($_POST['description'])) {
            $updateData['description'] = $_POST['description'];
        }

        if (isset($_POST['isPublic'])) {
            $updateData['isPublic'] = $_POST['isPublic'];
        }

        if (isset($_POST['taskID'])) {
            $updateData['taskID'] = intval($_POST['taskID']);
        }

        if (empty($updateData)) {
            throw new Exception('No changes were made. Please update at least one field before saving.');
        }

        // Add LastUpdate timestamp
        $updateData['LastUpdate'] = 'NOW()';

        // Update using update_table method
        $result = $DBConn->update_table(
            'tija_project_files',
            $updateData,
            array('fileID' => $fileID)
        );

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'File information updated successfully';
        } else {
            throw new Exception('Unable to save changes. Please try again or contact support if the problem persists.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        // Log error for developers
        error_log("File Update Error [User: {$userID}, FileID: " . ($_POST['fileID'] ?? 'N/A') . "]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle file deletion
 */
function handleFileDelete($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['fileID']) || empty($_POST['fileID'])) {
            throw new Exception('Unable to delete file: File information is missing. Please refresh the page and try again.');
        }

        $fileID = intval($_POST['fileID']);

        // Get file information using retrieve_db_table_rows
        $files = $DBConn->retrieve_db_table_rows(
            'tija_project_files',
            array('fileURL', 'fileOriginalName'),
            array('fileID' => $fileID, 'Suspended' => 'N')
        );

        if (!$files || empty($files)) {
            throw new Exception('File not found. It may have already been deleted.');
        }

        $file = $files[0]; // Get first result

        // Soft delete (set Suspended = 'Y') using update_table method
        $updateData = array(
            'Suspended' => 'Y',
            'LastUpdate' => 'NOW()'
        );

        $result = $DBConn->update_table(
            'tija_project_files',
            $updateData,
            array('fileID' => $fileID)
        );

        if ($result) {
            // Optionally delete physical file
            $filePath = '../../../' . $file->fileURL;
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    error_log("Warning: Could not delete physical file: {$filePath}");
                }
            }

            $fileName = $file->fileOriginalName ?? 'File';
            $response['success'] = true;
            $response['message'] = "File '{$fileName}' has been deleted successfully";
        } else {
            throw new Exception('Unable to delete file. Please try again or contact support if the problem persists.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        // Log error for developers
        error_log("File Delete Error [User: {$userID}, FileID: " . ($_POST['fileID'] ?? 'N/A') . "]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle get file details
 */
function handleGetFile($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['fileID']) || empty($_POST['fileID'])) {
            throw new Exception('Unable to load file: File information is missing. Please refresh the page and try again.');
        }

        $fileID = intval($_POST['fileID']);

        // Get file details using Projects class method
        $file = Projects::project_files(array('fileID' => $fileID, 'Suspended' => 'N'), true, $DBConn);

        if (!$file) {
            throw new Exception('File not found. It may have been deleted or you may not have permission to access it.');
        }

        $response['success'] = true;
        $response['message'] = 'File details loaded successfully';
        $response['data'] = $file;

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        // Log error for developers
        error_log("File Get Error [User: {$userID}, FileID: " . ($_POST['fileID'] ?? 'N/A') . "]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}
