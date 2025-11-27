<?php
/**
 * Sales API - Service Layer for Sales Module
 * Provides centralized data access and business logic for sales operations
 *
 * @author System
 * @version 2.0
 * @date 2025-10-09
 */

// Initialize session and includes
session_start();
$base = '../../../';
set_include_path($base);

// Start output buffering to capture any stray output
ob_start();

include 'php/includes.php';

// Clean any output from includes
ob_clean();

header('Content-Type: application/json');

try {
    // Validate user authentication
    if (!$isValidUser) {
        throw new Exception('Unauthorized access');
    }

    $userID = $userDetails->ID;

    $action = isset($_GET['action']) ? Utility::clean_string($_GET['action']) : '';
    $orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $userDetails->orgDataID;
    $entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $userDetails->entityID;

    $response = ['success' => false, 'message' => '', 'data' => null];

    switch ($action) {
        case 'get_dashboard_stats':
            $response['data'] = getSalesDashboardStats($orgDataID, $entityID, $userID, $DBConn);
            $response['success'] = true;
            break;

        case 'get_sales_cases':
            $stage = isset($_GET['stage']) ? Utility::clean_string($_GET['stage']) : 'opportunities';
            $filter = isset($_GET['filter']) ? Utility::clean_string($_GET['filter']) : 'all';
            $response['data'] = getSalesCases($orgDataID, $entityID, $userID, $stage, $filter, $DBConn);
            $response['success'] = true;
            break;

        case 'get_sales_pipeline':
            $response['data'] = getSalesPipeline($orgDataID, $entityID, $userID, $DBConn);
            $response['success'] = true;
            break;

        case 'get_proposals':
            $status = isset($_GET['status']) ? Utility::clean_string($_GET['status']) : 'all';
            $response['data'] = getProposals($orgDataID, $entityID, $userID, $status, $DBConn);
            $response['success'] = true;
            break;

        case 'get_sales_activities':
            $salesCaseID = isset($_GET['salesCaseID']) ? Utility::clean_string($_GET['salesCaseID']) : null;
            $response['data'] = getSalesActivities($orgDataID, $entityID, $salesCaseID, $DBConn);
            $response['success'] = true;
            break;

        case 'get_status_levels':
            $response['data'] = Sales::sales_status_levels(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
            $response['success'] = true;
            break;

        case 'get_lead_sources':
            $response['data'] = Sales::lead_sources(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
            $response['success'] = true;
            break;

        case 'getSalesCase':
            $caseID = isset($_GET['caseID']) ? Utility::clean_string($_GET['caseID']) : null;
            if (!$caseID) {
                throw new Exception('Sales case ID is required');
            }
            $response['data'] = getSingleSalesCase($caseID, $orgDataID, $entityID, $DBConn);
            $response['success'] = true;
            break;

        default:
            throw new Exception('Invalid action');
    }

    // Clean buffer and output JSON
    if (ob_get_level() > 0) ob_end_clean();
    echo json_encode($response);

} catch (Exception $e) {
    // Clean buffer before error response
    if (ob_get_level() > 0) ob_end_clean();

    http_response_code(400);

    // Log error for debugging
    error_log('Sales API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null,
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
}

/**
 * Get comprehensive dashboard statistics
 */
function getSalesDashboardStats($orgDataID, $entityID, $userID, $DBConn) {
    $currentMonth = date('Y-m-01');
    $nextMonth = date('Y-m-01', strtotime('+1 month'));

    // Get all sales cases for the current month
    $salesCases = Sales::sales_case_mid([
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ], false, $DBConn);

    $stats = [
        'total_value' => 0,
        'won_value' => 0,
        'estimated_value' => 0,
        'total_cases' => 0,
        'won_cases' => 0,
        'lost_cases' => 0,
        'active_cases' => 0,
        'attention_needed' => 0,
        'conversion_rate' => 0,
        'average_deal_size' => 0,
        'by_stage' => [],
        'by_status_level' => [],
        'recent_activities' => []
    ];

    if ($salesCases) {
        $stats['total_cases'] = count($salesCases);

        foreach ($salesCases as $case) {
            $stats['total_value'] += floatval($case->salesCaseEstimate);

            if ($case->closeStatus === 'won') {
                $stats['won_value'] += floatval($case->salesCaseEstimate);
                $stats['won_cases']++;
            } elseif ($case->closeStatus === 'lost') {
                $stats['lost_cases']++;
            } else {
                $stats['active_cases']++;
                $stats['estimated_value'] += floatval($case->salesCaseEstimate);
            }

            // Check if attention needed (no activity in 7 days or deadline approaching)
            $needsAttention = false;
            if (!empty($case->expectedCloseDate)) {
                $daysUntilClose = (strtotime($case->expectedCloseDate) - time()) / (60 * 60 * 24);
                if ($daysUntilClose > 0 && $daysUntilClose <= 7) {
                    $needsAttention = true;
                }
            }

            if ($needsAttention) {
                $stats['attention_needed']++;
            }

            // Group by stage
            $stage = $case->saleStage ?? 'unknown';
            if (!isset($stats['by_stage'][$stage])) {
                $stats['by_stage'][$stage] = ['count' => 0, 'value' => 0];
            }
            $stats['by_stage'][$stage]['count']++;
            $stats['by_stage'][$stage]['value'] += floatval($case->salesCaseEstimate);

            // Group by status level
            $statusLevel = $case->statusLevel ?? 'unknown';
            if (!isset($stats['by_status_level'][$statusLevel])) {
                $stats['by_status_level'][$statusLevel] = ['count' => 0, 'value' => 0];
            }
            $stats['by_status_level'][$statusLevel]['count']++;
            $stats['by_status_level'][$statusLevel]['value'] += floatval($case->salesCaseEstimate);
        }

        // Calculate conversion rate
        $totalClosed = $stats['won_cases'] + $stats['lost_cases'];
        if ($totalClosed > 0) {
            $stats['conversion_rate'] = round(($stats['won_cases'] / $totalClosed) * 100, 2);
        }

        // Calculate average deal size
        if ($stats['won_cases'] > 0) {
            $stats['average_deal_size'] = round($stats['won_value'] / $stats['won_cases'], 2);
        }
    }

    return $stats;
}

/**
 * Get sales cases with filtering
 */
function getSalesCases($orgDataID, $entityID, $userID, $stage, $filter, $DBConn) {
    $whereClause = [
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ];

    if ($stage !== 'all') {
        // Handle different stages
        if ($stage === 'business_development') {
            $whereClause['saleStage'] = 'business_development';
        } elseif ($stage === 'opportunities') {
            // Opportunities: All active cases that are NOT business_development, won, or lost
            // We'll filter these in PHP after fetching since we need to exclude multiple values
            // Don't add saleStage filter here
        } elseif ($stage === 'order') {
            // Orders: Cases with closeStatus = 'won'
            $whereClause['closeStatus'] = 'won';
        } elseif ($stage === 'lost') {
            // Lost: Cases with closeStatus = 'lost'
            $whereClause['closeStatus'] = 'lost';
        } else {
            // For any other specific stage
            $whereClause['saleStage'] = $stage;
        }
    }

    if ($filter === 'my') {
        $whereClause['salesPersonID'] = $userID;
    }

    $cases = Sales::sales_case_mid($whereClause, false, $DBConn);

    // For opportunities stage, filter out business_development, won, and lost cases
    if ($stage === 'opportunities' && $cases) {
        $cases = array_filter($cases, function($case) {
            // Include only cases that are:
            // - NOT business_development stage
            // - NOT won (closeStatus != 'won')
            // - NOT lost (closeStatus != 'lost')
            $isNotBusinessDev = ($case->saleStage !== 'business_development');
            $isNotWon = (empty($case->closeStatus) || $case->closeStatus !== 'won');
            $isNotLost = (empty($case->closeStatus) || $case->closeStatus !== 'lost');

            return $isNotBusinessDev && $isNotWon && $isNotLost;
        });

        // Re-index array after filtering
        $cases = array_values($cases);
    }

    if ($cases) {
        // Enhance each case with additional data
        foreach ($cases as &$case) {
            // Calculate days until close
            if (!empty($case->expectedCloseDate)) {
                $case->daysUntilClose = ceil((strtotime($case->expectedCloseDate) - time()) / (60 * 60 * 24));
            } else {
                $case->daysUntilClose = null;
            }

            // Get recent activities
            $activities = Sales::tija_sales_activities([
                'salesCaseID' => $case->salesCaseID,
                'Suspended' => 'N'
            ], false, $DBConn);
            $case->activityCount = $activities ? count($activities) : 0;
            $case->lastActivityDate = $activities ? $activities[0]->salesActivityDate : null;

            // Calculate probability-weighted value
            $case->weightedValue = floatval($case->salesCaseEstimate) * (floatval($case->probability) / 100);
        }
    }

    return $cases ?: [];
}

/**
 * Get sales pipeline overview
 * Only includes active opportunity status levels (excludes closed/won/lost levels)
 */
function getSalesPipeline($orgDataID, $entityID, $userID, $DBConn) {
    $statusLevels = Sales::sales_status_levels([
        'entityID' => $entityID,
        'Suspended' => 'N'
    ], false, $DBConn);

    $pipeline = [];

    if ($statusLevels) {
        foreach ($statusLevels as $level) {
            // Skip status levels marked as "close levels" (for won/lost/order sales)
            // These should not appear in the Kanban view
            if (!empty($level->closeLevel) && ($level->closeLevel === 'Y' || $level->closeLevel === '1')) {
                continue;
            }

            $cases = Sales::sales_case_mid([
                'orgDataID' => $orgDataID,
                'entityID' => $entityID,
                'saleStatusLevelID' => $level->saleStatusLevelID,
                'Suspended' => 'N'
            ], false, $DBConn);

            // Filter to include only active opportunities (not business_development, won, or lost)
            if ($cases) {
                $cases = array_filter($cases, function($case) {
                    $isNotBusinessDev = ($case->saleStage !== 'business_development');
                    $isNotWon = (empty($case->closeStatus) || $case->closeStatus !== 'won');
                    $isNotLost = (empty($case->closeStatus) || $case->closeStatus !== 'lost');
                    return $isNotBusinessDev && $isNotWon && $isNotLost;
                });
                $cases = array_values($cases);
            }

            $totalValue = 0;
            $weightedValue = 0;
            $caseCount = 0;

            if ($cases) {
                $caseCount = count($cases);
                foreach ($cases as $case) {
                    $totalValue += floatval($case->salesCaseEstimate);
                    $weightedValue += floatval($case->salesCaseEstimate) * (floatval($case->probability) / 100);
                }
            }

            $pipeline[] = [
                'level' => $level->statusLevel,
                'levelID' => $level->saleStatusLevelID,
                'percentage' => floatval($level->levelPercentage),
                'count' => $caseCount,
                'totalValue' => $totalValue,
                'weightedValue' => $weightedValue,
                'cases' => $cases ?: []
            ];
        }
    }

    return $pipeline;
}

/**
 * Get proposals with filtering
 */
function getProposals($orgDataID, $entityID, $userID, $status, $DBConn) {
    $whereClause = [
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ];

    if ($status !== 'all') {
        $whereClause['proposalStatusID'] = $status;
    }

    $proposals = Sales::proposal_full($whereClause, false, $DBConn);

    if ($proposals) {
        foreach ($proposals as &$proposal) {
            // Calculate days until deadline
            if (!empty($proposal->proposalDeadline)) {
                $proposal->daysUntilDeadline = ceil((strtotime($proposal->proposalDeadline) - time()) / (60 * 60 * 24));
            } else {
                $proposal->daysUntilDeadline = null;
            }

            // Add urgency flag
            $proposal->isUrgent = $proposal->daysUntilDeadline !== null && $proposal->daysUntilDeadline <= 3;
        }
    }

    return $proposals ?: [];
}

/**
 * Get sales activities
 */
function getSalesActivities($orgDataID, $entityID, $salesCaseID, $DBConn) {
    $whereClause = [
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ];

    if ($salesCaseID) {
        $whereClause['salesCaseID'] = $salesCaseID;
    }

    return Sales::tija_sales_activities($whereClause, false, $DBConn) ?: [];
}

/**
 * Get a single sales case by ID
 */
function getSingleSalesCase($caseID, $orgDataID, $entityID, $DBConn) {
    // First try with full criteria
    $whereClause = [
        'salesCaseID' => $caseID,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'Suspended' => 'N'
    ];

    $salesCases = Sales::sales_case_mid($whereClause, false, $DBConn);

    // If not found, try with just caseID and orgDataID (more permissive)
    if (!$salesCases || count($salesCases) === 0) {
        $whereClause = [
            'salesCaseID' => $caseID,
            'orgDataID' => $orgDataID,
            'Suspended' => 'N'
        ];
        $salesCases = Sales::sales_case_mid($whereClause, false, $DBConn);
    }

    // If still not found, try with just caseID (most permissive)
    if (!$salesCases || count($salesCases) === 0) {
        $whereClause = [
            'salesCaseID' => $caseID,
            'Suspended' => 'N'
        ];
        $salesCases = Sales::sales_case_mid($whereClause, false, $DBConn);
    }

    if (!$salesCases || count($salesCases) === 0) {
        throw new Exception("Sales case #$caseID not found or you do not have permission to access it");
    }

    $salesCase = $salesCases[0];

    // Convert to array properly
    if (is_object($salesCase)) {
        $caseData = json_decode(json_encode($salesCase), true);
    } else {
        $caseData = $salesCase;
    }

    // Calculate days until close
    $expectedCloseDate = $caseData['expectedCloseDate'] ?? null;
    if (!empty($expectedCloseDate)) {
        $caseData['daysUntilClose'] = ceil((strtotime($expectedCloseDate) - time()) / (60 * 60 * 24));
    } else {
        $caseData['daysUntilClose'] = null;
    }

    // Add urgency flag
    $caseData['isUrgent'] = isset($caseData['daysUntilClose']) && $caseData['daysUntilClose'] !== null && $caseData['daysUntilClose'] <= 7;

    // Format dates
    $caseData['expectedCloseDateFormatted'] = !empty($expectedCloseDate)
        ? date('M d, Y', strtotime($expectedCloseDate))
        : null;

    return (object) $caseData;
}

