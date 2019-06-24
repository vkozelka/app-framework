<?php

namespace App\System;

use App\System\Router\RouteNotFoundException;
use App\System\Router\RouteWithoutPathException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class Router extends RouteCollection
{

    /**
     * @var array
     */
    private $__routes = [];

    /**
     * @var array
     */
    private $__config = [];

    /**
     * @var null|ParameterBag
     */
    private $__matchedRoute = null;

    public function __construct()
    {
        $this->__getRouterConfig();
        $this->__prepareRouter();
    }

    public function match()
    {
        App::get()->getProfiler()->start("App::Routing");

        $context = (new RequestContext())->fromRequest(App::get()->getRequest());
        $config = App::get()->getConfig()->getConfigValues("system")["system"][App::get()->getEnvironment()];
        $context->setBaseUrl($config["base_url"]);

        $matcher = new UrlMatcher($this, $context);
        try {
            $this->__matchedRoute = $matcher->match($context->getPathInfo());
            if ($this->wasMatched()) {
                App::get()->getProfiler()->stop("App::Routing");
                return true;
            }
        } catch (ResourceNotFoundException $e) {
        }

        if ($this->hasRoute("_notFound")) {
            $this->__matchedRoute = $matcher->match($this->getRoute("_notFound")["route"]);
            if ($this->wasMatched()) {
                App::get()->getProfiler()->stop("App::Routing");
                return true;
            }
        }
        App::get()->getProfiler()->stop("App::Routing");
        return false;
    }

    public function getRoutes()
    {
        return $this->__routes;
    }

    public function hasRoutes()
    {
        return count($this->getRoutes()) > 0 ? true : false;
    }

    public function hasRoute($name)
    {
        return isset($this->__routes[$name]) ? true : false;
    }

    public function getRoute($name)
    {
        if ($this->hasRoute($name)) {
            return $this->__routes[$name];
        }
        throw new RouteNotFoundException("Route " . $name . " not exists");
    }

    public function wasMatched()
    {
        return $this->__matchedRoute && !empty($this->__matchedRoute) ? true : false;
    }

    /**
     * @return null|ParameterBag
     */
    public function getMatchedRoute()
    {
        if ($this->wasMatched()) {
            return $this->__matchedRoute;
        }
        return null;
    }

    /**
     * @throws Config\ConfigFileNotFoundException
     */
    private function __getRouterConfig()
    {
        $this->__routes = App::get()->getConfig()->getConfigValues("routes");
    }

    /**
     * @throws RouteWithoutPathException
     */
    private function __prepareRouter()
    {
        App::get()->getProfiler()->start("App::Routing::Init");
        if ($this->hasRoutes()) {
            foreach ($this->getRoutes() as $routeName => $routeDefinition) {
                $route = $routeDefinition["route"] ?: null;
                if (null === $route) {
                    throw new RouteWithoutPathException();
                }

                $defaults = isset($routeDefinition["defaults"]) ? $routeDefinition["defaults"] : [];
                $requirements = isset($routeDefinition["requirements"]) ? $routeDefinition["requirements"] : [];
                $options = isset($routeDefinition["options"]) ? $routeDefinition["options"] : [];

                $this->add($routeName, new Route($route, $defaults, $requirements, $options));
            }
        }
        App::get()->getProfiler()->stop("App::Routing::Init");
    }

}