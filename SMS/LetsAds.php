<?php
class RM_SMS_LetsAds
    extends
        RM_SMS_Abstract {

    protected function __addCredentials(DomNode $root) {
        $doc = $this->_doc;
        $auth = $root->appendChild($doc->createElement('auth'));
        $auth->appendChild($doc->createElement('login'))->appendChild($doc->createTextNode($this->getLogin()));
        $auth->appendChild($doc->createElement('password'))->appendChild($doc->createTextNode($this->getPassword()));
    }

    protected function __addSender(DomNode $root) {
        $doc = $this->_doc;
        $root->appendChild($doc->createElement('from'))->appendChild($doc->createTextNode($this->getSender()));
    }

    protected function __addRecipients(DomNode $root) {
        $doc = $this->_doc;
        foreach ($this->getRecipients() as $recipient) {
            $root->appendChild($doc->createElement('recipient'))->appendChild($doc->createTextNode($recipient));
        }
    }

    protected function __addBody(DomNode $root) {
        $doc = $this->_doc;
        $root->appendChild($doc->createElement('text'))->appendChild($doc->createTextNode($this->getBody()));
    }

    /**
     * @param DomDocument $doc
     * @return DomDocument
     */
    protected function __assembleDocument(DomDocument $doc) {
        $root = $doc->appendChild($doc->createElement('request'));
        $this->__addCredentials($root);
        $message = $root->appendChild($doc->createElement('message'));
        $this->__addSender($message);
        $this->__addRecipients($message);
        $this->__addBody($message);
        return $doc;
    }

}