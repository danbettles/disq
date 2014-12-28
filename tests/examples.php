<?php
/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 * @license http://opensource.org/licenses/MIT MIT
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

//List all files in the current directory:

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

//Search for something interesting and then stop after finding it:

Disq('*', __DIR__)->each(function () {
    if (strpos($this->getBasename(), 'Disq') !== false) {
        print "Found \"Disq\".\n";
        return false;
    }

    print "Found something else.\n";
});
