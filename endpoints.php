<?php

//	This is probably like views or controllers or whatever.
//	One function per API endpoint.
//	These are called by index.php, and found via urls.php

function homepage(){
	print 'You requested the homepage';
	pretty_print_r($_SERVER);
}

function items(){
	$methods = array(
		'GET' => array(
			'description' => 'list or search items',
			'handler' => function(){
				if(empty($_SERVER['QUERY_STRING'])){
					print "You requested a list of all items\n";
				} else {
					print "You requested to search the items\n";
				}
			}
		),
		'POST' => array(
			'description' => 'create a new item',
			'handler' => function(){
				print "You requested to create a new item\n";
				$r = mysqli_query($GLOBALS['dbc'], 'SELECT NOW() as time');
				if (mysqli_error($GLOBALS['dbc'])) {
					pretty_print_r(array('error','info'=>'MySQL error: ' . mysqli_error($GLOBALS['dbc']), 'query'=>$q, 'file'=>__FILE__, 'line'=>__LINE__));
				} else if(mysqli_num_rows($r) == 0) {
					print "No rows returned\n";
				} else {
					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
					print "It is now " . $row['time'] . "\n";
				}
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