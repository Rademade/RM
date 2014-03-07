<?php
class RM_Date_Time
    implements
        JsonSerializable {

    const HOURS_PER_DAY = 24;
    const MINUTES_PER_HOUR = 60;
    const SECONDS_PER_HOUR = 3600;
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_DAY = 86400;

    const LAST_HOUR = 23;
    const LAST_MINUTE = 59;
    const LAST_SECOND = 59;

    const MOON_00 = 1;
    const MOON_24 = 2;

    private $_timestamp;

    public function __construct($hours = 0, $minutes = 0, $seconds = 0) {
        $this->_timestamp = $hours * self::SECONDS_PER_HOUR + $minutes * self::SECONDS_PER_MINUTE + $seconds;
        $this->_correctTimestamp();
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
        return $this->subHours($this->getHours())->addHours($count % self::SECONDS_PER_MINUTE);
    }

    public function setMinutes($count) {
        return $this->subMinutes($this->getMinutes())->addMinutes($count % self::SECONDS_PER_MINUTE);
    }

    public function setSeconds($count) {
        return $this->subSeconds($this->getSeconds())->addSeconds($count % self::SECONDS_PER_MINUTE);
    }

    public function getHours() {
        return (int)($this->_timestamp / self::SECONDS_PER_HOUR);
    }

    public function getMinutes() {
        return (int)($this->_timestamp % self::SECONDS_PER_HOUR / self::SECONDS_PER_MINUTE);
    }

    public function getSeconds() {
        return $this->_timestamp % self::SECONDS_PER_HOUR % self::SECONDS_PER_MINUTE;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function addHours($count) {
        $this->_timestamp += $count * self::SECONDS_PER_HOUR;
        return $this->_correctTimestamp();
    }

    public function addMinutes($count) {
        $this->_timestamp += $count * self::SECONDS_PER_MINUTE;
        return $this->_correctTimestamp();
    }

    public function addSeconds($count) {
        $this->_timestamp += $count;
        return $this->_correctTimestamp();
    }

    public function subHours($count) {
        $this->_timestamp -= $count * self::SECONDS_PER_HOUR;
        return $this->_correctTimestamp();
    }

    public function subMinutes($count) {
        $this->_timestamp -= $count * self::SECONDS_PER_MINUTE;
        return $this->_correctTimestamp();
    }

    public function subSeconds($count) {
        $this->_timestamp -= $count;
        return $this->_correctTimestamp();
    }

    public function toArray($moon = self::MOON_00) {
        if ($moon == self::MOON_24) {
            $isEndOfDay = $this->isEndOfDay();
            $parts = array(
                $isEndOfDay ? 24 : $this->getHours(),
                $isEndOfDay ? 0 : $this->getMinutes(),
                $isEndOfDay ? 0 : $this->getSeconds()
            );
        } else {
            $parts = array(
                $this->getHours(),
                $this->getMinutes(),
                $this->getSeconds()
            );
        }
        foreach ($parts as &$part) {
            $part = $this->_addLeadingZero($part);
        }
        return $parts;
    }

    public function toShortString($moon = self::MOON_00) {
        $array = $this->toArray($moon);
        array_pop($array);
        return join(':', $array);
    }

    public function toFullString($moon = self::MOON_00) {
        return join(':', $this->toArray($moon));
    }

    public function toString($moon = self::MOON_00) {
        return $this->toShortString($moon);
    }

    public function round() {
        $this->setSeconds(0);
        if ($this->getMinutes() > 30) {
            $this->addMinutes(self::MINUTES_PER_HOUR - $this->getMinutes());
        } elseif ($this->getMinutes() > 0) {
            $this->addMinutes(30 - $this->getMinutes());
        }
        return $this;
    }

    public function lesser(RM_Date_Time $other) {
        return $this->getTimestamp() < $other->getTimestamp();
    }

    public function greater(RM_Date_Time $other) {
        return $this->getTimestamp() > $other->getTimestamp();
    }

    public function equal(RM_Date_Time $other) {
        return $this->getTimestamp() == $other->getTimestamp();
    }

    public function lesserEqual(RM_Date_Time $other) {
        return $this->lesser($other) || $this->equal($other);
    }

    public function greaterEqual(RM_Date_Time $other) {
        return $this->greater($other) || $this->equal($other);
    }

    public function between(RM_Date_Time $lhs, RM_Date_Time $rhs) {
        return $this->greaterEqual($lhs) && $this->lesser($rhs);
    }

    public function isLastHour() {
        return $this->getHours() == self::LAST_HOUR;
    }

    public function isLastMinute() {
        return $this->getMinutes() == self::LAST_MINUTE;
    }

    public function isLastSecond() {
        return $this->getSeconds() == self::LAST_SECOND;
    }

    public function isBeginOfDay() {
        return !$this->getHours() && !$this->getMinutes();
    }

    public function isEndOfDay() {
        return $this->isLastHour() && $this->isLastMinute();
    }

    public function __clone() {
        return self::fromTimestamp($this->getTimestamp());
    }

    public function __toString() {
        return $this->toString();
    }

    public function jsonSerialize() {
        return $this->toString();
    }

    private function _addLeadingZero($value) {
        return ($value < 10 ? '0' : '') . $value;
    }

    private function _correctTimestamp() {
        $this->_timestamp = $this->_timestamp % self::SECONDS_PER_DAY;
        return $this;
    }

}