<?php
namespace App\System;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\HttpFoundation\Request;

class DiContainer extends ContainerBuilder {

    public string $environment;
    public string $section;
    public string $locale;

    /**
     * DiContainer constructor.
     * @param ParameterBagInterface|null $parameterBag
     * @throws Exception
     */
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);

        $this->registerGlobalServices();
    }

    /**
     * @throws Exception
     */
    public function registerGlobalServices() {
        $this->environment = getenv("CMS_ENV") ? getenv("CMS_ENV") : "development";
        $this->section = "frontend";

        $this->register('profiler', Stopwatch::class)
            ->setArgument('morePrecision', true);

        $this->set('request', Request::createFromGlobals());

        $this->register('session', Session::class);

        $this->locale = $this->get('request')->getPreferredLanguage(["cs_CZ"]);

        $this->register('config', Config::class);

        $this->register('cache', Cache::class)
            ->setArgument('namespace', 'cms')
            ->setArgument('defaultLifetime', $this->environment === "development" ? 0 : 3600)
            ->setArgument('directory', CMS_DIR_VAR_CACHE);

        $this->register('response', Response::class);

        $this->register('router', Router::class);

        $this->register('database', Database::class);

        $this->register('url', Url::class)
            ->setArgument('router', new Reference('router'));

        $this->register('email', Email::class);

        $this->register('dispatcher', Dispatcher::class);
    }

}