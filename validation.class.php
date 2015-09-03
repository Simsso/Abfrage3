<?php
class Validation {
  // class validates strings
  
  // returns true if the given string is a valid email-address
  static function is_email($val) {
    if ((bool)(preg_match("/^([a-z0-9+_-]+)(.[a-z0-9+_-]+)*@([a-z0-9-]+.)+[a-z]{2,6}$/ix",$val)) && $val != NULL) {
      return true;
    } else {
      return false;
    }
  }

  // returns true if the given string is a valid password
  static function is_password($val) {
    // length between 4 and 20
    if ((bool)(preg_match("/^.{4,20}$/",$val)) && $val != NULL) {
      return true;
    } else {
      return false;
    }
  }

  // escapes HTMl special characters
  static function format_text ($val) {
    $val = htmlspecialchars($val); // htmlspecialchars — convert special characters to HTML entities
    return $val;
  }
}
?>
