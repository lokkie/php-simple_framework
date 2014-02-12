<?php
/**
 * User: A.Rusakevich
 * Date: 11.02.14
 * Time: 10:19
 */

namespace core\data\cache;

class Memcached
{
    const RES_NOTFOUND = 1;

    function __construct()
    {
    }

    public function get()
    {
        return null;
    }

    public function set()
    {
        return null;
    }

    public function getResultCode()
    {
        return self::RES_NOTFOUND;
    }
} 