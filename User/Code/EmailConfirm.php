<?php
class RM_User_Code_EmailConfirm
    extends
        RM_User_Code {

    public function __construct(stdClass $data) {
        parent::__construct($data);
    }

    public static function getMyType() {
        return self::TYPE_EMAIL_CONFIRM;
    }

}