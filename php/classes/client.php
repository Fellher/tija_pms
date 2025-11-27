<?php
/*Path: php/classes/client.php
Client class
*/
class Client {
   public static function clients($whereArr, $single, $DBConn) {
      $cols= array(  'clientID', 
                     'clientName', 
                     'clientCode', 
                     'accountOwnerID',   
                     'orgDataID',
                     'entityID',
                     'vatNumber',
                     'clientPin',
                     'clientSectorID', 
                     'clientIndustryID ',
                     'clientPin',
                     'clientDescription',            
                     'clientLevelID',
                     'countryID',
                     'city',
                     'LastUpdateByID', 
                     'LastUpdate', 
                     'Lapsed', 
                     'Suspended',
                     'inHouse', 
                     "isClient" //=> "IF(c.clientID IS NOT NULL, 1, 0) AS isClient"
                   
                  );
      $rows = $DBConn->retrieve_db_table_rows ('tija_clients', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }


  public static function client_full ($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $clientsArray = array(
         'clientID',
         'DateAdded',
         'orgDataID',
         'entityID',
         'clientName',
         'clientCode',
         'accountOwnerID',
         'vatNumber',
         'clientDescription',
         'clientIndustryID',
         'clientSectorID',
         'clientLevelID',
         'clientPin',
         'vatNumber',
         'countryID',
         'city',
         'LastUpdateByID',
         'LastUpdate',
         'Lapsed',
         'Suspended',
         'inHouse', 
         'isClient' //=> "IF(c.clientID IS NOT NULL, 1, 0) AS isClient"
      );

      $clientIndustry = array('industryID', 'industryName','industryDescription');
      $clientSector = array('sectorID', 'sectorName','sectorDescription');
      $clientLevel = array('clientLevelID', 'clientLevelName','clientLevelDescription');

      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if(in_array($col, $clientsArray)) {
               $where .= "c.{$col} = ?";
            } elseif(in_array($col, $clientIndustry)) {
               $where .= "i.{$col} = ?";
            } elseif(in_array($col, $clientSector)) {
               $where .= "cs.{$col} = ?";
            } elseif(in_array($col, $clientLevel)) {
               $where .= "cl.{$col} = ?";
            } else {
               $where .= "c.{$col} = ?";
            }
            $params[] = array($val, 's');
            $i++;
         }
      }

      // var_dump($where);
   
      $sql= "SELECT c.clientID, c.DateAdded, c.clientName, c.clientCode, c.accountOwnerID, c.vatNumber, c.clientDescription, c.clientIndustryID, c.clientSectorID, c.clientLevelID, c.countryID, c.city, c.LastUpdateByID, c.LastUpdate, c.Lapsed, c.Suspended, c.orgDataID, c.entityID, c.inHouse, c.isClient,
         i.industryID, i.industryName, i.industryDescription,
         cs.sectorID, cs.sectorName,
         cl.clientLevelID, cl.clientLevelName, cl.clientLevelDescription,
         u.FirstName as clientOwnerFirstName,
         u.Surname as clientOwnerLastName,
         CONCAT(u.FirstName, ' ', u.Surname) as clientOwnerName,
         o.orgDataID, o.orgName, 
         e.entityName, e.entityTypeID
      FROM tija_clients c 
      LEFT JOIN people u ON c.accountOwnerID = u.ID
      LEFT JOIN tija_organisation_data o ON c.orgDataID = o.orgDataID
      LEFT JOIN tija_entities e ON c.entityID = e.entityID
      LEFT JOIN tija_industries i ON c.clientIndustryID = i.industryID
      LEFT JOIN tija_industry_sectors cs ON c.clientSectorID = cs.sectorID
      LEFT JOIN tija_client_levels cl ON c.clientLevelID = cl.clientLevelID
      {$where}
      ORDER BY c.clientName ASC  ";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      if($rows){
         foreach($rows as $key => $row){
            $clientContacts = Client::client_contacts(array('clientID' => $row->clientID), false, $DBConn);
            $rows[$key]->clientContacts= $clientContacts;
            $clientAddresses = Client::client_address(array('clientID' => $row->clientID), false, $DBConn);
            $rows[$key]->clientAddresses= $clientAddresses;
            
         }
      }
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }


  public static function client_address ($whereArr, $single, $DBConn) {
      $cols= array(
         'clientAddressID', 
         'DateAdded',
         'clientID',
         'orgDataID', 
         'entityID',  
         'address', 
         'postalCode', 
         'city', 
         'clientEmail',
         'countryID', 
         'addressType', 
         'billingAddress', 
         'headquarters', 
         'LastUpdateByID', 
         'LastUpdate', 
         'Lapsed',
         'Suspended' );
      $rows= $DBConn->retrieve_db_table_rows('tija_client_addresses', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

   public static function clients_full($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $clients= array(  
                     'clientID', 
                     'DateAdded', 
                     'orgDataID', 
                     'entityID',   
                     'clientIndustryID',
                     'clientSectorID',
                     'clientLevelID',
                     'clientCode',
                     'clientName', 
                     'clientDescription',
                     'clientPin',
                     'vatNumber', 
                     'accountOwnerID',
                     'LastUpdateByID', 
                     'LastUpdate', 
                     'Lapsed', 
                     'Suspended',
                     'inHouse',
                     'isClient' //=> "IF(c.clientID IS NOT NULL, 1, 0) AS isClient"
                                  
                  );
      $clientIndustry = array('industryID', 'industryName','industryDescription');
      $clientSector = array('sectorID', 'sectorName','sectorDescription');

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
   
      $sql= "SELECT c.clientID,c.DateAdded, c.clientName, c.clientCode, c.accountOwnerID, c.vatNumber, c.clientDescription, c.clientIndustryID, c.clientSectorID, c.clientLevelID,  c.LastUpdateByID, c.LastUpdate, c.Lapsed, c.Suspended, c.orgDataID, c.entityID, c.inHouse, c.isClient,
      o.orgDataID, o.orgName,
      e.entityID, e.entityName, e.entityTypeID, e.entityParentID, e.industrySectorID, e.registrationNumber, e.entityPIN, e.entityCity, e.entityCountry, e.entityPhoneNumber, e.entityEmail,
      i.industryID, i.industryName, i.industryDescription,
      cs.sectorID, cs.sectorName, cs.sectorDescription,
      cl.clientLevelID, cl.clientLevelName, cl.clientLevelDescription,
      c.clientPin,
      u.FirstName as clientOwnerFirstName,
      u.Surname as clientOwnerLastName,
         CONCAT(u.FirstName, ' ', u.Surname) as clientOwnerName
      FROM tija_clients c 
      LEFT JOIN people u ON c.accountOwnerID = u.ID
      LEFT JOIN tija_industries i ON c.clientIndustryID = i.industryID
      LEFT JOIN tija_industry_sectors cs ON c.clientSectorID = cs.sectorID
      LEFT JOIN tija_client_levels cl ON c.clientLevelID = cl.clientLevelID
      LEFT JOIN tija_organisation_data o ON c.orgDataID = o.orgDataID
      LEFT JOIN tija_entities e ON c.entityID = e.entityID
      {$where}
      ORDER BY c.clientName ASC  ";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function contact_types($whereArr, $single, $DBConn) {
      $cols= array('contactTypeID', 'DateAdded', 'contactType', 'LastUpdateByID', 'LastUpdate', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows ('tija_contact_types', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function client_contacts($whereArr, $single, $DBConn) {
      $cols = array(
         'clientContactID', 
         'DateAdded', 
         'clientID', 
         'userID', 
         'title', 
         'contactTypeID', 
         'contactName', 
         'contactEmail', 
         'salutationID', 
         'contactPhone', 
         'clientAddressID', 
         'LastUpdateByID', 
         'LastUpdate', 
         'Lapsed', 
         'Suspended'
      );
      $rows = $DBConn->retrieve_db_table_rows('tija_client_contacts', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function client_contact_full ($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
         $clientContact = array(
         'clientContactID',
         'DateAdded',
         'clientID',
         'userID',         
         'contactTypeID',
         'contactName',
         'contactEmail',
         'contactPhone',
         'title',
         'clientAddressID',
         'LastUpdateByID',
         'LastUpdate',
         'Lapsed',
         'Suspended',
         'salutationID',
         );
         $clientArray = array(
         'clientName',
         'clientCode',
         'accountOwnerID',
         'industryID',
         'clientSectorID',
         'clientLevelID',
         'vatNumber',
         'clientDescription',
         'orgDataID',
         'entityID',
         );
         $clientAddress = array(
         'clientAddressID',
         'DateAdded',
         'address',
         'postalCode',
         'city',
         'countryID',
         'addressType',
         'billingAddress',
         'headquarters',
         );
         $contactType = array(
         'contactTypeID',
         'contactType',
         );
         $industrySector = array(
         'industrySectorID',
         'industrySector',
         );
         $salutation = array(
         'salutationID',
         'salutationName',
         'salutationDescription',
         );
         $clientLevel = array(     
         'clientLevel',
         'clientLevelDescription',
         );



      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if(in_array($col, $clientContact)) {
               $where .= "cc.{$col} = ?";
            } elseif (in_array($col, $clientArray)) {
               $where .= "c.{$col} = ?";
            } elseif (in_array($col, $clientAddress)) {
               $where .= "a.{$col} = ?";
            } elseif (in_array($col, $contactType)) {
               $where .= "ct.{$col} = ?";
            } elseif (in_array($col, $industrySector)) {
               $where .= "cs.{$col} = ?";
            } elseif (in_array($col, $salutation)) {
               $where .= "s.{$col} = ?";
            } elseif (in_array($col, $clientLevel)) {
               $where .= "cl.{$col} = ?";
            }
            else {
               $where .= "cc.{$col} = ?";
            }
            $params[] = array($val, 's');
            $i++;
         }
      }
      $sql = "SELECT 
               cc.clientContactID, cc.DateAdded, cc.clientID, cc.userID, cc.contactTypeID, cc.contactName, cc.contactEmail, cc.contactPhone, cc.title, cc.clientAddressID, cc.LastUpdateByID, cc.LastUpdate, cc.Lapsed, cc.Suspended, cc.salutationID,

               c.clientName, c.clientCode, c.accountOwnerID, c.clientIndustryID, c.clientSectorID, c.clientLevelID, c.vatNumber, c.clientDescription,  c.orgDataID, c.entityID, 
               
               a.clientAddressID, a.DateAdded, a.orgDataID, a.entityID, a.address, a.postalCode, a.city, a.countryID, a.addressType, a.billingAddress, a.headquarters, 

               ct.contactTypeID,  ct.contactType,
               cs.sectorID, cs.sectorName,
               i.industryID, i.industryName,
               s.prefixID, s.prefixName, s.prefixDescription,
               cl.clientLevelID, cl.clientLevelName, cl.clientLevelDescription


      FROM tija_client_contacts cc
      LEFT JOIN tija_clients c ON cc.clientID = c.clientID
      LEFT JOIN tija_client_addresses a ON cc.clientAddressID = a.clientAddressID 
      LEFT JOIN tija_contact_types ct ON cc.contactTypeID = ct.contactTypeID
      LEFT JOIN tija_industries i ON c.clientIndustryID = i.industryID
      LEFT JOIN tija_industry_sectors cs ON c.clientSectorID = cs.sectorID
      LEFT JOIN tija_name_prefixes s ON cc.salutationID = s.prefixID
      LEFT JOIN tija_client_levels cl ON c.clientLevelID = cl.clientLevelID
      
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function activities($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $activities= array( 'clientActivityID', 
                          'DateAdded', 
                          'clientID', 
                          'activityTypeID', 
                          'activityName', 
                          'activityDescription', 
                          'activityDate', 
                          'activityTime', 
                          'activityDuration', 
                          'activityStatusID', 
                          'LastUpdateByID', 
                          'LastUpdate', 
                          'Lapsed', 
                          'Suspended'
                        );
      $clients= array( 'clientName' );
      $activityTypes= array( 'activityType' );
      $activityStatus= array( 'statusName' );

      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            $where .= "ca.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
         }
      }

      // var_dump($where);
   
      $sql= "SELECT a.activityID, a.DateAdded, a.activityName, a.activityDescription, a.activityDate, a.activityTime, a.activityCategoryID, a.activityTypeID, a.orgDataID, a.entityID, a.clientID, a.activitySegment, a.salesCaseID, a.projectID, a.projectPhaseID, a.projectTaskID, a.subTaskID, a.activityStatus, a.activityStatusID, a.activityOwnerID, a.activityDeadline, a.activityParticipants, a.assignedByID, a.activityStartDate, a.activityNotes, a.LastUpdateByID, a.LastUpdate, a.Lapsed, a.Suspended,
        c.clientID, c.clientName, c.clientCode, c.accountOwnerID, c.vatNumber, c.clientDescription, c.clientIndustryID, c.clientSectorID, c.clientLevelID,
         ct.activityTypeID, ct.activityType, ct.activityCategoryID, ct.activityCategory,
         o.orgDataID, o.orgName, o.orgCode, o.orgTypeID, o.orgType,
         e.entityName, e.entityTypeID, e.entityParentID, e.industrySectorID, e.registrationNumber, e.entityPIN, e.entityCity, e.entityCountry, e.entityPhoneNumber, e.entityEmail,
         s.activityStatusName, s.activityStatusDescription, s.activityStatusID,
         
       
         CONCAT(u.FirstName, ' ', u.Surname) as activityOwnerName
      FROM tija_activities a
      LEFT JOIN tija_clients c ON a.clientID = c.clientID
      LEFT JOIN tija_activity_types ct ON a.activityTypeID = ct.activityTypeID
      LEFT JOIN tija_organisation_data o ON a.orgDataID = o.orgDataID
      LEFT JOIN tija_entities e ON a.entityID = e.entityID
      LEFT JOIN tija_activity_status s ON a.activityStatusID = s.statusID


       

       
        
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
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
            $where .= "s.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
         }
      }

      // var_dump($where);
   
      $sql= "SELECT s.activityStatusID, s.activityStatus, s.activityStatusDescription, s.LastUpdateByID, s.LastUpdate, s.Lapsed, s.Suspended
      FROM tija_activity_status s
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function client_levels($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $clientLevels= array( 'clientLevelID', 
                            'DateAdded', 
                            'clientLevelName', 
                            'LastUpdateByID', 
                            'clientLevelDescription',
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
            $where .= "cl.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
         }
      }

      // var_dump($where);
   
      $sql= "SELECT cl.clientLevelID, cl.DateAdded, cl.clientLevelName, cl.clientLevelDescription, cl.LastUpdateByID, cl.LastUpdate, cl.Lapsed, cl.Suspended
      FROM tija_client_levels cl
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }
   public static function client_relationships($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $clientRelationships= array( 'clientRelationshipID', 
                                  'DateAdded', 
                                  'clientID',
                                  'employeeID',
                                  'clientRelationshipType',
                                  'LastUpdateByID',
                                  'startDate',
                                    'endDate',
                                  'LastUpdate',
                                  'Lapsed',
                                  'Suspended'
                                  );
      $client = array( 'clientName' );
      $employee = array( 'employeeName' );


      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if (in_array($col, $clientRelationships)) {
               $where .= "cr.{$col} = ?";
            } elseif (in_array($col, $client)) {
               $where .= "c.{$col} = ?";
            } elseif (in_array($col, $employee)) {
               $where .= "e.{$col} = ?";
            }
            $params[] = array($val, 's');
            $i++;
         }
      }

      // var_dump($where);

      $sql= "SELECT cr.*,
      c.clientName,
      CONCAT(e.FirstName, ' ', e.Surname) as employeeName,
      cr.clientRelationshipType,
      cr.LastUpdate,
      cr.Lapsed,
      cr.Suspended
      FROM client_relationship_assignments cr
      LEFT JOIN tija_clients c ON cr.clientID = c.clientID
      LEFT JOIN people e ON cr.employeeID = e.ID
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function client_relationship_types($whereArr, $single, $DBConn) {
      $params= array();
      $where= '';
      $rows=array();
      $clientRelationshipTypes= array( 'clientRelationshipTypeID', 
                                       'DateAdded', 
                                       'clientRelationshipType', 
                                       'clientRelationshipTypeDescription',
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
            $where .= "crt.{$col} = ?";
            $params[] = array($val, 's');
            $i++;
         }
      }

      // var_dump($where);
   
      $sql= "SELECT crt.clientRelationshipTypeID, crt.DateAdded, crt.clientRelationshipType, crt.LastUpdateByID, crt.LastUpdate, crt.Lapsed, crt.Suspended
      FROM tija_client_relationship_types crt
      {$where}";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function client_documents($whereArr, $single, $DBConn){
      $params= array();
      $where= '';
      $rows=array();
      $clientDocuments= array( 'clientDocumentID', 
                                'DateAdded', 
                                'clientID', 
                                'clientDocumentName', 
                                'clientDocumentDescription', 
                                'documentTypeID', 
                                'clientDocumentFile',
                                'documentFileName', 
                                'documentFileSize', 
                                'documentFileType', 
                                'documentFilePath', 
                                'LastUpdateByID', 
                                'LastUpdate', 
                                'Lapsed', 
                                'Suspended'
                              );
      $documentTypes= array( 'documentTypeID',
                            'documentTypeName');
      $client= array( 'clientName' );
      if (count($whereArr) > 0) {
         $i = 0;
         foreach ($whereArr as $col => $val) {
            if ($where == '') {
               $where = "WHERE ";
            } else {
               $where .= " AND ";
            }
            if (in_array($col, $clientDocuments)) {
               $where .= "cd.{$col} = ?";
            } elseif (in_array($col, $documentTypes)) {
               $where .= "dt.{$col} = ?";
            } elseif (in_array($col, $client)) {
               $where .= "c.{$col} = ?";
            }
            $params[] = array($val, 's');
            $i++;
         }
      }
      // var_dump($where);
      $sql= "SELECT cd.clientDocumentID, cd.DateAdded, cd.clientID, cd.clientDocumentName, cd.clientDocumentDescription, cd.clientDocumentFile, cd.documentTypeID, cd.documentFileName, cd.documentFileSize, cd.documentFileType, cd.documentFilePath, cd.LastUpdateByID, cd.LastUpdate, cd.Lapsed, cd.Suspended,
         dt.documentTypeID, dt.documentTypeName,
         c.clientName
      FROM tija_client_documents cd
      LEFT JOIN tija_document_types dt ON cd.documentTypeID = dt.documentTypeID
      LEFT JOIN tija_clients c ON cd.clientID = c.clientID
      {$where}
      ORDER BY cd.clientDocumentName ASC";
      $rows = $DBConn->fetch_all_rows($sql, $params);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }


} 