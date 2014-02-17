<?php
/**
 * User: A.Rusakevich
 * Date: 02.09.13
 * Time: 16:15
 */

namespace core;

use core\data\StorageOrganiser;

class Log
{
    const DEBUG_NAME = "debug.log";
    const ERROR_NAME = "errors.log";
    private static $logPath = null;
    private static $logDir = 'logs';

    public static function d($tag, $data)
    {
        self::_init();
        StorageOrganiser::createPath(self::$logPath . DIRECTORY_SEPARATOR . self::DEBUG_NAME);
        file_put_contents(self::$logPath . DIRECTORY_SEPARATOR . self::DEBUG_NAME, sprintf("%s [%s]: %s\n", date("Y-m-d H:i:s"), $tag, $data), FILE_APPEND);
    }

    /**
     * Report an error into log
     *
     * Appends provided data into error log. Include calling file and line in it
     * @param string $tag
     * @param mixed $data
     */
    public static function e($tag, $data)
    {
        self::_init();
        $backtrace = debug_backtrace(0, 2);
        StorageOrganiser::createPath(self::$logPath . DIRECTORY_SEPARATOR . self::ERROR_NAME);
        file_put_contents(self::$logPath . DIRECTORY_SEPARATOR . self::ERROR_NAME, sprintf("%s [%s]: %s (in %s:%d)\n", date("Y-m-d H:i:s"), $tag, $data), FILE_APPEND);
    }

    /**
     * Initialises process
     *
     * Compiles work path to logs dir
     */
    private static function _init()
    {
        if (self::$logPath === null)
            self::$logPath = ROOT_PATH . DIRECTORY_SEPARATOR . self::$logDir;
    }

    /**
     * Changes log directory
     *
     * Set second parameter TRUE, if want to set absolute path to a logs directory
     * @param string $dirName
     * @param bool $absolute
     */
    public static function setLogDir($dirName, $absolute = false)
    {
        if ($absolute) {
            self::$logPath = $dirName;
        } else {
            self::$logPath = null;
            self::$logDir = $dirName;
        }
    }
}