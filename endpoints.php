<?php

//	This is probably like views or controllers or whatever.
//	One function per API endpoint.
//	These are called by index.php, and found via urls.php

function homepage(){
	print 'You requested the homepage';
	pretty_print_r($_SERVER);
}

function items(){
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		if(empty($_SERVER['QUERY_STRING'])){
			print 'You requested a list of all items';
		} else {
			print 'You requested to search the items';
		}
	} else if($_SERVER['REQUEST_METHOD'] == 'POST'){
		print 'You requested to create a new item';
	} else {
		if($_SERVER['REQUEST_METHOD'] != 'OPTIONS'){
			header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed");
		}
		header("Allow: GET, POST");
		print 'This endpoint only accepts GET and POST requests. Use GET to list or search items; use POST to create a new item.';
	}
	pretty_print_r('<br/><br/>', $_SERVER);
}

function item(){
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		print 'You requested details for a single item';
	} else if($_SERVER['REQUEST_METHOD'] == 'POST'){
		print 'You requested to update the item with the specified ID';
	} else if($_SERVER['REQUEST_METHOD'] == 'PUT'){
		print 'You requested to create an item with the specified ID';
	} else if($_SERVER['REQUEST_METHOD'] == 'DELETE'){
		print 'You requested to the delete the item with the specified ID';
	} else {
		if($_SERVER['REQUEST_METHOD'] != 'OPTIONS'){
			header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed");
		}
		header("Allow: GET, POST, PUT, DELETE");
		print 'This endpoint only accepts GET, POST, PUT and DELETE requests. Use GET to request details for the item with the specified ID; use POST to update the item with the specified ID; use PUT to create a new item with the specified ID; use DELETE to delete the item with the specified ID.';
	}
	pretty_print_r('<br/><br/>', $_SERVER);
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