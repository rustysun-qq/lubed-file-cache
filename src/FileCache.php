<?php
namespace Lubed\FileCache;

use Lubed\Caches\Cache;
use Lubed\Caches\Exceptions;

final class FileCache implements Cache {

    public function getInstance(?array $params=NULL) {
        if(!$params){
            Exceptions::InvalidArgument('Invalid parameters',['class'=>__CLASS__,'method'=>__METHOD__]);
        }
        return FileCacheHandler::getInstance($params);
    }
}