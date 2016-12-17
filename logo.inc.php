<?php

// logo url

// christmas
if (date("n") == 12 && date("j") <= 26) { 
	// month december and before christmas
	$logo_suffix = "christmas";
}

// easter
if (abs(easter_date(date("Y")) - time()) <= 3600 * 24 * 7) {
	// seven days around the easter date
	$logo_suffix = "easter";
}

// new year's eve
if (abs(mktime(0, 0, 0, 13, 0, date("Y")) - time()) <= 3600 * 24 * 2) {
	// two days around new year's eve
	$logo_suffix = "new-years-eve";
}



// build path
$logo_path = "img/logo";
if (isset($logo_suffix)) {
	$logo_path .= "-" . $logo_suffix;
}
$logo_path .= ".svg";

?>