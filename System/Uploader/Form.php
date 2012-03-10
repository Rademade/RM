<?php
class RM_System_Uploader_Form extends
	RM_System_Uploader_Abstract {

	private $_name;

	public function __construct( $inputName ) {
		if (!isset($_FILES[ $inputName ])) {
			throw new Exception("Form file with name {$inputName} not given");
		}
		$this->_name = $inputName;
	}

	/**
	 * Save the file to the specified path
	 * @param $path
	 * @return boolean TRUE on success
	 */
	function save($path) {
		if (!move_uploaded_file($this->getTmpPath(), $path)) {
			return false;
		}
		return true;
	}

	public function getTmpPath() {
		return $_FILES[ $this->_name ][ 'tmp_name' ];
	}

	function getName() {
		return $_FILES[ $this->_name ][ 'name' ];
	}

	function getSize() {
		return $_FILES[ $this->_name ][ 'size' ];
	}

}