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
    $resp->add_message('Welcome to MEMEX.');
    $resp->add_message('MEMEX is a system for remembering stuff.');
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
    $options = array(
        'order' => 'COALESCE(updated, added) DESC',
        'limit' => 100,
        'offset' => 0
    );
    if(isset($_GET['order'])){
        $options['order'] = $_GET['order'];
    }
    if(isset($_GET['limit'])){
        $options['limit'] = $_GET['limit'];
    }
    if(isset($_GET['offset'])){
        $options['offset'] = $_GET['offset'];
    }
    $r = get_items($options);
    $resp->set_limit($options['limit']);
    $resp->set_offset($options['offset']);
    if($r['success']){
        if($r['results']){
            $resp->set_count(count($r['results']));
            foreach($r['results'] as $item){
                $resp->add_data($item);
            }
        } else {
            $resp->add_message('No items returned by query');
        }
    } else {
        $resp->add_message("Could not query items database");
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
        $resp->add_message("New item added");
        $resp->set_status(201);
        $resp->add_data(array('id'=>$r['insert_id']));
    } else {
        $resp->add_message("New item could not be added");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 5);
    }
    $resp->send();
}

function getItem(){
    global $resp;
    $r = get_items(array('id'=>uri_part(1)));
    if($r['success']){
        if($r['results']){
            $resp->set_count(count($r['results']));
            foreach($r['results'] as $item){
                $resp->add_data($item);
            }
        } else {
            $resp->add_message('No items returned by query');
        }
    } else {
        $resp->add_message("Could not query items database");
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
        $resp->add_message("Item updated");
        $resp->add_data(array('id'=>$r['update_id']));
    } else {
        $resp->add_message("Could not query items database");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 13);
    }
    $resp->send();
}

function putItem(){
    global $_PUT;
    global $resp;
    $resp->add_message('You requested to create an item the id: ' . uri_part(1));
    $args = array('id' => uri_part(1));
    if(isset($_PUT['title'])){ $args['title'] = $_PUT['title']; }
    if(isset($_PUT['content'])){ $args['content'] = $_PUT['content']; }
    if(isset($_PUT['source'])){ $args['source'] = $_PUT['source']; }
    if(isset($_PUT['url'])){ $args['url'] = $_PUT['url']; }
    $r = add_item($args);
    if($r['success']){
        $resp->add_message("New item added");
        $resp->set_status(201);
        $resp->add_data(array('id'=>$r['insert_id']));
    } else {
        $resp->add_message("New item could not be added");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 5);
    }
    $resp->send();
}

function deleteItem(){
    global $resp;
    $resp->add_message('You requested to the delete the item the id: ' . uri_part(1));
    $r = delete_item(uri_part(1));
    if($r['success']){
        $resp->add_message("Item deleted");
    } else {
        $resp->set_status(404);
        $resp->add_message("Item could not be deleted");
        $resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 6);
    }
    $resp->send();
}

function reminders(){
    global $resp;
    $resp->add_message('reminders endpoint: under construction');
    $resp->send();
}

function reminder(){
    global $resp;
    $resp->add_message('reminder endpoint: under construction');
    $resp->send();
}

function updates(){
    global $resp;
    $resp->add_message('updates endpoint: under construction');
    $resp->send();
}

function settings(){
    global $resp;
    $resp->add_message('settings endpoint: under construction');
    $resp->send();
}

function fourohfour(){
    global $resp;
    $resp->set_status(404);
    $resp->add_message("You requested something we don't have");
    $resp->send();
}

?>