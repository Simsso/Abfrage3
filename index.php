<?php
session_start();

// include the files depending on the session status
if (isset($_SESSION['id'])) { // user logged in
  require('home.inc.php');
} else { // user not logged in
  require('login.inc.php');
}
?>
