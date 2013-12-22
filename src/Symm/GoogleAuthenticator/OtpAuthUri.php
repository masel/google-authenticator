<?php

namespace Symm\GoogleAuthenticator;

/**
 * Class OtpAuthUri
 * Spec: https://code.google.com/p/google-authenticator/wiki/KeyUriFormat
 *
 * @package Symm\GoogleAuthenticator
 */
class OtpAuthUri
{

    private $type;
    private $accountName;

    private $secret;
    private $issuer;
    private $algorithm;
    private $digits;

    private $counter;
    private $period;

    public function __construct($accountName, $secret)
    {
        $this->setAlgorithm('SHA1');
        $this->setType('totp');
        $this->setAccountName($accountName);
        $this->setSecret($secret);
        $this->digits = 6;
    }

    public function setAccountName($accountName)
    {
        if (strpos($accountName, ':') !== false) {
            throw new \Exception('Account name must not contain the : character');
        }
        $this->accountName= $accountName;

        return $this;
    }

    public function setSecret($secret)
    {
        if (!$this->isValidSecret($secret)) {
            throw new \Exception('Secret key must be base32 encoded');
        }

        $this->secret = $secret;

        return $this;
    }

    public function setAlgorithm($algorithm)
    {
        $allowedTypes = array(
            'SHA1',
            'SHA256',
            'SHA512',
            'MD5'
        );

        $algorithm = strtoupper($algorithm);

        if (!in_array($algorithm, $allowedTypes)) {
            throw new \Exception(
                'Invalid algorithm type: [' . $algorithm . '] must be one of: [' . implode(', ', $allowedTypes). ']'
            );
        }

        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * The digits parameter may have the values 6 or 8, and determines how
     * long of a one-time passcode to display to the user. The default is 6.
     *
     * @param $digits
     * @return $this
     * @throws \Exception
     */
    public function setDigits($digits)
    {
        if ($digits != 6 && $digits != 8) {
            throw new \Exception('Digits must be either 6 or 8');
        }

        $this->digits = $digits;

        return $this;
    }

    /**
     * Distinguish whether the key will be used for counter-based HOTP or for TOTP.
     *
     * @param $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        $type = strtolower($type);

        if ($type != 'totp' && $type != 'hotp') {
            throw new \InvalidArgumentException('Type must be either totp or hotp');
        }
        $this->type = $type;

        return $this;
    }

    /**
     * The issuer parameter is a string value indicating the provider or service this account is associated with
     *
     * @param $issuer
     * @return $this
     * @throws \Exception
     */
    public function setIssuer($issuer)
    {
        if (strpos($issuer, ':') !== false) {
            throw new \Exception('Issuer must not contain the : character');
        }

        $this->issuer = $issuer;

        return $this;
    }

    public function getUri()
    {
        $queryParams = array();
        $queryParams['secret'] = $this->secret;
        if ($this->issuer) {
            $queryParams['issuer'] = $this->issuer;
        }
        if ($this->algorithm) {
            $queryParams['algorithm'] = $this->algorithm;
        }

        return 'otpauth://' . $this->type . '/' . $this->generateLabel(). '?' . http_build_query($queryParams);
    }

    public function __toString()
    {
        return $this->getUri();
    }

    /**
     * The label is used to identify which account a key is associated with.
     * It contains an account name, which is a URI-encoded string, optionally
     * prefixed by an issuer string identifying the provider or service managing
     * that account.
     *
     * @return string
     */
    private function generateLabel()
    {
        $label = $this->accountName;
        if ($this->issuer) {
            $label = $this->issuer . ':' . $label;
        }

        return urlencode($label);
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
