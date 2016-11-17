<?php

header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");

if (isset($POST['data']){
	$data     = $POST['data'];
	$output   = array(); // pass back data
	$output["success"] = false;

	save_to_db($data);

	if(empty($data))
		$output["message"] = "Input cannot be empty!";
	else if(!is_numeric($data))
		$output["message"] = "Input has to be a number!";
	else if(!is_int($data))
		$output["message"] = "Input has to be integer!";
	else if($data<1 || $data>1000)
		$output["message"] = $data." is not between 1 and 1000";
	else if ($data%15==0)
		$output["success"] = true;
		$output["message"] = "Bisnow Media";
	else if ($data%3==0)
		$output["success"] = true;
		$output["message"] = "Bisnow";
	else if ($data%5==0)
		$output["success"] = true;
		$output["message"] = "Media";

	//Return the data back to form.php
	echo json_encode($form_data);
}

function save_to_db($data){

	$servername = "localhost";
	$username = "username";
	$password = "password";
	$dbname = "myDB";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 

	$sql = "INSERT INTO tracking (uname, input_) VALUES ('tmp_user', $data)";

	if ($conn->query($sql) === TRUE) {
	    echo "New record created successfully";
	} else {
	    echo "Error: " . $sql . "<br>" . $conn->error;
	}

	$conn->close();

}
?>