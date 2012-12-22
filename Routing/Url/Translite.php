<?php
class RM_Routing_Url_Translite {

    private $string;

    function __construct($str) {
    	$this->string = $str;
    }

    private function transliterate_return($str) {
        return str_replace($this->getNiddle(), $this->getReplace(), $str);
    }
    
    private function getNiddle() {
    	return array(
	    	"а", "б", "в", "г", "д", "ђ", "е", "ж", "з", "и", "ј", "к", "л", "љ", "м",
	    	"н", "њ", "о", "п", "р", "с", "т", "ћ", "у", "ф", "х", "ц", "ч", "џ", "ш",
    		'я', 'ь', 'ы', 'й', 'щ', 'ъ', 'ю', 'э', 'ё'
	    );
    }

    private function getReplace() {
	    return array (
	    	"a", "b", "v", "g", "d", "d", "e", "z", "z", "i", "j", "k", "l", "lj", "m",
	    	"n", "nj", "o", "p", "r", "s", "t", "c", "u", "f", "h", "c", "ch", "dz", "s",
	    	'ya', '', 'y', 'j', 'w', '', 'je', 'ju', 'jo'
	   	);		
    }

    public function __toString() {
    	return $this->transliterate_return( $this->string );
    }
   
}