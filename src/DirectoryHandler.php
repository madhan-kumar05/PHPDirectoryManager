<?php

namespace DirectoryManager;

use DirectoryManager\Helpers\PathHelper;

class DirectoryHandler
{
    public function listDirectories(string $path): array
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

    public function addFolder(string $folderName, string $destination): bool
    {
        $fullPath = PathHelper::joinPaths($destination, $folderName);

        if (!mkdir($fullPath, 0777, true)) {
            throw new \Exception("Failed to create folder at $fullPath");
        }
        return true;
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        return rename($oldPath, $newPath);
    }

    public function delete(string $path): bool
    {
        if (!is_dir($path)) {
            return false; // Not a directory
        }

        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file) {
            $fullPath = "$path/$file";
            is_dir($fullPath) ? $this->delete($fullPath) : unlink($fullPath);
        }

        return rmdir($path);
    }

    public function move(string $oldPath, string $newPath): bool
    {
        return rename($oldPath, $newPath);
    }
}
