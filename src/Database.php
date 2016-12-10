<?php
namespace waylaidwanderer\RotatingProxyManager;


class Database
{
    private $db;

    public function __construct($dbFileName, $newInstance)
    {
        $this->db = $this->initializeDatabase($dbFileName, $newInstance);
    }

    private function initializeDatabase($dbFileName, $newInstance)
    {
        $fileExists = file_exists($dbFileName);
        if ($newInstance && $fileExists) {
            unlink($dbFileName);
        }
        $db = new \PDO('sqlite:'.$dbFileName);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        if ($newInstance || !$fileExists) {
            $db->exec('CREATE TABLE `proxies` (
                         `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                         `proxy` text NOT NULL,
                         `min_wait` INTEGER NOT NULL DEFAULT \'0\',
                         `max_wait` INTEGER NOT NULL DEFAULT \'0\',
                         `num_uses` INTEGER NOT NULL DEFAULT \'0\',
                         `created_at` INTEGER NOT NULL DEFAULT \'0\',
                         `updated_at` INTEGER NOT NULL DEFAULT \'0\'
                      )');
        }
        return $db;
    }

    public function addProxy($proxy, $minWait, $maxWait)
    {
        $result = $this->db->query("SELECT COUNT(1) as count FROM proxies WHERE proxy = '$proxy';");
        if ($result->fetchAll()[0]['count'] == 0) {
            $timestamp = time();
            $this->db->exec("INSERT INTO proxies (proxy, min_wait, max_wait, created_at, updated_at) VALUES ('$proxy', $minWait, $maxWait, $timestamp, $timestamp);");
        }
    }

    public function getNextProxy()
    {
        $results = $this->db->query('SELECT * FROM proxies ORDER BY updated_at ASC, num_uses ASC LIMIT 1;')->fetchAll();
        return $results[0];
    }

    public function getProxies()
    {
        return $this->db->query('SELECT * FROM proxies ORDER BY updated_at ASC, num_uses ASC;');
    }

    public function incrementProxy($id)
    {
        $timestamp = time();
        $this->db->exec("UPDATE proxies SET updated_at = $timestamp, num_uses = num_uses + 1 WHERE id = $id;");
    }
}
