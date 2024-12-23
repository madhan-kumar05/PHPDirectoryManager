<?php

namespace Madhankumar\DirectoryManager;

use DirectoryManager\Exceptions\DirectoryManagerException;
use DirectoryManager\Helpers\PathHelper;
use Madhankumar\DirectoryManager\DirectoryHandler;
use Madhankumar\DirectoryManager\FileHandler;

class DirectoryManager
{
    private DirectoryHandler $directoryHandler;
    private FileHandler $fileHandler;

    public function __construct()
    {
        $this->directoryHandler = new DirectoryHandler();
        $this->fileHandler = new FileHandler();
    }

    public function listDirectory(string $path): array
    {
        return $this->directoryHandler->listDirectories($path);
    }

    // Directory
    public function addFolder(string $folderName, string $destination): string
    {
        return $this->directoryHandler->addDirectory($folderName, $destination);
    }

    public function renameFolder(string $oldFolderName, string $newFolderName, string $destination): string
    {
        return $this->directoryHandler->renameDirectory($oldFolderName, $newFolderName, $destination);
    }

    public function deleteFolder(string $path): string
    {
        return $this->directoryHandler->deleteDirectory($path);
    }

    public function moveFolder(string $oldPath, string $newPath): string
    {
        return $this->directoryHandler->moveDirectory($oldPath, $newPath);
    }

    public function copyFolder(string $oldPath, string $newPath): string
    {
        return $this->directoryHandler->copyDirectory($oldPath, $newPath);
    }

    // File
    public function addFile(array $fileDetails, string $destination): bool
    {
        return $this->fileHandler->addFile($fileDetails, $destination);
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
