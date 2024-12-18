<?php

namespace Madhankumar\DirectoryManager;

class FileHandler
{
    public function addFile(array $fileDetails, string $destination): bool
    {
        $filePath = $destination . DIRECTORY_SEPARATOR . $fileDetails['name'];

        if (move_uploaded_file($fileDetails['tmp_name'], $filePath)) {
            return true;
        }

        throw new \Exception("Failed to upload file to $destination");
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        return rename($oldPath, $newPath);
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function move(string $oldPath, string $newPath): bool
    {
        return rename($oldPath, $newPath);
    }
}
