<?php
/**
 * Invoice Template Management Script
 * Handles CRUD operations for invoice templates
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

$action = isset($_REQUEST['action']) ? Utility::clean_string($_REQUEST['action']) : '';
$response = array('success' => false, 'message' => 'Invalid action');

try {
    // Get employee details for organization context
    $employeeDetails = Employee::employees(array('ID' => $userDetails->ID), true, $DBConn);
    $orgDataID = isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
        ? $employeeDetails->orgDataID
        : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
            ? $_SESSION['orgDataID']
            : 1);
    $entityID = isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
        ? $employeeDetails->entityID
        : (isset($_SESSION['entityID'])
            ? $_SESSION['entityID']
            : 1);

    switch ($action) {
        case 'create':
            $response = createTemplate($DBConn, $userDetails, $orgDataID, $entityID);
            break;

        case 'update':
            $response = updateTemplate($DBConn, $userDetails);
            break;

        case 'delete':
            $response = deleteTemplate($DBConn, $userDetails);
            break;

        case 'get':
            $response = getTemplate($DBConn);
            break;

        default:
            $response = array('success' => false, 'message' => 'Invalid action');
    }
} catch (Exception $e) {
    error_log('Invoice Template Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
} catch (Error $e) {
    error_log('Invoice Template Fatal Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    $response = array('success' => false, 'message' => 'Fatal error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

/**
 * Create a new template
 */
function createTemplate($DBConn, $userDetails, $orgDataID, $entityID) {
    $errors = array();

    $templateName = isset($_POST['templateName']) ? Utility::clean_string($_POST['templateName']) : '';
    $templateCode = isset($_POST['templateCode']) ? Utility::clean_string($_POST['templateCode']) : '';

    if (!$templateName || !$templateCode) {
        return array('success' => false, 'message' => 'Template name and code are required');
    }

    // Check if code already exists
    $existing = Invoice::invoice_templates(array('templateCode' => $templateCode), true, $DBConn);
    if ($existing) {
        return array('success' => false, 'message' => 'Template code already exists');
    }

    // If setting as default, unset other defaults
    $isDefault = isset($_POST['isDefault']) && $_POST['isDefault'] == 'Y';
    if ($isDefault) {
        $defaultTemplates = Invoice::invoice_templates(array('isDefault' => 'Y', 'orgDataID' => $orgDataID, 'entityID' => $entityID), false, $DBConn);
        if ($defaultTemplates && is_array($defaultTemplates)) {
            foreach ($defaultTemplates as $dt) {
                $DBConn->update_table('tija_invoice_templates', array('isDefault' => 'N'), array('templateID' => $dt->templateID));
            }
        }
    }

    $templateData = array(
        'templateName' => $templateName,
        'templateCode' => strtoupper($templateCode),
        'templateDescription' => isset($_POST['templateDescription']) ? Utility::clean_string($_POST['templateDescription']) : null,
        'templateType' => isset($_POST['templateType']) ? Utility::clean_string($_POST['templateType']) : 'standard',
        'currency' => isset($_POST['currency']) ? Utility::clean_string($_POST['currency']) : 'KES',
        'taxEnabled' => isset($_POST['taxEnabled']) && $_POST['taxEnabled'] == 'Y' ? 'Y' : 'N',
        'defaultTaxPercent' => isset($_POST['defaultTaxPercent']) ? floatval($_POST['defaultTaxPercent']) : 0,
        'companyName' => isset($_POST['companyName']) ? Utility::clean_string($_POST['companyName']) : null,
        'companyAddress' => isset($_POST['companyAddress']) ? Utility::clean_string($_POST['companyAddress']) : null,
        'companyPhone' => isset($_POST['companyPhone']) ? Utility::clean_string($_POST['companyPhone']) : null,
        'companyEmail' => isset($_POST['companyEmail']) ? Utility::clean_string($_POST['companyEmail']) : null,
        'companyWebsite' => isset($_POST['companyWebsite']) ? Utility::clean_string($_POST['companyWebsite']) : null,
        'companyTaxID' => isset($_POST['companyTaxID']) ? Utility::clean_string($_POST['companyTaxID']) : null,
        'defaultTerms' => isset($_POST['defaultTerms']) ? Utility::clean_string($_POST['defaultTerms']) : null,
        'defaultNotes' => isset($_POST['defaultNotes']) ? Utility::clean_string($_POST['defaultNotes']) : null,
        'isDefault' => $isDefault ? 'Y' : 'N',
        'isActive' => isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N',
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'createdBy' => $userDetails->ID,
        'LastUpdatedByID' => $userDetails->ID,
        'Suspended' => 'N'
    );

    try {
        $insertResult = $DBConn->insert_data('tija_invoice_templates', $templateData);

        if ($insertResult) {
            // Get the inserted template ID
            $templateID = $DBConn->lastInsertId();
            if ($templateID) {
                return array('success' => true, 'message' => 'Template created successfully', 'templateID' => $templateID);
            } else {
                error_log('Template insert succeeded but lastInsertId returned: ' . var_export($templateID, true));
                return array('success' => false, 'message' => 'Template created but failed to retrieve ID. Please refresh the page.');
            }
        } else {
            error_log('Template insert failed. Data: ' . json_encode($templateData));
            return array('success' => false, 'message' => 'Failed to create template. Please check all required fields and try again.');
        }
    } catch (Exception $e) {
        error_log('Template creation exception: ' . $e->getMessage());
        return array('success' => false, 'message' => 'Error creating template: ' . $e->getMessage());
    }
}

/**
 * Update template
 */
function updateTemplate($DBConn, $userDetails) {
    $templateID = isset($_POST['templateID']) ? intval($_POST['templateID']) : 0;

    if (!$templateID) {
        return array('success' => false, 'message' => 'Template ID is required');
    }

    $template = Invoice::invoice_templates(array('templateID' => $templateID), true, $DBConn);
    if (!$template) {
        return array('success' => false, 'message' => 'Template not found');
    }

    // Get employee details for organization context
    $employeeDetails = Employee::employees(array('ID' => $userDetails->ID), true, $DBConn);
    $orgDataID = isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
        ? $employeeDetails->orgDataID
        : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
            ? $_SESSION['orgDataID']
            : 1);
    $entityID = isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
        ? $employeeDetails->entityID
        : (isset($_SESSION['entityID'])
            ? $_SESSION['entityID']
            : 1);

    // If setting as default, unset other defaults
    $isDefault = isset($_POST['isDefault']) && $_POST['isDefault'] == 'Y';
    if ($isDefault && $template->isDefault != 'Y') {
        $defaultTemplates = Invoice::invoice_templates(array('isDefault' => 'Y', 'orgDataID' => $orgDataID, 'entityID' => $entityID), false, $DBConn);
        if ($defaultTemplates && is_array($defaultTemplates)) {
            foreach ($defaultTemplates as $dt) {
                if ($dt->templateID != $templateID) {
                    $DBConn->update_table('tija_invoice_templates', array('isDefault' => 'N'), array('templateID' => $dt->templateID));
                }
            }
        }
    }

    $updateData = array();

    if (isset($_POST['templateName'])) {
        $updateData['templateName'] = Utility::clean_string($_POST['templateName']);
    }
    if (isset($_POST['templateCode'])) {
        $newCode = strtoupper(Utility::clean_string($_POST['templateCode']));
        // Check if code already exists (excluding current template)
        $existing = Invoice::invoice_templates(array('templateCode' => $newCode), true, $DBConn);
        if ($existing && $existing->templateID != $templateID) {
            return array('success' => false, 'message' => 'Template code already exists');
        }
        $updateData['templateCode'] = $newCode;
    }
    if (isset($_POST['templateDescription'])) {
        $updateData['templateDescription'] = Utility::clean_string($_POST['templateDescription']);
    }
    if (isset($_POST['templateType'])) {
        $updateData['templateType'] = Utility::clean_string($_POST['templateType']);
    }
    if (isset($_POST['currency'])) {
        $updateData['currency'] = Utility::clean_string($_POST['currency']);
    }
    if (isset($_POST['taxEnabled'])) {
        $updateData['taxEnabled'] = $_POST['taxEnabled'] == 'Y' ? 'Y' : 'N';
    }
    if (isset($_POST['defaultTaxPercent'])) {
        $updateData['defaultTaxPercent'] = floatval($_POST['defaultTaxPercent']);
    }
    if (isset($_POST['companyName'])) {
        $updateData['companyName'] = Utility::clean_string($_POST['companyName']);
    }
    if (isset($_POST['companyAddress'])) {
        $updateData['companyAddress'] = Utility::clean_string($_POST['companyAddress']);
    }
    if (isset($_POST['companyPhone'])) {
        $updateData['companyPhone'] = Utility::clean_string($_POST['companyPhone']);
    }
    if (isset($_POST['companyEmail'])) {
        $updateData['companyEmail'] = Utility::clean_string($_POST['companyEmail']);
    }
    if (isset($_POST['companyWebsite'])) {
        $updateData['companyWebsite'] = Utility::clean_string($_POST['companyWebsite']);
    }
    if (isset($_POST['companyTaxID'])) {
        $updateData['companyTaxID'] = Utility::clean_string($_POST['companyTaxID']);
    }
    if (isset($_POST['defaultTerms'])) {
        $updateData['defaultTerms'] = Utility::clean_string($_POST['defaultTerms']);
    }
    if (isset($_POST['defaultNotes'])) {
        $updateData['defaultNotes'] = Utility::clean_string($_POST['defaultNotes']);
    }
    if (isset($_POST['isDefault'])) {
        $updateData['isDefault'] = $_POST['isDefault'] == 'Y' ? 'Y' : 'N';
    }
    if (isset($_POST['isActive'])) {
        $updateData['isActive'] = $_POST['isActive'] == 'Y' ? 'Y' : 'N';
    }

    $updateData['LastUpdatedByID'] = $userDetails->ID;
    $updateData['LastUpdate'] = date('Y-m-d H:i:s');

    if (count($updateData) > 0) {
        try {
            $updateResult = $DBConn->update_table('tija_invoice_templates', $updateData, array('templateID' => $templateID));
            if ($updateResult) {
                return array('success' => true, 'message' => 'Template updated successfully');
            } else {
                error_log('Template update failed. TemplateID: ' . $templateID . ' | Data: ' . json_encode($updateData));
                return array('success' => false, 'message' => 'Failed to update template. Please check the data and try again.');
            }
        } catch (Exception $e) {
            error_log('Template update exception: ' . $e->getMessage());
            return array('success' => false, 'message' => 'Error updating template: ' . $e->getMessage());
        }
    }

    return array('success' => false, 'message' => 'No changes to update');
}

/**
 * Delete template
 */
function deleteTemplate($DBConn, $userDetails) {
    $templateID = isset($_REQUEST['templateID']) ? intval($_REQUEST['templateID']) : 0;

    if (!$templateID) {
        return array('success' => false, 'message' => 'Template ID is required');
    }

    $template = Invoice::invoice_templates(array('templateID' => $templateID), true, $DBConn);
    if (!$template) {
        return array('success' => false, 'message' => 'Template not found');
    }

    // Cannot delete default template
    if ($template->isDefault == 'Y') {
        return array('success' => false, 'message' => 'Cannot delete default template');
    }

    // Check if template is in use
    $invoicesUsingTemplate = Invoice::invoices(array('templateID' => $templateID), false, $DBConn);
    if ($invoicesUsingTemplate && is_array($invoicesUsingTemplate) && count($invoicesUsingTemplate) > 0) {
        return array('success' => false, 'message' => 'Cannot delete template that is in use by invoices');
    }

    if ($DBConn->update_table('tija_invoice_templates', array('Suspended' => 'Y'), array('templateID' => $templateID))) {
        return array('success' => true, 'message' => 'Template deleted successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to delete template');
    }
}

/**
 * Get template
 */
function getTemplate($DBConn) {
    $templateID = isset($_REQUEST['templateID']) ? intval($_REQUEST['templateID']) : 0;

    if (!$templateID) {
        return array('success' => false, 'message' => 'Template ID is required');
    }

    $template = Invoice::invoice_templates(array('templateID' => $templateID), true, $DBConn);
    if (!$template) {
        return array('success' => false, 'message' => 'Template not found');
    }

    return array('success' => true, 'template' => $template);
}

?>

