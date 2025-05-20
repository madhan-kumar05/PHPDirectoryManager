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

    public function createFile(string $destination, string $fileName, string $content = ''): bool
    {
        $filePath = $destination . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($filePath)) {
            throw new \Exception("File already exists: $filePath");
        }

        // Ensure the destination directory exists and is writable
        if (!is_dir($destination) || !is_writable($destination)) {
            throw new \Exception("Destination directory is not writable or does not exist: $destination");
        }

        if (file_put_contents($filePath, $content) === false) {
            throw new \Exception("Failed to create file: $filePath");
        }

        return true;
    }

    public function readFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File does not exist: $filePath");
        }

        if (!is_readable($filePath)) {
            throw new \Exception("File is not readable: $filePath");
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new \Exception("Failed to read file content: $filePath");
        }

        return $content;
    }

    public function updateFile(string $filePath, string $content): bool
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File does not exist: $filePath");
        }

        if (!is_writable($filePath)) {
            throw new \Exception("File is not writable: $filePath");
        }

        if (file_put_contents($filePath, $content) === false) {
            throw new \Exception("Failed to update file: $filePath");
        }

        return true;
    }
}
