<?php
class RM_Content_Field_Process_Libraries_StripAttributes {

    public $str = '';
    public $allow = array ();
    public $exceptions = array ();
    public $ignore = array ();
    
    public function strip($str) {
        $this->str = $str;
        if (is_string ( $str ) && strlen ( $str ) > 0) {
            $res = $this->findElements ();
            if (is_string ( $res ))
                return $res;
            $nodes = $this->findAttributes ( $res );
            $this->removeAttributes ( $nodes );
        }
        return $this->str;
    }
    
    private function findElements() {
        $nodes = array();
        preg_match_all ( "/<([^ !\/\>\n]+)([^>]*)>/i", $this->str, $elements );
        foreach ( $elements [1] as $el_key => $element ) {
            if ($elements [2] [$el_key]) {
                $literal = $elements [0] [$el_key];
                $element_name = $elements [1] [$el_key];
                $attributes = $elements [2] [$el_key];
                if (is_array ( $this->ignore ) && ! in_array( $element_name, $this->ignore ))
                    $nodes[] = array (
                        'literal' => $literal,
                        'name' => $element_name,
                        'attributes' => $attributes
                    );
            }
        }
        return (empty($nodes)) ? $this->str : $nodes;
    }
    
    private function findAttributes($nodes) {
        foreach( $nodes as &$node ) {
            preg_match_all( "/([^ =]+)\s*=\s*[\"|']{0,1}([^\"']*)[\"|']{0,1}/i", $node['attributes'], $attributes );
            if( $attributes[1] ) {
                foreach( $attributes[1] as $att_key => $att ) {
                    $literal = $attributes[0][$att_key];
                    $attribute_name = $attributes[1][$att_key];
                    $value = $attributes[2][$att_key];
                    $atts[] = array(
                        'literal' => $literal,
                        'name' => $attribute_name,
                        'value' => $value
                    );
                }
            } else {
                $node['attributes'] = null;
            }
            $node['attributes'] = isset($atts) ? $atts : null;
            unset( $atts );
        }
    
        return $nodes;
    }
    
    private function removeAttributes($nodes) {
        foreach ( $nodes as $node ) {// Check if node has any attributes to be kept
            $node_name = $node ['name'];
            $new_attributes = '';
            if (is_array ( $node ['attributes'] )) {
                foreach ( $node ['attributes'] as $attribute ) {
                    if ((is_array ( $this->allow ) &&
                        in_array ( $attribute ['name'], $this->allow )) ||
                        $this->isException (
                            $node_name,
                            $attribute['name'],
                            $this->exceptions
                        )
                    ) {
                        $new_attributes = $this->createAttributes(
                            $new_attributes,
                            $attribute['name'],
                            $attribute['value']
                        );
                    }
                }
            }
            $replacement = ($new_attributes) ? "<$node_name $new_attributes>" : "<$node_name>";
            $this->str = preg_replace( '/' . preg_quote( $node['literal'], '/' ) . '/', $replacement, $this->str );
        }
    
    }
    
    private function isException($element_name, $attribute_name, $exceptions) {
        if (array_key_exists ($element_name, $this->exceptions )) {
            if (in_array ( $attribute_name, $this->exceptions [$element_name] ))
                return true;
        }
        return false;
    }
    
    private function createAttributes($new_attributes, $name, $value) {
        if ($new_attributes)
            $new_attributes .= " ";
        $new_attributes .= "$name=\"$value\"";
        return $new_attributes;
    }

}