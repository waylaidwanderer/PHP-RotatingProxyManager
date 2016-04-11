<?php
namespace waylaidwanderer\RotatingProxyManager;


class RotatingProxyManager
{
    /** @var RotatingProxy[] $proxyArray */
    private $proxyArray;

    public function __construct(array $proxyArray)
    {
        $this->proxyArray = $proxyArray;
    }

    /**
     * Get the next proxy in the list, waiting the required amount of seconds before returning.
     * @param bool $skipWait Set to true to skip waiting before returning.
     * @return RotatingProxy
     * @throws ProxyManagerNoMoreProxiesException
     */
    public function getNextProxy($skipWait = false)
    {
        if (count($this->proxyArray) == 0) {
            throw new ProxyManagerNoMoreProxiesException;
        }
        /** @var RotatingProxy $proxy */
        $proxy = array_shift($this->proxyArray);
        $this->updateAndReturnProxy($proxy, $skipWait);
        array_push($this->proxyArray, $proxy);
        return $proxy;
    }

    /**
     * Get a random proxy from the list, without affecting the order, waiting the required amount of seconds before returning.
     * @param bool $skipWait Set to true to skip waiting before returning.
     * @return RotatingProxy
     * @throws ProxyManagerNoMoreProxiesException
     */
    public function getRandomProxy($skipWait = false)
    {
        if (count($this->proxyArray) == 0) {
            throw new ProxyManagerNoMoreProxiesException;
        }
        $proxy = $this->proxyArray[mt_rand(0, count($this->proxyArray) - 1)];
        $this->updateAndReturnProxy($proxy, $skipWait);
        return $proxy;
    }

    private function updateAndReturnProxy(RotatingProxy &$proxy, $skipWait = false)
    {
        $secondsToWait = $proxy->getSecondsToWait();
        $proxy->updateTimestamp();
        if (!$skipWait && $secondsToWait > 0) {
            sleep($secondsToWait);
        }
        return $proxy;
    }
}
