<?php

/**
 * Low-level CLI check of the PHP requirements and the CryptoManana `compatibility.php` script logic.
 */

// If web request, add MIME type
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
}

// Set debug configuration for check script
ini_set('display_startup_errors', '1');
ini_set('display_errors', '1');
ini_set('report_memleaks', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);
set_time_limit(120);
clearstatcache();

// Require the default framework comparability script
require 'src' . DIRECTORY_SEPARATOR . 'compatibility.php';

// Define the script constants

/**
 * Default row length for delimiters.
 */
define('DEFAULT_LENGTH', (PHP_SAPI === 'cli') ? 45 : 42);

/**
 * Default new line for script output.
 */
define('NEW_LINE', (PHP_SAPI === 'cli') ? PHP_EOL : '<br/>');

/**
 * Is the current system a X64 architecture
 */
define('IS_X64', (PHP_INT_SIZE === 8));

// Declare output functions

/**
 * The dump script output function.
 *
 * @param string $data The data for outputting.
 * @param string $colour The colour for outputting.
 */
function dump($data = PHP_EOL, $colour = 'green')
{
    // Dump newline case
    if ($data === PHP_EOL) {
        echo NEW_LINE;

        return;
    }

    if (!is_string($data)) {
        $data = var_export($data, true);
    }

    // Is this a CLI call?
    if (PHP_SAPI === 'cli') {
        switch ($colour) {
            case 'red':
                $colour = "1;31m";
                break;
            case 'green':
                $colour = "1;32m";
                break;
            case 'purple':
                $colour = "1;35m";
                break;
            default:
                $colour = "1;30m"; // black
                break;
        }

        echo PHP_EOL . "\033[" . $colour . $data . "\033[0m" . PHP_EOL . PHP_EOL;
    } else {
        switch ($colour) {
            case 'red':
                $colour = "red";
                break;
            case 'green':
                $colour = "green";
                break;
            case 'purple':
                $colour = "purple";
                break;
            default:
                $colour = "black"; // black
                break;
        }

        echo "<p style='color: $colour;'>" . $data . "</p>" . NEW_LINE;
    }
}

/**
 * The check script output delimiter.
 *
 * @see dump() Script outputting function.
 *
 * @param int $length The length of the delimiter.
 * @param string $char The character used for building.
 * @param string $colour The colour for outputting.
 */
function dump_a_delimiter($length = DEFAULT_LENGTH, $char = '*', $colour = 'blue')
{
    dump(str_repeat($char, $length), $colour);
}

/**
 * The check script error exit function.
 *
 * @see dump_a_delimiter() Ending delimiter.
 */
function dump_an_error()
{
    // If web request, add HTML
    if (PHP_SAPI !== 'cli') {
        echo '</body></html>';
    }

    dump_a_delimiter();

    exit(1);
}

// If web request, add HTML
if (PHP_SAPI !== 'cli') {
    echo '<html lang="en"><head><title>Compatibility Check</title><meta charset="UTF-8"></head><body>';
}

// Start the check
dump_a_delimiter();
dump('Starting compatibility check process ...', 'black');
dump_a_delimiter();

// PHP Version Check (5.5-7.4)
if (PHP_VERSION_ID >= 50500 && PHP_VERSION_ID < 70500) {
    $versionMessage = IS_X64 ? ' (x64)' : ' (x86)';
    $versionMessage = 'You are using a SUPPORTED PHP version: ' . NEW_LINE . NEW_LINE . PHP_VERSION . $versionMessage;

    dump($versionMessage);

    dump_a_delimiter();
} else {
    dump('You are using an UNSUPPORTED PHP version: ' . NEW_LINE . NEW_LINE . PHP_VERSION, 'red');

    dump_an_error();
}

// PHP required extensions check
$extensionsList = ['spl', 'hash', 'openssl'];

foreach ($extensionsList as $extensionName) {
    if (!extension_loaded($extensionName)) {
        dump('You are missing the following required extension: ' . $extensionName, 'red');

        dump_an_error();
    }
}

unset($extensionName);

// All requirements are accounted for
$versionMessage = 'You have all required extensions: ' . NEW_LINE . NEW_LINE;
$versionMessage .= '- ' . implode(';' . NEW_LINE . '- ', $extensionsList);
$versionMessage .= '.';

dump($versionMessage);

dump_a_delimiter();

// PHP suggested extensions check
dump('Suggestions List:', 'purple');

$none = true;

if (!IS_X64) {
    dump('- Migrate to a x64 version of PHP?', 'purple');

    $none = false;
}

if ((int)ini_get('precision') < 10) {
    dump('- Update your php.ini `precision` to >= 10?', 'purple');

    $none = false;
}

if (ini_get('default_charset') !== 'UTF-8') {
    dump('- Use UTF-8 as default charset for PHP?', 'purple');

    $none = false;
}

if (!extension_loaded('mbstring')) {
    dump('- Add or enable the "mbstring" extension?', 'purple');

    $none = false;
} elseif (mb_internal_encoding() !== 'UTF-8') {
    dump('- Use UTF-8 as internal encoding for "mbstring"?', 'purple');

    $none = false;
} elseif (mb_regex_encoding() !== 'UTF-8') {
    dump('- Use UTF-8 as regex encoding for "mbstring"?', 'purple');

    $none = false;
}

if (!extension_loaded('reflection')) {
    dump('- Add or enable the "reflection" extension?', 'purple');

    $none = false;
}

if (PHP_VERSION_ID < 70200 && !extension_loaded('libsodium')) {
    dump('- Add or enable the "libsodium" extension?', 'purple');

    $none = false;
} elseif (PHP_VERSION_ID >= 70200 && !extension_loaded('sodium')) {
    dump('- Add or enable the "sodium" extension?', 'purple');

    $none = false;
}

if (OPENSSL_VERSION_NUMBER < 268439887 /* >= OpenSSL 1.0.1t 3 May 2016 */) {
    // OpenSSL version, PHP 5.5.9 => 268439663, PHP 5.5.38 => 268439887
    dump('- Update your OpenSSL library and PHP extension?', 'purple');

    $none = false;
}

if (PHP_VERSION_ID >= 70000) {
    if (extension_loaded('mcrypt')) {
        dump('- Migrate from "mcrypt" to "openssl" or "sodium"!', 'purple');

        $none = false;
    }
}

if (PHP_VERSION_ID < 70200) {
    dump('- Migrate to PHP >= 7.2.0 to use the Camellia in CTR mode!', 'purple');

    $none = false;
} elseif (OPENSSL_VERSION_NUMBER < 269484191 /* >= OpenSSL 1.1.0i 14 Aug 2018 */) {
    // OpenSSL version, PHP 7.2.10 => 269484191
    dump('- Update OpenSSL to use Camellia with CTR mode?', 'purple');

    $none = false;
}

if (!defined('PASSWORD_ARGON2I')) {
    dump('- Migrate to PHP >= 7.2.0 to use the Argon2i digest!', 'purple');

    $none = false;
} elseif (!defined('PASSWORD_ARGON2ID')) {
    dump('- Migrate to PHP >= 7.3.0 to use the Argon2id digest!', 'purple');

    $none = false;
}

if ($none) {
    dump('No suggestions were found!', 'purple');
}

dump_a_delimiter();

// PHP constants check
$constantsList = [
    'PHP_INT_MIN',
    'PHP_INT_MAX',
    'PASSWORD_BCRYPT',
    'PASSWORD_DEFAULT',
    'OPENSSL_RAW_DATA',
    'OPENSSL_ZERO_PADDING',
];

if (PHP_VERSION_ID >= 70200) {
    $constantsList = array_merge(
        $constantsList,
        [
            'PASSWORD_ARGON2_DEFAULT_MEMORY_COST',
            'PASSWORD_ARGON2_DEFAULT_TIME_COST',
            'PASSWORD_ARGON2_DEFAULT_THREADS',
        ]
    );
}

foreach ($constantsList as $constantName) {
    if (!defined($constantName)) {
        dump('You must define the PHP global constant: "' . $constantName . '"', 'red');

        dump_an_error();
    }
}

unset($constantName);

// PHP functions check
$functionsList = [
    'mt_rand',
    'mt_srand',
    'mt_getrandmax',
    'random_bytes',
    'random_int',
    'bin2hex',
    'hex2bin',
    'hash',
    'hash_file',
    'hash_hmac',
    'hash_hmac_file',
    'hash_algos',
    'hash_hmac_algos',
    'hash_hkdf',
    'hash_pbkdf2',
    'hash_equals',
    'password_hash',
    'password_verify',
    'password_needs_rehash',
    'openssl_random_pseudo_bytes',
    'openssl_get_cipher_methods',
    'openssl_encrypt',
    'openssl_decrypt',
];

foreach ($functionsList as $functionName) {
    if (!function_exists($functionName)) {
        dump('Please provide an implementation for the PHP core function: ' . $functionName, 'red');

        dump_an_error();
    }
}
unset($functionName);

// PHP hashing algorithms check
$hashingAlgorithms = ['whirlpool', 'md5', 'sha1', 'sha224', 'sha256', 'sha384', 'sha512'];

if (PHP_VERSION_ID >= 70100) {
    $hashingAlgorithms = array_merge($hashingAlgorithms, ['sha3-224', 'sha3-256', 'sha3-384', 'sha3-512']);
}

$checkAgainstDigestAlgorithms = [hash_algos(), hash_hmac_algos()];

foreach ($checkAgainstDigestAlgorithms as $supportedAlgorithms) {
    foreach ($hashingAlgorithms as $algorithmName) {
        if (!in_array($algorithmName, $supportedAlgorithms, true)) {
            dump('Please provide an implementation for the digest algorithm: ' . $algorithmName, 'red');

            dump_an_error();
        }
    }
}

unset($supportedAlgorithms, $algorithmName);

// PHP encryption algorithms check
$supportedAlgorithmsList = openssl_get_cipher_methods(true);

$encryptionAlgorithms = [
    'AES-128-CBC',
    'AES-128-CFB',
    'AES-128-OFB',
    'AES-128-CTR',
    'AES-128-ECB',
    'AES-192-CBC',
    'AES-192-CFB',
    'AES-192-OFB',
    'AES-192-CTR',
    'AES-192-ECB',
    'AES-256-CBC',
    'AES-256-CFB',
    'AES-256-OFB',
    'AES-256-CTR',
    'AES-256-ECB',
    'CAMELLIA-128-CBC',
    'CAMELLIA-128-CFB',
    'CAMELLIA-128-OFB',
    'CAMELLIA-128-ECB',
    'CAMELLIA-192-CBC',
    'CAMELLIA-192-CFB',
    'CAMELLIA-192-OFB',
    'CAMELLIA-192-ECB',
    'CAMELLIA-256-CBC',
    'CAMELLIA-256-CFB',
    'CAMELLIA-256-OFB',
    'CAMELLIA-256-ECB',
];

if (PHP_VERSION_ID >= 70200 && OPENSSL_VERSION_NUMBER > 269484191) {
    $encryptionAlgorithms = array_merge(
        $encryptionAlgorithms,
        [
            'CAMELLIA-128-CTR',
            'CAMELLIA-192-CTR',
            'CAMELLIA-256-CTR',
        ]
    );
}

foreach ($encryptionAlgorithms as $algorithmName) {
    if (!in_array(strtolower($algorithmName), $supportedAlgorithmsList, true)) {
        dump('Please provide an implementation for the encryption algorithm: ' . $algorithmName, 'red');

        dump_an_error();
    }
}

unset($algorithmName);

// Output success!
dump('PHP is ready for the CryptoManana Framework!');

dump_a_delimiter();

// If web request, add HTML
if (PHP_SAPI !== 'cli') {
    echo '</body></html>';
} else {
    exit(0); // Exit with success
}