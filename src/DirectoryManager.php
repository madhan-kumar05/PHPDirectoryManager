<?php

namespace DirectoryManager;

use DirectoryManager\Exceptions\DirectoryManagerException;
use DirectoryManager\Helpers\PathHelper;
use DirectoryManager\DirectoryHandler;
use DirectoryManager\FileHandler;

class DirectoryManager
{
    private DirectoryHandler $directoryHandler;
    private FileHandler $fileHandler;

    public function __construct()
    {
        $this->directoryHandler = new DirectoryHandler();
        $this->fileHandler = new FileHandler();
    }

    public function listDirectories(string $path): array
    {
        return $this->directoryHandler->listDirectories($path);
    }

    public function addFile(array $fileDetails, string $destination): bool
    {
        return $this->fileHandler->addFile($fileDetails, $destination);
    }

    public function addFolder(string $folderName, string $destination): bool
    {
        return $this->directoryHandler->addFolder($folderName, $destination);
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        return $this->directoryHandler->rename($oldPath, $newPath) 
            || $this->fileHandler->rename($oldPath, $newPath);
    }

    public function delete(string $path): bool
    {
        return is_dir($path) 
            ? $this->directoryHandler->delete($path) 
            : $this->fileHandler->delete($path);
    }

    public function move(string $oldPath, string $newPath): bool
    {
        return $this->directoryHandler->move($oldPath, $newPath) 
            || $this->fileHandler->move($oldPath, $newPath);
    }
}
