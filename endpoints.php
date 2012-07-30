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
				if(empty($_SERVER['QUERY_STRING'])){
					$resp->add_response("You requested a list of all items");
				} else {
					$resp->add_response("You requested to search the items");
				}
				$resp->send();
			}
		),
		'POST' => array(
			'description' => 'create a new item',
			'handler' => function(){
				global $resp;
				$title = (isset($_POST['title']) ? $_POST['title'] : Null);
				$content = (isset($_POST['content']) ? $_POST['content'] : Null);
				$source = (isset($_POST['source']) ? $_POST['source'] : Null);
				$url = (isset($_POST['url']) ? $_POST['url'] : Null);
				$r = add_item($title, $content, $source, $url);
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
				$resp->add_response('You requested details for the item id: ' . uri_part(1));
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