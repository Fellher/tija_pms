<?php
class Notifications {
   public static function user_notifications($whereArr, $single, $DBConn) {
      $params= array();
		$where= '';
		$rows=array();
      $notificationArr = array(
         'notificationID', 'DateAdded', 'employeeID', 'approverID', 'originatorUserID', 'targetUserID', 'segmentType', 'segmentID',  'notificationNotes', 'notificationType', 'emailed', 'notificationText', 'notificationStatus', 'timestamp', 'Lapsed', 'Suspended'

      );
      

      if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
                // Check if the column is in the people table
            if (in_array($col, $notificationArr)) {
               $where .= "n.{$col} = ?";
            } elseif ($col == 'originatorName') {
               $where .= "(e.FirstName LIKE ? OR e.OtherNames LIKE ? OR e.Surname LIKE ?)";
               $val = "%$val%";
               $params[] = array($val, 's');
               $params[] = array($val, 's');
               $params[] = array($val, 's');
               $i++;
               continue; // Skip the rest of the loop
            } elseif ($col == 'targetName') {
               $where .= "(e2.FirstName LIKE ? OR e2.OtherNames LIKE ? OR e2.Surname LIKE ?)";
               $val = "%$val%";
               $params[] = array($val, 's');
               $params[] = array($val, 's');
               $params[] = array($val, 's');
               $i++;
               continue; // Skip the rest of the loop
            } elseif ($col == 'approverName') {
               $where .= "(e3.FirstName LIKE ? OR e3.OtherNames LIKE ? OR e3.Surname LIKE ?)";
               $val = "%$val%";
               $params[] = array($val, 's');
               $params[] = array($val, 's');
               $params[] = array($val, 's');
               $i++;
               continue; // Skip the rest of the loop
            } else {
               // Invalid column, skip it
               continue;
            }              
				// $where .= "u.{$col} = ?";
				$params[] = array($val, 's');
				$i++;
			}
		}

      // var_dump($where);
      $query = "SELECT n.*,
                     CONCAT_WS(' ', e.FirstName, e.OtherNames, e.Surname) AS originatorName,
                     CONCAT_WS(' ', e2.FirstName, e2.OtherNames, e2.Surname) AS targetName,
                     CONCAT_WS(' ', e3.FirstName, e3.OtherNames, e3.Surname) as approverName
               FROM tija_notifications n
               LEFT JOIN people e ON n.originatorUserID = e.ID
               LEFT JOIN people e2 ON n.targetUserID = e2.ID
               LEFT JOIN people e3 ON n.approverID = e3.ID
               {$where}
               ORDER BY n.DateAdded DESC";
      // echo $query;
      $rows = $DBConn->fetch_all_rows($query,$params);

      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }
}