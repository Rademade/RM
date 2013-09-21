<?php
class RM_View_ExtForm_Button {

    private $name;
    private $type;
    private $href;
    private $class;

    private $hidden = false;

    public function __construct($name, $type, $class = 'main-links', $href = null) {
        $this->name = $name;
        $this->type = $type;
        $this->href = $href;
        $this->class = $class;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getHref() {
        return $this->href;
    }

    public function getClass() {
        return $this->class;
    }

    public function hide() {
        $this->hidden = true;
        return $this;
    }

    public function isHidden() {
        return $this->hidden;
    }

}