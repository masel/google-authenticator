<?php

namespace Symm\GoogleAuthenticator\QrCodeGenerator;

use Symm\GoogleAuthenticator\OtpAuthUri;

interface QRCodeGenerator
{
    public function __construct();

    /**
     * Returns the QR Code image as a file
     *
     * @param OtpAuthUri $authenticationData
     * @return \SplFileObject
     */
    public function getImageFile(OtpAuthUri $authenticationData);

    /**
     * Returns a URL suitable for inserting into a img tag src attribute
     *
     * @param OtpAuthUri $authenticationData
     * @return mixed
     */
    public function getImageUrl(OtpAuthUri $authenticationData);

    /**
     * Sets the width and height of the QR code image
     *
     * @param $size
     * @return mixed
     */
    public function setSize($size);
}
