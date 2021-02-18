<?php

namespace App\System;

use App\System\Mvc\View;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class App
 * @package App\System
 *
 * @property-read Stopwatch $profiler
 * @property-read Session $session
 * @property-read Router $router
 * @property-read string $environment
 * @property-read Request $request
 * @property-read Response $response
 * @property-read Config $config
 * @property-read Cache $cache
 * @property-read Dispatcher $dispatcher
 * @property-read Database $database
 * @property-read Email $email
 * @property-read Translator $translator
 * @property-read Url $url
 * @property-read ?View $view
 */
final class App
{

    protected static ?App $instance = null;

    private DiContainer $diContainer;

    public static function get(): App
    {
        if (null === self::$instance) {
            self::$instance = new self(new DiContainer());
        }
        return self::$instance;
    }

    public static function di(): DiContainer
    {
        return self::get()->getDiContainer();
    }

    public function __construct(DiContainer $container)
    {
        $this->setDiContainer($container);
    }

    public function getDiContainer(): DiContainer
    {
        return $this->diContainer;
    }

    public function setDiContainer(DiContainer $diContainer): App
    {
        $this->diContainer = $diContainer;
        return $this;
    }

    /**
     * @return string
     * @throws App\Exception\ControllerNotFoundException
     * @throws Config\Exception\ConfigFileNotFoundException
     * @throws Filesystem\Exception\DirectoryNotWritableException
     */
    public function run(): string
    {
        $this->prepare();
        return $this->dispatcher->dispatch();
    }

    public function outputProfiler(bool $returnOnly = false)
    {
        $events = [];
        foreach ($this->profiler->getSections() as $section) {
            foreach ($section->getEvents() as $eventName => $event) {
                $events[$eventName] = $event->getDuration() . "ms";
            }
        }
        if ($returnOnly === true) {
            return $events;
        } else {
            echo $this->view->render("__profiler", ["events" => $events]);
        }
    }

    /**
     * @throws Config\Exception\ConfigFileNotFoundException
     * @throws Filesystem\Exception\DirectoryNotWritableException
     */
    private function prepare()
    {
        Filesystem::checkDir(CMS_DIR_VAR, true);
        Filesystem::checkDir(CMS_DIR_VAR_CACHE, true);
        Filesystem::checkDir(CMS_DIR_VAR_LOG, true);
        Filesystem::checkDir(CMS_DIR_VAR_SESSION, true);
        ini_set("session.save_path", CMS_DIR_VAR_SESSION);

        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $this->router->match();
    }

    /**)
     * @param $name
     * @return object|null
     * @throws Exception
     */
    public function __get($name): ?object {
        return $this->getDiContainer()->get($name);
    }

}