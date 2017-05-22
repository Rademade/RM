<?php
require_once 'Libraries/HTMLPurifier/library/HTMLPurifier.safe-includes.php';

abstract class RM_Content_Field_Process_HtmlAbstract
    extends
        RM_Content_Field_Process {
    
    private $_allowedTags;
    private $_allowedAttributes;
    private $_allowedCssAttributes;
    
    /**
     * @var HTMLPurifier
     */
    private $_purifier;
    
    abstract protected function __loadAllowedTags();
    abstract protected function __loadAllowedCssAttributes();
    abstract protected function __loadAllowedAttributes();
    
    /**
     * @return HTMLPurifier_Config
     */
    public function getCurrentConfig() {
        return $this->__getPurifier()->config;
    }
    
    public function getInitialContent($html) {
        return $html;
    }
    
    public function getParsedContent($html) {
        $html = $this->__getPurifier()->purify( $html );
        return $html;
    }
    
    public function getAllowedTags() {
        if (!$this->_allowedTags) {
            $this->_allowedTags = $this->__loadAllowedTags();
        }
        return $this->_allowedTags;
    }
    
    public function getAllowedCssAttributes() {
        if (!$this->_allowedCssAttributes) {
            $this->_allowedCssAttributes = $this->__loadAllowedCssAttributes();
        }
        return $this->_allowedCssAttributes;
    }
    
    public function getAllowedAttributes() {
        if (!$this->_allowedAttributes) {
            $this->_allowedAttributes = $this->__loadAllowedAttributes();
        }
        return $this->_allowedAttributes;
    }
    
    /**
     * @return HTMLPurifier_Config
     */
    protected function __getConfig() {
        /* @var HTMLPurifier_Config $config */
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
        $config->set('HTML.Allowed', join(',', $this->getAllowedTags()));
        $config->set('HTML.AllowedAttributes', $this->getAllowedAttributes());
        $config->set('HTML.Attr.Name.UseCDATA', false);
        $config->set('CSS.AllowedProperties', $this->getAllowedCssAttributes());
        $config->set('Attr.AllowedFrameTargets', array('_blank'));
        $config->set('Attr.DefaultImageAlt', '');
        $config->set('Attr.IDBlacklist', array('pastemarkerend'));
        $config->set('Attr.EnableID', true);
        return $config;
    }
    
    protected function __getPurifier() {
        if (!$this->_purifier instanceof HTMLPurifier) {
            $this->_purifier = new HTMLPurifier( $this->__getConfig() );
        }
        return $this->_purifier;
    }

}