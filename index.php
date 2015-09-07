<?php

// include database class
require('database.class.php');

// start session 
session_start();

Database::refresh_session_if_staying_logged_in();

// include the files depending on the session status
if (isset($_SESSION['id'])) { // user logged in
  require('home.inc.php');
} else { // user not logged in
  require('login.inc.php');
}
?>
