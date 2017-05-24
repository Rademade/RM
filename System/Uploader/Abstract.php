<?php
abstract class RM_System_Uploader_Abstract {

    abstract public function save($savePath);

    abstract public function getName();

    abstract public function getSize();

    abstract public function getTmpPath();

}