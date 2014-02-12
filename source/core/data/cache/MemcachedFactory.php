<?php
namespace core\data\cache;

use core\data\ConfigIni;

/**
 * Factory to work with Memcached
 * @author Lokkie (A.Rusakevich)
 */
class MemcachedFactory
{
    /**
     * @var MemcacheDriver
     */
    protected static $_instance = FALSE;
    /**
     * @var ConfigIni
     */
    protected static $_cfg = NULL;

    /**
     * Initialises factory; needs config
     * @param ConfigIni $cfg
     */
    public static function init($cfg)
    {
        self::$_cfg = $cfg;
    }

    /**
     * Gets instance of Memcached Driver
     * @return MemcacheDriver
     */
    public static function gi()
    {
        return self::getInstance();
    }

    /**
     * Gets instance of Memcached Driver
     * @return MemcacheDriver
     */
    public static function _()
    {
        return self::getInstance();
    }

    /**
     * Gets instance of Memcached Driver
     * @return MemcacheDriver
     */
    public static function getInstance()
    {
        if (self::$_instance === FALSE)
            self::_createInstance();
        return self::$_instance;
    }

    /**
     * Creates instance of MemcacheDriver
     */
    protected static function _createInstance()
    {
        self::$_instance = new MemcacheDriver();
        $serversCount = self::$_cfg->{'Memcached::Servers'};
        $defaultPort = self::$_cfg->{'Memcached::DefaultPort'};
        if ($serversCount == 1) {
            list($server, $port) = @explode(':', self::$_cfg->{'Memcached::host'});
            if ($port == NULL)
                $port = $defaultPort;
            self::$_instance->addServer($server, $port);
        } else {
            for ($iterator = 0; $iterator < $serversCount; $iterator++) {
                $data = @explode(':', self::$_cfg->{'Memcached::HostN' . $iterator});
                $server = $data[0];
                $port = @$data[1];
                if ($port == NULL)
                    $port = $defaultPort;
                self::$_instance->addServer($server, $port);
            }
        }
    }
}