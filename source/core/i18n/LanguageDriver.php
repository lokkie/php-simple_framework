<?php
/**
 * User: A.Rusakevich
 * Date: 10.02.14
 * Time: 21:18
 */

namespace core\i18n;

use core\data\cache\MemcacheDriver;
use core\System;

/**
 * Class LanguageDriver
 * @package core\i18n
 */
class LanguageDriver
{

    /**
     * @var LanguageBinaryFile
     */
    protected $_fileSource;

    /**
     * @var System
     */
    protected $_system;

    /**
     * @var string
     */
    protected $_lang;

    /**
     * @param System $system
     * @param string $lang
     */
    function __construct(System $system, $lang)
    {
        $this->_system = $system;
        $this->_lang = $lang;
        $this->_fileSource = new LanguageBinaryFile($system->cfg()->getPath('Path::Languages'), $lang);
    }

    /**
     * @param string $hash
     * @return null|string
     */
    public function get($hash)
    {
        $data = $this->_system->memcache()->get('i18n_' . $this->_lang . '_' . $hash);
        if ($this->_system->memcache()->getResultCode() === MemcacheDriver::RES_NOTFOUND) {
            $data = $this->_fileSource->get($hash);
            if ($data === null) {
                $data = $this->_system->db()->selectCell('SELECT `value` FROM `i18n_data` WHERE `lang` = ? AND `hash` = ?',
                    $this->_lang, $hash);
                if ($data !== null) {
                    $this->_fileSource->set($hash, $data);
                }
            }
            if ($data !== null) {
                $this->_system->memcache()->set('i18n_' . $this->_lang . '_' . $hash, $data);
            }
        }
        return $data;
    }

    /**.
     * @param string $hash
     * @param string $value
     */
    public function set($hash, $value)
    {
        $this->_system->db()->query('INSERT INTO `i18n_data` (`hash`, `lang`, `value`)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            $hash, $this->_lang, $value);
        $this->_fileSource->set($hash, $value);
        $this->_system->memcache()->set('i18n_' . $this->_lang . '_' . $hash, $value);
    }

    /**
     * @param string $hash
     * @return LanguageDriver
     */
    public function remove($hash)
    {
        $this->_system->memcache()->delete('i18n_' . $this->_lang . '_' . $hash);
        $this->_fileSource->remove($hash);
        $this->_system->db()->query('DELETE FROM `i18n_data` WHERE `hash` = ? AND `lang` = ?',
            $hash, $this->_lang);
        return $this;
    }

    /**
     * @param string $hash
     * @param string $value
     * @param null|string $oldHash
     * @return LanguageDriver
     */
    public function change($hash, $value, $oldHash = null)
    {
        if ($oldHash !== null) {
            $this->remove($oldHash);
        }
        $this->set($hash, $value);
        return $this;
    }

    /**
     * Switches hash for some value
     * @param string $oldHash
     * @param string $newHash
     * @return LanguageDriver
     */
    public function switchHash($oldHash, $newHash)
    {
        $this->change($newHash, $this->get($oldHash), $oldHash);
        return $this;
    }
} 