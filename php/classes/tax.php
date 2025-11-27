<?php 
/**
Tax class
*/
class Tax {
   // adjustment type function 
    public static function tax_adjustment_types ($whereArr, $single, $DBConn) {
        $cols = array('adjustmentTypeID', 'DateAdded', 'adjustmentType', 'adjustmentTypeDescription', 'Lapsed', 'Suspended', "LastUpdate");
        $rows = $DBConn->retrieve_db_table_rows('tija_tax_adjustment_types', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    public static function tax_adjustment_categories ($whereArr, $single, $DBConn) {
        $cols = array('adjustmentCategoryID', 'DateAdded', 'adjustmentCategoryName', 'adjustmentCategoryDescription', 'adjustmentTypeID', 'Lapsed', 'Suspended', "LastUpdate");
        $rows = $DBConn->retrieve_db_table_rows('tija_tax_adjustment_categories', $cols, $whereArr);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    public static function adjustment_accounts ($whereArr, $single, $DBConn) {
      $cols = array('adjustmentAccountsID', 'DateAdded', 'orgDataID', 'entityID', 'adjustmentTypeID', 'financialStatementAccountID', "financialStatementTypeID", "accountRate", 'Lapsed', 'Suspended', "LastUpdate");
      $rows = $DBConn->retrieve_db_table_rows('tija_tax_adjustments_accounts', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

    // Financial Statements types
   public static function financial_statements_types ($whereArr, $single, $DBConn) {
      $cols = array('financialStatementTypeID', 'DateAdded', 'financialStatementTypeName', 'financialStatementTypeDescription', 'statementTypeNode', 'LastUpdate', 'Lapsed', 'Suspended');
      $rows = $DBConn->retrieve_db_table_rows('tija_financial_statements_types', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  public static function financial_statement_accounts ($whereArr, $single, $DBConn) {
   $cols = array('financialStatementAccountID', 'DateAdded', 'accountNode', 'accountName', 'parentAccountID', 'accountCode', 'accountDescription', 'accountType', 'Lapsed', 'Suspended');
   $rows = $DBConn->retrieve_db_table_rows('tija_financial_statement_accounts', $cols, $whereArr);
   return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
}

  public static function financial_statementData ($whereArr, $single, $DBConn) {
      $cols = array('financialStatementDataID', 'DateAdded', 'orgDataID', 'entityID', 'financialStatementID', 'accountNode', 'accountName', 'accountCode', 'accountCategory', 'accountDescription', 'accountType', 'debitValue', 'creditValue', 'Lapsed', 'Suspended', 'LastUpdate');
      $rows = $DBConn->retrieve_db_table_rows('tija_financial_statement_data', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function financial_statements($whereArr, $single, $DBConn) {
      $cols = array('financialStatementID', 'DateAdded', 'orgDataID', 'entityID', 'financialStatementTypeID', 'financialStatementTypeName', 'fiscalYear', 'fiscalPeriod', 'statementTypeNode', 'Lapsed', 'Suspended', 'LastUpdate');
      $rows = $DBConn->retrieve_db_table_rows('tija_financial_statements', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  public static function statement_of_investment_allowance_accounts($whereArr, $single, $DBConn) {
      $cols = array('investmentAllowanceAccountID', 'DateAdded', 'accountName', 'parentAccountID', 'accountNode', 'accountCode', 'Lapsed', 'Suspended', 'LastUpdate');
      $rows = $DBConn->retrieve_db_table_rows('tija_statement_of_investment_allowance_accounts', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
   }

   public static function statement_of_investment_data($whereArr, $single, $DBConn) {
      $cols = array('InvestmentAllowanceID', 'DateAdded', 'orgDataID', 'entityID', 'financialStatementID', 'investmentName', 'rate', 'initialWriteDownValue', 'beginDate', 'additions', 'disposals', 'wearAndTearAllowance',  'endWriteDownValue', 'endDate',  'Lapsed', 'Suspended', 'LastUpdate', "allowInTotal");
      $rows = $DBConn->retrieve_db_table_rows('tija_statement_of_investment_allowance_data', $cols, $whereArr);
      return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
  }

  public static function trial_balance_mapped_accounts($whereArr, $single, $DBConn) {
   $cols = array('mappedAccountID', 'DateAdded', 'orgDataID', 'entityID', 'financialStatementID', 'financialStatementTypeID',  'statementTypeNode', 'financialStatementAccountID', 'financialStatementDataID', 'accountType',  'accountName', 'accountCategory', 'debitValue', 'creditValue', 'accountCode', 'categoryAccountCode', 'Lapsed', 'Suspended', 'LastUpdate');
   $rows = $DBConn->retrieve_db_table_rows('tija_trial_balance_mapped_accounts', $cols, $whereArr);
   return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
}

// Utility Methoods    
public static function account_node_short($str) {  
 // Remove all special characters and spaces
 $cleanedString = preg_replace('/[^a-zA-Z0-9]/', '', $str);
 return $cleanedString;
}

   // Utility Functions
   public static function nodes ($nodeName) {
      $nodeNameArr = explode(" ", Utility::clean_string($nodeName));
      $nodeItems = strtolower($nodeNameArr[0]);
      foreach ($nodeNameArr as $key => $node) {
         if($key > 0) {
          $addNode =strtolower($node);
          $nodeItems .="_{$addNode}"; 
         }
      }
      return $nodeItems;
  } 

public static function investment_accounts_mapping($whereArr, $single, $DBConn) {
   $cols = array('investmentMappedAccountID', 'DateAdded', 'orgDataID', 'entityID', 'investmentFinancialStatementID', 'InvestmentAllowanceID', 'investmentAllowanceAccountID', 'Lapsed', 'Suspended', 'LastUpdate');
   $rows = $DBConn->retrieve_db_table_rows('tija_investment_mapped_accounts', $cols, $whereArr);
   return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
}

public static function year_taxable_profit($whereArr, $single, $DBConn) {
   $cols = array('taxableProfitID', 'DateAdded', 'orgDataID', 'entityID', 'fiscalYear', 'taxableProfit', 'taxableProfitDescription', 'Lapsed', 'Suspended', 'LastUpdate');
   $rows = $DBConn->retrieve_db_table_rows('tija_taxable_profit', $cols, $whereArr);
   return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
}

public static function year_withholding_tax($whereArr, $single, $DBConn) {
   $cols = array('withholdingTaxID', 'DateAdded', 'orgDataID', 'entityID', 'fiscalYear', 'withholdingTax', 'withholdingTaxDescription', 'Lapsed', 'Suspended', 'LastUpdate');
   $rows = $DBConn->retrieve_db_table_rows('tija_withholding_tax', $cols, $whereArr);
   return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
}

public static function year_advance_tax($whereArr, $single, $DBConn) {
   $cols = array('advanceTaxID', 'DateAdded', 'orgDataID', 'entityID', 'fiscalYear', 'advanceTax', 'advanceTaxDescription', 'Lapsed', 'Suspended', 'LastUpdate');
   $rows = $DBConn->retrieve_db_table_rows('tija_advance_tax', $cols, $whereArr);
   return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
}


}