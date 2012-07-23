<?php
abstract class RM_Entity_Search_Abstract_Rules {

    /**
     * @var RM_Entity_Search_Rules
     */
    private $_searchRules;

    private $_searchPhrase;

    /**
     * @abstract
     * @return array
     */
    abstract public function getResults();

    /**
     * @param RM_Entity_Search_Rules $rules
     */
    public function setRules(RM_Entity_Search_Rules $rules) {
        $this->_searchRules = $rules;
    }

    /**
     * @return RM_Entity_Search_Rules
     */
    public function getRules() {
        return $this->_searchRules;
    }

    public function setPhrase($searchPhrase) {
        $searchPhrase = urldecode( trim( $searchPhrase) );
        $this->_searchPhrase = $searchPhrase;
    }

    public function getPhrase() {
        return $this->_searchPhrase;
    }

    protected function __setRulesToQuery(Zend_Db_Select $select) {
        if ($this->_searchRules instanceof RM_Entity_Search_Rules) {
            $this->getRules()->improveSelect( $select );
        }
    }

    /**
     * @param RM_Entity_Search_Abstract_Rules $search
     * @throws Exception
     */
    public function __copyFrom($search) {
        if ($search instanceof RM_Entity_Search_Abstract_Rules) {
            $this->setPhrase( $search->getPhrase() );
            if ($search->getRules() instanceof RM_Entity_Search_Rules) {
                $this->setRules( $search->getRules() );
            }
        } else {
            throw new Exception('$search must be instance of RM_Entity_Search_Abstract_Rules');
        }
    }

}