<?php

// include database class
require('database.class.php');

// start session 
session_start();

Database::refresh_session_if_staying_logged_in();

// include language files
require_once('lang/lang.inc.php');

if (isset($_GET['basic-page'])) {
	require('basic.inc.php');
} else {
	// include the files depending on the session status
	if (isset($_SESSION['id'])) { // user logged in
	  require('home.inc.php');
	} else { // user not logged in
	  require('login.inc.php');
	}
}
?>
