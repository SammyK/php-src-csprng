<?php

/**
 * Easy user-land API to retrieve an arbitrary length of
 * cryptographically secure pseudo-random bytes.
 */

/**
 * Returns pseudo-random binary string of arbitrary length.
 *
 * @param int $bytesLength The number of bytes to return.
 *
 * @return string
 *
 * @throws Exception
 */
function random_bytes($bytesLength)
{
    if ( ! is_int($bytesLength)) {
        throw new InvalidArgumentException('random_bytes() expects byte length as an integer.');
    }

    if ($bytesLength < 1) {
        throw new InvalidArgumentException('random_bytes() expects byte length greater than 0.');
    }

    // @todo Validate a max $bytesLength value here?

    // @todo Not sure if this is an issue only in user-land
    if (ini_get('open_basedir')) {
        throw new Exception('There is an open_basedir constraint that prevents access to /dev/urandom.');
    }

    // @todo Windowz???
    if ( ! is_readable('/dev/urandom')) {
        throw new Exception('Unable to read from /dev/urandom.');
    }

    $stream = fopen('/dev/urandom', 'rb');
    if ( ! is_resource($stream)) {
        throw new Exception('Unable to open stream to /dev/urandom.');
    }

    $binaryString = fread($stream, $bytesLength);
    fclose($stream);

    if( ! $binaryString) {
        throw new Exception('Stream to /dev/urandom returned no data.');
    }

    return $binaryString;
}

/**
 * Returns pseudo-random hexadecimal string of arbitrary length.
 *
 * @param int $stringLength The length of the string.
 *
 * @return string
 */
function random_hex($stringLength)
{
    // @todo Validate $stringLength here

    // @todo Double check bin2hex() string length calculations
    $length = (int) round($stringLength / 2);
    $bytes = random_bytes($length);

    $asHex = bin2hex($bytes);

    // @todo Not sure of mb_string support across systems
    return mb_substr($asHex, 0, $stringLength);
}

/**
 * Returns pseudo-random int between two values.
 *
 * @param int $min
 * @param int $max
 *
 * @return int
 *
 * @throws Exception
 */
function random_int($min, $max)
{
    // @todo Validate $min & $max here

    $bytes = random_bytes(256);
    // @todo Convert bytes to int somehow?
    // @todo Will seeding mt_rand with pr-bytes from urand make it cryptographically secure?
    // @todo Does seeding happen globally?
    mt_srand();

    return mt_rand($min, $max);
}

var_dump(random_bytes(10));
var_dump(random_bytes(15));

var_dump(random_hex(10));
var_dump(random_hex(15));

var_dump(random_int(0, 99));

// Just cause I like to see random output in the console...
var_dump(hash_hmac('sha256', 'Hash browns!', random_bytes(256)));