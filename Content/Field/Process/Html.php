<?php
require_once 'Libraries/HTMLPurifier/library/HTMLPurifier.auto.php';
class RM_Content_Field_Process_Html
	extends
        RM_Content_Field_Process {

	private $_allowedTags = array(
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
		'width',
		'align',
        'target',
        'style'
	);

    private $_allowedCssAttr = array(
        'text-align'
    );

	private static $_self;

    /**
     * @var HTMLPurifier
     */
    private $_purifier;

	public static function init() {
		if (!self::$_self instanceof static) {
            self::$_self = new static();
		}
		return self::$_self;
	}

	public function getInitialContent($html) {
		return $html;
	}

	public function getParsedContent($html) {
        $html = $this->getPurifier()->purify( $html );
        return $html;
	}

    /**
     * @return HTMLPurifier_Config
     */
    private function _getConfig() {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', join(',', $this->_allowedTags));
        $config->set('HTML.AllowedAttributes', $this->_allowedAttr);
        $config->set('CSS.AllowedProperties', $this->_allowedCssAttr);
        $config->set('Attr.AllowedFrameTargets', array('_blank'));
        return $config;
    }

    private function getPurifier() {
        if (!$this->_purifier instanceof HTMLPurifier) {
            $this->_purifier = new HTMLPurifier( $this->_getConfig() );
        }
        return $this->_purifier;
    }

}