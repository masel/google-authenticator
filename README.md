Google Authenticator
=====================

[![Build Status](https://api.travis-ci.org/symm/google-authenticator.png)](https://travis-ci.org/symm/google-authenticator)

This PHP class can be used to interact with the Google Authenticator mobile app for 2-factor-authentication. This class
can generate secrets, generate codes, validate codes and present a QR-Code for scanning the secret.

For a secret installation you have to make sure that used codes cannot be reused (replay-attack).

Usage:
------

A fully working page demo is available in ./example/index.php

    <?php
    require_once('vendor/autoload.php');

    use Symm\GoogleAuthenticator\GoogleAuthenticator;

    $googleAuthenticator = new GoogleAuthenticator('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ');
    $isValid = $googleAuthenticator->verifyCode($_POST['code']);
    if (!$isValid) {
        // Authentication failed
    } else {
        // Proceed with login
    }


License
-------

* Licensed under the BSD License.
* This project is a fork of PHPGangsta_GoogleAuthenticator by Michael Kliewe, [@PHPGangsta](http://twitter.com/PHPGangsta)
