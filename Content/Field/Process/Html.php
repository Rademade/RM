<?php
class RM_Content_Field_Process_Html
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
            'src',
            'href',
            'alt',
            'height',
            'width',
            'align',
            'style',
            'target',
            'data-id',
            'data-align',
            'rel',
            'id',
            'name',
            'title',
            'class',
            'data-href',
            'data-js-link'
        ];
    }
}