<?php
namespace App\System;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Cache extends FilesystemAdapter {

    public function __construct(string $namespace = '', int $defaultLifetime = 0, string $directory = null)
    {
        App::get()->profiler->start("App::Cache::init");
        parent::__construct($namespace, $defaultLifetime, $directory);
        App::get()->profiler->stop("App::Cache::init");
    }

}