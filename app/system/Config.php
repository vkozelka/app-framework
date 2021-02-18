<?php
namespace App\System;

use App\System\Config\Exception\ConfigFileNotFoundException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

final class Config {

    private FileLocator $config;

    public function __construct()
    {
        $this->config = new FileLocator(CMS_DIR_CONFIG);
    }

    public function getConfig(): FileLocator {
        return $this->config;
    }

    /**
     * @param $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getConfigValues($name) {
        App::get()->profiler->start("App::Config::".$name);
        $result = App::get()->cache->get("config_".$name, function() use ($name) {
            $foundConfig = $this->getConfig()->locate($name.".yml");
            if (!$foundConfig) {
                throw new ConfigFileNotFoundException();
            }

            return Yaml::parseFile($foundConfig);
        });
        App::get()->profiler->stop("App::Config::".$name);
        return $result;
    }

}