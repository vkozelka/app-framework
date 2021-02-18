<?php

namespace App\System;

use App\System\Router\RouteNotFoundException;
use App\System\Router\RouteWithoutPathException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class Router extends RouteCollection
{

    private array $routes;

    private ?array $matchedRoute = null;

    /**
     * Router constructor.
     * @throws Config\Exception\ConfigFileNotFoundException
     * @throws RouteWithoutPathException
     */
    public function __construct()
    {
        $this->routes = $this->getRouterConfig();
        $this->prepareRouter();
    }

    /**
     * @return bool
     * @throws Config\Exception\ConfigFileNotFoundException
     * @throws RouteNotFoundException
     */
    public function match(): bool
    {
        App::get()->profiler->start("App::Routing");

        $context = (new RequestContext())->fromRequest(App::get()->request);
        $config = App::get()->config->getConfigValues("system")["system"][App::di()->environment];
        $context->setBaseUrl($config["base_url"]);

        $matcher = new UrlMatcher($this, $context);
        try {
            $this->matchedRoute = $matcher->match($context->getPathInfo());
            if ($this->wasMatched()) {
                App::get()->profiler->stop("App::Routing");
                return true;
            }
        } catch (ResourceNotFoundException $e) {
        }

        if ($this->hasRoute("_notFound")) {
            $this->matchedRoute = $matcher->match($this->getRoute("_notFound")["route"]);
            if ($this->wasMatched()) {
                App::get()->profiler->stop("App::Routing");
                return true;
            }
        }
        App::get()->profiler->stop("App::Routing");
        return false;
    }

    public function getRoutes() : array
    {
        return $this->routes;
    }

    public function hasRoutes() : bool
    {
        return count($this->getRoutes()) > 0;
    }

    public function hasRoute(string $name) : bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * @param string $name
     * @return array
     * @throws RouteNotFoundException
     */
    public function getRoute(string $name): array
    {
        if ($this->hasRoute($name)) {
            return $this->routes[$name];
        }
        throw new RouteNotFoundException("Route " . $name . " not exists");
    }

    public function wasMatched(): bool
    {
        return $this->matchedRoute && !empty($this->matchedRoute);
    }

    public function getMatchedRoute(): ?array
    {
        if ($this->wasMatched()) {
            return $this->matchedRoute;
        }
        return null;
    }

    /**
     * @return array
     * @throws Config\Exception\ConfigFileNotFoundException
     */
    private function getRouterConfig(): array
    {
        return App::get()->config->getConfigValues("routes");
    }

    /**
     * @throws RouteWithoutPathException
     */
    private function prepareRouter(): void
    {
        App::get()->profiler->start("App::Routing::Init");
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
        App::get()->profiler->stop("App::Routing::Init");
    }

}