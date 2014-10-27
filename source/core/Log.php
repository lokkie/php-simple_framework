<?php
/**
 * User: A.Rusakevich
 * Date: 02.09.13
 * Time: 16:15
 */

namespace core;

use core\data\StorageOrganiser;

/**
 * Logger
 *
 * Allows to report en error into log files anywhere in code
 * Provides alternative var_dump, with one setting disabling and providing call point
 * @package core
 */
class Log
{
    const DEBUG_NAME = "debug.log";
    const ERROR_NAME = "errors.log";
    /**
     * @var string|null
     */
    private static $logPath = null;
    /**
     * @var string
     */
    private static $logDir = 'logs';
    /**
     * @var bool
     */
    private static $debugMode = true;


    /**
     * Report a debug message into log
     *
     * Appends provide data into debug log. Includes calling file and line in it
     * @param string $tag
     * @param mixed $data
     */
    public static function d($tag, $data)
    {
        self::_init();
        self::writeLog(self::$logPath . DIRECTORY_SEPARATOR . self::DEBUG_NAME, $tag, $data, self::findCallPosition());
    }

    /**
     * Report an error into log
     *
     * Appends provided data into error log. Includes calling file and line in it
     * @param string $tag
     * @param mixed $data
     */
    public static function e($tag, $data)
    {
        self::_init();
        self::writeLog(self::$logPath . DIRECTORY_SEPARATOR . self::ERROR_NAME, $tag, $data, self::findCallPosition());
    }
    
    /**
     * Returns plaace in code, where Log was called
     * @return array
     **/
    private static function findCallPosition() 
    {
        return debug_backtrace(0, 2)[1];
    }
    
    /**
     * Writes to loag file provided data
     * @param string $fileName
     * @param string $tag
     * @param mixed $data
     * @param array $backtrace
     **/
    private static function writeLog($fileName, $tag, $data, $backtrace)
    {
        StorageOrganiser::createPath($fileName);
        file_put_contents($fileName, sprintf("%s [%s]: %s (in %s:%d)\n", date("Y-m-d H:i:s"), $tag, $data, $backtrace['file'], $backtrace['line']), FILE_APPEND);
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

    /**
     * Returns debug mode
     * @return boolean
     */
    public static function getDebugMode()
    {
        return self::$debugMode;
    }

    /**
     * Changes debug mode
     * @param boolean $debugMode
     */
    public static function setDebugMode($debugMode)
    {
        self::$debugMode = $debugMode;
    }

    /**
     * Equivalent of var_dump()
     *
     * Adds caller file and caller line information to classical var_dump
     * If Log::debugMode if false does nothing
     * @param mixed $params ,$params...
     */
    public function dump($params)
    {
        if (self::$debugMode) {
            $backtrace = debug_backtrace(0, 2);
            $params = func_get_args();
            $params[] = sprintf('in %s:%d', $backtrace[1]['file'], $backtrace[1]['line']);
            call_user_func_array('var_dump', $params);
        }
    }
}