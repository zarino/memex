<?php

//  If you've used Django, this will look familiar.
//  We define mappings between REQUEST_URIs and functions from endpoints.php
//  array('<uri_regexp>','<endpoint_function>')

//  Hash symbols '#' in the <uri_regexp> must be escaped.

$urls = array(
    array('^/api/$','homepage'),
    array('^/api/items/?(\?.+)?$','items'),
    array('^/api/items/[a-zA-Z0-9]+/?(\?.+)?$','item'),
    array('^/api/reminders/?(\?.+)?$','reminders'),
    array('^/api/reminder/[a-zA-Z0-9]+/?(\?.+)?$','reminder'),
    array('.','fourohfour')
);

?>