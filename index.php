<?php

$start_time = microtime(true); // measure execution time

// include database class
require('database.class.php');

// start session 
session_start();

// if the session has expired but a stay logged in cookie is set update the session cookie
Database::refresh_session_if_staying_logged_in();

// include language files
require('lang/lang.inc.php');

// logo file
include('logo.inc.php');

if (isset($_GET['basic-page'])) {
	// basic page requested
	require('basic.inc.php');
} else {
	// include the files depending on the session status
	if (isset($_SESSION['id'])) { // user logged in
	  require('home.inc.php');
	} else { // user not logged in
	  require('login.inc.php');
	}
}

// echo serverside execution time
echo "<!-- serverside execution time: " . (microtime(true) - $start_time) * 1000 . " ms -->";
?>
