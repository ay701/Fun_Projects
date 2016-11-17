
<?php

/******************************
* @Author: Aizizi Yigaimu
* @Created: 2016-11-16
* @Contact: ay701@nyu.edu
*******************************/

class MyDB extends SQLite3
{	
	private $db = null;
	private $db_name;
	
	public static function getInstance($db_name='database_name.sqlite'){
		
		if ($this->db==null){
			$this->db = new MyDB($db_name);

			// Check if database open successfully
			if(!$this->db){
				echo $this->db->lastErrorMsg();
			} else {
				$this->db_name = $db_name;
				echo "Opened database successfully\n";
			}
		}

		return $this->db;
	}

  	function __construct()
  	{
     	$this->open($this->db_name);
  	}

  	function query($sql){

        $ret = $this->db->exec($sql);
		if(!$ret){
		  echo $this->db->lastErrorMsg();
		} else {
		  echo "Operation done successfully\n";
		}
	
	}
	
	function close(){
		$this->db->close();
  	}
}

class FileParser{

	private $filename;
	private $handle;

	# Use "input.txt" as default file
	private function __construct($filename="input.txt"){
		echo $this->filename = $filename;
	}

	public function open(){
		$this->handle = fopen($this->filename, "r");

		if (!$this->handle) {
		    // error opening the file.
		    exit("File Open Error!");
		} 

		return $this->handle;
	}

	public function close(){
		fclose($this->handle);
	}

	public function parse(){

		$db = MyDB::getInstance();
		$handle = $this->open();
		
		while (($line = fgets($handle)) !== false) {
	        // process the line read
	        $arr = explode(" ", trim($line));
	        $sql = "INSERT INTO users VALUES (full_name, phone, email, address) VALUES \
	        		(".implode(" ", array_slice($arr, 0, 1)).", \
	        		".implode(" ", array_slice($arr, 2, 1))", \
	        		".implode(" ", array_slice($arr, 3))");";
	        $db->query($sql);
	    }

		$this->close();
  	 	$db->close();
	}

}

$fp = new FileParser();
$fp->parse();

?>