<?php
abstract class RM_Content_Field_Process {

	const PROCESS_TYPE_HTML = 1;
	const PROCESS_TYPE_TEXT = 2;
	const PROCESS_TYPE_LINE = 3;

	protected function __construct() {}

	public static function getByType( $type ) {
		if (self::isTypeExist($type)) {
			switch ($type) {
				case self::PROCESS_TYPE_HTML:
					return RM_Content_Field_Process_Html::init();
				case self::PROCESS_TYPE_TEXT:
					return RM_Content_Field_Process_Text::init();
				case self::PROCESS_TYPE_LINE:
					return RM_Content_Field_Process_Line::init();
			}
		} else {
			throw new Exception('Wrong process type asked');
		}
	}
	
	private static function isTypeExist($type) {
		$type = (int)$type;
		return in_array($type, array(
			self::PROCESS_TYPE_HTML,
			self::PROCESS_TYPE_TEXT,
			self::PROCESS_TYPE_LINE
		));
	}

	abstract public function getInitialContent($html);
	abstract public function getParsedContent($html);

}