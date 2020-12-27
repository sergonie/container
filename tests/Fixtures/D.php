<?php

declare(strict_types=1);

namespace IgniTest\Fixtures;

class D
{
    protected C $c;
    protected int $num;
    protected ?string $string;

    public function __construct(C $c, int $num = 1, ?string $string = null)
    {
        $this->c = $c;
        $this->num = $num;
        $this->string = $string;
    }

    /**
     * @return C
     */
    public function getC(): C
    {
        return $this->c;
    }

    /**
     * @return int
     */
    public function getNum(): int
    {
        return $this->num;
    }

    /**
     * @return string|null
     */
    public function getString(): ?string
    {
        return $this->string;
    }
}
