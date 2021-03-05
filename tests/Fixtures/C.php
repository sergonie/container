<?php declare(strict_types=1);

namespace SergonieTest\Fixtures;

class C
{
    protected string $name;
    protected Bar $bar;

    public function __construct(Bar $bar, string $name = 'test')
    {
        $this->name = $name;
        $this->bar = $bar;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBar(): Bar
    {
        return $this->bar;
    }
}
