<?php

//	If you've used Django, this will look familiar.
//	We define mappings between REQUEST_URIs and functions from endpoints.php
//	array('<uri_regexp>','<endpoint_function>')
	
//	Hash symbols '#' in the <uri_regexp> must be escaped.

$urls = array(
	array('^/$','homepage'),
	array('^/items(/|\.html|\.json)?(\?.+)?$','items'),
	array('^/items/[a-zA-Z0-9]+(/|\.html|\.json)?(\?.+)?$','item'),
	array('^/reminders(/|\.html|\.json)?(\?.+)?$','reminders'),
	array('^/reminder/[a-zA-Z0-9]+(/|\.html|\.json)?(\?.+)?$','reminder'),
	array('.','fourohfour')
);

?>