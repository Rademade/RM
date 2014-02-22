<?php
use RM_Date_Time as RM_Time;
use RM_Date_Datetime as RM_Datetime;

class RM_Date_WeekdayPeriod {

    protected $_weekday;

    /**
     * @var RM_Date_Time
     */
    protected $_timeBegin;

    /**
     * @var RM_Date_Time
     */
    protected $_timeEnd;

    public function __construct($weekday, $timeBegin, $timeEnd) {
        $this->setWeekday($weekday);
        $this->_timeBegin = RM_Time::fromString($timeBegin);
        $this->_timeEnd = RM_Time::fromString($timeEnd);
    }

    public function setWeekday($weekday) {
        $this->_validateWeekday($weekday);
        $this->_weekday = $weekday;
        return $this;
    }

    public function setTimeBegin(RM_Time $time) {
        $this->_timeBegin = $time;
        return $this;
    }

    public function setTimeEnd(RM_Time $time) {
        $this->_timeEnd = $time;
        return $this;
    }

    public function getWeekday() {
        return $this->_weekday;
    }

    public function getTimeBegin() {
        return $this->_timeBegin;
    }

    public function getTimeEnd() {
        return $this->_timeEnd;
    }

    public function includesDate(RM_Date_Datetime $date) {
        return $this->getWeekday() == $date->getWeekday();
    }

    public function includesTime(RM_Time $time) {
        if ( $this->getTimeBegin()->greater($this->getTimeEnd()) ) {
            return $this->getTimeBegin()->lesserEqual($time) || $time->lesser($this->getTimeEnd());
        } else {
            return $time->between($this->getTimeBegin(), $this->getTimeEnd());
        }
    }

    private function _validateWeekday($weekday) {
        $weekdays = RM_Datetime::getLocale()->getWeekdays();
        if ( !isset($weekdays[$weekday]) ) {
            throw new Exception('Invalid weekday');
        }
    }

}