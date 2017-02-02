
<?PHP

include 'config.php'; 

session_start(); 

if (isset($_SESSION['login'])) {
	
	$aUser = $_SESSION['user']; 
		
	// TODO: check for empty fields 
	
	// connect to database 
	$mysqli = mysqli_connect(HOSTNAME, DB_USER, DB_PASS, DB_NAME); 

	// check connection
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	} else {
		echo 'Connected successfully';
	}

	// check database 
	if ($result = $mysqli->query("SELECT DATABASE()")) {
		$row = $result->fetch_row();
		printf("Default database is %s.\n", $row[0]); echo '<br>'; 
		$result->close();
		
		// loop the POST variable and place all values in corresponding field in database 
		foreach ($_POST as $param_name => $param_val) {
			
			$field = $param_name; 
			$value = $param_val;
			
			echo "$aUser: $field = $value";
			echo "<br>"; 

			$strQuery = "UPDATE user_picks SET $field = '$value' WHERE user = '$aUser'"; 	
			$retval = $mysqli->query($strQuery); 			
			if (!$retval) {
				die('Error: ' . $mysqli->error);
			}			
			
		}
		
	} else {
		die('Can\'t use ' . DB_NAME . ': ' . mysql_error()); 
	}
	
}

// redirect to home page 
echo "<meta http-equiv='refresh' content='0;url=home.php'>"; 

?>
