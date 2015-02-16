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
 * @throws Exception, InvalidArgumentException
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
    
    /**
     * If we can safely use mcrypt, let's use mcrypt
     */
    if (function_exists('mcrypt_create_iv') && version_compare(PHP_VERSION, '5.3.7') >= 0) {
        // If mcrypt_create_iv is available, it handles Windows well
        $binaryString = mcrypt_create_iv($bytesLength, MCRYPT_DEV_URANDOM);
        if ($binaryString !== false) {
            return $binaryString;
        }
    }
    
    if (function_exists('openssl_random_pseudo_bytes') && version_compare(PHP_VERSION, '5.3.7') >= 0) {
        $strong = false;
        
        // The $strong argument is passed by reference; we aren't telling
        // openssl_random_pseudo_bytes not to give us strong random, we're telling
        // it to put a boolean for whether or not a strong random source was available
        // into the $strong variable for later validation.
        $binaryString = openssl_random_pseudo_bytes($bytesLength, $strong);
        if ($strong) {
            return $binaryString;
        }
    }

    // @todo Not sure if this is an issue only in user-land
    if (ini_get('open_basedir')) {
        throw new Exception('There is an open_basedir constraint that prevents access to /dev/urandom.');
    }
    if ( ! is_readable('/dev/urandom')) {
        throw new Exception('Unable to read from /dev/urandom.');
    }

    $stream = fopen('/dev/urandom', 'rb');
    stream_set_read_buffer($stream, 0);
    if ( ! is_resource($stream)) {
        throw new Exception('Unable to open stream to /dev/urandom.');
    }
    
    // Disable buffering
    stream_set_chunk_size($stream, 0);
    
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
    if ($stringLength < 1) {
        throw new InvalidArgumentException('random_bytes() expects byte length greater than 0.');
    }

    // @todo Double check bin2hex() string length calculations
    $randomLength = ceil($stringLength / 2);
    $bytes = random_bytes($randomLength);

    $asHex = bin2hex($bytes);

    // mb_substr is not needed on ASCII strings
    return substr($asHex, 0, $stringLength);
}

/**
 * Returns pseudo-random int between two values.
 *
 * @param int $min
 * @param int $max
 *
 * @return int
 *
 * @throws Exception, InvalidArgumentException
 */
function random_int($min, $max)
{
    $range = $max - $min;
    if ($range < 1) {
        throw new InvalidArgumentException('random_int() expects two different integers.');
    }

    // 7776 -> 13
    $bits = ceil(log($range)/log(2));

    // 2^13 - 1 == 8191 or 0x00001111 11111111
    $mask =  ceil(pow(2, $bits)) - 1;
    do {
        // Grab a random integer
        $val = random_positive_int();
        if ($val === FALSE) {
            // RNG failure
            throw new Exception("Random Number Generator failure!");
        }
        // Apply mask
        $val &= $mask;

        // If $val is larger than the maximum acceptable number for
        // $min and $max, we discard and try again.

    } while ($val > $range);
    return (int) ($min + $val);
}


/**
 * Returns pseudo-random int between 0 and PHP_INT_MAX
 *
 * @return int
 */
function random_positive_int()
{
    $buf = random_bytes(PHP_INT_SIZE);
    $val = 0;
    $i = PHP_INT_SIZE;

    do {
        $i--;
        $val <<= 8;
        $val |= ord($buf[$i]);
    } while ($i != 0);

    return $val & PHP_INT_MAX;
}
