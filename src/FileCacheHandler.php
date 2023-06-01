<?php
namespace Lubed\FileCache;

use Lubed\Caches\CacheHandler;
use Lubed\Caches\Exceptions;

class FileCacheHandler implements CacheHandler {
    private static $instances=false;
    private $directory;

    public function fetch(string $name, &$result) {
        $result=false;
        $filename=$this->directory . $this->sanitize($name);
        clearstatcache(false, $filename);
        if (false === file_exists($filename)) {
            return false;
        }
        $result=true;
        $data=file_get_contents($filename);
        $data=unserialize($data);
        return $data;
    }

    public function store(string $name, $value) {
        $value=serialize($value);
        $name=$this->sanitize($name);
        return @file_put_contents($this->directory . $name, $value);
    }

    public function flush() {
        foreach (glob($this->directory . '/[^.]*') as $file) {
            @unlink($file);
        }
    }

    public function remove(string $name) {
        return @unlink($this->directory . $name);
    }

    public static function getInstance(array $options=[]) : FileCacheHandler {
        $dir=$options['directory'];
        if (!isset(self::$instances[$dir])) {
            $ret=new FileCacheHandler($options);
            self::$instances[$dir]=$ret;
            $ret->init();
        } else {
            $ret=self::$instances[$dir];
        }
        return $ret;
    }

    private function __construct(array $options) {
        $this->directory=$options['directory'];
        if ($this->directory[strlen($this->directory) - 1] !== '/') {
            $this->directory.='/';
        }
    }

    private function init() {
        if (!file_exists($this->directory)) {
            if (@mkdir($this->directory, 0750, true) === false) {
                Exceptions::CacheFailed(sprintf('%s:Could not create: %s', __CLASS__, $this->directory));
            }
        }
    }

    private function sanitize($name) {
        return str_replace('/', '_', str_replace('\\', '_', $name));
    }
}