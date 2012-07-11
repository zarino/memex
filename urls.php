<?php

//	If you've used Django, this will look familiar.
//	We define mappings between REQUEST_URIs and functions from endpoints.php
//	'<pairing_name>' => array('<uri_regexp>','<endpoint_function>')
	
//	Hash symbols '#' in the <uri_regexp> must be escaped.

$urls = array(
	'homepage' => array('^/$','homepage'),
	'items' => array('^/items(/(\?.+)|\.html|\.json)?$','items'),
	'item' => array('^/items/[a-zA-Z0-9]+(/|\.html|\.json)?$','item'),
	'reminders' => array('^/reminders(/(\?.+)|\.html|\.json)?$','reminders'),
	'reminder' => array('^/reminder/[a-zA-Z0-9]+(/|\.html|\.json)?$','reminder'),
	'fourohfour' => array('.','fourohfour')
);

?>