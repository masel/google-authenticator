<?php

namespace Symm\GoogleAuthenticator\QrCodeGenerator;

use Symm\GoogleAuthenticator\QRCodeGenerator\QRCodeGenerator;
use Symm\GoogleAuthenticator\OtpAuthUri;

/**
 * Provides a QR code using Google Charts
 *
 * Note: You should not rely on this library for secure implementation as the secret key
 * is passed to Google charts.
 *
 * Class GoogleChartQrCodeGenerator
 * @package Symm\GoogleAuthenticator
 */
class GoogleChartQrCodeGenerator implements QRCodeGenerator
{

    private $size;

    public function __construct()
    {
        $this->size = 200;
    }

    /**
     * @param OtpAuthUri $authenticationData
     * @return \SplTempFileObject
     * @throws \Exception
     */
    public function getImageFile(OtpAuthUri $authenticationData)
    {

        $imageContents = file_get_contents($this->getImageUrl($authenticationData));

        if ($imageContents === false) {
            throw new \Exception('Error getting QR code from google charts');
        }

        $fileType = $this->getMimeType($imageContents);
        if ($fileType !== 'image/png') {
            throw new \Exception('Unexpected filetype: [' . $fileType . ']');
        }

        $fileObject = new \SplTempFileObject();
        $fileObject->fwrite($imageContents);
        $fileObject->rewind();

        return $fileObject;
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function getImageUrl(OtpAuthUri $authenticationData)
    {
        $urlEncoded = urlencode($authenticationData);

        $dimensions = $this->size . 'x' . $this->size;
        $targetUrl = 'https://chart.googleapis.com/chart?chs=' . $dimensions . '&chld=M|0&cht=qr&chl='.$urlEncoded.'';

        return $targetUrl;
    }

    private function getMimeType($buffer)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->buffer($buffer);
    }
}
