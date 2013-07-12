<?php
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

    /**
     * @var RM_Gallery
     */
    protected $_gallery;

	public function __construct($data) {
		parent::__construct($data);
		$this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
		$this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
	}

    public function destroy() {
        if ($this->_gallery) $this->getGallery()->destroy();
        $this->_gallery = null;
        parent::destroy();
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
		return $this->_dataWorker->getValue('idGalleryPhoto');
	}
	
	public function getIdGallery() {
		return $this->_dataWorker->getValue('idGallery');
	}

    /**
     * @return RM_Gallery
     */
	public function getGallery() {
        if (!$this->_gallery instanceof RM_Gallery) {
            $this->_gallery = RM_Gallery::getById( $this->getIdGallery() );
        }
        return $this->_gallery;
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
		$order->add('galleryPhotoPosition', RM_Query_Order::ASC);
		$order->improveQuery($select);
		$list = $limit->getResult($select);
		foreach ($list as &$photo) {
			$photo = new self($photo);
		}
		return $list;
	}

	public function setPosition($position) {
        $position = (int)$position;
		$this->_dataWorker->setValue('galleryPhotoPosition', $position);
	}
	
	public function getPosition() {
		return $this->_dataWorker->getValue('galleryPhotoPosition');
	}
	
	public function getStatus() {
		return $this->_dataWorker->getValue('galleryPhotoStatus');
	}
	
	public function setStatus($status) {
        $status = (int)$status;
		$this->_dataWorker->setValue('galleryPhotoStatus', $status);
	}

	public function __refreshCache() {
		$this->getGallery()->__refreshCache();
	}
	
	public function remove(RM_User_Interface $user) {
		$this->setStatus( RM_Interface_Deletable::STATUS_DELETED );
		$this->save();
		$this->getGallery()->__refreshCache();
	}

	public function save() {
		parent::save();
		$this->_dataWorker->setValue('idPhoto', $this->getIdPhoto());
		$this->_dataWorker->save();
		$this->getGallery()->__refreshCache();
	}

	public function _toJSON() {
		return parent::_toJSON();
	}

}