<?php
class Orba_Ceneoplpro_Model_Resource_Category extends Mage_Core_Model_Resource_Db_Abstract {
    
    protected function _construct() {
        $this->_init('ceneoplpro/category', 'id');
    } 
    
}