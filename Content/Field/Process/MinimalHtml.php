<?php
class RM_Content_Field_Process_MinimalHtml
	extends
        RM_Content_Field_Process_HtmlAbstract {

    use RM_Content_Field_Process_Singleton;

    protected function __loadAllowedTags() {
        return [
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6', 
            'span',
            'p',
            'div',
            'b',
            'strong',
            'i',
            'em',
            'u',
            'br',
        ];
    }

    protected function __loadAllowedCssAttributes() {
        return [
            'text-align',
            'float'
        ];
    }

    protected function __loadAllowedAttributes() {
        return [
            'align',
            'style',
            'data-id',
            'data-align',
            'rel',
            'id',
            'name',
            'title'
        ];
    }

     /**
     * @return HTMLPurifier_Config
     */
    protected function __getConfig() {
        $config = parent::__getConfig();
        $config->set('URI.Disable', true);
        $config->set('URI.DisableExternal', true);
        $config->set('URI.DisableExternalResources', true);
        $config->set('URI.DisableResources', true);
        return $config;
    }

}