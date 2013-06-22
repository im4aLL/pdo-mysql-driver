<?php
/*
* CodeYah
* class.query.php
* 21.06.2013
*
* ===========================================
* @package		1.0
* @author 		Habib Hadi <me@habibhadi.com>
* @contribution	David Plic <Liberte sua criatividade>
* @copyright	Codeyah
* @version    	Release: 1.1 beta
* ===========================================
*/

class db{
	
	public $pdo;
	public $result;
	public $totalRow;
	public $DiplayErrorsEndUser = false;
	public $dbErrorMsg;
	private $error = array();
	private $host;
	private $driver;
	private $user;
	private $password;
	private $dbName;
	private $tables = array();
	private $final;
	
	//initial
	public function __construct(){
		$this->pdo = NULL;
		$this->result = array();
		$this->totalRow = 0;
		$this->dbErrorMsg = 'We are currently experiencing technical difficulties. We have a bunch of monkies working really hard to fix the problem. Check back soon: ';
	}
	
	//db connection
	/*
	* $db = new db();
	* $db->connect($config);
	*/
	public function connect($config=array()){
		if(count($config)==0) return false;

		try {
			
			$this->host = $config['db_host'];
			$this->driver = 'mysql';
			$this->user = $config['db_user'];
			$this->password = $config['db_pass'];
			$this->dbName = $config['db_name'];
		
            $dsn = "".$this->driver.":host=".$this->host.";dbname=".$this->dbName;
			$opt = array(
				// any occurring errors wil be thrown as PDOException
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				// an SQL command to execute when connecting
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
			);
			$this->pdo = new PDO($dsn, $this->user, $this->password, $opt);
			
			return $this->pdo;
        }
        catch (PDOException $e) {
            // in case that an error occurs, it returns the error;
			if($this->DiplayErrorsEndUser == true) {
				
				echo $this->dbErrorMsg . $e->getMessage();
				exit();	
							
			} else {
				
				$this->pdo = NULL;
				$this->error[] = $e->getMessage();
				return false;
			
			}
        }
	}
	
	//closing connection
	/*
	* $db = new db();
	* $db->connect($config);
	* $db->disconnect();
	*/
	public function disconnect(){
		return $this->pdo = NULL;	
	}
	
	//get result data
	/*
	* see query / select function usage
	*/
	public function result(){
		return $this->result;
	}
	
	public function error(){
		return $this->error;
	}
	
	//get result data
	/*
	* see query / select function usage
	*/
	public function total(){
		return $this->totalRow;
	}
	
	//safe execution
	/*
	* David Plic
	*/
	public function safe_execution($dataArray,$qryStr){
		
		$qry = $this->pdo->prepare($qryStr);
		
		foreach( $dataArray as $k=>$v ) {
					
				if(is_int($v)) {
                    $param = PDO::PARAM_INT;
                } elseif(is_bool($v)) {
                    $param = PDO::PARAM_BOOL;
                } elseif(is_null($v)) {
                    $param = PDO::PARAM_NULL;
                } elseif(is_string($v)) {
                    $param = PDO::PARAM_STR;
                } else {
                    $param = PDO::PARAM_STR;
                }    
                if($param) {

				$qry->bindValue(":$k", $v ,$param);
				
				}
		}
				
		$qry->execute();
		// affected row
		$affectedRow = $qry->rowCount();
				
		return $affectedRow;
	}	
		
	//direct query
	/*
	* $db = new db();
	* $db->connect($config);
	* $db->query('SELECT * FROM users WHERE id = 1');
	* $db->select($qryArray);
	* $db->result();
	*/
	public function query($queryString, $method = PDO::FETCH_OBJ){
		
		try {
			//query
			$qry = $this->pdo->prepare($queryString);
			$qry->execute();
			$qry->setFetchMode($method);
			
			//stroring data arrary into $result
			$this->result = $qry->fetchAll();
			
			// total row count
			$this->totalRow = $qry->rowCount();
		}
		catch (PDOException $e){
			if($this->DiplayErrorsEndUser == true) {
				
				echo $this->dbErrorMsg . $e->getMessage();
				exit();	
							
			} else {
				
				$this->error[] = $e->getMessage();
				return false;
			
			}
		}
	}
	
	//fetch data query
	/*
	* $db = new db();
	* $db->connect($config);
	* $qryArray = array( 'tbl_name' => 'users', 'field' => array('email', 'nickname'), 'method' => PDO::FETCH_OBJ, 'condition' => ' WHERE id = 1', 'limit' => '0,30', 'orderby' => 'created_at' );
	* $db->select($qryArray);
	* $db->result();
	*/
	public function select($qryArray){
		
		//preparing fields
		$fetchFields = (isset($qryArray['field']) && count($qryArray['field'])>0) ? implode(', ',$qryArray['field']): '*';
		
		//preparing query string
		$qryStr = 'SELECT '.$fetchFields.' FROM `'.$qryArray['tbl_name'].'` '.((isset($qryArray['condition']) && $qryArray['condition']!=NULL)?$qryArray['condition']:'');
		if(isset($qryArray['orderby']) && $qryArray['orderby']!=NULL) $qryStr .= ' ORDER BY '.$qryArray['orderby'];
		if(isset($qryArray['limit']) && $qryArray['limit']!=NULL) $qryStr .= ' LIMIT '.$qryArray['limit'];
		
		try {
			//query
			$qry = $this->pdo->prepare($qryStr);
			$qry->execute();
			
			//define method
			if(isset($qryArray['method']) && $qryArray['method']!=NULL) $qry->setFetchMode($qryArray['method']);
			
			//stroring data arrary into $result
			$this->result = $qry->fetchAll();
			
			// total row count
			$this->totalRow = $qry->rowCount();
		}
		catch (PDOException $e){
			if($this->DiplayErrorsEndUser == true) {
				
				echo $this->dbErrorMsg . $e->getMessage();
				exit();	
							
			} else {
				
				$this->error[] = $e->getMessage();
				return false;
			
			}
		}
	}
	
	
	//insert query
	/*
	* syntax connection->insert(table name, insert data array, duplicated field checking array)
	* $inserted = $db->insert('users', array('email'=>'c@yahoo.com', 'nickname'=> 'Mr. C', 'password' => '159159'), array('email'));
	* print_r($inserted);
	*/
	public function insert($tableName, $dataArray = array(), $unique = array()){
		
		$fields = array();
		$executeArray = array();
		
		//populating field array
		foreach($dataArray as $key=>$val){
			$fields[] = ':'.$key;
			$executeArray[':'.$key] = $val;
			
		}
		
		//generating field string
		$fields_str = implode(',',$fields);
		$rawFieldsStr = implode(',', str_replace(':','',$fields));
		
		// checking wheather same value exists or not
		if( count($unique) > 0 ){
			$condition = array();
			foreach($unique as $fieldName){
				$condition[] = $fieldName." = '".$dataArray[$fieldName]."' ";
			}

			$cQryStr = "SELECT ".$unique[0]." FROM ".$tableName." WHERE ".implode('AND ',$condition);
			$cQry = $this->pdo->query($cQryStr);
			
			//checking duplicate
			if( $cQry->rowCount() > 0 ) $duplicate = true;
			else $duplicate = false;
		}
		
		$affectedRow = 0;
		$lastInsertedId = 0;
		
		//processing insertsion while there is no duplicated value
		if(!$duplicate) {
			$qryStr = 'INSERT INTO '.$tableName.' ('.$rawFieldsStr.') VALUES('.$fields_str.')';
			
			try {
				//query
				$affectedRow = $this->safe_execution($dataArray,$qryStr);
				
				// last inseretd id
				$lastInsertedId = $this->pdo->lastInsertId();
			}
			catch (PDOException $e){
				if($this->DiplayErrorsEndUser == true) {
				
					echo $this->dbErrorMsg . $e->getMessage();
					exit();	
							
				} else {
				
					$this->error[] = $e->getMessage();
					return false;
			
				}
			}	
		}
		
		// returning insert log
		return array('affectedRow' => $affectedRow, 'insertedId' => $lastInsertedId, 'duplicate' => $duplicate);
	}
	
	
	//update query
	/*
	* update(tableName, updatedDataArray, whereArray, duplicateCheckingArray);
	* $updated = $db->update('users', array('nickname'=> 'Hadi', 'email'=> 'hadicse@gmail.com'), array('id'=>1, 'nickname'=>'Habib Hadi'), array('email'));
	* print_r($updated);
	*/
	public function update($tableName, $dataArray = array(), $where, $unique = array()){
		$fields = array();
		$executeArray = array();
		
		//populating field array
		foreach($dataArray as $key=>$val){
			$fields[] = $key.' = :'.$key;
			$executeArray[':'.$key] = $val;
			
		}
		
		//generating field string
		$fields_str = implode(', ',$fields);
		
		// checking wheather same value exists or not
		if( count($unique) > 0 ){
			$condition = array();
			foreach($unique as $fieldName){
				$condition[] = $fieldName." = '".$dataArray[$fieldName]."' ";
			}

			$cQryStr = "SELECT ".$unique[0]." FROM ".$tableName." WHERE ".implode('AND ',$condition);
			$cQry = $this->pdo->query($cQryStr);
			
			//checking duplicate
			if( $cQry->rowCount() > 0 ) $duplicate = true;
			else $duplicate = false;
		}
		else $duplicate = false;
		
		$affectedRow = 0;
		$lastInsertedId = 0;
		
		//processing query while there is no duplicated value
		if(!$duplicate && ($where!=NULL || (is_array($where) && count($where)>0)) ) {
			
			if(is_array($where)) {
				$affectedTo = array();
				foreach($where as $key=>$val){
					$affectedTo[] = $key." = '".$val."'";
				}
				$whereCond = ' WHERE '.implode(" AND ", $affectedTo);
			}
			else {
				$whereCond = ' WHERE '.$where;	
			}
			
			$qryStr = 'UPDATE '.$tableName.' SET '.$fields_str.$whereCond;
			
			try {
				//query
				$affectedRow = $this->safe_execution($dataArray,$qryStr);
			}
			catch (PDOException $e){
				if($this->DiplayErrorsEndUser == true) {
				
					echo $this->dbErrorMsg . $e->getMessage();
					exit();	
							
				} else {
				
					$this->error[] = $e->getMessage();
					return false;
			
				}
			}	
		}
		
		// returning insert log
		return array('affectedRow' => $affectedRow, 'duplicate' => $duplicate);	
	}
	
	
	//delete query
	/*
	* $d = $db->delete('users', array('id'=>1));
	* print_r($d);
	*/
	public function delete($tableName, $where){
		
		$affectedRow = 0;
		if($where!=NULL || (is_array($where) && count($where)) > 0 ){
			if(is_array($where)) {
				$affectedTo = array();
				foreach($where as $key=>$val){
					$affectedTo[] = $key." = '".$val."'";
				}
				$whereCond = ' WHERE '.implode(" AND ", $affectedTo);
			}
			else {
				$whereCond = ' WHERE '.$where;	
			}
			
			$qryStr = 'DELETE FROM '.$tableName.' '.$whereCond;
			
			try {
				//query
				$affectedRow = $this->safe_execution($where,$qryStr);

			}
			catch (PDOException $e){
				if($this->DiplayErrorsEndUser == true) {
				
					echo $this->dbErrorMsg . $e->getMessage();
					exit();	
							
				} else {
				
					$this->error[] = $e->getMessage();
					return false;
			
				}
			}	
		}
		
		return array('affectedRow' => $affectedRow);	
		
	}
	
	## functions of backup
	/**
	 *
	 * Call this function to get the database backup
	 * @example $db->backup();
	 */
	public function backup(){
		//return $this->final;
		if(count($this->error)>0){
			return array('error'=>true, 'msg'=>$this->error);
		}
		
		$this->final = 'CREATE DATABASE ' . $this->dbName.";\n\n";
		$this->getTables();
		$this->generateBackup();
		
		return array('error'=>false, 'msg'=>$this->final);
	}

	/**
	 *
	 * Generate backup string
	 * @uses Private use
	 */
	private function generateBackup(){
		foreach ($this->tables as $tbl) {
			$this->final .= '--CREATING TABLE '.$tbl['name']."\n";
			$this->final .= $tbl['create'] . ";\n\n";
			$this->final .= '--INSERTING DATA INTO '.$tbl['name']."\n";
			$this->final .= $tbl['data']."\n\n\n";
		}
		$this->final .= '-- THE END'."\n\n";
	}



	/**
	 *
	 * Get the list of tables
	 * @uses Private use
	 */
	private function getTables(){
		try {
			$stmt = $this->pdo->query('SHOW TABLES');
			$tbs = $stmt->fetchAll();
			$i=0;
			foreach($tbs as $table){
				$this->tables[$i]['name'] = $table[0];
				$this->tables[$i]['create'] = $this->getColumns($table[0]);
				$this->tables[$i]['data'] = $this->getData($table[0]);
				$i++;
			}
			unset($stmt);
			unset($tbs);
			unset($i);

			return true;
		} catch (PDOException $e) {
			if($this->DiplayErrorsEndUser == true) {
				
				echo $this->dbErrorMsg . $e->getMessage();
				exit();	
							
			} else {
				
				$this->error[] = $e->getMessage();
				return false;
			
			}
		}
	}

	/**
	 *
	 * Get the list of Columns
	 * @uses Private use
	 */
	private function getColumns($tableName){
		try {
			$stmt = $this->pdo->query('SHOW CREATE TABLE '.$tableName);
			$q = $stmt->fetchAll();
			$q[0][1] = preg_replace("/AUTO_INCREMENT=[\w]*./", '', $q[0][1]);
			return $q[0][1];
		} catch (PDOException $e){
			if($this->DiplayErrorsEndUser == true) {
				
				echo $this->dbErrorMsg . $e->getMessage();
				exit();	
							
			} else {
				
				$this->error[] = $e->getMessage();
				return false;
			
			}
		}
	}

	/**
	 *
	 * Get the insert data of tables
	 * @uses Private use
	 */
	private function getData($tableName){
		try {
			$stmt = $this->pdo->query('SELECT * FROM '.$tableName);
			$q = $stmt->fetchAll(PDO::FETCH_NUM);
			$data = '';
			foreach ($q as $pieces){
				foreach($pieces as &$value){
					$value = htmlentities(addslashes($value));
				}
				$data .= 'INSERT INTO '. $tableName .' VALUES (\'' . implode('\',\'', $pieces) . '\');'."\n";
			}
			return $data;
		} catch (PDOException $e){
			
			if($this->DiplayErrorsEndUser == true) {
				
				echo $this->dbErrorMsg . $e->getMessage();
				exit();	
							
			} else {
				
				$this->error[] = $e->getMessage();
				return false;
			
			}
		}
	}	
	## End functions of Backup
	
}
?>