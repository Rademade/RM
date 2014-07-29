<?php
require_once 'Libraries/Html2text/html2text.php';

class RM_Content_Field_Process_PrettyText
	extends
        RM_Content_Field_Process {

	private static $_self;

    /**
     * @var Html2Text
     */
    private $_converter;

	public static function init() {
		if (!self::$_self instanceof static) {
            self::$_self = new static();
		}
		return self::$_self;
	}

	public function getInitialContent($html) {
		return nl2br($html);
	}

	public function getParsedContent($html) {
   		$html2text = $this->_getConverter();
		$html2text->set_html(htmlspecialchars_decode($html));
        try {
            return $html2text->get_text();
        } catch (Exception $e) {
			return '';
		}
	}

    private function _getConverter() {
        if (!$this->_converter instanceof Html2Text) {
            $this->_converter = new Html2Text('', false, [ 'width' => 0 ]);
        }
        return $this->_converter;
    }

}