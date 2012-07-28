<?php

//	This is probably like views or controllers or whatever.
//	One function per API endpoint.
//	These are called by index.php, and found via urls.php

function homepage(){
	$resp = new_response_object();
	$resp['responses'][] = 'You requested the homepage';
	send_response($resp);
}

function items(){
	$methods = array(
		'GET' => array(
			'description' => 'list or search items',
			'handler' => function(){
				if(empty($_SERVER['QUERY_STRING'])){
					$resp = new_response_object();
					$resp['responses'][] = "You requested a list of all items";
				} else {
					$resp = new_response_object();
					$resp['responses'][] = "You requested to search the items";
				}
				send_response($resp);
			}
		),
		'POST' => array(
			'description' => 'create a new item',
			'handler' => function(){
				$resp = new_response_object();
				$resp['responses'][] = "You requested to create a new item";
				$q = 'SELECT NOW() as time';
				$r = mysqli_query($GLOBALS['dbc'], $q);
				if (mysqli_error($GLOBALS['dbc'])) {
					$resp['responses'][] = "Oh dear! There was a MySQL error";
					$resp['errors'] = array(array(
						'error'=>'MySQL error: ' . mysqli_error($GLOBALS['dbc']),
						'query'=> $q,
						'file'=> __FILE__,
						'line'=> __LINE__ - 7)
					);
				} else if(mysqli_num_rows($r) == 0) {
					$resp['reponses'][] = "No rows returned";
				} else {
					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
					$resp['data'][] = array('current_time' => $row['time']);
				}
				send_response($resp);
			}
		)
	);
	
	handle($methods);
	#route_to_method($_SERVER['REQUEST_METHOD'], $methods);
}

function item(){
	$methods = array(
		'GET' => array(
			'description' => 'request details for the item with the specified ID',
			'handler' => function(){
				print 'You requested details for the item id: ' . uri_part(1) . "\n";
			}
		),
		'POST' => array(
			'description' => 'update the item with the specified ID',
			'handler' => function(){
				print 'You requested to update the item the id: ' . uri_part(1) . "\n";
			}
		),
		'PUT' => array(
			'description' => 'create an item with the specified ID',
			'handler' => function(){
				print 'You requested to create an item the id: ' . uri_part(1) . "\n";
			}
		),
		'DELETE' => array(
			'description' => 'delete the item with the specified ID',
			'handler' => function(){
				print 'You requested to the delete the item the id: ' . uri_part(1) . "\n";
			}
		)
	);
	handle($methods);
	#route_to_method($_SERVER['REQUEST_METHOD'], $methods);
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
	header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
	print "You requested something we don&rsquo;t have &ndash; 404!\n";
	pretty_print_r('<br/><br/>', $_SERVER);
}

?>