<?php declare(strict_types=1);

namespace Sergonie\Container\ServiceFactory;

use Psr\Container\ContainerInterface;
use Sergonie\Container\DependencyResolver;
use Sergonie\Container\ServiceFactory;

class FactoryServiceFactory extends ServiceFactory
{
    public function __invoke(ContainerInterface $container, string $serviceName)
    {
        if ($this->resolver) {
            return ($this->resolver)($container, $serviceName);
        }
        $resolver = new DependencyResolver($container);

        return $resolver->resolve($this->getName(), $this->bindings);
    }
}
