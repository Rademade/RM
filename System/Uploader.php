<?php
class RM_System_Uploader {

    private $sizeLimit = 10485760;
    /**
     * @var RM_System_Uploader
     */
    private $file;
    
    function __construct( $fileKey, $sizeLimit ) {
        $this->sizeLimit = (int)$sizeLimit;
        $this->checkServerSettings();
        if (isset($_GET[ $fileKey ])) {
            $this->file = new RM_System_Uploader_XHR( $fileKey );
        } elseif (isset($_FILES[ $fileKey ])) {
            $this->file = new RM_System_Uploader_Form( $fileKey );
        } else {
            $this->file = false;
        }
    }
    
    private function checkServerSettings() {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }
    }
    
    private function toBytes($str) {
        $val = trim($str);
        $last = strtolower($str[ strlen($str) - 1 ]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
    
    function uploadPhoto(RM_Photo &$photo) {
        if (!$this->file)
            throw new Exception('File not exists');
        $size = $this->file->getSize();
        if ($size == 0)
            throw new Exception('File is empty');
        if ($size > $this->sizeLimit)
            throw new Exception('File is too large');
        $photo->upload( $this->file->getTmpPath() );
        $photo->save();
    }

}
