#PDO MySql driver class for PHP
##Introduction
This is simple class for SELECT, INSERT, UPDATE, DELETE query for MySQL

##Usage

###Connection

    // initializing configuration array file
    $config = array();
    
    // database settings variables
    $config['db_name'] = 'codeyah';
    $config['db_host'] = 'localhost';
    $config['db_user'] = 'root';
    $config['db_pass'] = '';
    
    // Class files
    include('class/class.query.php');
    
    // initializing db class
    $db = new db();
    
    // connecting to DB
    $db->connect($config);
    
###Disconnect
    $db = new db();
    $db->connect($config);
    $db->disconnect();
    
###Select Query

####Method #1
    $db = new db();
    $db->connect($config);
    $db->query('SELECT * FROM users WHERE id = 1'); // just write like mysql_query
    $db->result();
    
####Method #2

#####Syntax
    select(
      array(
        'tbl_name' => 'your table name',
        'field' => array('field 1', 'field 2') or array() // if array contain no value then it will fetch all (*),
        'method' => check the available list for PDO::FETCH // default is PDO::FETCH_OBJ
        'condition' => '',
        'limit' => ''
      )
    )

#####Example
    $db = new db();
    $db->connect($config);
    $qryArray = array( 'tbl_name' => 'users', 'field' => array('email', 'nickname'), 'method' => PDO::FETCH_OBJ, 'condition' => ' WHERE id = 1', 'limit' => '0,30', 'orderby' => 'created_at', 'groupby' => 'category' );
    $db->select($qryArray);
    $db->result();
    
###Insert
    syntax - connection->insert(table name, insert data array, duplicated field checking array)
    
    $inserted = $db->insert('users', array('email'=>'c@yahoo.com', 'nickname'=> 'Mr. C', 'password' => '159159'), array('email'));
    print_r($inserted);
    
###Update
    syntax - update(tableName, updatedDataArray, whereArray, duplicateCheckingArray);
    
    $db->update('users', array('nickname'=> 'Hadi', 'email'=> 'a@gmail.com'), array('id'=>1, 'nickname'=>'Mr. A'), array('email'));
    
###Delete
    $db->delete('users', array('id'=>1));
    


Remember all query will return array data. To view use print_r function. If anybody need more explanation with example then feel to ask.