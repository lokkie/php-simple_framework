<?php
/**
 * User: A.Rusakevich
 * Date: 02.09.13
 * Time: 16:15
 */

namespace core;

//use core\data\StorageOrganiser;

class Log
{

    private static $logPath = null;

    public static function d($tag, $data)
    {
        self::_init();
//        StorageOrganiser::createPath(self::$logPath . "/debug.log");
        file_put_contents(self::$logPath . "/debug.log", sprintf("%s [%s]: %s\n", date("Y-m-d H:i:s"), $tag, $data), FILE_APPEND);
    }

    public static function e($tag, $data)
    {
        self::_init();
//        StorageOrganiser::createPath(self::$logPath . "/errors.log");
        file_put_contents(self::$logPath . "/errors.log", sprintf("%s [%s]: %s\n", date("Y-m-d H:i:s"), $tag, $data), FILE_APPEND);
    }

    private static function _init()
    {
        if (self::$logPath === null)
            self::$logPath = ROOT_PATH . "/logs/";
    }
}