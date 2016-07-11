<?php
namespace waylaidwanderer\RotatingProxyManager;


class RotatingProxy
{
    private $ip;
    private $port;
    private $username;
    private $password;
    private $waitMin;
    private $waitMax;
    private $lastUseTimestamp;

    public function __construct($proxy, $waitMin = 0, $waitMax = 0, $lastUseTimestamp = 0)
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
        $this->waitMin = $waitMin;
        $this->waitMax = $waitMax;
        $this->lastUseTimestamp = $lastUseTimestamp;
    }

    private function parseAuthString($authString)
    {
        $authSplit = explode(':', $authString);
        if (count($authSplit) == 2) {
            $this->username = $authSplit[0];
            $this->password = $authSplit[1];
        } else {
            throw new RotatingProxyMalformedException;
        }
    }

    private function parseProxyString($proxyString)
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

    /**
     * @return int
     */
    public function getWaitMin()
    {
        return $this->waitMin;
    }

    /**
     * @return int
     */
    public function getWaitMax()
    {
        return $this->waitMax;
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

    /**
     * Set the wait interval for the proxy.
     * @param $minSeconds
     * @param int $maxSeconds | If this is 0, then getWaitInterval() will return $minSeconds instead of a random number between $minSeconds and $maxSeconds
     */
    public function setWaitInterval($minSeconds, $maxSeconds = 0)
    {
        if ($maxSeconds > $minSeconds) {
            $this->waitMax = $maxSeconds;
        }
        $this->waitMin = $minSeconds;
    }

    /**
     * Get the wait interval for the proxy;
     * @return int
     */
    public function getWaitInterval()
    {
        if ($this->waitMax > $this->waitMin) {
            return mt_rand($this->waitMin, $this->waitMax);
        }
        return $this->waitMin;
    }

    /**
     * You should not call this yourself but rather let the RotatingProxyManager handle setting this for you.
     * @param $value
     */
    public function setLastUseTimestamp($value)
    {
        $this->lastUseTimestamp = $value;
    }

    /**
     * @return int
     */
    public function getSecondsSinceLastUse()
    {
        return time() - $this->lastUseTimestamp;
    }

    /**
     * Return the number of seconds to wait before the proxy should be used again. If negative, will return 0.
     * @return int
     */
    public function getSecondsToWait()
    {
        $secondsToWait = $this->getWaitInterval() - $this->getSecondsSinceLastUse();
        return $secondsToWait < 0 ? 0 : $secondsToWait;
    }
}
