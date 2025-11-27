<?php
/*
 * This file is part of the Tija Project Management System.
 * 
 * (c) 2023 Tija Project Management System
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
class Work {
   public static function work_categories($whereArr, $single, $DBConn) {
       $cols = array('workCategoryID', 'DateAdded', 'workCategoryName', 'workCategoryCode', 'workCategoryDescription', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
       $rows = $DBConn->retrieve_db_table_rows('tija_work_categories', $cols, $whereArr);
       return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function work_types($whereArr, $single, $DBConn) {
      $where = '';
		$params = array();
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "h.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }
         $query = "SELECT h.workTypeID, h.DateAdded, h.workTypeName, h.workTypeCode, h.workTypeDescription, h.workCategoryID, c.workCategoryName, c.workCategoryCode, h.LastUpdate, h.LastUpdateByID, h.Lapsed, h.Suspended
         FROM tija_work_types h
         LEFT JOIN tija_work_categories c ON h.workCategoryID = c.workCategoryID
         {$where}
         ORDER BY h.workTypeID ASC";
         $rows = $DBConn->fetch_all_rows($query, $params);
         // if ($rows) {
         //    foreach ($rows as $key => $row) {
         //        $rows[$key]->workCategoryName = $row->workCategoryName;
         //        $rows[$key]->workCategoryCode = $row->workCategoryCode;
         //    }
         // }
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function work_segments($whereArr, $single, $DBConn) {
      $where = '';
        $params = array();
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "h.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }
         $query = "SELECT h.workSegmentID, h.DateAdded, h.workSegmentName, h.workSegmentCode, h.workSegmentDescription, h.LastUpdate, h.LastUpdateByID, h.Lapsed, h.Suspended
         FROM tija_pms_work_segment h
         
         {$where}
         ORDER BY h.workSegmentID ASC";
         $rows = $DBConn->fetch_all_rows($query, $params);
         // if ($rows) {
         //    foreach ($rows as $key => $row) {
         //        $rows[$key]->workCategoryName = $row->workCategoryName;
         //        $rows[$key]->workCategoryCode = $row->workCategoryCode;
         //    }
         // }
         return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
   
}
?>

