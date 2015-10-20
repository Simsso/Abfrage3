<?php
class Validation {
  // is email
  //
  // @param string val: the string to check
  // 
  // @return bool: true if the given string is a valid email-address
  static function is_email($val) {
    if ((bool)(preg_match("/^([a-z0-9+_-]+)(.[a-z0-9+_-]+)*@([a-z0-9-]+.)+[a-z]{2,6}$/ix",$val)) && $val != NULL) {
      return true;
    } else {
      return false;
    }
  }


  // is password
  // 
  // @param string val: the string to check
  //
  // @return bool: true if the given string is a valid password
  static function is_password($val) {
    // length between 4 and 20
    if ((bool)(preg_match("/^.{4,20}$/",$val)) && $val != NULL) {
      return true;
    } else {
      return false;
    }
  }


  // format text
  //
  // escapes HTMl special characters
  //
  // @param string val: string to handle
  //
  // @return string: passed string with replaced HTML special chars
  static function format_text ($val) {
    $val = htmlspecialchars($val); // htmlspecialchars — convert special characters to HTML entities
    return $val;
  }
}
?>
