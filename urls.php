<?php

//	If you've used Django, this will look familiar.
//	We define mappings between REQUEST_URIs and functions from endpoints.php
//	'<pairing_name>' => array('<uri_regexp>','<endpoint_function>')
	
//	Hash symbols '#' in the <uri_regexp> must be escaped.

$urls = array(
	'homepage' => array('^/$','homepage'),
	'items' => array('^/items/?$','items'),
	'fourohfour' => array('.','fourohfour')
);

?>