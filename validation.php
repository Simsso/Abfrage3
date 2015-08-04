<?php
	class Validation {
		static function is_email($val) {
			if ((bool)(preg_match("/^([a-z0-9+_-]+)(.[a-z0-9+_-]+)*@([a-z0-9-]+.)+[a-z]{2,6}$/ix",$val)) && $val != NULL) {
				return true;
			} else {
				return false;
			}
		}
		
		static function is_password($val) {
			if ((bool)(preg_match("/^.{4,20}$/",$val)) && $val != NULL) {
				return true;
			} else {
				return false;
			}
		}
		
		static function format_text ($val) {
			$val = htmlspecialchars($val);
			return $val;
		}
	}
?>