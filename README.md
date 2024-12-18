# PHP Directory manager
![![Latest Version on Packagist][ico-version]][link-packagist]

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
```
# Usage ##
```php
$directoryManager = new DirectoryManager();
```
List all folders and files in the given path:
```php
$this->directoryManager->listDirectory("/var/www/html");
```
The above function list all folders and files in the below format
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

Author: Madhankumar <madhantocontact@gmail.com>

[link-packagist]: https://packagist.org/packages/madhankumar/directory-manager
