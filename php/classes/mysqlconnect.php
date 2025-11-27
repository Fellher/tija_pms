<?php

/**
  *
  *  PDO Database Class
  * Connect to database
  * Create prepared statements
  * Bind values
  * Return rows and results
  */
 class mysqlConnect {
	public $numRows, $lastInsertID, $affectedRows, $userName, $password, $hostName, $databaseName;
	private $dbh;
	private $stmt;
	private $error;

	public function __construct ($userName="", $password="", $host="", $database) {
		if ($userName != "" && $password != "" && $host != "" && $database != "") {
		  $this->userName = $userName;
		  $this->password = $password;
		  $this->hostName = $host;
		  $this->databaseName = $database;
		  $this->stmt = null; // Initialize statement to null
		} else {
		  return;
		}
	}

	public function connect () {
		// Set DSN
		$dsn = 'mysql:host=' . $this->hostName . ';dbname=' . $this->databaseName . ';charset=utf8mb4';
		$options = array(
		  PDO::ATTR_PERSISTENT => true,
		  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		  PDO::ATTR_EMULATE_PREPARES=>false,
		  PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
		);
		// Create PDO instance
		try{
		  $this->dbh = new PDO($dsn, $this->userName, $this->password, $options);
		} catch(PDOException $e){
		  $this->error = $e->getMessage();
		  echo $this->error;
		}
	}

	public function begin(){
		return $this->dbh->beginTransaction();
	}

	public function commit(){
		return $this->dbh->commit();
	}

	public function rollBack(){
		return $this->dbh->rollBack();
	}

	// Prepare statement with query
	public function query($sql){
		// Close any previous cursor to prevent "unbuffered queries" error
		if ($this->stmt !== null) {
			try {
				$this->stmt->closeCursor();
			} catch (PDOException $e) {
				// Cursor was already closed or never opened, ignore
			}
		}
		$this->stmt = $this->dbh->prepare($sql);
	}


	// Bind values
	public function bind($param, $value, $type = null){
		if(is_null($type)){
		  	switch(true){
			 	case is_int($value):
					$type = PDO::PARAM_INT;
				break;
			 	case is_bool($value):
					$type = PDO::PARAM_BOOL;
				break;
			 	case is_null($value):
					$type = PDO::PARAM_NULL;
				break;
			 	default:
					$type = PDO::PARAM_STR;
		  	}

		}
		$this->stmt->bindValue($param, $value, $type);
	}

	// Execute the prepared statement
	public function execute(){
		return $this->stmt->execute();
	}

	// Execute the prepared statement with distinct array as attribute
	public function execute_1($executeArray){
		return $this->stmt->execute($executeArray);
	}

	// Get result set as array of objects
	public function resultSet(){
		$this->execute();
		return $this->stmt->fetchAll(PDO::FETCH_OBJ);
	}

		// Get result set as array of Arr
	public function resultSetArr(){
		$this->execute();
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Get single record as object
	public function single(){
		$this->execute();
		$result = $this->stmt->fetch(PDO::FETCH_OBJ);
		// Close cursor to free up the connection for next query
		$this->stmt->closeCursor();
		return $result;
	}

	// Get row count
	public function rowCount(){
		return $this->stmt->rowCount();
	}
	// Get last insert Data
	public function lastInsertId(){
		return $this->dbh->lastInsertId();
	}

	public function insert_id(){
		return $this->dbh->lastInsertId();
	}
	// Get result set as array of objects
	public function resultSet_1($executeArray){
		$this->execute($executeArray);
		return $this->stmt->fetchAll(PDO::FETCH_OBJ);
	}

	/*CHECK */
// Bind values
	public function bind_eval($param, $value, $type = null){
		if(is_null($type)){
		  	switch(true){
			 	case is_int($value):
					$type = PDO::PARAM_INT;
				break;
			 	case is_bool($value):
					$type = PDO::PARAM_BOOL;
				break;
			 	case is_null($value):
					$type = PDO::PARAM_NULL;
				break;
			 	default:
					$type = PDO::PARAM_STR;
		  	}
		  	var_dump($param);
		  	var_dump($value);
		  	var_dump($type);
		}
		$this->stmt->bindValue($param, $value, $type);
	}

	public function retrieve_db_table_rows_eval($table, $requiredCols, $where) {
		$query = "SELECT ";
		foreach ($requiredCols as $col) {
			$query .= "{$col}, ";
		}
		$query = trim($query, ' ,') . " FROM $table";
		$whereStr = '';
		$params=array();
		if ($where) {

		  	foreach ($where as $col=>$val) {
			 	if ($val == 'NULL' || $val == 'null') {
					$whereStr .= ($whereStr == '' ? '' : ' AND ') . "ISNULL(`{$col}`) ";
			 	} else {
					$whereStr .= ($whereStr == '' ? '' : ' AND ') . "{$col} = :{$col}";
					$params[] = array($col, $val);// "`{$col}` , :{$val}";
			 	}
		  	}
		}
		if ($whereStr !== '') {
			$query=("{$query}  WHERE {$whereStr}");
		} else {
			$query=("{$query} ");
		}



		$this->query("{$query}");
		if (count($params) >0) {
		  	foreach ($params as $key => $value) {
			 	$bindKey= $value[0];
			 	$bindVal= $value[1];

			 	$this->bind(":{$bindKey}", $bindVal);
		  	}
		}



		return $result= $this->resultSet();
	}

	// cusom functions
	// retrieve rows Obj
	public function retrieve_db_table_rows($table, $requiredCols, $where) {
		$query = "SELECT ";
		foreach ($requiredCols as $col) {
			$query .= "{$col}, ";
		}
		$query = trim($query, ' ,') . " FROM $table";
		$whereStr = '';
		$params=array();
		if ($where) {

		  	foreach ($where as $col=>$val) {
			 	if ($val == 'NULL' || $val == 'null') {
					$whereStr .= ($whereStr == '' ? '' : ' AND ') . "ISNULL(`{$col}`) ";
			 	} else {
					$whereStr .= ($whereStr == '' ? '' : ' AND ') . "{$col} = :{$col}";
					$params[] = array($col, $val);// "`{$col}` , :{$val}";
			 	}
		  	}
		}
		if ($whereStr !== '') {
			$query=("{$query}  WHERE {$whereStr}");
		} else {
			$query=("{$query} ");
		}
		$this->query("{$query}");
		if (count($params) >0) {
		  	foreach ($params as $key => $value) {
			 	$bindKey= $value[0];
			 	$bindVal= $value[1];

			 	$this->bind(":{$bindKey}", $bindVal);
		  	}
		}
		return $result= $this->resultSet();
	}

	// retrieve rows array
	public function retrieve_db_table_rows_arr($table, $requiredCols, $where) {
		$query = "SELECT ";
		foreach ($requiredCols as $col) {
			$query .= "{$col}, ";
		}
		$query = trim($query, ' ,') . " FROM $table";
		$whereStr = '';
		$params=array();
		if ($where) {

		  	foreach ($where as $col=>$val) {
			 	if ($val == 'NULL' || $val == 'null') {
					$whereStr .= ($whereStr == '' ? '' : ' AND ') . "ISNULL(`{$col}`) ";
			 	} else {
					$whereStr .= ($whereStr == '' ? '' : ' AND ') . "{$col} = :{$col}";
					$params[] = array($col, $val);// "`{$col}` , :{$val}";
			 	}
		  	}
		}

		if ($whereStr !== '') {
			$query=("{$query}  WHERE {$whereStr}");
		} else {
			$query=("{$query} ");
		}
		$this->query("{$query}");

		if (count($params) >0) {
		  	foreach ($params as $key => $value) {
			 	$bindKey= $value[0];
			 	$bindVal= $value[1];
			 	$this->bind(":{$bindKey}", $bindVal);
		  	}
		}
		return $result= $this->resultSetArr();
	}

/*
	FETCCH ALL ROWS WITH LIMITS AS OBJECT
*/
	public function fetch_all_table_rows( $query, $params, $limit='',$offset='') {
		$this->query($query);
		$s=0;
		if ($params && count($params) >0) {
		  	foreach ($params as $k => $paramVal) {
			 	$s++ ;
			 	$this->bind("{$s}", $paramVal[0] );
		  	}
		}
		if (!empty($offset)) {
		  	$s++;
		  	$this->bind("{$s}", (int)$offset, PDO::PARAM_INT);
		} else {
			$s++;

			$this->bind("{$s}", 0 , PDO::PARAM_INT);
		}
		if (!empty($limit)) {
		  	$s++;

			$this->bind("{$s}", (int)$limit, PDO::PARAM_INT);
		}  else {
			$s++;

				$this->bind("{$s}", 99999, PDO::PARAM_INT);
		}
		 $result= $this->resultSet();

		 return $result ? $result : false;
	}

	/*
	FETCCH ALL ROWS WITH LIMITS AS array
*/
	public function fetch_all_table_rows_arr( $query, $params, $limit='',$offset='') {
		$this->query($query);
		$s=0;
		if ($params && count($params) >0) {
		  	foreach ($params as $k => $paramVal) {
			 	$s++ ;

			 	$this->bind("{$s}", $paramVal[0] );
		  	}
		}

		if (!empty($offset)) {
		  	$s++;
		  	$this->bind("{$s}", (int)$offset, PDO::PARAM_INT);
		} else {
			$s++;

			$this->bind("{$s}", 0 , PDO::PARAM_INT);
		}
		if (!empty($limit)) {
		  	$s++;

			$this->bind("{$s}", (int)$limit, PDO::PARAM_INT);
		}  else {
			$s++;

				$this->bind("{$s}", 99999, PDO::PARAM_INT);
		}
		 $result= $this->resultSetArr();

		 return $result ? $result : false;
	}

	/*
		FETCH ALL ROWS WITHOUT LIMITS AS OBJECT
	*/

	public function fetch_all_rows( $query, $params) {
		$this->query($query);
		$s=0;

		if ($params && count($params) >0) {
		  	foreach ($params as $k => $paramVal) {
			 	$s++ ;

			 	$this->bind("{$s}", $paramVal[0] );
		  	}
		}

		$result= $this->resultSet();
		return $result ? $result : false;
	}

	/*
		FETCH ALL ROWS WITHOUT LIMITS AS ARRAY
	*/

	public function fetch_all_rows_arr( $query, $params) {
		$this->query($query);
		$s=0;

		if ($params && count($params) >0) {
		  	foreach ($params as $k => $paramVal) {
			 	$s++ ;

			 	$this->bind("{$s}", $paramVal[0] );
		  	}
		}

		$result= $this->resultSetArr();
		return $result ? $result : false;
	}

	/*INSERT DATA TO THE DATABASE*/

	public function insert_data($table, $data){
		$affectedRows=0;
		$query="INSERT INTO `" . $table."` ";
		$values=''; $columns=''; $params = array();
			foreach($data as $key=>$val) {
				$columns .= "$key, ";
				if (strtolower($val)=='null') {
					$values .= "NULL, ";
				} elseif (strtolower($val)=='now()') {
					$values .= "NOW(), ";
				} else {
					$values.= "?, ";
					$params[] = array($val, 's');
				}
			}
			$query .= "(". rtrim($columns, ', ') .") VALUES (". rtrim($values, ', ') .");";
			// $_SESSION['query'] = $query;
			$this->query($query);
			$s=0;
			if ($params && count($params) >0) {
				foreach ($params as $k => $paramVal) {
					$s++ ;
					$arrbind[]=array($paramVal[0], "{$s}");
					$this->bind("{$s}", $paramVal[0] );
				}
			}
			if($this->execute()){
				if($this->rowCount() > 0){
					return true;
				}
			} else {
				return false;
			}
		}

/*
	UPDATE CHANGES TO THE DATABASE
*/
		public function update_table($table, $data, $where){
			$query = "UPDATE `".$table."` SET ";
			$params = array();

			foreach ($data as $key => $val) {
				if ($val == null) {
					$query .= "`$key` = NULL, ";
				} elseif (strtolower($val)=='now()') {
					$query .= "`$key` = NOW(), ";
				} elseif ($key == 'password') {
					$query .= "`$key` = PASSWORD(?), ";
					$params[] = array($val, $key);
				} else {
					$query .= "`$key` = ?, ";
					$params[] = array($val, $key);
				}
			}
			$query = rtrim($query, ', ');


			$whereStr = '';
			foreach ($where as $key=>$val) {
				if ($val == null) {
					$whereStr .= ((strlen($whereStr) == 0 ? '' : ' AND ') . "ISNULL(`{$key}`)");
				} else {
					$whereStr .= ((strlen($whereStr) == 0 ? '' : ' AND ') . "`{$key}` = ?");
					$params[] = array($val, 's');
				}
			}

			$query = "{$query} WHERE {$whereStr};";
			$this->query($query);
			$s=0;

			// var_dump($params);
			if ($params && count($params) >0) {
				foreach ($params as $k => $paramVal) {
					$s++ ;
					$arrbind[]=array($paramVal[0], "{$s}");
					// var_dump($arrbind);


					$this->bind("{$s}", $paramVal[0] );
				}

			}

			if($this->execute()){
				if($this->rowCount() > 0){
					return true;
				}
			} else {
				return false;
			}
		}
		/*
		DELETE DATA FROM THE DATABASE
		*/
		public function delete_row($table, $idArray  ){
		 	$query="DELETE FROM " . $table." ";
		 	if (isset($idArray) && is_array($idArray) && $idArray !== '' && count($idArray) === 1) {
				foreach ($idArray as $key => $value) {
					$idKey= $key;
					$idValue= (int)$value;
				}
		 	}
		 $where= " WHERE  {$idKey}  = :{$idKey}";
		 $query.= $where ;
		$this->query($query);
		// Bind values
		$this->bind(":{$idKey}", $idValue);
		// Execute
		if($this->execute()){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retrieve database table rows with custom SQL query
	 * This method allows for complex custom queries with parameter binding
	 *
	 * @param string $query - Custom SQL query with placeholders
	 * @param array $params - Array of parameters in format [['value', 'table_alias'], ...]
	 * @return mixed - Array of results or false on failure
	 */
	public function retrieve_db_table_rows_custom($query, $params = array()) {
		$this->query($query);
		$s = 0;

		if ($params && count($params) > 0) {
			foreach ($params as $k => $paramVal) {
				$s++;
				$this->bind("{$s}", $paramVal[0]);
			}
		}

		$result = $this->resultSet();
		return $result ? $result : false;
	}

	/**
	 * Execute custom query with parameters (alias for retrieve_db_table_rows_custom)
	 *
	 * @param string $query - Custom SQL query with placeholders
	 * @param array $params - Array of parameters in format [['value', 'table_alias'], ...]
	 * @return mixed - Array of results or false on failure
	 */
	public function execute_custom_query($query, $params = array()) {
		return $this->retrieve_db_table_rows_custom($query, $params);
	}

	/**
	 * Get single row from custom query
	 *
	 * @param string $query - Custom SQL query with placeholders
	 * @param array $params - Array of parameters in format [['value', 'table_alias'], ...]
	 * @return mixed - Single result object or false on failure
	 */
	public function retrieve_single_row_custom($query, $params = array()) {
		$this->query($query);
		$s = 0;

		if ($params && count($params) > 0) {
			foreach ($params as $k => $paramVal) {
				$s++;
				$this->bind("{$s}", $paramVal[0]);
			}
		}

		$result = $this->single();
		return $result ? $result : false;
	}

	/**
	 * Count rows from custom query
	 *
	 * @param string $query - Custom SQL query with placeholders
	 * @param array $params - Array of parameters in format [['value', 'table_alias'], ...]
	 * @return int - Number of rows or 0 on failure
	 */
	public function count_rows_custom($query, $params = array()) {
		$this->query($query);
		$s = 0;

		if ($params && count($params) > 0) {
			foreach ($params as $k => $paramVal) {
				$s++;
				$this->bind("{$s}", $paramVal[0]);
			}
		}

		$result = $this->resultSet();
		return $result ? count($result) : 0;
	}
}?>