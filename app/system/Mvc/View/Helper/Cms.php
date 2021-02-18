<?php

namespace App\System\Mvc\View\Helper;

use App\System\App;
use App\System\Helper\StringHelper;
use Symfony\Component\Validator\ConstraintViolation;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Cms extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cmsUrl', [$this, 'url']),
            new TwigFunction('cmsTranslate', [$this, 'translate']),
            new TwigFunction('cmsBlock', [$this, 'block']),
            new TwigFunction('cmsDebug', [$this, 'debug']),
            new TwigFunction('cmsFormError', [$this, 'formError']),
            new TwigFunction('cmsDate', [$this, 'date']),
            new TwigFunction('cmsTime', [$this, 'time']),
            new TwigFunction('cmsDateTime', [$this, 'datetime']),
            new TwigFunction('cmsReplace', [$this, 'replace']),
        ];
    }

    protected function url($name, array $parameters = []) : string
    {
        return App::get()->url->generate($name, $parameters);
    }

    protected function replace($subject, $pattern, $replace): string
    {
        return preg_replace($pattern, $replace, $subject);
    }

    protected function date($timestamp = null, $format = "d.m.Y"): string
    {
        return date($format, is_numeric($timestamp) ? $timestamp : strtotime($timestamp));
    }

    protected function time($timestamp = null, $format = "H:i:s"): string
    {
        return date($format, is_numeric($timestamp) ? $timestamp : strtotime($timestamp));
    }

    protected function datetime($timestamp = null, $format = "d.m.Y H:i:s"): string
    {
        return date($format, is_numeric($timestamp) ? $timestamp : strtotime($timestamp));
    }

    protected function translate($id, array $parameters = [], $domain = null, $locale = null): string
    {
        return App::get()->translator->trans($id, $parameters, $domain, $locale);
    }

    protected function formError(ConstraintViolation $error): string
    {
        return App::get()->translator->trans($error->getMessageTemplate(), $error->getParameters());
    }

    /**
     * @param $name
     * @param array $options
     * @return mixed
     * @throws App\Exception\BlockNotFoundException
     */
    protected function block($name, array $options = [])
    {
        list($module, $block) = explode("/", $name);
        $module = StringHelper::kebabToCamelCase($module);
        $block = StringHelper::stringToClass($block) . "Block";
        $section = App::di()->section;

        $blockClass = $this->getFullBlockClass($module, $section, $block);
        $blockFile = $this->getFullBlockPath($module, $section, $block);

        if (!file_exists($blockFile)) {
            throw new App\Exception\BlockNotFoundException("Block " . $blockClass . " not found");
        }

        $blockInstance = new $blockClass($options);
        if (method_exists($blockInstance, "initialize")) {
            $blockInstance->initialize();
        }

        return $blockInstance;
    }

    public function debug($var)
    {
        ob_start();
        var_dump($var);
        $c = ob_get_contents();
        ob_end_clean();
        return $c;
    }

    private function getFullBlockPath(string $module, string $section, string $block): string
    {
        $parts = [
            "dir" => CMS_DIR_APP_MODULE,
            "module" => $module,
            "folder" => "block",
            "section" => $section,
            "file" => $block.".php"
        ];
        if (empty($parts["section"])) {
            unset($parts["section"]);
        }
        return implode(DS, array_values($parts));
    }

    private function getFullBlockClass(string $module, string $section, string $block): string
    {
        $parts = [
            "namespace" => "\\App\\Module",
            "module" => $module,
            "folder" => "Block",
            "section" => $section,
            "block" => $block
        ];
        if (empty($parts["section"])) {
            unset($parts["section"]);
        }
        return implode("\\", array_values($parts));
    }

}