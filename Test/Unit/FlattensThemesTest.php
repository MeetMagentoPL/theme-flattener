<?php


namespace MeetMagentoPL\Falkowskifier;

use MeetMagentoPL\Falkowskifier\Exception\UnableToCreateDirectoryException;


/**
 * @covers \MeetMagentoPL\Falkowskifier\FlattensThemes
 */
class FlattensThemesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlattensThemes
     */
    private $flattensThemes;

    /**
     * @var string
     */
    private $testDir;

    /**
     * @var ThemeFileCollectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockThemeFileCollector;

    private function setupTestDirectory()
    {
        $this->testDir = sys_get_temp_dir() . '/flattens-themes-test';
        $this->createTestDirectory();
        $this->ensureTestDirectoryIsWritable();
        chdir($this->testDir);
    }

    private function createTestDirectory()
    {
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0700, true);
        }
    }

    private function ensureTestDirectoryIsWritable()
    {
        if (!is_writable($this->testDir)) {
            chmod($this->testDir, 0700);
        }
    }

    private function removeTestDirectory()
    {
        $testDirectoryBranch = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->testDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($testDirectoryBranch as $item) {
            $this->ensureFilesystemItemCanBeRemoved($item);
            is_dir($item) ? rmdir($item) : unlink($item);
        }
    }

    /**
     * @param \SplFileInfo $item
     */
    private function ensureFilesystemItemCanBeRemoved($item)
    {
        if (!is_link($item) && !is_writable($item)) {
            chmod($item, 0700);
        }
        if (!is_writable(dirname($item))) {
            chmod(dirname($item), 0700);
        }
    }

    /**
     * @param $themeDirectory
     * @param string[] $filesInTheme
     */
    private function createSourceThemeFilesFixture($themeDirectory, array $filesInTheme)
    {
        $this->mockThemeFileCollector->method('getThemeDirectoryPath')->willReturn($themeDirectory);

        $fullPathToFilesInTheme = array_map(function ($fileInTheme) use ($themeDirectory) {
            $file = $themeDirectory . '/' . $fileInTheme;
            $this->ensureFileExists($file);
            return $file;
        }, $filesInTheme);

        $this->mockThemeFileCollector->method('getCssSourceFiles')->willReturn($fullPathToFilesInTheme);
    }

    /**
     *
     * @param string $file
     */
    private function ensureFileExists($file)
    {
        $this->ensureDirectoryExists(dirname($file));
        touch($file);
    }

    /**
     * @param string $dir
     */
    private function ensureDirectoryExists($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0700, true);
        }
    }

    /**
     * @param string $linkPath
     * @return int
     */
    private function getLinkTargetInode($linkPath)
    {
        $this->assertFileExists($linkPath);
        $linkTarget = readlink($linkPath);
        $cwd = getcwd();
        chdir(dirname($linkPath));
        $this->assertFileExists($linkTarget);
        $linkTargetInode = fileinode($linkTarget);
        chdir($cwd);
        return $linkTargetInode;
    }

    /**
     * @param string[] $expectedFlattenedFiles
     * @param string[] $themeDirectory
     * @param string[] $sourceThemeFiles
     */
    private function assertFlattenedFiles(array $expectedFlattenedFiles, $themeDirectory, array $sourceThemeFiles)
    {
        array_map(function ($themeFile, $link) use ($themeDirectory) {
            $this->assertFileExists($themeDirectory . '/' . $themeFile);
            $themeFileInode = fileinode($themeDirectory . '/' . $themeFile);
            $linkTargetInode = $this->getLinkTargetInode($link);
            $this->assertSame($themeFileInode, $linkTargetInode);
        }, $sourceThemeFiles, $expectedFlattenedFiles);
    }

    protected function setUp()
    {
        $this->setupTestDirectory();
        $this->mockThemeFileCollector = $this->getMock(ThemeFileCollectorInterface::class);
        $this->flattensThemes = new FlattensThemes($this->mockThemeFileCollector);
    }

    protected function tearDown()
    {
        $this->removeTestDirectory();
    }

    public function testItCreatesTheDestinationDirectoryIfItDoesNotExist()
    {
        $destinationDir = 'destination-dir';
        $this->assertFileNotExists($destinationDir);
        $this->flattensThemes->flatten('frontend', 'Test_test', $destinationDir);
        $this->assertTrue(is_dir($destinationDir), 'The destination directory was not created');
    }

    public function testItThrowsAnExceptionIfTheDestinationDirectoryCanNotBeCreated()
    {
        $this->setExpectedException(
            UnableToCreateDirectoryException::class,
            'Unable to create the destination directory "destination-dir"'
        );
        chmod($this->testDir, 0555);
        $this->flattensThemes->flatten('frontend', 'Test_theme', 'destination-dir');
    }

    public function testItCreatesTheCorrectDefaultDestinationDirectory()
    {
        $expectedDestinationDirectory = 'xx/test-theme-flat';
        $this->flattensThemes->flattenToDefaultDestination('frontend', 'Test_theme');
        $this->assertTrue(is_dir($expectedDestinationDirectory), 'The default destination directory was not created');
    }

    public function testItFlattensAModuleLessFile()
    {
        $themeDirectory = 'app/design/Test/theme';
        $sourceThemeFiles = [
            'Vendor_Module/web/css/source/_partial.less',
            'Vendor_Module/web/css/source/nonpartial.less',
        ];
        $this->createSourceThemeFilesFixture($themeDirectory, $sourceThemeFiles);

        $destinationDir = 'target';
        $expectedFlattenedFiles = [
            $destinationDir . '/css/Vendor_Module_partial.less',
            $destinationDir . '/css/Vendor_Module_nonpartial.less'
        ];

        $this->flattensThemes->flatten('frontend', 'Test_theme', $destinationDir);

        $this->assertFlattenedFiles($expectedFlattenedFiles, $themeDirectory, $sourceThemeFiles);
    }

    public function testItFlattensThemeLessFile()
    {
        $themeDirectory = 'app/design/Test/theme';
        $sourceThemeFiles = [
            'web/css/source/theme.less',
            'web/css/source/_partial.less'
        ];
        $this->createSourceThemeFilesFixture($themeDirectory, $sourceThemeFiles);

        $destinationDir = 'target';
        $expectedFlattenedFiles = [
            $destinationDir . '/css/theme.less',
            $destinationDir . '/css/_partial.less'
        ];

        $this->flattensThemes->flatten('frontend', 'Test_theme', $destinationDir);

        $this->assertFlattenedFiles($expectedFlattenedFiles, $themeDirectory, $sourceThemeFiles);
    }
}
