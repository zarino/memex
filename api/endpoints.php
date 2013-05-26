<?php

//  This is probably like views or controllers or whatever.
//  One function per API endpoint, with child functions for each HTTP method.
//  These are called by index.php, and found via urls.php

$endpoints = array(
    'homepage' => array(
        'GET' => array(
            'description' => 'show API documentation',
            'handler' => 'getHomepage'
        )
    ),
    'items' => array(
        'GET' => array(
            'description' => 'list or search items',
            'handler' => 'getItems'
        ),
        'POST' => array(
            'description' => 'create a new item',
            'handler' => 'postItems'
        )
    ),
    'item' => array(
        'GET' => array(
            'description' => 'request details for the item with the specified ID',
            'handler' => 'getItem'
        ),
        'POST' => array(
            'description' => 'update the item with the specified ID',
            'handler' => 'postItem'
        ),
        'PUT' => array(
            'description' => 'create an item with the specified ID',
            'handler' => 'putItem'
        ),
        'DELETE' => array(
            'description' => 'delete the item with the specified ID',
            'handler' => 'deleteItem'
        )
    ),
    'reminders' => array(
        '*' => array(
            'description' => 'reminders endpoint: under construction',
            'handler' => 'reminders'
        )
    ),
    'reminder' => array(
        '*' => array(
            'description' => 'reminder endpoint: under construction',
            'handler' => 'reminder'
        )
    ),
    'updates' => array(
        '*' => array(
            'description' => 'updates endpoint: under construction',
            'handler' => 'updates'
        )
    ),
    'settings' => array(
        '*' => array(
            'description' => 'settings endpoint: under construction',
            'handler' => 'settings'
        )
    )
);

function getHomepage(){
    global $resp;
    global $urls;
    global $endpoints;
    $resp->add_response('Welcome to MEMEX.');
    $resp->add_response('MEMEX is a system for remembering stuff.');
    $help = array();
    foreach($urls as $url => $handler){
        foreach($endpoints[$handler] as $method => $info){
            $help[$method . ' ' . $url] = $info['description'];
        }
    }
    $resp->add_data($help);
    $resp->send();
}

function getItems(){
    global $resp;
    if(isset($_GET['order'])){
        $order = $_GET['order'];
    } else {
        $order = 'COALESCE(updated, added) DESC';
    }
    $r = get_items(array('order'=>$order));
    if($r['success']){
        if($r['results']){
            $c = count($r['results']);
            $resp->add_response($c . ' item' . pluralise($c) . ' in database');
            foreach($r['results'] as $item){
                $resp->add_data($item);
            }
        } else {
            $resp->add_response('No items returned by query');
        }
    } else {
        $resp->add_response("Could not query items database");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 13);
    }
    $resp->send();
}

function postItems(){
    global $resp;
    $args = array();
    if(isset($_POST['title'])){ $args['title'] = $_POST['title']; }
    if(isset($_POST['content'])){ $args['content'] = $_POST['content']; }
    if(isset($_POST['source'])){ $args['source'] = $_POST['source']; }
    if(isset($_POST['url'])){ $args['url'] = $_POST['url']; }
    $r = add_item($args);
    if($r['success']){
        $resp->add_response("New item added (item id: " . $r['insert_id'] . ")");
        $resp->set_status(201);
        $resp->add_data(array('id'=>$r['insert_id']));
    } else {
        $resp->add_response("New item could not be added");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 5);
    }
    $resp->send();
}

function getItem(){
    global $resp;
    $r = get_items(array('id'=>uri_part(1)));
    if($r['success']){
        if($r['results']){
            $c = count($r['results']);
            $resp->add_response($c . ' matching item' . pluralise($c));
            foreach($r['results'] as $item){
                $resp->add_data($item);
            }
        } else {
            $resp->add_response('No items returned by query');
        }
    } else {
        $resp->add_response("Could not query items database");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 13);
    }
    $resp->send();
}

function postItem(){
    global $resp;
    $args = array();
    if(isset($_POST['title'])){ $args['title'] = $_POST['title']; }
    if(isset($_POST['content'])){ $args['content'] = $_POST['content']; }
    if(isset($_POST['source'])){ $args['source'] = $_POST['source']; }
    if(isset($_POST['url'])){ $args['url'] = $_POST['url']; }
    $r = update_item(uri_part(1), $args);
    if($r['success']){
        $resp->add_response("Item updated (item id: " . $r['update_id'] . ")");
        $resp->add_data(array('id'=>$r['update_id']));
    } else {
        $resp->add_response("Could not query items database");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 13);
    }
    $resp->send();
}

function putItem(){
    global $_PUT;
    global $resp;
    $resp->add_response('You requested to create an item the id: ' . uri_part(1));
    $args = array('id' => uri_part(1));
    if(isset($_PUT['title'])){ $args['title'] = $_PUT['title']; }
    if(isset($_PUT['content'])){ $args['content'] = $_PUT['content']; }
    if(isset($_PUT['source'])){ $args['source'] = $_PUT['source']; }
    if(isset($_PUT['url'])){ $args['url'] = $_PUT['url']; }
    $r = add_item($args);
    if($r['success']){
        $resp->add_response("New item added (item id: " . $r['insert_id'] . ")");
        $resp->set_status(201);
        $resp->add_data(array('id'=>$r['insert_id']));
    } else {
        $resp->add_response("New item could not be added");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 5);
    }
    $resp->send();
}

function deleteItem(){
    global $resp;
    $resp->add_response('You requested to the delete the item the id: ' . uri_part(1));
    $r = delete_item(uri_part(1));
    if($r['success']){
        $resp->add_response("Item " . uri_part(1) . " deleted");
    } else {
        $resp->set_status(404);
        $resp->add_response("Item " . uri_part(1) . " could not be deleted");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 6);
    }
    $resp->send();
}

function reminders(){
    global $resp;
    $resp->add_response('reminders endpoint: under construction');
    $resp->send();
}

function reminder(){
    global $resp;
    $resp->add_response('reminder endpoint: under construction');
    $resp->send();
}

function updates(){
    global $resp;
    $resp->add_response('updates endpoint: under construction');
    $resp->send();
}

function settings(){
    global $resp;
    $resp->add_response('settings endpoint: under construction');
    $resp->send();
}

function fourohfour(){
    global $resp;
    $resp->set_status(404);
    $resp->add_response("You requested something we don't have");
    $resp->send();
}

?>