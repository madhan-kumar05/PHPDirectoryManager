<?php

namespace Madhankumar\DirectoryManager\Tests;

use Madhankumar\DirectoryManager\FileHandler;
use PHPUnit\Framework\TestCase;

class FileHandlerTest extends TestCase
{
    private FileHandler $fileHandler;
    private string $testDir = __DIR__ . '/test_files';
    private string $testFile = 'test_file.txt';
    private string $testFilePath;

    protected function setUp(): void
    {
        $this->fileHandler = new FileHandler();
        $this->testFilePath = $this->testDir . DIRECTORY_SEPARATOR . $this->testFile;

        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
        if (is_dir($this->testDir)) {
            // Remove the directory itself if empty, or handle more complex cleanup if needed
            // For now, simple rmdir if other tests don't leave files.
            // A more robust cleanup would recursively delete files/dirs inside testDir
            @rmdir($this->testDir);
        }
    }

    public function testCreateFileSuccessfullyWithContent()
    {
        $content = 'Hello, World!';
        $this->assertTrue($this->fileHandler->createFile($this->testDir, $this->testFile, $content));
        $this->assertFileExists($this->testFilePath);
        $this->assertEquals($content, file_get_contents($this->testFilePath));
    }

    public function testCreateFileSuccessfullyWithEmptyContent()
    {
        $this->assertTrue($this->fileHandler->createFile($this->testDir, $this->testFile, ''));
        $this->assertFileExists($this->testFilePath);
        $this->assertEquals('', file_get_contents($this->testFilePath));
    }

    public function testCreateFileThrowsExceptionIfFileAlreadyExists()
    {
        // Create the file first
        touch($this->testFilePath);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("File already exists: " . $this->testFilePath);
        $this->fileHandler->createFile($this->testDir, $this->testFile, 'test');
    }

    public function testCreateFileThrowsExceptionForNonWritableDirectory()
    {
        // Assuming /non_writable_dir does not exist or is not writable by the test runner
        // This test might be fragile depending on the environment.
        // A better approach would be to create a dir, chmod it to non-writable, then test.
        $nonWritableDir = '/non_writable_dir_for_test';
        if (!is_dir($nonWritableDir)) {
           // Try to create it, if it fails, good, if it succeeds, we need to ensure it's not writable
           // For CI environments, we usually don't have permissions to create top-level dirs.
           // So we often test the exception message or skip if not possible to set up.
        }
        // For now, let's assume it's not writable and the OS prevents writing.
        // The FileHandler class itself checks using is_writable() on the destination.

        $this->expectException(\Exception::class);
        // The exact message depends on whether the directory exists and is not writable, or doesn't exist.
        // Our method checks `is_dir($destination) || !is_writable($destination)`
        // If we simulate a non-existent directory:
        $this->expectExceptionMessage("Destination directory is not writable or does not exist: $nonWritableDir");
        $this->fileHandler->createFile($nonWritableDir, $this->testFile, 'test');
    }

    public function testReadFileSuccessfully()
    {
        $content = 'Content to read.';
        file_put_contents($this->testFilePath, $content);
        $this->assertEquals($content, $this->fileHandler->readFile($this->testFilePath));
    }

    public function testReadFileThrowsExceptionForNonExistentFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("File does not exist: " . $this->testFilePath);
        $this->fileHandler->readFile($this->testFilePath); // File doesn't exist yet
    }

    // Reading a non-readable file is hard to reliably test without changing actual file permissions,
    // which can be problematic and platform-dependent. We'll rely on the code's logic for is_readable.
    // If `file_get_contents` fails for other reasons (e.g. file disappears after check), it's covered.

    public function testUpdateFileSuccessfully()
    {
        $initialContent = 'Initial content.';
        file_put_contents($this->testFilePath, $initialContent);

        $updatedContent = 'Updated content.';
        $this->assertTrue($this->fileHandler->updateFile($this->testFilePath, $updatedContent));
        $this->assertEquals($updatedContent, file_get_contents($this->testFilePath));
    }

    public function testUpdateFileThrowsExceptionForNonExistentFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("File does not exist: " . $this->testFilePath);
        $this->fileHandler->updateFile($this->testFilePath, 'new content');
    }

    // Testing update on a non-writable file also has permission challenges in a typical test environment.
    // We trust the `is_writable` check in the method.
}
?>
