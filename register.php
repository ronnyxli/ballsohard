
<html>

<head>

	<title>Register for 2016 NBA Playoffs Bracket</title>
	
	<!-- CSS stylesheet -->
	<link rel="stylesheet" href="css/form_style.css" type="text/css"  />
	
</head>

<body>

<h3> Registration Form </h3>

<?PHP

include 'config.php'; 

ob_start(); 

$error_message = 'Fill out the required fields (*)'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	// break apart $_POST array 
	$email = $_POST['email']; 
	$username = $_POST['username']; 
	$userpass = $_POST['pword']; 
	$userpass2 = $_POST['pword2']; 

	// check for errors 
	if (strpos($username, ' ') > 0) { // check that there are no spaces in the username
		$error_message = "Username cannot have spaces";	
	}
	elseif (strlen($userpass) < 6) { // check for minimum password length 
		$error_message = "Password must be at least 6 characters.";  
	}
	elseif (strlen($username) > 20) { // check for maximum username length
		$error_message = "Username cannot exceed 20 characters."; 
	}
	elseif ($userpass != $userpass2) { // check that the passwords match 
		$error_message = "Passwords do not match."; 
	}
	else {
		$error_message = ''; // no error!
	}
	
	if (empty($error_message)) {
		
		// connect to database 
		$mysqli = mysqli_connect(HOSTNAME, DB_USER, DB_PASS, DB_NAME); 
		// $mysqli = new mysqli(HOSTNAME, DB_USER, DB_PASS, DB_NAME);

		// check connection
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		} else {
			/*echo 'Connected successfully';
			echo '<br><br>'; */
		}
		
		// check that username and email are not already in use 
		$strQuery = "SELECT * FROM user_list WHERE user = '$username'"; 
		$aResult = $mysqli->query($strQuery); 
		$num_rows = $aResult->num_rows; 	
		$strQuery2 = "SELECT * FROM user_list WHERE email = '$email'"; 
		$aResult2 = $mysqli->query($strQuery2); 
		$num_rows2 = $aResult2->num_rows; 
		if ($num_rows > 0) { 
			$error_message = "Username taken."; 
		} 
		elseif ($num_rows2 > 0) {
			$error_message = "Email is in use."; 
		}
		else {
			$error_message = ''; 
		}

		if (empty($error_message)) { // username and email are both not already in use
		
			// create a NONCE ($username makes the nonce specific to the user)
			$nonce = md5('registration-' . $username . NONCE_SALT); 
			$secureHash = hash_hmac('sha512', $userpass . $nonce, SITE_KEY); //hash password
			$userpass = $secureHash; 
			
			// specify tables
			$user_table = "user_list"; 
			$picks_table = "user_picks";
			
			$sql1 = "INSERT INTO $user_table (user, pw, email) VALUES ('$username', '$userpass', '$email')";
			if ($mysqli->query($sql1) === FALSE) {
				echo "Error: " . $sql1 . "<br>" . $mysqli->error . "<br>";
			}
			else {
				$sql2 = "INSERT INTO $picks_table (user) VALUES ('$username')";
				if ($mysqli->query($sql2) === FALSE) {
					echo "Error: " . $sql2 . "<br>" . $mysqli->error . "<br>";
				}				
			}
			
			// email login information to user 	
			$subject = '2016 NBA Playoffs Bracket login information';
			$message = "Thank you for registering. Your username is $username and your password is $userpass. Please retain this email for your records."; 
			mail($email, $subject, $message);	
			
			$mysqli->close(); // close connection 
			
		}

	} 

}

if (empty($error_message)) { ?>
	
	<!-- Display registration confirmed message if no errors -->
	Successfully registered! Your login information will be e-mailed to the address you provided.
	You may now close this window and login with your username and password. 

<?PHP	
}
else { 

	// Display error message and registration form if there are errors 
	echo "$error_message"; 
	echo "<br>"; 

?>

	<br>
	<div class="regForm">
		<form id="register_form" align="center" action="register.php" method="post">
			<input type="email" name="email" required placeholder="E-mail *" autocomplete="off"><br>
			<input type="text" name="username" required placeholder="Username (no spaces) *" autocomplete="off"><br>
			<input type="password" name="pword" required placeholder="Password (min 6 characters) *" autocomplete="off"><br>
			<input type="password" name="pword2" required placeholder="Re-type password *" autocomplete="off"><br>
			<input type="submit" value="Register">
		</form>
	</div>
	
<?PHP	
}

?>
	
<p class="message" style="text-align:center"><a href="javascript: window.parent.closePopup('regPopup')">Close</a></p>
			
</body>
 
</html>

