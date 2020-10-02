<?php
declare(strict_types=1);

namespace Just\Test;

use Just\DI\Container;
use Just\DI\Resolver;
use Just\Test\Classes\Book;
use Just\Test\Classes\User;
use PHPUnit\Framework\TestCase;

class DITest extends TestCase
{
    public function test_call_method() : void
    {
        $resolver = new Resolver;
        $this->assertSame('First Book', $resolver->resolve($resolver->prepare([Book::class,'getName'])));
    }

    public function test_call_method_with_var() : void
    {
        $container = Container::instance();
        $container->setVar('name', 'PHP');

        $book = new Book();
        $resolver = new Resolver;
        $resolver->resolve([$book, 'setName']);
        $this->assertSame('PHP', $resolver->resolve($resolver->prepare([$book,'getName'])));
    }

    public function test_call_constructor_with_vars() : void
    {
        $container = Container::instance();
        $container->setVar('name', 'Mahmoud Elnezamy');
        $container->setVar('email', 'mahmoud@nezamy.com');

        $resolver = new Resolver;
        $name = $resolver->resolve($resolver->prepare([User::class, 'getName']));
        $this->assertSame('Mahmoud Elnezamy', $name);
    }

    public function test_magic_call() : void
    {
        $container = Container::instance();
        $container->setVar('user', ['Mahmoud', 'email@domain.com']);
        $container->setMagicCall(User::class, function ($attr, $value){
            return new User(...$value);
        });

        $resolver = new Resolver;
        $book = $resolver->resolve(
            $resolver->prepare([Book::class, 'Auther'])
        );
        $this->assertSame('Mahmoud', $book);
    }

}
