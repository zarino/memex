<?php

require 'config.php';
require 'functions.php';
require 'endpoints.php';
require 'urls.php';

handle_put_delete();
connect_to_database();
# setup_database();

$resp = new Response();

foreach($urls as $url){
    if(preg_match('#'.$url[0].'#', $_SERVER['REQUEST_URI'])){
        $url[1]();
        break;
    }
}

?>