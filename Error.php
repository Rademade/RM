<?php
/**
* @property int errorStatus
* @property int idError
* @property string errorUrl
* @property int idLog
* @property int errorTime
* @property string errorText
* @property string errorServer
* @property string errorRequestData
*/
class RM_Error
    extends
        RM_Entity
    implements
        RM_Interface_Deletable {
    
    const STATUS_NEW = 1;
    const STATUS_SHOW = 2;
    
    const TABLE_NAME = '_errorLogRow';
    
    protected static $_properties = array(
        'idError' => array(
            'id' => true,
            'field' => 'idLogRow',
            'type' => 'int'
        ),
        'idLog' => array(
            'type' => 'int'
        ),
        'errorTime' => array(
            'type' => 'string'
        ),
        'errorText' => array(
            'type' => 'string'
        ),
        'errorServer' => array(
            'type' => 'string'
        ),
        'errorRequestData' => array(
            'type' => 'string'
        ),
        'errorUrl' => array(
            'type' => 'string'
        ),
        'errorStatus' => array(
            'default' => self::STATUS_NEW,
            'type' => 'int'
        )
    );
    
    
    public function isNew() {
        return ($this->errorStatus === self::STATUS_NEW);
    }
    
    public function getIdLog() {
        return $this->idLog;
    }
    
    public function getUrl() {
        return $this->errorUrl;
    }
    
    public function getDate() {
        return date('d.m.Y H:i:s', strtotime($this->errorTime));
    }
    
    public function getStatus() {
        return $this->errorStatus;
    }
    
    public function getText() {
        return $this->errorText;
    }
    
    public function getServerData() {
        return json_decode($this->errorServer);
    }
    
    public function getRequestData() {
        return json_decode($this->errorRequestData);
    }
    
    public function remove() {
        $this->setStatus(self::STATUS_DELETED);
        $this->save();
        $this->__cleanCache();
    }
    
    public function read() {
        if ($this->isNew()) {
            $this->setStatus(self::STATUS_SHOW);
            $this->save();
        }
    }
    
    public function setStatus( $status ) {
        $status = (int)$status;
        if ($this->getStatus() !== $status) {
            $this->errorStatus = $status;
        }
    }
    
    /**
     * @param Zend_Db_Select
     * @return void
     */
    public static function _setSelectRules(Zend_Db_Select $select) {
        $select->where('errorStatus != ?', self::STATUS_DELETED);
    }
    
    public static function getNewErrorsCount() {
        $conditions = new RM_Query_Where();
        $conditions->add('errorStatus', RM_Query_Where::EXACTLY, self::STATUS_NEW);
        return self::getCount( $conditions );
    }
    
    public static function addLogRow($name, $error) {
        $error = new self( new RM_Compositor( array(
            'idLog' => RM_Error_Category::getByLog($name)->getId(),
            'errorTime' => date('Y-m-d H:i:s'),
            'errorText' => !is_string($error) ? serialize($error) : $error,
            'errorServer' => @json_encode($_SERVER),
            'errorRequestData' => json_encode($_REQUEST),
            'errorUrl' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'php-cli'
        ) ) );
        $error->save();
        return $error;
    }

}