<?php


define("host", "localhost");

define("database", "gordonco_enrollment");
define("user", "gordonco_gcdevelopers");
define("password", "7uQ(5kx&0E;4");
    
// define("database", "gc_enrollment");
// define("user", "root");
// define("password", "");

$conn = new mysqli(host, user, password, database);
$conn->set_charset('utf8');
date_default_timezone_set('Asia/Manila');
?>