<?php

class Orba_Ceneoplpro_Model_Resource_Mapping_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_joinedFields = array();


    protected function _construct(){
        parent::_construct();
        $this->_init('ceneoplpro/mapping');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }


    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!isset($this->_joinedFields['store'])) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }
            if (!is_array($store)) {
                $store = array($store);
            }
            if ($withAdmin) {
                $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
            }
            $this->addFilter('store', array('in' => $store), 'public');
            $this->_joinedFields['store'] = true;
        }
        return $this;
    }


    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('ceneoplpro/mapping_store')),
                'main_table.id = store_table.id',
                array()
            )
            ->group('main_table.id');
            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }


    protected function _toOptionArray($valueField='entity_id', $labelField='attributecodename', $additional=array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }


    protected function _toOptionHash($valueField='entity_id', $labelField='attributecodename')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }


    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }
}
