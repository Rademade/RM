<?php
class RM_Tel {

    /**
     * @link http://rubular.com/r/RMQU3IENhr
     * @link http://rubular.com/r/L43rX8cdra
     * @link http://rubular.com/r/u4uGex1H4G
     * @link http://rubular.com/r/wVaWr33xcI
     * @link http://rubular.com/r/gsItjWEPsh
     */
    public static $validationRegex = '/^\+\d{1,3}(([\-\s]|\s{0,2})((\(\d{2,3}\))|(\d{2,3}))([\-\s]|\s{0,2}))?\d{2,3}([\-\s]|\s{0,2})\d{2,3}([\-\s]|\s{0,2})\d{2,3}$/';

    public static $junkRegex = '/\D/';

    /**
     * @var string
     */
    protected $_number;

    public function __construct($number) {
        $this->setNumber($number);
    }

    /**
     * @param $number
     * @return bool
     */
    public static function isValid($number) {
        return (bool)preg_match(static::$validationRegex, $number);
    }

    /**
     * @param $number
     * @return string
     */
    public static function cleanup($number) {
        return '+' . preg_replace(static::$junkRegex, '', $number);
    }

    /**
     * @link http://www.phpliveregex.com/p/5Mf
     * @param $number
     * @return string
     */
    public static function prettyFormat($number) {
        return trim(preg_replace(
            '/^(\+(8|7|[0-9]{2}))?([0-9]{3})([0-9]*)([0-9]{2})([0-9]{2})$/',
            '${1} (${3}) ${4} ${5} ${6}',
            static::cleanup($number)
        ));
    }
    
    /**
     * @return string
     */
    public function getNumber() {
        return $this->_number;
    }

    /**
     * @param string $number
     * @return $this
     */
    public function setNumber($number) {
        $this->_number = static::cleanup($number);
        return $this;
    }

    /**
     * @return string
     */
    public function getPrettyNumber() {
        return static::prettyFormat($this->getNumber());
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function validate() {
        $num = $this->getNumber();
        if (!static::isValid($num)) {
            throw new Exception("String '$num' seems not to be valid phone number");
        }
        return true;
    }

    public function __toString() {
        return $this->getNumber();
    }

}