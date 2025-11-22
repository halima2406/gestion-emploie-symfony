<?php
namespace App\Service\Impl;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploaderService
{
    public function __construct(
        private readonly string $uploadDir,
        private readonly SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename     = preg_replace('/[^A-Za-z0-9\-_]+/', '_', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

       try {
            $file->move($this->uploadDir, $newFilename);
        } catch (FileException $e) {

           throw new \RuntimeException("Erreur lors de l'upload du fichier : " . $e->getMessage());
        }

        return $newFilename;
    }

}
