<?php

namespace App\Tests\Service;

use App\Service\ImageUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploaderTest extends TestCase
{
    private string $dossierTemp;
    private ImageUploader $imageUploader;
    protected function setUp(): void
    {
        $this->dossierTemp = sys_get_temp_dir() . '/uploads_test';
        mkdir($this->dossierTemp, 0777, true);
        $this->imageUploader = new ImageUploader($this->dossierTemp);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dossierTemp . "/*") as $fichier){
            unlink($fichier);
        }
        rmdir($this->dossierTemp);
    }

    public function testUpload()
    {
        $fichierTempChemin = tempnam(sys_get_temp_dir(), 'image.jpg');
        file_put_contents($fichierTempChemin,'contenu_test');
        $uploadedFile = new UploadedFile($fichierTempChemin, 'image.jpg', 'image/jpeg', null, true);

        $fichier = $this->imageUploader->upload($uploadedFile);

        self::assertFileExists($this->dossierTemp . '/' . $fichier);

    }

    public function testDelete()
    {
        $chemin = $this->dossierTemp . '/test.jpg';
        file_put_contents($chemin, 'contenu_test');
        $this->imageUploader->delete('test.jpg');
        self::assertFileDoesNotExist($chemin);
    }


}
