<?php

namespace App\System\Mvc;

use App\System\App;
use App\System\Mvc\View\Engine\Twig;
use Twig\Environment;

class View
{

    private Twig $engine;

    private array $data = [];

    public function __construct(string $section = null)
    {
        $this->engine = new Twig($section);
    }

    public function render(string $name, array $data = []): string {
        App::get()->profiler->start("App::View::Render::".$name);
        if ($data) {
            $this->setVars($data);
        }
        $result = $this->getEngine()->render($name.".twig", $this->data);
        App::get()->profiler->stop("App::View::Render::".$name);
        return $result;
    }

    public function setVars(array $data = []): void {
        $this->data = $data;
    }

    public function setVar(string $variable, $value = null): void {
        $this->data[$variable] = $value;
    }

    public function getEngine(): Environment {
        return $this->engine->getTwig();
    }

}