<?php
abstract class RM_XML_Abstract {

    const DEFAULT_FILE_NAME = 'report';
    const DEFAULT_FILE_EXTENSION = 'xml';
    const DEFAULT_DELIMITER = '_';

    /**
     * @var SimpleXMLElement
     */
    protected $_xml;

    protected $_fileName = self::DEFAULT_FILE_NAME;
    protected $_fileExtension = self::DEFAULT_FILE_EXTENSION;
    protected $_autoDate = true;

    abstract protected function __renderEntity($entity);

    public function __construct() {
        $this->_xml = $this->__createDocument();
    }

    public function render($entities) {
        return $this->__renderEntities($entities);
    }

    public function output() {
        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename="' . $this->getFileNameWithExtension() . '"');
        $this->_xml->saveXML('php://output');
    }

    public function saveAsFile($path, $fileName = null, $fileExtension = null) {
        $fileName = is_null($fileName) ? $this->getFileName() : trim($fileName, '/');
        $fileExtension = is_null($fileExtension) ? $this->getFileExtension() : trim($fileExtension, '/');
        $path = trim($path, '/');
        $path = '/' . $path . '/' . $fileName . '.' . $fileExtension;
        $this->_xml->saveXML($path);
        return $path;
    }

    public function setFileName($name) {
        $this->_fileName = trim($name, '/');
    }

    public function setFileExtension($ext) {
        $this->_fileExtension = trim($ext, '/.');
    }

    public function getFileName() {
        return $this->_fileName . ($this->_autoDate ? static::DEFAULT_DELIMITER . $this->__getAutoDate() : '');
    }

    public function getFileExtension() {
        return $this->_fileExtension;
    }

    public function getFileNameWithExtension() {
        return trim(join('.', [$this->getFileName(), $this->getFileExtension()]), '/');
    }

    public function enableFileNameDate() {
        $this->_autoDate = true;
    }

    public function disableFileNameDate() {
        $this->_autoDate = false;
    }

    /**
     * @return SimpleXMLElement
     */
    public function add() {
        return call_user_func_array([$this->_xml, 'addChild'], func_get_args());
    }

    public function xml() {
        return $this->_xml;
    }

    protected function __getAutoDate() {
        return RM_Date_Datetime::now()->getShortDate();
    }

    protected function __createDocument() {
        return new SimpleXMLElement('<data></data>');
    }

    protected function __renderEntities($entities) {
        foreach ($entities as $entity) {
            $this->__renderEntity($entity);
        }
        return $this;
    }

}