<?php
class RM_Date_DatetimeLocale {

    const WEEKDAY_MONDAY = 1;
    const WEEKDAY_TUESDAY = 2;
    const WEEKDAY_WEDNESDAY = 3;
    const WEEKDAY_THURSDAY = 4;
    const WEEKDAY_FRIDAY = 5;
    const WEEKDAY_SATURDAY = 6;
    const WEEKDAY_SUNDAY = 0;

    const MONTH_JANUARY = 1;
    const MONTH_FEBRUARY = 2;
    const MONTH_MARCH = 3;
    const MONTH_APRIL = 4;
    const MONTH_MAY = 5;
    const MONTH_JUNE = 6;
    const MONTH_JULY = 7;
    const MONTH_AUGUST = 8;
    const MONTH_SEPTEMBER = 9;
    const MONTH_OCTOBER = 10;
    const MONTH_NOVEMBER = 11;
    const MONTH_DECEMBER = 12;

    protected $_view;
    protected $_tr;
    protected $_weekdays;
    protected $_months;
    protected $_declinationMonths;

    public function __construct() {
        $this->__initView();
        $this->__initTranslate();
    }

    public function getMonths() {
        if (empty($this->_months)) {
            $this->__initMonths();
        }
        return $this->_months;
    }

    public function getDeclinationMonths() {
        if (empty($this->_declinationMonths)) {
            $this->__initDeclinationMonths();
        }
        return $this->_declinationMonths;
    }

    public function getWeekdays() {
        if (empty($this->_weekdays)) {
            $this->__initWeekdays();
        }
        return $this->_weekdays;
    }

    public function getWeekdayName($weekday) {
        return $this->getWeekdays()[$weekday];
    }

    public function getMonthName($month) {
        return $this->getMonths()[$month];
    }

    public function getDeclinationMonthName($month) {
        return $this->getDeclinationMonths()[$month];
    }

    public function getNextWeekdayNumber($weekday) {
        return ($weekday + 1) % 7; //RM_TODO fix SUNDAY = 7
    }

    protected function __initView() {
        $this->_view = Zend_Layout::getMvcInstance()->getView();
    }

    protected function __initTranslate() {
        #rm_todo extract class RM_Translate::getDefault()
        $this->_tr = $this->_view->translate ?: new Zend_Translate([
            'adapter' => 'gettext',
            'disableNotices' => true
        ]);
    }

    protected function __initWeekdays() {
        $this->_weekdays = array(
            static::WEEKDAY_MONDAY    => $this->_tr->_('Понедельник'),
            static::WEEKDAY_TUESDAY   => $this->_tr->_('Вторник'),
            static::WEEKDAY_WEDNESDAY => $this->_tr->_('Среда'),
            static::WEEKDAY_THURSDAY  => $this->_tr->_('Четверг'),
            static::WEEKDAY_FRIDAY    => $this->_tr->_('Пятница'),
            static::WEEKDAY_SATURDAY  => $this->_tr->_('Суббота'),
            static::WEEKDAY_SUNDAY    => $this->_tr->_('Воскресенье')
        );
    }

    protected function __initMonths() {
        $this->_months = array(
            static::MONTH_JANUARY     => $this->_tr->_('Январь'),
            static::MONTH_FEBRUARY    => $this->_tr->_('Февраль'),
            static::MONTH_MARCH       => $this->_tr->_('Март'),
            static::MONTH_APRIL       => $this->_tr->_('Апрель'),
            static::MONTH_MAY         => $this->_tr->_('Май'),
            static::MONTH_JUNE        => $this->_tr->_('Июнь'),
            static::MONTH_JULY        => $this->_tr->_('Июль'),
            static::MONTH_AUGUST      => $this->_tr->_('Август'),
            static::MONTH_SEPTEMBER   => $this->_tr->_('Сентябрь'),
            static::MONTH_OCTOBER     => $this->_tr->_('Октябрь'),
            static::MONTH_NOVEMBER    => $this->_tr->_('Ноябрь'),
            static::MONTH_DECEMBER    => $this->_tr->_('Декабрь')
        );
    }

    protected function __initDeclinationMonths() {
        $this->_declinationMonths = array(
            static::MONTH_JANUARY     => $this->_tr->_('января'),
            static::MONTH_FEBRUARY    => $this->_tr->_('февраля'),
            static::MONTH_MARCH       => $this->_tr->_('марта'),
            static::MONTH_APRIL       => $this->_tr->_('апреля'),
            static::MONTH_MAY         => $this->_tr->_('мая'),
            static::MONTH_JUNE        => $this->_tr->_('июня'),
            static::MONTH_JULY        => $this->_tr->_('июля'),
            static::MONTH_AUGUST      => $this->_tr->_('августа'),
            static::MONTH_SEPTEMBER   => $this->_tr->_('сентября'),
            static::MONTH_OCTOBER     => $this->_tr->_('октября'),
            static::MONTH_NOVEMBER    => $this->_tr->_('ноября'),
            static::MONTH_DECEMBER    => $this->_tr->_('декабря')
        );
    }

}