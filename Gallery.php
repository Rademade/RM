<?php
/**
* @property mixed idGallery
* @property mixed galleryStatus
*/
class RM_Gallery
	extends
		RM_Entity
	implements
		RM_Interface_Hideable,
		RM_Interface_Deletable {

	const CACHE_NAME = 'galleries';

	const TABLE_NAME = 'galleries';

	protected static $_properties = array(
		'idGallery' => array(
			'id' => true,
			'type' => 'int'
		),
		'galleryStatus' => array(
			'default' => self::STATUS_SHOW,
			'type' => 'int'
		)
	);

	private $_isPhotosLoaded = false;
	private $_photos = array();
	/**
	 * @var RM_Gallery_Photo
	 */
	private $_poster;
	private $_photosCount = 0;


	public static function create() {
		$gallery = new self();
		return $gallery;
	}

	public function setStatus($statusGallery) {
		$this->galleryStatus = (int)$statusGallery;
	}

	public function getStatus() {
		return $this->galleryStatus;
	}

	public function getMaxPosition() {
		$max = 0;
		foreach ($this->getPhotos() as $photo) {
			if ($max < $photo->getPosition()) {
				$max = $photo->getPosition();
			}
		}
		return $max;
	}

	public function addPhoto(RM_Photo $photo) {
		$this->_photos[] = RM_Gallery_Photo::createGalleryPhoto(
			$this->getId(),
			($this->getMaxPosition() + 1),
			$photo
		);
		$this->__refreshCache();
	}
	
	/**
	 * @param int $id
	 * @throws Exception
	 * @return RM_Gallery_Photo
	 */
	public function getPhotoById($id) {
		$id = (int)$id;
		foreach ($this->getPhotos() as $photo) {
			if ($photo->getIdPhoto() === $id) {
				return $photo;
			}
		}
		throw new Exception('Photo not found');
	}

	public function savePhotos() {
		foreach ($this->getPhotos() as $photo) {
			$photo->save();
		}
	}

	public function getPhotosCount() {
		return sizeof( $this->getPhotos() );
	}

	/**
	 * @return RM_Gallery_Photo[]
	 */
	public function getPhotos() {
		if (!$this->_isPhotosLoaded) {
			$this->_photos = RM_Gallery_Photo::getGalleryPhotos(
				$this->getId(),
				new RM_Query_Limits(0)
			);
			$this->_isPhotosLoaded = true;
		}
		return $this->_photos;
	}

	/**
	 * @return RM_Gallery_Photo
	 */
	public function getPosterPhoto() {
		if (!($this->_poster instanceof RM_Photo)) {
			$photos = RM_Gallery_Photo::getGalleryPhotos(
				$this->getId(),
				new RM_Query_Limits(1)
			);
			if (sizeof($photos) > 0) {
				$this->_poster = $photos[0];
			}
		}
		return $this->_poster;
	}
	
	private function _reload() {
		$this->_isPhotosLoaded = false;
		$this->getPhotos();
	}

	public function updatePositions() {
		$photos = array();
		$i = 0;
		foreach ($this->getPhotos() as $photo) {
			if ($photo->getPosition() !== $i) {
				$photo->setPosition($i);
				$photo->save();
			}
			$photos[ $i ] = $photo;
			++$i;
		}
		$this->__refreshCache();
	}

	public function __cachePrepare() {
		$this->_reload();
		$this->getPosterPhoto();
	}

	public function isShow() {
		return $this->getStatus() === self::STATUS_SHOW;
	}

	public function show() {
		$this->setStatus( self::STATUS_SHOW );
		$this->save();
	}

	public function hide() {
		$this->setStatus( self::STATUS_HIDE );
		$this->save();
	}

	public function remove() {
		$this->setStatus( self::STATUS_DELETED );
		$this->save();
		$this->__cleanCache();
	}
	
}