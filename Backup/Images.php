<?php
class RM_Backup_Images {

    public function createTar($path) {
        shell_exec(join(' && ', array(
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