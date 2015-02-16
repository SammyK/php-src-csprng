<?php
require "csprng.php";
/**
 * A test of the actual implementation (before the real tests)
 */

var_dump(random_bytes(32));
var_dump(bin2hex(random_bytes(16)));

var_dump(random_int());
var_dump(random_int(10));
/*
var_dump(random_int(500));
var_dump(random_int(10));
var_dump(random_int(3));
var_dump(random_int(1));
var_dump(random_int(0));
var_dump(random_hex(15));

var_dump(random_int(1, 99));

$int = PHP_INT_MAX;
$int2 = PHP_INT_MAX + 1;
var_dump($int);
var_dump($int2);
*/
// Just cause I like to see random output in the console...
//var_dump(hash_hmac('sha256', 'Hash browns!', random_bytes(256)));

$fub = array_fill(0, 100, 0);
$buf = array_fill(0, 100, 0);
for ($i = 0; $i < 20000; ++$i) {
    $j = random_int(0, 99);
    ++$buf[$j];
}
var_dump($buf);
