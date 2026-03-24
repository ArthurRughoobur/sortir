<?php

namespace App\Utils;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(private UserRepository $userRepository)
    {

    }

    public function upload(UploadedFile $file, string $directory, string $name = '')
    {
        $newFileName = ($name ? $name . '_' : '') . $file->guessExtension();
        $file->move($directory, $newFileName);
        return $newFileName;
    }

    public function delete(string $fileName, string $directory)
    {
        return unlink($directory . DIRECTORY_SEPARATOR . $fileName);
    }

    public function update(string $oldFileName, string $directory, UploadedFile $file, string $newName = '')
    {
        $this->delete($oldFileName, $directory);
        $this->upload($file, $directory, $newName);
    }

}
