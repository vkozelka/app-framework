<?php

namespace App\System;

use App\System\App\Exception\ActionNotFoundException;
use App\System\App\Exception\ControllerNotFoundException;
use App\System\Helper\StringHelper;
use App\System\Mvc\Controller;
use App\System\Mvc\View;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Dispatcher
{

    public string $moduleDirectory;
    public string $namespace = "App\\Module";
    public string $controllerPrefix = "";
    public string $controllerSuffix = "Controller";
    public string $controllerFilePrefix = "";
    public string $actionSuffix = "Action";
    public string $defaultTemplate;

    public string $module;
    public string $section;
    public string $controller;
    public string $action;
    public ?array $params;

    public function __construct(string $controllerSuffix = "Controller", string $actionSuffix = "Action")
    {
        $this->controllerSuffix = $controllerSuffix;
        $this->actionSuffix = $actionSuffix;

        $this->moduleDirectory = CMS_DIR_APP_MODULE;

        $this->parseRouteParams(App::get()->router->getMatchedRoute());
    }

    /**
     * @return string
     * @throws ControllerNotFoundException
     */
    public function dispatch(): string
    {
        App::di()->set('view', new View(App::di()->section));

        $fullControllerPath = $this->getFullControllerPath();
        $fullControllerClass = $this->getFullControllerClass();
        if (!file_exists($fullControllerPath)) {
            throw new ControllerNotFoundException("Controller " . $fullControllerClass . " not found");
        }

        $controllerInstance = new $fullControllerClass;
        try {
            return $this->handle($controllerInstance, $this->action, $this->params);
        } catch (Exception $e) {
            return App::get()->view->render($this->defaultTemplate);
        }
    }

    private function parseController(string $subject): string
    {
        $this->controllerFilePrefix = "";
        $this->controllerPrefix = "";
        $controller = "";
        $controllerParts = explode("-", $subject);
        $partsCount = count($controllerParts);

        for ($i=0;$i<$partsCount;$i++) {
            if ($i+1 === $partsCount) {
                $controller = StringHelper::kebabToCamelCase($controllerParts[$i]) . $this->controllerSuffix;
            } else {
                if (!empty($this->controllerPrefix)) {
                    $this->controllerPrefix.= "\\";
                }
                if (!empty($this->controllerFilePrefix)) {
                    $this->controllerFilePrefix.= DS;
                }
                $this->controllerPrefix.= ucfirst(strtolower($controllerParts[$i]));
                $this->controllerFilePrefix.= ucfirst(strtolower($controllerParts[$i]));
            }
        }
        return $controller;
    }

    /**
     * @param $controllerInstance
     * @param $action
     * @param array $params
     * @return false|string|Response
     * @throws ActionNotFoundException|Exception
     */
    private function handle(Controller $controllerInstance, string $action, array $params = []) {
        if (method_exists($controllerInstance, "initialize")) {
            $initializeResult = $controllerInstance->initialize();
            if ($initializeResult instanceof Response) {
                return $initializeResult->send();
            }
        }
        if (!method_exists($controllerInstance, $action)) {
            throw new ActionNotFoundException("Action " . $action . " not found in controller " . $this->controller);
        }
        $result = null;
        if ($params) {
            $result = $controllerInstance->$action($params);
        } else {
            $result = $controllerInstance->$action();
        }

        if ($result instanceof Response) {
            if ($result instanceof JsonResponse) {
                $result->sendHeaders();
                return $result->getContent();
            } else {
                return $result->send();
            }
        } elseif ($result instanceof View) {
            return $result->render($this->defaultTemplate);
        } else {
            throw new Exception("No valid result found. Fallback to template render");
        }
    }

    private function getFullControllerPath(): string
    {
        $parts = [
            "dir" => $this->moduleDirectory,
            "module" => $this->module,
            "folder" => "Controller",
            "section" => App::di()->section,
            "prefix" => $this->controllerFilePrefix,
            "file" => $this->controller.".php"
        ];
        if (empty($parts["section"])) {
            unset($parts["section"]);
        }
        if (empty($parts["prefix"])) {
            unset($parts["prefix"]);
        }
        return implode(DS, array_values($parts));
    }

    private function getFullControllerClass(): string
    {
        $parts = [
            "namespace" => $this->namespace,
            "module" => $this->module,
            "folder" => "Controller",
            "section" => App::di()->section,
            "prefix" => $this->controllerPrefix,
            "class" => $this->controller
        ];
        if (empty($parts["section"])) {
            unset($parts["section"]);
        }
        if (empty($parts["prefix"])) {
            unset($parts["prefix"]);
        }

        return implode("\\",array_values($parts));
    }

    private function parseRouteParams(array $routeParams): void {
        if (isset($routeParams["section"])) {
            App::di()->section = StringHelper::kebabToCamelCase($routeParams["section"]);
        }
        $this->module = StringHelper::kebabToCamelCase($routeParams["module"]);
        $this->controller = $this->parseController($routeParams["controller"]);
        $this->action = lcfirst(StringHelper::kebabToCamelCase($routeParams["action"]) . $this->actionSuffix);
        $this->params = $routeParams["params"] ?? [];

        $this->defaultTemplate = $routeParams["module"]."/".$routeParams["controller"]."/".$routeParams["action"];
    }

}