<?php

/* 
 * This class is used to define the activity class
 * 
 * @package    Admin
 * @subpackage Admin
 * @category   Admin
 * @version    1.0
 * @since      1.0
 */
class Activity {
   public static function log_activity($details, $isLog = false, $DBConn = null) {
      if($isLog) {
         $logDetails = array();
         $logDetails['userID'] = $details['userID'];
         $logDetails['action'] = $details['action'];
         $logDetails['objectType'] = $details['objectType'];
         $logDetails['objectID'] = $details['objectID'];
         $logDetails['objectName'] = $details['objectName'];
         return $DBConn->insert_data('tija_activity_log', $logDetails);
      }
   }
   public static function activity_mini($whereArr, $single, $DBConn){
      $cols = array(
         'activityID',
         'DateAdded',
         'orgDataID',
         'entityID',
         'clientID',
         'activityName',
         'activityDescription',
         'activityCategoryID',
         'activityTypeID',
         'activitySegment',
         'durationType',
         'activityDate',
         'activityStartTime',
         'activityDurationEndTime',
         'activityDurationEndDate',
         'recurring',
         'recurrenceType',
         'recurringInterval',
         'recurringIntervalUnit',
         'weekRecurringDays',
         'monthRepeatOnDays',
         'monthlyRepeatingDay',
         'customFrequencyOrdinal',
         'customFrequencyDayValue',
         'recurrenceEndType',
         'numberOfOccurrencesToEnd',
         'recurringEndDate',
         'salesCaseID',
         'projectID',
         'projectPhaseID',
         'projectTaskID',
         'activityStatus',
         'activityStatusID',
         'activityPriority',
         'activityOwnerID',
         'activityParticipants',
         'activityNotesID',
         'assignedByID',
         'LastUpdate',
         'LastUpdateByID',
         'Lapsed',
         'Suspended',
         'activityLocation'
      );
      
      $rows = $DBConn->retrieve_db_table_rows('tija_activities', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function activities ($whereArr, $single, $DBConn) {
      $params = array();
      $where="";
      $rows=array();
      $activity = array(
                           'activityID',
                           'DateAdded',
                           'orgDataID',
                           'entityID',
                           'activityCategoryID',
                           'activityTypeID',
                           'activityName',
                           'durationType',
                           'activityDate',
                           'activityStartTime',                           
                           'activityDescription',
                           'activityLocation',
                           'activitySegment',
                           'clientID',
                           'salesCaseID',
                           'projectID',
                           'projectPhaseID',
                           'projectTaskID',
                           'activityPriority',
                           'activityStatus',
                           'activityOwnerID',
                           'activityParticipants',
                           'duration',
                           'reccurring',
                           // Duration fields
                           'activityDurationEndTime',
                           'activityDurationEndDate',
                           // Recurrence fields
                           'recurrenceType',
                           'recurringInterval',
                           'recurringIntervalUnit',
                           'weekRecurringDays',
                           // month recurring fields
                           'monthRepeatOnDays',
                           'monthlyRepeatingDay',
                           'customFrequencyOrdinal',
                           'customFrequencyDayValue',
                           'recurrenceEndType',
                           'endDateOccurrenceValue',
                           'recurringEndDate',
                           'activityStatusID',
                                                  
                        );
      
      $activityTypes = array('activityTypeID', 'activityTypeName', 'activityTypeDescription');
      $activityCategories = array('activityCategoryID', 'activityCategoryName', 'activityCategoryDescription');
      $clients = array('clientID', 'clientName', 'clientCode', 'accountOwnerID',  'vatNumber', 'clientDescription');
      $organisations = array('orgDataID', 'orgName');
      $entities = array('entityID', 'entityName', 'entityTypeID', 'entityParentID');
      $activityStatus = array( 'activityStatus', 'activityStatusDescription');
      $sales = array( 'salesCaseName',  'salesPersonID');
      $projects = array('projectID', 'projectName',  'projectOwnerID');
      $projectPhases = array('projectPhaseID', 'projectPhaseName' );
      $projectTasks = array('projectTaskID', 'projectTaskName');
     
      
      $sales = array( 'salesCaseName');

      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
               if ($where == '') {
                  $where = "WHERE ";
               } else {
                  $where .= " AND ";
               }
            
               if(in_array($col, $activity)) {
                  $where .= "act.{$col} = ?";
               } elseif (in_array($col, $activityTypes)) {
                  $where .= "at.{$col} = ?";
               } elseif (in_array($col, $activityCategories)) {
                  $where .= "ac.{$col} = ?";
               } elseif (in_array($col, $clients)) {
                  $where .= "c.{$col} = ?";
               } elseif (in_array($col, $organisations)) {
                  $where .= "o.{$col} = ?";
               } elseif (in_array($col, $entities)) {
                  $where .= "e.{$col} = ?";
               } elseif (in_array($col, $activityStatus)) {
                  $where .= "s.{$col} = ?";
               } elseif (in_array($col, $sales)) {
                  $where .= "sc.{$col} = ?";
               } elseif (in_array($col, $projects)) {
                  $where .= "p.{$col} = ?";
               } elseif (in_array($col, $projectPhases)) {
                  $where .= "pp.{$col} = ?";
               } elseif (in_array($col, $projectTasks)) {
                  $where .= "pt.{$col} = ?";
               } else{
               // handle unknown columns
               continue;
               }
               $params[] = array($val, 's');
               $i++;
         }
      }

      $sql = "SELECT act.*, CONCAT(u.FirstName, ' ', u.Surname) as activityOwnerName, u.Email as activityOwnerEmail, 
      at.activityTypeName, at.activityTypeDescription, at.iconlink as activityTypeIcon,
      ac.activityCategoryName, ac.activityCategoryDescription, ac.iconlink as activityCategoryIcon,
      o.orgName,
      e.entityName, e.entityTypeID, e.entityParentID,
      s.activityStatusName as activityStatusName, 
      c.clientName, c.clientCode, c.accountOwnerID,
      sc.salesCaseName,sc.salesPersonID,
      p.projectName,  p.projectOwnerID,
      pp.projectPhaseName, 
      pt.projectTaskName

        FROM tija_activities act 
        LEFT JOIN people u ON act.activityOwnerID = u.ID 
        LEFT JOIN tija_activity_types at ON act.activityTypeID = at.activityTypeID
        LEFT JOIN tija_activity_categories ac ON act.activityCategoryID = ac.activityCategoryID
        LEFT JOIN tija_organisation_data o ON act.orgDataID = o.orgDataID
        LEFT JOIN tija_entities e ON act.entityID = e.entityID
        LEFT JOIN tija_activity_status s ON act.activityStatusID = s.activityStatusID
        LEFT JOIN tija_clients c ON act.clientID = c.clientID
        LEFT JOIN tija_sales_cases sc ON act.salesCaseID = sc.salesCaseID
        LEFT JOIN tija_projects p  ON act.projectID = p.projectID
        LEFT JOIN tija_project_phases pp ON act.projectPhaseID = pp.projectPhaseID
        LEFT JOIN tija_project_tasks pt ON act.projectTaskID = pt.projectTaskID
        
       
      
         {$where} 
      ORDER BY act.activityDate DESC";
      $rows = $DBConn->fetch_all_rows($sql,$params);
      if($rows){
         foreach ($rows as $key => $row) {
            $rows[$key]->activityStartTime = date('H:i', strtotime($row->activityStartTime));
            $rows[$key]->activityDate = date('Y-m-d', strtotime($row->activityDate));
            if($row->activityDurationEndTime){
               $rows[$key]->activityDurationEndTime = date('H:i', strtotime($row->activityDurationEndTime));
            }
            if($row->activityDurationEndDate){
               $rows[$key]->activityDurationEndDate = date('Y-m-d', strtotime($row->activityDurationEndDate));
            }
         $participantsIDs = explode(',', $row->activityParticipants);

         // var_dump($participantsIDs);
         $participantsDetails = [];
         if($participantsIDs){
            foreach ($participantsIDs as $participantID) {
               if(Utility::clean_string($participantID)){
                  $participantDetails = Core::user(['ID'=>$participantID], true, $DBConn);
                  // var_dump($participantDetails);
                  $participantName = Core::user_name($participantID, $DBConn);
                  $participantsDetails[] = (object)[
                     'name' => $participantName,
                     'id' => $participantDetails->ID,
                     'email' => $participantDetails->Email
                  ];
               }
            }
            $rows[$key]->activityParticipantsDetails = $participantsDetails;
         }
         
        
         }
      }
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

   }

   public static function activity_types_mini($whereArr, $single, $DBConn) {
      $cols= array('activityTypeID', 'DateAdded', 'activityTypeName', 'iconlink', 'activityTypeDescription', 'activityCategoryID',  'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_activity_types', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function activity_types($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $activityTypes= array( 'activityTypeID', 
                              'activityTypeName', 
                              'activityTypeDescription', 
                           
                              'LastUpdate', 
                              'Lapsed', 
                              'Suspended'
                            );
      $activityCategories= array('activityCategoryID', 'activityCategoryName', 'activityCategoryDescription');
  
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if(in_array($col, $activityTypes)) {
                  $where .= "s.{$col} = ?";
            } elseif (in_array($col, $activityCategories)) {
               $where .= "ac.{$col} =  ?";
            } else {
               // handle unknown columns
               continue;
            }
            $params[] = array($val, 's');
            $i++;
         }
      }
  
      // var_dump($where);
   
      $sql= "SELECT s.activityTypeID, s.activityTypeName, s.activityTypeDescription,  s.LastUpdate, s.Lapsed, s.Suspended,
      ac.activityCategoryID, ac.activityCategoryName, ac.activityCategoryDescription
      FROM tija_activity_types s
      LEFT JOIN tija_activity_categories ac ON s.activityCategoryID = ac.activityCategoryID
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function activity_categories($whereArr, $single, $DBConn) {
      $cols= array('activityCategoryID', 'DateAdded', 'activityCategoryName', 'iconlink', 'activityCategoryDescription',  'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_activity_categories', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function activity_status($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $activityStatus= array( 'activityStatusID', 
                                'activityStatusName', 
                                'activityStatusDescription', 
                                'LastUpdateByID', 
                                'LastUpdate', 
                                'Lapsed', 
                                'Suspended'
                              );
  
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if(in_array($col, $activityStatus)) {
                  $where .= "s.{$col} = ?";
            } else {
               // handle unknown columns
               continue;
            }
            $params[] = array($val, 's');
            $i++;
         }
      }
  
      // var_dump($where);
   
      $sql= "SELECT s.activityStatusID, s.activityStatusName, s.activityStatusDescription, s.LastUpdateByID, s.LastUpdate, s.Lapsed, s.Suspended
      FROM tija_activity_status s
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function activity_participants($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $activityParticipants= array( 
         
                              'activityParticipantID', 
                              'activityID', 
                              'participantUserID', 
                              'activityOwnerID', 
                              'recurring',
                              'recurringInterval',
                              'recurringIntervalUnit',
                              'activityStartDate',
                              'activityEndDate',                               
                              'LastUpdateByID',
                              'CreatedByID' ,
                              'LastUpdate', 
                              'Lapsed', 
                              'Suspended'
                           );
      $people= array('ID', 'FirstName', 'Surname', 'Email');

      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if(in_array($col, $activityParticipants)) {
               $where .= "s.{$col} = ?";
            } elseif (in_array($col, $people)) {
               $where .= "p.{$col} = ?";
            } else {
               // handle unknown columns
               continue;
            }
            $params[] = array($val, 's');
            $i++;
         }
      }
   
      $sql= "SELECT s.activityParticipantID, s.activityID, s.participantUserID, s.activityOwnerID, s.recurring, s.recurringInterval, s.recurringIntervalUnit, s.activityStartDate, s.activityEndDate, s.LastUpdateByID, s.CreatedByID, s.LastUpdate, s.Lapsed, s.Suspended, CONCAT(p.FirstName, ' ', p.Surname) as participantName, p.Email as participantEmail

      FROM tija_activity_participant_assignment s
      LEFT JOIN people p ON s.participantUserID = p.ID
         {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function recurring_activity_instances($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $recurringActivityInstances= array( 
                              'recurringInstanceID', 
                              'DateAdded',
                              'activityID', 
                              'activityInstanceDate',
                              'activityinstanceStartTime',
                            
                              'activityInstanceDurationEndTime',   
                              'instanceCount',
                              'orgDataiD',
                              'entityID',  
                              'activityStatusID',
                              'activityInstanceOwnerID',
                              
                              'completed',
                              'LastUpdateByID',                             
                              'LastUpdate', 
                              'Lapsed', 
                              'Suspended'
                           );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if(in_array($col, $recurringActivityInstances)) {
               $where .= "s.{$col} = ?";
            } else {
               // handle unknown columns
               continue;
            }
            $params[] = array($val, 's');
            $i++;
         }
      }
   
      $sql= "SELECT s.*

      FROM tija_recurring_activity_instances s
         {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }
}