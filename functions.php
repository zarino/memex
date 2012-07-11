<?php

$GLOBALS['_PUT'] = array();
$GLOBALS['_DELETE'] = array();

if($_SERVER['REQUEST_METHOD'] == 'PUT') {
	parse_str(file_get_contents("php://input"),$post_vars);
	$GLOBALS['_PUT'] = $post_vars;
} else if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
	parse_str(file_get_contents("php://input"),$post_vars);
	$GLOBALS['_DELETE'] = $post_vars;
}

function pretty_print_r($arg1, $arg2='', $suffix=''){
	if(is_array($arg1) || is_object($arg1)){
		$array = $arg1;
	} else if(is_array($arg2) || is_object($arg2)) {
		$array = $arg2;
	}
	if(is_string($arg1)){
		$prefix = $arg1;
	} else if(is_string($arg2)) {
		$prefix = $arg2;
	}
	if(isset($prefix) && isset($array)){
		print $prefix . '<pre>';
		print_r($array);
		print '</pre>' . $suffix;
	} else {
	    trigger_error("Incorrect parameters supplied to pretty_print_r() - function takes an optional string prefix, a required array, and an optional string suffix", E_USER_ERROR);
	}
}

?>