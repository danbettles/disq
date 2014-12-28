#Disq#

*Disq* is a very small library **for PHP 5.4+** that makes it easier to traverse and manipulate the filesystem.  It 
provides a simple, fluent interface that should feel familiar to users of *jQuery*.

Let me know if you find it useful.  Feel free to contribute.

##Examples##

###List all files in the current directory###

    Disq(__DIR__ . '/*.*')->each(function () {
        //The Disq class (partially) decorates `SplFileInfo`, which is where `getRealPath()` comes from.
        print $this->getRealPath() . "\n";
    });

    Disq('*.*', __DIR__)->each(function () {
        print $this->getRealPath() . "\n";
    });

    Disq('*', __DIR__)->each(function () {
        if ($this->isFile()) {
            print $this->getRealPath() . "\n";
        }
    });

    Disq('*.*', __DIR__)->each(function () {
        //`getInfo()` returns a `SplFileInfo` object for the current matched path.
        print $this->getInfo()->getRealPath() . "\n";
    });

See `tests/examples.php` for more.

##Installation##

@todo Explain how to install using Composer.

##TODO##

- Plugins.