<?php
namespace App\System;

use Symfony\Component\Cache\Simple\FilesystemCache;

class Cache extends FilesystemCache {

    public function __construct(string $namespace = '', int $defaultLifetime = 0, string $directory = null)
    {
        App::get()->getProfiler()->start("App::Cache::init");
        parent::__construct($namespace, $defaultLifetime, $directory);
        App::get()->getProfiler()->stop("App::Cache::init");
    }


}