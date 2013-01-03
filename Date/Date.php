<?php 
class RM_Date_Date {
	
	private $_year;
	private $_month;
	private $_day;
	
	const DAY = 86400;

	const ISO_DATE = 1;         // Y-m-d
	const SEARCH_DATE = 2;      // d.m.Y
	const STRONG_DATE = 3;      // d/m/Y

	public function __construct( $year, $month, $day ) {
		$this->_year = (int)$year;
		$this->_month = (int)$month;
		$this->_day = (int)$day;
	}
	
	public static function initFromDate($format, $date) {
		$format = (int)$format;
		if (self::isTrueFormat($format)) {
			if (preg_match(self::_getParseDatePreg($format), $date, $p)) {
				return self::_arrayInit($format, $p);
			} else {
				throw new Exception('Wrong date format given');
			}
		} else {
			throw new Exception('Wrong date format given');
		}
	}
	
	public static function initFromTimestamp($t) {
		return new self(
			date('Y', $t),
			date('m', $t),
			date('d', $t)
		);
	}

	public function getDay() {
		return $this->_day;
	}

	public function setDay($day) {
		$this->_day = (int)$day;
	}

	public function getMonth() {
		return $this->_month;
	}

	public function setMonth($month) {
		$this->_month = (int)$month;
	}

	public function getYear() {
		return $this->_year;
	}

	public function setYear($year) {
		$this->_year = (int)$year;
	}

	public function isPast() {
		return $this->getTimestamp() < time();
	}

	public function isMore(RM_Date_Date $then) {
		return $this->getTimestamp() >= $then->getTimestamp();
	}

	public function minusDay() {
		$prev = self::initFromTimestamp( $this->getTimestamp() - self::DAY );
		$this->setYear( $prev->getYear() );
		$this->setMonth( $prev->getMonth() );
		$this->setDay( $prev->getDay() );
		return $this;
	}

	public function addDay() {
		$next = self::initFromTimestamp( $this->getTimestamp() + self::DAY + 3600 );//TODO need fix february bug
		$this->setYear( $next->getYear() );
		$this->setMonth( $next->getMonth() );
		$this->setDay( $next->getDay() );
		return $this;
	}

	public static function isTrueFormat($format) {
		return (in_array($format, array(
			self::ISO_DATE,
		    self::SEARCH_DATE,
		    self::STRONG_DATE
        )));
	}

	private static function _arrayInit($format, array $p) {
		switch ($format) {
			case self::ISO_DATE:
				return new self($p[1], $p[2], $p[3]);
			case self::SEARCH_DATE:
				return new self($p[3], $p[2], $p[1]);
			case self::STRONG_DATE:
				return new self($p[3], $p[2], $p[1]);
		}
	}

	private static function _getParseDatePreg($format) {
		$day = '([0-3]?[0-9]{1})';
		$month = '([0-1]?[0-9]{1})';
		$year = '([0-9]{4})';
		switch ($format) {
			case self::ISO_DATE:
				return '/^' . $year . '\-' . $month . '\-' . $day . '$/';
			case self::SEARCH_DATE:
				return '/^' . $day . '\.' . $month . '\.' . $year . '$/';
			case self::STRONG_DATE:
				return '/^' . $day . '\/' . $month . '\/' . $year . '$/';
		}
	}
	
	public function getDate($format = self::ISO_DATE) {
		switch ($format) {
			case self::ISO_DATE:
				return date('Y-m-d', $this->getTimestamp());
			case self::SEARCH_DATE:
				return date('d.m.Y', $this->getTimestamp());
			case self::STRONG_DATE:
				return date('d/m/Y', $this->getTimestamp());
		}
	}

	public function getRangeDaysCount(RM_Date_Date $fromDate) {
		return floor( ( $this->getTimestamp() - $fromDate->getTimestamp() ) / self::DAY );
	}

	public function getTimestamp() {
		return mktime(
			0,
			0,
			0,
			$this->_month,
			$this->_day,
			$this->_year
		);
	}
	
}