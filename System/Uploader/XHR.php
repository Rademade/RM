<?php
class RM_System_Uploader_XHR
    extends 
        RM_System_Uploader_Abstract {

    private $_name;
    
    private $_tmpPath;
    private $_tmpSaved;
    
    public function __construct( $inputName ) {
        if (!isset($_GET[ $inputName ])) {
            throw new Exception("File name, on GET parameter {$inputName} not given");
        }
        $this->_name = $inputName;
    }
    
    /**
     * Save the file to the specified path
     * @param $path
     * @return boolean TRUE on success
     */
    function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        if ($realSize != $this->getSize()) {
            return false;
        }
        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        return true;
    }
    
    function getName() {
        return $_GET[ $this->_name ];
    }
    
    function getSize() {
        if (isset($_SERVER[ "CONTENT_LENGTH" ])) {
            return (int)$_SERVER[ "CONTENT_LENGTH" ];
        } else {
            throw new Exception('Getting content length is not supported.');
        }
    }
    
    private function _generateTmpPath() {
        $tmpName = uniqid();
        $tmpDir = realpath( sys_get_temp_dir() );
        $tmpPath = $tmpDir . '/' . $tmpName;
        if ( is_file( $tmpPath ) ) {
            $tmpPath = $this->_generateTmpPath();
        }
        return $tmpPath;
    }
    
    public function getTmpPath() {
        if ($this->_tmpSaved !== true) {
            $this->_tmpPath = $this->_generateTmpPath();
            $this->save( $this->_tmpPath );
            $this->_tmpSaved = true;
        }
        return $this->_tmpPath;
    }

}
