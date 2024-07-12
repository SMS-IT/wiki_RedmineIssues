<?php
/*
Mysql connector
*/
class RIMysqlConnector {
	var $message = "";

	var $dbserver;
	var $dbuser;
	var $database;
	var $dbpassword;
	var $dbencoding;
	
	var $db = FALSE;	
	
	function RIMysqlConnector( $db_server, $db_user, $db_name, $db_password, $db_encoding ) 
	{
		$this->dbserver = $db_server;
		$this->dbuser = $db_user;
		$this->database = $db_name;
		$this->dbpassword = $db_password;
		$this->dbencoding = $db_encoding;
	}
	
	public function connect() {
		$this->db = mysqli_connect($this->dbserver, $this->dbuser, $this->dbpassword);
		if (mysqli_connect_errno()) {
			printf("Не удалось подключиться: %s\n", mysqli_connect_error());
			exit();
		}
			
		// Set character encoding	
		mysqli_query($this->db, "SET NAMES '" . $this->dbencoding . "';");
		mysqli_query($this->db, "SET CHARACTER SET '" . $this->dbencoding . "';");
					
					
		if (! $this->db) {
			$this->setError(mysqli_error());	
			return FALSE;
		} 		

		return $this->db;
	}
	
	public function execute($sql) {
		if (! $this->db) {
			$this->connect();
		}

		if (! $this->db) {
			$this->setError("CONNECT ERROR:" . mysqli_error());	
			return FALSE;
		} 		
		
		return mysqli_query($this->db, $sql);
	}
	
	public function getRowCount($result) {
		return mysqli_num_rows($result);
	}
	
	public function fetch($result) {
		return mysqli_fetch_array($result, MYSQLI_ASSOC);
	}
	
	public function free($result) {
		mysqli_free_result($result);
	}
	
	public function close($db) {		
		/* 
		 * In PHP you should rely on script termination to close mysql
		 * and not explicitly call mysql_close($db) - see
		 * http://uk.php.net/manual/en/function.mysql-close.php
		 * This is because the implementation may reuse connections.  This 
		 * does happen if the connection details for the Bugzila database are
		 * the same as the wiki database.  Setting to null is good practice
		 * to free up the resource early.
		 */
		$db=null;
	}

	public function getTable($table) {
		return "`" . $this->database . "`." . $table;
	}
	
	protected function setError($message) {
		$this->message .= $message . "\n";
	}

	public function getError() {
		return $this->message;
	}
	
	public function getDbError($db) {
		return mysqli_error($db);
	}

}

?>