
<?PHP

include 'config.php'; 
include 'correctPicks.php'; 

session_start(); 
// $_currentSessionId = session_id(); 

$errorMessage = "";

// connect to database 
$mysqli = mysqli_connect(HOSTNAME, DB_USER, DB_PASS, DB_NAME); 

// check connection
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
} else {
	// echo 'Connected successfully';
}

// check database 
if ($result = $mysqli->query("SELECT DATABASE()")) {
	$row = $result->fetch_row();
	// printf("Default database is %s.\n", $row[0]); echo '<br>'; 
	$result->close();
} else {
	die('Can\'t use ' . DB_NAME . ': ' . mysql_error()); 
}

// FETCH ALL PICKS FOR ALL USERS (FOR SCOREBOARD)
$allPicksQuery = "SELECT * FROM user_picks"; 
$allPicksResult = $mysqli->query($allPicksQuery);

// FETCH POOL AFFILIATIONS ALL USERS
	
	
// INITIALIZE SESSION IF USER IS LOGGING IN 	
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	// extract relevant parameters from POST structure 
	$uname = $_POST['uname'];
	$pword = $_POST['pword'];
	
	// security shit 
	$uname = htmlspecialchars($uname);
	$pword = htmlspecialchars($pword);	
	$uname = $mysqli->real_escape_string($uname);
	$pword = $mysqli->real_escape_string($pword);

	// hash inputted password
	$nonce = md5('registration-' . $uname . NONCE_SALT); 
	$upass = hash_hmac('sha512', $pword . $nonce, SITE_KEY); 

	// search database for inputted login credentials 
	$strQuery = "SELECT pw,email FROM user_list WHERE user = '$uname' AND pw = '$upass'";		
	$aResult = $mysqli->query($strQuery); 
	
	if ($aResult) {
		$num_rows = $aResult->num_rows; 
		echo "$num_rows result(s) found for user $uname<br>";
		if ($num_rows == 1) { 
			// set session variables
			$_SESSION['login'] = "1"; 
			$_SESSION['user'] = "$uname";
		} elseif ($num_rows == 0) {
			$errorMessage = "invalid login"; 
		} else {
			$errorMessage = "found more than one instance of $uname"; 	
		}
	}
	else {
		echo "sql_query failed"; 
	}
	
}


// FETCH DATA FOR ACTIVE USER 
if (isset($_SESSION['login'])) {
	
	$aUser = $_SESSION['user'];
	echo "$aUser has logged in";
	
	// fetch user pool(s)
	/*$poolQuery = "SELECT pool1 FROM user_list WHERE user = '$aUser'"; 
	$aResult = $mysqli->query($poolQuery);	
	$userPoolArray = $aResult->fetch_array(MYSQLI_ASSOC);
	$userPool = $userPoolArray['pool']; */
	
	// fetch user picks 
	$picksQuery = "SELECT * FROM user_picks WHERE user = '$aUser'"; 
	$aResult = $mysqli->query($picksQuery);		
	$userPicks = $aResult->fetch_array(MYSQLI_ASSOC);
	
	/*var_dump($userPool);
	var_dump($userPicks);
	var_dump($correctPicks); */
}
else {
	
	// no active user
	$userPool = ''; 
	$userPicks = [
		'E18g' => '', 'E18t' => '', 'E45g' => '', 'E45t' => '', 'E36g' => '', 'E36t' => '',
		'E27g' => '', 'E27t' => '', 'W18g' => '', 'W18t' => '', 'W45g' => '', 'W45t' => '', 
		'W36g' => '', 'W36t' => '', 'W27g' => '', 'W27t' => '', 
		'ESF1g' => '', 'ESF1t' => '', 'ESF2g' => '', 'ESF2t' => '',
		'WSF1g' => '', 'WSF1t' => '', 'WSF2g' => '', 'WSF2t' => '',
		'EFg' => '', 'EFt' => '', 'WFg' => '', 'WFt' => '', 'Fg' => '', 'Ft' => '',	
	];
	
}


$mysqli->close();

?>

<html>

<head>
	
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>NBA Playoffs Bracket 2016</title>
	
	<!-- CSS stylesheets -->
	<link rel="stylesheet" href="css/home_style.css" type="text/css"  />
	<link rel="stylesheet" href="css/bracket_style.css" type="text/css"  />
	<link rel="stylesheet" href="css/leaderboard_style.css" type="text/css"  />
	
	<!-- javascript functions -->
	<script src="js/enter_picks.js"></script>
	<script type="text/javascript">	
	
		function activateTab(pageId, t_id) {
			// activate the tab 
			var tabCtrl = document.getElementById('tabCtrl');
			var pageToActivate = document.getElementById(pageId);
			for (var i = 0; i < tabCtrl.childNodes.length; i++) {		
				var node = tabCtrl.childNodes[i];
				if (node.nodeType == 1) { // Element 
					node.style.display = (node == pageToActivate) ? 'block' : 'none'; }
			}
			
			// activate tab title in menu bar 
			var list = document.getElementById('menu').children[0];
			cur_title = document.getElementById(t_id); 
			for(var i=0; i<list.children.length; i++){
				var cur = list.children[i];
				if (t_id == cur.id) {
					cur.classList.add("active");
				} else {
					cur.classList.remove("active");
				}
			}
		} 		
		
		function openPopup(el_id) {
			document.getElementById(el_id).style.display = 'block';
		}

		function closePopup(el_id) {
			document.getElementById(el_id).style.display = 'none';
		}
		
		function msgBox(txt) {
			alert(txt);			
		}

	</script>
	
</head>



<body>


<div id="pageContainer">


	<div id="menu"> <!-- MENU BAR -->
		<ul>
			<li id="t1"><a href="JavaScript:activateTab('tab1','t1')"><b>2016 NBA Playoffs</b></a></li>
			<li id="t2" class="active"><a href="JavaScript:activateTab('tab2','t2')"><b>My Bracket</b></a></li>
			<li id="t3"><a href="JavaScript:activateTab('tab3','t3')"><b>Leaderboard</b></a></li>	
			<?PHP 
			if (isset($_SESSION['login'])) { 
				$aUser = $_SESSION['user']; 			
			?>
				<li style="float:right"><a href="log_out.php">Log out</a></li>	
				<li style="float:right"><a href="#">Delete account</a></li>	
				<li style="float:right"><a href="#"><font color="yellow"><b><?PHP echo "Welcome, $aUser"; ?></b></font></a></li>
			<?PHP }
			else { ?>
				<li style="float:right"><a href="JavaScript:openPopup('loginPopup');">Login</a></li>
				<li style="float:right"><a href="JavaScript:openPopup('regPopup');">Register</a></li>
			<?PHP } ?>
		</ul>		
	</div>
	
	
	<div id="tabCtrl">	
	
	
		<!-- LOGIN POPUP -->
		<div id="loginPopup">
			<form class="loginForm" align="center" action="home.php" method="post">
				<input type="text" name="uname" required placeholder="Username" autocomplete="off">
				<input type="password" name="pword" required placeholder="Password" autocomplete="off">
				<input type="submit" value="Login">
				<p class="message"><a href="Javascript: msgBox('Too bad. Go find that email bitch.');">Forgot password?</a></p>
				<p class="message"><a href="Javascript: closePopup('loginPopup');">Close</a></p>
			</form>			
		</div>
			
			
		<!-- REGISTRATION POPUP -->
		<iframe id="regPopup" align="center" src="register.php"></iframe><br>
		
		
		<!-- CREATE/JOIN POOL POPUP -->
		<iframe id="poolsPopup" align="center" src="managePools.php"></iframe><br>
	
	
		<!-- PLAYOFFS TAB -->
		<div id="tab1" style=display:none> 
			THIS IS THE PLAYOFFS BRACKET PAGE
			<div class="nbaBracketModule">
				
			</div>
		</div> <!-- END PLAYOFFS TAB -->

		
		<!-- MY BRACKET TAB -->
		<div id="tab2" style=display:block> 
			THIS IS THE USER BRACKET PAGE 
			<div class="nbaBracketModule">	
			
				<form id="picks_form" action='savePicks.php' method='post'></form>
				
				<div class="east">
					<div class="firstRound">
						<div id="nbaBracketl" class="matchup series1">
							<input id="ECFR1" class="teamLogo" type="image" name="Cavaliers" src="logos/Cavaliers.gif" onclick="addPick('ECFR1','ECSF1','E18t');">
							<br><b>Cleveland</b>
							<select form="picks_form" name="E18g" class="selectGames"> 
								<option <?PHP if ($userPicks['E18g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['E18g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['E18g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['E18g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="ECFR8" class="teamLogo" type="image" name="Pistons" src="logos/Pistons.gif" onclick="addPick('ECFR8','ECSF1','E18t');">
							<br><b>Detroit</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="E18t" name="E18t" value="<?PHP print $userPicks['E18t']; ?>">
						</div>
						
						<div id="nbaBracketm" class="matchup series4">
							<input id="ECFR4" class="teamLogo" type="image" name="Celtics" src="logos/Celtics.gif" onclick="addPick('ECFR4','ECSF2','E45t');">
							<br><b>Boston</b>
							<select form="picks_form" name="E45g" class="selectGames"> 
								<option <?PHP if ($userPicks['E45g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['E45g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['E45g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['E45g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="ECFR5" class="teamLogo" type="image" name="Heat" src="logos/Heat.gif" onclick="addPick('ECFR5','ECSF2','E45t');">
							<br><b>Miami</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="E45t" name="E45t" value="<?PHP print $userPicks['E45t']; ?>">
						</div>
						
						<div id="nbaBracketn" class="matchup series3">
							<input id="ECFR3" class="teamLogo" type="image" name="Hawks" src="logos/Hawks.gif" onclick="addPick('ECFR3','ECSF3','E36t');">
							<br><b>Atlanta</b>
							<select form="picks_form" name="E36g" class="selectGames"> 
								<option <?PHP if ($userPicks['E36g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['E36g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['E36g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['E36g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="ECFR6" class="teamLogo" type="image" name="Hornets" src="logos/Hornets.gif" onclick="addPick('ECFR6','ECSF3','E36t');">
							<br><b>Charlotte</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="E36t" name="E36t" value="<?PHP print $userPicks['E36t']; ?>">
						</div>
						
						<div id="nbaBracketo" class="matchup series2">
							<input id="ECFR2" class="teamLogo" type="image" name="Raptors" src="logos/Raptors.gif" onclick="addPick('ECFR2','ECSF4','E27t');">
							<br><b>Toronto</b>
							<select form="picks_form" name="E27g" class="selectGames"> 
								<option <?PHP if ($userPicks['E27g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['E27g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['E27g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['E27g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="ECFR7" class="teamLogo" type="image" name="Pacers" src="logos/Pacers.gif" onclick="addPick('ECFR7','ECSF4','E27t');">
							<br><b>Indiana</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="E27t" name="E27t" value="<?PHP print $userPicks['E27t']; ?>">
						</div>			
					</div>				
				</div>
				
				<div class="west">
					<div class="firstRound">				
						<div id="nbaBracketa" class="matchup series1">
							<input id="WCFR1" class="teamLogo" type="image" name="Warriors" src="logos/Warriors.gif" onclick="addPick('WCFR1','WCSF1','W18t');">
							<br><b>Golden State</b>
							<select form="picks_form" name="W18g" class="selectGames"> 
								<option <?PHP if ($userPicks['W18g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['W18g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['W18g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['W18g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="WCFR8" class="teamLogo" type="image" name="Rockets" src="logos/Rockets.gif" onclick="addPick('WCFR8','WCSF1','W18t');">
							<br><b>Houston</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="W18t" name="W18t" value="<?PHP print $userPicks['W18t']; ?>">
						</div>
						
						<div id="nbaBracketb" class="matchup series4">
							<input id="WCFR4" class="teamLogo" type="image" name="Clippers" src="logos/Clippers.gif" onclick="addPick('WCFR4','WCSF2','W45t');">
							<br><b>Los Angeles</b>
							<select form="picks_form" name="W45g" class="selectGames"> 
								<option <?PHP if ($userPicks['W45g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['W45g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['W45g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['W45g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="WCFR5" class="teamLogo" type="image" name="Grizzlies" src="logos/Grizzlies.gif" onclick="addPick('WCFR5','WCSF2','W45t');">
							<br><b>Memphis</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="W45t" name="W45t" value="<?PHP print $userPicks['W45t']; ?>">
						</div>
						
						<div id="nbaBracketc" class="matchup series3">
							<input id="WCFR3" class="teamLogo" type="image" name="Thunder" src="logos/Thunder.gif" onclick="addPick('WCFR3','WCSF3','W36t');">
							<br><b>Oklahoma City</b>
							<select form="picks_form" name="W36g" class="selectGames"> 
								<option <?PHP if ($userPicks['W36g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['W36g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['W36g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['W36g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="WCFR6" class="teamLogo" type="image" name="Mavericks" src="logos/Mavericks.gif" onclick="addPick('WCFR6','WCSF3','W36t');">
							<br><b>Dallas</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="W36t" name="W36t" value="<?PHP print $userPicks['W36t']; ?>">
						</div>
						
						<div id="nbaBracketd" class="matchup series2">
							<input id="WCFR2" class="teamLogo" type="image" name="Spurs" src="logos/Spurs.gif" onclick="addPick('WCFR2','WCSF4','W27t');">
							<br><b>San Antonio</b>
							<select form="picks_form" name="W27g" class="selectGames"> 
								<option <?PHP if ($userPicks['W27g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['W27g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['W27g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['W27g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input id="WCFR7" class="teamLogo" type="image" name="Blazers" src="logos/Blazers.gif" onclick="addPick('WCFR7','WCSF4','W27t');">
							<br><b>Portland</b>
							<input type="hidden" class="selectTeam" form="picks_form" id="W27t" name="W27t" value="<?PHP print $userPicks['W27t']; ?>">
						</div> 				
					</div>				
				</div>

				<div class="east">
					<div class="confSemifinals">
						<div id="nbaBracketj" class="matchup series5">					
							<input name="<?PHP print $userPicks['E18t']; ?>" id="ECSF1" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['E18t']; ?>.gif" onclick="addPick('ECSF1','ECF1','ESF1t');">
							<select form="picks_form" name="ESF1g" class="selectGames"> 
								<option <?PHP if ($userPicks['ESF1g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['ESF1g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['ESF1g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['ESF1g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input name="<?PHP print $userPicks['E45t']; ?>" id="ECSF2" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['E45t']; ?>.gif" onclick="addPick('ECSF2','ECF1','ESF1t');">
							<input type="hidden" class="selectTeam" form="picks_form" id="ESF1t" name="ESF1t" value="<?PHP print $userPicks['ESF1t']; ?>">
						</div>
						
						<div id="nbaBracketk" class="matchup series6">
							<input name="<?PHP print $userPicks['E36t']; ?>" id="ECSF3" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['E36t']; ?>.gif" onclick="addPick('ECSF3','ECF2','ESF2t');">
							<select form="picks_form" name="ESF2g" class="selectGames"> 
								<option <?PHP if ($userPicks['ESF2g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['ESF2g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['ESF2g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['ESF2g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input name="<?PHP print $userPicks['E27t']; ?>" id="ECSF4" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['E27t']; ?>.gif" onclick="addPick('ECSF4','ECF2','ESF2t');">
							<input type="hidden" class="selectTeam" form="picks_form" id="ESF2t" name="ESF2t" value="<?PHP print $userPicks['ESF2t']; ?>">
						</div>
					</div>
				</div>
				
				<div class="west">
					<div class="confSemifinals">
						<div id="nbaBrackete" class="matchup series5">
							<input name="<?PHP print $userPicks['W18t']; ?>" id="WCSF1" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['W18t']; ?>.gif" onclick="addPick('WCSF1','WCF1','WSF1t');">
							<select form="picks_form" name="WSF1g" class="selectGames"> 
								<option <?PHP if ($userPicks['WSF1g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['WSF1g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['WSF1g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['WSF1g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input name="<?PHP print $userPicks['W45t']; ?>" id="WCSF2" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['W45t']; ?>.gif" onclick="addPick('WCSF2','WCF1','WSF1t');">
							<input type="hidden" class="selectTeam" form="picks_form" id="WSF1t" name="WSF1t" value="<?PHP print $userPicks['WSF1t']; ?>">
						</div>
						<div id="nbaBracketf" class="matchup series6">
							<input name="<?PHP print $userPicks['W36t']; ?>" id="WCSF3" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['W36t']; ?>.gif" onclick="addPick('WCSF3','WCF2','WSF2t');">
							<select form="picks_form" name="WSF2g" class="selectGames"> 
								<option <?PHP if ($userPicks['WSF2g']==4) { print 'selected'; } ?> value="4">4 games</option>
								<option <?PHP if ($userPicks['WSF2g']==5) { print 'selected'; } ?> value="5">5 games</option>
								<option <?PHP if ($userPicks['WSF2g']==6) { print 'selected'; } ?> value="6">6 games</option>
								<option <?PHP if ($userPicks['WSF2g']==7) { print 'selected'; } ?> value="7">7 games</option>
							</select>
							<input name="<?PHP print $userPicks['W27t']; ?>" id="WCSF4" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['W27t']; ?>.gif" onclick="addPick('WCSF4','WCF2','WSF2t');">
							<input type="hidden" class="selectTeam" form="picks_form" id="WSF2t" name="WSF2t" value="<?PHP print $userPicks['WSF2t']; ?>">
						</div>
					</div>				
				</div>
				
				<div class="east">
					<div class="confFinals">
						<input name="<?PHP print $userPicks['ESF1t']; ?>" id="ECF1" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['ESF1t']; ?>.gif" onclick="addPick('ECF1','FE','EFt');" >
						<select form="picks_form" name="EFg" class="selectGames"> 
							<option <?PHP if ($userPicks['EFg']==4) { print 'selected'; } ?> value="4">4 games</option>
							<option <?PHP if ($userPicks['EFg']==5) { print 'selected'; } ?> value="5">5 games</option>
							<option <?PHP if ($userPicks['EFg']==6) { print 'selected'; } ?> value="6">6 games</option>
							<option <?PHP if ($userPicks['EFg']==7) { print 'selected'; } ?> value="7">7 games</option>
						</select>
						<input name="<?PHP print $userPicks['ESF2t']; ?>" id="ECF2" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['ESF2t']; ?>.gif" onclick="addPick('ECF2','FE','EFt');">
						<input type="hidden" class="selectTeam" form="picks_form" id="EFt" name="EFt" value="<?PHP print $userPicks['EFt']; ?>">
					</div>
				</div>
				
				<div class="west">
					<div class="confFinals">
						<input name="<?PHP print $userPicks['WSF1t']; ?>" id="WCF1" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['WSF1t']; ?>.gif" onclick="addPick('WCF1','FW','WFt');">
						<select form="picks_form" name="WFg" class="selectGames"> 
							<option <?PHP if ($userPicks['WFg']==4) { print 'selected'; } ?> value="4">4 games</option>
							<option <?PHP if ($userPicks['WFg']==5) { print 'selected'; } ?> value="5">5 games</option>
							<option <?PHP if ($userPicks['WFg']==6) { print 'selected'; } ?> value="6">6 games</option>
							<option <?PHP if ($userPicks['WFg']==7) { print 'selected'; } ?> value="7">7 games</option>
						</select>
						<input name="<?PHP print $userPicks['WSF2t']; ?>" id="WCF2" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['WSF2t']; ?>.gif" onclick="addPick('WCF2','FW','WFt');">
						<input type="hidden" class="selectTeam" form="picks_form" id="WFt" name="WFt" value="<?PHP print $userPicks['WFt']; ?>">
					</div>
				</div>
			
				<div class="east">
					<div class="confChamp">
						<input name="<?PHP print $userPicks['EFt']; ?>" id="FE" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['EFt']; ?>.gif" onclick="addPick('FE','CHAMP','Ft');">
					</div>
				</div>
				
				<div class="west">
					<div class="confChamp">
						<input name="<?PHP print $userPicks['WFt']; ?>" id="FW" class="teamLogo2" type="image" src="logos/<?PHP print $userPicks['WFt']; ?>.gif" onclick="addPick('FW','CHAMP','Ft');">
					</div>
				</div>
	
				<div class="finals">
					<div class= "finalsGames">
						<select form="picks_form" name="Fg" class="selectGames"> 
							<option <?PHP if ($userPicks['Fg']==4) { print 'selected'; } ?> value="4">4 games</option>
							<option <?PHP if ($userPicks['Fg']==5) { print 'selected'; } ?> value="5">5 games</option>
							<option <?PHP if ($userPicks['Fg']==6) { print 'selected'; } ?> value="6">6 games</option>
							<option <?PHP if ($userPicks['Fg']==7) { print 'selected'; } ?> value="7">7 games</option>
						</select>
					</div>
				</div>
	
				<div class="finals">
					<div class= "finalsChamp">
						<input name="<?PHP print $userPicks['Ft']; ?>" id="CHAMP" class="teamLogo3" type="image" src="logos/<?PHP print $userPicks['Ft']; ?>.gif">
						<input type="hidden" class="selectTeam" form="picks_form" id="Ft" name="Ft" value="<?PHP print $userPicks['Ft']; ?>">
					</div>
				</div>
				
				<div class="instructions">
					<h2><b>Instructions</b></h2>
					<p align="left">
					1. Predict the winner of each matchup by clicking on the team logo.<br><br>
					2. Use the selecter in between the logos of each matchup to predict the number of games the series will last.<br><br>
					3. Click the "SAVE PICKS" button below to lock in your picks! (You must create an account and be logged in.)<br><br>
					4. Check the Leaderboard for scoring rules.
					</p>
				</div>

			</div>
			
			<button class="clearPicksButton" onclick="clearAllPicks();"><b>Clear all picks</b></button>	

			<?PHP 
			if (isset($_SESSION['login'])) { ?>
				<button class="savePicksButton" type="submit" form="picks_form" value="Submit"><b>SAVE PICKS</b></button>	
			<?PHP } ?>

		</div> <!-- END MY BRACKET TAB --> 

		
		<!-- LEADERBOARD TAB -->
		<div id="tab3" style=display:none> 
			THIS IS THE LEADERBOARD PAGE<br>
			<a href="JavaScript:openPopup('loginPopup');">View Scoring Guidelines</a><br>
			<a href="JavaScript:openPopup('poolsPopup');">Manage My Pools</a>
			
			<div class="scoreboard_area">

				<table align="center" id="score_table" border="1">

					<!-- table headers -->
					<tr>	
						<td align="center"><b>Rank</b></td>
						<td align="center"><b>User</b></td>
						<td align="center"><b>Bracket Score</b></td>
						<td align="center"><b>Max Score</b></td>
						<td align="center"><b>NBA Champion</b></td>
						<td align="center"><b>Bracket</b></td>
					</tr>
					
					<?PHP
					
						$allUsersArray = array(); // empty array to sort post-processed data for all users 
						
						// initialize arrays to display on the leaderboard 
						$lbUser = array(); $lbScore = array(); $lbScoreMax = array(); $lbChamp = array(); 
					
						while($row = $allPicksResult->fetch_assoc()) {
							
							// var_dump($row); 
							
							// TODO: compute bracket score and max possible score for each user 
							$score = 0; 
							$scoreMax = 0; 
							
							array_push($lbUser, $row['user']); 
							array_push($lbScore, $score); 
							array_push($lbScoreMax, $scoreMax); 
							array_push($lbChamp, $row['Ft']); 
						}

						// TODO: sort array of user scores
						
						// TODO: loop through sorted array and populate the leaderboard table 
						
						for ($n = 0; $n <= count($lbUser)-1; $n++) { ?>
							
							<!-- create new row in table and fill in user data --> 
							<tr>
								<td align="center"><?PHP echo $n+1; ?></td> <!-- rank -->
								<td align="center"><?PHP echo $lbUser[$n]; ?></td> <!-- user -->
								<td align="center"><?PHP echo $lbScore[$n]; ?></td> 
								<td align="center"><?PHP echo $lbScoreMax[$n]; ?></td> 
								<td align="center"><?PHP echo $lbChamp[$n]; ?></td> 
								<td align="center"><a href="#">view</a></td> 
							</tr>
							
						<?PHP } ?>

				</table> 
			
			</div>
			
		</div> <!-- END LEADERBOARD TAB --> 
		


	</div> <!-- END TABCTRL --> 

	<br>
	<b><p align="center"><?PHP echo "&copy";?> 2016 Ronny Li<br>All Rights Reserved</p></b>
	<p align="center"><b>DISCLAIMER:</b><br>This website is <b>not</b> affiliated with the National Basketball Association.<br>Team logos were downloaded from <a href="http://www.sportslogos.net/teams/list_by_league/6">sportslogos.net</a>.</p><br><br><br><br>
	

</div> <!-- END PAGE CONTAINER -->

</body>

</html>


