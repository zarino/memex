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
    $GLOBALS['_ALL'] = array_merge($GLOBALS['_GET'], $GLOBALS['_POST'], $GLOBALS['_PUT'], $GLOBALS['_DELETE']);
}

function handle($methods){
    global $resp;
    if(in_array($_SERVER['REQUEST_METHOD'], array_keys($methods))){
        $resp->add_header('Allow', implode(', ', array_keys($methods)) . ', OPTIONS');
        if(is_authenticated($methods[$_SERVER['REQUEST_METHOD']])){
            $methods[$_SERVER['REQUEST_METHOD']]['handler']();
        } else {
            $resp->set_status(403);
            $resp->add_message('You must supply an apikey to ' . ($_SERVER['REQUEST_METHOD'] == 'GET' ? 'read from' : 'write to' ) . ' this endpoint');
            $resp->send();
        }
    } else if(in_array('*', array_keys($methods))){
        $methods['*']['handler']();
    } else {
        if($_SERVER['REQUEST_METHOD'] != 'OPTIONS'){
            $resp->set_status(405);
            $resp->add_message($_SERVER['REQUEST_METHOD'] . ' method not allowed');
        }
        $resp->add_header('Allow', implode(', ', array_keys($methods)) . ', OPTIONS');
        $resp->add_message('This endpoint accepts ' . implode(', ', array_keys($methods)) . ', and OPTIONS requests');
        $options = array();
        foreach($methods as $method => $properties){
            $options[$method] = $properties['description'];
        }
        $options['OPTIONS'] = 'show the HTTP methods this endpoint accepts';
        $resp->add_data($options);
        $resp->send();
    }
}

function is_authenticated($endpoint_settings){
    global $resp;

    # is this a public endpoint?
    if(isset($endpoint_settings['public']) && $endpoint_settings['public']){
        return True;
    }

    # have they supplied a write apikey?
    if(!defined('APIKEY_WRITE') || APIKEY_WRITE == '' || APIKEY_WRITE == Null){
        # apikey has not been set in config-secret.php
        return True;
    } else if(isset($GLOBALS['_ALL']['apikey']) && $GLOBALS['_ALL']['apikey'] == APIKEY_WRITE){
        # apikey has been supplied as a query parameter
        return True;
    }

    # is this is a GET endpoint, and have they supplied a read-only apikey?
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        if(!defined('APIKEY_READ') || APIKEY_READ == '' || APIKEY_READ == Null){
            # apikey has not been set in config-secret.php
            return True;
        } else if(isset($GLOBALS['_ALL']['apikey']) && $GLOBALS['_ALL']['apikey'] == APIKEY_READ){
            # apikey has been supplied as a query parameter
            return True;
        }
    }

    return False;
}

class Response {

    public $response;
    
    public $sent = False;

    public function __construct() {
        $this->response = array();
        $this->status_code = 200;
        $this->headers = array('Content-Type' => 'application/json');
        $this->cookie = Null;
    }

    public function add_data($data){
        if(!isset($this->response['data'])){
            $this->response['data'] = array();
        }
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

    public function add_header($key, $value){
        $this->headers[$key] = $value;
    }

    public function add_message($message){
        if(!isset($this->response['message'])){
            $this->response['message'] = array();
        }
        $this->response['message'][] = $message;
    }

    public function set_cookie($name, $value){
        $this->cookie = array(
            'name' => $name,
            'value' => $value,
            'expires' => time() + 3600,
        );
    }

    public function remove_cookie($name){
        $this->cookie = array(
            'name' => $name,
            'value' => False,
            'expires' => time() - 3600,
        );
    }

    public function set_status($status_code){
        global $HTTP_CODES;
        if(in_array($status_code, array_keys($HTTP_CODES))){
            $this->status_code = $status_code;
        } else {
            throw new Exception($status_code . ' is not a valid HTTP status code');
        }
    }

    public function set_count($count){
        if(is_null($count)){
            unset($this->response['count']);
        } else {
            $this->response['count'] = (int) $count;
        }
    }

    public function set_limit($limit){
        if(is_null($limit)){
            unset($this->response['limit']);
        } else {
            $this->response['limit'] = (int) $limit;
        }
    }

    public function set_offset($offset){
        if(is_null($offset)){
            unset($this->response['offset']);
        } else {
            $this->response['offset'] = (int) $offset;
        }
    }

    public function set_order($order){
        if(is_null($order)){
            unset($this->response['order']);
        } else {
            $this->response['order'] = $order;
        }
    }

    public function send(){
        global $HTTP_CODES;
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $this->status_code . ' ' . $HTTP_CODES[$this->status_code]);
        foreach($this->headers as $key=>$value){
            header($key . ': ' . $value);
        }
        if(isset($this->cookie)){
            setcookie($this->cookie['name'], $this->cookie['value'], $this->cookie['expires'], '/api/');
        }
        if($this->status_code < 400){
            $this->response['status'] = 'ok';
        } else {
            $this->response['status'] = 'error';
        }
        $this->response['code'] = $this->status_code;
        $this->sent = True;
        print pretty_json(json_encode($this->response)) . "\n";
    }
}

/* DATABASE FUNCTIONS */

function add_item($args){
    // $args is an array of item attributes, like 'title', 'content', 'source' and 'url'
    $defaults = array('title'=>Null, 'content'=>Null, 'source'=>Null, 'url'=>Null);
    $a_safe = array_map('db_safe', array_merge($defaults, $args));
    $a_safe['added'] = db_safe(date('Y-m-d H:i:s'));
    $q = "INSERT INTO `items` (" . implode(',', array_keys($a_safe)) . ") VALUES (" . implode(',', $a_safe) . ")";
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
    mysqli_query($GLOBALS['dbc'], "SET time_zone='" + get_mysql_timezone_offset() + "';");
}

function db_safe($v, $s="'"){
    if(is_null($v)){
        return 'NULL';
    } else if(is_string($v) || is_int($v) || is_float($v) || is_numeric($v)) {
        return $s . mysqli_real_escape_string($GLOBALS['dbc'], (string) $v) . $s;
    } else {
        throw new Exception("db_safe() can't handle arguments of type " . gettype($v));
    }
}

function delete_item($id){
    $q = "UPDATE `items` SET `deleted`=" . db_safe(date('Y-m-d H:i:s')) . " WHERE id=" . db_safe($id) . " LIMIT 1";
    $r = mysqli_query($GLOBALS['dbc'], $q);
    if (mysqli_error($GLOBALS['dbc']) || mysqli_affected_rows($GLOBALS['dbc']) == 0) {
        return array('success'=>False, 'query'=>$q, 'error'=>mysqli_error($GLOBALS['dbc']));
    } else {
        return array('success'=>True, 'query'=>$q, 'error'=>Null);
    }
}

function get_items($args){
    // $args is an array of arguments, such as 'id', 'limit', 'order' and 'search'
    $q = "SELECT * FROM `items` WHERE `deleted` IS NULL";
    if(isset($args['id'])){ $q = $q . " AND id=" . db_safe($args['id']); }
    if(isset($args['order'])){ $q = $q . " ORDER BY " . db_safe($args['order'], ''); }
    if(isset($args['limit'])){
        $q = $q . " LIMIT " . db_safe($args['limit'], '');
        if(isset($args['offset'])){
            $q = $q . " OFFSET " . db_safe($args['offset'], '');
        }
    }
    $r = mysqli_query($GLOBALS['dbc'], $q);
    if (mysqli_error($GLOBALS['dbc'])) {
        return array('success'=>False, 'query'=>$q, 'error'=>mysqli_error($GLOBALS['dbc']), 'insert_id'=>Null);
    } else if(mysqli_num_rows($r) == 0) {
        return array('success'=>True, 'query'=>$q, 'error'=>Null, 'results'=>array());
    } else {
        $results = array();
        while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
            $results[] = $row;
        }
        return array('success'=>True, 'query'=>$q, 'error'=>Null, 'results'=>$results);
    }
}

function get_mysql_timezone_offset(){
    $tz = new DateTimeZone("Europe/London");
    $dt = new DateTime("now", $tz);
    $mins = $dt->getOffset() / 60;
    $sgn = ($mins < 0 ? -1 : 1);
    $mins = abs($mins);
    $hrs = floor($mins / 60);
    $mins -= $hrs * 60;
    return sprintf('%+d:%02d', $hrs*$sgn, $mins);
}

function setup_database(){
    mysqli_query($GLOBALS['dbc'], 'CREATE TABLE IF NOT EXISTS items (id INT PRIMARY KEY AUTO_INCREMENT, title VARCHAR(255) NULL, content TEXT NULL, source VARCHAR(32) NULL, url VARCHAR(255) NULL, added DATETIME NULL, viewed DATETIME NULL, updated DATETIME NULL, deleted DATETIME NULL)');
    mysqli_query($GLOBALS['dbc'], 'CREATE TABLE IF NOT EXISTS reminders (id INT PRIMARY KEY AUTO_INCREMENT, item_id INT NULL, reminder_datetime DATETIME NULL, medium VARCHAR(32) NULL, destination VARCHAR(255) NULL, content TEXT NULL, source VARCHAR(32) NULL, added DATETIME NULL, reminded DATETIME NULL, updated DATETIME NULL, deleted DATETIME NULL)');
}

function update_item($id, $args){
    // $args is an array of item attributes, like 'title', 'content', 'source' and 'url'
    $a_safe = array_map('db_safe', $args);
    $a_safe['updated'] = db_safe(date('Y-m-d H:i:s'));
    $update_args = array();
    foreach($a_safe as $k=>$v){
        $update_args[] = $k . '=' . $v;
    }
    $q = "UPDATE `items` SET " . implode(',', $update_args) . " WHERE id=" . db_safe($id);
    $r = mysqli_query($GLOBALS['dbc'], $q);
    if (mysqli_error($GLOBALS['dbc']) || mysqli_affected_rows($GLOBALS['dbc']) == 0) {
        return array('success'=>False, 'query'=>$q, 'error'=>mysqli_error($GLOBALS['dbc']), 'update_id'=>Null);
    } else {
        return array('success'=>True, 'query'=>$q, 'error'=>Null, 'update_id'=>$id);
    }
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
    $parts = array_values(array_filter(explode('/', preg_replace('#\.[a-zA-Z]$#', '', $_SERVER['REQUEST_URI']))));
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