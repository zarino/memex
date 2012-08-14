<?php

//	This is probably like views or controllers or whatever.
//	One function per API endpoint.
//	These are called by index.php, and found via urls.php

function homepage(){
	global $resp;
	$resp->add_response('You requested the homepage');
	$resp->send();
}

function items(){
	$methods = array(
		'GET' => array(
			'description' => 'list or search items',
			'handler' => function(){
				global $resp;
				if(isset($_GET['order'])){
					$order = $_GET['order'];
				} else {
					$order = 'updated DESC, added DESC';
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
		),
		'POST' => array(
			'description' => 'create a new item',
			'handler' => function(){
				global $resp;
				$args = array();
				if(isset($_POST['title'])){ $args['title'] = $_POST['title']; }
				if(isset($_POST['content'])){ $args['content'] = $_POST['content']; }
				if(isset($_POST['source'])){ $args['source'] = $_POST['source']; }
				if(isset($_POST['url'])){ $args['url'] = $_POST['url']; }
				$r = add_item($args);
				if($r['success']){
					$resp->add_response("New item added (item id: " . $r['insert_id'] . ")");
					$resp->add_data(array('id'=>$r['insert_id']));
				} else {
					$resp->add_response("New item could not be added");
					$resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 5);
				}
				$resp->send();
			}
		)
	);
	handle($methods);
}

function item(){
	$methods = array(
		'GET' => array(
			'description' => 'request details for the item with the specified ID',
			'handler' => function(){
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
		),
		'POST' => array(
			'description' => 'update the item with the specified ID',
			'handler' => function(){
				global $resp;
				$resp->add_response('You requested to update the item the id: ' . uri_part(1));
				$resp->send();
			}
		),
		'PUT' => array(
			'description' => 'create an item with the specified ID',
			'handler' => function(){
				global $resp;
				$resp->add_response('You requested to create an item the id: ' . uri_part(1));
				$resp->send();
			}
		),
		'DELETE' => array(
			'description' => 'delete the item with the specified ID',
			'handler' => function(){
				global $resp;
				$resp->add_response('You requested to the delete the item the id: ' . uri_part(1));
				$r = delete_item(uri_part(1));
				if($r['success']){
					$resp->add_response("Item " . uri_part(1) . " deleted");
				} else {
					$resp->add_header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
					$resp->add_response("Item " . uri_part(1) . " could not be deleted");
					$resp->add_error('MySQL error', $r['error'], $r['query'], __FILE__, __LINE__ - 6);
				}
				$resp->send();
			}
		)
	);
	handle($methods);
}

function reminders(){
	print "You requested the reminders page\n";
	pretty_print_r('<br/><br/>', $_SERVER);
}

function reminder(){
	print "You requested a single reminder page\n";
	pretty_print_r('<br/><br/>', $_SERVER);
}

function updates(){
	print "You requested the updates page\n";
	pretty_print_r('<br/><br/>', $_SERVER);
}

function settings(){
	print "You requested the settings page\n";
	pretty_print_r('<br/><br/>', $_SERVER);
}

function fourohfour(){
	global $resp;
	$resp->add_header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
	$resp->add_response("You requested something we don't have");
	$resp->send();
}

?>