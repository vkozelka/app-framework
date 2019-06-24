<?php
namespace App\System;

class Block {

    protected $_template = null;

    protected $_options = [];

    protected $_data = [];

    public function __construct(array $options = [])
    {
        $this->_options = array_merge($this->_options,$options);
    }

    public function setVar($var, $value) {
        $this->_data[$var] = $value;
    }

    public function setVars(array $vars) {
        $this->_data = array_merge($this->_data, $vars);
    }

    public function hasOption($key) {
        return isset($this->_options[$key]);
    }

    public function getOption($key, $default = null) {
        if (!$this->hasOption($key)) {
            return $default;
        }
        return $this->_options[$key];
    }

    public function render($template = null, array $data = []) {
        if (!$template && $this->_template) {
            $template = $this->_template;
        }
        $data = array_merge($this->_data, $data);
        $data["current_block"] = $this;
        return App::get()->getView()->render("__block/".$template, $data);
    }

}