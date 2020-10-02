<?php

declare(strict_types=1);
/**
 * This file is part of Just.
 *
 * @license  https://github.com/just-framework/php/blob/master/LICENSE MIT License
 * @link     https://justframework.com/php/
 * @author   Mahmoud Elnezamy <mahmoud@nezamy.com>
 * @package  Just
 */
namespace Just\DI;

use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

class Resolver
{
    private Container $container;

    public function __construct(Container $container = null)
    {
        $this->container = $container ?? Container::instance();
    }

    public function prepare(array $callable): callable
    {
        try {
            $method = new ReflectionMethod(...$callable);
            if (! $method->isStatic()) {
                $callable = [new NonStaticMethod($callable), 'getCallable'];
            }
        } catch (ReflectionException $e) {
            throw new LogicException("[{$callable}] is not callable");
        }

        return $callable;
    }

    /**
     * @throws ReflectionException
     * @return mixed
     */
    public function resolve(callable $callable)
    {
        if (! is_array($callable)) {
            return $this->function($callable);
        }

        if ($callable[0] instanceof NonStaticMethod) {
            $callable = $callable[0]->getCallable();
        }

        return $this->class($callable[0], $callable[1]);
    }

    /**
     * @param mixed $class
     * @throws ReflectionException
     * @return mixed|object
     */
    public function class($class, string $method = null)
    {
        $reflector = new ReflectionClass($class);

        if (! $reflector->isInstantiable()) {
            throw new LogicException("[{$class}] is not instantiable");
        }

        $constructor = null;
        if (! is_object($class)) {
            $constructor = $reflector->getConstructor();
        }

        $getMethod = null;

        if ($method) {
            $getMethod = $reflector->getMethod($method);
            if ($getMethod->isStatic()) {
                $constructor = null;
            }
        }

        if (is_null($constructor)) {
            if (is_null($method)) {
                return is_object($class) ? $class : new $class();
            }
            $callable = $getMethod->isStatic() ? "{$class}::{$method}" : [$class, $method];
            return call_user_func_array(
                $callable,
                $this->getDependencies($getMethod->getParameters())
            );
        }

        $dependencies = $this->getDependencies($constructor->getParameters());

        $class = $reflector->newInstanceArgs($dependencies);

        if ($getMethod) {
            return call_user_func_array(
                [$class, $method],
                $this->getDependencies($getMethod->getParameters())
            );
        }

        return $class;
    }

    /**
     * @param mixed $func
     * @throws ReflectionException
     * @return mixed
     */
    public function function($func)
    {
        $function = new ReflectionFunction($func);
        return call_user_func_array($func, $this->getDependencies($function->getParameters()));
    }

    /**
     * @throws ReflectionException
     */
    public function getDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();
            $name = $parameter->name;
            if (is_null($dependency)) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                if ($this->container->hasVar($name) && $this->container->hasMagicCall($dependency->name)) {
                    $dependencies[] = $this->container->getMagicCall($dependency->name)($name, $this->container->getVar($name));
                } elseif ($this->container->has($dependency->name)) {
                    $dependencies[] = $this->container->get($dependency->name);
                } else {
                    $dependencies[] = $this->class($dependency->name);
                }
            }
        }

        return $dependencies;
    }

    /**
     * @throws ReflectionException
     * @return mixed
     */
    public function resolveNonClass(ReflectionParameter $parameter)
    {
        if ($this->container->hasVar($parameter->name)) {
            return $this->container->getVar($parameter->name);
        }
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->hasType() && $parameter->getType()->getName() != 'object') {
            return $this->defaultValue($parameter->getType()->getName());
        }
        throw new ReflectionException("Cannot resolve the parameter {$parameter->getDeclaringClass()->name} {$parameter->getDeclaringFunction()->name}(\${$parameter->name} ...)");
    }

    /**
     * @return mixed
     */
    public function defaultValue(string $type)
    {
        switch ($type) {
            case 'array':
                return [];
            case 'int':
            case 'float':
                return -1;
            case 'bool':
                return false;
            default:
                return null;
        }
    }
}
