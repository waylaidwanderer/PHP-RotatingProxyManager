<?php
namespace waylaidwanderer\RotatingProxyManager;


class RotatingProxy
{
    private $ip;
    private $port;
    private $username;
    private $password;
    private $waitIntervalMin = 0;
    private $waitIntervalMax = 0;
    private $lastUseTimestamp = 0;

    public function __construct($proxy)
    {
        $split = explode('@', $proxy);
        if (count($split) == 2) {
            $this->parseAuthString($split[0]);
            $this->parseProxyString($split[1]);
        } else if (count($split) == 1) {
            $this->parseProxyString($split[0]);
        } else {
            throw new RotatingProxyMalformedException;
        }
    }

    public function parseAuthString($authString)
    {
        $authSplit = explode(':', $authString);
        if (count($authSplit) == 2) {
            $this->username = $authSplit[0];
            $this->password = $authSplit[1];
        } else {
            throw new RotatingProxyMalformedException;
        }
    }

    public function parseProxyString($proxyString)
    {
        $proxySplit = explode(':', $proxyString);
        if (count($proxySplit) == 2) {
            $this->ip = $proxySplit[0];
            $this->port = $proxySplit[1];
        } else {
            throw new RotatingProxyMalformedException;
        }
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function toString()
    {
        $output = "";
        if ($this->username) {
            $output .= $this->username;
            if ($this->password) {
                $output .= ":" . $this->password;
            }
            $output .= "@";
        }
        $output .= $this->ip . ":" . $this->port;
        return $output;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function setWaitInterval($minSeconds, $maxSeconds = 0)
    {
        if ($maxSeconds > $minSeconds) {
            $this->waitIntervalMax = $maxSeconds;
        }
        $this->waitIntervalMin = $minSeconds;
    }

    public function getWaitInterval()
    {
        if ($this->waitIntervalMax > $this->waitIntervalMin) {
            return mt_rand($this->waitIntervalMin, $this->waitIntervalMax);
        }
        return $this->waitIntervalMin;
    }

    public function updateTimestamp()
    {
        $this->lastUseTimestamp = time();
    }

    public function getSecondsSinceLastUse()
    {
        return time() - $this->lastUseTimestamp;
    }

    public function getSecondsToWait()
    {
        $secondsToWait = $this->getWaitInterval() - $this->getSecondsSinceLastUse();
        return $secondsToWait < 0 ? 0 : $secondsToWait;
    }
}
