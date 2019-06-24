<?php

namespace App\System\Mvc;

use App\System\App;
use App\System\Mvc\View\Engine\Twig;

class View
{

    /**
     * @var Twig
     */
    private $__engine;

    private $__data = [];

    public function __construct(string $section = null)
    {
        $this->__engine = new Twig($section);
    }

    public function render(string $name, array $data = []) {
        App::get()->getProfiler()->start("App::View::Render::".$name);
        if ($data) {
            $this->setVars($data);
        }
        $result = $this->__engine->render($name.".twig", $this->__data);
        App::get()->getProfiler()->stop("App::View::Render::".$name);
        return $result;
    }

    public function setVars(array $data = []) {
        $this->__data = $data;
    }

    public function setVar(string $variable, $value = null) {
        $this->__data[$variable] = $value;
    }

    /**
     * @return \Twig_Environment
     */
    public function getEngine() {
        return $this->__engine->getTwig();
    }

}