<?php

/*
 * This class is used to define the admin class
 *
 * @package    Admin
 * @subpackage Admin
 * @category   Admin
 * @version    1.0
 * @since      1.0
 */
class Admin {


    	/*Instance organisations Data
	================================*/
	public static function organisation_data_mini ($whereArr, $single, $DBConn){
		$cols = array("orgDataID", "DateAdded", "orgLogo", "orgName", 'industrySectorID', "numberOfEmployees", "registrationNumber", 'orgPIN ', "costCenterEnabled", "orgAddress", "orgPostalCode", "orgCity", "countryID", "orgPhoneNumber1","orgPhoneNUmber2", "orgEmail","LastUpdate", "LastUpdateByID",   "Lapsed", "Suspended" );
		$rows= $DBConn->retrieve_db_table_rows('tija_organisation_data', $cols, $whereArr);
		return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
	}

    public static function org_data($whereArr, $single, $DBConn){
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
                $where .= "o.{$col} = ?";
                $params[] = array($val, 's');
                $i++;
            }
        }
        $query = "SELECT o.*, i.industryTitle FROM tija_organisation_data o
        LEFT JOIN industry_sectors i ON o.industrySectorID = i.industrySectorID
        {$where}
        ORDER BY o.orgDataID DESC";


        $rows = $DBConn->fetch_all_rows($query, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_products($whereArr, $single, $DBConn){
        $cols = array("productID", "DateAdded", "productName", "productDescription", "LastUpdatedByID", "LastUpdated", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_products', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_permission_roles($whereArr, $single, $DBConn){
        $cols = array("permissionRoleID", "DateAdded", "permRoleTitle", "permRoleDescription", 'roleTypeID', 'permissionProfileID',  'permissionScopeID', 'importPermission', 'exportPermission', 'viewPermission',  'editPermission',  'addPermission', 'deletePermission',  "LastUpdatedByID", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_permission_roles', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_permission_roles_details ($whereArr, $single, $DBConn) {
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
       $query = "SELECT u.*, r.roleTypeTitle, p.permissionProfileTitle, s.permissionScopeTitle FROM tija_permission_roles u
        Left JOIN tija_role_types r ON u.roleTypeID = r.roleTypeID
        Left JOIN tija_permission_profiles p ON u.permissionProfileID = p.permissionProfileID
        Left JOIN tija_permission_scopes s ON u.permissionScopeID = s.permissionScopeID

        {$where}
        ORDER BY u.permissionRoleID ASC";
        $rows = $DBConn->fetch_all_rows($query, $params);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);

    }


    public static function tija_role_types($whereArr, $single, $DBConn){
        $cols = array("roleTypeID", "DateAdded", "roleTypeTitle", "roleTypeDescription", "LastUpdatedByID", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_role_types', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_permission_types($whereArr, $single, $DBConn){
        $cols = array("permissionTypeID", "DateAdded", "permissionTypeTitle", "permissionTypeDescription", "LastUpdatedByID", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_permission_types', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_permission_levels($whereArr, $single, $DBConn){
        $cols = array("permissionLevelID", "DateAdded", "permissionLevelTitle", "permissionLevelDescription", "iconClass", "LastUpdatedByID", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_permission_levels', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function permission_scope($whereArr, $single, $DBConn) {
        $cols = array("permissionScopeID", "DateAdded", "permissionScopeTitle", "permissionScopeDescription", "LastUpdatedByID", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_permission_scopes', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function permission_profile_types($whereArr, $single, $DBConn) {
        $cols = array("permissionProfileID", "DateAdded", "permissionProfileTitle", "permissionProfileDescription", "permissionProfileScopeID", "LastUpdatedByID", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_permission_profiles', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function tija_job_categories($whereArr, $single, $DBConn) {
        $cols = array("jobCategoryID", "DateAdded", "jobCategoryTitle", "jobCategoryDescription", "LastUpdatedByID", "LastUpdated", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_job_categories', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_employment_status($whereArr, $single, $DBConn) {
        $cols = array("employmentStatusID", "DateAdded", "employmentStatusTitle", "employmentStatusDescription", "LastUpdatedByID", "LastUpdated", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_employment_status', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_salary_components($whereArr, $single, $DBConn) {
        $cols = array("salaryComponentID", "DateAdded", "salaryComponentTitle", "salaryComponentDescription", "salaryComponentType", "salaryComponentValueType", "applyTo", 'salaryComponentCategoryID', "LastUpdatedByID", 'LastUpdatedByID', "LastUpdated", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_salary_components', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_salary_component_category($whereArr, $single, $DBConn) {
        $cols = array("salaryComponentCategoryID", "DateAdded", "salaryComponentCategoryTitle", "salaryComponentCategoryDescription", "LastUpdatedByID", "LastUpdated", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_salary_component_category', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_job_bands ($whereArr, $single, $DBConn) {
        $cols = array("jobBandID", "DateAdded", "jobBandTitle", "jobBandDescription", "LastUpdatedByID", "LastUpdated", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_job_bands', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function tija_job_titles($whereArr, $single, $DBConn) {
        $cols = array("jobTitleID", "DateAdded", "jobTitle", "jobCategoryID",  "jobDescription", "LastUpdatedByID", "jobDescriptionDoc", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_job_titles', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function admin_types($whereArr, $single, $DBConn) {
        $cols = array("adminTypeID", "DateAdded", "adminTypeName", "adminTypeDescription", "LastUpdateByID", "LastUpdate", "Lapsed", "Suspended");
        $rows = $DBConn->retrieve_db_table_rows('tija_admin_types', $cols, $whereArr);
        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get license types from database
     *
     * @param array $whereArr Where clause conditions
     * @param bool $single Return single record or array
     * @param object $DBConn Database connection
     * @return mixed Single object or array of objects, or false
     */
    public static function license_types($whereArr, $single, $DBConn) {
        $where = '';
        $params = array();

        if (count($whereArr) > 0) {
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= "{$col} = ?";
                $params[] = array($val, 's');
            }
        }

        $query = "SELECT * FROM tija_license_types {$where} ORDER BY displayOrder ASC, licenseTypeID ASC";
        $rows = $DBConn->fetch_all_rows($query, $params);

        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }


}?>
