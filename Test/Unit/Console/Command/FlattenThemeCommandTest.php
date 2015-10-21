<?php


namespace MeetMagentoPL\Falkowskifier\Console\Command;

use MeetMagentoPL\Falkowskifier\Exception\FlattenThemeException;
use MeetMagentoPL\Falkowskifier\FlattensThemes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \MeetMagentoPL\Falkowskifier\Console\Command\FlattenThemeCommand
 */
class FlattenThemeCommandTest extends \PHPUnit_Framework_TestCase
{
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

    protected function setUp()
    {
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

        $this->mockFlattensThemes->method('flattenToDefaultDestination')
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
        $this->mockFlattensThemes->expects($this->once())->method('flattenToDefaultDestination');
        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
