<?php
/**
 * SOP Management Class
 *
 * Manages Standard Operating Procedures
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

class SOPManagement {

    /**
     * Create SOP
     *
     * @param array $data SOP data
     * @param object $DBConn Database connection
     * @return int|false SOP ID or false
     */
    public static function createSOP($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'sopCode', 'sopTitle', 'sopDescription', 'processID',
            'functionalArea', 'sopVersion', 'sopDocumentURL', 'sopContent',
            'effectiveDate', 'expiryDate', 'approvalStatus', 'createdByID',
            'functionalAreaOwnerID'
        );

        $data['approvalStatus'] = $data['approvalStatus'] ?? 'draft';
        $data['sopVersion'] = $data['sopVersion'] ?? '1.0';

        return $DBConn->insert_db_table_row('tija_sops', $cols, $data);
    }

    /**
     * Get SOP with sections and attachments
     *
     * @param int $sopID SOP ID
     * @param object $DBConn Database connection
     * @return array|false SOP data or false
     */
    public static function getSOP($sopID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'sopID', 'sopCode', 'sopTitle', 'sopDescription', 'processID',
            'functionalArea', 'sopVersion', 'sopDocumentURL', 'sopContent',
            'effectiveDate', 'expiryDate', 'approvalStatus', 'approvedByID',
            'approvedDate', 'createdByID', 'functionalAreaOwnerID',
            'DateAdded', 'LastUpdate', 'isActive'
        );

        $sop = $DBConn->retrieve_db_table_rows('tija_sops', $cols, ['sopID' => $sopID], true);

        if (!$sop) {
            return false;
        }

        // Get sections
        $sop['sections'] = self::getSOPSections($sopID, $DBConn);

        // Get attachments
        $sop['attachments'] = self::getSOPAttachments($sopID, $DBConn);

        return $sop;
    }

    /**
     * Get SOP sections
     *
     * @param int $sopID SOP ID
     * @param object $DBConn Database connection
     * @return array|false Sections or false
     */
    public static function getSOPSections($sopID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array('sectionID', 'sopID', 'sectionOrder', 'sectionTitle', 'sectionContent', 'sectionType');
        $whereArr = ['sopID' => $sopID];

        $sections = $DBConn->retrieve_db_table_rows('tija_sop_sections', $cols, $whereArr);

        if ($sections && is_array($sections)) {
            usort($sections, function($a, $b) {
                return $a['sectionOrder'] <=> $b['sectionOrder'];
            });
        }

        return $sections ?: false;
    }

    /**
     * Get SOP attachments
     *
     * @param int $sopID SOP ID
     * @param object $DBConn Database connection
     * @return array|false Attachments or false
     */
    public static function getSOPAttachments($sopID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array('attachmentID', 'sopID', 'fileName', 'fileURL', 'fileType', 'fileSize', 'uploadedDate');
        return $DBConn->retrieve_db_table_rows('tija_sop_attachments', $cols, ['sopID' => $sopID]);
    }

    /**
     * Link SOP to task/template
     *
     * @param int $sopID SOP ID
     * @param string $linkType Link type
     * @param int $linkedEntityID Linked entity ID
     * @param bool $isRequired Is required review
     * @param object $DBConn Database connection
     * @return int|false Link ID or false
     */
    public static function linkSOPToTask($sopID, $linkType, $linkedEntityID, $isRequired = false, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array('sopID', 'linkType', 'linkedEntityID', 'isRequired');
        $data = [
            'sopID' => $sopID,
            'linkType' => $linkType,
            'linkedEntityID' => $linkedEntityID,
            'isRequired' => $isRequired ? 'Y' : 'N'
        ];

        return $DBConn->insert_db_table_row('tija_sop_links', $cols, $data);
    }

    /**
     * Approve SOP
     *
     * @param int $sopID SOP ID
     * @param int $approverID Approver ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function approveSOP($sopID, $approverID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $updateData = [
            'approvalStatus' => 'approved',
            'approvedByID' => $approverID,
            'approvedDate' => date('Y-m-d H:i:s')
        ];

        return $DBConn->update_db_table_row('tija_sops', $updateData, ['sopID' => $sopID]);
    }
}

