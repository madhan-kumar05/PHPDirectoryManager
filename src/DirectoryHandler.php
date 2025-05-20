<?php

namespace Madhankumar\DirectoryManager;

use Madhankumar\DirectoryManager\Helpers\PathHelper;

class DirectoryHandler
{
    public function listDirectories(string $base_directory): array
    {
        $folders = [];
        $files = [];
    
        if (is_dir($base_directory)) {
            $items = scandir($base_directory);
            $i = 0;
    
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
    
                $full_path = $base_directory . DIRECTORY_SEPARATOR . $item;
    
                if (is_dir($full_path)) {
                    $folders[] = [
                        'id' => $i,
                        'name' => $item,
                        'path' => $full_path,
                        'size' => $this->get_directory_size($full_path),
                        'created_time' => date("Y-m-d H:i:s", filectime($full_path)),
                        'modified_time' => date("Y-m-d H:i:s", filemtime($full_path)),
                        'created_timestamp' => filectime($full_path), // To aid sorting
                        'modified_timestamp' => filemtime($full_path), // To aid sorting
                    ];
                } elseif (is_file($full_path)) {
                    $files[] = [
                        'id' => $i,
                        'name' => $item,
                        'path' => $full_path,
                        'size' => filesize($full_path),
                        'created_time' => date("Y-m-d H:i:s", filectime($full_path)),
                        'modified_time' => date("Y-m-d H:i:s", filemtime($full_path)),
                        'created_timestamp' => filectime($full_path), // To aid sorting
                        'modified_timestamp' => filemtime($full_path), // To aid sorting
                    ];
                }
                $i++;
            }
    
            // Sort folders by created time (descending)
            usort($folders, function ($a, $b) {
                return $b['created_timestamp'] <=> $a['created_timestamp']; // Descending order
            });
    
            // Sort files by created time (descending)
            usort($files, function ($a, $b) {
                return $b['created_timestamp'] <=> $a['created_timestamp']; // Descending order
            });
        }
    
        return [
            'folders' => $folders,
            'files' => $files,
        ];
    }

    public function addDirectory(string $folderName, string $destination): bool
    {
        $fullPath = PathHelper::joinPaths($destination, $folderName);

        if (is_dir($fullPath)) {
            throw new \Exception("Directory already exists at given path: $fullPath");
        }

        if (!is_writable($destination)) {
            throw new \Exception("Destination directory is not writable: $destination");
        }

        if (!mkdir($fullPath, 0777, true)) {
            throw new \Exception("Failed to create directory at $fullPath");
        }
        return true;
    }

    public function renameDirectory(string $oldFolderName, string $newFolderName, string $destination): bool
    {
        $oldPath = PathHelper::joinPaths($destination, $oldFolderName);
        $newPath = PathHelper::joinPaths($destination, $newFolderName);

        if (!is_dir($oldPath)) {
            throw new \Exception("Directory not found at path: $oldPath");
        }

        if (is_dir($newPath)) {
            throw new \Exception("Directory already exists at given path: $newPath");
        }

        if (!is_writable($destination)) {
            throw new \Exception("Destination directory is not writable: $destination");
        }

        if (!rename($oldPath, $newPath)) {
            throw new \Exception("Failed to rename directory from $oldPath to $newPath");
        }
        return true;
    }

    public function deleteDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            throw new \Exception("Directory not found at path: $path");
        }

        if (!is_writable(dirname($path))) {
            throw new \Exception("Parent directory is not writable: " . dirname($path));
        }
        
        $files = array_diff(scandir($path), ['.', '..']);

        foreach ($files as $file) {
            $fullPath = PathHelper::joinPaths($path, $file);
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                if (!is_writable($fullPath) || !unlink($fullPath)) {
                    throw new \Exception("Failed to delete file: $fullPath");
                }
            }
        }

        if (!rmdir($path)) {
            throw new \Exception("Failed to delete directory at $path");
        }
        return true;
    }

    public function moveDirectory(string $oldPath, string $newPath): bool
    {
        if (!is_dir($oldPath)) {
            throw new \Exception("Source directory not found at path: $oldPath");
        }

        if (is_dir($newPath)) {
            throw new \Exception("Destination directory already exists at path: $newPath");
        }

        // Check if the parent directory of the new path is writable
        $newPathParent = dirname($newPath);
        if (!is_dir($newPathParent) || !is_writable($newPathParent)) {
            throw new \Exception("Destination parent directory is not writable or does not exist: $newPathParent");
        }
        
        if (!is_writable($oldPath)) {
            throw new \Exception("Source directory is not writable: $oldPath");
        }

        if (!rename($oldPath, $newPath)) {
            throw new \Exception("Failed to move directory from $oldPath to $newPath");
        }
        return true;
    }

    public function copyDirectory(string $source, string $destination): bool
    {
        if (!is_dir($source)) {
            throw new \Exception("Source directory does not exist: $source");
        }

        if (!is_writable(dirname($destination))) {
            // Check if the parent of the destination is writable
             throw new \Exception("Destination directory parent is not writable: " . dirname($destination));
        }
        
        if (!file_exists($destination)) {
            if (!mkdir($destination, 0777, true)) {
                throw new \Exception("Failed to create destination directory: $destination");
            }
        }


        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $srcPath = PathHelper::joinPaths($source, $file);
            $destPath = PathHelper::joinPaths($destination, $file);

            if (is_dir($srcPath)) {
                // Recursively call copyDirectory for subdirectories
                if (!$this->copyDirectory($srcPath, $destPath)) {
                    // If recursive copy fails, throw an exception
                    throw new \Exception("Failed to copy subdirectory from $srcPath to $destPath");
                }
            } else {
                // Copy file
                if (!copy($srcPath, $destPath)) {
                    throw new \Exception("Failed to copy file from $srcPath to $destPath");
                }
            }
        }
        return true;
    }

    function get_directory_size($directory) {
        $size = 0;
        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $full_path = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($full_path)) {
                // Recursive call for nested directories
                $size += $this->get_directory_size($full_path);
            } else {
                // Get the file size
                $size += filesize($full_path);
            }
        }

        return $size;
    }

    public function delete(string $path): bool
    {
        return $this->deleteDirectory($path);
    }

    public function move(string $oldPath, string $newPath): bool
    {
        return $this->moveDirectory($oldPath, $newPath);
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        $oldFolderName = basename($oldPath);
        $newFolderName = basename($newPath);
        $destination = dirname($oldPath);
        $newDestination = dirname($newPath);

        if ($destination !== $newDestination) {
            throw new \Exception("For renaming, the source and destination parent directories must be the same. Use move() to change the parent directory.");
        }

        if (!is_dir($oldPath)) {
            throw new \Exception("Source directory not found: $oldPath");
        }
        
        // renameDirectory will handle the other checks like if newFolderName already exists
        return $this->renameDirectory($oldFolderName, $newFolderName, $destination);
    }
}
