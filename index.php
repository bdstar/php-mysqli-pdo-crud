<?php
class MyPDO extends PDO{

    const PARAM_host='localhost';
    const PARAM_port='3306';
    const PARAM_db_name='test';
    const PARAM_user='root';
    const PARAM_db_pass='';
    const PARAM_table = 'people';
    public $connection;


    public function __construct(){	

		$user = MyPDO::PARAM_user;
		$password = MyPDO::PARAM_db_pass;
		$dsn = 'mysql:host='.MyPDO::PARAM_host.';port='.MyPDO::PARAM_port.';dbname='.MyPDO::PARAM_db_name;

		$this->connection = new PDO($dsn, $user, $password);
		// set the PDO error mode to exception
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//Connect Database
		try {
		    echo "<br>Connected successfully";
		} catch (PDOException $e) {
		    echo '<br>Connection failed:' .$e->getMessage();
		}

    } //End of constructor function


	public function CreateTable($TableSql){
		// Create Table
		try {
		    // use exec() because no results are returned
		    $this->connection->exec($TableSql);
		    echo "<br>Table ".MyPDO::PARAM_table." created successfully";   
		}
		catch(PDOException $e){
		    echo "<br>For Table: " . $e->getMessage();
		}
	}

    public function fetchAll($table){

		try {
			$FetchSql = "SELECT * FROM $table";
			 
			$q = $this->connection->query($FetchSql);
			$q->setFetchMode(PDO::FETCH_ASSOC);
			 
		} catch (PDOException $ex) {
			die("Could not connect to the database:" . $ex->getMessage());
		}

		while ($r = $q->fetch()){
			$data[]=$r;
		}
		return $data;
    }//End of function fetch


	# Insert Data within table by accepting TableName and Table column => Data as associative array
	public function insert($tblname, array $val_cols){

		$keysString = implode(", ", array_keys($val_cols));

		$i=0;
		foreach($val_cols as $key=>$value) {
			$StValue[$i] = "'".$value."'";
		    $i++;
		}

		$StValues = implode(", ",$StValue);
		
		//echo "<br>INSERT INTO $tblname ($keysString) VALUES ($StValues)<br>";

		try {
		    $sql = "INSERT INTO $tblname ($keysString) VALUES ($StValues)";
		    // use exec() because no results are returned
		    $this->connection->exec($sql);
		    echo "New record Inserted successfully";
		}
		catch(PDOException $e){
		    echo $sql . "<br>" . $e->getMessage();
		}

	}//End of function insert



	//Delete data form table; Accepting Table Name and Keys=>Values as associative array
	public function delete($tblname, array $val_cols){
		//Append each element of val_cols associative array 
		$i=0;
		foreach($val_cols as $key=>$value) {
			$exp[$i] = $key." = '".$value."'";
		    $i++;
		}

		$Stexp = implode(" AND ",$exp);


		try {
		    // sql to delete a record
		    $sql = "DELETE FROM $tblname WHERE $Stexp";

		    // use exec() because no results are returned
		    //$this->connection->exec($sql);
		    
		    $delete = $this->connection->prepare($sql);
		    $delete->execute();
			$count = $delete->rowCount();
			print("Deleted $count rows.\n");

			if($count==0){
				echo "<br>Record you want to delete is no loger exists<br>";		
			}
			else{
				echo "<br>Data is deleted successfully.<br>";
			}

		}
		catch(PDOException $e){
		    echo $sql . "<br>" . $e->getMessage();
		}
	}//End of Delete Function	



	//Update data within table; Accepting Table Name and Keys=>Values as associative array
	public function update($tblname, array $set_val_cols, array $cod_val_cols){
		
		//append set_val_cols associative array elements 
		$i=0;
		foreach($set_val_cols as $key=>$value) {
			$set[$i] = $key." = '".$value."'";
		    $i++;
		}

		$Stset = implode(", ",$set);

		//append cod_val_cols associative array elements
		$i=0;
		foreach($cod_val_cols as $key=>$value) {
			$cod[$i] = $key." = '".$value."'";
		    $i++;
		}

		$Stcod = implode(" AND ",$cod);

		try{
		    $sql = "UPDATE $tblname SET $Stset WHERE $Stcod";

		    // Prepare statement
		    $stmt = $this->connection->prepare($sql);

		    // execute the query
		    $stmt->execute();

		    // echo a message to say the UPDATE succeeded
		    echo $stmt->rowCount() . " records UPDATED successfully";
			if($stmt->rowCount()==0){
				echo "<br>Record you want to update is no loger exists<br>";		
			}
			else{
				echo "<br>Data is updated successfully.<br>";
			}

	    }
		catch(PDOException $e){
	    	echo $sql . "<br>" . $e->getMessage();
	    }

	}//End of function Fetch

	public function __destruct(){
	 	$this->connection = null;
	 	echo "<br>Connection is closed";
	}

} //End of class

$obj = new MyPDO;
$tablename = MyPDO::PARAM_table;

//Table created query
$TableSql = "CREATE TABLE ".MyPDO::PARAM_table." (
	id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
	firstname VARCHAR(30) NOT NULL,
	lastname VARCHAR(30) NOT NULL,
	email VARCHAR(50),
	reg_date TIMESTAMP
)";
$obj->CreateTable($TableSql);


//Associative array for insert function
$InsColumnVal = array("firstname"=>'Daniyal',"lastname"=>'Zahan',"email"=>'email@mail.com');
//Call insert function to insert record
$obj->insert($tablename, $InsColumnVal);


//Associative array for delete function
$DelColumnVal = array("firstname"=>'Daniyal',"lastname"=>'Zahan');
//Call Delete function
//$obj->delete($tablename, $DelColumnVal);


//Associative array to set query for update function
$condition = array("id"=>9,"firstname"=>'Daniyal');
//Associative array to condition query for update function
$set = array("firstname"=>'md',"lastname"=>'Hatim');
//call update function
$obj->update($tablename, $set,$condition);

//To show all data
foreach($obj->fetchAll($tablename) as $value){
	extract($value);
	echo $firstname." = ".$email." = ".$lastname;
}

?>