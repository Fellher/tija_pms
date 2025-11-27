<?php
/**
 * Get Client Contacts API
 * Returns contacts for a specific client
 *
 * @author System
 * @version 1.0
 * @date 2025-10-09
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '', 'contacts' => null);

try {
    // Validate user authentication
    if (!$isValidUser) {
        throw new Exception('Unauthorized access');
    }

    // Get client ID from request
    $clientID = isset($_GET['clientID']) ? Utility::clean_string($_GET['clientID']) : null;

    if (empty($clientID)) {
        throw new Exception('Client ID is required');
    }

    // Get client contacts
    $contacts = Client::client_contacts(array(
        'clientID' => $clientID,
        'Suspended' => 'N'
    ), false, $DBConn);

    if ($contacts) {
        // Format contacts for dropdown
        $formattedContacts = array();
        foreach ($contacts as $contact) {
            $formattedContacts[] = array(
                'clientContactID' => $contact->clientContactID,
                'contactName' => $contact->contactName,
                'contactEmail' => $contact->contactEmail ?? '',
                'contactPhone' => $contact->contactPhone ?? '',
                'title' => $contact->title ?? '',
                'contactTypeID' => $contact->contactTypeID ?? ''
            );
        }

        $response['success'] = true;
        $response['message'] = 'Contacts retrieved successfully';
        $response['contacts'] = $formattedContacts;
    } else {
        // No contacts found, but this is not an error
        $response['success'] = true;
        $response['message'] = 'No contacts found for this client';
        $response['contacts'] = array();
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Get Client Contacts Error: ' . $e->getMessage());
}

echo json_encode($response);

