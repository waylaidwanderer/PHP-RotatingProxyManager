<?php

require '../vendor/autoload.php';

use waylaidwanderer\RotatingProxyManager\RotatingProxy;
use waylaidwanderer\RotatingProxyManager\RotatingProxyMalformedException;

class RotatingProxyTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidStringThrowsRotatingProxyMalformedException()
    {
        $this->expectException(RotatingProxyMalformedException::class);
        new RotatingProxy('test');
    }

    public function testInvalidAuthStringThrowsRotatingProxyMalformedException()
    {
        $this->expectException(RotatingProxyMalformedException::class);
        new RotatingProxy('test:test:test@127.0.0.1:80');
    }

    public function testInvalidProxyStringThrowsRotatingProxyMalformedException()
    {
        $this->expectException(RotatingProxyMalformedException::class);
        new RotatingProxy('test:test@127.0.0.1:80:2');
    }

    public function testValidProxyStringDoesNotThrowException()
    {
        $proxyString = '127.0.0.1:80';
        $proxy = new RotatingProxy($proxyString);
        $this->assertEquals($proxyString, $proxy->toString());
        $proxyString = 'username:password@127.0.0.1:80';
        $proxy = new RotatingProxy($proxyString);
        $this->assertEquals($proxyString, $proxy->toString());
    }
}
