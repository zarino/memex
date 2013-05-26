<?php

require 'config.php';
require 'functions.php';
require 'endpoints.php';
require 'urls.php';

handle_put_delete();
connect_to_database();
# setup_database();

$resp = new Response();

# find the right endpoint
foreach($urls as $regexp => $handler){
    if(preg_match('#^/api' . $regexp . '(\?.+)?$#', $_SERVER['REQUEST_URI'])){
        handle($endpoints[$handler]);
        break;
    }
}

# couldn't find the right endpoint!
if(!$resp->sent){
    fourohfour();
}

?>