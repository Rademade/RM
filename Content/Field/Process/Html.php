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
		'br',
        'iframe'
	);
	
	private $_allowedAttr = array(
		'src',
		'href',
		'alt',
		'height',
        'target',
		'width',
		'align',
        'frameborder',
        'allowfullscreen'
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

	public function getInitialContent($html) {
		return $html;
	}

	public function getParsedContent($html) {
        $XML = simplexml_load_string(
            '<root>'. $html .'</root>',
            'SimpleXMLElement',
            LIBXML_NOERROR | LIBXML_NOXMLDECL
        );
        /* @var SimpleXMLElement $XML */
        return ($XML instanceof SimpleXMLElement) ?
            strip_tags( preg_replace(
                $this->_getRemoveAttributeGreps( $XML ),
                array(''),
                $XML->asXML()
            ), $this->_getAllowedTags() ) : '';
	}

    private function _getRemoveAttributeGreps(SimpleXMLElement $XML) {
        $removeGreps = array();
        foreach ($XML->xpath('descendant::*[@*]') as $tag) {
            /* @var SimpleXMLElement $tag */
            foreach ($tag->attributes() as $name => $value) {
                if (!in_array($name, $this->_allowedAttr)) {
                    $tag->attributes()->{ $name } = '';
                    $removeGreps[$name] = '/ '. $name .'=""/';
                }
            }
        }
        return $removeGreps;
    }

    private function _getAllowedTags() {
        return '<' . join( '><', $this->allowedTags ) . '>';
    }

}