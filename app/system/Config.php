<?php
namespace App\System;

use App\System\Config\ConfigFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

final class Config {

    /**
     * @var FileLocator
     */
    private $__config;

    public function __construct()
    {
        $this->__config = new FileLocator(CMS_DIR_CONFIG);
    }

    /**
     * @return FileLocator
     */
    public function getConfig() {
        return $this->__config;
    }

    /**
     * @param $name
     * @return mixed
     * @throws ConfigFileNotFoundException
     */
    public function getConfigValues($name) {
        App::get()->getProfiler()->start("App::Config::".$name);
        if (!App::get()->getCache()->has("config_".$name) || App::get()->getEnvironment() == "development") {
            $foundConfig = $this->getConfig()->locate($name.".yml");
            if (!$foundConfig) {
                throw new ConfigFileNotFoundException();
            }

            $result = Yaml::parseFile($foundConfig);
            App::get()->getCache()->set("config_".$name, $result);
        } else {
            $result = App::get()->getCache()->get("config_".$name);
        }
        App::get()->getProfiler()->stop("App::Config::".$name);
        return $result;
    }

}