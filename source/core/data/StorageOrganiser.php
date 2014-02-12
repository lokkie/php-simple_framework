<?php
/**
 * User: A.Rusakevich
 * Date: 08.08.13
 * Time: 12:49
 */

namespace core\data;

/**
 * Storage maintenance implementation
 * @package core\data
 */
class StorageOrganiser
{
    /**
     * Creates full path to specified file name
     * @param string $fileName
     */
    public static function createPath($fileName)
    {
        $components = explode('/', pathinfo($fileName, PATHINFO_DIRNAME));
        $absolute = '';
        foreach ($components as $component) {
            if ($component != "") {
                $absolute .= '/' . $component;
                if (!file_exists($absolute)) {
                    if (!mkdir($absolute)) {
                        //var_dump($path);
                    }
                }
            }
        }
    }
}