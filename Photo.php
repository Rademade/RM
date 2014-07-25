<?php
class RM_Photo
    extends
        RM_Entity
    implements
        JsonSerializable {

    const CACHE_NAME = 'photos';
    const TABLE_NAME = 'photos';

    const SAVE_PATH = '/s/public/upload/images/';

    const FULL_IMAGE = 1;
    const NO_IMAGE = 'no.jpg';

    const ERROR_NO_PHOTO = 'Photo was not uploaded';
    const ERROR_NOT_FOUND = 'Photo was not found';
    const ERROR_WRONG_FILE = 'You can upload only images';

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

    private $_imageInfo = null;
    /**
     * @var RM_Content
     */
    private $_content = null;
    /**
     * @var RM_Entity_Worker_Data
     */
    protected $_rmPhotoDataWorker;
    /**
     * @var RM_Entity_Worker_Cache
     */
    protected $_cacheWorker;
    private $_noSave = false;

    public function __construct($data) {
        $this->_rmPhotoDataWorker = new RM_Entity_Worker_Data(get_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
    }

    public function destroy() {
        if ($this->_content) $this->getContent()->destroy();
        $this->_content = null;
        parent::destroy();
    }

    /**
     * @deprecated
     * @param RM_User_Interface $user
     * @return RM_Photo
     */
    public static function create(RM_User_Interface $user) {
       return static::createPhoto($user);
    }

    public static function createPhoto(RM_User_Interface $user) {
        $photo = new static(new RM_Compositor(array(
            'idUser' => $user->getId()
        )));
        return $photo;
    }

    public function save() {
        if (!$this->isNoSave()) {
            $this->_rmPhotoDataWorker->save();
            $this->__refreshCache();
        }
        return $this;
    }

    public static function getEmpty() {
        $photo = new static(new RM_Compositor(array(
            'photoPath' => self::NO_IMAGE
        )));
        $photo->noSave();
        return $photo;
    }

    private function createContent() {
        if ($this->getIdContent() === 0) {
            $contentClassName = $this->__getContentClassName();
            $this->_content = $contentClassName::create();
            $this->_content->save();
        }
    }

    public function getIdContent() {
        return $this->_rmPhotoDataWorker->getValue('idContent');
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
                $contentClassName = $this->__getContentClassName();
                $this->_content = $contentClassName::getById($this->getIdContent());
            }
        }
        return $this->_content;
    }

    public function getId() {
        return $this->getIdPhoto();
    }

    public function getIdPhoto() {
        return $this->_rmPhotoDataWorker->getValue('idPhoto');
    }

    public function getIdUser() {
        return $this->_rmPhotoDataWorker->getValue('idUser');
    }

    public function getPhotoPath() {
        return $this->_rmPhotoDataWorker->getValue('photoPath');
    }

    public function getStatus() {
        return $this->_rmPhotoDataWorker->getValue('photoStatus');
    }

    public function setStatus($status) {
        $this->_rmPhotoDataWorker->setValue('photoStatus', (int)$status);
    }

    public function setPhotoPath($path) {
        $this->_rmPhotoDataWorker->setValue('photoPath', $path);
    }

    public function getPhotoDir() {
        if (preg_match('/^(.*)\/([0-9a-z]{4})(\.(jpg|gif|jpeg|png))?$/i', $this->getPhotoPath(), $data)) {
            return $data[1];
        } else {
            throw new Exception(self::ERROR_NOT_FOUND);
        }
    }

    public function getFullPhotoPath() {
        return PUBLIC_PATH . $this->_getSavePath();
    }

    protected static function getProportionPath($width, $height, $imagePath) {
        return join('', array(
            '/image.php?',
            "width={$width}&",
            "height={$height}&",
            "crop&",
            "image={$imagePath}"
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
        return static::SAVE_PATH . $this->getPhotoPath();
    }

    public function getProportionalPhoto($maxWidth, $maxHeight, &$width = null, &$height = null) {
        $height = $this->getHeight();
        $width = $this->getWidth();
        if ($height == 0 || $width == 0) {
            return '';
        }
        if ($maxWidth / $width < $maxHeight / $height) {
            $height = floor($maxWidth / $width * $height);
            $width = $maxWidth;
        } else {
            $width = floor($maxHeight / $height * $width);
            $height = $maxHeight;
        }
        return $this->_getResizedPath($width, $height);
    }

    public function getPath($width = null, $height = null) {
        if (is_null($width) && is_null($height)) { //original
            return $this->_getSavePath();
        } else {
            if (is_null($width) && $this->getHeight() !== 0) {
                $width = $height / $this->getHeight() * $this->getWidth();
            }
            if (is_null($height) && $this->getWidth() !== 0) {
                $height = $width / $this->getWidth() * $this->getHeight();
            }
            return $this->_getResizedPath($width, $height);
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
        if (!$imageInfo) {
            throw new Exception(self::ERROR_WRONG_FILE);
        }
        if (!preg_match('/^image\/([a-z]{2,5})$/i', $imageInfo['mime'], $expansion)) {
            throw new Exception(self::ERROR_WRONG_FILE);
        }
        return $expansion[1];
    }

    public function upload($tmpName) {
        $extension = $this->validate($tmpName);
        $savePath = $this->_generateImageSavePath();
        $this->setPhotoPath($savePath . '.' . $extension);
        copy($tmpName, $this->getFullPhotoPath());
        $this->save();
    }

    /**
     * is it using somewhere?
     * @param $imageBinary
     */
    public function setBinaryImage($imageBinary) {
        //TODO validate binary
        $this->setPhotoPath($this->_generateImageSavePath());
        file_put_contents($this->getFullPhotoPath(), $imageBinary);
        $this->save();
    }

    public function remove(RM_User_Interface $user) {
        if (
            $user->getId() === $this->getIdUser() ||
            $user->getRole()->isAdmin()
        ) {
            $this->setStatus(RM_Interface_Deletable::ACTION_DELETE);
            $this->save();
            $this->__cleanCache();
        } else {
            throw new Exception('Access photo error');
        }
    }

    protected function _getResizedPath($width, $height) {
        return self::getProportionPath($width, $height, $this->_getSavePath());
    }

    public function _toJSON() {
        return array(
            'id' => $this->getId(),
            'photoPath' => $this->_getSavePath()
        );
    }

    public function setUser(RM_User_Interface $user) {
        $this->_rmPhotoDataWorker->setValue('idUser', $user->getId());
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
        $old = umask(0);
        mkdir(PUBLIC_PATH . static::SAVE_PATH . $dirPath, 0777, true);
        umask($old);
        return $dirPath . substr($randomPath, $i, $step);
    }

    function jsonSerialize() {
        return array(
            'path' => $this->getPath()
        );
    }

    /**
     * @return RM_Content
     */
    protected function __getContentClassName() {
        return 'RM_Content';
    }

}