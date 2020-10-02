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

class NonStaticMethod
{
    private array $callable;

    public function __construct(array $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @throws \ReflectionException
     */
    public function getCallable(): callable
    {
        return [
            (new Resolver())->class($this->callable[0]),
            $this->callable[1]
        ];
    }
}
