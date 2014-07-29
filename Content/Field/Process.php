<?php
abstract class RM_Content_Field_Process {

	const PROCESS_TYPE_HTML = 1;
	const PROCESS_TYPE_TEXT = 2;
	const PROCESS_TYPE_LINE = 3;
    const PROCESS_TYPE_NONE = 4;
	const PROCESS_TYPE_PRETTY_TEXT = 5;

	protected function __construct() {}

	public static function getByType( $type ) {
        switch ($type) {
            case self::PROCESS_TYPE_HTML:
                return RM_Content_Field_Process_Html::init();
            case self::PROCESS_TYPE_TEXT:
                return RM_Content_Field_Process_Text::init();
            case self::PROCESS_TYPE_LINE:
                return RM_Content_Field_Process_Line::init();
            case self::PROCESS_TYPE_NONE:
                return RM_Content_Field_Process_None::init();
			case self::PROCESS_TYPE_PRETTY_TEXT:
                return RM_Content_Field_Process_PrettyText::init();
            default:
                throw new Exception('Wrong process type asked');
        }
	}
	
	abstract public function getInitialContent($html);
	abstract public function getParsedContent($html);

}