<?php

define('HASH_CHARS', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
# define('HASH_CHARS', '0123456789abcdefghijklmnopqrstuvwxyz');

# define('DB_USER', 'override-in-config-secret.php');
# define('DB_PASSWORD', 'override-in-config-secret.php');
# define('DB_HOST', 'override-in-config-secret.php');
# define('DB_NAME', 'override-in-config-secret.php');

if(file_exists('config-secret.php')){
	ob_start();
	include 'config-secret.php';
	ob_end_clean();
}

?>