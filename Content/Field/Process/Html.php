<?php
class RM_Content_Field_Process_Html
	extends RM_Content_Field_Process {

	private $allowedTags = array(
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6', 
		'a',
		'span',
		'p',
		'div',
		'ul',
		'li',
		'ol',
		'b',
		'strong',
		'i',
		'em',
		'u',
		'img',
		'table',
		'tr',
		'th',
		'td',
		'thead',
		'tbody',
		'tfoot',
		'br'
	);
	
	private $allowedAttr = array(
		'src',
		'class',
		'style',
		'href',
		'alt',
		'height',
		'width',
		'align'
	);

	private static $_self;

	protected function __construct(){
		parent::__construct();
	}

	public static function init() {
		if (!(self::$_self instanceof self)) {
			self::$_self = new self();
		}
		return self::$_self;
	}
	
	private function stripWrongHtml($html) {
		return strip_tags(
			$html,
			'<' . join( '><', $this->allowedTags ) . '>'
		);
	}

	private function stripWrongAttr($html) {
		$stripAttr = new RM_Content_Field_Process_Libraries_StripAttributes();
		$stripAttr->allow = $this->allowedAttr;
		return $stripAttr->strip( $html );
	}

	public function getInitialContent($html) {
		return $html;
	}

	public function getParsedContent($html) {
		$html = $this->stripWrongHtml($html);
		$html = $this->stripWrongAttr($html);
		return $html;
	}

}