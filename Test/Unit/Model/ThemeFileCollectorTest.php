<?php


namespace MeetMagentoPL\Falkowskifier\Model;

use Magento\Framework\Component\ComponentRegistrarInterface;
use MeetMagentoPL\Falkowskifier\Exception\UnableToLocateThemeDirectoryException;
use MeetMagentoPL\Falkowskifier\Test\Unit\FileSystemThemeFixtureTrait;

class ThemeFileCollectorTest extends \PHPUnit_Framework_TestCase
{
    use FileSystemThemeFixtureTrait;
    
    /**
     * @var ThemeFileCollector
     */
    private $themeFileCollector;

    /**
     * @var string
     */
    private $testDir;

    /**
     * @var ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockThemeRegistrar;

    protected function setUp()
    {
        $this->testDir = sys_get_temp_dir() . '/theme-file-collector-test';
        $this->ensureDirectoryExists($this->testDir);
        
        $this->mockThemeRegistrar = $this->getMock(ComponentRegistrarInterface::class);
        $this->themeFileCollector = new ThemeFileCollector($this->mockThemeRegistrar);
    }
    
    protected function tearDown()
    {
        $this->removeDirectoryAndContents($this->testDir);
    }

    public function testItImplementsTheThemeFileCollectorInterface()
    {
        $this->assertInstanceOf(ThemeFileCollectorInterface::class, $this->themeFileCollector);
    }

    public function testItThrowsAnExceptionIfTheThemeCanNotBeLocated()
    {
        $this->setExpectedException(
            UnableToLocateThemeDirectoryException::class,
            'Unable to locate the theme directory for area "frontend" and theme "Test_theme"'
        );
        $this->themeFileCollector->getCssSourceFiles('frontend', 'Test_theme');
    }

    public function testItReturnsAnEmptyArrayIfThereAreNoCssSourceFiles()
    {
        $this->mockThemeRegistrar->method('getPath')->willReturn($this->testDir);
        $this->assertSame([], $this->themeFileCollector->getCssSourceFiles('frontend', 'Test_theme'));
    }

    public function testItIncludesThemeCssSourceFilesInTheReturnedArray()
    {
        $themeCssFiles = [
            $this->testDir . '/web/css/source/_partial.less',
            $this->testDir . '/web/css/source/nonpartial.less',
            $this->testDir . '/web/css/source/foo/_another-partial.less',
        ];
        $this->ensureFilesExist($themeCssFiles);
        
        $this->mockThemeRegistrar->method('getPath')->willReturn($this->testDir);

        $result = $this->themeFileCollector->getCssSourceFiles('frontend', 'Test_theme');
        
        sort($themeCssFiles);
        sort($result);
        
        $this->assertSame($themeCssFiles, $result);
    }

    public function testItIncludesModulesSourceFilesInTheReturnedArray()
    {
        $moduleCssFiles = [
            $this->testDir . '/Vendor_Module/web/css/source/_partial.less',
            $this->testDir . '/Vendor_Module/web/css/source/nonpartial.less',
            $this->testDir . '/Vendor_Module/web/css/source/foo/_another-partial.less',
        ];
        $this->ensureFilesExist($moduleCssFiles);
        
        $this->mockThemeRegistrar->method('getPath')->willReturn($this->testDir);

        $result = $this->themeFileCollector->getCssSourceFiles('frontend', 'Test_theme');
        
        sort($moduleCssFiles);
        sort($result);
        
        $this->assertSame($moduleCssFiles, $result);
    }
}
