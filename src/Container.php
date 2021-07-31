<?php declare(strict_types=1);
/**
 * This file is part of Just.
 *
 * @license  https://github.com/just-framework/php/blob/master/LICENSE MIT License
 * @link     https://justframework.com/php/
 * @author   Mahmoud Elnezamy <mahmoud@nezamy.com>
 * @package  Just
 */
namespace Just\DI;

class Container
{
    private static ?self $instance = null;

    private array $singletons = [];

    private array $callable_map = [];

    private array $vars = [];

    private array $magicCall = [];

    public static function instance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function setCallable(string $class, callable $callback): void
    {
        $this->callable_map[$class] = $callback;
    }

    public function importCallable(array $classes): void
    {
        $this->callable_map = array_merge($this->callable_map, $classes);
    }

    public function setMagicCall(string $class, callable $callback): void
    {
        $this->magicCall[$class] = $callback;
    }

    public function hasMagicCall(string $class): bool
    {
        return isset($this->magicCall[$class]);
    }

    public function getMagicCall(string $class)
    {
        return $this->magicCall[$class] ?? false;
    }

    public function set(string $class, $instance): void
    {
        if (is_callable($instance)) {
            $this->setCallable($class, $instance);
        } else {
            $this->singletons[$class] = $instance;
        }
    }

    public function import(array $classes): void
    {
        $this->singletons = array_merge($this->singletons, $classes);
    }

    public function has(string $class): bool
    {
        return isset($this->singletons[$class]) || isset($this->callable_map[$class]);
    }

    public function get(string $class)
    {
        return $this->singletons[$class] ?? $this->makeSingleton($class);
    }

    public function setVar(string $name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function importVars(array $vars)
    {
        $this->vars = array_merge($this->vars, $vars);
    }

    public function hasVar($name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    public function getVar(string $name)
    {
        return $this->vars[$name] ?? '';
    }

    private function makeSingleton($class)
    {
        if (isset($this->callable_map[$class])) {
            return $this->singletons[$class] = $this->callable_map[$class]();
        }
        throw new \LogicException("[{$class}] is not registered in Container ");
    }
    
    public function remove(...$keys){
        foreach($keys as $key) {
            if(isset($this->singletons[$key])) unset($this->singletons[$key]);
        }
        $this->vars = [];
    }

    public function reset()
    {
        $this->singletons = [];
        $this->callable_map = [];
        $this->vars = [];
        $this->magicCall = [];
    }
}
