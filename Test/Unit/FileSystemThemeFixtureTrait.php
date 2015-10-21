<?php


namespace MeetMagentoPL\Falkowskifier\Test\Unit;

trait FileSystemThemeFixtureTrait
{
    /**
     * @param string $dir
     */
    protected function ensureDirectoryExists($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0700, true);
        }
    }

    /**
     * @param string $dir
     */
    protected function ensureDirectoryIsWritable($dir)
    {
        if (!is_writable($dir)) {
            chmod($dir, 0700);
        }
    }

    /**
     * @param \SplFileInfo|string $item
     */
    protected function ensureFilesystemItemCanBeRemoved($item)
    {
        if (!is_link($item) && !is_writable($item)) {
            chmod($item, 0700);
        }
        if (!is_writable(dirname($item))) {
            chmod(dirname($item), 0700);
        }
    }

    /**
     * @param string[] $files
     */
    protected function ensureFilesExist($files)
    {
        foreach ($files as $file) {
            $this->ensureFileExists($file);
        }
    }

    /**
     *
     * @param string $file
     */
    protected function ensureFileExists($file)
    {
        $this->ensureDirectoryExists(dirname($file));
        touch($file);
    }

    /**
     * @param string $dir
     */
    protected function removeDirectoryAndContents($dir)
    {
        $testDirectoryBranch = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($testDirectoryBranch as $item) {
            $this->ensureFilesystemItemCanBeRemoved($item);
            is_dir($item) ? rmdir($item) : unlink($item);
        }
    }
}
