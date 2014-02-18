<?php
/**
 * User: Lokkie
 * Date: 18.02.14
 * Time: 23:48
 */

namespace core\data\i18n;

use core\data\cache\SimpleBinaryFileCache;

/**
 * Implementation of binary cold static for languages (i18n)
 * @package core\i18n
 */
class LanguageBinaryFile extends SimpleBinaryFileCache
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $lang;

    /**
     * @param string $path Path to language storage
     * @param string $lang Lang to open
     */
    function __construct($path, $lang)
    {
        $this->path = $path;
        $this->lang = $lang;
        parent::__construct($path . DIRECTORY_SEPARATOR . $lang);
        $this->setIndexPackFormat('H40');
        $this->setIndexSize(20);
        $this->parseIndex();
    }

}