<?php

namespace cmf\console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cli
{
    private string $name;
    private string $description;
    private array $usages;
    private array $examples;

    /**
     * @param string $description
     */
    public function __construct(string $description, array $usages = [], array $examples = [])
    {
        $this->description = $description;
        $this->usages      = $usages;
        $this->examples    = $examples;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getUsages(): array
    {
        return $this->usages;
    }

    /**
     * @return array
     */
    public function getExamples(): array
    {
        return $this->examples;
    }


}
