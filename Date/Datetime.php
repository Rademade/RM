<?php
class RM_Date_Datetime
    extends
        DateTime {

    const SHORT_DATE_FORMAT = 'd.m.Y';
    const SEARCH_DATE_FORMAT = 'Y-m-d';
    const FULL_DATE_DELIMITER = ' ';
    const SHORT_TIME_FORMAT = 'H:i';
    const FULL_TIME_FORMAT = 'H:i:s';

    /**
     * @var RM_Date_DatetimeLocale
     */
    private static $_locale;

    public function __construct($value = 'now', $timezone = null) {
        if ( is_string($value) ) {
            parent::__construct($value, $timezone);
        } else {
            parent::__construct('now', $timezone);
            if ( is_int($value) ) {
                $this->setTimestamp($value);
            }
        }
    }

    public static function now() {
        return new self();
    }

    public static function create($value, $timezone) {
        return new self($value, $timezone);
    }

    public static function fromString($datetime) {
        return new self($datetime);
    }

    public static function fromTimestamp($timestamp) {
        return new self((int)$timestamp);
    }

    public static function getLocale() {
        if (!self::$_locale) {
            self::__initDatetimeLocale();
        }
        return self::$_locale;
    }

    public function getShortDate() {
        return $this->format(self::SHORT_DATE_FORMAT);
    }

    public function getFullDate($year = true, $delimiter = self::FULL_DATE_DELIMITER) {
        $pieces = array(
            $this->format('j'),
            $this->getDeclinationMonthName()
        );
        if ($year) {
            $pieces[] = $this->format('Y');
        }
        return join($delimiter, $pieces);
    }

    public function getSearchDate() {
        return $this->format(self::SEARCH_DATE_FORMAT);
    }

    public function getTime($seconds = false) {
        return $this->format($seconds ? self::FULL_TIME_FORMAT : self::SHORT_TIME_FORMAT);
    }

    public function getShortDatetime($seconds = false) {
        return $this->getShortDate() . ' ' . $this->getTime($seconds);
    }

    public function getFullDatetime($seconds = false) {
        return $this->getFullDate() . ' ' . $this->getTime($seconds);
    }

    public function getSearchDatetime() {
        return $this->getSearchDate() . ' ' . $this->getTime(true);
    }

    public function getSeconds() {
        return (int)$this->format('s');
    }

    public function getMinutes() {
        return (int)$this->format('i');
    }

    public function getHours() {
        return (int)$this->format('H');
    }

    public function getDay() {
        return (int)$this->format('j');
    }

    public function getWeekday() {
        return (int)$this->format('w');
    }

    public function getMonth() {
        return (int)$this->format('m');
    }

    public function getYear() {
        return (int)$this->format('Y');
    }

    public function getWeekdayName() {
        return self::getLocale()->getWeekdayName($this->getWeekday());
    }

    public function getMonthName() {
        return self::getLocale()->getMonthName($this->getMonth());
    }

    public function getDeclinationMonthName() {
        return self::getLocale()->getDeclinationMonthName($this->getMonth());
    }

    public function getTimestampOfDate() {
        return $this->getTimestamp() - $this->getTimestampOfTime();
    }

    public function getTimestampOfTime() {
        return $this->getHours() * RM_Date_Time::HOUR + $this->getMinutes() * RM_Date_Time::MINUTE + $this->getSeconds();
    }

    /**
     * @param $count
     * @return RM_Date_Datetime
     */
    public function addYears($count) {
        return $this->add(new DateInterval('P' . $count . 'Y'));
    }

    /**
     * @param $count
     * @return RM_Date_Datetime
     */
    public function addMonths($count) {
        return $this->add(new DateInterval('P' . $count . 'M'));
    }

    /**
     * @param $count
     * @return RM_Date_Datetime
     */
    public function addDays($count) {
        return $this->add(new DateInterval('P' . $count . 'D'));
    }

    /**
     * @param $count
     * @return RM_Date_Datetime
     */
    public function addHours($count) {
        return $this->add(new DateInterval('PT' . $count . 'H'));
    }

    /**
     * @param $count
     * @return RM_Date_Datetime
     */
    public function addMinutes($count) {
        return $this->add(new DateInterval('PT' . $count . 'M'));
    }

    /**
     * @param $count
     * @return RM_Date_Datetime
     */
    public function addSeconds($count) {
        return $this->add(new DateInterval('PT' . $count . 'S'));
    }

    public function addYear() {
        return $this->addYears(1);
    }

    public function addMonth() {
        return $this->addMonths(1);
    }

    public function addDay() {
        return $this->addDays(1);
    }

    public function addHour() {
        return $this->addHours(1);
    }

    /**
     * @param $count
     * @return RM_Date_Datetime
     */
    public function subDays($count) {
        return $this->sub(new DateInterval('P' . $count . 'D'));
    }

    public function subDay() {
        return $this->subDays(1);
    }

    public function resetTime() {
        $this->setTime(0, 0, 0);
        return $this;
    }

    public function setDate($day, $month, $year) {
        parent::setDate($year, $month, $day);
        return $this;
    }

    public function getTimeInstance() {
        return RM_Date_Time::fromTimestamp( $this->getTimestampOfTime() );
    }

    public function toString() {
        return $this->getShortDate();
    }

    public function __toString() {
        return $this->toString();
    }

    public function setDay($day) {
        $this->setDate($day, $this->getMonth(), $this->getYear());
        return $this;
    }

    protected static function __initDatetimeLocale() {
        self::$_locale = new RM_Date_DatetimeLocale();
    }

}