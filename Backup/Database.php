<?php
class RM_Backup_Database {

	private $_userName;
	private $_password;
	private $_database;

	public function __construct(
		$user,
		$password,
		$database
	) {
		$this->_userName = $this->_formatCliData( $user );
		$this->_password = $this->_formatCliData( $password );
		$this->_database = $this->_formatCliData( $database );
	}

	public function save($to) {
		echo (join(' ', array(
			'mysqldump',
			'-u' . $this->_userName,
			'-p' . $this->_password,
			$this->_database,
		    '>',
		    $this->_formatPath($to) . '/' . date('d-m-Y') . '.sql'
		)));
	}

	public function _formatPath($path) {
		$path = rtrim($path, "/");
		return $this->_formatCliData( $path );
	}

	public function _formatCliData($string) {
		$string = trim( $string );
		$string = addcslashes($string, "\\\'\"&\n\r<>$. ");
		return $string;
	}

}