<?php

/**
 * Testing the TokenGenerator component used for cryptographic secure token generation.
 */

namespace CryptoManana\Tests\TestSuite\Utilities;

use CryptoManana\Tests\TestTypes\AbstractUnitTest;
use CryptoManana\Randomness\PseudoRandom;
use CryptoManana\Utilities\TokenGenerator;
use CryptoManana\DataStructures\KeyPair;

/**
 * Class TokenGeneratorTest - Tests the cryptographic secure token generator class.
 *
 * @package CryptoManana\Tests\TestSuite\Utilities
 */
final class TokenGeneratorTest extends AbstractUnitTest
{
    /**
     * Creates new instances for testing.
     *
     * @return TokenGenerator Testing instance.
     * @throws \Exception Wrong usage errors.
     */
    private function getTokenGeneratorForTesting()
    {
        $generator = $this->getMockBuilder(PseudoRandom::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();

        $generator->expects($this->atLeast(0))
            ->method('getInt')
            ->willReturn(1, 2, 3, 4);

        $generator->expects($this->atLeast(0))
            ->method('getAlphaNumeric')
            ->willReturn('A');

        $generator->expects($this->atLeast(0))
            ->method('getHex')
            ->willReturn('a');

        $generator->expects($this->atLeast(0))
            ->method('getAscii')
            ->willReturn('b');

        $generator->expects($this->atLeast(0))
            ->method('getBytes')
            ->willReturn("\0");

        return new TokenGenerator($generator);
    }

    /**
     * Testing the cloning of an instance.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testCloningCapabilities()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $tmp = clone $generator;

        $this->assertEquals($generator, $tmp);
        $this->assertNotEmpty($tmp->getTokenString(10));

        unset($tmp);
        $this->assertNotNull($generator);
    }

    /**
     * Testing secure password generation.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testPasswordStringGeneration()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $resultOne = $generator->getPasswordString(20);
        $resultTwo = $generator->getPasswordString(20);

        $this->assertEquals($resultOne, $resultTwo);

        $resultOne = $generator->getPasswordString(20, false);
        $resultTwo = $generator->getPasswordString(20, false);

        $this->assertEquals($resultOne, $resultTwo);
    }

    /**
     * Testing secure token generation.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testTokenStringGeneration()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $resultOne = $generator->getTokenString(64);
        $resultTwo = $generator->getTokenString(64);

        $this->assertEquals($resultOne, $resultTwo);

        $resultOne = $generator->getTokenString(64, false);
        $resultTwo = $generator->getTokenString(64, false);

        $this->assertEquals($resultOne, $resultTwo);
    }

    /**
     * Testing secure hashing salt generation.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testHashingSaltGeneration()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $resultOne = $generator->getHashingSalt(64);
        $resultTwo = $generator->getHashingSalt(64);

        $this->assertEquals($resultOne, $resultTwo);

        $resultOne = $generator->getHashingSalt(64, false);
        $resultTwo = $generator->getHashingSalt(64, false);

        $this->assertEquals($resultOne, $resultTwo);
    }

    /**
     * Testing secure hashing key generation.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testHashingKeyGeneration()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $resultOne = $generator->getHashingKey(64);
        $resultTwo = $generator->getHashingKey(64);

        $this->assertEquals($resultOne, $resultTwo);

        $resultOne = $generator->getHashingKey(64, false);
        $resultTwo = $generator->getHashingKey(64, false);

        $this->assertEquals($resultOne, $resultTwo);
    }

    /**
     * Testing secure encryption key generation.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testEncryptionKeyGeneration()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $resultOne = $generator->getEncryptionKey(64);
        $resultTwo = $generator->getEncryptionKey(64);

        $this->assertEquals($resultOne, $resultTwo);

        $resultOne = $generator->getEncryptionKey(64, false);
        $resultTwo = $generator->getEncryptionKey(64, false);

        $this->assertEquals($resultOne, $resultTwo);
    }

    /**
     * Testing secure encryption initialization vector generation.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testInitializationVectorGeneration()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $resultOne = $generator->getEncryptionInitializationVector(64);
        $resultTwo = $generator->getEncryptionInitializationVector(64);

        $this->assertEquals($resultOne, $resultTwo);

        $resultOne = $generator->getEncryptionInitializationVector(64, false);
        $resultTwo = $generator->getEncryptionInitializationVector(64, false);

        $this->assertEquals($resultOne, $resultTwo);
    }

    /**
     * Testing secure asymmetric key pair generation.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testAsymmetricKeyPairGeneration()
    {
        $generator = $this->getTokenGeneratorForTesting();

        $keyPair = $generator->getAsymmetricKeyPair(
            $generator::KEY_PAIR_1024_BITS,
            $generator::RSA_KEY_PAIR_TYPE
        );

        $this->assertTrue(
            is_object($keyPair) &&
            $keyPair instanceof KeyPair &&
            isset($keyPair->private) &&
            isset($keyPair->public)
        );

        $privateKey = $keyPair->private;
        $publicKey = $keyPair->public;

        $this->assertTrue(
            preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $privateKey) && strlen($privateKey) % 4 === 0
        );
        $this->assertTrue(
            preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $publicKey) && strlen($publicKey) % 4 === 0
        );
    }

    /**
     * Testing validation case for non-positive output length.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testValidationCaseForNonPositiveOutputLength()
    {
        $generator = $this->getTokenGeneratorForTesting();

        // Backward compatible for different versions of PHPUnit
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LengthException::class);

            $generator->getTokenString(0);
        } else {
            $hasThrown = null;

            try {
                $generator->getTokenString(0);
            } catch (\LengthException $exception) {
                $hasThrown = !empty($exception->getMessage());
            } catch (\Exception $exception) {
                $hasThrown = $exception->getMessage();
            }

            $this->assertTrue($hasThrown);

            return;
        }
    }

    /**
     * Testing validation case invalid asymmetric key pair type.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testValidationCaseForInvalidAsymmetricKeyPairAlgorithmType()
    {
        $generator = $this->getTokenGeneratorForTesting();

        // Backward compatible for different versions of PHPUnit
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);

            $generator->getAsymmetricKeyPair(512, ['wrong']);
        } else {
            $hasThrown = null;

            try {
                $generator->getAsymmetricKeyPair(512, ['wrong']);
            } catch (\InvalidArgumentException $exception) {
                $hasThrown = !empty($exception->getMessage());
            } catch (\Exception $exception) {
                $hasThrown = $exception->getMessage();
            }

            $this->assertTrue($hasThrown);

            return;
        }
    }


    /**
     * Testing validation case invalid asymmetric key pair size.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testValidationCaseForInvalidAsymmetricKeyPairSize()
    {
        $generator = $this->getTokenGeneratorForTesting();

        // Backward compatible for different versions of PHPUnit
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);

            $generator->getAsymmetricKeyPair(['wrong']);
        } else {
            $hasThrown = null;

            try {
                $generator->getAsymmetricKeyPair(['wrong']);
            } catch (\InvalidArgumentException $exception) {
                $hasThrown = !empty($exception->getMessage());
            } catch (\Exception $exception) {
                $hasThrown = $exception->getMessage();
            }

            $this->assertTrue($hasThrown);

            return;
        }
    }

    /**
     * Testing validation case for too big asymmetric key pair size.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testValidationCaseForTooBigAsymmetricKeyPairSize()
    {
        $generator = $this->getTokenGeneratorForTesting();

        // Backward compatible for different versions of PHPUnit
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);

            $generator->getAsymmetricKeyPair(30000);
        } else {
            $hasThrown = null;

            try {
                $generator->getAsymmetricKeyPair(30000);
            } catch (\InvalidArgumentException $exception) {
                $hasThrown = !empty($exception->getMessage());
            } catch (\Exception $exception) {
                $hasThrown = $exception->getMessage();
            }

            $this->assertTrue($hasThrown);

            return;
        }
    }

    /**
     * Testing validation case for too small asymmetric key pair size.
     *
     * @throws \Exception Wrong usage errors.
     */
    public function testValidationCaseForTooSmallAsymmetricKeyPairSize()
    {
        $generator = $this->getTokenGeneratorForTesting();

        // Backward compatible for different versions of PHPUnit
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);

            $generator->getAsymmetricKeyPair(128);
        } else {
            $hasThrown = null;

            try {
                $generator->getAsymmetricKeyPair(128);
            } catch (\InvalidArgumentException $exception) {
                $hasThrown = !empty($exception->getMessage());
            } catch (\Exception $exception) {
                $hasThrown = $exception->getMessage();
            }

            $this->assertTrue($hasThrown);

            return;
        }
    }
}
