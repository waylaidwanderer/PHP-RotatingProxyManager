# PHP-RotatingProxyManager
A PHP library you can use to select proxies in a rotation. It uses SQLite to store proxy data so you can use multiple instances of `RotatingProxyManager` across multiple scripts and it will still work. Requires the SQLite extension for PHP to be installed.

# Installation

`composer require waylaidwanderer/php-rotatingproxymanager dev-master`

# Usage

    // supports proxies in the format "user:pass@ip:port" or simply "ip:port"

    $list = [];
    $proxiesFile = './proxies.txt';
    $proxies = file($proxiesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($proxies as $proxy) {
        $rotatingProxy = new RotatingProxy($proxy);
        $rotatingProxy->setWaitInterval(2);
        $list[] = $rotatingProxy;
    }
    
    $databaseFileLocation = __DIR__;
    $proxyManager = new RotatingProxyManager($list, $databaseFileLocation);
    $proxyToUse = $proxyManager->getNextProxy();
    var_dump($proxyToUse->getUsername());
    var_dump($proxyToUse->getPassword());
    var_dump($proxyToUse->getIp());
    var_dump($proxyToUse->getPort());
    var_dump($proxyToUse->toString()); // will output "user:pass@ip:port" or just "ip:port"
