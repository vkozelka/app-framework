<?php

namespace App\System;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

/**
 * Class Url
 * @package App\System
 */
final class Url
{

    private UrlGenerator $generator;

    /**
     * Url constructor.
     * @param Router $router
     * @throws Config\Exception\ConfigFileNotFoundException
     */
    public function __construct(Router $router)
    {
        $context = (new RequestContext())->fromRequest(App::get()->request);
        $config = App::get()->config->getConfigValues("system")["system"][App::di()->environment];
        $context->setBaseUrl(trim($config["base_url"],"/"));

        $this->generator = new UrlGenerator($router, $context);
    }

    public function getGenerator(): UrlGenerator {
        return $this->generator;
    }

    public function generate($name, array $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH) : string {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

}