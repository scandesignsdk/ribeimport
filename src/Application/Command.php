<?php
namespace Ribe\Import\Application;

use Ribe\Import\Command\CreateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class Command extends Application
{

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'ribe:import';
    }
    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new CreateCommand();
        return $defaultCommands;
    }
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        return $inputDefinition;
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help',    '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version.'),
            new InputOption('--ansi',    null, InputOption::VALUE_NONE, 'Force ANSI output.'),
            new InputOption('--no-ansi', null, InputOption::VALUE_NONE, 'Disable ANSI output.'),
        ));
    }
}