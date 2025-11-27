<?php

/**
 * Data classes
 * classes that relate  to data that is universal to the instance
 * */
class Data {




    //Name prefixes function
    public static function prefixes ($whereArr, $single,$DBConn) {
        $cols = array('prefixID', 'DateAdded', 'prefixName', 'prefixDescription',  'LastUpdate', 'Lapsed', 'Suspended' );
        $rows = $DBConn->retrieve_db_table_rows ('tija_name_prefixes', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function industry_sectors ($whereArr, $single,$DBConn) {
        $cols = array('industrySectorID', 'DateAdded', 'industryTitle',  'Suspended' );
       $rows= $DBConn->retrieve_db_table_rows('industry_sectors', $cols, $whereArr);
       return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function users_offset ($whereArr, $offset, $limit, $DBConn) {
        $params= array();
        $where= '';
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "u.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }
        if (is_int($offset) && is_int($limit)) {
            $params[] = array($offset, 's');
            $params[] = array($limit, 's');
        }

        $sql = "SELECT
            u.ID,
            u.DateAdded,
            u.FirstName,
            u.Surname,
            u.OtherNames,
            u.Email,
            u.userInitials,
            u.profile_image,
            u.Valid,
            d.UID,
            d.DateAdded,
            d.orgDataID,
            d.entityID,
            d.prefixID,
            d.phoneNo,
            d.payrollNo,
            d.pin,
            d.dateOfBirth,
            d.gender,
            d.workTypeID,
            d.jobTitleID,
            d.jobCategoryID,
            d.jobBandID,
            d.employmentStatusID,
            d.payGradeID,
            d.employmentStartDate,
            d.employmentEndDate,
            d.costPerHour,
            d.dailyHours,
            d.overtimeAllowed,
            d.weekWorkDays,
            d.workHourRoundingID,
            d.setUpProfile,
            d.LastUpdatedByID,
            d.nationalID,
            d.nhifNumber,
            d.nssfNumber,
            d.basicSalary,
            d.bonusEligible,
            d.commissionEligible,
            d.commissionRate,
            d.LastUpdate,
            d.Lapsed,
            d.Suspended,
            e.entityName,
            e.entityTypeID,
            e.entityParentID,
            e.industrySectorID,
            e.registrationNumber,
            e.entityPIN,
            e.entityCity,
            e.entityCountry,
            e.entityPhoneNumber,
            e.entityEmail,
            o.orgName,
            p.prefixName,
            jt.jobTitle,
            jt.jobDescription,
        CONCAT(u.FirstName, ' ', u.Surname) AS employeeName,
        FROM people u
        LEFT JOIN user_details d ON u.ID = d.ID
        LEFT JOIN tija_entities e ON d.entityID = e.entityID
        LEFT JOIN tija_organisation_data o ON d.orgDataID = o.orgDataID
        LEFT JOIN tija_name_prefixes p ON d.prefixID = p.prefixID
        LEFT JOIN tija_job_titles jt ON d.jobTitleID = jt.jobTitleID
        {$where}
        ORDER BY u.DateAdded DESC
        LIMIT ?, ?";
            $rows = $DBConn->fetch_all_rows($sql,$params);
            return count($rows) > 0 ? $rows : false;
    }
    public static function users($whereArr, $single,$DBConn) {
        $params= array();
		$where= '';
		$rows=array();
        $people = array('ID', 'DateAdded', 'FirstName', 'Surname', 'userInitials', 'OtherNames', 'Email', 'profile_image', 'Valid');
        $userDetails = array('UID', 'DateAdded', 'orgDataID', 'entityID', 'prefixID', 'phoneNo', 'payrollNo', 'pin', 'dateOfBirth', 'gender', 'workTypeID', 'jobTitleID', 'jobCategoryID', 'jobBandID', 'employmentStatusID', 'payGradeID', 'employmentStartDate', 'employmentEndDate', 'costPerHour', 'dailyHours', 'overtimeAllowed', 'weekWorkDays', 'workHourRoundingID', 'setUpProfile', 'LastUpdatedByID', 'nationalID', 'nhifNumber', 'nssfNumber', 'basicSalary', 'bonusEligible', 'commissionEligible', 'commissionRate', 'LastUpdate', 'Lapsed', 'Suspended');
        $entity = array('entityName', 'entityTypeID', 'entityParentID', 'industrySectorID', 'registrationNumber', 'entityPIN', 'entityCity', 'entityCountry', 'entityPhoneNumber', 'entityEmail');
        $organisation = array('orgName');
        $prefix = array('prefixName');
        $jobTitle = array('jobTitle', 'jobDescription');


		if (count($whereArr) > 0) {
			$i = 0;
			foreach ($whereArr as $col => $val) {
				if ($where == '') {
					$where = "WHERE ";
				} else {
					$where .= " AND ";
				}
                // Check if the column is in the people table
                if (in_array($col, $people)) {
                    $where .= "u.{$col} = ?";
                } elseif (in_array($col, $userDetails)) {
                    $where .= "d.{$col} = ?";
                } elseif (in_array($col, $entity)) {
                    $where .= "e.{$col} = ?";
                } elseif (in_array($col, $organisation)) {
                    $where .= "o.{$col} = ?";
                } elseif (in_array($col, $prefix)) {
                    $where .= "p.{$col} = ?";
                } elseif (in_array($col, $jobTitle)) {
                    $where .= "jt.{$col} = ?";
                }
                else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
				// $where .= "u.{$col} = ?";
				$params[] = array($val, 's');
				$i++;
			}
		}

        // var_dump($whereArr);
        // var_dump($where);

        $sql = "SELECT
            u.ID,
            u.DateAdded,
            u.FirstName,
            u.Surname,
            u.OtherNames,
            u.userInitials,
            u.Email,
            u.profile_image,
            u.Valid,
            d.UID,
            d.DateAdded,
            d.orgDataID,
            d.entityID,
            d.prefixID,
            d.phoneNo,
            d.payrollNo,
            d.pin,
            d.dateOfBirth,
            d.gender,
            d.businessUnitID,
            d.supervisorID,
            d.supervisingJobTitleID,
            d.workTypeID,
            d.jobTitleID,
            d.jobCategoryID,
            d.jobBandID,
            d.employmentStatusID,
            d.payGradeID,
            d.employmentStartDate,
            d.employmentEndDate,
            d.costPerHour,
            d.dailyHours,
            d.overtimeAllowed,
            d.weekWorkDays,
            d.workHourRoundingID,
            d.setUpProfile,
            d.nationalID,
            d.nhifNumber,
            d.nssfNumber,
            d.basicSalary,
            d.profileImageFile,
            d.LastUpdatedByID,
            d.LastUpdate,
            d.Lapsed,
            d.Suspended,
            e.entityName,
            e.entityTypeID,
            e.entityParentID,
            e.industrySectorID,
            e.registrationNumber,
            e.entityPIN,
            e.entityCity,
            e.entityCountry,
            e.entityPhoneNumber,
            e.entityEmail,
            o.orgName,
            p.prefixName,
            jt.jobTitle,
            jt.jobDescription,
            CONCAT(u.FirstName, ' ', u.Surname) AS employeeName
        FROM people u
        LEFT JOIN user_details d ON u.ID = d.ID
        LEFT JOIN tija_entities e ON d.entityID = e.entityID
        LEFT JOIN tija_organisation_data o ON d.orgDataID = o.orgDataID
        LEFT JOIN tija_name_prefixes p ON d.prefixID = p.prefixID
        LEFT JOIN tija_job_titles jt ON d.jobTitleID = jt.jobTitleID
        {$where}
        ORDER BY  u.FirstName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);


        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }




    public static function units ($whereArr, $single,$DBConn) {
		$cols = array('unitID', 'DateAdded', 'unitCode',  'unitName',  'orgDataID', 'unitTypeID', 'headOfUnitID',  'parentUnitID', "entityID", 'unitDescription', 'LastUpdate','Lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_units', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

    public static function units_full ($whereArr, $single, $DBConn) {
        $params= array();
        $where= '';
        $rows=array();
        $unitsArr = array('unitID', 'DateAdded', 'unitCode',  'unitName',  'orgDataID', 'unitTypeID', 'headOfUnitID',  'parentUnitID', "entityID", 'unitDescription', 'LastUpdate','Lapsed', 'Suspended');
        $unitTypesArr = array('unitTypeID', 'DateAdded', 'unitTypeName', 'unitOrder', 'LastUpdate', 'Lapsed', 'Suspended');
        $entitiesArr = array('entityID', 'DateAdded', 'entityName', 'entityDescription', 'entityTypeID', 'orgDataID', 'entityParentID', 'industrySectorID', "registrationNumber", 'entityPIN ',  "entityCity", "entityCountry", "entityPhoneNumber", "entityEmail","LastUpdate", "LastUpdateByID",  "Lapsed", "Suspended");
        $organisationDataArr = array('orgDataID', 'DateAdded', 'orgName', 'orgDescription', 'orgTypeID', 'orgParentID', 'industrySectorID', "registrationNumber", 'entityPIN ',  "entityCity", "entityCountry", "entityPhoneNumber", "entityEmail","LastUpdate", "LastUpdateByID",  "Lapsed", "Suspended");
        $peopleArr = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'Email', 'profile_image', 'Valid');

        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                // Check if the column is in the units table
                if (in_array($col, $unitsArr)) {
                    $where .= "u.{$col} = ?";
                } elseif (in_array($col, $unitTypesArr)) {
                    $where .= "t.{$col} = ?";
                } elseif (in_array($col, $entitiesArr)) {
                    $where .= "e.{$col} = ?";
                } elseif (in_array($col, $organisationDataArr)) {
                    $where .= "o.{$col} = ?";
                } elseif (in_array($col, $peopleArr)) {
                    $where .= "p.{$col} = ?";
                }
                else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
                $params[] = array($val, 's');
                $i++;
            }
        }
        // if (is_int($offset) && is_int($limit)) {
        //     $params[] = array($offset, 's');
        //     $params[] = array($limit, 's');
        // }

       $sql = "SELECT
       u.unitID, u.DateAdded, u.unitCode, u.unitName, u.orgDataID, u.unitTypeID, t.unitTypeName, u.headOfUnitID,
       p.FirstName, p.Surname, u.parentUnitID, u.entityID, e.entityName, u.unitDescription, u.LastUpdate, u.Lapsed, u.Suspended,
       o.orgName,
       CONCAT(p.FirstName, ' ', p.Surname) AS headOfUnitName

       FROM tija_units u
       LEFT JOIN tija_unit_types t ON u.unitTypeID = t.unitTypeID
       LEFT JOIN tija_entities e ON u.entityID = e.entityID
       LEFT JOIN tija_organisation_data o ON u.orgDataID = o.orgDataID
       LEFT JOIN people p ON u.headOfUnitID = p.ID
       {$where}

        ORDER BY u.unitName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function unit_types ($whereArr, $single,$DBConn) {
        $cols = array('unitTypeID', 'DateAdded', 'unitTypeName', 'unitOrder', 'LastUpdate', 'Lapsed', 'Suspended' );
        $rows = $DBConn->retrieve_db_table_rows ('tija_unit_types', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    // unit assignment to employees
    public static function unit_user_assignments ($whereArr, $single,$DBConn) {
        $cols = array('unitAssignmentID', 'DateAdded', 'orgDataID', 'entityID',  'unitID', 'userID', 'unitTypeID','assignmentStartDate', 'assignmentEndDate', 'LastUpdate', 'Lapsed', 'Suspended' );
        $rows = $DBConn->retrieve_db_table_rows ('tija_user_unit_assignments', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function unit_user_assignments_full ($whereArr, $single,$DBConn) {
        $params= array();
        $where= '';
        $units =array('unitID', 'unitCode', 'unitName', 'unitTypeID', 'orgDataID', 'entityID', 'unitDescription');
        $users = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'Email', );
        $unitAssignments = array('unitAssignmentID', 'DateAdded', 'orgDataID', 'entityID',  'unitID', 'userID', 'unitTypeID','assignmentStartDate', 'assignmentEndDate', 'LastUpdate', 'Lapsed', 'Suspended' );
        $unitTypes = array('unitTypeID', 'DateAdded', 'unitTypeName' );
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                // Check if the column is in the people table
                if (in_array($col, $units)) {
                    $where .= "u.{$col} = ?";
                } elseif (in_array($col, $users)) {
                    $where .= "d.{$col} = ?";
                } elseif (in_array($col, $unitAssignments)) {
                    $where .= "ua.{$col} = ?";
                } elseif (in_array($col, $unitTypes)) {
                    $where .= "ut.{$col} = ?";
                }
                else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
                // $where .= "u.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }
        $sql = "SELECT ua.unitAssignmentID, ua.DateAdded, ua.orgDataID, ua.entityID,  ua.unitID, ua.userID, ua.unitTypeID, ua.assignmentStartDate, ua.assignmentEndDate, ua.LastUpdate, ua.Lapsed, ua.Suspended,
        u.unitCode, u.unitName, u.unitTypeID, u.orgDataID, u.entityID, u.unitDescription,
        e.entityName,
        p.ID, p.DateAdded, p.FirstName, p.Surname, p.OtherNames, p.Email,
        ut.unitTypeID, ut.DateAdded, ut.unitTypeName
        FROM tija_user_unit_assignments ua
        LEFT JOIN tija_units u ON ua.unitID = u.unitID
        LEFT JOIN tija_entities e ON u.entityID = e.entityID
        LEFT JOIN people p ON ua.userID = p.ID
        LEFT JOIN tija_unit_types ut ON u.unitTypeID = ut.unitTypeID
        {$where}
        ORDER BY u.unitName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    // unit assignments full
    // public static function unit_user_assignments_full ($whereArr, $single,$DBConn) {
    //     $params= array();
    //     $where= '';
    //     $units =array('unitID', 'unitCode', 'unitName', 'unitTypeID', 'orgDataID', 'entityID', 'unitDescription');
    //     $users = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'Email', );
    //     $unitAssignments = array('unitAssignmentID', 'DateAdded', 'orgDataID', 'entityID',  'unitID', 'userID', 'unitTypeID','assignmentStartDate', 'assignmentEndDate', 'LastUpdate', 'Lapsed', 'Suspended' );
    //     $unitTypes = array('unitTypeID', 'DateAdded', 'unitTypeName' );
    //     if (count($whereArr) > 0) {
    //         $i = 0;
    //         foreach ($whereArr as $col => $val) {
    //             if ($where == '') {
    //                 $where = "WHERE ";
    //             } else {
    //                 $where .= " AND ";
    //             }
    //             // Check if the column is in the people table
    //             if (in_array($col, $units)) {
    //                 $where .= "u.{$col} = ?";
    //             } elseif (in_array($col, $users)) {
    //                 $where .= "d.{$col} = ?";
    //             } elseif (in_array($col, $unitAssignments)) {
    //                 $where .= "ua.{$col} = ?";
    //             } elseif (in_array($col, $unitTypes)) {
    //                 $where .= "ut.{$col} = ?";
    //             }
    //             else {
    //                 // If the column is not found in any of the tables, you can choose to skip it or handle it differently
    //                 continue;
    //             }
    //             // $where .= "u.{$col} = ?";
    //             $params[] = array($val, 's');
    //             $i++;
    //         }
    //     }
    //     $sql = "SELECT ua.unitAssignmentID, ua.DateAdded, ua.orgDataID, ua.entityID,  ua.unitID, ua.userID, ua.unitTypeID, ua.assignmentStartDate, ua.assignmentEndDate, ua.LastUpdate, ua.Lapsed, ua.Suspended,
    //     u.unitCode, u.unitName, u.unitTypeID, u.orgDataID, u.entityID, u.unitDescription,
    //     e.entityName,
    //     p.ID, p.DateAdded, p.FirstName, p.Surname, p.OtherNames, p.Email,
    //     ut.unitTypeID, ut.DateAdded, ut.unitTypeName
    //     FROM tija_user_unit_assignments ua
    //     LEFT JOIN tija_units u ON ua.unitID = u.unitID
    //     LEFT JOIN tija_entities e ON u.entityID = e.entityID
    //     LEFT JOIN people p ON ua.userID = p.ID
    //     LEFT JOIN tija_unit_types ut ON u.unitTypeID = ut.unitTypeID
    //     {$where}
    //     ORDER BY u.unitName ASC";
    //     $rows = $DBConn->fetch_all_rows($sql,$params);
    //     return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    // }


    public static function entity_types ($whereArr, $single,$DBConn) {
        $cols = array('entityTypeID', 'DateAdded', 'entityTypeTitle', 'entityTypeDescription',  'LastUpdate', 'Lapsed', 'Suspended' );
        $rows = $DBConn->retrieve_db_table_rows ('tija_entity_types', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function entities($whereArr, $single,$DBConn) {
        $cols = array('entityID', 'DateAdded', 'entityName', 'entityDescription', 'entityTypeID', 'orgDataID', 'entityParentID', 'industrySectorID', "registrationNumber", 'entityPIN ',  "entityCity", "entityCountry", "entityPhoneNumber", "entityEmail","LastUpdate", "LastUpdateByID",  "Lapsed", "Suspended" );
        $rows = $DBConn->retrieve_db_table_rows ('tija_entities', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function entities_full($whereArr, $single, $DBConn) {
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
				$where .= "d.{$col} = ?";
				$params[] = array($val, 's');
				$i++;
			}
		}
        $sql = "SELECT d.entityID, d.DateAdded, d.entityName, d.entityDescription, d.entityTypeID,
        u.entityTypeTitle, d.orgDataID, o.orgName, d.entityParentID, d.industrySectorID, i.industryTitle, d.registrationNumber, d.entityPIN, d.entityCity, d.entityCountry, ac.countryName, d.entityPhoneNumber, d.entityEmail, d.LastUpdate, d.LastUpdateByID, d.Lapsed, d.Suspended, p.entityName AS parentEntityName
        FROM tija_entities d
        LEFT JOIN tija_entity_types u ON d.entityTypeID = u.entityTypeID
        LEFT JOIN industry_sectors i ON d.industrySectorID = i.industrySectorID
        LEFT JOIN african_countries ac ON d.entityCountry = ac.countryID
        LEFT JOIN tija_organisation_data o ON d.orgDataID = o.orgDataID
        LEFT JOIN tija_entities p ON d.entityParentID = p.entityID
        {$where}
        ORDER BY d.entityName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function countries($whereArr, $single,$DBConn) {
        $cols = array('countryID', 'DateAdded', 'countryName', 'countryCode',  'countryISO3Code', 'phoneCode', 'countryCapital', 'region', 'subregion', 'isActive', 'created_at', 'updated_at',);
        $rows = $DBConn->retrieve_db_table_rows ('african_countries', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    // Organisation chart data
    public static function org_charts($whereArr, $single, $DBConn) {
        $cols= array('orgChartID', 'DateAdded','orgChartName', 'orgChartDescription', 'chartType', 'effectiveDate', 'isCurrent', 'orgDataID', 'entityID', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_org_charts', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    // Organisation chart position Assignment
    public static function org_chart_position_assignments($whereArr, $single, $DBConn) {
        $cols= array('positionAssignmentID', 'DateAdded', 'orgDataID', 'orgChartID','entityID', 'positionID', 'positionTypeID', 'positionTitle', 'positionDescription', 'positionParentID', 'positionOrder', 'positionLevel', 'positionCode', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_org_chart_position_assignments', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function business_units($whereArr, $single, $DBConn) {
        $cols= array('businessUnitID', 'DateAdded', 'businessUnitName', 'businessUnitDescription', 'unitTypeID', 'categoryID', 'orgDataID', 'entityID',  'LastUpdate', 'LastUpdateByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_business_units', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get business unit categories
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function business_units_full($whereArr, $single, $DBConn) {
        $params= array();
        $where= '';
        $rows=array();
        $businessUnitArray = array('businessUnitID', 'DateAdded', 'businessUnitName', 'businessUnitDescription', 'unitTypeID', 'categoryID', 'orgDataID', 'entityID',  'LastUpdate', 'LastUpdateByID', 'Lapsed', 'Suspended');
        $categoryArray = array('categoryID', 'DateAdded', 'categoryName', 'categoryCode', 'categoryDescription', 'categoryOrder', 'iconClass', 'colorCode', 'isActive', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $unitTypesArray = array('unitTypeID', 'DateAdded', 'unitTypeName', 'unitOrder', 'LastUpdate', 'Lapsed', 'Suspended');
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if (in_array($col, $businessUnitArray)) {
                    $where .= "b.{$col} = ?";
                    $params[] = array($val, 'b');
                } elseif (in_array($col, $categoryArray)) {
                    $where .= "c.{$col} = ?";
                    $params[] = array($val, 'c');
                } elseif (in_array($col, $unitTypesArray)) {
                    $where .= "ut.{$col} = ?";
                    $params[] = array($val, 'ut');
                 } else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                 }
                $i++;
            }
        }
        $sql = "SELECT b.businessUnitID, b.DateAdded, b.businessUnitName, b.businessUnitDescription, b.unitTypeID, b.categoryID, b.orgDataID, b.entityID, b.LastUpdate, b.LastUpdateByID, b.Lapsed, b.Suspended, c.categoryName, c.categoryCode, c.categoryDescription, c.categoryOrder, c.iconClass, c.colorCode, c.isActive
        FROM tija_business_units b
        LEFT JOIN tija_business_unit_categories c ON b.categoryID = c.categoryID
        LEFT JOIN tija_unit_types ut ON b.unitTypeID = ut.unitTypeID
        {$where}
        ORDER BY b.businessUnitName ASC";

        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function business_unit_categories($whereArr, $single, $DBConn) {
        $cols = array('categoryID', 'DateAdded', 'categoryName', 'categoryCode', 'categoryDescription', 'categoryOrder', 'iconClass', 'colorCode', 'isActive', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_business_unit_categories', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get organizational roles
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function roles($whereArr, $single, $DBConn) {
        $params= array();
        $where= '';
        $roleTypesArray = array('roleTypeID', 'DateAdded', 'roleTypeName', 'roleTypeCode', 'roleTypeDescription', 'displayOrder', 'colorCode', 'iconClass', 'isDefault', 'isActive', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $roleLevelsArray = array('roleLevelID', 'DateAdded', 'levelNumber', 'levelName', 'levelCode', 'levelDescription', 'displayOrder', 'isDefault', 'isActive', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if(in_array($col, $roleTypesArray)) {
                    $where .= "rt.{$col} = ?";
                    $params[] = array($val, 's');
                } elseif(in_array($col, $roleLevelsArray)) {
                    $where .= "l.{$col} = ?";
                    $params[] = array($val, 's');
                } else {
                    $where .= "r.{$col} = ?";
                    $params[] = array($val, 's');
                }
                $i++;
            }
        }
        $sql = "SELECT r.roleID, r.DateAdded, r.roleName, r.roleCode, r.roleDescription, r.orgDataID, r.entityID, r.departmentID, r.unitID, r.parentRoleID,
                r.roleTypeID, r.roleLevelID,
                r.roleLevel, r.roleType,
                r.requiresApproval, r.canApprove, r.approvalLimit, r.reportsCount, r.iconClass, r.colorCode, r.isActive, r.LastUpdate, r.LastUpdatedByID, r.Lapsed, r.Suspended,
                rt.roleTypeID as roleType_roleTypeID, rt.roleTypeName, rt.roleTypeCode, rt.roleTypeDescription as roleTypeDescription,
                l.roleLevelID as roleLevel_roleLevelID, l.levelNumber, l.levelName, l.levelCode, l.levelDescription as roleLevelDescription
        FROM tija_roles r
        LEFT JOIN tija_org_role_types rt ON r.roleTypeID = rt.roleTypeID
        LEFT JOIN tija_role_levels l ON r.roleLevelID = l.roleLevelID
        {$where}
        ORDER BY r.roleName ASC";
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get role types
     * @param array $whereArr - WHERE conditions (e.g., ['isActive' => 'Y', 'Suspended' => 'N'])
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function role_types($whereArr, $single, $DBConn) {
        $cols = array('roleTypeID', 'DateAdded', 'roleTypeName', 'roleTypeCode', 'roleTypeDescription', 'displayOrder', 'colorCode', 'iconClass', 'isDefault', 'isActive', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_org_role_types', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get role levels
     * @param array $whereArr - WHERE conditions (e.g., ['isActive' => 'Y', 'Suspended' => 'N'])
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function role_levels($whereArr, $single, $DBConn) {
        $cols = array('roleLevelID', 'DateAdded', 'levelNumber', 'levelName', 'levelCode', 'levelDescription', 'displayOrder', 'isDefault', 'isActive', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_role_levels', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get reporting relationships
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function reporting_relationships($whereArr, $single, $DBConn) {
        $cols = array('relationshipID', 'DateAdded', 'employeeID', 'supervisorID', 'roleID', 'orgDataID', 'entityID', 'relationshipType', 'relationshipStrength', 'effectiveDate', 'endDate', 'isCurrent', 'reportingFrequency', 'canDelegate', 'canSubstitute', 'notes', 'createdBy', 'approvedBy', 'approvedDate', 'LastUpdate', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_reporting_relationships', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get delegation assignments
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function delegation_assignments($whereArr, $single, $DBConn) {
        $cols = array('delegationID', 'DateAdded', 'delegatorID', 'delegateID', 'orgDataID', 'entityID', 'delegationType', 'startDate', 'endDate', 'reason', 'approvalScope', 'financialLimit', 'isActive', 'approvedBy', 'approvedDate', 'LastUpdate', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_delegation_assignments', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function sales_status_levels($whereArr, $single, $DBConn) {
        $cols= array('saleStatusLevelID', 'DateAdded', 'statusLevel', 'statusOrder', 'StatusLevelDescription', 'orgDataID', 'entityID', 'levelPercentage', 'previousLevelID', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_sales_status_levels', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function lead_sources($whereArr, $single, $DBConn) {
        $cols = array('leadSourceID', 'DateAdded', 'leadSourceName', 'leadSourceDescription', 'orgDataID', 'entityID', 'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_lead_sources', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get job titles
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function job_titles($whereArr, $single, $DBConn) {
        $cols = array('jobTitleID', 'DateAdded', 'jobTitle', 'jobDescription', 'LastUpdate', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_job_titles', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get departments from tija_units where unitTypeID = 1 (Department type)
     * Returns units as departments with departmentID, departmentName, and departmentDescription
     * @param array $whereArr - WHERE conditions (e.g., ['entityID' => 1, 'Suspended' => 'N'])
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function departments($whereArr, $single, $DBConn) {
        // Use SQL query with JOIN to get head of unit information
        $whereClauses = array('u.unitTypeID = 1'); // Filter for departments
        $params = array();

        foreach ($whereArr as $key => $value) {
            if ($key !== 'unitTypeID') { // Already handled above
                $whereClauses[] = "u.{$key} = ?";
                $params[] = array($value, 's');
            }
        }

        $whereSQL = implode(' AND ', $whereClauses);

        $sql = "SELECT u.unitID, u.DateAdded, u.unitName, u.unitCode, u.unitDescription,
                       u.unitTypeID, u.orgDataID, u.entityID, u.headOfUnitID, u.Lapsed,
                       u.Suspended, u.LastUpdate,
                       CONCAT(p.FirstName, ' ', p.Surname) AS headName,
                       (SELECT COUNT(*) FROM user_details ud WHERE ud.departmentID = u.unitID) AS employeeCount
                FROM tija_units u
                LEFT JOIN people p ON u.headOfUnitID = p.ID
                WHERE {$whereSQL}
                ORDER BY u.unitName ASC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        // Transform the results to use department field names
        if (is_array($rows) && count($rows) > 0) {
            $departments = array();
            foreach ($rows as $row) {
                $dept = new stdClass();
                $dept->departmentID = $row->unitID;
                $dept->unitID = $row->unitID; // Keep original field name too
                $dept->DateAdded = $row->DateAdded;
                $dept->departmentName = $row->unitName;
                $dept->departmentCode = $row->unitCode;
                $dept->departmentDescription = $row->unitDescription;
                $dept->unitTypeID = $row->unitTypeID;
                $dept->orgDataID = $row->orgDataID;
                $dept->entityID = $row->entityID;
                $dept->headOfUnitID = $row->headOfUnitID;
                $dept->headName = $row->headName;
                $dept->employeeCount = $row->employeeCount;
                $dept->Lapsed = $row->Lapsed;
                $dept->Suspended = $row->Suspended;
                $dept->LastUpdate = $row->LastUpdate;
                $departments[] = $dept;
            }

            return ($single === true)
                ? ((count($departments) === 1) ? $departments[0] : false)
                : $departments;
        }

        return false;
    }

    /**
     * Get employment statuses
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function employment_statuses($whereArr, $single, $DBConn) {
        $cols = array('employmentStatusID', 'DateAdded', 'employmentStatusTitle', 'employmentStatusDescription', 'LastUpdated', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_employment_status', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get pay grades
     * @param array $whereArr - WHERE conditions (e.g., ['entityID' => 1, 'Suspended' => 'N'])
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function pay_grades($whereArr, $single, $DBConn) {

        $cols = array('payGradeID', 'DateAdded', 'orgDataID', 'entityID', 'payGradeCode', 'payGradeName',
                      'payGradeDescription', 'minSalary', 'midSalary', 'maxSalary', 'currency', 'gradeLevel',
                      'allowsOvertime', 'bonusEligible', 'commissionEligible', 'notes', 'createdBy',
                      'updatedBy', 'LastUpdate', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_pay_grades', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get employee count based on various criteria
     * @param array $whereArr - WHERE conditions (e.g., ['payGradeID' => 5, 'entityID' => 1])
     * @param object $DBConn - Database connection
     * @return int - Count of employees matching criteria
     */
    public static function employee_count($whereArr, $DBConn) {
        $sql = "SELECT COUNT(*) as count FROM user_details WHERE Suspended = 'N'";

        // Build WHERE clause
        if (is_array($whereArr) && count($whereArr) > 0) {
            foreach ($whereArr as $key => $value) {
                if ($key !== 'Suspended') { // Already added
                    if (is_numeric($value)) {
                        $sql .= " AND {$key} = {$value}";
                    } else {
                        $sql .= " AND {$key} = '{$value}'";
                    }
                }
            }
        }

        $DBConn->query($sql);
        $DBConn->execute();
        $result = $DBConn->single();

        return $result ? (int)$result->count : 0;
    }


    /**
     * Pay grade to job title mapping
     * @param array $whereArr - WHERE conditions (e.g., ['entityID' => 1, 'Suspended' => 'N'])
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function job_title_pay_grade_mapping($whereArr, $single, $DBConn) {
        $params = array();
        $where = '';
        $payGradeMappingArr = array('mappingID', 'DateAdded', 'jobTitleID', 'payGradeID', 'effectiveDate', 'endDate', 'isCurrent', 'createdBy', 'updatedBy', 'LastUpdate', 'Lapsed', 'Suspended');
        $jobTitleArr = array('jobTitleID', 'jobTitle', 'jobDescription', 'LastUpdate', 'Lapsed', 'Suspended');
        $payGradeArr = array('payGradeID', 'DateAdded', 'orgDataID', 'entityID', 'payGradeCode', 'payGradeName', 'payGradeDescription', 'minSalary', 'midSalary', 'maxSalary', 'currency', 'gradeLevel', 'allowsOvertime', 'bonusEligible', 'commissionEligible', 'notes', 'createdBy', 'updatedBy', 'LastUpdate', 'Lapsed', 'Suspended');
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if (in_array($col, $payGradeMappingArr)) {
                    $where .= "jtpg.{$col} = ?";
                } elseif (in_array($col, $jobTitleArr)) {
                    $where .= "jt.{$col} = ?";
                } elseif (in_array($col, $payGradeArr)) {
                    $where .= "pg.{$col} = ?";
                }
                else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
                $params[] = array($val, 's');
                $i++;
            }
        }
        $sql = "SELECT jtpg.mappingID, jtpg.DateAdded, jtpg.jobTitleID, jtpg.payGradeID, jtpg.effectiveDate, jtpg.endDate, jtpg.isCurrent, jtpg.createdBy, jtpg.updatedBy, jtpg.LastUpdate, jtpg.Suspended
        FROM tija_job_title_pay_grade jtpg
        LEFT JOIN tija_job_titles jt ON jtpg.jobTitleID = jt.jobTitleID
        LEFT JOIN tija_pay_grades pg ON jtpg.payGradeID = pg.payGradeID
        {$where}
        ORDER BY jtpg.effectiveDate ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

    }

    /**
     * ==========================================
     * SALARY COMPONENTS MANAGEMENT
     * ==========================================
     */

    /**
     * Get salary component categories
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function salary_component_categories($whereArr, $single, $DBConn) {
        $cols = array('salaryComponentCategoryID', 'DateAdded', 'orgDataID', 'entityID', 'categoryCode',
                      'salaryComponentCategoryTitle', 'salaryComponentCategoryDescription', 'categoryType',
                      'isSystemCategory', 'sortOrder', 'LastUpdatedByID', 'LastUpdated', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_salary_component_category', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get salary components
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function salary_components($whereArr, $single, $DBConn) {
        $cols = array('salaryComponentID', 'DateAdded', 'orgDataID', 'entityID', 'componentCode',
                      'salaryComponentTitle', 'salaryComponentDescription', 'salaryComponentCategoryID',
                      'salaryComponentType', 'salaryComponentValueType', 'defaultValue', 'calculationFormula',
                      'applyTo', 'isStatutory', 'isMandatory', 'isVisible', 'isTaxable', 'isProrated',
                      'affectsGross', 'affectsNet', 'minimumValue', 'maximumValue', 'effectiveDate',
                      'expiryDate', 'payrollFrequency', 'eligibilityCriteria', 'notes', 'sortOrder',
                      'LastUpdatedByID', 'LastUpdated', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_salary_components', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get salary components with category information (JOIN)
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function salary_components_with_category($whereArr, $single, $DBConn) {
        $params = array();
        $where = '';

        $componentCols = array('salaryComponentID', 'orgDataID', 'entityID', 'componentCode',
                              'salaryComponentTitle', 'salaryComponentDescription', 'salaryComponentCategoryID',
                              'salaryComponentType', 'salaryComponentValueType', 'defaultValue',
                              'applyTo', 'isStatutory', 'isMandatory', 'isTaxable', 'sortOrder', 'Suspended');

        $categoryCols = array('salaryComponentCategoryTitle', 'categoryCode', 'categoryType');

        if (count($whereArr) > 0) {
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (in_array($col, $componentCols)) {
                    $where .= "sc.{$col} = ?";
                } elseif (in_array($col, $categoryCols)) {
                    $where .= "cat.{$col} = ?";
                } else {
                    continue;
                }
                $params[] = array($val, 's');
            }
        }

        $sql = "SELECT
                    sc.salaryComponentID, sc.componentCode, sc.salaryComponentTitle,
                    sc.salaryComponentDescription, sc.salaryComponentType, sc.salaryComponentValueType,
                    sc.defaultValue, sc.applyTo, sc.isStatutory, sc.isMandatory, sc.isTaxable,
                    sc.isVisible, sc.sortOrder, sc.entityID, sc.orgDataID,
                    cat.salaryComponentCategoryID, cat.salaryComponentCategoryTitle,
                    cat.categoryCode, cat.categoryType
                FROM tija_salary_components sc
                INNER JOIN tija_salary_component_category cat ON sc.salaryComponentCategoryID = cat.salaryComponentCategoryID
                {$where}
                ORDER BY cat.sortOrder, sc.sortOrder";

        $rows = $DBConn->fetch_all_rows($sql, $params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get employee salary component assignments
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function employee_salary_components($whereArr, $single, $DBConn) {
        $cols = array('employeeComponentID', 'DateAdded', 'employeeID', 'salaryComponentID',
                      'componentValue', 'valueType', 'applyTo', 'effectiveDate', 'endDate',
                      'isCurrent', 'isActive', 'frequency', 'oneTimePayrollDate', 'notes',
                      'assignedBy', 'assignedAt', 'updatedBy', 'updatedAt', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_employee_salary_components', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    /**
     * GET salary component assignment with ability to get the component details
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function employee_salary_components_with_component_details($whereArr, $single, $DBConn) {
        $where = '';
        $params = array();
        $employeeComponentCols = array('employeeComponentID', 'DateAdded', 'employeeID', 'salaryComponentID',
                                      'componentValue', 'valueType', 'applyTo', 'effectiveDate', 'endDate',
                                      'isCurrent', 'isActive', 'frequency', 'oneTimePayrollDate', 'notes',
                                      'assignedBy', 'assignedAt', 'updatedBy', 'updatedAt', 'Suspended');
        $salaryComponentCols = array('salaryComponentID', 'componentCode', 'salaryComponentTitle',
                                      'salaryComponentDescription', 'salaryComponentType', 'isStatutory',
                                      'isTaxable', 'sortOrder', 'Suspended');
        $salaryComponentCategoryCols = array('salaryComponentCategoryTitle', 'categoryCode', 'categoryType');
        if (count($whereArr) > 0) {
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if (in_array($col, $employeeComponentCols)) {
                    $where .= "esc.{$col} = ?";
                } elseif (in_array($col, $salaryComponentCols)) {
                    $where .= "sc.{$col} = ?";
                } elseif (in_array($col, $salaryComponentCategoryCols)) {
                    $where .= "cat.{$col} = ?";
                }
                $params[] = array($val, 's');
            }
        }
        $sql = "SELECT
                    esc.employeeComponentID,
                    esc.DateAdded,
                    esc.employeeID,
                    esc.salaryComponentID,
                    esc.componentValue,
                    esc.valueType,
                    esc.applyTo,
                    esc.effectiveDate,
                    esc.endDate,
                    esc.isCurrent,
                    esc.isActive,
                    esc.frequency,
                    esc.oneTimePayrollDate,
                    esc.notes,
                    esc.assignedBy,
                    esc.assignedAt,
                    esc.updatedBy,
                    esc.updatedAt,
                    esc.Suspended,
                    sc.salaryComponentID,
                    sc.componentCode,
                    sc.salaryComponentTitle,
                    sc.salaryComponentDescription,
                    sc.salaryComponentType,
                    sc.isStatutory,
                    sc.isTaxable,
                    sc.sortOrder,
                    cat.salaryComponentCategoryTitle,
                    cat.categoryType,
                    cat.categoryCode
                FROM tija_employee_salary_components esc
                INNER JOIN tija_salary_components sc ON esc.salaryComponentID = sc.salaryComponentID
                INNER JOIN tija_salary_component_category cat ON sc.salaryComponentCategoryID = cat.salaryComponentCategoryID
                {$where}
                ORDER BY cat.sortOrder, sc.sortOrder";
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get employee salary components with full details (JOIN)
     * @param int $employeeID - Employee ID
     * @param object $DBConn - Database connection
     * @return mixed - Array of component assignments with details or false
     */
    public static function employee_salary_components_detailed($employeeID, $DBConn) {
        $sql = "SELECT
                    esc.employeeComponentID,
                    esc.employeeID,
                    esc.componentValue,
                    esc.valueType,
                    esc.applyTo,
                    esc.effectiveDate,
                    esc.endDate,
                    esc.isCurrent,
                    esc.isActive,
                    esc.frequency,
                    sc.salaryComponentID,
                    sc.componentCode,
                    sc.salaryComponentTitle,
                    sc.salaryComponentDescription,
                    sc.salaryComponentType,
                    sc.isStatutory,
                    sc.isTaxable,
                    sc.sortOrder,
                    cat.salaryComponentCategoryTitle,
                    cat.categoryType
                FROM tija_employee_salary_components esc
                INNER JOIN tija_salary_components sc ON esc.salaryComponentID = sc.salaryComponentID
                INNER JOIN tija_salary_component_category cat ON sc.salaryComponentCategoryID = cat.salaryComponentCategoryID
                WHERE esc.employeeID = ?
                  AND esc.isCurrent = 'Y'
                  AND esc.isActive = 'Y'
                  AND esc.Suspended = 'N'
                  AND sc.Suspended = 'N'
                  AND (esc.endDate IS NULL OR esc.endDate >= CURDATE())
                ORDER BY sc.salaryComponentType DESC, cat.sortOrder, sc.sortOrder";

        $params = array(array($employeeID, 'i'));
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return (is_array($rows) && count($rows) > 0) ? $rows : false;
    }

    /**
     * Get salary component history
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function salary_component_history($whereArr, $single, $DBConn) {
        $cols = array('historyID', 'DateAdded', 'salaryComponentID', 'changeType', 'fieldChanged',
                      'oldValue', 'newValue', 'changedBy', 'changeReason', 'changeDate');
        $rows = $DBConn->retrieve_db_table_rows('tija_salary_component_history', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get payroll computation rules
     * @param array $whereArr - WHERE conditions
     * @param bool $single - Return single record or multiple
     * @param object $DBConn - Database connection
     * @return mixed - Array of records or single record or false
     */
    public static function payroll_computation_rules($whereArr, $single, $DBConn) {
        $cols = array('ruleID', 'DateAdded', 'orgDataID', 'entityID', 'ruleName', 'ruleDescription',
                      'ruleType', 'computationFormula', 'parameters', 'effectiveDate', 'expiryDate',
                      'isActive', 'priority', 'createdBy', 'updatedBy', 'LastUpdated', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows('tija_payroll_computation_rules', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Calculate employee total earnings
     * @param int $employeeID - Employee ID
     * @param float $basicSalary - Basic salary amount
     * @param object $DBConn - Database connection
     * @return array - Array with breakdown of earnings
     */
    public static function calculate_employee_earnings($employeeID, $basicSalary, $DBConn) {
        $components = self::employee_salary_components_detailed($employeeID, $DBConn);

        $earnings = array(
            'basic_salary' => $basicSalary,
            'allowances' => 0,
            'bonuses' => 0,
            'total_earnings' => $basicSalary,
            'components' => array()
        );

        if ($components) {
            foreach ($components as $component) {
                if ($component->salaryComponentType == 'earning') {
                    $amount = 0;

                    if ($component->valueType == 'fixed') {
                        $amount = $component->componentValue;
                    } elseif ($component->valueType == 'percentage') {
                        $base = $basicSalary; // Default to basic salary
                        if ($component->applyTo == 'gross_salary') {
                            $base = $earnings['total_earnings'];
                        }
                        $amount = ($base * $component->componentValue) / 100;
                    }

                    $earnings['allowances'] += $amount;
                    $earnings['total_earnings'] += $amount;
                    $earnings['components'][] = array(
                        'componentID' => $component->salaryComponentID,
                        'title' => $component->salaryComponentTitle,
                        'amount' => $amount,
                        'type' => 'earning'
                    );
                }
            }
        }

        return $earnings;
    }

    /**
     * Calculate employee total deductions
     * @param int $employeeID - Employee ID
     * @param float $grossSalary - Gross salary amount
     * @param object $DBConn - Database connection
     * @return array - Array with breakdown of deductions
     */
    public static function calculate_employee_deductions($employeeID, $grossSalary, $DBConn) {
        $components = self::employee_salary_components_detailed($employeeID, $DBConn);

        $deductions = array(
            'statutory' => 0,
            'loans' => 0,
            'other' => 0,
            'total_deductions' => 0,
            'components' => array()
        );

        if ($components) {
            foreach ($components as $component) {
                if ($component->salaryComponentType == 'deduction') {
                    $amount = 0;

                    if ($component->valueType == 'fixed') {
                        $amount = $component->componentValue;
                    } elseif ($component->valueType == 'percentage') {
                        $base = $grossSalary; // Default to gross salary
                        $amount = ($base * $component->componentValue) / 100;
                    }

                    if ($component->isStatutory == 'Y') {
                        $deductions['statutory'] += $amount;
                    } else {
                        $deductions['other'] += $amount;
                    }

                    $deductions['total_deductions'] += $amount;
                    $deductions['components'][] = array(
                        'componentID' => $component->salaryComponentID,
                        'title' => $component->salaryComponentTitle,
                        'amount' => $amount,
                        'type' => 'deduction',
                        'isStatutory' => $component->isStatutory
                    );
                }
            }
        }

        return $deductions;
    }

    		/*==========================================
  Expenses
  ===========================================*/

  public static function expense_types ($whereArr, $single, $DBConn) {
    $cols = array('expenseTypeID', 'DateAdded', 'typeName',   'Lapsed', 'Suspended');
    $rows= $DBConn->retrieve_db_table_rows('tija_expense_types', $cols, $whereArr);
  	return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  	/* ===============================
	WORK TYPES
	=================================*/

	public static function work_types ($whereArr, $single, $DBConn) {
		$cols = array('workTypeID', 'DateAdded', 'workTypeName', 'workTypeDescription', 'LastUpdate', 'lapsed', 'Suspended');
		$rows = $DBConn->retrieve_db_table_rows ('tija_work_types', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

    // public static function holidays ($whereArr, $single, $DBConn) {
    //     $cols = array('holidayID', 'DateAdded', 'holidayName', 'holidayDate', 'holidayType', 'countryID', 'repeatsAnnually', 'LastUpdate', 'Lapsed', 'Suspended');
    //     $rows = $DBConn->retrieve_db_table_rows ('tija_holidays', $cols, $whereArr);
    //     return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    // }

    public static function holidays ($whereArr, $single, $DBConn) {
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
        $sql = "SELECT h.holidayID, h.DateAdded, h.holidayName, h.holidayDate, h.holidayType, h.countryID, c.countryName, h.repeatsAnnually, h.LastUpdate, h.Lapsed, h.Suspended
        FROM tija_holidays h
        LEFT JOIN african_countries c ON h.countryID = c.countryID
        {$where}
        ORDER BY h.holidayDate ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

    }
    public static function tija_sectors($whereArr, $single, $DBConn){
        $cols = array('sectorID', 'DateAdded', 'sectorName', 'sectorDescription', 'LastUpdate', 'LastUpdateByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows ('tija_industry_sectors', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_industry($whereArr, $single, $DBConn){
        $where = '';
		$params = array();
        $industry = array('industryID', 'DateAdded', 'industryName', 'industryDescription', 'LastUpdate', 'sectorID', 'LastUpdateByID', 'Lapsed', 'Suspended');
        $sectors = array('sectorID',  'sectorName', 'sectorDescription' );
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
               if (in_array($col, $industry)) {
                    $where .= "i.{$col} = ?";
                } elseif (in_array($col, $sectors)) {
                    $where .= "s.{$col} = ?";
                }
                else {
                    // If the column is not found in any of the tables, you can choose to skip it or handle it differently
                    continue;
                }
                $params[] = array($val, 's');
                $i++;
            }
        }
        $sql = "SELECT i.industryID, i.DateAdded, i.industryName, i.industryDescription, i.LastUpdate, i.LastUpdateByID, i.Lapsed, i.Suspended,
        s.sectorID, s.sectorName, s.sectorDescription
        FROM tija_industries i
        LEFT JOIN tija_industry_sectors s ON i.sectorID = s.sectorID
        {$where}
        ORDER BY i.industryName ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

    }

    public static function document_types($whereArr, $single, $DBConn){
        $cols = array('documentTypeID', 'DateAdded', 'documentTypeName', 'documentTypeDescription', 'LastUpdate', 'LastUpdateByID', 'Lapsed', 'Suspended');
        $rows = $DBConn->retrieve_db_table_rows ('tija_document_types', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }


}?>