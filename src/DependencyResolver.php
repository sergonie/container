<?php
declare(strict_types=1);

namespace Sergonie\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Sergonie\Container\DependencyResolver\Argument;
use Sergonie\Container\Exception\DependencyResolverException;

final class DependencyResolver
{
    /** @var ServiceLocator|ContainerInterface*/
    private ContainerInterface $container;

    /**
     * @var ReflectionClass[]
     */
    private static array $reflections = [];

    /**
     * DependencyResolver constructor.
     *
     * @param  ServiceLocator|ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param  string  $service
     * @param  array  $bindings
     *
     * @return object
     * @throws ReflectionException
     */
    public function __invoke(string $service, array $bindings = []): object
    {
        return $this->resolve($service, $bindings);
    }

    /**
     * @param  string  $className
     * @param  array  $bindings
     *
     * @return object
     * @throws ReflectionException
     */
    public function resolve(string $className, array $bindings = []): object
    {
        $reflection = self::reflectClass($className);

        if (!$reflection->isInstantiable()) {
            throw DependencyResolverException::forNonInstantiableClass($className);
        }

        $constructor = $reflection->getConstructor();

        $values = [];
        if (!is_null($constructor)) {
            $arguments = $this->parseArguments($reflection->getConstructor());
            $values = $this->resolveArguments($arguments, $className,
                $bindings);
        }

        return $reflection->newInstanceArgs($values);
    }

    /**
     * @param  ReflectionMethod  $function
     *
     * @return Argument[]
     * @throws ReflectionException
     */
    private function parseArguments(ReflectionMethod $function): array
    {
        $arguments = [];
        foreach ($function->getParameters() as $parameter) {
            $type = assert($parameter->getType() instanceof
                \ReflectionNamedType)
                ? $parameter->getType()->getName()
                : '';

            $arguments[] = new Argument(
                '$'.$parameter->getName(),
                $type,
                $parameter->isOptional(),
                $parameter->isOptional() ? $parameter->getDefaultValue() : null
            );
        }

        return $arguments;
    }

    /**
     * @param  Argument[]  $arguments
     * @param  string[]  $bindings
     * @param  string  $context
     *
     * @return array
     * @throws ReflectionException|\Sergonie\Container\Exception\DependencyResolverException
     */
    private function resolveArguments(
        array $arguments,
        string $context,
        array $bindings = []
    ): array {
        $values = [];
        foreach ($arguments as $argument) {
            if (isset($bindings[$argument->getName()])) {
                $values[] = $bindings[$argument->getName()];
            } else {
                if (class_exists($argument->getType())) {
                    $values[] = $this->container->has($argument->getType())
                        ? $this->container->get($argument->getType(), $context)
                        : $this->resolve($argument->getType());
                } else {
                    if ($argument->isOptional()) {
                        $values[] = $argument->getDefaultValue();
                    } else {
                        throw DependencyResolverException::forAutowireFailure($argument->getName(),
                            $context);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @param  string  $class
     *
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    private static function reflectClass(string $class): ReflectionClass
    {
        return self::$reflections[$class] ??
            (self::$reflections[$class] = new ReflectionClass($class));
    }
}
