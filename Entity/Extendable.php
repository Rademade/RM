<?php
interface RM_Entity_Extendable {

    public function __get($name);

    public function __set($name, $value);

    public function save();

}