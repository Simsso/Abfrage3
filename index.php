<?php
  	session_start();
	if (isset($_SESSION['id'])) {
		require('home.inc.php');
	} else {
		require('login.inc.php');
	}
?>