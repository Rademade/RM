<?php
class RM_Content_Field_Process_Line
	extends RM_Content_Field_Process {

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
		
	public function getInitialContent($html) {
		return htmlspecialchars_decode( $html );
	}
	
	private function removeNewLine($string) {
		$string = str_replace("\n", "", $string);
		$string = str_replace("\r", "", $string);
		return $string;
	}
	
	public function getParsedContent($html) {
		$html = htmlspecialchars( trim($html) );
		$html = $this->removeNewLine( $html );
		return trim($html);
	}

}