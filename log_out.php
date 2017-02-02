<?PHP
	session_start();
	session_destroy();
	
	// redirect to home page
    echo "<meta http-equiv='refresh' content='0;url=home.php'>";
?>