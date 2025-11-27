<?php
/**
 * Proposal Classes
 */
class Proposal {
   // Proposal functions
   public static function proposals ($whereArr, $single, $DBConn) {
      $cols= array(
         'proposalID',
         'DateAdded',
         'proposalTitle',
         'clientID',
         'salesCaseID',
         'proposalDeadline',
         'proposalStatusID',
         'proposalDescription',
         'proposalComments',
         'proposalValue',
         'employeeID',
         'entityID',
         'orgDataID',
         'proposalFile',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_proposals', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function proposal_full( $whereArr, $single=false, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();

      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            $where .= "p.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql= "SELECT
         p.proposalID, p.DateAdded, p.proposalTitle, p.proposalCode, p.clientID, p.salesCaseID, p.proposalDeadline, p.proposalStatusID, p.proposalDescription, p.proposalComments, p.proposalValue, p.employeeID, p.entityID, p.orgDataID, p.proposalFile,
         c.clientName, c.clientCode,
         s.salesCaseName,
         ps.proposalStatusName,
         u.FirstName as employeeFirstName,
         u.Surname as employeeLastName,
         CONCAT(u.FirstName, ' ', u.Surname) as employeeName

      FROM tija_proposals p
      LEFT JOIN tija_clients c ON p.clientID = c.clientID
      LEFT JOIN tija_sales_cases s ON p.salesCaseID = s.salesCaseID
      LEFT JOIN tija_proposal_statuses ps ON p.proposalStatusID = ps.proposalStatusID
      LEFT JOIN people u ON p.employeeID = u.ID

      {$where}
      ORDER BY p.proposalDeadline DESC, p.DateAdded DESC";
       $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function proposal_statuses($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();

      $proposalStatuses= array(
         'proposalStatusID ',
         'DateAdded',
         'proposalStatusName',
         'proposalStatusDescription',
         'proposalStatusCategoryID',
         'orgDataID',
         'entityID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $proposalStatusCategories = array('proposalStatusCategoryName', 'proposalStatusCategoryDescription' );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $proposalStatuses)) {
               $where .= "u.{$col} = ?";
             } elseif (in_array($col, $proposalStatusCategories)) {
               $where .= "c.{$col} = ?";
             } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
             }

            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql= "SELECT
         u.proposalStatusID, u.DateAdded, u.proposalStatusName, u.proposalStatusDescription, u.orgDataID, u.entityID, u.LastUpdate, u.LastUpdateByID, u.Lapsed, u.Suspended,
         c.proposalStatusCategoryName,
         c.proposalStatusCategoryDescription,
         CONCAT(p.FirstName, ' ', p.Surname) as LastUpdatedByName
         FROM tija_proposal_statuses u
         LEFT JOIN tija_proposal_status_categories c ON u.proposalStatusCategoryID = c.proposalStatusCategoryID
         LEFT JOIN people p ON u.LastUpdateByID = p.ID
         {$where}
         ORDER BY u.DateAdded ASC, u.proposalStatusName ASC
      ";
         $rows = $DBConn->fetch_all_rows($sql,$params);

      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }
   public static function proposal_activities($whereArr, $single, $DBConn) {
      $cols= array(
         'proposalActivityID',
         'DateAdded',
         'proposalID',
         'activityTypeID',
         'activityDate',
         'activityTime',
         'activityName',
         'activityDescription',
         'activityOwnerID',
         'activityStatusID',
         'activityDeadline',
         'activityNotes',
         'LastUpdate',
         'LastUpdatedByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_proposal_activities', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function proposal_status_categories($whereArr, $single, $DBConn) {
      $cols= array(
         'proposalStatusCategoryID ',
         'DateAdded',
         'proposalStatusCategoryName',
         'proposalStatusCategoryDescription',
         'orgDataID',
         'entityID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_proposal_status_categories', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }
   /**
    * proposal checklist functions
      * proposal checklist items categories
      * proposal checklist items
      * proposal checklist
      * proposal checklist status
    * proposal checklist status categories
      * proposal checklist items categories
    */

   // proposal checklist items categories
   public static function proposal_checklist_items_categories($whereArr, $single, $DBConn) {
      $cols= array(
         'proposalChecklistItemCategoryID',
         'DateAdded',
         'proposalChecklistItemCategoryName',
         'proposalChecklistItemCategoryDescription',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_proposal_checklist_item_categories', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   // proposal checklist items
   public static function proposal_checklist_items($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $checklistItems= array(
         'proposalChecklistItemID',
         'DateAdded',
         'proposalChecklistItemName',
         'proposalChecklistItemDescription',
         'proposalChecklistItemCategoryID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $checklistItemCategories = array('proposalChecklistItemCategoryID', 'proposalChecklistItemCategoryDescription' );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $checklistItems)) {
               $where .= "u.{$col} = ?";
             } elseif (in_array($col, $checklistItemCategories)) {
               $where .= "c.{$col} = ?";
             } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
             }

            $params[] = array($val, 's');
            $i++;
         }
      }

      $sql = "SELECT
         u.proposalChecklistItemID, u.DateAdded, u.proposalChecklistItemName, u.proposalChecklistItemDescription, u.proposalChecklistItemCategoryID,  u.LastUpdate, u.LastUpdateByID, u.Lapsed, u.Suspended,
         c.proposalChecklistItemCategoryName,
         c.proposalChecklistItemCategoryDescription,
         CONCAT(p.FirstName, ' ', p.Surname) as LastUpdatedByName
         FROM tija_proposal_checklist_items u
         LEFT JOIN tija_proposal_checklist_item_categories c ON u.proposalChecklistItemCategoryID = c.proposalChecklistItemCategoryID
         LEFT JOIN people p ON u.LastUpdateByID = p.ID
         {$where}
         ORDER BY u.DateAdded ASC, u.proposalChecklistItemName ASC";
         $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   // !check this out to confirm
   public static function proposal_checklist($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $checklistItems= array(
         'proposalChecklistID',
         'DateAdded',
         'proposalChecklistName',
         'proposalID',
         'proposalChecklistStatusID',
         'proposalChecklistDeadlineDate',
         'proposalChecklistDescription',
         'assignedEmployeeID',
         'assigneeID',
         'entityID',
         'orgDataID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $checklistItemCategories = array('proposalChecklistStatusID', 'proposalChecklistStatusDescription' );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $checklistItems)) {
               $where .= "u.{$col} = ?";
             } elseif (in_array($col, $checklistItemCategories)) {
               $where .= "c.{$col} = ?";
             } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
             }

            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql = "SELECT
         u.proposalChecklistID,  u.DateAdded, u.proposalChecklistName, u.proposalID, u.proposalChecklistStatusID, u.proposalChecklistDeadlineDate, u.proposalChecklistDescription, u.assignedEmployeeID, u.assigneeID, u.entityID, u.orgDataID, u.LastUpdate, u.LastUpdateByID, u.Lapsed, u.Suspended,
         c.proposalChecklistStatusName,
         c.proposalChecklistStatusDescription,
         CONCAT(p2.FirstName, ' ', p2.Surname) as AssigneeName,
         CONCAT(p.FirstName, ' ', p.Surname) as AssignedEmployeeName
         FROM tija_proposal_checklists u
         LEFT JOIN tija_proposal_checklist_status c ON u.proposalChecklistStatusID = c.proposalChecklistStatusID
         LEFT JOIN people p ON u.assignedEmployeeID = p.ID
         LEFT JOIN people p2 ON u.assigneeID = p2.ID

         {$where}
         ORDER BY u.DateAdded ASC";
         $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   // proposal checklist status
   public static function proposal_checklist_status($whereArr, $single, $DBConn) {
      $cols= array(
         'proposalChecklistStatusID',
         'DateAdded',
         'proposalChecklistStatusName',
         'proposalChecklistStatusDescription',
         'proposalChecklistStatusType',
         'orgDataID',
         'entityID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_proposal_checklist_status', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function proposal_checklist_item_assignment_full($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $checklistItemAssignment= array(
         'proposalChecklistItemAssignmentID',
         'DateAdded',
         'proposalID',
         'proposalChecklistID',
         'proposalChecklistItemCategoryID',
         'proposalChecklistItemID',
         'proposalChecklistItemAssignmentDueDate',
         'proposalChecklistItemAssignmentDescription',
         'proposalChecklistAssignmentDocument',
         'proposalChecklistTemplate',
         'proposalChecklistItemAssignmentStatusID',
         'checklistItemAssignedEmployeeID',
         'proposalChecklistAssignorID',
         'checklistTemplate',
         'orgDataID',
         'entityID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );

      $proposalArr = array('proposalID', 'proposalTitle', 'proposalDeadline', 'proposalDescription', 'proposalValue' );

      $checklistsArr= array(
         'proposalChecklistName',
         'proposalChecklistStatusID',
         'proposalChecklistDeadlineDate',
         'proposalChecklistDescription',
         'assignedEmployeeID',
         'assigneeID',

      );
      $checklistStatusArr = array(
         'proposalChecklistStatusName',
         'proposalChecklistStatusDescription',
      );
      $proposalChecklistCategoryArr = array( 'proposalChecklistItemCategoryName', 'proposalChecklistItemCategoryDescription' );

      $checklistItemsArr= array(
         'proposalChecklistItemID',
         'DateAdded',
         'proposalChecklistItemName',
         'proposalChecklistItemDescription',
         );

      $checklistItemAssignmentStatus = array('proposalChecklistItemAssignmentStatusName', 'proposalChecklistItemAssignmentStatusDescription' );
      $checklistItemAssignedEmployee = array( 'checklistItemAssignedEmployeeName' );
      $checklistItemAssignedEmployeeArr = array( 'checklistItemAssignedEmployeeID', 'checklistItemAssignedEmployeeName' );


      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if (in_array($col, $checklistItemAssignment)) {
               $where .= "u.{$col} = ?";
            } elseif (in_array($col, $proposalArr)) {
               $where .= "p.{$col} = ?";
            } elseif (in_array($col, $checklistsArr)) {
               $where .= "t.{$col} = ?";
            } elseif (in_array($col, $checklistStatusArr)) {
               $where .= "c.{$col} = ?";
            } elseif (in_array($col, $proposalChecklistCategoryArr)) {
               $where .= "i.{$col} = ?";
            } elseif (in_array($col, $checklistItemsArr)) {
               $where .= "i.{$col} = ?";
            } elseif (in_array($col, $checklistItemAssignmentStatus)) {
               $where .= "s.{$col} = ?";
            } elseif (in_array($col, $checklistItemAssignedEmployee)) {
               $where .= "p2.{$col} = ?";
            } elseif (in_array($col, $checklistItemAssignedEmployeeArr)) {
               $where .= "u.{$col} = ?";
            } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
            }

            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql = "SELECT
      u.proposalChecklistItemAssignmentID, u.DateAdded, u.proposalID, u.proposalChecklistID, u.proposalChecklistItemCategoryID, u.proposalChecklistItemID, u.proposalChecklistItemAssignmentDueDate,
      u.proposalChecklistItemAssignmentDescription, u.proposalChecklistAssignmentDocument, u.proposalChecklistTemplate, u.proposalChecklistItemAssignmentStatusID, u.checklistItemAssignedEmployeeID,
      u.proposalChecklistAssignorID, u.checklistTemplate, u.orgDataID, u.entityID, u.LastUpdate, u.LastUpdateByID, u.Lapsed, u.Suspended,
         pt.proposalTitle,
         pt.proposalDeadline,
         pt.proposalDescription,
         pt.proposalValue,
         t.proposalChecklistName,
         t.assignedEmployeeID,
         t.assigneeID,
         s.proposalChecklistStatusName AS proposalChecklistStatusItemName,
         s.proposalChecklistStatusDescription As proposalChecklistStatusItemDescription,
         c.proposalChecklistItemCategoryName,
         c.proposalChecklistItemCategoryDescription,
         i.proposalChecklistItemName,
         i.proposalChecklistItemDescription,

         CONCAT(p2.FirstName, ' ', p2.Surname) as checklistItemAssignedEmployeeName,
         CONCAT(p.FirstName, ' ', p.Surname) as LastUpdatedByName,
         CONCAT(p5.FirstName, ' ', p5.Surname) as checklistOwnerName,
         CONCAT(p4.FirstName, ' ', p4.Surname) as checklistAssignedEmployeeName,
         CONCAT (p6.FirstName, ' ', p6.Surname) as proposalChecklistAssignorName

      FROM tija_proposal_checklist_item_assignment u
      LEFT JOIN tija_proposal_checklist_status s ON u.proposalChecklistItemAssignmentStatusID = s.proposalChecklistStatusID
      LEFT JOIN people p ON u.LastUpdateByID = p.ID
      LEFT JOIN people p2 ON u.checklistItemAssignedEmployeeID = p2.ID
      LEFT JOIN tija_proposal_checklist_items i ON u.proposalChecklistItemID = i.proposalChecklistItemID
      LEFT JOIN tija_proposals pt ON u.proposalID = pt.proposalID
      LEFT JOIN tija_proposal_checklists t ON u.proposalChecklistID = t.proposalChecklistID
      LEFT JOIN people p4 ON t.assignedEmployeeID = p4.ID
      LEFT JOIN people p5 ON t.assigneeID = p5.ID
      LEFT JOIN people p6 ON u.proposalChecklistAssignorID = p6.ID


      LEFT JOIN tija_proposal_checklist_item_categories c ON u.proposalChecklistItemCategoryID = c.proposalChecklistItemCategoryID

      {$where}
      ORDER BY u.DateAdded ASC";

      $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }


   public static function proposal_checklist_item_assignment($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $checklistItemAssignment= array(
         'proposalChecklistItemAssignmentID',
         'DateAdded',
         'proposalChecklistID',
         'proposalID',
         'proposalChecklistItemID',
         'proposalChecklistItemAssignmentDescription',
         'proposalChecklistItemAssignmentStatusID',
         'checklistItemAssignedEmployeeID',
         'checklistItemAssignedEmployeeName',
         'proposalChecklistItemAssignmentDueDate',
         'proposalChecklistTemplate',
         'proposalChecklistAssignorID',
         'orgDataID',
         'entityID',

         'checklistTemplate',
         'checklistAssignmentDocument',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $checklistItemAssignmentStatus = array('proposalChecklistItemAssignmentStatusName', 'proposalChecklistItemAssignmentStatusDescription' );
      $checklistItemAssignedEmployee = array('checklistItemAssignedEmployeeID', 'checklistItemAssignedEmployeeName' );
      $checklistItems= array(
         'proposalChecklistItemID',
         'DateAdded',
         'proposalChecklistItemName',
         'proposalChecklistItemDescription',
         'proposalChecklistItemCategoryID'
      );






      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $checklistItemAssignment)) {
               $where .= "u.{$col} = ?";
             } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
             }

            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql = "SELECT
         u.proposalChecklistItemAssignmentID, u.DateAdded, u.proposalChecklistID, u.proposalChecklistItemID, u.proposalChecklistItemAssignmentDescription, u.proposalChecklistItemAssignmentDueDate, u.proposalChecklistItemAssignmentStatusID, u.checklistItemAssignedEmployeeID, u.checklistTemplate, u.proposalChecklistAssignmentDocument, u.LastUpdate, u.LastUpdateByID, u.Lapsed, u.Suspended, u.orgDataID, u.entityID, u.proposalChecklistTemplate, u.proposalChecklistAssignorID,
         c.proposalChecklistStatusName,
         CONCAT(p.FirstName, ' ', p.Surname) as LastUpdatedByName,
         CONCAT(p2.FirstName, ' ', p2.Surname) as AssignedEmployeeName


      FROM tija_proposal_checklist_item_assignment u
      LEFT JOIN tija_proposal_checklist_status c ON u.proposalChecklistItemAssignmentStatusID = c.proposalChecklistStatusID
      LEFT JOIN people p ON u.LastUpdateByID = p.ID
      LEFT JOIN people p2 ON u.checklistItemAssignedEmployeeID = p2.ID
      {$where}
      ORDER BY u.DateAdded ASC";

      $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function proposal_attachments($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $proposalAttachments= array(
         'proposalAttachmentID',
         'DateAdded',
         'proposalID',
         'proposalAttachmentName',
         'proposalAttachmentFile',
         'proposalAttachmentType',
         'uploadByEmployeeID',

      );
      $proposalAttachmentTypes = array('proposalChecklistItemID', 'proposalChecklistItemName' );
      $proposals = array('proposalID', 'proposalTitle' );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if (in_array($col, $proposalAttachments)) {
               $where .= "u.{$col} = ?";
            } elseif (in_array($col, $proposalAttachmentTypes)) {
               $where .= "c.{$col} = ?";
            } elseif (in_array($col, $proposals)) {
               $where .= "p.{$col} = ?";
            } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
            }

            $params[] = array($val, 's');
            $i++;
         }
      }

      $sql = "SELECT
         u.proposalAttachmentID, u.DateAdded, u.proposalID, u.proposalAttachmentName, u.proposalAttachmentFile, u.proposalAttachmentType, u.uploadByEmployeeID, u.LastUpdate, u.LastUpdateByID, u.Lapsed, u.Suspended,
         c.proposalChecklistItemName,
         p.proposalTitle,
         CONCAT(p2.FirstName, ' ', p2.Surname) as LastUpdatedByName,
         CONCAT(p3.FirstName, ' ', p3.Surname) as UploadByEmployeeName

         FROM tija_proposal_attachments u
         LEFT JOIN tija_proposal_checklist_items c ON u.proposalAttachmentType = c.proposalChecklistItemID
         LEFT JOIN tija_proposals p ON u.proposalID = p.proposalID
         LEFT JOIN people p2 ON u.LastUpdateByID = p2.ID
         LEFT JOIN people p3 ON u.uploadByEmployeeID = p3.ID

         {$where}
         ORDER BY u.DateAdded ASC";

         $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

   }

   public static function proposal_checklist_item_assignment_submissions($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $checklistItemAssignmentSubmission= array(
         'proposalChecklistItemAssignmentSubmissionID',
         'DateAdded',
         'proposalChecklistItemAssignmentID',
         'checklistItemAssignedEmployeeID',
         'proposalChecklistItemAssignmentStatusID',
         'proposalChecklistItemUploadfiles',
         'proposalChecklistItemAssignmentSubmissionDescription',
         'proposalChecklistItemAssignmentSubmissionDate',
         'proposalChecklistItemAssignmentSubmissionStatusID',
         'submissionStatus',
         'submissionNotes',
         'submittedBy',
         'submissionDate',
         'reviewedBy',
         'reviewedDate',
         'reviewNotes',
         'submissionFiles',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $checklistItemAssignmentStatus = array('proposalChecklistStatusName', 'proposalChecklistStatusDescription' );
      $proposalChecklistItemAssignment = array('proposalChecklistItemAssignmentID', 'proposalChecklistItemID', 'proposalChecklistID', 'proposalID' );
      $checklistItems= array(
         'proposalChecklistItemID',
         'DateAdded',
         'proposalChecklistItemName',
         'proposalChecklistItemDescription',
         );

      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $checklistItemAssignmentSubmission)) {
               $where .= "u.{$col} = ?";
               $params[] = array($val, 's');
            } elseif (in_array($col, $checklistItems)) {
               $where .= "i.{$col} = ?";
               $params[] = array($val, 's');
            } elseif (in_array($col, $checklistItemAssignmentStatus)) {
               $where .= "s.{$col} = ?";
               $params[] = array($val, 's');
            } elseif (in_array($col, $proposalChecklistItemAssignment)) {
               $where .= "a.{$col} = ?";
               $params[] = array($val, 's');
            } else {
               // If the column is not found in any of the tables, skip it
               // Remove the "AND" that was added if this is the first condition
               if (strpos($where, 'WHERE ') !== false && substr($where, -5) === ' AND ') {
                  $where = substr($where, 0, -5);
               }
               continue; // Skip this column
            }

            $i++;
         }
      }
      // Use new table structure if it exists, otherwise fall back to old table
      // Check which table exists and use appropriate structure
      $sql = "SELECT
      u.submissionID as proposalChecklistItemAssignmentSubmissionID,
      u.DateAdded,
      u.proposalChecklistItemAssignmentID,
      u.submittedBy as checklistItemAssignedEmployeeID,
      u.submissionStatus,
      u.submissionNotes as proposalChecklistItemAssignmentSubmissionDescription,
      u.submissionFiles as proposalChecklistItemUploadfiles,
      u.submissionDate as proposalChecklistItemAssignmentSubmissionDate,
      u.reviewedBy,
      u.reviewedDate,
      u.reviewNotes,
      u.LastUpdate,
      u.LastUpdatedByID as LastUpdateByID,
      u.Suspended,
      a.proposalChecklistItemAssignmentStatusID,
      s.proposalChecklistStatusName AS proposalChecklistItemAssignmentStatusName,
      s.proposalChecklistStatusDescription AS proposalChecklistItemAssignmentStatusDescription,
      i.proposalChecklistItemName,
      i.proposalChecklistItemDescription,
      a.proposalChecklistID,
      a.proposalID,
      CONCAT(p.FirstName, ' ', p.Surname) as LastUpdatedByName,
      CONCAT(p2.FirstName, ' ', p2.Surname) as checklistItemAssignedEmployeeName
      FROM tija_proposal_checklist_item_submissions u
      LEFT JOIN tija_proposal_checklist_item_assignment a ON u.proposalChecklistItemAssignmentID = a.proposalChecklistItemAssignmentID
      LEFT JOIN tija_proposal_checklist_items i ON a.proposalChecklistItemID = i.proposalChecklistItemID
      LEFT JOIN tija_proposal_checklist_status s ON a.proposalChecklistItemAssignmentStatusID = s.proposalChecklistStatusID
      LEFT JOIN people p ON u.LastUpdatedByID = p.ID
      LEFT JOIN people p2 ON u.submittedBy = p2.ID

      {$where}
      ORDER BY u.submissionDate DESC, u.DateAdded DESC";
      $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get proposal status stages
    * @param array $whereArr - WHERE conditions
    * @param bool $single - Return single record or multiple
    * @param object $DBConn - Database connection
    * @return mixed - Array of records or single record or false
    */
   public static function proposal_status_stages($whereArr, $single, $DBConn) {
      $cols = array(
         'stageID',
         'stageCode',
         'stageName',
         'stageDescription',
         'stageOrder',
         'isActive',
         'requiresApproval',
         'canEdit',
         'colorCode',
         'iconClass',
         'DateAdded',
         'LastUpdate'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_proposal_status_stages', $cols, $whereArr);
      return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get proposal tasks
    * @param array $whereArr - WHERE conditions
    * @param bool $single - Return single record or multiple
    * @param object $DBConn - Database connection
    * @return mixed - Array of records or single record or false
    */
   public static function proposal_tasks($whereArr, $single, $DBConn) {
      $params = array();
      $where = '';

      $taskArray = array(
         'proposalTaskID',
         'proposalID',
         'taskName',
         'taskDescription',
         'assignedTo',
         'assignedBy',
         'dueDate',
         'priority',
         'status',
         'completionPercentage',
         'isMandatory',
         'completedDate',
         'completedBy',
         'orgDataID',
         'entityID',
         'DateAdded',
         'LastUpdate',
         'LastUpdatedByID',
         'Suspended'
      );

      if (count($whereArr) > 0) {
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if (in_array($col, $taskArray)) {
               $where .= "pt.{$col} = ?";
            } else {
               continue;
            }
            $params[] = array($val, 's');
         }
      }

      $sql = "SELECT
         pt.proposalTaskID,
         pt.proposalID,
         pt.taskName,
         pt.taskDescription,
         pt.assignedTo,
         pt.assignedBy,
         pt.dueDate,
         pt.priority,
         pt.status,
         pt.completionPercentage,
         pt.isMandatory,
         pt.completedDate,
         pt.completedBy,
         pt.orgDataID,
         pt.entityID,
         pt.DateAdded,
         pt.LastUpdate,
         pt.LastUpdatedByID,
         pt.Suspended,
         CONCAT(a.FirstName, ' ', a.Surname) as assignedToName,
         CONCAT(b.FirstName, ' ', b.Surname) as assignedByName,
         CONCAT(c.FirstName, ' ', c.Surname) as completedByName
      FROM tija_proposal_tasks pt
      LEFT JOIN people a ON pt.assignedTo = a.ID
      LEFT JOIN people b ON pt.assignedBy = b.ID
      LEFT JOIN people c ON pt.completedBy = c.ID
      {$where}
      ORDER BY pt.dueDate ASC, pt.priority DESC";

      $rows = $DBConn->fetch_all_rows($sql, $params);
      return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Calculate proposal completion percentage
    * @param int $proposalID
    * @param object $DBConn
    * @return array - ['total' => percentage, 'mandatory' => percentage, 'totalItems' => count, 'completedItems' => count, 'mandatoryItems' => count, 'completedMandatory' => count]
    */
   public static function calculate_proposal_completion($proposalID, $DBConn) {
      // Get all checklist item assignments for this proposal
      $assignments = Proposal::proposal_checklist_item_assignment_full(
         array('proposalID' => $proposalID, 'Suspended' => 'N'),
         false,
         $DBConn
      );

      if (!$assignments || empty($assignments)) {
         return array(
            'total' => 0,
            'mandatory' => 0,
            'totalItems' => 0,
            'completedItems' => 0,
            'mandatoryItems' => 0,
            'completedMandatory' => 0
         );
      }

      $totalItems = 0;
      $completedItems = 0;
      $mandatoryItems = 0;
      $completedMandatory = 0;

      // Status IDs that indicate completion (adjust based on your status system)
      $completedStatusIDs = array();
      // Get all checklist statuses and find completed ones
      $allStatuses = Proposal::proposal_checklist_status(
         array('proposalChecklistStatusType' => 'checkListItem', 'Suspended' => 'N'),
         false,
         $DBConn
      );

      if ($allStatuses) {
         foreach ($allStatuses as $status) {
            $statusName = strtolower($status->proposalChecklistStatusName ?? '');
            // Consider statuses as completed if they contain these keywords
            if (strpos($statusName, 'complete') !== false ||
                strpos($statusName, 'approved') !== false ||
                strpos($statusName, 'done') !== false ||
                strpos($statusName, 'finished') !== false) {
               $completedStatusIDs[] = $status->proposalChecklistStatusID;
            }
         }
      }

      foreach ($assignments as $assignment) {
         $totalItems++;
         $isMandatory = ($assignment->isMandatory ?? 'N') === 'Y';

         if ($isMandatory) {
            $mandatoryItems++;
         }

         // Check if assignment is completed
         $isCompleted = false;
         if (isset($assignment->proposalChecklistItemAssignmentStatusID)) {
            $isCompleted = in_array($assignment->proposalChecklistItemAssignmentStatusID, $completedStatusIDs);
         }

         // Also check if there's a submission with approved status
         if (!$isCompleted) {
            $submissions = Proposal::proposal_checklist_submissions(
               array('proposalChecklistItemAssignmentID' => $assignment->proposalChecklistItemAssignmentID, 'submissionStatus' => 'approved', 'Suspended' => 'N'),
               false,
               $DBConn
            );
            if ($submissions && !empty($submissions)) {
               $isCompleted = true;
            }
         }

         if ($isCompleted) {
            $completedItems++;
            if ($isMandatory) {
               $completedMandatory++;
            }
         }
      }

      $totalPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0;
      $mandatoryPercentage = $mandatoryItems > 0 ? round(($completedMandatory / $mandatoryItems) * 100, 2) : ($mandatoryItems == 0 ? 100 : 0);

      return array(
         'total' => $totalPercentage,
         'mandatory' => $mandatoryPercentage,
         'totalItems' => $totalItems,
         'completedItems' => $completedItems,
         'mandatoryItems' => $mandatoryItems,
         'completedMandatory' => $completedMandatory
      );
   }

   /**
    * Update proposal completion percentages
    * @param int $proposalID
    * @param object $DBConn
    * @return bool - Success status
    */
   public static function update_proposal_completion($proposalID, $DBConn) {
      $completion = self::calculate_proposal_completion($proposalID, $DBConn);

      $changes = array(
         'completionPercentage' => $completion['total'],
         'mandatoryCompletionPercentage' => $completion['mandatory'],
         'LastUpdate' => 'NOW()'
      );

      return $DBConn->update_table('tija_proposals', $changes, array('proposalID' => $proposalID));
   }

   /**
    * Get proposal checklist item submissions
    * @param array $whereArr - WHERE conditions
    * @param bool $single - Return single record or multiple
    * @param object $DBConn - Database connection
    * @return mixed - Array of records or single record or false
    */
   public static function proposal_checklist_submissions($whereArr, $single, $DBConn) {
      $params = array();
      $where = '';

      $submissionArray = array(
         'submissionID',
         'proposalChecklistItemAssignmentID',
         'submittedBy',
         'submissionDate',
         'submissionStatus',
         'submissionNotes',
         'reviewedBy',
         'reviewedDate',
         'reviewNotes',
         'orgDataID',
         'entityID',
         'DateAdded',
         'LastUpdate',
         'Suspended'
      );

      if (count($whereArr) > 0) {
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if (in_array($col, $submissionArray)) {
               $where .= "s.{$col} = ?";
            } else {
               continue;
            }
            $params[] = array($val, 's');
         }
      }

      $sql = "SELECT
         s.submissionID,
         s.proposalChecklistItemAssignmentID,
         s.submittedBy,
         s.submissionDate,
         s.submissionStatus,
         s.submissionNotes,
         s.reviewedBy,
         s.reviewedDate,
         s.reviewNotes,
         s.submissionFiles,
         s.orgDataID,
         s.entityID,
         s.DateAdded,
         s.LastUpdate,
         s.Suspended,
         CONCAT(p1.FirstName, ' ', p1.Surname) as submittedByName,
         CONCAT(p2.FirstName, ' ', p2.Surname) as reviewedByName,
         a.proposalChecklistItemAssignmentID,
         a.proposalID,
         a.proposalChecklistID,
         i.proposalChecklistItemName,
         i.proposalChecklistItemDescription
      FROM tija_proposal_checklist_item_submissions s
      LEFT JOIN people p1 ON s.submittedBy = p1.ID
      LEFT JOIN people p2 ON s.reviewedBy = p2.ID
      LEFT JOIN tija_proposal_checklist_item_assignment a ON s.proposalChecklistItemAssignmentID = a.proposalChecklistItemAssignmentID
      LEFT JOIN tija_proposal_checklist_items i ON a.proposalChecklistItemID = i.proposalChecklistItemID
      {$where}
      ORDER BY s.submissionDate DESC";

      $rows = $DBConn->fetch_all_rows($sql, $params);
      return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

}