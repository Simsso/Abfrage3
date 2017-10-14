<?php
 
if(isset($_GET['lang'])) { // get parameter
  $lang = $_GET['lang'];
  setcookie('lang', $lang, time() + (3600 * 24 * 30));
}
else if(isset($_COOKIE['lang'])) { // cookie
  $lang = $_COOKIE['lang'];
}
else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { // browser settings
  $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}
else { // default
  $lang = 'en';
}
 
switch ($lang) {
  case 'en':
    $lang_file = 'lang.en.inc.php';
    break;
 
  case 'de':
    $lang_file = 'lang.de.inc.php';
    break;
 
  default:
    $lang = 'en';
    $lang_file = 'lang.en.inc.php';
    break;
}

$l = array();
include('lang.en.inc.php');
include_once($lang_file);

?>