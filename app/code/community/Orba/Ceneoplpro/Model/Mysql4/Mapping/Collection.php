<?php
class Orba_Ceneoplpro_Model_Mysql4_Mapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    protected function _construct(){
        parent::_construct();
        $this->_init('ceneoplpro/mapping');
    }
    
}
