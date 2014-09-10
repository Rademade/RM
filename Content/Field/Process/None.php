<?php
class RM_Content_Field_Process_None
    extends
        RM_Content_Field_Process {

    use RM_Content_Field_Process_Singleton;

    public function getInitialContent($html) {
        return $html;
    }

    public function getParsedContent($html) {
        return $html;
    }

}