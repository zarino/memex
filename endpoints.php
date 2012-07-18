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
		'GET' => 'list or search items',
		'POST' => 'create a new item'
	);

	function GET(){
		if(empty($_SERVER['QUERY_STRING'])){
			print 'You requested a list of all items';
		} else {
			print 'You requested to search the items';
		}
	}
	
	function POST(){
		print 'You requested to create a new item';
		$r = mysqli_query($GLOBALS['dbc'], 'SELECT NOW() as time');
		if (mysqli_error($GLOBALS['dbc'])) {
			pretty_print_r(array('error','info'=>'MySQL error: ' . mysqli_error($GLOBALS['dbc']), 'query'=>$q, 'file'=>__FILE__, 'line'=>__LINE__));
		} else if(mysqli_num_rows($r) == 0) {
			print 'No rows returned';
		} else {
			$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
			print "\n<br/>\n<br/>\nit is now " . $row['time'];
		}
	}
	
	route_to_method($_SERVER['REQUEST_METHOD'], $methods);
}

function item(){
	$methods = array(
		'GET' => 'request details for the item with the specified ID',
		'POST' => 'update the item with the specified ID',
		'PUT' => 'create an item with the specified ID',
		'DELETE' => 'delete the item with the specified ID'
	);

	function GET(){
		print 'You requested details for the item id: ' . uri_part(1);
	}
	
	function POST(){
		print 'You requested to update the item the id: ' . uri_part(1);
	}
	
	function PUT(){
		print 'You requested to create an item the id: ' . uri_part(1);
	}
	
	function DELETE(){
		print 'You requested to the delete the item the id: ' . uri_part(1);
	}
	
	route_to_method($_SERVER['REQUEST_METHOD'], $methods);
}

function reminders(){
	print 'You requested the reminders page';
	pretty_print_r('<br/><br/>', $_SERVER);
}

function reminder(){
	print 'You requested a single reminder page';
	pretty_print_r('<br/><br/>', $_SERVER);
}

function updates(){
	print 'You requested the updates page';
	pretty_print_r('<br/><br/>', $_SERVER);
}

function settings(){
	print 'You requested the settings page';
	pretty_print_r('<br/><br/>', $_SERVER);
}

function fourohfour(){
	header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
	print 'You requested something we don&rsquo;t have &ndash; 404!';
	pretty_print_r('<br/><br/>', $_SERVER);
}

?>