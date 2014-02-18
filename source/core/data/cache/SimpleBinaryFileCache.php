<?php
/**
 * User: Lokkie
 * Date: 18.02.14
 * Time: 1:35
 */

namespace core\data\cache;

use core\data\StorageOrganiser;

/**
 * Simple file cache implementation
 *
 * Uses 2 files:
 * *.bdb - data collection
 * *.bdi - data index
 * @package core\data\cache
 */
class SimpleBinaryFileCache
{
    /**
     * @var resource
     */
    protected $data_handle;
    /**
     * @var resource
     */
    protected $index_handle;
    /**
     * @var bool
     */
    protected $changed = false;
    /**
     * @var array[]
     */
    protected $indexes = array();
    /**
     * @var array[]
     */
    protected $changes = array();

    /**
     * @var int
     */
    private $indexSize = 20;
    /**
     * @var string
     */
    private $fileName = '';
    /**
     * @var string
     */
    private $indexPackFormat = 'h40';
    /**
     * @var int
     */
    private $offsetSize = 4;
    /**
     * @var int
     */
    private $lengthSize = 4;

    private $serializeFunction = 'json_encode';
    private $unserializeFunction = 'json_decode';

    /**
     * @param string $fileName
     */
    function __construct($fileName)
    {
        $this->fileName = $fileName;
        StorageOrganiser::createPath($fileName);
        $this->index_handle = fopen($this->fileName . '.bdi', 'r+');
        $this->data_handle = fopen($this->fileName . '.bdb', 'r+');
        if (!function_exists('json_encode') || !function_exists('json_decode')) {
            $this->serializeFunction = 'serialize';
            $this->unserializeFunction = 'unserialise';
        }
    }

    /**
     * Destructor
     * On destruction writing all changes and closing file handlers
     */
    function __destruct()
    {
        if ($this->changed) {
            $this->writeData();
        } else {
            @fclose($this->data_handle);
            @fclose($this->index_handle);
        }
    }

    /**
     * Gets index id size in bytes
     * @return int
     */
    public function getIndexSize()
    {
        return $this->indexSize;
    }

    /**
     * Sets index id size in bytes
     * @param int $indexBlockSize
     */
    public function setIndexSize($indexBlockSize)
    {
        $this->indexSize = $indexBlockSize;
    }

    /**
     * Gets index id format
     * @return string
     */
    public function getIndexPackFormat()
    {
        return $this->indexPackFormat;
    }

    /**
     * Sets index id format
     * @param string $indexPackFormat
     */
    public function setIndexPackFormat($indexPackFormat)
    {
        $this->indexPackFormat = $indexPackFormat;
    }

    /**
     * Parses index file
     * @return $this
     */
    protected function parseIndex()
    {
        fseek($this->index_handle, 0);
        $counter = 0;
        $indexBlockSize = $this->indexSize + $this->offsetSize + $this->lengthSize;
        while (!feof($this->index_handle)) {
            $indexBlock = fread($this->index_handle, $indexBlockSize);
            if (strlen($indexBlock) == $indexBlockSize) {
                $blockData = unpack($this->indexPackFormat . 'i/Lo/Ll', $indexBlock);
                $this->indexes[$blockData['i']] = array($blockData['o'], $blockData['l'], $counter);
                $counter++;
            }
        }
        return $this;
    }

    /**
     * Reads data from file cache with specified index
     * @param $index
     * @return mixed|null
     */
    public function get($index)
    {
        $data = null;
        if (isset($this->indexes[$index])) {
            fseek($this->data_handle, $this->indexes[$index][0]);
            $data = call_user_func($this->unserializeFunction, fread($this->data_handle, $this->indexes[$index][1]));
        }
        return $data;
    }

    /**
     * Inserts or updates data into file cache by index
     * @param string|int $index
     * @param mixed $data
     * @return $this
     */
    public function set($index, $data)
    {
        $data = call_user_func($this->serializeFunction, $data);
        $change = array(
            'a' => (isset($this->indexes[$index]) ? 'u' : 'i'),
            'i' => $index,
            'd' => $data,
            'l' => strlen($data)
        );
        $this->changes[] = $change;
        $this->changed = true;
        return $this;
    }

    /**
     * Removes record by index
     * @param int|string $index
     * @return $this
     */
    public function remove($index)
    {
        if (isset($this->indexes[$index])) {
            $change = array(
                'a' => 'd',
                'i' => $index
            );
            $this->changes[] = $change;
            $this->changed = true;
        }
        return $this;
    }

    /**
     * Changes data by index
     *
     * If old index provided, changes index and data
     * @param string|int $index
     * @param mixed $data
     * @param string $oldIndex
     * @return $this
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
     * @return $this
     */
    private function writeData()
    {
        $changes = array();

        $indexBlockSize = $this->indexSize + $this->offsetSize + $this->lengthSize;

        foreach ($this->changes as $changeData) {
            if (!isset($this->indexes[$changeData['i']])) {
                $this->indexes[$changeData['i']] = array($changeData, $changeData['l'], count($this->indexes));
            } else {
                $changes[$changeData['i']][] = $changeData;
            }
        }

        $tmpHandler = fopen($this->fileName . '.bltd', 'w+b');
        $tmpIndex = fopen($this->fileName . '.blti', 'w+b');

        $fileIndex = 0;
        foreach ($this->indexes as $key => $indexData) {
            if (is_array($indexData[0])) {
                $data = $indexData[0]['d'];
            } else {
                fseek($this->data_handle, $indexData[0]);
                $data = fread($this->data_handle, $indexData[1]);
            }


            if (isset($changes[$key])) {
                foreach ($changes[$key] as $changeData) {
                    if ($changeData['a'] == 'u' || $changeData['a'] == 'i') {
                        $data = $changeData['d'];
                        $indexData[1] = $this->indexes[$key][1] = $changeData['l'];
                    } else {
                        $data = null;
                        unset($this->indexes[$key]);
                        $indexData = null;
                    }
                }
            }
            if ($indexData != null) {
                $indexData[0] = $this->indexes[$key][0] = $fileIndex;
                $fileIndex += $indexData[1];
                fwrite($tmpHandler, $data, $indexData[1]);
                fwrite($tmpIndex, pack($this->indexPackFormat . 'LL', $key, $indexData[0], $indexData[1]), $indexBlockSize);
            }
        }
        fclose($tmpHandler);
        fclose($tmpIndex);

        fclose($this->data_handle);
        fclose($this->index_handle);
        rename($this->fileName . '.bltd', $this->fileName . '.bld');
        rename($this->fileName . '.blti', $this->fileName . '.bli');

        return $this;
    }

}