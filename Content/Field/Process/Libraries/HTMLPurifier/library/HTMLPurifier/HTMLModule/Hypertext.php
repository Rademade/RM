<?php

/**
 * XHTML 1.1 Hypertext Module, defines hypertext links. Core Module.
 */
class HTMLPurifier_HTMLModule_Hypertext extends HTMLPurifier_HTMLModule
{

    public $name = 'Hypertext';

    public function setup($config) {
        $a = $this->addElement(
            'a', 'Inline', 'Inline', 'Common',
            array(
                // 'accesskey' => 'Character',
                // 'charset' => 'Charset',
                'name' => 'Text',
                'data-href' => 'Text',
                'href' => 'URI',
                // 'hreflang' => 'LanguageCode',
                'rel' => 'Text',
                'rev' => new HTMLPurifier_AttrDef_HTML_LinkTypes('rev'),
                // 'tabindex' => 'Number',
                // 'type' => 'ContentType',
            )
        );
        $a->formatting = true;
        $a->excludes = array('a' => true);
    }

}

// vim: et sw=4 sts=4
