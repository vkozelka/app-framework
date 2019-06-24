<?php

namespace App\System;

use App\Module\User\Entity\User;
use App\System\App\ActionNotFoundException;
use App\System\App\ControllerNotFoundException;
use App\System\App\DirectoryNotWritableException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Stopwatch\Stopwatch;
use App\System\Mvc\View;

final class App
{

    /**
     * @var null|App
     */
    protected static $_instance = null;

    /**
     * @var string
     */
    private $__environment = "development";

    /**
     * @var string
     */
    private $__section = "frontend";

    /**
     * @var null|Config
     */
    private $__config = null;

    /**
     * @var null|Request
     */
    private $__request = null;

    /**
     * @var null|Response
     */
    private $__response = null;

    /**
     * @var null|Router
     */
    private $__router = null;

    /**
     * @var null|View
     */
    private $__view = null;

    /**
     * @var null|Stopwatch
     */
    private $__profiler = null;

    /**
     * @var string
     */
    private $__locale;

    /**
     * @var Translator
     */
    private $__translator;

    /**
     * @var Database
     */
    private $__database;

    /**
     * @var Url
     */
    private $__url;

    /**
     * @var Cache
     */
    private $__cache;

    /**
     * @var Session
     */
    private $__session;

    /**
     * @var null|User
     */
    private $__user = null;

    /**
     * @var null|User
     */
    private $__adminUser = null;

    /**
     * @var null|Email
     */
    private $__email = null;

    /**
     * @return App|null
     */
    public static function get()
    {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    public function __construct()
    {
        $this->__profiler = new Stopwatch(true);
        $this->__environment = getenv("CMS_ENV") ? getenv("CMS_ENV") : "development";
    }

    public function run()
    {
        $this->__prepare();
        $this->__route();
        return $this->__dispatch();
    }

    /**
     * @return array|false|string
     */
    public function getEnvironment() {
        return $this->__environment;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->__request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->__response;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->__config;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->__router;
    }

    /**
     * @return null|Stopwatch
     */
    public function getProfiler()
    {
        return $this->__profiler;
    }

    /**
     * @return null|View
     */
    public function getView()
    {
        return $this->__view;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->__locale;
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->__translator;
    }

    /**
     * @return Url
     */
    public function getUrl() {
        return $this->__url;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->__database;
    }

    /**
     * @return Cache
     */
    public function getCache() {
        return $this->__cache;
    }

    /**
     * @return Session
     */
    public function getSession() {
        return $this->__session;
    }

    /**
     * @return User|null
     */
    public function getUser() {
        return $this->__user;
    }

    /**
     * @return User|null
     */
    public function getAdminUser() {
        return $this->__adminUser;
    }

    /**
     * @return Email|null
     */
    public function getEmail()
    {
        return $this->__email;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->__section;
    }

    public function outputProfiler() {
        $events = [];
        foreach ($this->getProfiler()->getSections() as $section) {
            foreach ($section->getEvents() as $eventName => $event) {
                $events[$eventName] = $event->getDuration()."ms";
            }
        }

        echo $this->getView()->render("__profiler",["events" => $events]);
    }

    private function __prepare()
    {
        $this->__prepareDirs();
        $this->__cache = new Cache("cms", "development" === $this->getEnvironment() ? 0 : 3600, CMS_DIR_VAR_CACHE);
        $this->__request = Request::createFromGlobals();
        $this->getProfiler()->start("App::Session::init");
        $this->__session = new Session();
        if (!$this->getSession()->isStarted()) {
            $this->getSession()->start();
        }
        $this->getProfiler()->stop("App::Session::init");
        $this->__locale = $this->getRequest()->getPreferredLanguage(["cs_CZ"]);
        $this->__translator = new Translator();
        $this->__config = new Config();
        $this->__response = new Response();
        $this->__router = new Router();
        $this->__database = new Database();
        $this->__url = new Url($this->getRouter());
        $this->__email = new Email();
        if ($this->getSession()->has("user") && $this->getSession()->get("user")) {
            $this->__user = (new \App\Module\User\Model\User())->findFirst($this->getSession()->get("user")->id_user);
        }
        if ($this->getSession()->has("admin_user") && $this->getSession()->get("admin_user")) {
            $this->__adminUser = (new \App\Module\User\Model\User())->findFirst($this->getSession()->get("admin_user")->id_admin_user);
        }
    }

    private function __prepareDirs() {
        $this->checkDir(CMS_DIR_VAR,true);
        $this->checkDir(CMS_DIR_VAR_CACHE,true);
        $this->checkDir(CMS_DIR_VAR_LOG,true);
        $this->checkDir(CMS_DIR_VAR_SESSION,true);
        ini_set("session.save_path", CMS_DIR_VAR_SESSION);
    }

    /**
     * @param $path
     * @param bool $createIfNotExists
     * @return bool
     * @throws DirectoryNotWritableException
     */
    public function checkDir($path, $createIfNotExists = false) {
        if (!file_exists($path)) {
            if ($createIfNotExists) {
                mkdir($path, 0777, true);
            } else {
                throw new DirectoryNotWritableException("Directory ".$path." not writable");
            }
        }
        if (!is_writable($path)) {
            if ($createIfNotExists) {
                chmod($path, 0777);
            } else {
                throw new DirectoryNotWritableException("Directory ".$path." not writable");
            }
        }
        if (!is_writable($path)) {
            throw new DirectoryNotWritableException("Directory ".$path." not writable");
        }
        return true;
    }

    private function __route()
    {
        $this->getRouter()->match();
    }

    private function __dispatch()
    {
        $this->getProfiler()->start("App::Dispatch");
        $routeParams = $this->getRouter()->getMatchedRoute();

        $this->__section = isset($routeParams["section"])?$routeParams["section"]:null;

        $this->__view = new View(isset($routeParams["section"])?$routeParams["section"]:null);

        $namespace = "App\\Module";
        $controllerFilePrefix = "";
        $controllerPrefix = "";
        $dir = CMS_DIR_APP_MODULE;
        $section = isset($routeParams["section"])?str_replace(" ", "", ucwords(str_replace("_", " ", $routeParams["section"]))):"";
        $module = str_replace(" ", "", ucwords(str_replace("_", " ", $routeParams["module"])));
        if (strpos($routeParams["controller"], "-") !== false) {
            $controllerParts = explode("-", $routeParams["controller"]);
            $controller = str_replace(" ", "", ucwords(str_replace("_", " ", $controllerParts[1]))) . "Controller";
            $controllerPrefix = ucfirst(strtolower($controllerParts[0]))."\\";
            $controllerFilePrefix = ucfirst(strtolower($controllerParts[0])).DS;
        } else {
            $controller = str_replace(" ", "", ucwords(str_replace("_", " ", $routeParams["controller"]))) . "Controller";
        }
        $action = lcfirst(str_replace(" ", "", ucwords(str_replace("_", " ", $routeParams["action"]))) . "Action");
        $params = $routeParams["params"];

        if (!file_exists($dir . DS . $module . DS . "Controller" . ($section?(DS.$section):"") . DS  . $controllerFilePrefix . $controller . ".php")) {
            throw new ControllerNotFoundException("Controller " . $controller . " not found");
        }

        $controllerClass = $namespace . "\\" . $module . "\\Controller\\" . ($section?($section."\\"):"") . $controllerPrefix . $controller;
        $controllerInstance = new $controllerClass;
        if (method_exists($controllerInstance, "initialize")) {
            $initializeResult = $controllerInstance->initialize();
            if ($initializeResult instanceof Response) {
                return $initializeResult->send();
            }
        }
        if (!method_exists($controllerInstance, $action)) {
            throw new ActionNotFoundException("Action " . $action . " not found in controller " . $controller);
        }
        $result = null;
        if ($params) {
            $result = $controllerInstance->$action($params);
        } else {
            $result = $controllerInstance->$action();
        }

        $this->getProfiler()->stop("App::Dispatch");
        if ($result instanceof Response) {
            if ($result instanceof JsonResponse) {
                $result->sendHeaders();
                return $result->getContent();
            } else {
                return $result->send();
            }
        } elseif ($result instanceof View) {
            return $result->render();
        } elseif (is_null($result)) {
            return $controllerInstance->getView()->render($routeParams["module"] . "/" . $routeParams["controller"] . "/" . $routeParams["action"]);
        }
    }

}