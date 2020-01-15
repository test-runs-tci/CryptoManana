<?php

/**
 * The PBKDF2-MD5 hashing algorithm class.
 */

namespace CryptoManana\Hashing;

use \CryptoManana\Core\Abstractions\MessageDigestion\AbstractIterativeSlowDerivation as SlowDerivationAlgorithm;

/**
 * Class Pbkdf2Md5 - The PBKDF2-MD5 hashing algorithm object.
 *
 * @package CryptoManana\Hashing
 */
class Pbkdf2Md5 extends SlowDerivationAlgorithm
{
    /**
     * The internal name of the algorithm.
     */
    const ALGORITHM_NAME = 'md5';

    /**
     * The internal maximum length in bytes of the output digest for the algorithm.
     *
     * @internal For the current algorithm: `PHP_INT_MAX`
     */
    const ALGORITHM_MAXIMUM_OUTPUT = PHP_INT_MAX;

    /**
     * The derivation output digest size in bytes length property storage.
     *
     * @var int The derivation output digest size in bytes length value.
     *
     * @internal The default output size in bytes for this algorithm.
     */
    protected $outputLength = 16;
}
