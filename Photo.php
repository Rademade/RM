<?php
class RM_Photo
	extends
		RM_Entity
    implements
        JsonSerializable  {

	const CACHE_NAME = 'photos';

	const TABLE_NAME = 'photos';

	protected static $_properties = array(
		'idPhoto' => array(
			'id' => true,
			'type' => 'int'
		),
		'idContent' => array(
			'type' => 'int'
		),
		'idUser' => array(
			'type' => 'int'
		),
		'photoPath' => array(
			'type' => 'string'
		),
		'photoStatus' => array(
			'default' => RM_Interface_Hideable::STATUS_SHOW,
			'type' => 'int'
		)
	);

    const FULL_IMAGE = 1;
	
	private $_imageInfo = null;

	/**
	 * @var RM_Content
	 */
	private $_content = null;
	/**
	 * @var RM_Entity_Worker_Data
	 */
	private $_dataWorker;
	/**
	 * @var RM_Entity_Worker_Cache
	 */
	protected $_cacheWorker;
	/**
	 * @var bool
	 */
	private $_noSave = false;

	const SAVE_PATH = '/s/public/upload/images/';
	
	const NO_IMAGE = 'no.jpg';
	
	const ERROR_NO_PHOTO = 'Photo not upload';
	const ERROR_NOT_FOUND = 'Photo not found';
	const ERROR_WRONG_FILE = 'You can upload only images';

	public function __construct($data) {
		$this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
		$this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
	}

    public function destroy() {
        if ($this->_content) $this->getContent()->destroy();
        $this->_content = null;
        parent::destroy();
    }

	public static function create(RM_User_Interface $user) {
		$photo = new self(new stdClass());
		$photo->_dataWorker->setValue('idUser', $user->getId());
		return $photo;
	}

	public function save() {
        if (!$this->isNoSave()) {
            $this->_dataWorker->save();
            $this->__refreshCache();
        }
        return $this;
	}

	public static function getEmpty() {
		$photo = new static( new RM_Compositor( array(
            'photoPath' => self::NO_IMAGE
        ) ) );
		$photo->noSave();
		return $photo;
	}
	
	private function check() {
		if ($this->getPhotoPath() === '') {
			throw new Exception(self::ERROR_NO_PHOTO);
		}
	}
	
	private function createContent() {
		if ($this->getIdContent() === 0) {
			$this->_content = RM_Content::create();
			$this->_content->save();
		}
	}

    public function getIdContent() {
        return $this->_dataWorker->getValue('idContent');
    }

    public function isNoSave() {
        return $this->_noSave === true;
    }

	public function noSave() {
		$this->_noSave = true;
	}
	
	public function getContent() {
		if (is_null($this->_content)) {
			if ($this->getIdContent() === 0) {			
				$this->createContent();
			} else {
				$this->_content = RM_Content::getById( $this->getIdContent() );
			}
		}
		return $this->_content;
	}

    public function getId() {
        return $this->getIdPhoto();
    }

	public function getIdPhoto() {
		return $this->_dataWorker->getValue('idPhoto');
	}

	public function getIdUser() {
		return $this->_dataWorker->getValue('idUser');
	}

	public function getPhotoPath() {
		return $this->_dataWorker->getValue('photoPath');
	}
	
	public function getStatus() {
		return $this->_dataWorker->getValue('photoStatus');
	}
	
	public function setStatus($status) {
		$this->_dataWorker->setValue('photoStatus', (int)$status);
	}

	public function setPhotoPath($path) {
		$this->_dataWorker->setValue('photoPath', $path);
	}

	public function getPhotoDir() {
		if (preg_match('/^(.*)\/([0-9a-z]{4})\.(jpg|gif|jpeg|png)$/i', $this->getPhotoPath(), $data)) {
			return $data[1];
		} else {
			throw new Exception(self::ERROR_NOT_FOUND);
		}
	}
	
	public function getFullPhotoPath() {
		return PUBLIC_PATH . self::SAVE_PATH . $this->getPhotoPath();
	}

	private static function getProportionPath($width, $height) {
		return join('', array(
			'/image.php?',
			"width={$width}&",
			"height={$height}&",
			"crop&",
			'image='
		)); 
	}

	/**
	 * @static
	 * @param $select Zend_Db_Select
	 */
	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where(RM_Photo::TABLE_NAME . '.photoStatus != ?', RM_Interface_Deletable::STATUS_DELETED);
	}

	public function _getSavePath() {
		return self::SAVE_PATH . $this->getPhotoPath();
	}

	public function getPath($width = null, $height = null) {
		if (is_null($width) && is_null($height)) {//original
			return $this->_getSavePath();
		} else {
            if (is_null($width) && $this->getHeight() !== 0) {
    		    $width = $height / $this->getHeight() * $this->getWidth();
            }
            if (is_null($height) && $this->getWidth() !== 0) {
                $height = $width / $this->getWidth() * $this->getHeight();
            }
            return self::getProportionPath($width, $height) . $this->_getSavePath();
        }
	}
	
	private function getImageInfo() {
		if (is_null($this->_imageInfo)) {
			$this->_imageInfo = @getimagesize($this->getFullPhotoPath());
            if (!is_array($this->_imageInfo)) {
                $this->_imageInfo = array(0, 0);
            }
		}
		return $this->_imageInfo;
	}
	
	public function getHeight() {
		$imageInfo = $this->getImageInfo();
		return (int)$imageInfo[1];
	}
	
	public function getWidth() {
		$imageInfo = $this->getImageInfo();
		return (int)$imageInfo[0];
	}
	
	public function validate($tmpName) {
		$imageInfo = @getimagesize($tmpName);
		if (!$imageInfo)
			throw new Exception(self::ERROR_WRONG_FILE);
		if (!preg_match('/^image\/([a-z]{2,5})$/i', $imageInfo['mime'], $expansion))
			throw new Exception(self::ERROR_WRONG_FILE);
		return $expansion[1];
	}

	public function upload($tmpName) {
        $this->validate($tmpName);
        $this->_generateImageSavePath(  );
        copy($tmpName, $this->getFullPhotoPath());
		$this->save();
	}

    public function setBinaryImage($imageBinary) {
        //TODO validate binary
        $this->_generateImageSavePath(  );
        file_put_contents( $this->getFullPhotoPath(), $imageBinary );
        $this->save();
    }

	public function remove(RM_User_Interface $user) {
		if ($user->getId() === $this->getIdUser() || 
			$user->getRole()->isAdmin()
		) {
			$this->setStatus( RM_Interface_Deletable::ACTION_DELETE );
			$this->save();
			$this->__cleanCache();
		} else {
			throw new Exception('Access photo error');
		}
	}

	public function _toJSON() {
		return array(
			'id' => $this->getId(),
			'photoPath' => $this->_getSavePath()
		);
	}

    private function _generateImageSavePath() {
        $randomPath = md5(uniqid() . microtime(true));
        $i = 0;
        $step = 4;
        $dirPath = '';
        while ($i < strlen($randomPath) - $step) {
            $segment = substr($randomPath, $i, $step);
            $dirPath .= $segment . '/';
            $i += $step;
        }
        mkdir(PUBLIC_PATH . self::SAVE_PATH . $dirPath, 0777, true);
        $this->setPhotoPath( $dirPath . substr($randomPath, $i, $step) );
    }

    function jsonSerialize() {
        return array(
            'path' => $this->getPath(),
//            'width' => $this->getWidth(),
//            'height' => $this->getHeight()
        );
    }

}