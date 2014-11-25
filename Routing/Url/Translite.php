<?php
class RM_Routing_Url_Translite {

    private static $_needle;
    private static $_replace;

    private $string;

    function __construct($str) {
    	$this->string = $str;
    }

    private function transliterate_return($str) {
        return str_replace($this->getNeedle(), $this->getReplace(), $str);
    }

    private function getNeedle() {
        $this->_loadDictionary();
        return self::$_needle;
    }

    private function getReplace() {
        $this->_loadDictionary();
        return self::$_replace;
    }

    /**
     * Аа Бб Вв Гг Дд Ее Ёё Жж Зз Ии Йй Кк Лл Мм Нн Оо Пп Рр Сс Тт Уу Фф Хх Цц Чч Шш Щщ Ъъ Ыы Ьь Ээ Юю Яя
     */
    private function _loadDictionary() {

        if (self::$_needle) return;

        $f = [];
        $t = [];

        $f[] = 'а'; $t[] = 'a';
        $f[] = 'б'; $t[] = 'b';
        $f[] = 'в'; $t[] = 'v';
        $f[] = 'г'; $t[] = 'g';
        $f[] = 'д'; $t[] = 'd';
        $f[] = 'е'; $t[] = 'e';
        $f[] = 'ё'; $t[] = 'e';
        $f[] = 'ж'; $t[] = 'zh';
        $f[] = 'з'; $t[] = 'z';
        $f[] = 'и'; $t[] = 'i';
        $f[] = 'й'; $t[] = 'y';
        $f[] = 'к'; $t[] = 'k';
        $f[] = 'л'; $t[] = 'l';
        $f[] = 'м'; $t[] = 'm';
        $f[] = 'н'; $t[] = 'n';
        $f[] = 'о'; $t[] = 'o';
        $f[] = 'п'; $t[] = 'p';
        $f[] = 'р'; $t[] = 'r';
        $f[] = 'с'; $t[] = 's';
        $f[] = 'т'; $t[] = 't';
        $f[] = 'у'; $t[] = 'u';
        $f[] = 'ф'; $t[] = 'f';
        $f[] = 'х'; $t[] = 'h';
        $f[] = 'ц'; $t[] = 'c';
        $f[] = 'ч'; $t[] = 'ch';
        $f[] = 'ш'; $t[] = 'sh';
        $f[] = 'щ'; $t[] = 'shch';
        $f[] = 'ъ'; $t[] = '';
        $f[] = 'ы'; $t[] = 'y';
        $f[] = 'ь'; $t[] = '';
        $f[] = 'э'; $t[] = 'e';
        $f[] = 'ю'; $t[] = 'yu';
        $f[] = 'я'; $t[] = 'ya';

        $f[] = 'ђ'; $t[] = 'd';
        $f[] = 'љ'; $t[] = 'lj';
        $f[] = 'њ'; $t[] = 'nj';
        $f[] = 'ћ'; $t[] = 'c';
        $f[] = 'џ'; $t[] = 'dz';
        $f[] = 'ñ'; $t[] = 'n';
        $f[] = 'á'; $t[] = 'a';
        $f[] = 'í'; $t[] = 'i';
        $f[] = 'ú'; $t[] = 'u';

        $f[] = 'і'; $t[] = 'i';
        $f[] = 'ј'; $t[] = 'j';

        $f[] = 'ґ'; $t[] = 'g';
        $f[] = 'є'; $t[] = 'e';
        $f[] = 'ї'; $t[] = 'ї';

        self::$_needle = $f;
        self::$_replace = $t;
    }

    public function __toString() {
    	return $this->transliterate_return( $this->string );
    }
   
}