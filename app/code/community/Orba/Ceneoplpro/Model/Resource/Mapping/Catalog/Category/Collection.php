<?php
class Orba_Ceneoplpro_Model_Resource_Mapping_Catalog_Category_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    
    protected function _construct(){
        parent::_construct();
        $this->_init('ceneoplpro/mapping_catalog_category');
    }
    
}
