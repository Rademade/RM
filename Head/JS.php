<?php
class RM_Head_JS
    extends
        RM_Head_Abstract {

	private $_compress;
	private $_path;
	private $_compress_path;
	private $_files;
	private $_ver;
	private $_usedTags = array();

	public function __construct(Zend_Config $cfg) {
		$this->_compress = intval($cfg->compress) === 1 ? true : false;
		$this->_path = $cfg->path;
		$this->_files = $cfg->file;
		$this->_ver = $cfg->version;
		$this->_compress_path = $cfg->compress_path;
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

	private function _isResolveAddTag($tag) {
		return $this->_isTagExist($tag) && !$this->_isTagUsed($tag);
	}

	public function isCommpress() {
		return $this->_compress;
	}

	private function  _appendTag($tag) {
		foreach ($this->_files->{$tag} as $path) {
			$this->getView()->headScript()->appendFile($this->__getPath($this->_path, $path));
		}
	}

	public function _compressTag($tag) {
		$c = new RM_Head_Compressor_JS();
		foreach ($this->_files->{$tag} as $path) {
			$c->add($this->__getFullPath($this->_path, $path));
		}
		$c->hideDebugInfo();
		$c->simpleMode();
		$c->cacheDir( PUBLIC_PATH . $this->_compress_path );
		$c->setVersion( $this->getVersion() );
		$c->compress();
		$this->getView()->headScript()->appendFile(join('', array(
			$this->_compress_path,
			$c->getFileName()
		)));
	}

	public function getVersion() {
		return $this->_ver;
	}

	public function add($tag, $compress = true) {
		if ($this->_isResolveAddTag($tag)) {
			if ($this->isCommpress() && $compress) {
                $this->_compressTag( $tag );
			} else {
                $this->_appendTag( $tag );
			}
			$this->_setTagAsUsed($tag);
		} else {
			if (!$this->_isTagExist($tag)) {
				throw new Exception('JS tag ' . $tag . ' not exist');
			}
		}
		return $this;
	}
	
}