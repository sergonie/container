<?php declare(strict_types=1);

namespace Sergonie\Container;

use Psr\Container\ContainerInterface;
use Closure;

abstract class ServiceFactory
{
    protected string $name;
    protected string $context;

    /** @var string[] */
    protected array $bindings;
    protected ?Closure $resolver;

    public function __construct(string $serviceName, Closure $resolver = null)
    {
        $this->name = $serviceName;
        $this->resolver = $resolver;
        $this->bindings = [];
        $this->context = '';
    }

    public function for(string $context): ServiceFactory
    {
        $this->context = $context;

        return $this;
    }

    abstract public function __invoke(ContainerInterface $container, string $serviceName);

    public function getContext(): string
    {
        return $this->context;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function argument(string $name, string $config): ServiceFactory
    {
        $this->bindings[$name] = $config;

        return $this;
    }
}
