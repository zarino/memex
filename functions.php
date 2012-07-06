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

function pretty_print_r($array){
	print '<pre>';
	print_r($array);
	print '</pre>';
}

pretty_print_r($_SERVER);
pretty_print_r($_GET);
pretty_print_r($_POST);
pretty_print_r($_PUT);
pretty_print_r($_DELETE);

?>