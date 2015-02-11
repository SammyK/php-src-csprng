# [WIP] Easy-and-stable CSPRNG in PHP

A proposal to add an easy user-land API for a reliable CSPRNG in PHP.

## The Problem

PHP particularly sucks at providing CSPRNG's, but there are a few options like `openssl_random_pseudo_bytes()` and `mcrypt_create_iv()`. Unfortunately system support for these functions varies.

The [MCrypt lib](http://mcrypt.sourceforge.net/) is solid, but unmaintained and is built into PHP as an extension that might not be enabled in certain environments (like most versions of PHP on Mac OS X). The longer this lib goes unmaintained, the more likely it is to have a security hole discovered that goes unfixed.

The [OpenSSL lib](https://www.openssl.org/) is being maintained but is hugely bloated and we've seen several major security issues pop up requiring the most-up-to-date version of the lib to stay secure. Moreover, in certain configurations, `openssl_random_pseudo_bytes()` will return bytes that are not cryptographically secure adding more required knowledge in user-land to ensure secure bytes. 

Currently the most reliable way to grab pseudo-random bytes across systems is by using either of the libs mentioned above or falling back to a stream of bytes from `/dev/urandom` which is OS-specific and can fail when the `open_basedir` ini setting is set. This requires user-land apps to write potentially 100's of lines of code to simply generate pseudo-random bytes and there are several caveats that will not generate cryptographically secure bytes. And in some cases no reliable method can be found at all.

See the [Facebook PHP SDK's CSPRNG's](https://github.com/facebook/facebook-php-sdk-v4/tree/master/src/Facebook/PseudoRandomString) for an example implementation.

## Proposed Solution

There should be a user-land API to easily return an arbitrary length of cryptographically secure pseudo-random bytes directly from `/dev/urandom` and work on any supported server configuration or OS.

The initial proposal is to add **three** user-land functions that return the bytes as binary, hexadecimal, and integer.

```php
$randBinary = random_bytes(10);

$randHexadecimal = random_hex(10);

$randomInt = random_int(0, 99);
```

See the [proof-of-concept](https://github.com/SammyK/php-src-csprng/blob/master/csprng.php) written in user-land. This is still a work in progress.

## Personal Learning

I have many questions that I need to research and answer in a comprehensive manner before I'm able to [submit an RFC](https://wiki.php.net/rfc). I will post the answers here so others can learn as well.

- Need to brush up on C.
    - [Intro to C](http://www.cprogramming.com/tutorial/c/lesson1.html)
    - [`fgets()` docs](http://www.tutorialspoint.com/c_standard_library/c_function_fgets.htm)
- Learn to build an extension in PHP.
    - [Intro to extensions](http://devzone.zend.com/303/extension-writing-part-i-introduction-to-php-and-zend/) from Zend
    - [Offical docs on extensions](http://php.net/manual/en/internals2.structure.php) from php.net
    - [Open Grock](http://lxr.php.net/) thanks Daniel R!
    - [PHP namespace](https://github.com/ircmaxell/php-src/compare/function-autoloading-7#diff-057763e7b765a6c5c50714033fd04ff4R1) from Anthony
    - [The `hash_password()` function](https://github.com/php/php-src/pull/191/files) from Anthony
- How to read a file stream in PHP core.
    - Maybe look at `fopen()` session handler or file uploads or php.ini parser?
- Need to learn about how PHP core validates arguments in functions.
- How to grab a stream of bytes from Window's equivalent of `/dev/urandom`?
    - [E. Smith](https://twitter.com/auroraeosrose) will help! Yay!
    - [Ian Littman](https://twitter.com/iansltx) suggested checking out [CryptGenRandom](http://en.wikipedia.org/wiki/CryptGenRandom)
- Will the `open_basedir` ini setting have to be accounted for in core?
- Need to learn more about how binary and hex are converted.
- Need to learn more about how to convert binary to integer.
- Will we need to worry about `mb_string` support in core for this?
- Will seeding `mt_rand()` with CSPRNG make it cryptographically secure?
- Does seeding with `mt_srand()` seed `mt_rand()` globally?
- RFC Process
    - [Example accepted RFC](https://wiki.php.net/rfc/password_hash)
