# Dependency Injection
Dependency Injection and container

## Usage
Let's say we have a `Book` class, and we need to call `getName` method.
Whatever the method is static or not.
```php
class Book
{
    private string $name = 'First Book';

    public function getName(): string
    {
        return $this->name;
    }
}

$resolver = new Just\DI\Resolver;
$resolver->resolve($resolver->prepare([Book::class, 'getName']));
//Or 
$resolver->resolve([new Book, 'getName']);
// the both returns 'First Book'
```
Maybe you call Book without `new` instance you should use `prepare` method like the first one.

### Call method with parameter
Now we have new method with one parameter
```php
class Book
{
    private string $name = 'First Book';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

$container = Just\DI\Container::instance();
$container->setVar('name', 'PHP');

$book = new Book();
$resolver = new Just\DI\Resolver; 
$resolver->resolve([$book, 'setName']);
$book->getName();
// will return 'PHP'
```
We call th `setName` without the parameter, but we set `name` in our container above as a global variable.
The resolver will search in the container and if got a variable match the same name of the parameter then pass it, if not will pass null.


#### Object type parameter & singleton
```php
$function = function(Book $book){
    return $book->getName();
};

$container = Container::instance();
$resolver = new Resolver;
$name = $resolver->resolve($function);
//here $name returns 'First Book' because it's initial value


$book = new Book();
$book->setName('Test Book');
// for define a singleton object
$container->set(Book::class, $book);

$resolver = new Resolver;
$name = $resolver->resolve($function);
$this->assertSame('Test Book', $name);
//$name now is equal 'Test Book'
```

### Call a method in a class has constructor, and the constructor needs two parameters
```php
class User{
    private string $name;
    private string $email;
    
    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
}

$container = Just\DI\Container::instance();
$container->setVar('name', 'Mahmoud Elnezamy');
$container->setVar('email', 'mahmoud@nezamy.com');

$resolver = new Just\DI\Resolver;
$name = $resolver->resolve($resolver->prepare([User::class, 'getName']));
// name here will return 'Mahmoud Elnezamy'
```

### Magic Call
```php
class User{
    private string $name;
    private string $email;
    
    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}
class Book
{
    public function Auther(User $user){
        return $user->getName();
    }
}

$container = Just\DI\Container::instance();
$container->setVar('user', ['Mahmoud', 'email@domain.com']);
$container->setMagicCall(User::class, function ($attr, $value){
    return new User(...$value);
});

$resolver = new Just\DI\Resolver;
$book = $resolver->resolve(
    $resolver->prepare([Book::class, 'Auther'])
);
//$book will return 'Mahmoud'
```


## API
```php
$container = \Just\DI\Container::instance();

$container->setVar('name', 'value');
$container->getVar('name');
$container->hasVar('name');
$container->importVars([
    'name' => 'name here',
    'id' => '1'
]);

$container->set('className', new stdClass());

//Maybe the new instance do some processing or load some configurations or connect with database.
//and you won't to make the instance until the first use or call
$container->set('className', function (){
    return new stdClass();
});
$container->get('className');
$container->has('className');
// define some singleton objects
$container->import([
    Request::class => new Request(...),
    Response::class => new Response(...),
    DB::class => new DB('user', 'pass',...)
]);

$container->setMagicCall('UserModel', function ($attr, $value){
    if($attr == 'id'){
       return new UserModel($value);
    }
    return null;
});
$container->getMagicCall('UserModel');
$container->hasMagicCall('UserModel');

```