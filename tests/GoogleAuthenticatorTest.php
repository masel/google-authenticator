<?php

class GoogleAuthenticatorTest extends PHPUnit_Framework_TestCase {

    /* @var $googleAuthenticator PHPGangsta_GoogleAuthenticator */
    protected $googleAuthenticator;
    protected $secret;

    protected function setUp()
    {
        $this->secret = "SECRET";
        $this->googleAuthenticator = new PHPGangsta_GoogleAuthenticator($this->secret);
    }

    public function codeProvider()
    {
        // Secret, time, code
        return array(
            array(0, '200470'),
            array(1385909245, '780018'),
            array(1378934578, '705013'),
        );
    }

    public function testItCanBeInstantiated()
    {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $this->assertInstanceOf('PHPGangsta_GoogleAuthenticator', $ga);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreatedWithInvalidSecret()
    {
        $ga = new PHPGangsta_GoogleAuthenticator('^%^&%^34543');
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
        $this->googleAuthenticator->getCode('twentyseven');
    }

    /**
     * @dataProvider codeProvider
     */
    public function testgetCodeReturnsCorrectValues($timeSlice, $code)
    {
        $generatedCode = $this->googleAuthenticator->getCode($timeSlice);

        $this->assertEquals($code, $generatedCode);
    }

    public function testgetQRCodeGoogleUrlReturnsCorrectUrl()
    {
        $name   = 'Test';
        $url = $this->googleAuthenticator->getQRCodeGoogleUrl($name);

        $urlParts = parse_url($url);
        parse_str($urlParts['query'], $queryStringArray);

        $this->assertEquals($urlParts['scheme'], 'https');
        $this->assertEquals($urlParts['host'], 'chart.googleapis.com');
        $this->assertEquals($urlParts['path'], '/chart');

        $expectedChl = 'otpauth://totp/' . $name . '?secret=' . $this->googleAuthenticator->getSecret();
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
        $code = $this->googleAuthenticator->getCode();
        $result = $this->googleAuthenticator->verifyCode($code);
        $this->assertEquals(true, $result);

        $code = 'INVALIDCODE';
        $result = $this->googleAuthenticator->verifyCode($code);
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