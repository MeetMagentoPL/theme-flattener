<?php


namespace MeetMagentoPL\ThemeFlattener\Console\Command;

use MeetMagentoPL\ThemeFlattener\Exception\FlattenThemeException;
use MeetMagentoPL\ThemeFlattener\Model\FlattensThemes;
use MeetMagentoPL\ThemeFlattener\Test\Unit\FileSystemThemeFixtureTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \MeetMagentoPL\ThemeFlattener\Console\Command\FlattenThemeCommand
 */
class FlattenThemeCommandTest extends \PHPUnit_Framework_TestCase
{
    use FileSystemThemeFixtureTrait;
    
    /**
     * @var FlattenThemeCommand
     */
    private $command;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInput;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutput;

    /**
     * @var FlattensThemes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFlattensThemes;

    /**
     * @var string
     */
    private $testDir;

    protected function setUp()
    {
        $this->testDir = sys_get_temp_dir() . '/flatten-theme-command-test';
        $this->ensureDirectoryExists($this->testDir);
        chdir($this->testDir);
        
        $this->mockFlattensThemes = $this->getMock(FlattensThemes::class, [], [], '', false);
        $this->command = new FlattenThemeCommand($this->mockFlattensThemes);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }
    
    public function testItIsAConsoleCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasTheCorrectName()
    {
        $this->assertSame('dev:theme:flatten', $this->command->getName());
    }

    public function testItHasADescription()
    {
        $this->assertNotEmpty($this->command->getDescription());
    }

    public function testItTakesAThemeName()
    {
        $argument = $this->command->getDefinition()->getArgument('theme');
        $this->assertTrue($argument->isRequired());
        $this->assertNotEmpty($argument->getDescription());
    }

    public function testItTakeADestinationOption()
    {
        $option = $this->command->getDefinition()->getOption('dest');
        $this->assertSame('d', $option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertNotEmpty($option->getDescription());
    }

    public function testItTakesAnOptionalAreaOption()
    {
        $option = $this->command->getDefinition()->getOption('area');
        $this->assertSame('a', $option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertNotEmpty($option->getDescription());
        $this->assertSame('frontend', $option->getDefault());
    }

    public function testItDelegatesToTheThemeFlattener()
    {
        $this->mockInput->method('getArgument')->with('theme')->willReturn('Test_theme');
        $this->mockInput->method('getOption')->willReturnMap([
            ['dest', 'destination-dir'],
            ['area', 'frontend']
        ]);
        $this->mockFlattensThemes->expects($this->once())->method('flatten');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysExceptionsAsErrors()
    {
        $expectedMessage = 'Dummy Message';
        $this->mockOutput->expects($this->once())->method('writeln')->with('<error>' . $expectedMessage . '</error>');

        $this->mockFlattensThemes->method('flatten')
            ->willThrowException(new FlattenThemeException($expectedMessage));
        
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAConfirmationMessage()
    {
        $this->mockInput->method('getArgument')->with('theme')->willReturn('Test_theme');
        $this->mockInput->method('getOption')->willReturnMap([
            ['dest', 'destination-dir'],
            ['area', 'frontend']
        ]);
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with('<comment>Frontend theme Test_theme flattened into directory destination-dir</comment>');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAConfirmationMessageWithTheDefaultDestinationDirectory()
    {
        $this->mockInput->method('getArgument')->with('theme')->willReturn('Test_theme');
        $this->mockInput->method('getOption')->willReturnMap([
            ['dest', null],
            ['area', 'frontend']
        ]);
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with('<comment>Frontend theme Test_theme flattened into directory xx/test-theme-flat</comment>');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItCallsTheFlattenCommandIfADestinationDirectoryIsSpecified()
    {
        $this->mockInput->method('getArgument')->with('theme')->willReturn('Test_theme');
        $this->mockInput->method('getOption')->willReturnMap([
            ['dest', 'destination-dir'],
            ['area', 'frontend']
        ]);
        $this->mockFlattensThemes->expects($this->once())->method('flatten');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItCallsTheWithDefaultMethodIfNoDestinationDirectoryIsSpecified()
    {
        $this->mockInput->method('getArgument')->with('theme')->willReturn('Test_theme');
        $this->mockInput->method('getOption')->willReturnMap([
            ['dest', null],
            ['area', 'frontend']
        ]);
        $this->mockFlattensThemes->expects($this->once())->method('flatten')
            ->with('frontend', 'Test_theme', 'xx/test-theme-flat');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItCreatesADotWithTheCommandDetails()
    {
        $this->mockInput->method('getArgument')->with('theme')->willReturn('Test_theme');
        $destinationDir = 'destination-dir';
        $this->mockInput->method('getOption')->willReturnMap([
            ['dest', $destinationDir],
            ['area', 'frontend']
        ]);
        $this->ensureDirectoryExists($destinationDir);
        $this->command->run($this->mockInput, $this->mockOutput);
        $this->assertFileExists($destinationDir . '/' . FlattenThemeCommand::DOTFILE);
        $this->assertSame(
            sprintf('bin/magento --dest="%s" --area="frontend" Test_theme', $destinationDir),
            file_get_contents($destinationDir . '/' . FlattenThemeCommand::DOTFILE)
        );
    }
}
