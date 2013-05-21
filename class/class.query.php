<?php
/*
* CodeYah
* class.query.php
* 15.05.2013
*
* ===========================================
* @package		1.0
* @author 		Habib Hadi <me@habibhadi.com>
* @copyright	Codeyah
* @version    	Release: 1.0 beta
* ===========================================
*/

class db{
	
	public $pdo;
	public $result;
	public $totalRow;
	public $dbErrorMsg;
	
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
            $dsn = "mysql:host=".$config['db_host'].";dbname=".$config['db_name'];
			$opt = array(
				// any occurring errors wil be thrown as PDOException
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				// an SQL command to execute when connecting
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
			);
			$this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $opt);
			
			return $this->pdo;
        }
        catch (PDOException $ex) {
            // in case that an error occurs, it returns the error;
            echo $this->dbErrorMsg . $ex->getMessage();
			exit();
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
	
	//get result data
	/*
	* see query / select function usage
	*/
	public function total(){
		return $this->totalRow;
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
		catch (PDOException $ex){
			echo $this->dbErrorMsg . $ex->getMessage();
			exit();
		}
	}
	
	//fetch data query
	/*
	* $db = new db();
	* $db->connect($config);
	* $qryArray = array( 'tbl_name' => 'users', 'field' => array('email', 'nickname'), 'method' => PDO::FETCH_OBJ, 'condition' => ' WHERE id = 1', 'limit' => '0,30' );
	* $db->select($qryArray);
	* $db->result();
	*/
	public function select($qryArray){
		
		//preparing fields
		$fetchFields = (isset($qryArray['field']) && count($qryArray['field'])>0) ? implode(', ',$qryArray['field']): '*';
		
		//preparing query string
		$qryStr = 'SELECT '.$fetchFields.' FROM `'.$qryArray['tbl_name'].'` '.((isset($qryArray['condition']) && $qryArray['condition']!=NULL)?$qryArray['condition']:'');
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
		catch (PDOException $ex){
			echo $this->dbErrorMsg . $ex->getMessage();
			exit();
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
				$qry = $this->pdo->prepare($qryStr);
				$qry->execute($executeArray);
				
				// affected row
				$affectedRow = $qry->rowCount();
				
				// last inseretd id
				$lastInsertedId = $this->pdo->lastInsertId();
			}
			catch (PDOException $ex){
				echo $this->dbErrorMsg . $ex->getMessage();
				exit();
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
				$qry = $this->pdo->prepare($qryStr);
				$qry->execute($executeArray);
				
				// affected row
				$affectedRow = $qry->rowCount();

			}
			catch (PDOException $ex){
				echo $this->dbErrorMsg . $ex->getMessage();
				exit();
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
				$qry = $this->pdo->prepare($qryStr);
				$qry->execute();
				
				// affected row
				$affectedRow = $qry->rowCount();

			}
			catch (PDOException $ex){
				echo $this->dbErrorMsg . $ex->getMessage();
				exit();
			}	
		}
		
		return array('affectedRow' => $affectedRow);	
		
	}
	
}
?>