<?php

// logo url

// christmas
if (date("n") == 12 && date("j") <= 26) { 
	// month december and before christmas
	$logo_suffix = "christmas";
}



// build path
$logo_path = "img/logo";
if (isset($logo_suffix)) {
	$logo_path .= "-" . $logo_suffix;
}
$logo_path .= ".svg";

?>