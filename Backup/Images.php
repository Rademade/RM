<?php
class RM_Backup_Images {

	public function createTar($path) {
		exec(join(' && ', array(
			'cd ' . PUBLIC_PATH . RM_Photo::SAVE_PATH,
			join(' ', array(
				'tar',
			    '-czf',
			    rtrim($path, '/') . '/' . 'images.tar.gz',
			    '*'
	        ))
        )));
	}

}