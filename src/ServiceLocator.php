<?php declare(strict_types=1);

namespace Sergonie\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Sergonie\Container\Exception\ServiceLocatorException;
use Sergonie\Container\ServiceFactory\FactoryServiceFactory;
use Sergonie\Container\ServiceFactory\SharedServiceFactory;

/** @todo: get rid of contexts */
class ServiceLocator implements ContainerInterface
{
    /** @var array<string, ServiceFactory[]> */
    private array $services = [];

    /**
     * @inheritDoc
     */
    public function get(string $id, string $context = '')
    {
        if (!isset($this->services[$id])) {
            throw ServiceLocatorException::serviceNotFoundException($id);
        }

        if ($context !== '') {
            foreach ($this->services[$id] as $service) {
                if ($service->getContext() === $context) {
                    return $service;
                }
            }
        }

        /** @var ServiceFactory|mixed $result */
        $result = $this->services[$id][0];

        //@todo: in my opinion this check is unnecessary here
        return $result instanceof ServiceFactory
            ? $result($this, $id)
            : $result;
    }

    public function factory(string $id, Closure $definition = null): ServiceFactory
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = [];
        }

        return $this->services[$id][] = new FactoryServiceFactory($id, $definition);
    }

    public function share(string $id, Closure $definition = null): ServiceFactory
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = [];
        }

        return $this->services[$id][] = new SharedServiceFactory($id, $definition);
    }

    public function set(string $id, $service): void
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = [];
        }

        $this->services[$id][] = $service;
    }

    /**
     * @param  string  $id
     * @param  string  $context
     *
     * @return bool
     */
    public function has(string $id, string $context = ''): bool
    {
        if ($context === '') {
            return !empty($this->services[$id]);
        }

        if (empty($this->services[$id])) {
            return false;
        }

        /** @var ServiceFactory $service */
        foreach ($this->services[$id] as $service) {
            if ($service->getContext() === $context) {
                return true;
            }
        }

        return false;
    }
}
