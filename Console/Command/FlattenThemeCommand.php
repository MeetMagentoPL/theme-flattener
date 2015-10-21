<?php


namespace MeetMagentoPL\Falkowskifier\Console\Command;

use MeetMagentoPL\Falkowskifier\Exception\FlattenThemeException;
use MeetMagentoPL\Falkowskifier\FlattensThemes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FlattenThemeCommand extends Command
{
    /**
     * @var FlattensThemes
     */
    private $flattensThemes;

    public function __construct(FlattensThemes $flattensThemes)
    {
        parent::__construct();
        $this->flattensThemes = $flattensThemes;
    }


    protected function configure()
    {
        $this->setName('dev:theme:flatten');
        $this->setDescription('Flatten less files from theme into a directory');
        $this->addArgument('theme', InputArgument::REQUIRED, 'The theme to flatten in [Vendor]_[theme] notation');
        $this->addOption(
            'dest',
            'd',
            InputOption::VALUE_REQUIRED,
            'The target directory to contain the flattened theme'
        );
        $this->addOption(
            'area',
            'a', 
            InputOption::VALUE_REQUIRED,
            'Source theme area: frontend (default) or adminhtml',
            'frontend'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            list($area, $theme, $destinationDir) = $this->getInputArguments($input);
            
            $destinationDir ?
                $this->flattensThemes->flatten($area, $theme, $destinationDir) :
                $this->flattensThemes->flattenToDefaultDestination($area, $theme);
            
            $this->displayConfirmationMessage($output, $area, $theme, $destinationDir);
        } catch (FlattenThemeException $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
        }
    }

    /**
     * @param InputInterface $input
     * @return string[]
     */
    private function getInputArguments(InputInterface $input)
    {
        $area = $input->getOption('area');
        $theme = $input->getArgument('theme');
        $destinationDir = $input->getOption('dest');
        return [$area, $theme, $destinationDir];
    }

    /**
     * @param OutputInterface $output
     * @param string $area
     * @param string $theme
     * @param string $destinationDir
     */
    private function displayConfirmationMessage(OutputInterface $output, $area, $theme, $destinationDir)
    {
        $message = sprintf('%s theme %s flattened into directory %s', ucfirst($area), $theme, $destinationDir);
        $output->writeln('<comment>' . $message . '</comment>');
    }
}
