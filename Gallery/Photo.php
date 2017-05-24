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
    protected $_rmGalleryPhotoDataWorker;
    /**
     * @var RM_Gallery
     */
    protected $_gallery;
    
    public function __construct($data) {
        parent::__construct($data);
        $this->_rmGalleryPhotoDataWorker = new RM_Entity_Worker_Data(get_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
    }
    
    public function destroy() {
        if ($this->_gallery) $this->getGallery()->destroy();
        $this->_gallery = null;
        parent::destroy();
    }
    
    public function __get($name) {
        $val = $this->_rmGalleryPhotoDataWorker->getValue($name);
        return (is_null($val)) ? parent::__get($name) : $val;
    }
    
    public function __set($name, $value) {
        if (is_null($this->_rmGalleryPhotoDataWorker->setValue($name, $value))) {
            parent::__set($name, $value);
        }
    }
    
    public static function createGalleryPhoto(
        $idGallery,
        $position,
        RM_Photo $photo
    ) {
        $galleryPhoto = new static( new RM_Compositor( array(
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
        return $this->_rmGalleryPhotoDataWorker->getValue('idGalleryPhoto');
    }
    
    public function getIdGallery() {
        return $this->_rmGalleryPhotoDataWorker->getValue('idGallery');
    }
    
    public function setIdGallery($id) {
        return $this->_rmGalleryPhotoDataWorker->setValue('idGallery', $id);
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
        if ($idGallery == 0) {
            return array();
        }
        $select = static::_getSelect();
        $select->where('galleriesPhotos.idGallery = ?', intval($idGallery));
        $order = new RM_Query_Order();
        $order->add('galleryPhotoPosition', RM_Query_Order::ASC);
        $order->improveQuery($select);
        $list = $limit->getResult($select);
        foreach ($list as &$photo) {
            $photo = new static($photo);
        }
        return $list;
    }
    
    public function setPosition($position) {
        $position = (int)$position;
        $this->_rmGalleryPhotoDataWorker->setValue('galleryPhotoPosition', $position);
    }

    public function getPosition() {
        return $this->_rmGalleryPhotoDataWorker->getValue('galleryPhotoPosition');
    }

    public function getStatus() {
        return $this->_rmGalleryPhotoDataWorker->getValue('galleryPhotoStatus');
    }

    public function setStatus($status) {
        $status = (int)$status;
        $this->_rmGalleryPhotoDataWorker->setValue('galleryPhotoStatus', $status);
    }

    public function __refreshCache() {
        if ($this->getGallery() instanceof RM_Gallery) {
            $this->getGallery()->__refreshCache();
        }
    }

    public function remove(RM_User_Interface $user) {
        $this->setStatus( RM_Interface_Deletable::STATUS_DELETED );
        $this->save();
    }

    public function save() {
        parent::save();
        $this->_rmGalleryPhotoDataWorker->setValue('idPhoto', $this->getIdPhoto());
        $this->_rmGalleryPhotoDataWorker->save();
    }

    public function _toJSON() {
        return parent::_toJSON();
    }

}