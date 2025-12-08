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

      // $prospectStatus = array('salesProspectStatusID', 'salesProspectStatusName', 'salesProspectStatusDescription' );
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

}
