<?php

namespace App\System\Mvc\View\Engine;

use App\System\App;
use App\System\Mvc\View\Helper\Cms;
use Symfony\Component\Templating\EngineInterface;

class Twig implements EngineInterface
{

    /**
     * @var \Twig_Environment
     */
    private $__twig;

    public function __construct($section = null)
    {
        if ($section) {
            $loader = new \Twig_Loader_Filesystem(CMS_DIR_APP_VIEW.DS.$section);
        } else {
            $loader = new \Twig_Loader_Filesystem(CMS_DIR_APP_VIEW);
        }
        $this->__twig = new \Twig_Environment($loader,[
            "debug" => "development" === App::get()->getEnvironment(),
            "charset" => "utf-8",
            "cache" => CMS_DIR_VAR_CACHE,
            "auto_reload" => "development" === App::get()->getEnvironment(),
            "strict_variables" => "development" !== App::get()->getEnvironment(),
        ]);
        $this->__twig->addExtension(new Cms());
        $this->__twig->addGlobal("cms", App::get());
    }

    public function render($name, array $parameters = [])
    {
        return $this->__twig->render($name, $parameters);
    }

    public function exists($name)
    {
        return $this->__twig->getLoader()->exists($name);
    }

    public function supports($name)
    {
        return true;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig() {
        return $this->__twig;
    }

}