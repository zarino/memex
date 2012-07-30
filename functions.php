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

function handle($methods){
	global $resp;
	if(in_array($_SERVER['REQUEST_METHOD'], array_keys($methods))){
		$resp->add_header('Allow: ' . implode(', ', array_keys($methods)) . ', OPTIONS');
		$methods[$_SERVER['REQUEST_METHOD']]['handler']();
	} else {
		if($_SERVER['REQUEST_METHOD'] != 'OPTIONS'){
			$resp->add_header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed");
		}
		$resp->add_header('Allow: ' . implode(', ', array_keys($methods)) . ', OPTIONS');
		$resp->set_options($methods);
		$resp->send();
	}
}

class Response {

	public $response;

	public function __construct() {
		$this->response = array('request' => array(
			'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
			'HTTP_ACCEPT' => $_SERVER['HTTP_ACCEPT'],
			'REQUEST_URI' => $_SERVER['REQUEST_URI'],
			'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
			'REQUEST_TIME' => $_SERVER['REQUEST_TIME'],
			'RESPONSE_FORMAT' => $_SERVER['RESPONSE_FORMAT']
		), 'responses' => array(), 'data' => array());
		$this->headers = array();
	}

	public function add_data($data){
		$this->response['data'][] = $data;
	}
	
	public function add_error($type, $error, $input, $file, $line){
		if(!isset($this->response['errors'])){
			$this->response['errors'] = array();
		}
		$this->response['errors'][] = array(
			'type' => $type,
			'error' => $error,
			'query' => $input,
			'file' => $file,
			'line' => $line
		);
	}

	public function add_header($header){
		$this->headers[] = $header;
	}

	public function add_response($message){
		$this->response['responses'][] = $message;
	}

	public function set_options($methods){
		$a = array();
		foreach($methods as $method => $properties){
			$a[$method] = $properties['description'];
		}
		$a['OPTIONS'] = 'show the HTTP methods this endpoint accepts';
		$this->response['options'] = $a;
		$this->add_response('This endpoint accepts ' . implode(', ', array_keys($methods)) . ', and OPTIONS requests');
	}

	public function send(){
		if($_SERVER['RESPONSE_FORMAT']=='json'){
			$this->add_header('Content-Type: application/json');
			foreach($this->headers as $h){ header($h); }
			print pretty_json(json_encode($this->response)) . "\n";
		} else if($_SERVER['RESPONSE_FORMAT']=='html'){
			$this->add_header('Content-Type: text/html');
			foreach($this->headers as $h){ header($h); }
			pretty_print_r($this->response, "\n");
		} else {
			$this->add_header('Content-Type: text/html');
			foreach($this->headers as $h){ header($h); }
			throw new Exception('Could not print response object to requested format: ' . $_SERVER['RESPONSE_FORMAT']);
		}
	}
}

/* DATABASE FUNCTIONS */

function add_item($title=Null, $content=Null, $source=Null, $url=Null){
	$q = "INSERT INTO `items` (title, content, source, url, added) VALUES (" . db_safe($title) . "," . db_safe($content) . "," . db_safe($source) . "," . db_safe($url) . ",NOW())";
	$r = mysqli_query($GLOBALS['dbc'], $q);
	if (mysqli_error($GLOBALS['dbc']) || mysqli_affected_rows($GLOBALS['dbc']) == 0) {
		return array('success'=>False, 'query'=>$q, 'error'=>mysqli_error($GLOBALS['dbc']), 'insert_id'=>Null);
	} else {
		return array('success'=>True, 'query'=>$q, 'error'=>Null, 'insert_id'=>mysqli_insert_id($GLOBALS['dbc']));
	}
}

function connect_to_database(){
	$GLOBALS['dbc'] = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error() );
	mysqli_set_charset($GLOBALS['dbc'], 'utf8');
}

function db_safe($v, $s="'"){
	if(is_null($v)){
		return 'NULL';
	} else if(is_string($v) || is_int($v) || is_float($v) || is_numeric($v)) {
		return $s . mysqli_real_escape_string($GLOBALS['dbc'], $v) . $s;
	} else {
		throw new Exception("db_safe() can't handle arguments of type " . gettype($v));
	}
}

function setup_database(){
	mysqli_query($GLOBALS['dbc'], 'CREATE TABLE IF NOT EXISTS items (id INT PRIMARY KEY AUTO_INCREMENT, title VARCHAR(255) NULL, content TEXT NULL, source VARCHAR(32) NULL, url VARCHAR(255) NULL, added DATETIME NULL, viewed DATETIME NULL, updated DATETIME NULL, deleted DATETIME NULL)');
	mysqli_query($GLOBALS['dbc'], 'CREATE TABLE IF NOT EXISTS reminders (id INT PRIMARY KEY AUTO_INCREMENT, item_id INT NULL, reminder_datetime DATETIME NULL, medium VARCHAR(32) NULL, destination VARCHAR(255) NULL, content TEXT NULL, source VARCHAR(32) NULL, added DATETIME NULL, reminded DATETIME NULL, updated DATETIME NULL, deleted DATETIME NULL)');
}

/* UTILITY FUNCTIONS */

# Turns an alphanumeric hash into an integer (eg: '2n9c' -> 123456)
function hash_to_integer($string, $base = HASH_CHARS){
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
function integer_to_hash($integer, $base = HASH_CHARS){
    $length = strlen($base);
    $out = '';
    while($integer > $length - 1){
        $out = $base[fmod($integer, $length)] . $out;
        $integer = floor( $integer / $length );
    }
    return $base[$integer] . $out;
}

function uri_part($index){
	$parts = array_values(array_filter(explode('/', preg_replace('#(\.json|\.html)$#', '', $_SERVER['REQUEST_URI']))));
	if($index < count($parts)){
		return $parts[$index];
	} else {
		throw new Exception("Cannot return part #" . $index . ': REQUEST_URI "' . $_SERVER['REQUEST_URI'] . '" only has ' . count($parts) . ' part' . pluralise(count($parts)));
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
function human_list($items, $separator='and'){
	$last = array_pop($items);
	return implode(', ', $items) . ' $separator ' . $last;
}

function pluralise($number, $plural_suffix='s', $singular_suffix=''){
	if(is_int($number) || is_float($number)){
		if($number == 1){
			return $singular_suffix;
		} else {
			return $plural_suffix;
		}
	} else {
		throw new Exception("pluralise() was passed a non-integer, non-float argument");
	}
}

# Returns a pretty json string with whitespace and linebreaks
# http://snipplr.com/view/60559/prettyjson/
function pretty_json($json) {
    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = '  ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;
    for ($i=0; $i<=$strLen; $i++) {
        $char = substr($json, $i, 1);
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        $result .= $char;
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        $prevChar = $char;
    }
    return $result;
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
	    throw new Exception("Incorrect parameters supplied to pretty_print_r() - function takes an optional string prefix, a required array, and an optional string suffix");
	}
}

?>