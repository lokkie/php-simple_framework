<?php
/**
 * User: A.Rusakevich
 * Date: 10.02.14
 * Time: 14:20
 */

namespace core\i18n;

use core\System;

/**
 * i18n implementation library
 * @package core\i18n
 */
class Lang
{
    /**
     * @var LanguageDriver[]
     */
    protected $_languages = array();

    /**
     * @var System
     */
    protected $_system;
    /**
     * @var string
     */
    protected $_currentLang;
    /**
     * @var string
     */
    protected $_defaultLang;

    /**
     * @param System $system
     */
    function __construct(System $system)
    {
        $this->_system = $system;
        $this->_defaultLang = $system->cfg()->{'i18n::DefaultLanguage'};
        $this->_currentLang = $system->getCurrentLang();
        $this->_developersLang = $system->cfg()->{'i18n::DevelopersLanguage'};
    }

    /**
     * Return's string in current language
     *
     * By the way adds strings to default dictionary. Can be used to change old original strings
     *
     * @param string $text
     * @param string $lang
     * @param int $form
     * @param null|string $oldText if not null, triggers original changing
     * @return null|string
     */
    public function _($text, $lang = null, $form = 0, $oldText = null)
    {
        if ($lang === null) {
            $lang = $this->_currentLang;
        }
        $this->initLang($lang);

        // Changing base
        if ($oldText != null) {
            $this->update($text, $form, $oldText);
        }

        $data = $this->get($text, $lang, $form);

        if ($lang != $this->_developersLang) {
            $this->getOriginal($text, $form);
        }

        if ($data === null) {
            $data = $text;
        }
        return $data;
    }


    /**
     * Loads driver for specified language
     * @param string $lang
     * @return Lang
     */
    protected function initLang($lang)
    {
        if (!isset($this->_languages[$lang])) {
            $this->_languages[$lang] = new LanguageDriver($this->_system, $lang);
        }
        return $this;
    }

    /**
     * Returns translation for specified language string in specified language
     * @param string $text
     * @param string $lang
     * @param int $form
     * @param bool $doNotTakeDefault
     * @return null|string
     */
    protected function get($text, $lang, $form = 0, $doNotTakeDefault = false)
    {
        $this->initLang($lang);
        $langHash = sha1($text . $form);
        $data = $this->_languages[$lang]->get($langHash);
        if ($data === null && $form != 0 && !$doNotTakeDefault) {
            $langHash = sha1($text . 0);
            $data = $this->_languages[$lang]->get($langHash);
        }
        return $data;
    }

    /**
     * Writes new value for specified text
     * @param string $text
     * @param string $lang
     * @param int $form
     * @return Lang
     */
    protected function set($text, $lang, $form)
    {
        $this->initLang($lang);
        $langHash = sha1($text . $form);
        $this->_languages[$lang]->set($langHash, $text);
        return $this;
    }

    /**
     * Updates base language string an fixes hash in other languages
     * @param string $text
     * @param int $form
     * @param string $oldText
     * @return Lang
     */
    protected function update($text, $form, $oldText)
    {
        $newHash = sha1($text . $form);
        $oldHash = sha1($oldText . $form);
        $this->initLang($this->_defaultLang)
            ->_languages[$this->_defaultLang]
            ->change($newHash, $text, $oldHash);
        $this->updateHashes($newHash, $oldHash);
        return $this;
    }

    /**
     * Updates hashes on changing base language string
     * @param $newHash
     * @param $oldHash
     * @return Lang
     */
    protected function updateHashes($newHash, $oldHash)
    {
        if (count($this->_languages) > 1) {
            foreach ($this->_languages as $key => $driver) {
                if ($key != $this->_defaultLang) {
                    $driver->switchHash($newHash, $oldHash);
                }
            }
        }
        return $this;
    }

    /**
     * Returns original value of provided language string
     * @param string $text
     * @param int $form
     * @return string
     */
    protected function getOriginal($text, $form)
    {
        if ($this->_developersLang) {
            $lang = $this->_system->cfg()->{'i18n::DevelopersCode'};
        } else {
            $lang = $this->_defaultLang;
        }
        $data = $this->initLang($lang)->get($text, $lang, $form, true);
        if ($data === null) {
            $this->set($text, $lang, $form);
            $data = $text;
        }
        return $data;
    }

    public function getDefaultLang()
    {
        return $this->_defaultLang;
    }
}