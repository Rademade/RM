<?php
class RM_Date_Time {

    const HOUR = 3600;
    const MINUTE = 60;

    private $_timestamp;

    public function __construct($hours = 0, $minutes = 0, $seconds = 0) {
        $this->_timestamp = $hours * self::HOUR + $minutes * self::MINUTE + $seconds;
    }

    /**
     * @return RM_Date_Time
     */
    public static function now() {
        $now = RM_Date_Datetime::now();
        return new self($now->getHours(), $now->getMinutes(), $now->getSeconds());
    }

    /**
     * @param int $time
     * @return RM_Date_Time
     */
    public static function fromString($time) {
        $tokens = explode(':', $time) + array(0, 0, 0);
        return new self($tokens[0], $tokens[1], $tokens[2]);
    }

    /**
     * @param int $timestamp
     * @return RM_Date_Time
     */
    public static function fromTimestamp($timestamp) {
        return (new self(0, 0, 0))->setTimestamp($timestamp);
    }

    /**
     * @param int $timestamp
     * @return RM_Date_Time
     */
    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
        return $this;
    }

    public function setHours($count) {
        return $this->subHours($this->getHours())->addHours($count % self::MINUTE);
    }

    public function setMinutes($count) {
        return $this->subMinutes($this->getMinutes())->addMinutes($count % self::MINUTE);
    }

    public function setSeconds($count) {
        return $this->subSeconds($this->getSeconds())->addSeconds($count % self::MINUTE);
    }

    public function getHours() {
        return (int)($this->_timestamp / self::HOUR);
    }

    public function getMinutes() {
        return (int)($this->_timestamp % self::HOUR / self::MINUTE);
    }

    public function getSeconds() {
        return $this->_timestamp % self::HOUR % self::MINUTE;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function addHours($count) {
        $this->_timestamp += $count * self::HOUR;
        return $this;
    }

    public function addMinutes($count) {
        $this->_timestamp += $count * self::MINUTE;
        return $this;
    }

    public function addSeconds($count) {
        $this->_timestamp += $count;
        return $this;
    }

    public function subHours($count) {
        $this->_timestamp -= $count * self::HOUR;
        return $this;
    }

    public function subMinutes($count) {
        $this->_timestamp -= $count * self::MINUTE;
        return $this;
    }

    public function subSeconds($count) {
        $this->_timestamp -= $count;
        return $this;
    }

    public function toArray() {
        return array(
            $this->_addLeadingZero($this->getHours()),
            $this->_addLeadingZero($this->getMinutes()),
            $this->_addLeadingZero($this->getSeconds())
        );
    }

    public function toShortString() {
        $array = $this->toArray();
        array_pop($array);
        return join(':', $array);
    }

    public function toFullString() {
        return join(':', $this->toArray());
    }

    public function toString() {
        return $this->toShortString();
    }

    public function __toString() {
        return $this->toString();
    }

    public function round() {
        $this->setSeconds(0);
        if ($this->getMinutes() > 30) {
            $this->addMinutes(60 - $this->getMinutes());
        } elseif ($this->getMinutes() > 0) {
            $this->addMinutes(30 - $this->getMinutes());
        }
        return $this;
    }

    private function _addLeadingZero($value) {
        return ($value < 10 ? '0' : '') . $value;
    }

}