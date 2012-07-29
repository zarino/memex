<?php

require 'config.php';
require 'functions.php';
require 'endpoints.php';
require 'urls.php';

handle_put_delete();
parse_request();
connect_to_database();
# setup_database();

$resp = new Response();

foreach($urls as $u){
	if(preg_match('#'.$u[0].'#', $_SERVER['REQUEST_URI'])){
		$u[1]();
		break;
	}
}

// pretty_print_r($_SERVER);
// pretty_print_r($_GET);
// pretty_print_r($_POST);
// pretty_print_r($_PUT);
// pretty_print_r($_DELETE);

?>