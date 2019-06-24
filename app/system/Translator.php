<?php

namespace App\System;

use App\System\View\Engine\Twig;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Translator
{

    private $__translator;

    public function __construct()
    {
        App::get()->getProfiler()->start("App::Translator");
        $this->__translator = new \Symfony\Component\Translation\Translator(App::get()->getLocale());
        $this->__translator->addLoader("yaml", new YamlFileLoader());
        $this->__translator->addLoader("xlf", new XliffFileLoader());
        foreach ($this->__loadAvailableTranslations() as $lang => $file) {
            $this->__translator->addResource("yaml", $file, $lang);
            $this->__translator->addResource("xlf",CMS_DIR_VENDOR.DS."symfony".DS."validator".DS."Resources".DS."translations".DS."validators.".$lang.".xlf", $lang);
        }
        App::get()->getProfiler()->stop("App::Translator");
    }

    public function getTranslator() {
        return $this->__translator;
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->getTranslator()->transChoice($id, $number, $parameters, $domain, $locale);
    }

    private function __loadAvailableTranslations() {
        App::get()->getProfiler()->start("App::Translator::LoadTranslations");
        $langs = [];
        $dir = new \DirectoryIterator(CMS_DIR_APP_LANGUAGE);
        foreach ($dir as $file) {
            if ($file->isFile() && "yml" === $file->getExtension()) {
                $langs[strtok($file->getFilename(),".")] = $file->getPath().DS.$file->getFilename();
            }
        }
        App::get()->getProfiler()->stop("App::Translator::LoadTranslations");
        return $langs;
    }

}