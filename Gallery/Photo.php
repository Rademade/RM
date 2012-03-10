<?php
/**
* @property int idGalleryPhoto
* @property int idGallery
* @property mixed galleryPhotoPosition
* @property mixed galleryPhotoStatus
*/
class RM_Gallery_Photo
	extends
		RM_Photo {

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

	/**
	 * @static
	 * @return Zend_Db_Select
	 */
	protected static function _getSelect() {
		$select = parent::_getSelect();
		$select->join('galleriesPhotos', 'galleriesPhotos.idPhoto = photos.idPhoto', self::_getDbAttributes());
		$select->where('galleryPhotoStatus != ?', RM_Interface_Deletable::STATUS_DELETED);
		return $select;
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
	
	public function remove(RM_User $user) {
		$this->setStatus( RM_Interface_Deletable::STATUS_DELETED );
		$this->save();
	}
		
}