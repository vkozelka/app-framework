<?php

namespace App\System;

use DirectoryIterator;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator as BaseTranslator;

class Translator
{

    private BaseTranslator $translator;

    public function __construct()
    {
        App::get()->profiler->start("App::Translator");
        $this->translator = new BaseTranslator(App::di()->locale);
        $this->translator->addLoader("yaml", new YamlFileLoader());
        $this->translator->addLoader("xlf", new XliffFileLoader());
        foreach ($this->loadAvailableTranslations() as $lang => $file) {
            $this->translator->addResource("yaml", $file, $lang);
            $this->translator->addResource("xlf",CMS_DIR_VENDOR.DS."symfony".DS."validator".DS."Resources".DS."translations".DS."validators.".$lang.".xlf", $lang);
        }
        App::get()->profiler->stop("App::Translator");
    }

    public function getTranslator(): BaseTranslator {
        return $this->translator;
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    private function loadAvailableTranslations(): array {
        App::get()->profiler->start("App::Translator::LoadTranslations");
        $languages = [];
        $dir = new DirectoryIterator(CMS_DIR_APP_LANGUAGE);
        foreach ($dir as $file) {
            if ($file->isFile() && "yml" === $file->getExtension()) {
                $languages[strtok($file->getFilename(),".")] = $file->getPath().DS.$file->getFilename();
            }
        }
        App::get()->profiler->stop("App::Translator::LoadTranslations");
        return $languages;
    }

}