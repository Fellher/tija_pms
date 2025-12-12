<?Php
/**
!Sales class
 */

 class Sales {
   public static function sales_status_levels($whereArr, $single, $DBConn) {
      $cols= array(
         'saleStatusLevelID',
         'DateAdded',
         'statusLevel',
         'statusOrder',
         'StatusLevelDescription',
         'orgDataID',
         'entityID',
         'levelPercentage',
         'previousLevelID',
         'closeLevel',
         'LastUpdate',
         'LastUpdatedByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_sales_status_levels', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }
   public static function lead_sources($whereArr, $single, $DBConn) {
      $cols = array('leadSourceID', 'DateAdded', 'leadSourceName', 'leadSourceDescription', 'orgDataID', 'entityID', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_lead_sources', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function sales_cases($whereArr, $single, $DBConn) {
      $cols= array(
         'salesCaseID',
         'DateAdded',
         'salesCaseName',
         'clientID',
         'salesCaseContactID',
         'orgDataID',
         'entityID',
         'businessUnitID',
         'salesPersonID',
         'saleStatusLevelID',
         'salesCaseEstimate',
         'probability',
         'expectedCloseDate',
         'dateClosed',
         'closeStatus',
         'leadSourceID',
         'saleStage',
         'LastUpdate',
         'projectID',
         'LastUpdatedByID',
         'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_sales_cases', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }
   public static function sales_case_mid($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $salesArray = array(
         'salesCaseID',
         'DateAdded',
         'salesCaseName',
         'clientID',
         'salesCaseContactID',
         'orgDataID',
         'entityID',
         'businessUnitID',
         'salesPersonID',
         'saleStatusLevelID',
         'saleStage',
         'salesCaseEstimate',
         'probability',
         'expectedCloseDate',
         'dateClosed',
         'closeStatus',
         'leadSourceID',
         'LastUpdate',
         'projectID',
         'LastUpdatedByID',
         'Lapsed',
         'Suspended',
         'salesProgressID',
      );
      $clientArray = array(
         'clientName',
         'clientCode',
         'accountOwnerID',
         'vatNumber',
         'clientDescription'
      );
      $businessUnitArray = array(
         'businessUnitName',
         'businessUnitDescription'
      );
      $salesStatusArray = array(

         'statusLevel',
         'statusOrder',
         'StatusLevelDescription',
         'levelPercentage'

      );




      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }

            if (in_array($col, $salesArray)) {
               $where .= "c.{$col} = ?";
            } elseif (in_array($col, $clientArray)) {
               $where .= "cl.{$col} = ?";
            } elseif (in_array($col, $businessUnitArray)) {
               $where .= "bu.{$col} = ?";
            } elseif (in_array($col, $salesStatusArray)) {
               $where .= "sl.{$col} = ?";
            } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
            }
            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql= "SELECT
         c.salesCaseID, c.DateAdded, c.salesCaseName, c.clientID,  c.salesCaseContactID, c.orgDataID, c.entityID, c.businessUnitID, c.salesPersonID, c.saleStatusLevelID, c.saleStage, c.salesCaseEstimate, c.probability, c.expectedCloseDate, c.dateClosed, c.closeStatus, c.leadSourceID, c.LastUpdate, c.projectID, c.LastUpdatedByID, c.Lapsed, c.Suspended, c.salesProgressID,
         cl.clientName, cl.clientCode, cl.accountOwnerID, cl.vatNumber, cl.clientDescription,
         bu.businessUnitName, bu.businessUnitDescription,
         sl.statusLevel, sl.statusOrder, sl.StatusLevelDescription, sl.levelPercentage,





          u.FirstName as salesPersonFirstName,
         u.Surname as salesPersonLastName,
          CONCAT(u.FirstName, ' ', u.Surname) as salesPersonName


      FROM tija_sales_cases c
      LEFT JOIN tija_clients cl ON c.clientID = cl.clientID
      LEFT JOIN tija_business_units bu ON c.businessUnitID = bu.businessUnitID
      LEFT JOIN tija_sales_status_levels sl ON c.saleStatusLevelID = sl.saleStatusLevelID
      LEFT JOIN tija_client_contacts cc ON c.salesCaseContactID = cc.clientContactID

   --
      LEFT JOIN  people  u ON c.salesPersonID = u.ID



      {$where}";
       $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function sales_case_full($whereArr, $single, $DBConn) {
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
            $where .= "c.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql= "SELECT
         c.salesCaseID, c.DateAdded, c.salesCaseName, c.clientID,  c.salesCaseContactID, c.orgDataID, c.entityID, c.businessUnitID, c.salesPersonID, c.saleStatusLevelID, c.saleStage, c.salesCaseEstimate, c.probability, c.expectedCloseDate, c.dateClosed, c.closeStatus, c.leadSourceID, c.LastUpdate, c.projectID, c.LastUpdatedByID, c.Lapsed, c.Suspended,
         cl.clientName, cl.clientCode, cl.accountOwnerID, cl.vatNumber, cl.clientDescription,
         bu.businessUnitName, bu.businessUnitDescription,
         sl.statusLevel, sl.statusOrder, sl.StatusLevelDescription, sl.levelPercentage,
         sa.salesActivityID, sa.DateAdded, sa.activityTypeID, sa.salesActivityDate, sa.activityTime, sa.activityDescription, sa.salesCaseID, sa.orgDataID, sa.entityID, sa.activityName, sa.activityOwnerID, sa.clientID, sa.activityCategory, sa.salesPersonID,
         sa.activityStatus, sa.activityDeadline, sa.activityStartDate, sa.activityCloseDate, sa.activityCloseStatus, sa.ActivityNotes, sa.LastUpdate, sa.LastUpdatedByID, sa.Lapsed, sa.Suspended,

          u.FirstName as salesPersonFirstName,
         u.Surname as salesPersonLastName,
          CONCAT(u.FirstName, ' ', u.Surname) as salesPersonName


      FROM tija_sales_cases c
      LEFT JOIN tija_clients cl ON c.clientID = cl.clientID
      LEFT JOIN tija_business_units bu ON c.businessUnitID = bu.businessUnitID
      LEFT JOIN tija_sales_status_levels sl ON c.saleStatusLevelID = sl.saleStatusLevelID
      LEFT JOIN tija_sales_activities sa ON c.salesCaseID = sa.salesCaseID
     LEFT JOIN  people  u ON c.salesPersonID = u.ID



      {$where}";
       $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function tija_activity_types($whereArr, $single, $DBConn) {
      $cols= array('activityTypeID', 'DateAdded', 'activityTypeName', 'iconlink', 'activityTypeDescription',  'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_activity_types', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function tija_sales_activities($whereArr, $single, $DBConn) {
      $cols= array(
         'salesActivityID',
         'DateAdded',
         'activityTypeID',
         'salesActivityDate',
         'activityTime',
         'activityDescription',
         'salesCaseID',
         'orgDataID',
         'entityID',
         'activityName',
         'activityOwnerID',
         'clientID',
         'activityCategory',
         'salesPersonID',
         'activityStatus',
         'activityDeadline',
         'activityStartDate',
         'activityCloseDate',
         'activityCloseStatus',
         'ActivityNotes',
         'LastUpdate',
         'LastUpdatedByID',
         'Lapsed',
         'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_sales_activities', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function tija_activity_categories($whereArr, $single, $DBConn) {
      $cols= array('activityCategoryID', 'DateAdded', 'activityCategoryName', 'activityCategoryDescription', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_activity_categories', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

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
      $proposalArr = array(
         'proposalID',
         'DateAdded',
         'proposalTitle',
         'proposalCode',
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
      $clientArr = array(
         'clientName',
         'clientCode'
      );
      $salesCaseArr = array(
         'salesCaseName'
      );
      $proposalStatusArr = array(
         'proposalStatusName'
      );
      $employeeArr = array(
         'FirstName',
         'Surname'
      );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $proposalArr)) {
               $where .= "p.{$col} = ?";
             } elseif (in_array($col, $clientArr)) {
               $where .= "c.{$col} = ?";
             } elseif (in_array($col, $salesCaseArr)) {
               $where .= "s.{$col} = ?";
             } elseif (in_array($col, $proposalStatusArr)) {
               $where .= "ps.{$col} = ?";
             } elseif (in_array($col, $employeeArr)) {
               $where .= "u.{$col} = ?";
             } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
             }

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
         u.userInitials as employeeInitials,
         CONCAT(u.FirstName, ' ', u.Surname) as employeeName

      FROM tija_proposals p
      LEFT JOIN tija_clients c ON p.clientID = c.clientID
      LEFT JOIN tija_sales_cases s ON p.salesCaseID = s.salesCaseID
      LEFT JOIN tija_proposal_statuses ps ON p.proposalStatusID = ps.proposalStatusID
      LEFT JOIN people u ON p.employeeID = u.ID

      {$where}
      ORDER BY p.proposalDeadline DESC, p.DateAdded DESC
      ";
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
         u.checklistItemID, u.DateAdded, u.checklistItemName, u.checklistItemDescription, u.orgDataID, u.entityID, u.LastUpdate, u.LastUpdateByID, u.Lapsed, u.Suspended,
         c.checklistItemCategoryName,
         c.checklistItemCategoryDescription,
         CONCAT(p.FirstName, ' ', p.Surname) as LastUpdatedByName
         FROM tija_proposal_checklist_items u
         LEFT JOIN tija_proposal_checklist_item_categories c ON u.checklistItemCategoryID = c.checklistItemCategoryID
         LEFT JOIN people p ON u.LastUpdateByID = p.ID
         {$where}
         ORDER BY u.DateAdded ASC, u.checklistItemName ASC";
         $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   // !check this out to confirm
   public static function proposal_checklist($whereArr, $single, $DBConn) {
      $cols= array(
         'proposalChecklistID',
         'DateAdded',
         'proposalID',
         'checklistItemID',
         'proposalChecklistStatusID',
         'checklistDescription',
         'checklistComments',
         'assignedEmployeeID',
         'entityID',
         'orgDataID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_proposal_checklist', $cols, $whereArr);
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

   // prospects
   public static function sales_prospects($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $prospects= array(
         'salesProspectID',
         'DateAdded',
         'salesProspectName',
         'isClient',
         'clientID',
         'prospectCaseName',
         'address',
         'prospectEmail',
         'estimatedValue',
         'probability',
         'entityID',
         'orgDataID',
         'businessUnitID',
         'leadSourceID',
         'salesProspectStatus',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended'
      );

      $prospectStatus = array('salesProspectStatusID', 'salesProspectStatusName', 'salesProspectStatusDescription' );
      $clients = array('clientID', 'clientName', 'clientCode', 'accountOwnerID', 'vatNumber', 'clientDescription' );
      $businessUnits = array('businessUnitID', 'businessUnitName', 'businessUnitDescription' );
      $leadSources = array('leadSourceID', 'leadSourceName', 'leadSourceDescription' );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $prospects)) {
               $where .= "u.{$col} = ?";
             } elseif (in_array($col, $prospectStatus)) {
               $where .= "s.{$col} = ?";
             } elseif (in_array($col, $clients)) {
               $where .= "c.{$col} = ?";
             } elseif (in_array($col, $businessUnits)) {
               $where .= "b.{$col} = ?";
             } elseif (in_array($col, $leadSources)) {
               $where .= "l.{$col} = ?";
             } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
             }

            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql= "SELECT
         u.salesProspectID, u.DateAdded, u.salesProspectName, u.isClient, u.clientID, u.prospectCaseName, u.address, u.prospectEmail, u.estimatedValue, u.probability,
         u.entityID, u.orgDataID, u.businessUnitID, u.leadSourceID, u.salesProspectStatus,
         c.clientName, c.clientCode,
         b.businessUnitName,
         l.leadSourceName,

         CONCAT(p.FirstName, ' ', p.Surname) as LastUpdatedByName
         FROM tija_sales_prospects u

         LEFT JOIN tija_clients c ON u.clientID = c.clientID
         LEFT JOIN tija_business_units b ON u.businessUnitID = b.businessUnitID
         LEFT JOIN tija_lead_sources l ON u.leadSourceID = l.leadSourceID
         LEFT JOIN people p ON u.LastUpdateByID = p.ID
         {$where}
         ORDER BY u.DateAdded ASC, u.salesProspectName ASC
      ";
         $rows = $DBConn->fetch_all_rows($sql,$params);

      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function sales_progress ($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $salesProgress= array(
         'salesProgressID',
         'DateAdded',
         'salesCaseID',
         'saleStatusLevelID',
         'progressPercentage',
         'progressDescription',
         'orgDataID',
         'entityID',
         'Lapsed',
         'Suspended'
      );
      $salesStatusLevels = array(
         'statusLevel',
         'statusOrder',
         'StatusLevelDescription',
         'levelPercentage'
      );
      $salesPersons = array('salesPersonID', 'FirstName', 'Surname' );
      $salesCases = array('salesCaseID', 'salesCaseName');
     $clientArray = array('clientID', 'clientName', 'clientCode', 'accountOwnerID', 'vatNumber', 'clientDescription' );
     $businessUnitArray = array('businessUnitID', 'businessUnitName', 'businessUnitDescription' );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
             if (in_array($col, $salesProgress)) {
               $where .= "u.{$col} = ?";
             } elseif (in_array($col, $salesStatusLevels)) {
               $where .= "sl.{$col} = ?";
             } elseif (in_array($col, $salesPersons)) {
               $where .= "p.{$col} = ?";
             } elseif (in_array($col, $salesCases)) {
               $where .= "s.{$col} = ?";
             } elseif (in_array($col, $clientArray)) {
               $where .= "c.{$col} = ?";
             } elseif (in_array($col, $businessUnitArray)) {
               $where .= "bu.{$col} = ?";
             } else {
               // If the column is not found in any of the tables, you can choose to skip it or handle it differently
               continue; // Skip this column
             }

            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql= "SELECT
         u.salesProgressID, u.DateAdded, u.salesCaseID, u.salesPersonID, u.saleStatusLevelID, u.progressPercentage, u.progressDescription, u.orgDataID, u.entityID,  u.Lapsed, u.Suspended, u.clientID, u.businessUnitID,
         sl.statusLevel, sl.statusOrder, sl.StatusLevelDescription, sl.levelPercentage,
         CONCAT(p.FirstName, ' ', p.Surname) as salesPersonName,
         c.clientName, c.clientCode, c.accountOwnerID, c.vatNumber, c.clientDescription,
         s.salesCaseName, s.businessUnitID,
         bu.businessUnitName, bu.businessUnitDescription


      FROM tija_sales_progress u
      LEFT JOIN people p ON u.salesPersonID = p.ID
      LEFT JOIN tija_sales_status_levels sl ON u.saleStatusLevelID = sl.saleStatusLevelID
      LEFT JOIN tija_sales_cases s ON u.salesCaseID = s.salesCaseID
      LEFT JOIN tija_clients c ON u.clientID = c.clientID
      LEFT JOIN tija_business_units bu ON u.businessUnitID = bu.businessUnitID

      {$where}
      ORDER BY u.DateAdded ASC";
       $rows = $DBConn->fetch_all_rows($sql,$params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get sales documents
    * @param array $whereArr - WHERE conditions
    * @param bool $single - Return single record or multiple
    * @param object $DBConn - Database connection
    * @return mixed - Array of records or single record or false
    */
   public static function sales_documents($whereArr, $single, $DBConn) {
      $params = array();
      $where = '';
      $rows = array();

      $documentArray = array(
         'documentID',
         'DateAdded',
         'documentName',
         'fileName',
         'fileOriginalName',
         'fileURL',
         'fileType',
         'fileSize',
         'fileMimeType',
         'documentCategory',
         'documentType',
         'version',
         'uploadedBy',
         'description',
         'expenseID',
         'isConfidential',
         'isPublic',
         'requiresApproval',
         'approvalStatus',
         'approvedBy',
         'approvedDate',
         'downloadCount',
         'LastUpdate',
         'LastUpdatedByID',
         'Suspended',
         'salesStage',
         'saleStatusLevelID',
         'documentStage',
         'tags',
         'expiryDate',
         'linkedActivityID',
         'sharedWithClient',
         'sharedDate',
         'viewCount',
         'lastAccessedDate'
      );

      $salesCaseArray = array('salesCaseID', 'salesCaseName');
      $proposalArray = array('proposalID', 'proposalTitle');
      $userArray = array('uploadedByName', 'approvedByName');

      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }

            if (in_array($col, $documentArray)) {
               $where .= "sd.{$col} = ?";
            } elseif (in_array($col, $salesCaseArray)) {
               $where .= "sc.{$col} = ?";
            } elseif (in_array($col, $proposalArray)) {
               $where .= "p.{$col} = ?";
            } else {
               continue;
            }

            $params[] = array($val, 's');
            $i++;
         }
      }

      $sql = "SELECT
         sd.documentID,
         sd.salesCaseID,
         sd.proposalID,
         sd.documentName,
         sd.fileName,
         sd.fileOriginalName,
         sd.fileURL,
         sd.fileType,
         sd.fileSize,
         sd.fileMimeType,
         sd.documentCategory,
         sd.documentType,
         sd.version,
         sd.uploadedBy,
         sd.description,
         sd.expenseID,
         sd.isConfidential,
         sd.isPublic,
         sd.requiresApproval,
         sd.approvalStatus,
         sd.approvedBy,
         sd.approvedDate,
         sd.downloadCount,
         sd.DateAdded,
         sd.LastUpdate,
         sd.LastUpdatedByID,
         sd.Suspended,
         sd.salesStage,
         sd.saleStatusLevelID,
         sd.documentStage,
         sd.tags,
         sd.expiryDate,
         sd.linkedActivityID,
         sd.sharedWithClient,
         sd.sharedDate,
         sd.viewCount,
         sd.lastAccessedDate,
         sc.salesCaseName,
         p.proposalTitle,
         CONCAT(u1.FirstName, ' ', u1.Surname) as uploadedByName,
         CONCAT(u2.FirstName, ' ', u2.Surname) as approvedByName
      FROM tija_sales_documents sd
      LEFT JOIN tija_sales_cases sc ON sd.salesCaseID = sc.salesCaseID
      LEFT JOIN tija_proposals p ON sd.proposalID = p.proposalID
      LEFT JOIN people u1 ON sd.uploadedBy = u1.ID
      LEFT JOIN people u2 ON sd.approvedBy = u2.ID
      {$where}
      ORDER BY sd.DateAdded DESC";

      $rows = $DBConn->fetch_all_rows($sql, $params);
      return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get sales document by ID
    * @param int $documentID
    * @param object $DBConn
    * @return mixed - Document object or false
    */
   public static function sales_document_by_id($documentID, $DBConn) {
      return self::sales_documents(array('documentID' => $documentID), true, $DBConn);
   }

   /**
    * Get documents by sales case
    * @param int $salesCaseID
    * @param object $DBConn
    * @return mixed - Array of documents or false
    */
   public static function sales_documents_by_case($salesCaseID, $DBConn) {
      return self::sales_documents(array('salesCaseID' => $salesCaseID, 'Suspended' => 'N'), false, $DBConn);
   }

   /**
    * Get documents by category
    * @param int $salesCaseID
    * @param string $category
    * @param object $DBConn
    * @return mixed - Array of documents or false
    */
   public static function sales_documents_by_category($salesCaseID, $category, $DBConn) {
      return self::sales_documents(array('salesCaseID' => $salesCaseID, 'documentCategory' => $category, 'Suspended' => 'N'), false, $DBConn);
   }

   // ============================================================================
   // ENHANCED PROSPECT MANAGEMENT METHODS
   // ============================================================================

   /**
    * Get prospects with advanced filtering and pagination
    * @param array $filters - Complex filter array
    * @param array $pagination - Pagination parameters (limit, offset, orderBy, orderDir)
    * @param object $DBConn - Database connection
    * @return mixed - Array with 'data' and 'total' or false
    */
   public static function sales_prospects_advanced($filters = array(), $pagination = array(), $DBConn) {
      $params = array();
      $where = '';
      $having = '';

      // Build WHERE clause from filters
      $conditions = array();

      if (!empty($filters['orgDataID'])) {
         $conditions[] = "p.orgDataID = ?";
         $params[] = array($filters['orgDataID'], 'i');
      }

      if (!empty($filters['entityID'])) {
         $conditions[] = "p.entityID = ?";
         $params[] = array($filters['entityID'], 'i');
      }

      if (!empty($filters['businessUnitID'])) {
         $conditions[] = "p.businessUnitID = ?";
         $params[] = array($filters['businessUnitID'], 'i');
      }

      if (!empty($filters['leadSourceID'])) {
         $conditions[] = "p.leadSourceID = ?";
         $params[] = array($filters['leadSourceID'], 'i');
      }

      if (!empty($filters['salesProspectStatus'])) {
         $conditions[] = "p.salesProspectStatus = ?";
         $params[] = array($filters['salesProspectStatus'], 's');
      }

      if (!empty($filters['leadQualificationStatus'])) {
         $conditions[] = "p.leadQualificationStatus = ?";
         $params[] = array($filters['leadQualificationStatus'], 's');
      }

      if (!empty($filters['assignedTeamID'])) {
         $conditions[] = "p.assignedTeamID = ?";
         $params[] = array($filters['assignedTeamID'], 'i');
      }

      if (!empty($filters['territoryID'])) {
         $conditions[] = "p.territoryID = ?";
         $params[] = array($filters['territoryID'], 'i');
      }

      if (!empty($filters['industryID'])) {
         $conditions[] = "p.industryID = ?";
         $params[] = array($filters['industryID'], 'i');
      }

      if (!empty($filters['companySize'])) {
         $conditions[] = "p.companySize = ?";
         $params[] = array($filters['companySize'], 's');
      }

      if (!empty($filters['ownerID'])) {
         $conditions[] = "p.ownerID = ?";
         $params[] = array($filters['ownerID'], 'i');
      }

      // Value range filter
      if (isset($filters['minValue']) && $filters['minValue'] !== '') {
         $conditions[] = "p.estimatedValue >= ?";
         $params[] = array($filters['minValue'], 'd');
      }

      if (isset($filters['maxValue']) && $filters['maxValue'] !== '') {
         $conditions[] = "p.estimatedValue <= ?";
         $params[] = array($filters['maxValue'], 'd');
      }

      // Lead score range
      if (isset($filters['minScore']) && $filters['minScore'] !== '') {
         $conditions[] = "p.leadScore >= ?";
         $params[] = array($filters['minScore'], 'i');
      }

      if (isset($filters['maxScore']) && $filters['maxScore'] !== '') {
         $conditions[] = "p.leadScore <= ?";
         $params[] = array($filters['maxScore'], 'i');
      }

      // Date filters
      if (!empty($filters['dateAddedFrom'])) {
         $conditions[] = "DATE(p.DateAdded) >= ?";
         $params[] = array($filters['dateAddedFrom'], 's');
      }

      if (!empty($filters['dateAddedTo'])) {
         $conditions[] = "DATE(p.DateAdded) <= ?";
         $params[] = array($filters['dateAddedTo'], 's');
      }

      if (!empty($filters['lastContactFrom'])) {
         $conditions[] = "DATE(p.lastContactDate) >= ?";
         $params[] = array($filters['lastContactFrom'], 's');
      }

      if (!empty($filters['lastContactTo'])) {
         $conditions[] = "DATE(p.lastContactDate) <= ?";
         $params[] = array($filters['lastContactTo'], 's');
      }

      // Search filter
      if (!empty($filters['search'])) {
         $searchTerm = '%' . $filters['search'] . '%';
         $conditions[] = "(p.salesProspectName LIKE ? OR p.prospectEmail LIKE ? OR p.prospectCaseName LIKE ?)";
         $params[] = array($searchTerm, 's');
         $params[] = array($searchTerm, 's');
         $params[] = array($searchTerm, 's');
      }

      // Default filters
      $conditions[] = "p.Suspended = 'N'";
      $conditions[] = "p.Lapsed = 'N'";

      if (count($conditions) > 0) {
         $where = "WHERE " . implode(" AND ", $conditions);
      }

      // Build ORDER BY clause
      $orderBy = !empty($pagination['orderBy']) ? $pagination['orderBy'] : 'p.DateAdded';
      $orderDir = !empty($pagination['orderDir']) ? $pagination['orderDir'] : 'DESC';
      $limit = !empty($pagination['limit']) ? (int)$pagination['limit'] : 50;
      $offset = !empty($pagination['offset']) ? (int)$pagination['offset'] : 0;

      // Get total count
      $countSql = "SELECT COUNT(*) as total FROM tija_sales_prospects p {$where}";
      $countResult = $DBConn->fetch_all_rows($countSql, $params);
      $total = $countResult[0]->total ?? 0;

      // Get data
      $sql = "SELECT
         p.salesProspectID, p.DateAdded, p.salesProspectName, p.isClient, p.clientID,
         p.prospectCaseName, p.address, p.prospectEmail, p.prospectPhone, p.prospectWebsite,
         p.estimatedValue, p.probability, p.salesProspectStatus, p.leadScore,
         p.leadQualificationStatus, p.companySize, p.expectedCloseDate, p.lastContactDate,
         p.nextFollowUpDate, p.budgetConfirmed, p.decisionMakerIdentified, p.timelineDefined,
         p.needIdentified, p.tags, p.entityID, p.orgDataID, p.businessUnitID, p.leadSourceID,
         p.assignedTeamID, p.territoryID, p.industryID, p.ownerID,
         c.clientName, c.clientCode,
         bu.businessUnitName,
         ls.leadSourceName,
         t.teamName, t.teamCode,
         ter.territoryName,
         ind.industryName,
         CONCAT(u.FirstName, ' ', u.Surname) as ownerName,
         CONCAT(lup.FirstName, ' ', lup.Surname) as lastUpdatedByName,
         DATEDIFF(CURRENT_DATE, p.DateAdded) as daysInPipeline,
         CASE
            WHEN p.nextFollowUpDate < CURRENT_DATE THEN 'Overdue'
            WHEN p.nextFollowUpDate = CURRENT_DATE THEN 'Due Today'
            WHEN p.nextFollowUpDate IS NULL THEN 'Not Scheduled'
            ELSE 'Scheduled'
         END as followUpStatus
      FROM tija_sales_prospects p
      LEFT JOIN tija_clients c ON p.clientID = c.clientID
      LEFT JOIN tija_business_units bu ON p.businessUnitID = bu.businessUnitID
      LEFT JOIN tija_lead_sources ls ON p.leadSourceID = ls.leadSourceID
      LEFT JOIN tija_prospect_teams t ON p.assignedTeamID = t.prospectTeamID
      LEFT JOIN tija_prospect_territories ter ON p.territoryID = ter.territoryID
      LEFT JOIN tija_prospect_industries ind ON p.industryID = ind.industryID
      LEFT JOIN people u ON p.ownerID = u.ID
      LEFT JOIN people lup ON p.LastUpdateByID = lup.ID
      {$where}
      ORDER BY {$orderBy} {$orderDir}
      LIMIT {$limit} OFFSET {$offset}";

      $rows = $DBConn->fetch_all_rows($sql, $params);

      return array(
         'data' => $rows ? $rows : array(),
         'total' => $total,
         'limit' => $limit,
         'offset' => $offset
      );
   }

   /**
    * Get complete prospect details with all related data
    * @param int $prospectID
    * @param object $DBConn
    * @return mixed - Prospect object or false
    */
   public static function sales_prospect_full($prospectID, $DBConn) {
      $params = array(array($prospectID, 'i'));

      $sql = "SELECT
         p.*,
         c.clientName, c.clientCode,
         bu.businessUnitName,
         ls.leadSourceName,
         t.prospectTeamName, t.prospectTeamCode, t.prospectTeamManagerID,
         ter.territoryName, ter.territoryCode,
         ind.industryName, ind.industryCode,
         CONCAT(u.FirstName, ' ', u.Surname) as ownerName,
         u.Email as ownerEmail,
         CONCAT(tm.FirstName, ' ', tm.Surname) as teamManagerName,
         CONCAT(lup.FirstName, ' ', lup.Surname) as lastUpdatedByName,
         DATEDIFF(CURRENT_DATE, p.DateAdded) as daysInPipeline,
         (SELECT COUNT(*) FROM tija_prospect_interactions WHERE salesProspectID = p.salesProspectID AND Suspended = 'N') as interactionCount,
         (SELECT MAX(interactionDate) FROM tija_prospect_interactions WHERE salesProspectID = p.salesProspectID AND Suspended = 'N') as lastInteractionDate
      FROM tija_sales_prospects p
      LEFT JOIN tija_clients c ON p.clientID = c.clientID
      LEFT JOIN tija_business_units bu ON p.businessUnitID = bu.businessUnitID
      LEFT JOIN tija_lead_sources ls ON p.leadSourceID = ls.leadSourceID
      LEFT JOIN tija_prospect_teams t ON p.assignedTeamID = t.prospectTeamID
      LEFT JOIN tija_prospect_territories ter ON p.territoryID = ter.territoryID
      LEFT JOIN tija_prospect_industries ind ON p.industryID = ind.industryID
      LEFT JOIN people u ON p.ownerID = u.ID
      LEFT JOIN people tm ON t.prospectTeamManagerID = tm.ID
      LEFT JOIN people lup ON p.LastUpdateByID = lup.ID
      WHERE p.salesProspectID = ? AND p.Suspended = 'N' AND p.Lapsed = 'N'";

      $rows = $DBConn->fetch_all_rows($sql, $params);
      return (is_array($rows) && count($rows) === 1) ? $rows[0] : false;
   }

   /**
    * Get prospect teams
    * @param array $whereArr
    * @param bool $single
    * @param object $DBConn
    * @return mixed
    */
   public static function prospect_teams($whereArr, $single, $DBConn) {
      $cols = array('prospectTeamID', 'DateAdded', 'prospectTeamName', 'prospectTeamCode', 'teamDescription', 'prospectTeamManagerID',
              'orgDataID', 'entityID', 'territoryID', 'isActive', 'LastUpdate', 'LastUpdatedByID',
              'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_prospect_teams', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get prospect territories
    * @param array $whereArr
    * @param bool $single
    * @param object $DBConn
    * @return mixed
    */
   public static function prospect_territories($whereArr, $single, $DBConn) {
      $cols = array('territoryID', 'DateAdded', 'territoryName', 'territoryCode', 'territoryDescription',
                    'territoryType', 'parentTerritoryID', 'orgDataID', 'entityID', 'countryCode',
                    'regionName', 'cityName', 'isActive', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_prospect_territories', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get prospect industries
    * @param array $whereArr
    * @param bool $single
    * @param object $DBConn
    * @return mixed
    */
   public static function prospect_industries($whereArr, $single, $DBConn) {
      $cols = array('industryID', 'DateAdded', 'industryName', 'industryCode', 'industryDescription',
                    'parentIndustryID', 'industryLevel', 'isActive', 'LastUpdate', 'LastUpdatedByID',
                    'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_prospect_industries', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get prospect interactions
    * @param int $prospectID
    * @param array $filters
    * @param object $DBConn
    * @return mixed
    */
   public static function prospect_interactions($prospectID, $filters = array(), $DBConn) {
      $params = array(array($prospectID, 'i'));
      $where = "WHERE i.salesProspectID = ? AND i.Suspended = 'N'";

      if (!empty($filters['interactionType'])) {
         $where .= " AND i.interactionType = ?";
         $params[] = array($filters['interactionType'], 's');
      }

      if (!empty($filters['dateFrom'])) {
         $where .= " AND DATE(i.interactionDate) >= ?";
         $params[] = array($filters['dateFrom'], 's');
      }

      if (!empty($filters['dateTo'])) {
         $where .= " AND DATE(i.interactionDate) <= ?";
         $params[] = array($filters['dateTo'], 's');
      }

      $sql = "SELECT
         i.*,
         CONCAT(u.FirstName, ' ', u.Surname) as userName,
         u.Email as userEmail
      FROM tija_prospect_interactions i
      LEFT JOIN people u ON i.userID = u.ID
      {$where}
      ORDER BY i.interactionDate DESC, i.DateAdded DESC";

      return $DBConn->fetch_all_rows($sql, $params);
   }

   /**
    * Calculate lead score for a prospect
    * @param int $prospectID
    * @param object $DBConn
    * @return int - Calculated score (0-100)
    */
   public static function calculate_lead_score($prospectID, $DBConn) {
      // Get prospect details
      $prospect = self::sales_prospect_full($prospectID, $DBConn);
      if (!$prospect) return 0;

      // Get scoring rules for this org/entity
      $rules = self::get_lead_scoring_rules($prospect->orgDataID, $prospect->entityID, $DBConn);
      if (!$rules) return 0;

      $totalScore = 0;
      $maxScore = 100;

      foreach ($rules as $rule) {
         $points = 0;
         $fieldValue = $prospect->{$rule->ruleField} ?? null;

         switch ($rule->ruleCondition) {
            case 'equals':
               if ($fieldValue == $rule->ruleValue) {
                  $points = $rule->scorePoints;
               }
               break;
            case 'greater_than':
               if (is_numeric($fieldValue) && $fieldValue > $rule->ruleValue) {
                  $points = $rule->scorePoints;
               }
               break;
            case 'less_than':
               if (is_numeric($fieldValue) && $fieldValue < $rule->ruleValue) {
                  $points = $rule->scorePoints;
               }
               break;
            case 'between':
               $range = explode(',', $rule->ruleValue);
               if (count($range) == 2 && is_numeric($fieldValue)) {
                  if ($fieldValue >= $range[0] && $fieldValue <= $range[1]) {
                     $points = $rule->scorePoints;
                  }
               }
               break;
            case 'in':
               $values = explode(',', $rule->ruleValue);
               if (in_array($fieldValue, $values)) {
                  $points = $rule->scorePoints;
               }
               break;
            case 'within_days':
               if ($fieldValue && strtotime($fieldValue) >= strtotime("-{$rule->ruleValue} days")) {
                  $points = $rule->scorePoints;
               }
               break;
         }

         $totalScore += ($points * $rule->scoreWeight);
      }

      // Normalize to 0-100
      $finalScore = min($maxScore, max(0, round($totalScore)));

      // Update the prospect's lead score using update_table
      $updateData = array('leadScore' => $finalScore);
      $where = array('salesProspectID' => $prospectID);
      $DBConn->update_table('tija_sales_prospects', $updateData, $where);

      return $finalScore;
   }

   /**
    * Get lead scoring rules
    * @param int $orgDataID
    * @param int $entityID
    * @param object $DBConn
    * @return mixed
    */
   public static function get_lead_scoring_rules($orgDataID, $entityID, $DBConn) {
      $params = array(
         array($orgDataID, 'i'),
         array($entityID, 'i')
      );

      $sql = "SELECT * FROM tija_lead_scoring_rules
              WHERE orgDataID = ? AND entityID = ? AND isActive = 'Y' AND Suspended = 'N'
              ORDER BY ruleCategory, scoreWeight DESC";

      return $DBConn->fetch_all_rows($sql, $params);
   }

   /**
    * Get prospect analytics by lead source
    * @param int $orgDataID
    * @param int $entityID
    * @param array $dateRange
    * @param object $DBConn
    * @return mixed
    */
   public static function prospect_analytics_by_source($orgDataID, $entityID, $dateRange = array(), $DBConn) {
      $params = array(
         array($orgDataID, 'i'),
         array($entityID, 'i')
      );

      $dateFilter = '';
      if (!empty($dateRange['from'])) {
         $dateFilter .= " AND DATE(p.DateAdded) >= ?";
         $params[] = array($dateRange['from'], 's');
      }
      if (!empty($dateRange['to'])) {
         $dateFilter .= " AND DATE(p.DateAdded) <= ?";
         $params[] = array($dateRange['to'], 's');
      }

      $sql = "SELECT
         ls.leadSourceID,
         ls.leadSourceName,
         COUNT(p.salesProspectID) as prospectCount,
         SUM(p.estimatedValue) as totalValue,
         AVG(p.estimatedValue) as avgValue,
         AVG(p.leadScore) as avgLeadScore,
         SUM(CASE WHEN p.salesProspectStatus = 'closed' THEN 1 ELSE 0 END) as convertedCount,
         SUM(CASE WHEN p.salesProspectStatus = 'closed' THEN p.estimatedValue ELSE 0 END) as convertedValue,
         ROUND((SUM(CASE WHEN p.salesProspectStatus = 'closed' THEN 1 ELSE 0 END) / COUNT(p.salesProspectID)) * 100, 2) as conversionRate
      FROM tija_lead_sources ls
      LEFT JOIN tija_sales_prospects p ON ls.leadSourceID = p.leadSourceID
         AND p.orgDataID = ? AND p.entityID = ? AND p.Suspended = 'N' {$dateFilter}
      WHERE ls.Suspended = 'N'
      GROUP BY ls.leadSourceID, ls.leadSourceName
      ORDER BY prospectCount DESC";

      return $DBConn->fetch_all_rows($sql, $params);
   }

   /**
    * Get conversion funnel metrics with flexible filtering
    *
    * @param array $whereArr - Dynamic filter conditions (column => value pairs)
    *   Supported columns from tija_sales_prospects (p.):
    *     - salesProspectID, orgDataID, entityID, ownerID, assignedTeamID, businessUnitID
    *     - leadSourceID, territoryID, industryID, clientID
    *     - salesProspectStatus, leadQualificationStatus, isClient
    *     - Suspended, Lapsed, convertedToSale
    *   Supported columns from tija_clients (c.):
    *     - clientName, clientCode
    *   Supported columns from tija_lead_sources (ls.):
    *     - leadSourceName
    *   Supported columns from people (u.):
    *     - FirstName, Surname, Email (owner details)
    * @param array $dateRange - Optional date range array('from' => 'Y-m-d', 'to' => 'Y-m-d')
    * @param object $DBConn - Database connection
    * @param array $options - Additional options:
    *   - 'groupBy' => string - Group by column (default: leadQualificationStatus)
    *   - 'orderBy' => string - Order by clause
    * @return mixed
    */
   public static function prospect_conversion_funnel($whereArr, $dateRange = array(), $DBConn, $options = array()) {
      $params = array();
      $where = '';

      // Define which columns belong to which table
      $prospectCols = array(
         'salesProspectID', 'DateAdded', 'orgDataID', 'entityID', 'ownerID', 'assignedTeamID',
         'businessUnitID', 'leadSourceID', 'territoryID', 'industryID', 'clientID',
         'salesProspectName', 'prospectCaseName', 'prospectEmail', 'prospectPhone',
         'salesProspectStatus', 'leadQualificationStatus', 'leadScore', 'estimatedValue',
         'probability', 'isClient', 'Suspended', 'Lapsed', 'convertedToSale',
         'expectedCloseDate', 'lastContactDate', 'nextFollowUpDate',
         'budgetConfirmed', 'decisionMakerIdentified', 'timelineDefined', 'needIdentified'
      );
      $clientCols = array('clientName', 'clientCode');
      $leadSourceCols = array('leadSourceName');
      $ownerCols = array('FirstName', 'Surname', 'Email');
      $teamCols = array('prospectTeamName', 'teamName');

      // Build WHERE clause dynamically
      if (is_array($whereArr) && count($whereArr) > 0) {
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }

            // Map column to correct table alias
            if (in_array($col, $prospectCols)) {
               $where .= "p.{$col} = ?";
            } elseif (in_array($col, $clientCols)) {
               $where .= "c.{$col} = ?";
            } elseif (in_array($col, $leadSourceCols)) {
               $where .= "ls.{$col} = ?";
            } elseif (in_array($col, $ownerCols)) {
               $where .= "u.{$col} = ?";
            } elseif (in_array($col, $teamCols)) {
               $where .= "t.{$col} = ?";
            } else {
               // Skip unknown columns
               $where = rtrim($where, " AND ");
               $where = rtrim($where, "WHERE ");
               continue;
            }

            // Determine parameter type
            $type = is_int($val) ? 'i' : 's';
            $params[] = array($val, $type);
         }
      }

      // Date range filters
      if (!empty($dateRange['from'])) {
         $where .= ($where == '' ? "WHERE " : " AND ");
         $where .= "DATE(p.DateAdded) >= ?";
         $params[] = array($dateRange['from'], 's');
      }
      if (!empty($dateRange['to'])) {
         $where .= ($where == '' ? "WHERE " : " AND ");
         $where .= "DATE(p.DateAdded) <= ?";
         $params[] = array($dateRange['to'], 's');
      }

      // Group by option (default: leadQualificationStatus)
      $groupBy = !empty($options['groupBy']) ? $options['groupBy'] : 'leadQualificationStatus';

      // Order by option
      $orderBy = !empty($options['orderBy'])
         ? $options['orderBy']
         : "FIELD(p.leadQualificationStatus, 'unqualified', 'cold', 'warm', 'hot', 'qualified')";

      $sql = "SELECT
         p.{$groupBy} as stage,
         COUNT(*) as count,
         SUM(p.estimatedValue) as totalValue,
         AVG(p.estimatedValue) as avgValue,
         AVG(p.leadScore) as avgScore,
         SUM(CASE WHEN p.convertedToSale = 'Y' THEN 1 ELSE 0 END) as convertedCount
      FROM tija_sales_prospects p
      LEFT JOIN tija_clients c ON p.clientID = c.clientID
      LEFT JOIN tija_lead_sources ls ON p.leadSourceID = ls.leadSourceID
      LEFT JOIN people u ON p.ownerID = u.ID
      LEFT JOIN tija_prospect_teams t ON p.assignedTeamID = t.prospectTeamID
      {$where}
      GROUP BY p.{$groupBy}
      ORDER BY {$orderBy}";

      return $DBConn->fetch_all_rows($sql, $params);
   }

   /**
    * Get team performance metrics
    * @param int $teamID
    * @param array $dateRange
    * @param object $DBConn
    * @return mixed
    */
   public static function team_performance_metrics($teamID, $dateRange = array(), $DBConn) {
      $params = array(array($teamID, 'i'));

      $dateFilter = '';
      if (!empty($dateRange['from'])) {
         $dateFilter .= " AND DATE(p.DateAdded) >= ?";
         $params[] = array($dateRange['from'], 's');
      }
      if (!empty($dateRange['to'])) {
         $dateFilter .= " AND DATE(p.DateAdded) <= ?";
         $params[] = array($dateRange['to'], 's');
      }

      $sql = "SELECT
         t.teamID,
         t.teamName,
         COUNT(p.salesProspectID) as totalProspects,
         SUM(CASE WHEN p.salesProspectStatus = 'open' THEN 1 ELSE 0 END) as openProspects,
         SUM(CASE WHEN p.salesProspectStatus = 'closed' THEN 1 ELSE 0 END) as closedProspects,
         SUM(p.estimatedValue) as totalPipelineValue,
         SUM(CASE WHEN p.salesProspectStatus = 'closed' THEN p.estimatedValue ELSE 0 END) as closedValue,
         AVG(p.leadScore) as avgLeadScore,
         AVG(DATEDIFF(CURRENT_DATE, p.DateAdded)) as avgDaysInPipeline,
         ROUND((SUM(CASE WHEN p.salesProspectStatus = 'closed' THEN 1 ELSE 0 END) / COUNT(p.salesProspectID)) * 100, 2) as conversionRate
      FROM tija_prospect_teams t
      LEFT JOIN tija_sales_prospects p ON t.teamID = p.assignedTeamID AND p.Suspended = 'N' {$dateFilter}
      WHERE t.teamID = ? AND t.Suspended = 'N'
      GROUP BY t.teamID, t.teamName";

      return $DBConn->fetch_all_rows($sql, $params);
   }

   /**
    * Get all notes for a prospect
    * @param int $prospectID
    * @param object $DBConn
    * @return array|false
    */
   public static function getProspectNotes($prospectID, $DBConn) {
      $sql = "SELECT
         n.*,
         CONCAT(u.FirstName, ' ', u.Surname) as createdByName,
         u.Email as createdByEmail,
         CONCAT(lu.FirstName, ' ', lu.Surname) as lastUpdatedByName,
         CONCAT(r.FirstName, ' ', r.Surname) as recipientName,
         r.Email as recipientEmail
      FROM tija_prospect_notes n
      LEFT JOIN people u ON n.createdByID = u.ID
      LEFT JOIN people lu ON n.LastUpdatedByID = lu.ID
      LEFT JOIN people r ON n.recipientID = r.ID
      WHERE n.salesProspectID = ? AND n.Suspended = 'N'
      ORDER BY n.DateAdded DESC";

      $params = array(array($prospectID, 'i'));
      $notes = $DBConn->fetch_all_rows($sql, $params);

      return $notes ? $notes : array();
   }

   /**
    * Add a new prospect note
    * @param array $data Note data
    * @param object $userDetails Current user
    * @param object $DBConn Database connection
    * @return array Response with success status and note ID
    */
   public static function addProspectNote($data, $userDetails, $DBConn) {
      $noteData = array(
         'salesProspectID' => (int)$data['salesProspectID'],
         'noteContent' => Utility::clean_string($data['noteContent']),
         'noteType' => isset($data['noteType']) ? Utility::clean_string($data['noteType']) : 'general',
         'isPrivate' => isset($data['isPrivate']) ? Utility::clean_string($data['isPrivate']) : 'N',
         'createdByID' => $userDetails->ID
      );

      // Add recipient if note is private and recipient is specified
      if ($noteData['isPrivate'] === 'Y' && isset($data['recipientID']) && !empty($data['recipientID'])) {
         $noteData['recipientID'] = (int)$data['recipientID'];
      }

      $result = $DBConn->insert_data('tija_prospect_notes', $noteData);

      if ($result) {
         $noteID = $DBConn->lastInsertId();

         // Create in-app notification if note has a recipient
         if (isset($noteData['recipientID']) && $noteData['recipientID']) {
            $notificationData = array(
               'eventID' => 1000, // Prospect Note Received event
               'userID' => $noteData['recipientID'],
               'originatorUserID' => $userDetails->ID,
               'notificationTitle' => 'New Prospect Note',
               'notificationBody' => 'You have received a new note on prospect #' . $noteData['salesProspectID'],
               'notificationLink' => 'html/?s=user&ss=sales&p=prospect_details&prospectID=' . $noteData['salesProspectID'],
               'notificationIcon' => 'ri-sticky-note-line',
               'priority' => $noteData['noteType'] === 'warning' ? 'high' : 'normal',
               'status' => 'unread',
               'segmentType' => 'prospect',
               'segmentID' => $noteData['salesProspectID']
            );

            $DBConn->insert_data('tija_notifications_enhanced', $notificationData);
         }

         return array('success' => true, 'message' => 'Note added successfully.', 'noteID' => $noteID);
      }

      return array('success' => false, 'message' => 'Failed to add note.');
   }

   /**
    * Edit an existing prospect note
    * @param int $noteID Note ID
    * @param array $data Update data
    * @param object $userDetails Current user
    * @param object $DBConn Database connection
    * @return array Response with success status
    */
   public static function editProspectNote($noteID, $data, $userDetails, $DBConn) {
      // Verify user owns the note or is admin
      $note = $DBConn->retrieve_db_table_rows('tija_prospect_notes',
         array('prospectNoteID', 'createdByID'),
         array('prospectNoteID' => $noteID));

      if (!$note || count($note) === 0) {
         return array('success' => false, 'message' => 'Note not found.');
      }

      if ($note[0]->createdByID != $userDetails->ID && $userDetails->userType != 'admin') {
         return array('success' => false, 'message' => 'You do not have permission to edit this note.');
      }

      $updateData = array(
         'noteContent' => Utility::clean_string($data['noteContent']),
         'LastUpdatedByID' => $userDetails->ID
      );

      if (isset($data['noteType'])) {
         $updateData['noteType'] = Utility::clean_string($data['noteType']);
      }

      $result = $DBConn->update_table('tija_prospect_notes', $updateData, array('prospectNoteID' => $noteID));

      if ($result) {
         return array('success' => true, 'message' => 'Note updated successfully.');
      }

      return array('success' => false, 'message' => 'Failed to update note.');
   }

   /**
    * Delete a prospect note (soft delete)
    * @param int $noteID Note ID
    * @param object $userDetails Current user
    * @param object $DBConn Database connection
    * @return array Response with success status
    */
   public static function deleteProspectNote($noteID, $userDetails, $DBConn) {
      // Verify user owns the note or is admin
      $note = $DBConn->retrieve_db_table_rows('tija_prospect_notes',
         array('prospectNoteID', 'createdByID'),
         array('prospectNoteID' => $noteID));

      if (!$note || count($note) === 0) {
         return array('success' => false, 'message' => 'Note not found.');
      }

      if ($note[0]->createdByID != $userDetails->ID && $userDetails->userType != 'admin') {
         return array('success' => false, 'message' => 'You do not have permission to delete this note.');
      }

      // Soft delete
      $result = $DBConn->update_table('tija_prospect_notes',
         array('Suspended' => 'Y', 'LastUpdatedByID' => $userDetails->ID),
         array('prospectNoteID' => $noteID));

      if ($result) {
         return array('success' => true, 'message' => 'Note deleted successfully.');
      }

      return array('success' => false, 'message' => 'Failed to delete note.');
   }

   // =====================================================
   // SALES CASE NOTES METHODS
   // =====================================================

   /**
    * Get sales case notes
    * @param array $whereArr - Filter conditions
    * @param bool $single - Return single record or array
    * @param object $DBConn - Database connection
    * @return mixed - Note object(s) or false
    */
   public static function sales_case_notes($whereArr, $single, $DBConn) {
      $cols = array(
         'salesCaseNoteID',
         'salesCaseID',
         'saleStatusLevelID',
         'noteText',
         'noteType',
         'isPrivate',
         'createdByID',
         'targetUserID',
         'DateAdded',
         'LastUpdate',
         'LastUpdatedByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_sales_case_notes', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get sales case notes with full details (joins user names, stage info, recipients)
    * @param int $salesCaseID - Sales case ID
    * @param int $userID - Current user ID (for privacy filtering)
    * @param object $DBConn - Database connection
    * @return array - Array of note objects with details
    */
   public static function sales_case_notes_full($salesCaseID, $userID, $DBConn) {
      $sql = "SELECT
                n.*,
                u.employeeName as addedByName,
                sl.statusLevel as stageName,
                tu.employeeName as targetUserName
              FROM tija_sales_case_notes n
              LEFT JOIN sbsl_users u ON n.createdByID = u.ID
              LEFT JOIN tija_sales_status_levels sl ON n.saleStatusLevelID = sl.saleStatusLevelID
              LEFT JOIN sbsl_users tu ON n.targetUserID = tu.ID
              WHERE n.salesCaseID = :salesCaseID
                AND n.Suspended = 'N'
                AND (
                    n.noteType = 'general'
                    OR n.createdByID = :userID
                    OR n.targetUserID = :userID
                    OR EXISTS (
                        SELECT 1 FROM tija_sales_case_note_recipients r
                        WHERE r.salesCaseNoteID = n.salesCaseNoteID
                        AND r.recipientUserID = :userID
                    )
                )
              ORDER BY n.DateAdded DESC";

      $params = array(
         ':salesCaseID' => $salesCaseID,
         ':userID' => $userID
      );

      $notes = $DBConn->execute_query($sql, $params);

      // Get recipients for each note
      if ($notes) {
         foreach ($notes as &$note) {
            if ($note->isPrivate === 'Y') {
               $note->recipients = self::get_note_recipients($note->salesCaseNoteID, $DBConn);
            }
         }
      }

      return $notes;
   }

   /**
    * Get recipients for a private note
    * @param int $salesCaseNoteID - Note ID
    * @param object $DBConn - Database connection
    * @return array - Array of recipient objects
    */
   public static function get_note_recipients($salesCaseNoteID, $DBConn) {
      $sql = "SELECT
                r.*,
                u.employeeName as recipientName
              FROM tija_sales_case_note_recipients r
              LEFT JOIN sbsl_users u ON r.recipientUserID = u.ID
              WHERE r.salesCaseNoteID = :noteID
              ORDER BY u.employeeName";

      return $DBConn->execute_query($sql, array(':noteID' => $salesCaseNoteID));
   }

   /**
    * Add a sales case note
    * @param array $noteData - Note data
    * @param array $recipients - Array of recipient user IDs (for private notes)
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function add_sales_case_note($noteData, $recipients, $DBConn) {
      // Insert note
      $noteID = $DBConn->insert_data('tija_sales_case_notes', $noteData);

      if (!$noteID) {
         return array('success' => false, 'message' => 'Failed to create note.');
      }

      // If private note with recipients, add recipients
      if ($noteData['isPrivate'] === 'Y' && !empty($recipients)) {
         foreach ($recipients as $recipientID) {
            $recipientData = array(
               'salesCaseNoteID' => $noteID,
               'recipientUserID' => (int)$recipientID
            );
            $DBConn->insert_data('tija_sales_case_note_recipients', $recipientData);
         }
      }

      return array('success' => true, 'message' => 'Note added successfully.', 'noteID' => $noteID);
   }

   /**
    * Delete a sales case note
    * @param int $noteID - Note ID
    * @param int $userID - User attempting deletion (must be creator)
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function delete_sales_case_note($noteID, $userID, $DBConn) {
      // Verify user is the creator
      $note = self::sales_case_notes(array('salesCaseNoteID' => $noteID), true, $DBConn);

      if (!$note) {
         return array('success' => false, 'message' => 'Note not found.');
      }

      if ($note->createdByID != $userID) {
         return array('success' => false, 'message' => 'You can only delete your own notes.');
      }

      // Soft delete
      $updateData = array('Suspended' => 'Y');
      $whereData = array('salesCaseNoteID' => $noteID);

      if ($DBConn->update_table('tija_sales_case_notes', $updateData, $whereData)) {
         return array('success' => true, 'message' => 'Note deleted successfully.');
      }

      return array('success' => false, 'message' => 'Failed to delete note.');
   }

   // =====================================================
   // SALES CASE NEXT STEPS METHODS
   // =====================================================

   /**
    * Get sales case next steps
    * @param array $whereArr - Filter conditions
    * @param bool $single - Return single record or array
    * @param object $DBConn - Database connection
    * @return mixed - Next step object(s) or false
    */
   public static function sales_case_next_steps($whereArr, $single, $DBConn) {
      $cols = array(
         'salesCaseNextStepID',
         'salesCaseID',
         'saleStatusLevelID',
         'nextStepDescription',
         'dueDate',
         'priority',
         'status',
         'assignedToID',
         'completedDate',
         'completedByID',
         'createdByID',
         'DateAdded',
         'LastUpdate',
         'LastUpdatedByID',
         'Lapsed',
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_sales_case_next_steps', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   /**
    * Get sales case next steps with full details
    * @param int $salesCaseID - Sales case ID
    * @param object $DBConn - Database connection
    * @return array - Array of next step objects with details
    */
   public static function sales_case_next_steps_full($salesCaseID, $DBConn) {
      $sql = "SELECT
                ns.*,
                cu.employeeName as createdByName,
                au.employeeName as assignedToName,
                cou.employeeName as completedByName,
                sl.statusLevel as stageName
              FROM tija_sales_case_next_steps ns
              LEFT JOIN sbsl_users cu ON ns.createdByID = cu.ID
              LEFT JOIN sbsl_users au ON ns.assignedToID = au.ID
              LEFT JOIN sbsl_users cou ON ns.completedByID = cou.ID
              LEFT JOIN tija_sales_status_levels sl ON ns.saleStatusLevelID = sl.saleStatusLevelID
              WHERE ns.salesCaseID = :salesCaseID
                AND ns.Suspended = 'N'
              ORDER BY
                CASE ns.status
                    WHEN 'pending' THEN 1
                    WHEN 'in_progress' THEN 2
                    WHEN 'completed' THEN 3
                    WHEN 'cancelled' THEN 4
                END,
                CASE ns.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                ns.dueDate ASC";

      return $DBConn->execute_query($sql, array(':salesCaseID' => $salesCaseID));
   }

   /**
    * Add a sales case next step
    * @param array $stepData - Next step data
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function add_sales_case_next_step($stepData, $DBConn) {
      $stepID = $DBConn->insert_data('tija_sales_case_next_steps', $stepData);

      if ($stepID) {
         return array('success' => true, 'message' => 'Next step added successfully.', 'stepID' => $stepID);
      }

      return array('success' => false, 'message' => 'Failed to create next step.');
   }

   /**
    * Update next step status
    * @param int $stepID - Next step ID
    * @param string $status - New status
    * @param int $userID - User making the update
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function update_next_step_status($stepID, $status, $userID, $DBConn) {
      $updateData = array(
         'status' => $status,
         'LastUpdatedByID' => $userID
      );

      // If marking as completed, set completion details
      if ($status === 'completed') {
         $updateData['completedDate'] = date('Y-m-d H:i:s');
         $updateData['completedByID'] = $userID;
      }

      $whereData = array('salesCaseNextStepID' => $stepID);

      if ($DBConn->update_table('tija_sales_case_next_steps', $updateData, $whereData)) {
         return array('success' => true, 'message' => 'Next step status updated successfully.');
      }

      return array('success' => false, 'message' => 'Failed to update next step status.');
   }

   /**
    * Delete a sales case next step
    * @param int $stepID - Next step ID
    * @param int $userID - User attempting deletion
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function delete_sales_case_next_step($stepID, $userID, $DBConn) {
      // Soft delete
      $updateData = array(
         'Suspended' => 'Y',
         'LastUpdatedByID' => $userID
      );
      $whereData = array('salesCaseNextStepID' => $stepID);

      if ($DBConn->update_table('tija_sales_case_next_steps', $updateData, $whereData)) {
         return array('success' => true, 'message' => 'Next step deleted successfully.');
      }

      return array('success' => false, 'message' => 'Failed to delete next step.');
   }

   // =====================================================
   // PROSPECT CRUD METHODS (Phase 1)
   // =====================================================

   /**
    * Create a new prospect
    * @param array $prospectData - Prospect data
    * @param int $userID - User creating the prospect
    * @param object $DBConn - Database connection
    * @return array - Success/failure response with prospectID
    */
   public static function create_prospect($prospectData, $userID, $DBConn) {
      // Validate required fields
      $required = array('salesProspectName', 'prospectEmail', 'prospectCaseName', 'businessUnitID', 'leadSourceID');
      foreach ($required as $field) {
         if (empty($prospectData[$field])) {
            return array('success' => false, 'message' => "Missing required field: {$field}");
         }
      }

      // Add audit fields
      $prospectData['LastUpdateByID'] = $userID;

      // Handle tags if array
      if (isset($prospectData['tags']) && is_array($prospectData['tags'])) {
         $prospectData['tags'] = json_encode($prospectData['tags']);
      }

      // Insert prospect
      $result = $DBConn->insert_data('tija_sales_prospects', $prospectData);

      if ($result) {
         $prospectID = $DBConn->lastInsertId();

         // Calculate initial lead score
         self::calculate_lead_score($prospectID, $DBConn);

         return array(
            'success' => true,
            'message' => 'Prospect created successfully.',
            'prospectID' => $prospectID
         );
      }

      return array('success' => false, 'message' => 'Failed to create prospect.');
   }

   /**
    * Update an existing prospect
    * @param int $prospectID - Prospect ID
    * @param array $prospectData - Data to update
    * @param int $userID - User making the update
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function update_prospect($prospectID, $prospectData, $userID, $DBConn) {
      // Verify prospect exists
      $prospect = self::sales_prospect_full($prospectID, $DBConn);
      if (!$prospect) {
         return array('success' => false, 'message' => 'Prospect not found.');
      }

      // Add audit field
      $prospectData['LastUpdateByID'] = $userID;

      // Handle tags if array
      if (isset($prospectData['tags']) && is_array($prospectData['tags'])) {
         $prospectData['tags'] = json_encode($prospectData['tags']);
      }

      // Update prospect
      $result = $DBConn->update_table(
         'tija_sales_prospects',
         $prospectData,
         array('salesProspectID' => $prospectID)
      );

      if ($result) {
         // Recalculate lead score if relevant fields changed
         if (isset($prospectData['budgetConfirmed']) || isset($prospectData['decisionMakerIdentified']) ||
             isset($prospectData['timelineDefined']) || isset($prospectData['needIdentified'])) {
            self::calculate_lead_score($prospectID, $DBConn);
         }

         return array('success' => true, 'message' => 'Prospect updated successfully.');
      }

      return array('success' => false, 'message' => 'Failed to update prospect.');
   }

   /**
    * Delete a prospect (soft delete)
    * @param int $prospectID - Prospect ID
    * @param int $userID - User deleting the prospect
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function delete_prospect($prospectID, $userID, $DBConn) {
      // Verify prospect exists
      $prospect = self::sales_prospect_full($prospectID, $DBConn);
      if (!$prospect) {
         return array('success' => false, 'message' => 'Prospect not found.');
      }

      // Soft delete
      $updateData = array(
         'Suspended' => 'Y',
         'LastUpdateByID' => $userID
      );

      $result = $DBConn->update_table(
         'tija_sales_prospects',
         $updateData,
         array('salesProspectID' => $prospectID)
      );

      if ($result) {
         return array('success' => true, 'message' => 'Prospect deleted successfully.');
      }

      return array('success' => false, 'message' => 'Failed to delete prospect.');
   }

   /**
    * Assign prospect to a team
    * @param int $prospectID - Prospect ID
    * @param int $teamID - Team ID
    * @param int $userID - User making the assignment
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function assign_prospect_team($prospectID, $teamID, $userID, $DBConn) {
      $updateData = array(
         'assignedTeamID' => $teamID,
         'LastUpdateByID' => $userID
      );

      $result = $DBConn->update_table(
         'tija_sales_prospects',
         $updateData,
         array('salesProspectID' => $prospectID)
      );

      if ($result) {
         return array('success' => true, 'message' => 'Team assigned successfully.');
      }

      return array('success' => false, 'message' => 'Failed to assign team.');
   }

   /**
    * Update prospect status
    * @param int $prospectID - Prospect ID
    * @param string $status - New status
    * @param int $userID - User making the update
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function update_prospect_status($prospectID, $status, $userID, $DBConn) {
      $updateData = array(
         'salesProspectStatus' => $status,
         'LastUpdateByID' => $userID
      );

      $result = $DBConn->update_table(
         'tija_sales_prospects',
         $updateData,
         array('salesProspectID' => $prospectID)
      );

      if ($result) {
         return array('success' => true, 'message' => 'Status updated successfully.');
      }

      return array('success' => false, 'message' => 'Failed to update status.');
   }

   /**
    * Update prospect qualification
    * @param int $prospectID - Prospect ID
    * @param array $qualificationData - BANT qualification data
    * @param int $userID - User making the update
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function update_prospect_qualification($prospectID, $qualificationData, $userID, $DBConn) {
      // Add audit field
      $qualificationData['LastUpdateByID'] = $userID;

      $result = $DBConn->update_table(
         'tija_sales_prospects',
         $qualificationData,
         array('salesProspectID' => $prospectID)
      );

      if ($result) {
         // Recalculate lead score
         self::calculate_lead_score($prospectID, $DBConn);

         return array('success' => true, 'message' => 'Qualification updated successfully.');
      }

      return array('success' => false, 'message' => 'Failed to update qualification.');
   }

   /**
    * Log an interaction with a prospect
    * @param int $prospectID - Prospect ID
    * @param array $interactionData - Interaction details
    * @param int $userID - User logging the interaction
    * @param object $DBConn - Database connection
    * @return array - Success/failure response
    */
   public static function log_prospect_interaction($prospectID, $interactionData, $userID, $DBConn) {
      // Prepare interaction data
      $data = array(
         'salesProspectID' => $prospectID,
         'interactionType' => $interactionData['interactionType'] ?? 'other',
         'interactionNotes' => $interactionData['interactionNotes'] ?? '',
         'interactionDate' => $interactionData['interactionDate'] ?? date('Y-m-d H:i:s'),
         'createdByID' => $userID
      );

      // Add optional fields
      if (isset($interactionData['nextFollowUpDate'])) {
         $data['nextFollowUpDate'] = $interactionData['nextFollowUpDate'];
      }

      // Insert interaction
      $result = $DBConn->insert_data('tija_prospect_interactions', $data);

      if ($result) {
         // Update prospect's last contact date
         $updateData = array(
            'lastContactDate' => date('Y-m-d'),
            'LastUpdateByID' => $userID
         );

         if (isset($interactionData['nextFollowUpDate'])) {
            $updateData['nextFollowUpDate'] = $interactionData['nextFollowUpDate'];
         }

         $DBConn->update_table(
            'tija_sales_prospects',
            $updateData,
            array('salesProspectID' => $prospectID)
         );

         return array(
            'success' => true,
            'message' => 'Interaction logged successfully.',
            'interactionID' => $DBConn->lastInsertId()
         );
      }

      return array('success' => false, 'message' => 'Failed to log interaction.');
   }

   // =====================================================
   // PROSPECT BULK OPERATIONS (Phase 2)
   // =====================================================

   /**
    * Bulk assign prospects to a team
    * @param array $prospectIDs - Array of prospect IDs
    * @param int $teamID - Team ID to assign
    * @param int $userID - User making the assignment
    * @param object $DBConn - Database connection
    * @return array - Success/failure with count
    */
   public static function bulk_assign_team($prospectIDs, $teamID, $userID, $DBConn) {
      if (empty($prospectIDs) || !is_array($prospectIDs)) {
         return array('success' => false, 'message' => 'No prospects selected.');
      }

      $successCount = 0;
      foreach ($prospectIDs as $prospectID) {
         $result = self::assign_prospect_team((int)$prospectID, $teamID, $userID, $DBConn);
         if ($result['success']) $successCount++;
      }

      return array(
         'success' => true,
         'message' => "{$successCount} prospects assigned to team successfully.",
         'count' => $successCount
      );
   }

   /**
    * Bulk update prospect status
    * @param array $prospectIDs - Array of prospect IDs
    * @param string $status - New status
    * @param int $userID - User making the update
    * @param object $DBConn - Database connection
    * @return array - Success/failure with count
    */
   public static function bulk_update_status($prospectIDs, $status, $userID, $DBConn) {
      if (empty($prospectIDs) || !is_array($prospectIDs)) {
         return array('success' => false, 'message' => 'No prospects selected.');
      }

      $successCount = 0;
      foreach ($prospectIDs as $prospectID) {
         $result = self::update_prospect_status((int)$prospectID, $status, $userID, $DBConn);
         if ($result['success']) $successCount++;
      }

      return array(
         'success' => true,
         'message' => "{$successCount} prospects updated successfully.",
         'count' => $successCount
      );
   }

   /**
    * Bulk update prospect qualification
    * @param array $prospectIDs - Array of prospect IDs
    * @param array $qualificationData - Qualification data
    * @param int $userID - User making the update
    * @param object $DBConn - Database connection
    * @return array - Success/failure with count
    */
   public static function bulk_update_qualification($prospectIDs, $qualificationData, $userID, $DBConn) {
      if (empty($prospectIDs) || !is_array($prospectIDs)) {
         return array('success' => false, 'message' => 'No prospects selected.');
      }

      $successCount = 0;
      foreach ($prospectIDs as $prospectID) {
         $result = self::update_prospect_qualification((int)$prospectID, $qualificationData, $userID, $DBConn);
         if ($result['success']) $successCount++;
      }

      return array(
         'success' => true,
         'message' => "{$successCount} prospects qualified successfully.",
         'count' => $successCount
      );
   }

   /**
    * Bulk delete prospects (soft delete)
    * @param array $prospectIDs - Array of prospect IDs
    * @param int $userID - User deleting the prospects
    * @param object $DBConn - Database connection
    * @return array - Success/failure with count
    */
   public static function bulk_delete_prospects($prospectIDs, $userID, $DBConn) {
      if (empty($prospectIDs) || !is_array($prospectIDs)) {
         return array('success' => false, 'message' => 'No prospects selected.');
      }

      $successCount = 0;
      foreach ($prospectIDs as $prospectID) {
         $result = self::delete_prospect((int)$prospectID, $userID, $DBConn);
         if ($result['success']) $successCount++;
      }

      return array(
         'success' => true,
         'message' => "{$successCount} prospects deleted successfully.",
         'count' => $successCount
      );
   }

   /**
    * Bulk calculate lead scores
    * @param array $prospectIDs - Array of prospect IDs
    * @param object $DBConn - Database connection
    * @return array - Success/failure with count
    */
   public static function bulk_calculate_scores($prospectIDs, $DBConn) {
      if (empty($prospectIDs) || !is_array($prospectIDs)) {
         return array('success' => false, 'message' => 'No prospects selected.');
      }

      $successCount = 0;
      foreach ($prospectIDs as $prospectID) {
         $score = self::calculate_lead_score((int)$prospectID, $DBConn);
         if ($score !== false) $successCount++;
      }

      return array(
         'success' => true,
         'message' => "{$successCount} lead scores calculated successfully.",
         'count' => $successCount
      );
   }

}
