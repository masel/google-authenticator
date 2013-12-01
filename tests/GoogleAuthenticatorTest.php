<?php

class GoogleAuthenticatorTest extends PHPUnit_Framework_TestCase {

    /* @var $googleAuthenticator PHPGangsta_GoogleAuthenticator */
    protected $googleAuthenticator;

    protected function setUp()
    {
        $this->googleAuthenticator = new PHPGangsta_GoogleAuthenticator();
    }

    public function codeProvider()
    {
        // Secret, time, code
        return array(
            array('SECRET', '0', '200470'),
            array('SECRET', '1385909245', '780018'),
            array('SECRET', '1378934578', '705013'),
        );
    }

    public function testItCanBeInstantiated()
    {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $this->assertInstanceOf('PHPGangsta_GoogleAuthenticator', $ga);
    }

    public function testCreateSecretDefaultsToSixteenCharacters()
    {
        $ga = $this->googleAuthenticator;
        $secret = $ga->createSecret();
        $this->assertEquals(strlen($secret), 16);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateSecretLengthCannotBeZero()
    {
        $this->googleAuthenticator->createSecret(0);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateSecretLengthMustBeInteger()
    {
        $this->googleAuthenticator->createSecret('three');
    }

    public function testCreateSecretLengthCanBeSpecified()
    {
        $ga = $this->googleAuthenticator;
        for ($secretLength = 1; $secretLength < 100; $secretLength++) {
            $secret = $ga->createSecret($secretLength);
            $this->assertEquals(strlen($secret), $secretLength);
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetCodeThrowsExceptionIfTimesliceNotNumeric()
    {
        $this->googleAuthenticator->getCode('SECRET', 'twentyseven');
    }

    /**
     * @dataProvider codeProvider
     */
    public function testgetCodeReturnsCorrectValues($secret, $timeSlice, $code)
    {
        $generatedCode = $this->googleAuthenticator->getCode($secret, $timeSlice);

        $this->assertEquals($code, $generatedCode);
    }

    public function testgetQRCodeGoogleUrlReturnsCorrectUrl()
    {
        $secret = 'SECRET';
        $name   = 'Test';
        $url = $this->googleAuthenticator->getQRCodeGoogleUrl($name, $secret);

        $urlParts = parse_url($url);
        parse_str($urlParts['query'], $queryStringArray);

        $this->assertEquals($urlParts['scheme'], 'https');
        $this->assertEquals($urlParts['host'], 'chart.googleapis.com');
        $this->assertEquals($urlParts['path'], '/chart');

        $expectedChl = 'otpauth://totp/' . $name . '?secret=' . $secret;
        $this->assertEquals($queryStringArray['chl'], $expectedChl);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testVerifyCodeThrowsExceptionIfDiscrepancyNotNumeric()
    {
        $this->googleAuthenticator->verifyCode('SECRET', 'CODE', 'twentyseven');
    }

    public function testVerifyCode()
    {
        $secret = 'SECRET';
        $code = $this->googleAuthenticator->getCode($secret);
        $result = $this->googleAuthenticator->verifyCode($secret, $code);
        $this->assertEquals(true, $result);

        $code = 'INVALIDCODE';
        $result = $this->googleAuthenticator->verifyCode($secret, $code);
        $this->assertEquals(false, $result);

    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetCodeLengthIsNumeric()
    {
        $this->googleAuthenticator->setCodeLength('two');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetCodeLengthBounds()
    {
        $this->googleAuthenticator->setCodeLength(1);
    }

    public function testsetCodeLength()
    {
        $result = $this->googleAuthenticator->setCodeLength(6);
        $this->assertInstanceOf('PHPGangsta_GoogleAuthenticator', $result);
    }
} 