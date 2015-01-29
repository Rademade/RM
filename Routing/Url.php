<?php
class RM_Routing_Url {

    protected $url;

    /**
     * @access    public
     * @param string $url
     * @ParamType url string
     */
    public function __construct($url) {
        $this->url = $url;
    }

    public function format($raw = false) {
        $this->url = trim(mb_strtolower($this->url, 'UTF-8'));
        $this->url = (new RM_Routing_Url_Translite($this->url))->__toString();
        if (!$raw) {
            $this->url = $this->_removeWrongChars($this->url);
        }
        $this->url = $this->_prettify($this->url);
        return $this;
    }

    public function stripLastSlashes($url) {
        return rtrim($url, '/');
    }

    public function formatLikeAlias() {
        $this->format();
        $this->url = $this->_aliasFormat($this->url);
        $this->url = $this->_prettify($this->url);
        return $this;
    }

    public function checkUnique($excludedId = null) {
        $db = Zend_Registry::get('db');
        /* @var $db Zend_Db_Adapter_Abstract */
        $select = $db->select();
        /* @var $select Zend_Db_Select */
        $select->from('routing', array(
            'count' => 'COUNT(idRoute)'
        ))->where('url = ? ', $this->url);
        if (!is_null($excludedId)) {
            $select->where('idRoute != ? ', $excludedId);
        }
        $select->where('routeStatus != ? ', RM_Interface_Deletable::STATUS_DELETED);
        $res = $db->fetchRow($select);
        return intval($res->count) === 0;
    }

    public function checkFormat(array $params) {
        $url = $this->url;
        $allParams = array_merge(array_keys($params), $this->_getValidParams());
        foreach ($allParams as $param) {
            $url = str_replace('/:' . $param, '', $url);
        }
        $url = '/' . ltrim($url, '/');
        return preg_match('/^\/[a-z0-9\_\&\.\-\/]*\/?$/i', $url);
    }

    /**
     * @return string
     */
    public function getInitialUrl() {
        return $this->url;
    }

    /**
     * Same getInitialUrl
     * @deprecated
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    public function getAssembledUrl(array $params) {
        $url = $this->url;
        foreach ($params as $param => $value) {
            $url = str_replace(':' . $param, $value, $url);
        }
        return $url;
    }

    private function _getValidParams() {
        $cfg = Zend_Registry::get('cfg');
        if (isset($cfg['routing']) && isset($cfg['routing']['valid_params'])) {
            $validParams = (array)$cfg['routing']['valid_params'];
        } else {
            $validParams = array();
        }
        return $validParams;
    }

    private function _removeWrongChars($url) {
        return preg_replace('/[^a-z0-9\-_\.~ ]/i', '', $url);
    }

    private function _prettify($url) {
        $count = 0;
        do {
            $url = str_replace([' ', '--'], '-', $url, $count);
        } while ($count != 0);
        return rtrim($url, '/');
    }

    private function _aliasFormat($url) {
        return trim(str_replace(array(
            '/', '\\'
        ), '-', $url), '-');
    }

}
