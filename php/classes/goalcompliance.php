<?php
/**
 * Goal Compliance Class
 *
 * Handles jurisdiction-specific compliance rules
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalCompliance {

    /**
     * Check jurisdiction rules for goal
     *
     * @param string $goalUUID Goal UUID
     * @param int $jurisdictionID Jurisdiction entity ID
     * @param object $DBConn Database connection
     * @return array Compliance check results
     */
    public static function checkJurisdictionRules($goalUUID, $jurisdictionID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);
        if (!$goal) {
            return array('valid' => false, 'errors' => array('Goal not found'));
        }

        // Get jurisdiction entity
        $jurisdiction = $DBConn->retrieve_db_table_rows(
            'tija_entities',
            array('entityID', 'entityCountry', 'entityName'),
            array('entityID' => $jurisdictionID),
            true
        );

        if (!$jurisdiction) {
            return array('valid' => false, 'errors' => array('Jurisdiction not found'));
        }

        $errors = array();
        $warnings = array();

        // Check country-specific rules
        $country = $jurisdiction->entityCountry ?? '';

        // Germany: Works Council approval required for certain goals
        if (strtoupper($country) === 'GERMANY' || strtoupper($country) === 'DE') {
            if ($goal->propriety === 'Critical' || $goal->visibility === 'Global') {
                $warnings[] = 'Works Council approval may be required for this goal in Germany';
            }
        }

        // France: Certain performance metrics are prohibited
        if (strtoupper($country) === 'FRANCE' || strtoupper($country) === 'FR') {
            // Check if goal contains prohibited metrics (would need more detailed checking)
            if ($goal->goalType === 'KPI' && isset($goal->kpiData)) {
                // Example: Individual stack ranking is prohibited
                // This would require more sophisticated checking
            }
        }

        // Check library template jurisdiction deny list
        if ($goal->libraryRefID) {
            require_once 'goallibrary.php';
            $template = GoalLibrary::getTemplate($goal->libraryRefID, $DBConn);
            if ($template && $template->jurisdictionDeny) {
                $deniedJurisdictions = is_array($template->jurisdictionDeny)
                    ? $template->jurisdictionDeny
                    : json_decode($template->jurisdictionDeny, true);

                if (in_array($country, $deniedJurisdictions)) {
                    $errors[] = "This goal template is not valid for jurisdiction: {$country}";
                }
            }
        }

        return array(
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
            'jurisdiction' => $jurisdiction
        );
    }

    /**
     * Apply Works Council rules (Germany-specific)
     *
     * @param string $goalUUID Goal UUID
     * @param int $jurisdictionID Jurisdiction entity ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function applyWorksCouncilRules($goalUUID, $jurisdictionID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $jurisdiction = $DBConn->retrieve_db_table_rows(
            'tija_entities',
            array('entityCountry'),
            array('entityID' => $jurisdictionID),
            true
        );

        if (!$jurisdiction || strtoupper($jurisdiction->entityCountry ?? '') !== 'GERMANY') {
            return true; // Not applicable
        }

        // In production, this would:
        // 1. Check if goal requires Works Council approval
        // 2. Create approval workflow
        // 3. Set goal status to 'Pending Approval'
        // 4. Notify Works Council members

        // For now, just return success
        return true;
    }

    /**
     * Enforce data retention (GDPR compliance)
     *
     * @param int $jurisdictionID Jurisdiction entity ID
     * @param object $DBConn Database connection
     * @return int Number of records deleted
     */
    public static function enforceDataRetention($jurisdictionID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $jurisdiction = $DBConn->retrieve_db_table_rows(
            'tija_entities',
            array('entityCountry'),
            array('entityID' => $jurisdictionID),
            true
        );

        if (!$jurisdiction) {
            return 0;
        }

        $country = strtoupper($jurisdiction->entityCountry ?? '');
        $retentionYears = 3; // Default

        // Country-specific retention periods
        if ($country === 'GERMANY' || $country === 'DE') {
            $retentionYears = 3;
        } elseif ($country === 'FRANCE' || $country === 'FR') {
            $retentionYears = 5;
        }

        $cutoffDate = date('Y-m-d', strtotime("-{$retentionYears} years"));

        // Delete old goal history (would need a history table)
        // For now, just soft delete old completed goals
        $updateData = array('Lapsed' => 'Y');
        $where = array(
            'jurisdictionID' => $jurisdictionID,
            'status' => 'Completed',
            'endDate' => array('BETWEEN', '1900-01-01', $cutoffDate)
        );

        $result = $DBConn->update_table('tija_goals', $updateData, $where);

        // Delete old evaluations (anonymize instead of delete for audit trail)
        // This would be handled separately

        return $result ? 1 : 0; // Simplified return
    }
}

