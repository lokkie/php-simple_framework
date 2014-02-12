<?php
/**
 * User: A.Rusakevich
 * Date: 14.11.13
 * Time: 12:47
 */

namespace core\data\cache;

use core\data\ConfigIni;

if (!class_exists('\Memcached')) {
    class MemcacheDriver extends \Memcached
    {

        function __construct(ConfigIni $config)
        {
            if ($config->{"Memcached::PersistenId"} == NULL)
                parent::__construct();
            else
                parent::__construct($config->{"Memcached::PersistenId"});
            $this->addServer($config->{"Memcached::server"}, $config->{"Memcached::port"});
        }
    }
} else {
    class MemcacheDriver extends Memcached
    {
    }
}

