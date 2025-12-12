<?php
/**
 * Export Prospects to CSV/Excel
 * Supports filtered export and selected prospects export
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();

// Check authentication
if (!isset($userDetails->ID) || empty($userDetails->ID)) {
    die('Unauthorized access.');
}

$userID = $userDetails->ID;


if (!$userDetails) {
    die('Invalid user session.');
}

// Determine export type
$exportType = isset($_GET['type']) ? $_GET['type'] : 'csv'; // csv or excel
$prospectIDs = isset($_POST['prospectIDs']) ? json_decode($_POST['prospectIDs'], true) : null;

// Build filters
$filters = array();

if ($prospectIDs && is_array($prospectIDs)) {
    // Export selected prospects
    $placeholders = implode(',', array_fill(0, count($prospectIDs), '?'));
    $whereClause = "WHERE p.salesProspectID IN ({$placeholders}) AND p.Suspended = 'N'";
    $params = array();
    foreach ($prospectIDs as $id) {
        $params[] = array((int)$id, 'i');
    }
} else {
    // Export with filters from GET parameters
    $filters['orgDataID'] = isset($_GET['orgDataID']) ? (int)$_GET['orgDataID'] : $userDetails->orgDataID;
    $filters['entityID'] = isset($_GET['entityID']) ? (int)$_GET['entityID'] : $userDetails->entityID;

    if (isset($_GET['businessUnitID']) && !empty($_GET['businessUnitID'])) {
        $filters['businessUnitID'] = (int)$_GET['businessUnitID'];
    }
    if (isset($_GET['leadSourceID']) && !empty($_GET['leadSourceID'])) {
        $filters['leadSourceID'] = (int)$_GET['leadSourceID'];
    }
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['salesProspectStatus'] = Utility::clean_string($_GET['status']);
    }
    if (isset($_GET['qualification']) && !empty($_GET['qualification'])) {
        $filters['leadQualificationStatus'] = Utility::clean_string($_GET['qualification']);
    }
    if (isset($_GET['teamID']) && !empty($_GET['teamID'])) {
        $filters['assignedTeamID'] = (int)$_GET['teamID'];
    }
    if (isset($_GET['territoryID']) && !empty($_GET['territoryID'])) {
        $filters['territoryID'] = (int)$_GET['territoryID'];
    }
    if (isset($_GET['industryID']) && !empty($_GET['industryID'])) {
        $filters['industryID'] = (int)$_GET['industryID'];
    }
    if (isset($_GET['ownerID']) && !empty($_GET['ownerID'])) {
        $filters['ownerID'] = (int)$_GET['ownerID'];
    }

    // Build WHERE clause
    $conditions = array();
    $params = array();

    foreach ($filters as $field => $value) {
        $conditions[] = "p.{$field} = ?";
        $params[] = array($value, is_numeric($value) ? 'i' : 's');
    }

    $conditions[] = "p.Suspended = 'N'";
    $conditions[] = "p.Lapsed = 'N'";

    $whereClause = "WHERE " . implode(" AND ", $conditions);
}

// Get prospect data
$sql = "SELECT
    p.salesProspectID,
    p.DateAdded,
    p.salesProspectName,
    p.prospectCaseName,
    p.prospectEmail,
    p.prospectPhone,
    p.prospectWebsite,
    p.address,
    p.estimatedValue,
    p.probability,
    p.salesProspectStatus,
    p.leadScore,
    p.leadQualificationStatus,
    p.companySize,
    p.expectedCloseDate,
    p.lastContactDate,
    p.nextFollowUpDate,
    p.budgetConfirmed,
    p.decisionMakerIdentified,
    p.timelineDefined,
    p.needIdentified,
    c.clientName,
    bu.businessUnitName,
    ls.leadSourceName,
    t.teamName,
    ter.territoryName,
    ind.industryName,
    CONCAT(u.FirstName, ' ', u.Surname) as ownerName,
    DATEDIFF(CURRENT_DATE, p.DateAdded) as daysInPipeline
FROM tija_sales_prospects p
LEFT JOIN tija_clients c ON p.clientID = c.clientID
LEFT JOIN tija_business_units bu ON p.businessUnitID = bu.businessUnitID
LEFT JOIN tija_lead_sources ls ON p.leadSourceID = ls.leadSourceID
LEFT JOIN tija_prospect_teams t ON p.assignedTeamID = t.prospectTeamID
LEFT JOIN tija_prospect_territories ter ON p.territoryID = ter.territoryID
LEFT JOIN tija_prospect_industries ind ON p.industryID = ind.industryID
LEFT JOIN people u ON p.ownerID = u.ID
{$whereClause}
ORDER BY p.DateAdded DESC";

$prospects = $DBConn->fetch_all_rows($sql, $params);

if (!$prospects || count($prospects) == 0) {
    die('No prospects found to export.');
}

// Prepare CSV export
$filename = 'prospects_export_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// CSV Headers
$headers = array(
    'Prospect ID',
    'Date Added',
    'Prospect Name',
    'Case Name',
    'Email',
    'Phone',
    'Website',
    'Address',
    'Estimated Value',
    'Probability (%)',
    'Status',
    'Lead Score',
    'Qualification',
    'Company Size',
    'Expected Close Date',
    'Last Contact Date',
    'Next Follow-up Date',
    'Budget Confirmed',
    'Decision Maker Identified',
    'Timeline Defined',
    'Need Identified',
    'Client Name',
    'Business Unit',
    'Lead Source',
    'Team',
    'Territory',
    'Industry',
    'Owner',
    'Days in Pipeline'
);

fputcsv($output, $headers);

// Data rows
foreach ($prospects as $prospect) {
    $row = array(
        $prospect->salesProspectID,
        $prospect->DateAdded,
        $prospect->salesProspectName,
        $prospect->prospectCaseName ?? '',
        $prospect->prospectEmail ?? '',
        $prospect->prospectPhone ?? '',
        $prospect->prospectWebsite ?? '',
        strip_tags($prospect->address ?? ''),
        $prospect->estimatedValue ?? 0,
        $prospect->probability ?? 0,
        ucfirst($prospect->salesProspectStatus),
        $prospect->leadScore,
        ucfirst($prospect->leadQualificationStatus),
        ucfirst($prospect->companySize ?? ''),
        $prospect->expectedCloseDate ?? '',
        $prospect->lastContactDate ?? '',
        $prospect->nextFollowUpDate ?? '',
        $prospect->budgetConfirmed == 'Y' ? 'Yes' : 'No',
        $prospect->decisionMakerIdentified == 'Y' ? 'Yes' : 'No',
        $prospect->timelineDefined == 'Y' ? 'Yes' : 'No',
        $prospect->needIdentified == 'Y' ? 'Yes' : 'No',
        $prospect->clientName ?? '',
        $prospect->businessUnitName ?? '',
        $prospect->leadSourceName ?? '',
        $prospect->teamName ?? '',
        $prospect->territoryName ?? '',
        $prospect->industryName ?? '',
        $prospect->ownerName ?? '',
        $prospect->daysInPipeline
    );

    fputcsv($output, $row);
}

fclose($output);
exit;
?>
