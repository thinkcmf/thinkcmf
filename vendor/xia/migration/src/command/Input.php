<?php


namespace think\migration\command;


use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

class Input implements InputInterface
{

    public function getFirstArgument()
    {
        // TODO: Implement getFirstArgument() method.
    }

    public function hasParameterOption($values, bool $onlyParams = false)
    {
        // TODO: Implement hasParameterOption() method.
    }

    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        // TODO: Implement getParameterOption() method.
    }

    public function bind(InputDefinition $definition)
    {
        // TODO: Implement bind() method.
    }

    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function getArguments()
    {
        // TODO: Implement getArguments() method.
    }

    public function getArgument(string $name)
    {
        // TODO: Implement getArgument() method.
    }

    public function setArgument(string $name, $value)
    {
        // TODO: Implement setArgument() method.
    }

    public function hasArgument($name)
    {
        // TODO: Implement hasArgument() method.
    }

    public function getOptions()
    {
        // TODO: Implement getOptions() method.
    }

    public function getOption(string $name)
    {
        // TODO: Implement getOption() method.
    }

    public function setOption(string $name, $value)
    {
        // TODO: Implement setOption() method.
    }

    public function hasOption(string $name)
    {
        // TODO: Implement hasOption() method.
    }

    public function isInteractive()
    {
        // TODO: Implement isInteractive() method.
    }

    public function setInteractive(bool $interactive)
    {
        // TODO: Implement setInteractive() method.
    }
}
