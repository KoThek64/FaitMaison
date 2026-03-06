<?php

namespace App\Tests\Service;

use App\Service\ImageUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploaderTest extends TestCase
{
    protected function setUp(): void
    {
        $dossierTemp = sys_get_temp_dir() . '/uploads_test';
        $imageUploader = new ImageUploader($fichier);
    }

    protected function tearDown(): void
    {
    }

}
