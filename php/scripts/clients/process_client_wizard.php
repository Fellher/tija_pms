<?php
session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Handle multipart/form-data (for file uploads)
$errors = [];

$clientName       = trim($_POST['clientName'] ?? '');
$clientCode       = trim($_POST['clientCode'] ?? '');
$accountOwnerID   = $_POST['accountOwnerID'] ?? null;
$vatNumber        = trim($_POST['vatNumber'] ?? '');
$orgDataID        = $_POST['orgDataID'] ?? null;
$entityID         = $_POST['entityID'] ?? null;
$inHouse          = (!empty($_POST['inHouse']) && $_POST['inHouse'] === 'Y') ? 'Y' : 'N';
$clientDescription= trim($_POST['clientDescription'] ?? '');

// Validate required fields
if (!$clientName)     { $errors[] = 'Client Name is required'; }
if (!$accountOwnerID) { $errors[] = 'Account Owner is required'; }
if (!$orgDataID)      { $errors[] = 'Organization is required'; }
if (!$entityID)       { $errors[] = 'Entity is required'; }

// Auto-generate client code if not provided
if (!$clientCode && $clientName) {
    $clientCode = Utility::clientCode($clientName);
}

// Parse addresses from JSON
$addresses = [];
if (!empty($_POST['addresses'])) {
    $addresses = json_decode($_POST['addresses'], true);
    if (!is_array($addresses) || count($addresses) === 0) {
        $errors[] = 'At least one address is required';
    }
}

// Parse contacts from JSON
$contacts = [];
if (!empty($_POST['contacts'])) {
    $contacts = json_decode($_POST['contacts'], true);
    if (!is_array($contacts)) {
        $contacts = [];
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

$DBConn->begin();

try {
    // 1. Create Client
    $clientDetails = [
        'clientName'        => $clientName,
        'clientCode'        => Utility::clean_string($clientCode),
        'accountOwnerID'    => $accountOwnerID,
        'orgDataID'         => $orgDataID,
        'entityID'          => $entityID,
        'vatNumber'         => $vatNumber,
        'clientDescription' => $clientDescription,
        'inHouse'           => $inHouse,
        'LastUpdateByID'    => $userDetails->ID,
        'LastUpdate'        => $config['currentDateTimeFormated']
    ];

    if (!$DBConn->insert_data('tija_clients', $clientDetails)) {
        throw new Exception('Failed to create client');
    }

    $clientID = $DBConn->lastInsertID();

    // 2. Create Addresses
    foreach ($addresses as $addr) {
        $addressDetails = [
            'clientID'        => $clientID,
            'address'         => trim($addr['address'] ?? ''),
            'city'            => trim($addr['city'] ?? ''),
            'countryID'       => $addr['country'] ?? null,
            'postalCode'      => trim($addr['postalCode'] ?? ''),
            'addressType'     => $addr['addressType'] ?? 'OfficeAddress',
            'headquarters'    => ($addr['headquarters'] ?? 'N') === 'Y' ? 'Y' : 'N',
            'billingAddress'  => ($addr['billingAddress'] ?? 'N') === 'Y' ? 'Y' : 'N',
            'orgDataID'       => $orgDataID,
            'entityID'        => $entityID,
            'LastUpdateByID'  => $userDetails->ID,
            'LastUpdate'      => $config['currentDateTimeFormated']
        ];

        if (!$DBConn->insert_data('tija_client_addresses', $addressDetails)) {
            throw new Exception('Failed to create address');
        }
    }

    // 3. Create Relationship Assignment
    if ($accountOwnerID) {
        $rel = [
            'clientID'               => $clientID,
            'employeeID'             => $accountOwnerID,
            'clientRelationshipType' => 'engagementPartner',
            'LastUpdateByID'         => $userDetails->ID,
            'LastUpdate'             => $config['currentDateTimeFormated']
        ];
        $DBConn->insert_data('client_relationship_assignments', $rel);
    }

    // 4. Create Contacts
    // First, we need to get the created address IDs to map them
    $createdAddressIDs = [];
    $addressQuery = "SELECT clientAddressID FROM tija_client_addresses WHERE clientID = ? ORDER BY clientAddressID ASC";
    $addressResults = $DBConn->fetch_all_rows($addressQuery, [[$clientID, 's']]);
    if ($addressResults) {
        foreach ($addressResults as $index => $addr) {
            $createdAddressIDs[$index] = $addr->clientAddressID;
        }
    }

    foreach ($contacts as $contact) {
        $name  = trim($contact['contactName'] ?? '');
        $email = trim($contact['contactEmail'] ?? '');
        $phone = trim($contact['contactPhone'] ?? '');
        $title = trim($contact['title'] ?? '');
        $salutationID = $contact['salutationID'] ?? null;
        $contactTypeID = $contact['contactTypeID'] ?? null;
        $linkedAddressIndex = $contact['clientAddressID'] ?? null;

        if (!$name) {
            continue; // Skip if no name
        }

        // Map the address index to actual clientAddressID
        $clientAddressID = null;
        if ($linkedAddressIndex !== null && $linkedAddressIndex !== '' && isset($createdAddressIDs[$linkedAddressIndex])) {
            $clientAddressID = $createdAddressIDs[$linkedAddressIndex];
        }

        $contactRow = [
            'clientID'        => $clientID,
            'userID'          => $userDetails->ID,  // Current user creating the contact
            'salutationID'    => $salutationID ?: null,
            'contactName'     => $name,
            'title'           => $title,
            'contactTypeID'   => $contactTypeID ?: null,
            'contactEmail'    => $email,
            'contactPhone'    => $phone,
            'clientAddressID' => $clientAddressID,
            'LastUpdateByID'  => $userDetails->ID,
            'LastUpdate'      => $config['currentDateTimeFormated']
        ];
        $DBConn->insert_data('tija_client_contacts', $contactRow);
    }

    // 5. Handle Document Uploads
    $documentCount = intval($_POST['documentCount'] ?? 0);

    if ($documentCount > 0) {
        for ($i = 0; $i < $documentCount; $i++) {
            $fileKey = "clientDocumentFile_$i";
            $typeKey = "documentTypeID_$i";
            $nameKey = "clientDocumentName_$i";
            $descKey = "clientDocumentDescription_$i";

            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$fileKey];
                $documentTypeID = $_POST[$typeKey] ?? null;
                $clientDocumentName = $_POST[$nameKey] ?? '';
                $clientDocumentDescription = $_POST[$descKey] ?? '';

                if (!$documentTypeID) {
                    continue; // Skip if no document type
                }

                // Use File utility to upload
                $fileUpload = File::upload_file(
                    $file,
                    'client_documents',
                    '',
                    1024 * 1024 * 10, // 10MB max
                    $config,
                    $DBConn
                );

                if ($fileUpload['status'] === 'error') {
                    throw new Exception("File upload failed: " . $fileUpload['message']);
                }

                // Save document metadata to database
                $docRow = [
                    'clientID'                  => $clientID,
                    'documentTypeID'            => $documentTypeID,
                    'clientDocumentName'        => $clientDocumentName,
                    'clientDocumentDescription' => $clientDocumentDescription,
                    'clientDocumentFile'        => $fileUpload['uploadedFilePaths'],
                    'documentFileName'          => $fileUpload['fileName'],
                    'documentFileType'          => $fileUpload['fileType'],
                    'documentFileSize'          => $fileUpload['fileSize'],
                    'documentFilePath'          => $fileUpload['fileDestination'],
                    'LastUpdateByID'            => $userDetails->ID,
                    'LastUpdate'                => $config['currentDateTimeFormated']
                ];

                if (!$DBConn->insert_data('tija_client_documents', $docRow)) {
                    throw new Exception('Failed to save document metadata');
                }
            }
        }
    }

    $DBConn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Client created successfully',
        'clientID'=> $clientID
    ]);
} catch (Exception $e) {
    $DBConn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
