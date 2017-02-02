
<html>

<head>

	<title>Create/Join Pool</title>
	
	<!-- CSS stylesheet -->
	<link rel="stylesheet" href="css/form_style.css" type="text/css"  />
	
	<script type="text/javascript">	
	
		function leavePoolMsg (poolID) {
			if (confirm('Are you sure you want to leave this pool?')) {
				return true; 
			} else {
				return false; 
			}
		}
	
	</script>
	
</head>

<body>

<h3> Manage My Pools </h3>

<?PHP

include 'config.php'; 

ob_start(); 
session_start(); 

if (isset($_SESSION['login'])) {
	
	$aUser = $_SESSION['user']; // active user 

	// connect to database 
	$mysqli = mysqli_connect(HOSTNAME, DB_USER, DB_PASS, DB_NAME); 

	// check connection
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	} 
	else {
		
		// echo 'Connected successfully';
		
		// fetch active user's pools 
		$queryPools = "SELECT pool1, pool2 FROM user_list WHERE user = '$aUser'";
		$aResult = $mysqli->query($queryPools);
		$userPools = $aResult->fetch_array(MYSQLI_ASSOC);		
		$uPool1 = $userPools['pool1']; 
		$uPool2 = $userPools['pool2']; 
		
		var_dump($userPools); 
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			
			var_dump($_POST); 

			$button = $_POST['buttonName'];

			$confirm_message = ''; 
			
			switch ($button) {
				
				case 'leaveP1':
					
					break;
					
				case 'leaveP2':
					
					break;
					
				case ('Create' || 'Join'):
					$pid = $_POST['poolID']; 
					
					// query all existing pools 
					$queryAllPools = "SELECT pool1, pool2 FROM user_list WHERE 1";
					$aResult = $mysqli->query($queryAllPools);
					$allPools = $aResult->fetch_array(MYSQLI_ASSOC);	
					
					// merge both pools into a list 
					$pools = $allPools['pool1'] . $allPools['pool2'];

					var_dump($pools); 
					
					if (empty($uPool1)) {
						$insertQuery = "INSERT INTO user_list pool1 VALUES '$pid' WHERE user = '$aUser'"; 
					}
					elseif  (empty($uPool2)) {
						$insertQuery = "INSERT INTO user_list pool2 VALUES '$pid' WHERE user = '$aUser'"; 
					}
					else {
						// do nothing 
					}
					
					$iResult = $mysqli->query($insertQuery);
					
					if ($iResult) {
						$confirm_message = "Successfully joined $pid!";
					}
					
	
					break;
					
				default:

			}
			
			if (!empty($confirm_message)) {
				echo $confirm_message; 
			}
			

		}
		
		// fetch active user's pools again
		$queryPools = "SELECT pool1, pool2 FROM user_list WHERE user = '$aUser'";
		$aResult = $mysqli->query($queryPools);
		$userPools = $aResult->fetch_array(MYSQLI_ASSOC);		
		$uPool1 = $userPools['pool1']; 
		$uPool2 = $userPools['pool2']; 

		// check how many pools the user has joined 
		if ((!empty($uPool1)) && (!empty($uPool2))) {
			// both pools filled
			$num_pools = 2; 
		}
		elseif ((empty($uPool1)) && (empty($uPool2))) {
			// both pools empty
			$num_pools = 0; 
		}
		else {
			// one empty pool 
			$num_pools = 1; 
		}
		
		$showForm = 0; 
		if ($num_pools > 0) {
			echo "You have joined in the following pools:<br><br>"; 

			if (!empty($uPool1)) {
				echo $uPool1; ?>
				<button name="buttonName" type="submit" form="joinPool_form" value="leaveP1"><b>Leave</b></button>
		<?PHP
				echo "<br>";
			}
			
			if (!empty($uPool2)) {
				echo $uPool2; ?>
				<button name="buttonName" type="submit" form="joinPool_form" value="leaveP2"><b>Leave</b></button>
		<?PHP
				echo "<br>";
			}			
			if ($num_pools == 1) {
				$showForm = 1; 
			}
		} 
		else {
			echo "You have not joined any pools yet."; 
			$showForm = 1; 
		}
		
		if ($showForm == 1) { ?>
		
			<br>
			<p>Use the form below to create or join a pool (limit 2 per person):</p>
			<div class="joinPoolForm">
				<form id="joinPool_form" align="center" action="managePools.php" method="post">
					<input type="text" name="poolID" placeholder="Pool ID (letters only)" autocomplete="off"><br><br>
					<input type="submit" name="buttonName" value="Create">
					<input type="submit" name="buttonName" value="Join">
				</form>
			</div>
			
		<?PHP 	
		}
		else {
			echo "You have joined the maximum allowable number of pools. You must leave one of them if you would like to create or join a new pool.";
		}
		
		$mysqli->close(); // close connection 
		
	}
	
	
} ?>


<p class="message" style="text-align:center"><a href="javascript: window.parent.closePopup('poolsPopup')">Close</a></p>
			
</body>
 
</html>

