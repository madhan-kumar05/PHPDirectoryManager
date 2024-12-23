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

    public function addDirectory(string $folderName, string $destination): string
    {
        $fullPath = PathHelper::joinPaths($destination, $folderName);

        if (is_dir($fullPath)) {
            return "Directory already exists at given path: $folderName";
        }

        return mkdir($fullPath, 0777, true) ? "Directory created at $destination" : "Failed to create folder at $fullPath";
    }

    public function renameDirectory(string $oldFolderName, string $newFolderName, string $destination): string
    {
        $oldPath = PathHelper::joinPaths($destination, $oldFolderName);
        $newPath = PathHelper::joinPaths($destination, $newFolderName);

        if (!is_dir($oldPath)) {
            return "Directory not exists at given path: $oldFolderName";
        }

        if (is_dir($newPath)) {
            return "Directory already exists at given path: $newFolderName";
        }

        return rename($oldPath, $newPath) ? "Directory renamed from $oldFolderName to $newFolderName" : "Failed to rename directory";
    }

    public function deleteDirectory(string $path): string
    {
        if (!is_dir($path)) {
            return "Directory not exists at given path: $path";
        }

        $files = array_diff(scandir($path), ['.', '..']);

        foreach ($files as $file) {
            $fullPath = PathHelper::joinPaths($path, $file);
            is_dir($fullPath) ? $this->deleteDirectory($fullPath) : unlink($fullPath);
        }

        return rmdir($path) ? "Directory deleted at $path successfully" : "Failed to delete directory at $path";
    }

    public function moveDirectory(string $oldPath, string $newPath): string
    {
        if (is_dir($newPath)) {
            return "Directory already exists at given path: $newPath";
        }

        if (!is_dir($oldPath)) {
            return "Directory not exists at given path: $oldPath";
        }

        return rename($oldPath, $newPath) ? "Directory moved successfully" : "Failed to move directory";
    }

    public function copyDirectory(string $source, string $destination): string
    {
        if (!is_dir($source)) {
            return "Source directory does not exist: $source";
        }

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $srcPath = PathHelper::joinPaths($source, $file);
            $destPath = PathHelper::joinPaths($destination, $file);

            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $destPath);
            } else {
                if (!copy($srcPath, $destPath)) {
                    return "Failed to copy file: $srcPath to $destPath";
                }
            }
        }

        return "Directory copied successfully to '$destination'";
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
}
