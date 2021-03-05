<?php declare(strict_types=1);

namespace SergonieTest\Fixtures;

class B implements BInterface
{
    private $b;

    public function __construct($b)
    {
        $this->b = $b;
    }

    public function getB()
    {
        return $this->b;
    }
}
