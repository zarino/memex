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

function pluralise($number, $plural_suffix='s', $singular_suffix=''){
	if(is_int($number) || is_float($number)){
		if($number == 1){
			return $singular_suffix;
		} else {
			return $plural_suffix;
		}
	} else {
		trigger_error("pluralise() was passed a non-integer, non-float argument", E_USER_ERROR);
	}
}

# Turns an array into a list like: "First, second and third"
function human_list($items){
	$last = array_pop($items);
	return implode(', ', $items) . ' and ' . $last;
}

# A sort of fake endpoint function to show HTTP Request options
function OPTIONS($methods){
	print 'This endpoint accepts ' . human_list(array_keys($methods)) . ' requests:';
	foreach($methods as $method => $description){
		print ' Use ' . $method . ' to ' . $description . ';';
	}
	print ' Or use OPTIONS to see these options again.';
}

# Called when an unsupported Request Method is used
function unexpected_method($methods){
	if($_SERVER['REQUEST_METHOD'] != 'OPTIONS'){
		header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed");
	}
	header('Allow: ' . implode(', ', array_keys($methods)) . ', OPTIONS');
	OPTIONS($methods);
}

function respond_to_method($method, $methods){
	if(in_array($method, array_keys($methods))){
		header('Allow: ' . implode(', ', array_keys($methods)) . ', OPTIONS');
		$method();
	} else {
		unexpected_method($methods);
	}
	pretty_print_r('<br/><br/>', $_SERVER);
}

function uri_part($index){
	$parts = array_values(array_filter(explode('/', $_SERVER['REQUEST_URI'])));
	if($index < count($parts)){
		return $parts[$index];
	} else {
		trigger_error("Cannot return part #" . $index . ': REQUEST_URI "' . $_SERVER['REQUEST_URI'] . '" only has ' . count($parts) . ' part' . pluralise(count($parts)), E_USER_ERROR);
	}
}

?>