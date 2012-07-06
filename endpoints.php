<?php

//	This is probably like views or controllers or whatever.
//	One function per API endpoint.
//	These are called by index.php, and found via urls.php

function homepage(){
	print 'You requested the homepage<br/><br/>';
	pretty_print_r($_SERVER);
}

function items(){
	print 'You requested the items page<br/><br/>';
	pretty_print_r($_SERVER);
}

function fourohfour(){
	print 'You requested something we don&rsquo;t have &ndash; 404!<br/><br/>';
	pretty_print_r($_SERVER);
}

?>