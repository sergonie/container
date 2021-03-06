<?php declare(strict_types=1);

namespace Sergonie\Container\DependencyResolver;

final class Argument
{
    private string $name;
    private string $type;
    private bool $optional;
    private $default;

    public function __construct(string $name, string $type, bool $optional = false, $default = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->optional = $optional;
        $this->default = $default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function getDefaultValue()
    {
        return $this->default;
    }
}
