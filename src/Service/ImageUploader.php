<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    public function __construct(private string $uploadDir){}

    public function upload(UploadedFile $file) : string
    {
        $extension = $file->guessExtension();
        $nom = uniqid() . '.' . $extension;

        $file->move($this->uploadDir, $nom);

        return $nom;
    }

    public function delete(string $filename) : void
    {
        $chemin = $this->uploadDir . '/' . $filename;
        if (file_exists($chemin)){
            unlink($chemin);
        }
    }
}
