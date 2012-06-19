<?php
class RM_View_Element_Icon
	extends RM_View_Element {

	private $_iconName;
	private $_iconType;
	
	const ICON_SETTINGS = 1;
	const ICON_BLOCKS = 2;
	const ICON_OPTIONS = 3;
	const ICON_STIKER = 4;
	const ICON_HOUSE = 5;
	const ICON_DIGG = 6;
	const ICON_BED = 7;
	const ICON_TIME = 8;
	const ICON_GALLERY = 9;
	const ICON_USER = 10;
	const ICON_VOTE = 11;
	const ICON_BOOK = 12;
	const ICON_STAR = 13;
	const ICON_NEW = 14;
	const ICON_COMMENTS = 15;
	const ICON_RECOMMEND = 16;
    const ICON_ANSWER = 17;
    const ICON_RIGHT_BLOCK = 18;
    const ICON_TEXT_BLOCK = 19;
    const ICON_TEXT_FIELD = 20;
	
	public function __construct(
		$routeName,
		array $routeData,
		$descriptions,
		$iconType
	) {
		parent::__construct($routeName, $routeData);
		$this->setName( $descriptions );
		$this->setIconType( $iconType );
	}

	public function setName($name) {
		$this->_iconName = $name;
	}
	
	public function setIconType($type) {
		$this->_iconType = $type;
	}
	
	public function getIconType() {
		return $this->_iconType;
	}
		
	public function getName() {
		return $this->_iconName;
	}
	
	public function getIcon() {
		switch ( $this->getIconType() ) {
			case self::ICON_SETTINGS:
				return 'settings.png';
			case self::ICON_OPTIONS:
				return 'tag.png';
			case self::ICON_BLOCKS:
				return 'text.png';
			case self::ICON_HOUSE:
				return 'house.png';
			case self::ICON_STIKER:
				return 'stiker.png';
			case self::ICON_DIGG:
				return 'digg.png';
			case self::ICON_BED:
				return 'bed.png';
			case self::ICON_TIME:
				return 'time.png';
			case self::ICON_GALLERY:
				return 'gallery.png';
			case self::ICON_USER:
				return 'user.png';
			case self::ICON_VOTE:
				return 'vote.png';
			case self::ICON_BOOK:
				return 'book.png';
			case self::ICON_STAR:
				return 'popular.png';
			case self::ICON_NEW:
				return 'new.gif';
			case self::ICON_COMMENTS:
				return 'comments.png';
			case self::ICON_RECOMMEND:
				return 'recommend.png';
            case self::ICON_ANSWER:
                return 'answer.png';
            case self::ICON_RIGHT_BLOCK:
                return 'right-block.png';
            case self::ICON_TEXT_BLOCK:
                return 'text-block.png';
            case self::ICON_TEXT_FIELD:
                return 'text-field.png';
		}
	}
	
}