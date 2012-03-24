<?php
/**
* @property int idBlock
* @property int idPage
* @property int idContent
* @property int blockType
* @property int searchType
* @property int blockStatus
*/
class RM_Block
	extends
		RM_Entity
	implements
		RM_Interface_Hideable,
		RM_Interface_Deletable,
		RM_Interface_Contentable {
	
	const TYPE_SIMPLE_NAME = 1;
	const TYPE_MULTI_NAME = 2;
	
	const SEARCH_TYPE_OPTION = 1;
	const SEARCH_TYPE_BLOCK = 2;

	/**
	 * @var RM_Content
	 */
	private $_content = null;

	const TABLE_NAME = 'blocks';

	protected static $_properties = array(
		'idBlock' => array(
			'id' => true,
			'type' => 'int'
		),
		'idPage' => array(
			'type' => 'int'
		),
		'idContent' => array(
			'type' => 'int'
		),
		'blockType' => array(
			'type' => 'int'
		),
		'searchType' => array(
			'type' => 'int'
		),
		'blockStatus' => array(
			'default' => self::STATUS_HIDE,
			'type' => 'int'
		)
	);

	public static function create($blockType, $idPage, $searchType) {
		$block = new self( new RM_Compositor( array(
            'idPage' => $idPage,
            'blockType' => $blockType,
            'searchType' => $searchType
        ) ) );
		$block->setContentManager( RM_Content::create() );
		return $block;
	}

	public function validate() {
		if ($this->getName() === '') {
			throw new Exception('Enter block name');
		}
	}

	public function getIdPage() {
		return $this->idPage;
	}
	
	public function getType() {
		return $this->blockType;
	}
	
	public function getSearchType() {
		return $this->searchType;
	}

	public function getIdContent() {
		return $this->idContent;
	}
	
	public function getName() {
		return $this->getContentManager()->getDefaultContentLang()->getName();
	}
	
	public function setName($name) {
		if ($this->getName() !== $name) {
			$name = trim($name);
			if ($name === '') {
				throw new Exception('Enter block name');
			}
			if ( strlen($name) > 200) {
				throw new Exception('Block is too long');
			}
			$this->getContentManager()->getDefaultContentLang()->setName(
				$name,
				RM_Content_Field_Process::PROCESS_TYPE_TEXT
			);
		}
	}

	public function setContentManager(RM_Content $contentManager) {
		if ($this->getIdContent() !== $contentManager->getId()) {
			$this->idContent = $contentManager->getId();
		}
		$this->_content = $contentManager;
	}
	
	public function getContentManager() {
		if (is_null($this->_content)) {
			$this->_content = RM_Content::getById( $this->getIdContent() );
		}
		return $this->_content;
	}
	
	public function getDefaultContent() {
		return $this->getContentManager()->getDefaultContentLang();
	}
	
	public function getContent() {
		return $this->getContentManager()->getCurrentContentLang();
	}
	
	public function isShow() {
		return $this->getStatus() === self::STATUS_SHOW;
	}

	public function getStatus() {
		return $this->blockStatus;
	}
	
	public function setStatus($status) {
		$this->blockStatus = (int)$status;
	}

	public function show() {
		if ($this->getStatus() !== self::STATUS_SHOW) {
			$this->setStatus(self::STATUS_SHOW);
			$this->save();
		}
	}
	
	public function hide() {
		if ($this->getStatus() !== self::STATUS_HIDE) {
			$this->setStatus(self::STATUS_HIDE);
			$this->save();
		}
	}
	
	public function remove() {
		$this->setStatus(self::STATUS_DELETED);
		$this->save();
		$this->getContentManager()->remove();
		$this->__cleanCache();
	}

}
