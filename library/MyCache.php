<?php
/**
 * Created by PhpStorm.
 * User: padavan
 * Date: 21.05.17
 * Time: 16:09
 */

class MyCache
{
    static $_instance = null;

    /**
     * Return instance of Zend_Cache_Core
     *
     * @return Zend_Cache_Core
     */
    static function getInstance()
    {
        if (self::$_instance === null) {
            $frontend = 'Core';
            $frontendOptions = [
                'lifetime' => 86400, /* 1 day */
                'automatic_serialization' => true,
            ];
            //$backend = 'Memcached';
            //$backendOptions = [];
            /* for File cache use this */
            $backend = 'File';
            $cacheDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp/sys_cache/';
            if (!is_dir($cacheDir))
            {
                $oldMask = @umask(0);
                mkdir($cacheDir, 0777, true);
                chmod($cacheDir, 0777);
                umask($oldMask);
            }
            $backendOptions = [
                'cache_dir' => $cacheDir
            ];

            self::$_instance = Zend_Cache::factory(
                $frontend,
                $backend,
                $frontendOptions,
                $backendOptions
            );
        }

        return self::$_instance;
    }

    static public function cleanAllCache()
    {
        if (self::$_instance != null) {
            MyCache::getInstance()->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
    }
}