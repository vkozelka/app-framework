<?php
namespace App\System;

class Block {

    protected ?string $template = null;

    protected array $options = [];

    protected array $data = [];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options,$options);
    }

    public function setVar($var, $value): void {
        $this->data[$var] = $value;
    }

    public function setVars(array $vars): void {
        $this->data = array_merge($this->data, $vars);
    }

    public function hasOption($key): bool {
        return isset($this->options[$key]);
    }

    public function getOption($key, $default = null): ?string {
        if (!$this->hasOption($key)) {
            return $default;
        }
        return $this->options[$key];
    }

    public function render($template = null, array $data = []): string {
        if (!$template && $this->template) {
            $template = $this->template;
        }
        $data = array_merge($this->data, $data);
        $data["current_block"] = $this;
        return App::get()->view->render("__block/".$template, $data);
    }

}