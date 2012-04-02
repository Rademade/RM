<?php
/**
* @property int idGalleryPhoto
* @property int idGallery
* @property int galleryPhotoPosition
* @property int galleryPhotoStatus
* @property int idPhoto
 */
class RM_Gallery_Photo
	extends
		RM_Photo {

	const CACHE_NAME = 'galleriesPhotos';

	const TABLE_NAME = 'galleriesPhotos';

	protected static $_properties = array(
		'idGalleryPhoto' => array(
			'id' => true,
			'type' => 'int'
		),
		'idPhoto' => array(
			'type' => 'int'
		),
		'idGallery' => array(
			'type' => 'int'
		),
		'galleryPhotoStatus' => array(
			'default' => RM_Interface_Hideable::STATUS_SHOW,
			'type' => 'int'
		),
		'galleryPhotoPosition' => array(
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

	public function __construct($data) {
		parent::__construct($data);
		$this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
		$this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
	}

	public function __get($name) {
		$val = $this->_dataWorker->getValue($name);
		return (is_null($val)) ? parent::__get($name) : $val;
	}

	public function __set($name, $value) {
		if (is_null($this->_dataWorker->setValue($name, $value))) {
			parent::__set($name, $value);
		}
	}

	public function getIdPhoto() {
		return $this->idPhoto;
	}
	
	public static function createGalleryPhoto(
		$idGallery,
		$position,
		RM_Photo $photo
	) {
		$galleryPhoto = new self( new RM_Compositor( array(
            'idGallery' => $idGallery,
		    'idPhoto' => $photo->getIdPhoto(),
		    'idContent' => $photo->getIdContent(),
		    'idUser' => $photo->getIdUser(),
		    'photoPath' => $photo->getPhotoPath(),
			'galleryPhotoPosition' => $position
        ) ) );
		$galleryPhoto->save();
		return $galleryPhoto;
	}

	public function getIdRelation() {
		return $this->idGalleryPhoto;
	}
	
	public function getIdGallery() {
		return $this->idGallery;
	}

	public function getGallery() {
		return RM_Gallery::getById( $this->getIdGallery() );
	}

	/**
	 * @static
	 * @param Zend_Db_Select
	 */
	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->join('photos', 'galleriesPhotos.idPhoto = photos.idPhoto', RM_Photo::_getDbAttributes());
		$select->where('galleryPhotoStatus != ?', RM_Interface_Deletable::STATUS_DELETED);
	}
	
	public static function getGalleryPhotos(
		$idGallery,
		RM_Query_Limits $limit
	) {
		$select = self::_getSelect();
		$select->where('galleriesPhotos.idGallery = ?', intval($idGallery));
		$order = new RM_Query_Order();
		$order->addOrder('galleryPhotoPosition', RM_Query_Order::ASC);
		$order->improveQuery($select);
		$list = $limit->getResult($select);
		foreach ($list as &$photo) {
			$photo = new self($photo);
		}
		return $list;
	}

	public function setPosition($position) {
		$this->galleryPhotoPosition = (int)$position;
	}
	
	public function getPosition() {
		return $this->galleryPhotoPosition;
	}
	
	public function getStatus() {
		return $this->galleryPhotoStatus;
	}
	
	public function setStatus($status) {
		$this->galleryPhotoStatus = (int)$status;
	}

	public function __refreshCache() {
		$this->getGallery()->__refreshCache();
	}
	
	public function remove(RM_User $user) {
		$this->setStatus( RM_Interface_Deletable::STATUS_DELETED );
		$this->save();
		$this->getGallery()->__refreshCache();
	}

	public function save() {
		parent::save();
		$this->idPhoto = $this->getIdPhoto();
		$this->_dataWorker->save();
		$this->getGallery()->__refreshCache();
	}

}