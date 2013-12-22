<?php

namespace Symm\GoogleAuthenticator\QrCodeGenerator;

use Symm\GoogleAuthenticator\QRCodeGenerator\QRCodeGenerator;
use Symm\GoogleAuthenticator\OtpAuthUri;
use Endroid\QrCode\QrCode;

/**
 * Provides a QR code using the Endroid QR library
 *
 * Class EndroidQrCodeGenerator
 * @package Symm\GoogleAuthenticator
 */
class EndroidQrCodeGenerator implements QRCodeGenerator
{

    private $size;

    public function __construct()
    {
        $this->size = 200;

        if (!class_exists('Endroid\QrCode\QrCode')) {
            throw new \Exception(
                'This generator requires the endroid QrCode library (https://github.com/endroid/QrCode).'
            );
        }
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param OtpAuthUri $authenticationData
     * @return \SplFileObject
     */
    public function getImageFile(OtpAuthUri $authenticationData)
    {
        $padding = 0;

        $keyUri = $authenticationData->getUri();

        $qrCode = new QrCode();
        $qrCode->setText($keyUri);
        $qrCode->setSize($this->size);
        $qrCode->setPadding($padding);

        $file = new \SplFileObject(tempnam(sys_get_temp_dir(), rand()), 'w+');
        $file->fwrite($qrCode->get());
        $file->rewind();

        return $file;
    }

    public function getImageUrl(OtpAuthUri $authenticationData)
    {
        $imageFile = $this->getImageFile($authenticationData);

        $imageContents = '';
        while (!$imageFile->eof()) {
            $imageContents .= $imageFile->fgets();
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $imageFormat = $finfo->buffer($imageContents);

        $imageUrl = 'data:' . $imageFormat . ';base64,' . base64_encode($imageContents);

        return $imageUrl;
    }
}
