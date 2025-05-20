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
        if (!file_exists($oldPath)) {
            throw new \Exception("Source path does not exist: $oldPath");
        }

        if (is_dir($oldPath)) {
            return $this->directoryHandler->rename($oldPath, $newPath);
        } elseif (is_file($oldPath)) {
            return $this->fileHandler->rename($oldPath, $newPath);
        } else {
            throw new \Exception("Source path is not a file or directory: $oldPath");
        }
    }

    public function delete(string $path): bool
    {
        if (!file_exists($path)) {
            throw new \Exception("Path does not exist: $path");
        }

        if (is_dir($path)) {
            return $this->directoryHandler->delete($path);
        } elseif (is_file($path)) {
            return $this->fileHandler->delete($path);
        } else {
            throw new \Exception("Path is not a file or directory: $path");
        }
    }

    public function move(string $oldPath, string $newPath): bool
    {
        if (!file_exists($oldPath)) {
            throw new \Exception("Source path does not exist: $oldPath");
        }

        if (is_dir($oldPath)) {
            return $this->directoryHandler->move($oldPath, $newPath);
        } elseif (is_file($oldPath)) {
            return $this->fileHandler->move($oldPath, $newPath);
        } else {
            throw new \Exception("Source path is not a file or directory: $oldPath");
        }
    }

    public function readFile(string $filePath): string
    {
        return $this->fileHandler->readFile($filePath);
    }

    public function updateFile(string $filePath, string $content): bool
    {
        return $this->fileHandler->updateFile($filePath, $content);
    }

    public function createFile(string $destination, string $fileName, string $content = ''): bool
    {
        return $this->fileHandler->createFile($destination, $fileName, $content);
    }
}
