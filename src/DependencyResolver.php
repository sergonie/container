<?php declare(strict_types=1);

namespace Igni\Container;

use Igni\Container\DependencyResolver\Argument;
use Igni\Container\Exception\DependencyResolverException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;

final class DependencyResolver
{
    private ContainerInterface $container;

    /**
     * @var ReflectionClass[]
     */
    private static array $reflections = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(string $service, array $bindings = [])
    {
        return $this->resolve($service, $bindings);
    }

    public function resolve(string $className, array $bindings = [])
    {
        $reflection = self::reflectClass($className);

        if (!$reflection->isInstantiable()) {
            throw DependencyResolverException::forNonInstantiableClass($className);
        }

        $constructor = $reflection->getConstructor();

        $values = [];
        if ($constructor) {
            $arguments = $this->parseArguments($reflection->getConstructor());
            $values = $this->resolveArguments($arguments, $className, $bindings);
        }

        return $reflection->newInstanceArgs($values);
    }

    /**
     * @param ReflectionMethod $function
     * @return Argument[]
     */
    private function parseArguments(ReflectionMethod $function): array
    {
        $arguments = [];
        foreach ($function->getParameters() as $parameter) {
            $type = $parameter->getType() ? $parameter->getType()->getName() : '';
            $arguments[] = new Argument(
                '$' . $parameter->getName(),
                $type,
                $parameter->isOptional(),
                $parameter->isOptional() ? $parameter->getDefaultValue() : null
            );
        }

        return $arguments;
    }

    /**
     * @param Argument[] $arguments
     * @param array $bindings
     * @param string $context
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function resolveArguments(array $arguments, string $context, array $bindings = []): array
    {
        $values = [];
        foreach ($arguments as $argument) {
            if (isset($bindings[$argument->getName()])) {
                $values[] = $bindings[$argument->getName()];

            } else if (class_exists($argument->getType())) {
                if ($this->container->has($argument->getType())) {
                    $values[] = $this->container->get($argument->getType(), $context);
                } else {
                    $values[] = $this->resolve($argument->getType());
                }

            } else if ($argument->isOptional()) {
                $values[] = $argument->getDefaultValue();

            } else {
                throw DependencyResolverException::forAutowireFailure($argument->getName(), $context);
            }
        }

        return $values;
    }

    private static function reflectClass(string $class): ReflectionClass
    {
        if (isset(self::$reflections[$class])) {
            return self::$reflections[$class];
        }

        return self::$reflections[$class] = new ReflectionClass($class);
    }
}
