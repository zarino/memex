<?php

//  If you've used Django, this will look familiar.
//  We define mappings between REQUEST_URIs and functions from endpoints.php

$urls = array(
    '/?' => 'homepage',
    '/items/?' => 'items',
    '/items/[a-zA-Z0-9]+/?' => 'item',
    '/reminders/?' => 'reminders',
    '/reminder/[a-zA-Z0-9]+/?' => 'reminder'
);

?>