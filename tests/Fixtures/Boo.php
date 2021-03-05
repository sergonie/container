<?php declare(strict_types=1);

namespace SergonieTest\Fixtures;

class Boo
{
    public $string;
    public $a;

    public function __construct($string, A $a)
    {
        $this->string = $string;
        $this->a = $a;
    }
}
