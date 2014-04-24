<?php

require_once LIBRARY_PATH . '/PHPExcel/PHPExcel.php';

abstract class RM_XLS_Abstract {

    const ROW_HEIGHT = 15;
    const FIRST_COLUMN = 'A';
    const FIRST_ROW = 1;
    const DEFAULT_DOCUMENT_TITLE = 'Document';
    const DEFAULT_FILE_NAME = 'Report';
    const DEFAULT_FILE_EXTENSION = 'xls';
    const DEFAULT_DELIMITER = '_';

    /**
     * @var RM_Entity[]
     */
    protected $_entitiesForSheets = array();

    protected $_title = self::DEFAULT_DOCUMENT_TITLE;

    protected $_fileName = self::DEFAULT_FILE_NAME;
    protected $_fileExtension = self::DEFAULT_FILE_EXTENSION;
    protected $_autoDate = true;

    /**
     * @var PHPExcel
     */
    private $_document;
    private $_currentRow = self::FIRST_ROW;
    private $_currentColumn = self::FIRST_COLUMN;

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
        $this->_document = new PHPExcel();
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
        $this->setCurrentRowHeight(self::ROW_HEIGHT);
    }

    protected function __getAutoDate() {
        return RM_Date_Datetime::now()->getShortDate();
    }

    private function _renderHead() {
        $this->_resetRow();
        $this->_resetColumn();
        foreach ($this->__getHeaderTitles() as $headerTitle) {
            $this->_writeCurrentRow($headerTitle);
        }
        $this->resetCurrentRowHeight();
        $this->_nextRow();
    }

    private function _renderEntities() {
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

    private function _writeCurrentRow($value) {
        if (is_array($value)) {
            $value = join(', ', $value) . ' ';
        }
        $this->_document->getActiveSheet()->setCellValue($this->_currentColumn++ . $this->_currentRow, $value);
    }

    private function _resetRow() {
        $this->_currentRow = self::FIRST_ROW;
    }

    private function _nextRow() {
        $this->_currentRow++;
    }

    private function _resetColumn() {
        $this->_currentColumn = self::FIRST_COLUMN;
    }

    private function _columnsAutoWidth() {
        foreach (range(self::FIRST_COLUMN, $this->_currentColumn) as $column) {
            $this->_document->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
        }
    }

    //RM_TODO auto row height

}