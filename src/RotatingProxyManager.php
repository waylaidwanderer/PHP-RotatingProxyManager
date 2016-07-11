<?php
namespace waylaidwanderer\RotatingProxyManager;


class RotatingProxyManager
{
    /**
     * @var RotatingProxy[] $proxyArray
     */
    private $proxyArray;
    private $database;

    /**
     * RotatingProxyManager constructor.
     * @param RotatingProxy[] $proxyArray | An array of RotatingProxy instances you wish to use.
     * @param $sqliteDbLocation | The location you want the SQLite database file to be stored.
     * @param bool $newInstance
     */
    public function __construct($proxyArray, $sqliteDbLocation, $newInstance = false)
    {
        $this->proxyArray = $proxyArray;
        $this->database = new Database($sqliteDbLocation.DIRECTORY_SEPARATOR.'sqlite.db', $newInstance);
        foreach ($proxyArray as $proxy) {
            $this->database->addProxy($proxy->toString(), $proxy->getWaitMin(), $proxy->getWaitMax());
        }
    }

    /**
     * Get the next proxy in the list, waiting the required amount of seconds before returning.
     * @param bool $skipWait | Set to true to skip waiting before returning.
     * @return RotatingProxy
     * @throws ProxyManagerNoMoreProxiesException
     */
    public function getNextProxy($skipWait = false)
    {
        if (count($this->proxyArray) == 0) {
            throw new ProxyManagerNoMoreProxiesException;
        }
        $dbProxy = $this->database->getNextProxy();
        $proxy = new RotatingProxy($dbProxy['proxy'], $dbProxy['min_wait'], $dbProxy['max_wait'], $dbProxy['updated_at']);
        $secondsToWait = $proxy->getSecondsToWait();
        if (!$skipWait && $secondsToWait > 0) {
            sleep($secondsToWait);
        }
        $this->database->incrementProxy($dbProxy['id']);
        return $proxy;
    }

    /**
     * Get a random proxy from the list, without affecting the order, waiting the required amount of seconds before returning.
     * @param bool $skipWait | Set to true to skip waiting before returning.
     * @return RotatingProxy
     * @throws ProxyManagerNoMoreProxiesException
     */
    public function getRandomProxy($skipWait = false)
    {
        if (count($this->proxyArray) == 0) {
            throw new ProxyManagerNoMoreProxiesException;
        }
        $proxies = $this->database->getProxies();
        $dbProxy = $proxies[mt_rand(0, count($this->proxyArray) - 1)];
        $proxy = new RotatingProxy($dbProxy['proxy'], $dbProxy['min_wait'], $dbProxy['max_wait'], $dbProxy['updated_at']);
        $secondsToWait = $proxy->getSecondsToWait();
        if (!$skipWait && $secondsToWait > 0) {
            sleep($secondsToWait);
        }
        $this->database->incrementProxy($dbProxy['id']);
        return $proxy;
    }

    public function getProxies()
    {
        $proxies = [];
        $dbProxies = $this->database->getProxies();
        foreach ($dbProxies as $dbProxy) {
            $proxies[] = new RotatingProxy($dbProxy['proxy'], $dbProxy['min_wait'], $dbProxy['max_wait'], $dbProxy['updated_at']);
        }
        return $proxies;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
