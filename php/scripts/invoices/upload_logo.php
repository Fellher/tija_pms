<?php
/**
 * Logo Upload Script for Invoice Templates
 *
 * @package    TIJA_PMS
 * @subpackage Invoicing
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

// Check authentication
if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    exit;
}

// Admin check
if (!$isAdmin && !$isValidAdmin) {
    echo json_encode(array('success' => false, 'message' => 'Administrator privileges required'));
    exit;
}

$templateID = isset($_POST['templateID']) ? intval($_POST['templateID']) : 0;

if (!$templateID) {
    echo json_encode(array('success' => false, 'message' => 'Template ID is required'));
    exit;
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(array('success' => false, 'message' => 'No file uploaded or upload error'));
    exit;
}

try {
    // Allowed image extensions
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed'));
        exit;
    }

    // Check file size (max 2MB)
    if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
        echo json_encode(array('success' => false, 'message' => 'File size exceeds 2MB limit'));
        exit;
    }

    // Create upload directory if it doesn't exist
    $uploadSubDir = 'invoice_templates/logos';
    $uploadDir = $config['DataDir'] . $uploadSubDir . '/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $fileName = 'template_' . $templateID . '_' . time() . '_' . Utility::generate_unique_string(10) . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;

    // Move uploaded file
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
        // Get relative path for database storage
        $relativePath = $uploadSubDir . '/' . $fileName;
        $logoURL = $config['DataDirURL'] . $relativePath;

        // Update template in database
        $updateData = array(
            'logoURL' => $logoURL,
            'LastUpdatedByID' => $userDetails->ID,
            'LastUpdate' => date('Y-m-d H:i:s')
        );

        // Delete old logo if exists
        $template = Invoice::invoice_templates(array('templateID' => $templateID), true, $DBConn);
        if ($template && $template->logoURL) {
            $oldLogoPath = str_replace($config['DataDirURL'], $config['DataDir'], $template->logoURL);
            if (file_exists($oldLogoPath)) {
                @unlink($oldLogoPath);
            }
        }

        if ($DBConn->update_table('tija_invoice_templates', $updateData, array('templateID' => $templateID))) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Logo uploaded successfully',
                'logoURL' => $logoURL
            ));
        } else {
            // Delete uploaded file if database update fails
            @unlink($filePath);
            echo json_encode(array('success' => false, 'message' => 'Failed to update template'));
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to upload file'));
    }
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
}

?>

