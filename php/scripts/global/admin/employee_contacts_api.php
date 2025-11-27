<?php
/**
 * Employee Contacts API
 * Handles AJAX requests for addresses and emergency contacts
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    if (!$isValidUser) {
        throw new Exception('You must be logged in to perform this action');
    }

    $action = isset($_GET['action']) ? Utility::clean_string($_GET['action']) : (isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '');

    if (!$action) {
        throw new Exception('No action specified');
    }

    $DBConn->begin();

    switch ($action) {
        // ========================================
        // ADDRESS OPERATIONS
        // ========================================

        case 'save_address':
            $addressID = isset($_POST['addressID']) && !empty($_POST['addressID']) ?
                Utility::clean_string($_POST['addressID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            $data = [
                'employeeID' => $employeeID,
                'addressType' => Utility::clean_string($_POST['addressType'] ?? 'home'),
                'isPrimary' => isset($_POST['isPrimary']) && $_POST['isPrimary'] == 'Y' ? 'Y' : 'N',
                'addressLine1' => Utility::clean_string($_POST['addressLine1'] ?? ''),
                'addressLine2' => Utility::clean_string($_POST['addressLine2'] ?? ''),
                'city' => Utility::clean_string($_POST['city'] ?? ''),
                'county' => Utility::clean_string($_POST['county'] ?? ''),
                'postalCode' => Utility::clean_string($_POST['postalCode'] ?? ''),
                'country' => Utility::clean_string($_POST['country'] ?? 'Kenya'),
                'landmark' => Utility::clean_string($_POST['landmark'] ?? ''),
                'validFrom' => isset($_POST['validFrom']) && !empty($_POST['validFrom']) ? Utility::clean_string($_POST['validFrom']) : null,
                'validTo' => isset($_POST['validTo']) && !empty($_POST['validTo']) ? Utility::clean_string($_POST['validTo']) : null,
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            // Validate required fields
            if (empty($data['addressLine1']) || empty($data['city'])) {
                throw new Exception('Address Line 1 and City are required');
            }

            // If setting as primary, unset other primary addresses of same type
            if ($data['isPrimary'] == 'Y') {
                $DBConn->update_table('tija_employee_addresses',
                    ['isPrimary' => 'N'],
                    ['employeeID' => $employeeID, 'addressType' => $data['addressType']]);
            }

            if ($addressID) {
                // Update existing address
                if (!$DBConn->update_table('tija_employee_addresses', $data, ['addressID' => $addressID])) {
                    throw new Exception('Failed to update address');
                }
                $response['message'] = 'Address updated successfully';
            } else {
                // Create new address
                $data['createdBy'] = $userDetails->ID;

                if (!$DBConn->insert_data('tija_employee_addresses', $data)) {
                    throw new Exception('Failed to create address');
                }
                $response['message'] = 'Address created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_address':
            $addressID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$addressID) {
                throw new Exception('Address ID is required');
            }

            $address = EmployeeProfileExtended::get_addresses_full(['addressID' => $addressID], true, $DBConn);
            if (!$address) {
                throw new Exception('Address not found');
            }

            $response['success'] = true;
            $response['data'] = $address;
            break;

        case 'delete_address':
            $addressID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$addressID) {
                throw new Exception('Address ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_employee_addresses', ['Suspended' => 'Y'], ['addressID' => $addressID])) {
                throw new Exception('Failed to delete address');
            }

            $response['success'] = true;
            $response['message'] = 'Address deleted successfully';
            break;

        // ========================================
        // EMERGENCY CONTACT OPERATIONS
        // ========================================

        case 'save_emergency_contact':
            $emergencyContactID = isset($_POST['emergencyContactID']) && !empty($_POST['emergencyContactID']) ?
                Utility::clean_string($_POST['emergencyContactID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            $data = [
                'employeeID' => $employeeID,
                'contactName' => Utility::clean_string($_POST['contactName'] ?? ''),
                'relationship' => Utility::clean_string($_POST['relationship'] ?? ''),
                'primaryPhoneNumber' => Utility::clean_string($_POST['primaryPhoneNumber'] ?? ''),
                'secondaryPhoneNumber' => Utility::clean_string($_POST['secondaryPhoneNumber'] ?? ''),
                'workPhoneNumber' => Utility::clean_string($_POST['workPhoneNumber'] ?? ''),
                'emailAddress' => Utility::clean_string($_POST['emailAddress'] ?? ''),
                'address' => Utility::clean_string($_POST['address'] ?? ''),
                'city' => Utility::clean_string($_POST['city'] ?? ''),
                'county' => Utility::clean_string($_POST['county'] ?? ''),
                'postalCode' => Utility::clean_string($_POST['postalCode'] ?? ''),
                'country' => Utility::clean_string($_POST['country'] ?? 'Kenya'),
                'isPrimary' => isset($_POST['isPrimaryEC']) && $_POST['isPrimaryEC'] == 'Y' ? 'Y' : 'N',
                'contactPriority' => Utility::clean_string($_POST['contactPriority'] ?? 'secondary'),
                'sortOrder' => intval($_POST['sortOrder'] ?? 0),
                'occupation' => Utility::clean_string($_POST['occupation'] ?? ''),
                'employer' => Utility::clean_string($_POST['employer'] ?? ''),
                'nationalID' => Utility::clean_string($_POST['nationalID'] ?? ''),
                'bloodType' => Utility::clean_string($_POST['bloodType'] ?? ''),
                'medicalConditions' => Utility::clean_string($_POST['medicalConditions'] ?? ''),
                'authorizedToCollectSalary' => isset($_POST['authorizedToCollectSalary']) && $_POST['authorizedToCollectSalary'] == 'Y' ? 'Y' : 'N',
                'authorizedForMedicalDecisions' => isset($_POST['authorizedForMedicalDecisions']) && $_POST['authorizedForMedicalDecisions'] == 'Y' ? 'Y' : 'N',
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            // Validate required fields
            if (empty($data['contactName']) || empty($data['relationship']) || empty($data['primaryPhoneNumber'])) {
                throw new Exception('Contact name, relationship, and primary phone number are required');
            }

            // If setting as primary, unset other primary emergency contacts
            if ($data['isPrimary'] == 'Y') {
                $DBConn->update_table('tija_employee_emergency_contacts',
                    ['isPrimary' => 'N'],
                    ['employeeID' => $employeeID]);
            }

            if ($emergencyContactID) {
                // Update existing contact
                if (!$DBConn->update_table('tija_employee_emergency_contacts', $data, ['emergencyContactID' => $emergencyContactID])) {
                    throw new Exception('Failed to update emergency contact');
                }
                $response['message'] = 'Emergency contact updated successfully';
            } else {
                // Create new contact
                $data['createdBy'] = $userDetails->ID;

                if (!$DBConn->insert_data('tija_employee_emergency_contacts', $data)) {
                    throw new Exception('Failed to create emergency contact');
                }
                $response['message'] = 'Emergency contact created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_emergency_contact':
            $contactID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$contactID) {
                throw new Exception('Emergency contact ID is required');
            }

            $contact = EmployeeProfileExtended::get_emergency_contacts_full(['emergencyContactID' => $contactID], true, $DBConn);
            if (!$contact) {
                throw new Exception('Emergency contact not found');
            }

            $response['success'] = true;
            $response['data'] = $contact;
            break;

        case 'delete_emergency_contact':
            $contactID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$contactID) {
                throw new Exception('Emergency contact ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_employee_emergency_contacts', ['Suspended' => 'Y'], ['emergencyContactID' => $contactID])) {
                throw new Exception('Failed to delete emergency contact');
            }

            $response['success'] = true;
            $response['message'] = 'Emergency contact deleted successfully';
            break;

        default:
            throw new Exception('Invalid action specified');
    }

    if ($response['success']) {
        $DBConn->commit();
    } else {
        $DBConn->rollback();
    }

} catch (Exception $e) {
    $DBConn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

