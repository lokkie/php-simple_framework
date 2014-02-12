<?php

namespace core\data\db;

use core\data\cache\MemcacheDriver;
use core\data\ConfigIni;


/**
 * Database Factory, witch can work with servers farm
 * @author Lokkie (A.Rusakevich)
 */
class DbFactory
{

    /**
     * @var bool
     */
    protected static $_initialised = false;
    /**
     * @var ConfigIni
     */
    protected static $_cfg = NULL;
    /**
     * @var \Memcached
     */
    protected static $_memcached = NULL;
    /**
     * @var DbDriver
     */
    protected static $_instance = FALSE;
    protected static $_memcache_prefix = NULL;
    protected static $_memcache_id = NULL;
    protected static $_server_down_errors = array(2003, 2005, 2006, 2007, 2009, 2010, 2011, 2017, 2018, 2026);

    /**
     * Initialises factory; need0s config
     * @param ConfigIni $_cfg
     * @param MemcacheDriver $memcached
     */
    public static function init($_cfg, &$memcached = NULL)
    {
        self::$_cfg = $_cfg;
        self::$_memcached = $memcached;
        self::$_memcache_prefix = @$_cfg->{'DatabaseMisc::MemcachePrefix'};
        if (self::$_memcache_prefix === NULL)
            self::$_memcache_prefix = '';
    }

    /**
     * Gets instance of Database Driver
     * @return DbDriver
     */
    public static function getInstance()
    {

        while (self::$_instance === FALSE) {
            self::_createConnection();

        }
        if (self::$_instance !== NULL && !self::$_initialised) {
            self::$_instance->query('SET NAMES "utf8"');
            if (
                strpos($_SERVER['REQUEST_URI'], 'ajax') !== FALSE || (
                    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            ) {
                self::$_instance->debugMode = false;
            } else {
                self::$_instance->debugMode = true;
            }
            self::$_initialised = true;
        }
        return self::$_instance;
    }

    public static function initialize($memcached = NULL)
    {
        if (self::$_cfg === NULL) {
            $cfg = new ConfigIni(MAIN_CONF);
            self::init($cfg, $memcached);
        }
    }

    /**
     * Gets instance of Database Driver
     * @return DbDriver
     */
    public static function gi()
    {
        return self::getInstance();
    }

    /**
     * Gets instance of Database Driver
     * @return DbDriver
     */
    public static function _()
    {
        return self::getInstance();
    }

    protected static function _createConnection()
    {
        if (self::$_cfg->{'Database::Servers'} > 1) {
            $serversCount = self::$_cfg->{'Database::Servers'};
            if (self::$_memcached !== NULL) {
                self::_createConnectionWithMemcacheBalance($serversCount);

            } else {
                self::_createConnectionWithRoundRobbingBalance($serversCount);

            }
        } else {
            self::$_instance = new DbDriver(self::$_cfg->{'Database::Host'}, self::$_cfg->{'Database::User'}, self::$_cfg->{'Database::Password'});
            if (self::$_instance->connect_errno) {
                throw new \Exception('DB connection error');
            }
            self::$_instance->usedb(self::$_cfg->{'Database::Dbname'});
        }
        if (self::$_instance != FALSE) {
            self::$_instance->errorReporting = self::$_cfg->{'DatabaseMisc::ErrorReporting'};
            $log = self::$_cfg->getPath('DatabaseMisc::ErrorLog');
            if ($log) {
                self::$_instance->logFile = $log;
            }
        }

    }

    protected static function _createConnectionWithRoundRobbingBalance($serversCount)
    {
        $pool = array();
        for ($iter = 0; $iter < $serversCount; $iter++) {
            $host = @self::$_cfg->{"Database::HostN{$iter}"};
            if ($host === NULL)
                continue;
            $pool[$iter] = $host;
        }
        $serverId = array_rand($pool);
        self::_connectToSelectedServer($serverId);
        if (self::$_instance->connect_errno) {
            self::$_instance = FALSE;
        } else {
            self::_useSelectedDb($serverId);
        }
    }

    protected static function _connectToSelectedServer($serverId)
    {
        $host = self::$_cfg->{"Database::HostN{$serverId}"};
        $user = self::$_cfg->{"Database::UserN{$serverId}"};
        if ($user === NULL)
            $user = self::$_cfg->{'Database::User'};
        $pass = self::$_cfg->{"Database::PasswordN{$serverId}"};
        if ($pass === NULL)
            $pass = self::$_cfg->{'Database::Password'};

        self::$_instance = @new DbDriver($host, $user, $pass);
    }

    protected static function _useSelectedDb($serverId)
    {
        $dbName = self::$_cfg->{"Database::DbnameN{$serverId}"};
        if ($dbName === NULL)
            $dbName = self::$_cfg->{'Database::Dbname'};
        self::$_instance->usedb($dbName);
    }

    protected static function _createConnectionWithMemcacheBalance($serversCount)
    {
        $pool = array();
        $servers = array();
        for ($iter = 0; $iter < $serversCount; $iter++) {
            $host = @self::$_cfg->{"Database::HostN{$iter}"};
            if ($host === NULL)
                continue;
            $id = self::$_memcache_prefix . sha1($host);
            $connections = self::$_memcached->get($id);
            if ($connections === FALSE) {
                self::$_memcached->set($id, 0);
                $connections = 0;
            } elseif ($connections == -1)
                continue;
            $pool[$id] = $connections;
            $servers[$id] = $iter;
        }
        if (count($pool) == 0)
            throw new \Exception('DB connection error');
        asort($pool);
        $id = array_shift(array_keys($pool));
        $serverId = $servers[$id];
        self::_connectToSelectedServer($serverId);
        if (self::$_instance->connect_errno) { // add code based setting server down
            self::$_memcached->set($id, -1);
            self::$_instance = FALSE;
        } else {
            self::$_memcached->increment($id);
            self::$_memcache_id = $id;
            self::_useSelectedDb($serverId);
        }
    }

    public static function freeConnection()
    {
        if (self::$_memcached !== NULL && self::$_memcache_id != NULL)
            self::$_memcached->decrement(self::$_memcache_id);
    }
}

function db_debug_func($params)
{
    if (defined('VERBOSE_MODE') && !VERBOSE_MODE)
        return;
    unset($params['args']);
    unset($params['holders']);
    /*ну а про аяксовые контроллеры которые отвечают JSON я буду думать? :))))))) */
    $text = '<script type="text/javascript">console.log("Database debug in ' . $params['file'] . ' on line ' . $params['line'] . '"); console.log(' . json_encode($params) . ');</script>';
    echo $text;
}

function smallDb($parrams)
{
    if (defined('VERBOSE_MODE') && !VERBOSE_MODE)
        return;

    //varDump($parrams);
    $paramsMy = array();
    $arg_str = '';
    if (isset($parrams['args']) and isset($parrams['args'][0])) {
        $args = $parrams['args'];
        unset ($args[0]);
        if (is_array($args) and count($args) > 0) {
            ob_start();
            var_export($args);
            $echo = ob_get_contents();
            ob_clean();
            $arg_str = $echo;
        }
    }

    $paramsMy['executionTime'] = $parrams['executionTime'];
    $paramsMy['query'] = $parrams['query'];
    $paramsMy['file_line'] = $parrams['file'] . '::' . $parrams['line'];
    $paramsMy['args_str'] = $arg_str;
    //varDump($paramsMy);
}