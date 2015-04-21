#Disq

*Disq* is a very small library **for PHP 5.4+** that makes it easier to traverse and manipulate the filesystem.  It provides a simple, fluent interface that should feel familiar to users of *jQuery*.

##Examples

Also see [`tests/examples.php`](tests/examples.php).

###List all files in the current directory

```php
Disq(__DIR__ . '/*.*')->each(function () {
    //The Disq class (partially) decorates `SplFileInfo`, which is where `getRealPath()` comes from.
    print $this->getRealPath() . "\n";
});
```

```php
Disq('*.*', __DIR__)->each(function () {
    print $this->getRealPath() . "\n";
});
```

```php
Disq('*', __DIR__)->each(function () {
    if ($this->isFile()) {
        print $this->getRealPath() . "\n";
    }
});
```

```php
Disq('*.*', __DIR__)->each(function () {
    //`getInfo()` returns a `SplFileInfo` object for the current matched path.
    print $this->getInfo()->getRealPath() . "\n";
});
```

##Installation

Install using [Composer](https://getcomposer.org/).

```sh
composer require danbettles/disq:dev-master
```

##TODO

- Plugins.