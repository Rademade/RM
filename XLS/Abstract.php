<?php

require_once LIBRARY_PATH . '/PHPExcel/PHPExcel.php';

abstract class RM_XLS_Abstract {

    const ROW_HEIGHT = 15;
    const FIRST_COLUMN = 'A';
    const FIRST_ROW = 1;
    const DEFAULT_DOCUMENT_TITLE = 'Document';
    const DEFAULT_FILE_NAME = 'report';
    const DEFAULT_FILE_EXTENSION = 'xls';
    const DEFAULT_DELIMITER = '_';

    /**
     * @var RM_Entity[]
     */
    protected $_entitiesForSheets = array();

    protected $_title;

    protected $_fileName;
    protected $_fileExtension;
    protected $_autoDate = true;

    /**
     * @var PHPExcel
     */
    protected $_document;
    protected $_currentRow = self::FIRST_ROW;
    protected $_currentColumn = self::FIRST_COLUMN;

    /**
     * @return array
     */
    abstract protected function __getHeaderTitles();

    /**
     * in same order as __getHeaderTitles()
     * @param RM_Entity $e
     * @return array
     */
    abstract protected function __getEntityValues($e);

    public function __construct() {
        $this->_title = static::DEFAULT_DOCUMENT_TITLE;
        $this->_fileName = static::DEFAULT_FILE_NAME;
        $this->_fileExtension = static::DEFAULT_FILE_EXTENSION;
        $this->_currentRow = static::FIRST_ROW;
        $this->_currentColumn = static::FIRST_COLUMN;

        $this->_document = $this->__createDocument();
        $this->_document->getProperties()->setTitle($this->getTitle());
        $this->_document->getDefaultStyle()->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX);
    }

    public function addSheet($title, $entities) {
        if (sizeof($this->_entitiesForSheets) > 0) {
            $sheet = $this->_document->createSheet();
        } else {
            $sheet = $this->_document->getActiveSheet();
        }
        $sheet->setTitle($title);
        $this->_entitiesForSheets[] = $entities;
    }

    public function render() {
        $this->_document->setActiveSheetIndex(0);
        $sheetCount = count($this->_document->getAllSheets());
        for ($sheetIndex = 0; $sheetIndex < $sheetCount; ++$sheetIndex) {
            $this->_document->setActiveSheetIndex($sheetIndex);
            $this->_renderHead();
            $this->_renderEntities();
        }
    }

    public function output() {
        $objWriter = new PHPExcel_Writer_Excel5($this->_document);
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $this->getFileNameWithExtension() . '"');
        $objWriter->save('php://output');
    }

    public function saveAsFile($path, $fileName = null, $fileExtension = null) {
        $fileName = is_null($fileName) ? $this->getFileName() : trim($fileName, '/');
        $fileExtension = is_null($fileExtension) ? $this->getFileExtension() : trim($fileExtension, '/');
        $path = trim($path, '/');
        $objWriter = new PHPExcel_Writer_Excel5($this->_document);
        $path = '/' . $path . '/' . $fileName . '.' . $fileExtension;
        $objWriter->save( $path );
        return $path;
    }

    public function setTitle($title) {
        $this->_title = $title;
    }

    public function getTitle() {
        return $this->_title;
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

    public function setCurrentRowHeight($height) {
        $this->_document->getActiveSheet()->getRowDimension($this->_currentRow)->setRowHeight($height);
    }

    public function getCurrentRowHeight() {
        return $this->_document->getActiveSheet()->getRowDimension($this->_currentRow)->getRowHeight();
    }

    public function resetCurrentRowHeight() {
        $this->setCurrentRowHeight(static::ROW_HEIGHT);
    }

    protected function __createDocument() {
        return new PHPExcel();
    }

    protected function __getAutoDate() {
        return RM_Date_Datetime::now()->getShortDate();
    }

    protected function _renderHead() {
        $this->_resetRow();
        $this->_resetColumn();
        foreach ($this->__getHeaderTitles() as $headerTitle) {
            $this->_writeCurrentRow($headerTitle);
        }
        $this->resetCurrentRowHeight();
        $this->_nextRow();
    }

    protected function _renderEntities() {
        $sheetIndex = $this->_document->getActiveSheetIndex();
        $entities = isset($this->_entitiesForSheets[$sheetIndex])
            ? $this->_entitiesForSheets[$sheetIndex] : array();
        foreach ($entities as $entity) {
            /** @var RM_Entity $entity */
            $this->_resetColumn();
            $this->resetCurrentRowHeight();
            foreach ($this->__getEntityValues($entity) as $value) {
                $this->_writeCurrentRow($value);
            }
            $this->_nextRow();
            if (method_exists($entity, 'destroy')) {
                $entity->destroy();
            }
            unset($this->_entitiesForSheets[$sheetIndex]);
        }
        $this->_columnsAutoWidth();
    }

    protected function _writeCurrentRow($value) {
        if (is_array($value)) {
            $value = join(', ', $value) . ' ';
        }
        $this->_document->getActiveSheet()->setCellValue($this->_currentColumn++ . $this->_currentRow, $value);
    }

    protected function _resetRow() {
        $this->_currentRow = static::FIRST_ROW;
    }

    protected function _nextRow() {
        $this->_currentRow++;
    }

    protected function _resetColumn() {
        $this->_currentColumn = static::FIRST_COLUMN;
    }

    protected function _columnsAutoWidth() {
        foreach (range(static::FIRST_COLUMN, $this->_currentColumn) as $column) {
            $this->_document->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
        }
    }

    //RM_TODO auto row height

}