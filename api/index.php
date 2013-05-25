<?php

require 'config.php';
require 'functions.php';
require 'endpoints.php';
require 'urls.php';

handle_put_delete();
connect_to_database();
# setup_database();

$resp = new Response();

foreach($urls as $regexp => $handler){
    if(preg_match('#^/api' . $regexp . '(\?.+)?$#', $_SERVER['REQUEST_URI'])){
        $handler();
        break;
    }
}

?>