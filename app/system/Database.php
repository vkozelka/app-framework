<?php
namespace App\System;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;

class Database {

    /**
     * @var Manager
     */
    private Manager $connectionManager;

    /**
     * @var string
     */
    private string $connectionName = "mysql";

    /**
     * Database constructor.
     * @throws Config\Exception\ConfigFileNotFoundException
     */
    public function __construct()
    {
        App::get()->profiler->start("App::Database::Init");
        $config = App::get()->config->getConfigValues("database")[$this->connectionName][App::di()->environment];
        $this->connectionManager = new Manager();
        $this->connectionManager->addConnection($config, $this->connectionName);
        $this->connectionManager->bootEloquent();
        App::get()->profiler->stop("App::Database::Init");
    }

    /**
     * @return Connection
     */
    public function getConnection() : Connection
    {
        return $this->connectionManager->getConnection($this->connectionName);
    }

}