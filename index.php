<?php

require_once 'functions.php';
require_once 'endpoints.php';
require_once 'urls.php';

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