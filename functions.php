<?php

/* DEALING WITH REQUESTS, ISSUING RESPONSES */

function handle_put_delete(){
	$GLOBALS['_PUT'] = $GLOBALS['_DELETE'] = array();
	if($_SERVER['REQUEST_METHOD'] == 'PUT') {
		parse_str(file_get_contents("php://input"),$post_vars);
		$GLOBALS['_PUT'] = $post_vars;
	} else if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
		parse_str(file_get_contents("php://input"),$post_vars);
		$GLOBALS['_DELETE'] = $post_vars;
	}
}

# A sort of fake endpoint function to show HTTP Request options
function OPTIONS($methods){
	print 'This endpoint accepts ' . human_list(array_keys($methods)) . ' requests:';
	foreach($methods as $method => $description){
		print ' Use ' . $method . ' to ' . $description . ';';
	}
	print ' Or use OPTIONS to see these options again.';
}

function parse_request(){
	# set default response formats
	if($_SERVER['REQUEST_URI'] == '/'){
		$GLOBALS['_SERVER']['RESPONSE_FORMAT'] = 'html';
	} else {
		$GLOBALS['_SERVER']['RESPONSE_FORMAT'] = 'json';
	}
	# use JSON or HTML format if specified in REQUEST_URI
	if(ends_with($_SERVER['REQUEST_URI'], '.json')){
		$GLOBALS['_SERVER']['RESPONSE_FORMAT'] = 'json';
	} else if(ends_with($_SERVER['REQUEST_URI'], '.html')){
		$GLOBALS['_SERVER']['RESPONSE_FORMAT'] = 'html';
	}
	# allow override using HTTP headers
	if((isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT']=='application/json') || (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE']=='application/json')){
		$GLOBALS['_SERVER']['RESPONSE_FORMAT'] = 'json';
	} else if((isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT']=='text/html') || (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE']=='text/html')){
		$GLOBALS['_SERVER']['RESPONSE_FORMAT'] = 'html';
	}
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

# Called when an unsupported Request Method is used
function unexpected_method($methods){
	if($_SERVER['REQUEST_METHOD'] != 'OPTIONS'){
		header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed");
	}
	header('Allow: ' . implode(', ', array_keys($methods)) . ', OPTIONS');
	OPTIONS($methods);
}

/* UTILITY FUNCTIONS */

# Turns an alphanumeric hash into an integer (eg: '2n9c' -> 123456)
function hash_to_integer($string, $base = ALLOWED_CHARS){
    $length = strlen($base);
    $size = strlen($string) - 1;
    $string = str_split($string);
    $out = strpos($base, array_pop($string));
    foreach($string as $i => $char){
        $out += strpos($base, $char) * pow($length, $size - $i);
    }
    return $out;
}

# Turns an integer into an alphanumeric hash (eg: 123456 -> '2n9c')
function integer_to_hash($integer, $base = ALLOWED_CHARS){
    $length = strlen($base);
    $out = '';
    while($integer > $length - 1){
        $out = $base[fmod($integer, $length)] . $out;
        $integer = floor( $integer / $length );
    }
    return $base[$integer] . $out;
}


function connect_to_database(){
	global $dbc;
	$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error() );
	mysqli_set_charset($dbc, 'utf8');
}

function setup_database(){
	mysqli_query($dbc, 'CREATE TABLE IF NOT EXISTS items (id INT PRIMARY KEY AUTO_INCREMENT, title VARCHAR(255) NULL, content TEXT NULL, source VARCHAR(32) NULL, url VARCHAR(255) NULL, added DATETIME NULL, viewed DATETIME NULL, updated DATETIME NULL, deleted DATETIME NULL)');
	mysqli_query($dbc, 'CREATE TABLE IF NOT EXISTS reminders (id INT PRIMARY KEY AUTO_INCREMENT, item_id INT NULL, reminder_datetime DATETIME NULL, medium VARCHAR(32) NULL, destination VARCHAR(255) NULL, content TEXT NULL, source VARCHAR(32) NULL, added DATETIME NULL, reminded DATETIME NULL, updated DATETIME NULL, deleted DATETIME NULL)');
}

function uri_part($index){
	$parts = array_values(array_filter(explode('/', preg_replace('#(\.json|\.html)$#', '', $_SERVER['REQUEST_URI']))));
	if($index < count($parts)){
		return $parts[$index];
	} else {
		trigger_error("Cannot return part #" . $index . ': REQUEST_URI "' . $_SERVER['REQUEST_URI'] . '" only has ' . count($parts) . ' part' . pluralise(count($parts)), E_USER_ERROR);
	}
}

/* STRING FUNCTIONS */

function begins_with($haystack, $needle){
    return (strpos($haystack, $needle) === 0 ? True : False);
}

function contains($haystack, $needle){
    return (strpos($haystack, $needle) ? True : False);
}

function ends_with($haystack, $needle){
    return (strpos($haystack, $needle) === strlen($haystack)-strlen($needle) ? True : False);
}

# Turns an array into a list like: "First, second and third"
function human_list($items){
	$last = array_pop($items);
	return implode(', ', $items) . ' and ' . $last;
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