<?php
/**
* @property int idUser
* @property int idContent
* @property int idPhoto
* @property string photoPath
* @property int photoStatus
*/
class RM_Photo
	extends
		RM_Entity {

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
	 * @var RM_Entity_Worker
	 */
	private $_entityWorker;
	/**
	 * @var bool
	 */
	private $_noSave = false;

	const SAVE_PATH = '/s/public/upload/images/';
	
	const NO_IMAGE = 'no.jpg';
	
	const ERROR_NO_PHOTO = 'Photo not upload';
	const ERROR_NOT_FOUND = 'Photo not found';
	const ERROR_WRONG_FILE = 'You can upload only images';

	const CACHE_NAME = 'photo';

	public function __construct($data) {
		if ($this->isEntity()) {
			$this->_entityWorker = new RM_Entity_Worker(
				'RM_Photo',
				$data,
				RM_Photo::TABLE_NAME
			);
		}
		parent::__construct($data);
	}

	public static function create(RM_User $user) {
		$photo = new self(new stdClass());
		$photo->idUser = $user->getId();
		return $photo;
	}

	private function isEntity() {
		return get_class() !== get_called_class();
	}

	public function __get($name) {
		$val = ($this->isEntity()) ? $this->_entityWorker->getValue($name) : null;
		return (is_null($val)) ? parent::__get($name) : $val;
	}

	public function __set($name, $value) {
		$parent = true;
		if ($this->isEntity()) {
			$parent = is_null($this->_entityWorker->setValue($name, $value));
		}
		if ($parent)
			parent::__set($name, $value);
	}

	public function save() {
		if ($this->isEntity()) {
			$this->_entityWorker->save();
		}
		parent::save();
	}

	public static function getEmpty() {
		$photo = new self( new RM_Compositor( array(
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
		if ($this->idContent === 0) {
			$this->_content = RM_Content::create();
			$this->_content->save();
		}
	}
	
	public function noSave() {
		$this->_noSave = true;
	}
	
	public function getContent() {
		if (is_null($this->_content)) {
			if ($this->getIdContent() === 0) {			
				$this->createContent();
			} else {
				$this->_content = RM_Content::getById($this->idContent);
			}
		}
		return $this->_content;
	}

	public function getIdPhoto() {
		return $this->idPhoto;
	}

	public function getIdContent() {
		return $this->idContent;
	}

	public function getIdUser() {
		return $this->idUser;
	}

	public function getPhotoPath() {
		return $this->photoPath;
	}
	
	public function getStatus() {
		return $this->photoStatus;
	}
	
	public function setStatus($status) {
		$this->photoStatus = (int)$status;
	}

	public function setPhotoPath($path) {
		$this->photoPath = $path;
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

	/**
	 * @static
	 * @return Zend_Cache_Core
	 */
	private static function _getCache() {
		$manager = Zend_Registry::get('cachemanager');
		/* @var $manager Zend_Cache_Manager */
		return $manager->getCache( self::CACHE_NAME );
	}
	
	private function clearCache() {
		self::_getCache()->remove($this->getIdPhoto());
	}
	
	private static function getProportionPath($width, $height) {
		$proportions = intval($width/$height*100)/100;
		return join('', array(
			'/image.php?',
			"width={$width}&amp;",
			"height={$height}&amp;",
			"cropratio=$proportions:1&amp;",
			'image='
		)); 
	}

	/**
	 * @static
	 * @return Zend_Db_Select
	 */
	protected static function _getSelect() {
		$select = self::getDb()->select();
		$select->from(RM_Photo::TABLE_NAME, RM_Photo::_getDbAttributes());
//		$select->where(RM_Photo::TABLE_NAME . '.photoStatus != ?', RM_Interface_Deletable::STATUS_DELETED);
		return $select;
	}
	
	public function getPath(
		$width = null,
		$height = null,
		$proportion = true
	) {
		if (is_null($width) && is_null($height)) {//original
			return self::SAVE_PATH . $this->getPhotoPath(); 
		} else {
			if ($proportion) //resize with proportion
				return self::getProportionPath($width, $height) . self::SAVE_PATH . $this->getPhotoPath();
			else
				return false;
		}
	}
	
	private function getImageInfo() {
		if (is_null($this->_imageInfo)) {
			$this->_imageInfo = getimagesize($this->getFullPhotoPath());
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
		$expansion = $this->validate($tmpName);
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
		$this->setPhotoPath(
			$dirPath .
			substr($randomPath, $i, $step) . '.' .//last segment
			strtolower($expansion)
		);
		copy($tmpName, $this->getFullPhotoPath());
		$this->save();
	}

	public function remove(RM_User $user) {
		if ($user->getId() === $this->getIdUser() || 
			$user->getRole()->isAdmin()
		) {
			$this->setStatus( RM_Interface_Deletable::ACTION_DELETE );
			$this->save();
			$this->clearCache();
		} else {
			throw new Exception('Access photo error');
		}
	}
}