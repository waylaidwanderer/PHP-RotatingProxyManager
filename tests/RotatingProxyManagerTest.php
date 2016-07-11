<?php

use waylaidwanderer\RotatingProxyManager\RotatingProxy;
use waylaidwanderer\RotatingProxyManager\RotatingProxyManager;

require '../vendor/autoload.php';


class RotatingProxyManagerTest extends PHPUnit_Framework_TestCase
{
    private $proxies = [];

    public function setUp()
    {
        parent::setUp();
        $rotatingProxy = new RotatingProxy('127.0.0.1:80');
        $this->proxies[] = $rotatingProxy;
        $rotatingProxy = new RotatingProxy('127.0.0.2:80');
        $rotatingProxy->setWaitInterval(5);
        $this->proxies[] = $rotatingProxy;
        $rotatingProxy = new RotatingProxy('127.0.0.3:81');
        $rotatingProxy->setWaitInterval(2, 4);
        $this->proxies[] = $rotatingProxy;
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink(__DIR__.DIRECTORY_SEPARATOR.'sqlite.db');
    }

    public function testNumberOfProxiesInsertedEqualsNumberOfProxiesInDatabase()
    {
        $proxyManager = new RotatingProxyManager($this->proxies, __DIR__, true);
        $this->assertEquals(3, count($proxyManager->getProxies()));
    }

    public function testGetNextProxyReturnsCorrectProxy()
    {
        $proxyManager = new RotatingProxyManager($this->proxies, __DIR__, true);
        $this->assertEquals('127.0.0.1:80', $proxyManager->getNextProxy()->toString());
        $this->assertEquals('127.0.0.2:80', $proxyManager->getNextProxy()->toString());
        $this->assertEquals('127.0.0.3:81', $proxyManager->getNextProxy()->toString());
        $this->assertEquals('127.0.0.1:80', $proxyManager->getNextProxy()->toString());
    }

    public function testGetNextProxyReturnsCorrectProxyAcrossMultipleManagers()
    {
        $proxyManager = new RotatingProxyManager($this->proxies, __DIR__, true);
        $this->assertEquals('127.0.0.1:80', $proxyManager->getNextProxy()->toString());
        $proxyManager = new RotatingProxyManager($this->proxies, __DIR__);
        $this->assertEquals('127.0.0.2:80', $proxyManager->getNextProxy()->toString());
        $proxyManager = new RotatingProxyManager($this->proxies, __DIR__);
        $this->assertEquals('127.0.0.3:81', $proxyManager->getNextProxy()->toString());
        $proxyManager = new RotatingProxyManager($this->proxies, __DIR__);
        $this->assertEquals('127.0.0.1:80', $proxyManager->getNextProxy()->toString());
    }
}
