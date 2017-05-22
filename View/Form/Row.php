<?php
class RM_View_Form_Row {

    private $html = '';
    private $desc = '';
    private $hide = false;

    static private $i = 0;
    static private $n = 0;
    static private $j = null;

    const TPL = 'blocks/form/tr.phtml';

    public function setHTML($HTML) {
        $this->html = $HTML;
    }

    public function setDesc($desc) {
        $this->desc = $desc;
    }

    public function isHide() {
        return $this->hide;
    }

    public function hide() {
        $this->hide = true;
    }

    public static function startNewTranslation() {
        if (is_null(self::$j)) {
            self::$j = self::$i;
        }
        self::$i = self::$j;
    } 

    public function render() {
        if (!$this->isHide()) {
            ++self::$i;
        }
        ++self::$n;
        return Zend_Layout::getMvcInstance()->getView()->partial(self::TPL, array(
            'grey' => self::$i%2,
            'id' => self::$n,
            'html' => $this->html,
            'hide' => $this->isHide(),
            'name' => $this->desc
        ));
    }

}