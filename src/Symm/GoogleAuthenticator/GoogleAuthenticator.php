<?php

/**
 * PHP Class for handling Google Authenticator 2-factor authentication
 *
 * @author Michael Kliewe
 * @copyright 2012 Michael Kliewe
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.phpgangsta.de/
 */

namespace Symm\GoogleAuthenticator;

use Base32\Base32;

class GoogleAuthenticator
{

    protected $codeLength = 6;
    private $secret;
    private $tolerance;

    public function __construct($secret = null)
    {
        $this->tolerance = 1;

        if (!$secret) {
            $this->secret = $this->createSecret(16);

        } else {
            if (!$this->isValidSecret($secret)) {
                throw new \InvalidArgumentException($secret . ' is not a valid base32 encoded secret');
            }

            $this->secret = $secret;
        }
    }

    /**
     * Set the clock drift tolerance in minutes for accepting codes.
     * Codes generated x minutes either side of the current code will be accepted
     *
     * @param $minutes
     * @return int
     */
    public function setTolerance($minutes)
    {
        $this->tolerance = $minutes;

        return $this->tolerance;
    }

    /**
     * Returns the clock drift tolerance in minutes for accepting codes.
     *
     * @return int
     */
    public function getTolerance()
    {
        return $this->tolerance;
    }

    private function isValidSecret($secret)
    {
        $validCharacters = $this->getBase32LookupTable();
        $secret = str_replace($validCharacters, '', $secret);

        if ($secret  === '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create new secret.
     * Randomly chosen from the allowed base32 characters.
     *
     * @param int $secretLength Defaults to 16 characters
     * @return string
     * @throws \InvalidArgumentException
     */
    public function createSecret($secretLength = 16)
    {
        if (!is_numeric($secretLength)) {
            throw new \InvalidArgumentException('Secret must be numeric');
        }
        if ($secretLength < 1) {
            throw new \InvalidArgumentException('Secret must be at least one character long');
        }

        $validChars = $this->getBase32LookupTable();
        unset($validChars[32]);

        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[array_rand($validChars)];
        }

        return $secret;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Calculate the code, with given secret and point in time
     *
     * @param int|null $timeSlice
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getCode($timeSlice = null)
    {
        if ($timeSlice !== null && !is_numeric($timeSlice)) {
            throw new \InvalidArgumentException('Time slice must be numeric');
        }

        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->base32Decode($this->secret);

        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->codeLength);

        return str_pad($value % $modulo, $this->codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get QR-Code URL for image, from google charts
     *
     * @param string $name
     * @return string
     */
    public function getQRCodeGoogleUrl($name)
    {
        $urlencoded = urlencode('otpauth://totp/'.$name.'?secret='.$this->secret.'');

        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl='.$urlencoded.'';
    }

    /**
     * Check if the code is correct.
     *
     * @param string $code
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function verifyCode($code)
    {
        // Allowed time drift in 30 second units. 4 minutes === 8 units
        // Tolerance is stored in seconds. Double to get the number of 30 second units
        $discrepancy = $this->tolerance * 2;

        $currentTimeSlice = floor(time() / 30);

        $validCodes = array();
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($currentTimeSlice + $i);
            $validCodes[$currentTimeSlice + $i] = $calculatedCode;
        }

        if (in_array($code, $validCodes)) {
            return true;
        }

        return false;
    }

    /**
     * Set the code length, should be >=6
     *
     * @param int $length
     * @return PHPGangsta_GoogleAuthenticator
     * @throws \InvalidArgumentException
     */
    public function setCodeLength($length)
    {
        if (!is_numeric($length)) {
            throw new \InvalidArgumentException('Length must be numeric');
        }
        if ($length < 6) {
            throw new \InvalidArgumentException('Length must be greater than or equal to 6');
        }
        $this->codeLength = $length;

        return $this;
    }

    /**
     * Helper class to decode base32
     *
     * @param $secret
     * @return bool|string
     */
    protected function base32Decode($secret)
    {
        return Base32::decode($secret);
    }

    /**
     * Helper class to encode base32
     *
     * @param string $secret
     * @param bool $padding
     * @return string
     */
    protected function base32Encode($secret, $padding = true)
    {
        return Base32::encode($secret);
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32
     *
     * @return array
     */
    protected function getBase32LookupTable()
    {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        );
    }
}
