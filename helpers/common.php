<?php
// Various helper functions

// makes sure all values in array are numbers. Typically used to sanitize POST data from multiple checkboxes
function qbuddy_int_array($value) {
   if(empty($value) or !is_array($value)) return array();
   $value = array_filter($value, 'is_numeric');
   return $value;
}

// safe redirect
function qbuddy_redirect($url) {
	echo "<meta http-equiv='refresh' content='0;url=$url' />"; 
	exit;
}