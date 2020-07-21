<?php

  include('errorHandler.php');

  /** Secure C.R.U.D API -  is a secure create/read/update/delete api in php utilizing PDO.
  * @author Tshaba Phomolo Benedict
  * @file scrudAPI.php 
  * @version 1.2.0
  * @date  26/11/2017
  * @brief File containing Database abstraction layer API
  */

  /**Get the database credentials and parse them, for added security place config.ini
  outside the document root.*/
  $config = parse_ini_file("api/config.ini");
  $host = 'localhost';
  $user = $config['user'];
  $pass = $config['pass'];
  $dbname = $config['dbname'];
  
  /*Define our exceptions*/
  class QueryErrorException extends CustomException {} //!< query error exception
  class ExecuteErrorException extends CustomException {} //!< query execution error exception
  class ConnectErrorException extends CustomException {} //!< connection error exception
  class NoSuchTableException extends CustomException {} //!< table does not exist in database exception
  
  
  //set our home timezone
  date_default_timezone_set('Africa/Johannesburg');

  /**
  * @brief Error logging function
  * 
  * @note error messages are automatically appended the 'newline' character so the caller should take care of that
  */
  //We will do our own error handling
  function errorHandler($errcode, $errmsg, $errfile, $errline) {
     //timestamp for the error entry
     $dt = date("Y-m-d H:i:s");
     $err = "[Date: ".$dt."] ".$errfile." ($errline) ".$errmsg."\n";
     //log it
     error_log($err, 3, "/tmp/error.log");
  }
  
  $old_error_handler = set_error_handler("errorHandler");
  
  /*Main database class - This is where the magic happens*/
  class Database {
     
     private $conn = false;
     private $conobj = "";
     private $errorMsg = "";
     private $result = array();
     private $Query = "";
     private $numRows = "";
     
     /**
     * @brief Connect to the database
     * @param none
     * @return boolean (true on connection success/ false otherwise) - additional info in result
     * @warning on connection error will log the error and throw ConnectErrorException
     */
     public function connect() {
      global $host, $user, $pass, $dbname;
      $charset = "utf8mb4";
      $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
      $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, //EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
      ];

      //!check if we are already connected
      if(!$this->conn) {
	try {
	  $this->conobj = new PDO($dsn, $user, $pass, $opt);
	} catch(PDOException $e) {
	  $this->errorMsg = "Error: ".$e->getMessage()."\n";
	  trigger_error($this->errorMsg);
	  throw new ConnectErrorException($this->errorMsg);
	}
	 $this->conn = true;
	 return true;
      }else{ //we are already connected so nothing to do
	return true;
      }
     }
     
     /**
     * @brief Disconnect from the database
     * @param none
     * @return boolean (true on successful disconnect/ false otherwise)
     * @note PDO has no PDO::close function to close the database connection, so we just set the connection object to null
     */
     public function disconnect() {
      if($this->conn) {
	if($this->conobj==null) {
	  $this->conn = false;
	  return true;
	}else{
	  $this->conobj = null;
	  return false;
	}
      }
     }
     
    /** 
    *@brief sqlQuery function runs whatever 'valid' query you sent to it
    *@param string $query sql query to execute
    *@param array $bind values to bind
    *@return boolean true on success, throws exception on error. errors are logged
     @code 
      if(!$conn->sqlQuery("SELECT * FROM user WHERE name=:name OR mail=:mail",['name'=>$username,'mail'=>$email]))
       {//handle the error...}
       $data = $conn->getResult();
       //do stuff with data...
     @endcode
    */
    public function sqlQuery($query, $bind= array()) {
      
      $this->Query = $query;
      if(!$stmt = $this->conobj->prepare($query)) {
	$this->errorMsg = "Query Failed: ".$query.PHP_EOL;
	trigger_error($this->errorMsg);
	throw new QueryErrorException($this->errorMsg);
      }
	  
      if(!$stmt->execute($bind)) {
	$this->errorMsg = "Execute Failed!\n";
	trigger_error($this->errorMsg);
	throw new ExecuteErrorException($this->errorMsg);
      }
      
      //store results and number of rows.
      $this->result = $stmt->fetchAll();
      $this->numRows = $stmt->rowCount();
      return true;
    }
     
    
    /** Select Function
    *@brief select data from the database implements the @b READ in C.R.U.D
    *@param string $table - the table to extract data from
    *@param string $columns - selected columns(default all of them)
    *@param string $where - the where clause
    *@param array $params - required only when where clause is specified
    *@return boolean true on success - data is stored in the result array. throws exception, errors are logged
     @code
      if(!$conn->select('expenses','name, amount, date','amount > :amount',['amount'=>20])){//handle the error...}
	$data = $conn->getResult();
	//do stuff with data...
     @endcode
    */
    public function select($table, $columns='*', $where=null, $params=array()) {
    
      if(!$this->tableExists($table)) {
	throw new NoSuchTableException($table." table does not exist");
      }
      
      $query = "SELECT ".$columns." FROM ".$table;
      
      if($where != null)
	$query = "SELECT ".$columns." FROM ".$table." WHERE ".$where;
      
      $this->Query = $query;
      $stmt = $this->conobj->prepare($query);
      
      if(!$stmt) {
	$this->errorMsg = "Query Failed: ".$query.PHP_EOL;
	trigger_error($this->errorMsg);
	throw new QueryErrorException($this->errorMsg);
      }
      
      if(!$stmt->execute($params)) {
	$this->errorMsg = "Execute Failed!\n";
	trigger_error($this->errorMsg);
	throw new ExecuteErrorException($this->errorMsg);
      }
      
      $this->result = $stmt->fetchAll();
      $this->numRows = $stmt->rowCount();
      return true;
    }
    
    /** Insert Function
    *@brief Insert data into the database table implements the @b CREATE in C.R.U.D
    *@param string $table - the table to insert data into
    *@param array $values - the values to insert into the table
    *@return number/boolen - the id of the last inserted value/throws exception on failure errors are logged
     @code
      $numAff = $conn->insert('expenses',['name'=>'drinks', 'amount'=>450]);
      if(false==numAff){//handle the error...}
      //do stuff with numAff
     @endcode
    */
    public function insert($table, $values=array()) {
    
      if(!$this->tableExists($table)) {
	throw new NoSuchTableException($table." table does not exist");
      }
      
      $columns = array();
      foreach($values as $key => $value) {
	$columns[] = $key;
      }
      $cStr = implode($columns,',');
      $vStr = implode($columns,',:');
      $query = "INSERT INTO ".$table."(".$cStr.") VALUES (:".$vStr.")";
      $this->Query = $query;
      
      $stmt = $this->conobj->prepare($query);

      if(!$stmt) {
	$this->errorMsg = "Query Failed: ".$query.PHP_EOL;
	trigger_error($this->errorMsg);
	throw new QueryErrorException($this->errorMsg);
      }
      
      if(!$stmt->execute($values)) {
	$this->errorMsg = "Execute Failed! \n";
	trigger_error($this->errorMsg);
	throw new ExecuteErrorException($this->errorMsg);
      }
      
      $this->numRows = $this->conobj->lastInsertId();
      return $this->numRows;
    }
    
    /** Update Function
    *@brief Update data in the database implements the @b UPDATE in C.R.U.D
    *@param string $table - the name of the table to update
    *@param string $datavals - the data values to set
    *@param string $where - where clause
    *@param array $params - the parameters to execute when where clause is specified
    *@return number/boolean - the number of rows affected by the query/throws exception on failure. errors are logged
     @code
      $rows = $conn->update('expenses','name = :name','amount=:amount',['name'=>'water','amount'=>40]);
      if(false==$rows) {//handle the error...}
      //do stuff with rows...
     @endcode
    */
    public function update($table, $datavals='', $where=null, $params=array()) {
    
      if(!$this->tableExists($table)) {
	throw new NoSuchTableException($table." table does not exist");
      }
      
      $query = "UPDATE ".$table." SET ".$datavals;
      if($where != null)
	$query = "UPDATE ".$table." SET ".$datavals." WHERE ".$where;
      
      $this->Query = $query;
      $stmt = $this->conobj->prepare($query);
      
      if(!$stmt) {
	$this->errorMsg = "Query Failed: ".$query.PHP_EOL;
	trigger_error($this->errorMsg);
	throw new QueryErrorException($this->errorMsg);
      }
      
      if(!$stmt->execute($params)) {
	$this->errorMsg = "Execute Failed!\n";
	trigger_error($this->errorMsg);
	throw new ExecuteErrorException($this->errorMsg);
      }
      
      $this->numRows = $stmt->rowCount();
      return $this->numRows;
    }
    
     /**
     * @brief Delete table/rows from database implements the @b DELETE in C.R.U.D
     * @param string $table  table name, $where string where clause.
     * @return boolean (true on successful deletion/ throws exception otherwise) - additional info stored in result. errors logged.
       @code
	if(!$conn->delete('expenses',"name='drinks'")) {//handle the error...}
	 //do stuff here
       @endcode
     */
    public function delete($table,$where=null) {
      if($this->tableExists($table)) {
	if($where == null) { //no where clause means we drop the table
	  $del = 'DROP TABLE '.$table;
	}else{ //otherwise delete the row
	  $del = 'DELETE FROM '.$table.' WHERE '.$where;
	}
	
	if($d = $this->conobj->query($del)) {
	  array_push($this->result,$d->rowCount());
	  $this->Query = $del;
	  $this->numRows = $d->rowCount();
	  return true;
	}else{
	  $this->errorMsg = "Query Failed: ".$query.PHP_EOL;
	  trigger_error($this->errorMsg);
	  throw new QueryErrorException($this->errorMsg);
	}
      }else{
	$this->errorMsg = "Table: ".$table." does not exist in the database\n";
	trigger_error($this->errorMsg);
	throw new NoSuchTableException($this->errorMsg);
      }
    }
    
    /**
    * @brief Check if the specified table exists in the database
    * @param $table string table name
    * @return boolean (true if table exists/ false otherwise)
    */
    private function tableExists($table) {
      global $dbname;
      $tablesInDB = $this->conobj->query('SHOW TABLES FROM '.$dbname.' LIKE "'.$table.'"');
      if($tablesInDB) {
	if($tablesInDB->rowCount() == 1){
	  return true;
	}else{
	  return false;
	}
      }
    }
    
    /**
    * @brief Get contents of result - Error/Status messages or database data/rows
    * @param none
    * @return array of arrays contanting the requested data or error messages.
    */
    public function getResult() {
      $val = $this->result;
      $this->result = array();
      return $val;
    }

    //Start of Debugging functions
    /**
    * @brief Get contents of Query
    * @param none
    * @return string last executed sql query
    */
    public function dbgSql(){
      $val = $this->Query;
      $this->Query = "";
      return $val;      
    }
    
    /**
    * @brief Get the last error message
    * @param none
    * @return string containing last error message
    */
    public function dbgErrMsg(){
      $val = $this->errorMsg;
      $this->errorMsg = "";
      return $val;
    }

    /**
    * @brief Get total number or rows from last executed sql query
    * @param none
    * @return number
    */
    public function dbgNr(){
      $val = $this->numRows;
      $this->numRows = "";
      return $val;
    }
    //End of debugging functions
    
}
?>
