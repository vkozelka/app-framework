<?php
namespace App\System\Mvc\View\Helper;

use App\System\App;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\ConstraintViolation;

class Cms Extends \Twig_Extension {

    public function getFunctions()
    {
        return [
            new \Twig_Function('cmsUrl', [$this, 'url']),
            new \Twig_Function('cmsTranslate', [$this, 'translate']),
            new \Twig_Function('cmsBlock', [$this, 'block']),
            new \Twig_Function('cmsDebug', [$this, 'debug']),
            new \Twig_Function('cmsFormError', [$this, 'formError']),
            new \Twig_Function('cmsUser', [$this, 'user']),
            new \Twig_Function('cmsAdmin', [$this, 'admin']),
            new \Twig_Function('cmsDate', [$this, 'date']),
            new \Twig_Function('cmsTime', [$this, 'time']),
            new \Twig_Function('cmsDateTime', [$this, 'datetime']),
            new \Twig_Function('cmsReplace', [$this, 'replace']),
        ];
    }

    public function url($name, array $parameters = []) {
        return App::get()->getUrl()->generate($name, $parameters);
    }

    public function user() {
        return App::get()->getUser();
    }

    public function admin() {
        return App::get()->getAdmin();
    }

    public function replace($subject, $pattern, $replace)
    {
        return preg_replace($pattern, $replace, $subject);
    }

    public function date($timestamp = null,$format = "d.m.Y")
    {
        return date($format, is_numeric($timestamp) ? $timestamp : strtotime($timestamp));
    }

    public function time($timestamp = null,$format = "H:i:s")
    {
        return date($format, is_numeric($timestamp) ? $timestamp : strtotime($timestamp));
    }

    public function datetime($timestamp = null,$format = "d.m.Y H:i:s")
    {
        return date($format, is_numeric($timestamp) ? $timestamp : strtotime($timestamp));
    }

    public function translate($id, array $parameters = [], $domain = null, $locale = null) {
        return App::get()->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    public function formError(ConstraintViolation $error) {
        if ($error->getPlural()) {
            return App::get()->getTranslator()->transChoice($error->getMessageTemplate(),$error->getPlural(),$error->getParameters());
        }
        return App::get()->getTranslator()->trans($error->getMessageTemplate(),$error->getParameters());
    }

    public function block($name, array $options = []) {
        list($module, $block) = explode("/",$name);
        $module = str_replace(" ", "", ucwords(str_replace("_", " ", $module)));
        $block = str_replace(" ", "\\", ucwords(str_replace(".", " ", $block))) . "Block";
        $section = str_replace(" ", "\\", ucwords(str_replace(".", " ", App::get()->getSection())));
        $blockClass = "\\App\\Module\\" . $module . "\\Block\\" . ($section?($section."\\"):"") . $block;

        if (!file_exists(CMS_DIR_APP_MODULE . DS . $module . DS . "Block" . ($section ? (DS.$section):"") . DS . $block . ".php")) {
            throw new App\BlockNotFoundException("Block " . $block . " not found");
        }

        $blockInstance = new $blockClass($options);
        if (method_exists($blockInstance, "initialize")) {
            $blockInstance->initialize();
        }

        return $blockInstance;
    }

    public function debug($var) {
        ob_start();
        var_dump($var);
        $c = ob_get_contents();
        ob_end_clean();
        return $c;
    }

}