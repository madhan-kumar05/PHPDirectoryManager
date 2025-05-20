# PHP Directory manager
[![Latest Version on Packagist][ico-version]][link-packagist]

A PHP package for manage your directory
## Setup
Ensure you have [composer](http://getcomposer.org) installed, then run the following command:
```bash
composer require madhankumar/directory-manager
```
That will fetch the library and its dependencies inside your vendor folder. Then you can add the following to your .php files in order to use the library
```php
require_once __DIR__.'/vendor/autoload.php';
```
Then you need to `use` the relevant classes:
```php
use Madhankumar\DirectoryManager\DirectoryManager;
// It's good practice to also be ready to catch specific exceptions if defined,
// though \Exception will catch all.
// use Madhankumar\DirectoryManager\Exceptions\DirectoryManagerException;
```
# Usage ##
```php
$directoryManager = new DirectoryManager();

// Before running examples that create files/directories, 
// ensure the base path (e.g., './example_data') exists and is writable.
// You might need to create it manually or add a setup step in your script:
if (!is_dir('./example_data')) {
    mkdir('./example_data', 0777, true);
}

// List all folders and files in the given path:
try {
    $contents = $directoryManager->listDirectory("./example_data"); // Using a relative path
    // print_r($contents); // Output format is as previously documented
    echo "Directory listed successfully (see documentation for output format).\n";
} catch (\Exception $e) {
    echo "Error listing directory: " . $e->getMessage() . "\n";
}

// Create a new directory
try {
    if ($directoryManager->addFolder("new_folder", "./example_data")) {
        echo "Folder 'new_folder' created successfully in ./example_data.\n";
    }
} catch (\Exception $e) {
    echo "Error creating folder: " . $e->getMessage() . "\n";
}

// Create a new file with content
try {
    if ($directoryManager->createFile("./example_data", "new_file.txt", "Hello, World!")) {
        echo "File 'new_file.txt' created successfully in ./example_data.\n";
    }
} catch (\Exception $e) {
    echo "Error creating file: " . $e->getMessage() . "\n";
}

// Read content from a file
try {
    // Ensure the file exists for this example, the createFile example above should create it
    $content = $directoryManager->readFile("./example_data/new_file.txt");
    echo "File content: " . $content . "\n";
} catch (\Exception $e) {
    echo "Error reading file: " . $e->getMessage() . "\n";
}

// Update content of an existing file
try {
    // Ensure the file exists
    if ($directoryManager->updateFile("./example_data/new_file.txt", "New content here.")) {
        echo "File updated successfully.\n";
    }
} catch (\Exception $e) {
    echo "Error updating file: " . $e->getMessage() . "\n";
}

// Rename a file or folder
try {
    // First create something to rename for the example to be self-contained
    if (!file_exists("./example_data/old_name.txt")) { // Check if it wasn't created by a previous failed run
        $directoryManager->createFile("./example_data", "old_name.txt", "content");
    }
    if ($directoryManager->rename("./example_data/old_name.txt", "./example_data/new_name.txt")) {
        echo "Renamed successfully.\n";
    }
} catch (\Exception $e) {
    echo "Error renaming: " . $e->getMessage() . "\n";
}

// Move a file or folder
try {
    // Create source and destination for self-contained example
    if (!is_dir("./example_data/item_to_move_parent")) {
      $directoryManager->addFolder("item_to_move_parent", "./example_data");
    }
    if (!file_exists("./example_data/item_to_move_parent/move_me.txt")) {
      $directoryManager->createFile("./example_data/item_to_move_parent", "move_me.txt", "content");
    }
    if (!is_dir("./example_data/another_location")) {
      $directoryManager->addFolder("another_location", "./example_data");
    }
    
    if ($directoryManager->move("./example_data/item_to_move_parent/move_me.txt", "./example_data/another_location/moved_item.txt")) {
        echo "Moved successfully.\n";
    }
} catch (\Exception $e) {
    echo "Error moving: " . $e->getMessage() . "\n";
}

// Copy a folder
try {
    // Create source and destination for self-contained example
    if (!is_dir("./example_data/source_folder")) {
        $directoryManager->addFolder("source_folder", "./example_data");
    }
    if (!file_exists("./example_data/source_folder/copy_me.txt")) {
        $directoryManager->createFile("./example_data/source_folder", "copy_me.txt", "content");
    }
    if (!is_dir("./example_data/destination_parent")) {
        $directoryManager->addFolder("destination_parent", "./example_data");
    }
    // The copyFolder method should create 'destination_folder' if it doesn't exist.
    if ($directoryManager->copyFolder("./example_data/source_folder", "./example_data/destination_parent/destination_folder")) {
        echo "Folder copied successfully.\n";
    }
} catch (\Exception $e) {
    echo "Error copying folder: " . $e->getMessage() . "\n";
}

// Delete a file or folder
try {
    // Create something to delete
    if (!file_exists("./example_data/to_delete.txt")) {
        $directoryManager->createFile("./example_data", "to_delete.txt", "content");
    }
    if ($directoryManager->delete("./example_data/to_delete.txt")) {
        echo "Deleted successfully.\n";
    }
} catch (\Exception $e) {
    echo "Error deleting: " . $e->getMessage() . "\n";
}

// Example of how to clean up the example_data directory (optional for users)
// try {
//     if (is_dir('./example_data')) {
//         // Be careful with recursive deletion! This is a simple example.
//         // You might need a more robust recursive delete for production scenarios.
//         function recursiveRemoveDirectory($directory) {
//             $items = array_diff(scandir($directory), ['.', '..']);
//             foreach ($items as $item) {
//                 $path = $directory . DIRECTORY_SEPARATOR . $item;
//                 if (is_dir($path)) {
//                     recursiveRemoveDirectory($path);
//                 } else {
//                     unlink($path);
//                 }
//             }
//             return rmdir($directory);
//         }
//         if (recursiveRemoveDirectory('./example_data')) {
//             echo "Cleaned up ./example_data directory.\n";
//         } else {
//             echo "Failed to clean up ./example_data directory.\n";
//         }
//     }
// } catch (\Exception $e) {
//     echo "Error during cleanup: " . $e->getMessage() . "\n";
// }

```
The `listDirectory` function lists all folders and files in the below format
```bash
{
    "folders": [ // Array
        {
            "id": int,
            "name":  string, // Folder name
            "size": int, // Folder size
            "created_time": string, // Folder created date timestamp in the format of Y-m-d HH:mm:ss
            "modified_time":  string, // Folder modified date timestamp in the format of Y-m-d HH:mm:ss
            "created_timestamp": int, // timestamp
            "modified_timestamp": int, // timestamp
        },
        ...
    ],
    "files": [ // Array
        {
            "id": int,
            "name":  string, // File name
            "path": string, // File full path
            "size": int, // File size
            "created_time": string, // Folder created date timestamp in the format of Y-m-d HH:mm:ss
            "modified_time":  string, // Folder modified date timestamp in the format of Y-m-d HH:mm:ss
            "created_timestamp": int, // timestamp
            "modified_timestamp": int, // timestamp
        },
        ...
    ]
}
```

## Error Handling

All operational methods (like `addFolder`, `createFile`, `readFile`, `updateFile`, `rename`, `delete`, `move`, `copyFolder`, `listDirectory`) will throw an `\Exception` if an error occurs (e.g., file not found, permissions error, destination already exists). You should wrap your calls in `try-catch` blocks to handle potential exceptions.

```php
use Madhankumar\DirectoryManager\DirectoryManager;
// use Madhankumar\DirectoryManager\Exceptions\DirectoryManagerException; // If you want to be more specific and it's consistently used

$directoryManager = new DirectoryManager();

try {
    // Example: Attempt to read a non-existent file
    $content = $directoryManager->readFile("./example_data/non_existent_file.txt");
    echo "File content: " . $content . "\n";
} catch (\Exception $e) {
    // Handle the error gracefully
    // error_log("Operation failed: " . $e->getMessage()); // For server-side logging
    echo "Sorry, an error occurred: " . $e->getMessage() . "\n";
}
```
This ensures that your application can gracefully manage issues without crashing. The exception message will provide details about the specific error encountered.

Author: Madhankumar <madhantocontact@gmail.com>

[ico-version]: https://img.shields.io/packagist/v/madhankumar/directory-manager.svg
[link-packagist]: https://packagist.org/packages/madhankumar/directory-manager
