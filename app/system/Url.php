<?php

namespace App\System;

use App\System\Router\RouteNotFoundException;
use App\System\Router\RouteWithoutPathException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class Url
{

    /**
     * @var array
     */
    private $__routes = [];

    /**
     * @var UrlGenerator
     */
    private $__generator;

    public function __construct(Router $router)
    {
        $context = (new RequestContext())->fromRequest(App::get()->getRequest());
        $config = App::get()->getConfig()->getConfigValues("system")["system"][App::get()->getEnvironment()];
        $context->setBaseUrl(trim($config["base_url"],"/"));

        $this->__generator = new UrlGenerator($router, $context);
    }

    /**
     * @return UrlGenerator
     */
    public function getGenerator() {
        return $this->__generator;
    }

    public function generate($name, array $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH) {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

}