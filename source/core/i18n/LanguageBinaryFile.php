<?php
/**
 * User: A.Rusakevich
 * Date: 10.02.14
 * Time: 15:51
 */

namespace core\i18n;

use core\data\StorageOrganiser;

/**
 * Implementation of binary cold static for languages (i18n)
 * @package core\i18n
 */
class LanguageBinaryFile
{
    /**
     * 160 bits for sha1 index, 32 bits start position, 32 bits length
     */
    const INDEX_BLOCK_SIZE = 28;
    /**
     * @var resource
     */
    protected $_index_handle;
    /**
     * @var resource
     */
    protected $_data_handle;
    /**
     * @var bool
     */
    protected $_changed = false;

    /**
     * @var array[]
     */
    protected $index = array();
    /**
     * @var array[]
     */
    protected $changes = array();

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
        StorageOrganiser::createPath($path . DIRECTORY_SEPARATOR . $lang . '.bli');
        StorageOrganiser::createPath($path . DIRECTORY_SEPARATOR . $lang . '.bld');
        $this->_index_handle = fopen($path . DIRECTORY_SEPARATOR . $lang . '.bli', 'a+b');
        $this->_data_handle = fopen($path . DIRECTORY_SEPARATOR . $lang . '.bld', 'a+b');
        $this->parseIndex();
    }

    /**
     * Parses language index file
     * @return LanguageBinaryFile
     */
    protected function parseIndex()
    {
        fseek($this->_index_handle, 0);
        $counter = 0;
        while (!feof($this->_index_handle)) {
            $block = fread($this->_index_handle, self::INDEX_BLOCK_SIZE);
            if (strlen($block) == self::INDEX_BLOCK_SIZE) {
                $block = unpack('h40h/Lp/Ll', $block);
                $this->index[$block['h']] = array($block['p'], $block['l'], $counter);
                $counter++;
            }
        }
        return $this;
    }

    /**
     * Returns value for selected index
     * @param string $hash
     * @return null|string
     */
    public function get($hash)
    {
        $data = null;
        if (isset($this->index[$hash])) {
            fseek($this->_data_handle, $this->index[$hash][0]);
            $data = fread($this->_data_handle, $this->index[$hash][1]);
        }
        return $data;
    }

    /**
     * Sets value for specified index
     * @param string $index
     * @param string $data
     * @return LanguageBinaryFile
     */
    public function set($index, $data)
    {
        $change = array(
            'a' => (isset($this->_index_handle[$index]) ? 'u' : 'i'),
            'i' => $index,
            'd' => $data,
            'l' => strlen($data)
        );
        $this->changes[] = $change;
        $this->_changed = true;
        return $this;
    }

    /**
     * Removes specified index and data for it
     * @param string $index
     * @return LanguageBinaryFile
     */
    public function remove($index)
    {
        if (isset($this->_index_handle[$index])) {
            $change = array(
                'a' => 'd',
                'i' => $index
            );
            $this->changes[] = $change;
            $this->_changed = true;
        }
        return $this;
    }

    /**
     * Changes data with specified index. Could change index for data
     * @param string $index
     * @param string $data
     * @param string $oldIndex
     * @return LanguageBinaryFile
     */
    public function change($index, $data, $oldIndex = '')
    {
        if ($oldIndex != '') {
            $this->remove($oldIndex);
        }
        $this->set($index, $data);
        return $this;
    }

    /**
     * Writes changes to files
     * @return LanguageBinaryFile
     */
    protected function writeData()
    {

        $changes = array();

        foreach ($this->changes as $changeData) {
            if (!isset($this->index[$changeData['i']])) {
                $this->index[$changeData['i']] = array($changeData, $changeData['l'], count($this->index));
            } else {
                $changes[$changeData['i']][] = $changeData;
            }
        }

        $tmpHandler = fopen($this->path . DIRECTORY_SEPARATOR . $this->lang . '.bltd', 'w+b');
        $tmpIndex = fopen($this->path . DIRECTORY_SEPARATOR . $this->lang . '.blti', 'w+b');

        $fileIndex = 0;
        foreach ($this->index as $key => $indexData) {
            if (is_array($indexData[0])) {
                $data = $indexData[0]['d'];
            } else {
                fseek($this->_data_handle, $indexData[0]);
                $data = fread($this->_data_handle, $indexData[1]);
            }


            if (isset($changes[$key])) {
                foreach ($changes[$key] as $changeData) {
                    if ($changeData['a'] == 'u' || $changeData['a'] == 'i') {
                        $data = $changeData['d'];
                        $indexData[1] = $this->index[$key][1] = $changeData['l'];
                    } else {
                        $data = null;
                        unset($this->index[$key]);
                        $indexData = null;
                    }
                }
            }
            if ($indexData != null) {
                $indexData[0] = $this->index[$key][0] = $fileIndex;
                $fileIndex += $indexData[1];
                fwrite($tmpHandler, $data, $indexData[1]);
                fwrite($tmpIndex, pack('h40LL', $key, $indexData[0], $indexData[1]), self::INDEX_BLOCK_SIZE);
            }
        }
        fclose($tmpHandler);
        fclose($tmpIndex);

        fclose($this->_data_handle);
        fclose($this->_index_handle);
        rename($this->path . DIRECTORY_SEPARATOR . $this->lang . '.bltd', $this->path . DIRECTORY_SEPARATOR . $this->lang . '.bld');
        rename($this->path . DIRECTORY_SEPARATOR . $this->lang . '.blti', $this->path . DIRECTORY_SEPARATOR . $this->lang . '.bli');

        return $this;
    }

    /**
     * Closing descriptors on destruction
     */
    function __destruct()
    {
        if ($this->_changed) {
            $this->writeData();
        } else {
            @fclose($this->_data_handle);
            @fclose($this->_index_handle);
        }
    }
} 