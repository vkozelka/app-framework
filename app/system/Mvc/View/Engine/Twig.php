<?php

namespace App\System\Mvc\View\Engine;

use App\System\App;
use App\System\Mvc\View\Helper\Cms;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Twig implements EngineInterface
{

    /**
     * @var Environment
     */
    private $twig;

    public function __construct($section = null)
    {
        if ($section) {
            $loader = new FilesystemLoader(CMS_DIR_APP_VIEW.DS.$section);
        } else {
            $loader = new FilesystemLoader(CMS_DIR_APP_VIEW);
        }
        $this->twig = new Environment($loader,[
            "debug" => "development" === App::di()->environment,
            "charset" => "utf-8",
            "cache" => CMS_DIR_VAR_CACHE,
            "auto_reload" => "development" === App::di()->environment,
            "strict_variables" => "development" !== App::di()->environment,
        ]);
        $this->twig->addExtension(new Cms());
        $this->twig->addGlobal("di", App::di());
    }

    public function render($name, array $parameters = []) : string
    {
        return $this->getTwig()->render($name, $parameters);
    }

    public function exists($name) : bool
    {
        return $this->getTwig()->getLoader()->exists($name);
    }

    public function supports($name) : bool
    {
        return true;
    }

    /**
     * @return Environment
     */
    public function getTwig() {
        return $this->twig;
    }

}