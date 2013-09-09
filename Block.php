<?php
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
    //...
    const SEARCH_TYPE_RIGHT_BLOCK = 7;

	const CACHE_NAME = 'block';

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

    /**
     * @var RM_Entity_Worker_Data
     */
    private $_dataWorker;
    /**
     * @var RM_Entity_Worker_Cache
     */
    protected $_cacheWorker;
    /**
     * @var RM_Content
     */
    private $_content = null;

    public function __construct(stdClass $data) {
        $this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
    }

    public function destroy() {
        parent::destroy();
        if ($this->_content) $this->getContentManager()->destroy();
        $this->_content = null;
    }

    public function getId() {
        return $this->_dataWorker->getValue('idBlock');
    }

    public function getIdBlock() {
        return $this->_dataWorker->getValue('idBlock');
    }

	public static function create($blockType, $idPage, $searchType) {
		$block = new self( new RM_Compositor( array(
            'idPage' => $idPage,
            'blockType' => $blockType,
            'searchType' => $searchType
        ) ) );
		$block->setContentManager( RM_Content::create() );
		return $block;
	}

	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('blockStatus != ?', self::STATUS_DELETED);
	}

    public function validate(RM_Exception $e = null, $throw = true) {
        if (is_null($e)) {
            $e = new RM_Exception();
        }
        if ($this->getName() === '') {
            $e[] = 'Enter block default name';
        }
        if ($this->getType() === self::TYPE_MULTI_NAME) {
            foreach ($this->getContentManager()->getAllContentLangs() as $contentLang) {
                if ($contentLang->getName() == '') {
                    $lang = RM_Lang::getById($contentLang->getIdLang());
                    $e[] = 'Enter block name on ' . $lang->getName() . ' language not defined';
                }
            }
        }
        if ($throw && (bool)$e->current()) {
            throw $e;
        }
    }

	public function getIdPage() {
		return $this->_dataWorker->getValue('idPage');
	}
	
	public function getType() {
		return $this->_dataWorker->getValue('blockType');
	}
	
	public function getSearchType() {
		return $this->_dataWorker->getValue('searchType');
	}

	public function getIdContent() {
		return $this->_dataWorker->getValue('idContent');
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
            $this->_dataWorker->setValue('idContent', $contentManager->getId());
		}
		$this->_content = $contentManager;
	}

    /**
     * @return RM_Content
     */
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
		return $this->_dataWorker->getValue('blockStatus');
	}
	
	public function setStatus($status) {
        $this->_dataWorker->setValue('blockStatus', $status);
	}


	public function show() {
        $this->setStatus(self::STATUS_SHOW);
        $this->save();
	}
	
	public function hide() {
        $this->setStatus(self::STATUS_HIDE);
        $this->save();
	}

	public function save() {
        $this->_dataWorker->setValue(
            'idContent',
            $this->getContentManager()->save()->getId()
        );
        $this->_dataWorker->save();
        $this->__refreshCache();
	}
	
	public function remove() {
		$this->setStatus(self::STATUS_DELETED);
		$this->save();
		$this->getContentManager()->remove();
		$this->__cleanCache();
	}

}
