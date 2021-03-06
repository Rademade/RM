<?php
class RM_Head_CSS
    extends
        RM_Head_Abstract {

    protected $_compress = null;
    protected $_path;
    protected $_compress_path;
    protected $_files;
    protected $_ver;
    protected $_usedTags = array();

    public function __construct(Zend_Config $cfg) {
        if ( isset($cfg->compress) ) {
            $this->_compress = intval($cfg->compress) === 1 ? true : false;
        }
        $this->_path = $cfg->path;
        $this->_files = $cfg->file;
        $this->_ver = $cfg->version;
        $this->_compress_path = $cfg->compress_path;
    }

    public function _compressTag($tag) {
        $c = new RM_Head_Compressor_CSS();
        foreach ($this->_files->{$tag} as $path) {
            $c->add($this->__getFullPath($this->_path, $path));
        }
        $c->setCacheDir( PUBLIC_PATH . $this->_compress_path );
        $c->setVersion( $this->getVersion() );
        $c->compress();
        $this->getView()->headLink()->appendStylesheet(join('', array(
            $this->_compress_path,
            $c->getFileName()
        )));
    }

    public function getVersion() {
        return $this->_ver;
    }

    private function _isResolveAddTag($tag) {
        return $this->_isTagExist($tag) && !$this->_isTagUsed($tag);
    }

    public function isCommpress() {
        return ( $this->_compress === null ) ? $this->__getBaseCompressState() : $this->_compress;
    }

    public function add($tag) {
        if ($this->_isResolveAddTag($tag)) {
            if (!$this->isCommpress()) {
                $this->_appendTag( $tag );
            } else {
                $this->_compressTag( $tag );
            }
            $this->_setTagAsUsed($tag);
        } else {
            if (!$this->_isTagExist($tag)) {
                throw new Exception('CSS tag ' . $tag . ' not exist');
            }
        }
        return $this;
    }

    private function _isTagExist($tag) {
        return isset( $this->_files->{$tag} ) && !empty( $this->_files->{$tag} );
    }

    private function _isTagUsed($tag) {
        return in_array($tag, $this->_usedTags);
    }

    private function _setTagAsUsed($tag) {
        $this->_usedTags[] = $tag;
    }

    private function  _appendTag($tag) {
        foreach ($this->_files->{$tag} as $path) {
            $this->getView()->headLink()->appendStylesheet($this->__getPath($this->_path, $path));
        }
    }

}