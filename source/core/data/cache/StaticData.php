<?php
/**
 * User: A.Rusakevich
 * Date: 14.11.13
 * Time: 12:31
 */

namespace core\data\cache;


use core\data\StorageOrganiser;
use core\System;

class StaticData
{

    protected static $validation = null;

    protected static function initValidation()
    {
        if (self::$validation === null) {
            self::$validation = System::instance()->db()->selectIndexed('key', 'SELECT * FROM `cold_static_updates`');
        }
    }

    public static function loadStaticData($key, $index)
    {
        self::initValidation();
        $validFrom = System::instance()->memcache()->get($key . '_validation');
        $data = null;
        if (System::instance()->memcache()->getResultCode() !== \Memcached::RES_NOTFOUND) {
            if ($validFrom < self::$validation[sha1($key)]) {
                self::invalidateStaticData($key);
            } else {
                $data = System::instance()->memcache()->get($key);
            }
        }
        if (System::instance()->memcache()->getResultCode() === \Memcached::RES_NOTFOUND) {
            $data = null;
            StorageOrganiser::createPath(COLD_STATIC_DIR . '/.');
            if (file_exists(COLD_STATIC_DIR . '/' . sha1($key) . '.store')) {
                if (filectime(COLD_STATIC_DIR . '/' . sha1($key) . '.store') < self::$validation[sha1($key)]) {
                    self::invalidateStaticData($key);
                } else {
                    $data = json_decode(file_get_contents(COLD_STATIC_DIR . '/' . sha1($key) . '.store'), true);
                    if (!$data) {
                        $data = null;
                    } else {
                        System::instance()->memcache()->set($key, $data);
                        System::instance()->memcache()->set($key . '_validation', time());
                    }
                }
            }
        }

        if ($data == null) {
            $data = System::instance()->db()->selectIndexed($index, 'SELECT * FROM ?#', $key);
            self::saveStaticData($key, $data);
        }
        return $data;
    }

    public static function saveStaticData($key, $data)
    {
        StorageOrganiser::createPath(COLD_STATIC_DIR . '/.');
        file_put_contents(COLD_STATIC_DIR . '/' . sha1($key) . '.store', json_encode($data, true));
        System::instance()->memcache()->set($key, $data);
    }

    public static function invalidateStaticData($key)
    {
        System::instance()->memcache()->delete($key);
        System::instance()->memcache()->delete($key . '_validation');
        if (file_exists(COLD_STATIC_DIR . '/' . sha1($key) . '.store'))
            unlink(COLD_STATIC_DIR . '/' . sha1($key) . '.store');
    }
} 