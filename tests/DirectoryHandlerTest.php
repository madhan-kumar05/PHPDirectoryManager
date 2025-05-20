<?php

namespace Madhankumar\DirectoryManager\Tests;

use Madhankumar\DirectoryManager\DirectoryHandler;
use Madhankumar\DirectoryManager\Helpers\PathHelper; // Assuming PathHelper is used and autoloaded
use PHPUnit\Framework\TestCase;

class DirectoryHandlerTest extends TestCase
{
    private DirectoryHandler $directoryHandler;
    private string $testBaseDir = __DIR__ . '/test_dirs';
    private string $dir1 = 'dir1';
    private string $dir2 = 'dir2';
    private string $nestedDir = 'nested_dir';
    private string $fileInDir = 'file.txt';

    protected function setUp(): void
    {
        $this->directoryHandler = new DirectoryHandler();

        // Ensure the base test directory exists and is clean
        if (is_dir($this->testBaseDir)) {
            $this->recursiveRemoveDirectory($this->testBaseDir);
        }
        mkdir($this->testBaseDir, 0777, true);
    }

    protected function tearDown(): void
    {
        // Clean up the base test directory
        if (is_dir($this->testBaseDir)) {
            $this->recursiveRemoveDirectory($this->testBaseDir);
        }
    }

    private function recursiveRemoveDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = array_diff(scandir($directory), ['.', '..']);

        foreach ($items as $item) {
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->recursiveRemoveDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    }

    // --- addDirectory Tests ---
    public function testAddDirectorySuccessfully()
    {
        $fullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $this->assertTrue($this->directoryHandler->addDirectory($this->dir1, $this->testBaseDir));
        $this->assertDirectoryExists($fullPath);
    }

    public function testAddDirectoryThrowsExceptionIfAlreadyExists()
    {
        $fullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        mkdir($fullPath); // Create directory first

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Directory already exists at given path: " . $fullPath);
        $this->directoryHandler->addDirectory($this->dir1, $this->testBaseDir);
    }

    public function testAddDirectoryThrowsExceptionForNonWritableDestination()
    {
        // Create a temporary non-writable directory
        $nonWritableDir = PathHelper::joinPaths($this->testBaseDir, 'non_writable_parent');
        mkdir($nonWritableDir, 0555); // Read and execute only

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Destination directory is not writable: " . $nonWritableDir);
        $this->directoryHandler->addDirectory($this->dir1, $nonWritableDir);

        // Cleanup: Make it writable to remove
        chmod($nonWritableDir, 0777);
    }

    // --- renameDirectory & generic rename Tests ---
    public function testRenameDirectorySuccessfully()
    {
        $oldFullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $newFullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        mkdir($oldFullPath);

        $this->assertTrue($this->directoryHandler->renameDirectory($this->dir1, $this->dir2, $this->testBaseDir));
        $this->assertDirectoryDoesNotExist($oldFullPath);
        $this->assertDirectoryExists($newFullPath);
    }
    
    public function testGenericRenameDirectorySuccessfully()
    {
        $oldFullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $newFullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        mkdir($oldFullPath);

        $this->assertTrue($this->directoryHandler->rename($oldFullPath, $newFullPath));
        $this->assertDirectoryDoesNotExist($oldFullPath);
        $this->assertDirectoryExists($newFullPath);
    }

    public function testRenameDirectoryThrowsExceptionForNonExistentOldName()
    {
        $this->expectException(\Exception::class);
        $oldPath = PathHelper::joinPaths($this->testBaseDir, 'non_existent_dir');
        $this->expectExceptionMessage("Directory not found at path: " . $oldPath);
        $this->directoryHandler->renameDirectory('non_existent_dir', $this->dir2, $this->testBaseDir);
    }
    
    public function testGenericRenameThrowsExceptionForNonExistentOldPath()
    {
        $this->expectException(\Exception::class);
        $oldPath = PathHelper::joinPaths($this->testBaseDir, 'non_existent_dir');
        $newPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        $this->expectExceptionMessage("Source directory not found: " . $oldPath);
        $this->directoryHandler->rename($oldPath, $newPath);
    }

    public function testRenameDirectoryThrowsExceptionForExistingNewName()
    {
        $oldFullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $newFullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        mkdir($oldFullPath);
        mkdir($newFullPath); // New name already exists

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Directory already exists at given path: " . $newFullPath);
        $this->directoryHandler->renameDirectory($this->dir1, $this->dir2, $this->testBaseDir);
    }

    public function testGenericRenameThrowsExceptionIfParentDirectoriesDiffer()
    {
        $dirInTestBase = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        mkdir($dirInTestBase);

        $anotherParentDir = PathHelper::joinPaths($this->testBaseDir, 'another_parent');
        mkdir($anotherParentDir); // Ensure this parent exists for the new path

        $newPathInAnotherParent = PathHelper::joinPaths($anotherParentDir, $this->dir2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("For renaming, the source and destination parent directories must be the same. Use move() to change the parent directory.");
        $this->directoryHandler->rename($dirInTestBase, $newPathInAnotherParent);
    }


    // --- deleteDirectory & generic delete Tests ---
    public function testDeleteEmptyDirectorySuccessfully()
    {
        $fullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        mkdir($fullPath);
        $this->assertTrue($this->directoryHandler->deleteDirectory($fullPath));
        $this->assertDirectoryDoesNotExist($fullPath);
    }
    
    public function testGenericDeleteEmptyDirectorySuccessfully()
    {
        $fullPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        mkdir($fullPath);
        $this->assertTrue($this->directoryHandler->delete($fullPath));
        $this->assertDirectoryDoesNotExist($fullPath);
    }

    public function testDeleteNonEmptyDirectorySuccessfully()
    {
        $parentDir = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $nestedPath = PathHelper::joinPaths($parentDir, $this->nestedDir);
        $filePath = PathHelper::joinPaths($nestedPath, $this->fileInDir);
        mkdir($parentDir);
        mkdir($nestedPath);
        touch($filePath);

        $this->assertTrue($this->directoryHandler->deleteDirectory($parentDir));
        $this->assertDirectoryDoesNotExist($parentDir);
        $this->assertDirectoryDoesNotExist($nestedPath);
        $this->assertFileDoesNotExist($filePath);
    }

    public function testDeleteDirectoryThrowsExceptionForNonExistentPath()
    {
        $nonExistentPath = PathHelper::joinPaths($this->testBaseDir, 'non_existent_dir');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Directory not found at path: " . $nonExistentPath);
        $this->directoryHandler->deleteDirectory($nonExistentPath);
    }

    // --- moveDirectory & generic move Tests ---
    public function testMoveDirectorySuccessfully()
    {
        $oldPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $newParentPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        $newPath = PathHelper::joinPaths($newParentPath, $this->dir1); // Moving dir1 into dir2
        mkdir($oldPath);
        mkdir($newParentPath); // Destination parent must exist

        $this->assertTrue($this->directoryHandler->moveDirectory($oldPath, $newPath));
        $this->assertDirectoryDoesNotExist($oldPath);
        $this->assertDirectoryExists($newPath);
    }

    public function testGenericMoveDirectorySuccessfully()
    {
        $oldPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $newParentPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        $newPath = PathHelper::joinPaths($newParentPath, $this->dir1); 
        mkdir($oldPath);
        mkdir($newParentPath);

        $this->assertTrue($this->directoryHandler->move($oldPath, $newPath));
        $this->assertDirectoryDoesNotExist($oldPath);
        $this->assertDirectoryExists($newPath);
    }


    public function testMoveDirectoryThrowsExceptionForNonExistentSource()
    {
        $oldPath = PathHelper::joinPaths($this->testBaseDir, 'non_existent_source');
        $newPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Source directory not found at path: " . $oldPath);
        $this->directoryHandler->moveDirectory($oldPath, $newPath);
    }

    public function testMoveDirectoryThrowsExceptionIfDestinationExists()
    {
        $oldPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $newPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        mkdir($oldPath);
        mkdir($newPath); // Destination already exists

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Destination directory already exists at path: " . $newPath);
        $this->directoryHandler->moveDirectory($oldPath, $newPath);
    }
    
    public function testMoveDirectoryThrowsExceptionIfDestinationParentNonWritable()
    {
        $oldPath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        mkdir($oldPath);

        $nonWritableParent = PathHelper::joinPaths($this->testBaseDir, "non_writable_dest_parent");
        mkdir($nonWritableParent, 0555); // Read and execute only

        $newPath = PathHelper::joinPaths($nonWritableParent, $this->dir2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Destination parent directory is not writable or does not exist: " . $nonWritableParent);
        $this->directoryHandler->moveDirectory($oldPath, $newPath);
        
        chmod($nonWritableParent, 0777); // cleanup
    }


    // --- copyDirectory Tests ---
    public function testCopyDirectorySuccessfully()
    {
        $sourcePath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        $nestedSourcePath = PathHelper::joinPaths($sourcePath, $this->nestedDir);
        $filePathInSource = PathHelper::joinPaths($nestedSourcePath, $this->fileInDir);
        mkdir($sourcePath);
        mkdir($nestedSourcePath);
        touch($filePathInSource);

        $destinationPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        // Destination directory itself might be created by copyDirectory if it doesn't exist

        $this->assertTrue($this->directoryHandler->copyDirectory($sourcePath, $destinationPath));
        $this->assertDirectoryExists($destinationPath);
        $this->assertDirectoryExists(PathHelper::joinPaths($destinationPath, $this->nestedDir));
        $this->assertFileExists(PathHelper::joinPaths($destinationPath, $this->nestedDir, $this->fileInDir));
        $this->assertEquals(file_get_contents($filePathInSource), file_get_contents(PathHelper::joinPaths($destinationPath, $this->nestedDir, $this->fileInDir)));
    }

    public function testCopyDirectoryThrowsExceptionForNonExistentSource()
    {
        $sourcePath = PathHelper::joinPaths($this->testBaseDir, 'non_existent_source');
        $destinationPath = PathHelper::joinPaths($this->testBaseDir, $this->dir2);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Source directory does not exist: " . $sourcePath);
        $this->directoryHandler->copyDirectory($sourcePath, $destinationPath);
    }

    public function testCopyDirectoryThrowsExceptionForNonWritableDestinationParent()
    {
        $sourcePath = PathHelper::joinPaths($this->testBaseDir, $this->dir1);
        mkdir($sourcePath);

        $nonWritableParent = PathHelper::joinPaths($this->testBaseDir, "non_writable_dest_parent");
        mkdir($nonWritableParent, 0555); // Read and execute only

        $destinationPath = PathHelper::joinPaths($nonWritableParent, $this->dir2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Destination directory parent is not writable: " . $nonWritableParent);
        $this->directoryHandler->copyDirectory($sourcePath, $destinationPath);
        
        chmod($nonWritableParent, 0777); // cleanup
    }
}
?>
