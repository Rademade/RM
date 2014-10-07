<?php
abstract class RM_SMS_Abstract {

    /** @var string */
    protected $_apiUrl;

    /** @var string|int */
    protected $_apiVersion;

    /** @var string */
    protected $_login;

    /** @var string */
    protected $_password;

    /** @var string */
    protected $_sender;

    /** @var string */
    protected $_body;

    /** @var string[] */
    protected $_recipients;

    /** @var DomDocument */
    protected $_doc;

    /** @var RM_Phone */
    protected $_phone;

    /**
     * @param DomDocument $doc
     * @return DomDocument
     */
    abstract protected function __assembleDocument(DomDocument $doc);

    public function __construct() {
        $this->reset();
        $phoneClass = RM_Dependencies::getInstance()->phoneClass;
        $this->_phone = new $phoneClass(null);
    }

    public function reset() {
        $this->_recipients = [];
    }

    public function addRecipient($recipient) {
        try {
            $this->_phone->setPhoneNumber($recipient);
            $this->_recipients[] = $this->_phone->getPhoneNumber();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getRecipients() {
        return $this->_recipients;
    }

    public function setLogin($login) {
        $this->_login = $login;
    }

    public function getLogin() {
        return $this->_login;
    }

    public function setPassword($password) {
        $this->_password = $password;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function setSender($sender) {
        $this->_sender = ltrim($sender, '+');
    }

    public function getSender() {
        return $this->_sender;
    }

    public function setBody($body) {
        $this->_body = $body;
    }

    public function getBody() {
        return $this->_body;
    }

    public function setApiUrl($url) {
        $this->_apiUrl = $url;
    }

    public function getApiUrl() {
        return $this->_apiUrl;
    }

    protected function __createDocument() {
        return new DomDocument('1.0', 'UTF-8');
    }

    protected function __configureDocument(DomDocument $doc) {
        if ($doc->firstChild) {
            $doc->removeChild($doc->firstChild);
        }
        $doc->formatOutput = true;
        return $doc;
    }

    public function asXml() {
        if (!$this->_doc) {
            $this->_doc = $this->__createDocument();
        }
        $doc = $this->__configureDocument($this->_doc);
        $doc = $this->__assembleDocument($doc);
        return $doc->saveXML();
    }

    public function send() {
        if (empty($this->_recipients)) {
            return 'No recipients';
        }
        $this->__beforeSendEvent();
        $rCurl = curl_init($this->getApiUrl());
        curl_setopt($rCurl, CURLOPT_HEADER, 0);
        curl_setopt($rCurl, CURLOPT_POSTFIELDS, $this->asXml());
        curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCurl, CURLOPT_POST, 1);
        $this->__sendEvent();
        $response = curl_exec($rCurl);
        curl_close($rCurl);
        return $this->__responseHandler($response);
    }

    protected function __beforeSendEvent() {

    }

    protected function __sendEvent() {

    }

    protected function __responseHandler($res) {
        $dom = new DomDocument();
        $dom->loadXML($res);
        if (!$dom) {
            throw new Exception('SMS Service: Error while parsing response');
        }
        $xml = simplexml_import_dom($dom);
        return $xml->asXML();
    }

}