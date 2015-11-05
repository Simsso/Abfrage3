<?php
 
if(isSet($_GET['lang'])) {
  $lang = $_GET['lang'];
   
  setcookie('lang', $lang, time() + (3600 * 24 * 30));
} else if(isSet($_COOKIE['lang'])) {
  $lang = $_COOKIE['lang'];
} else {
  $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}
 
switch ($lang) {
  case 'en':
  $lang_file = 'lang.en.inc.php';
  break;
 
  case 'de':
  $lang_file = 'lang.de.inc.php';
  break;
 
  default:
  $lang_file = 'lang.en.inc.php';
 
}

$l = array();
include('lang.en.inc.php');
include_once($lang_file);

?>