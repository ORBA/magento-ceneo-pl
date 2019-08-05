<?php

class Orba_Ceneoplpro_Model_Resource_Mapping extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct() {
        $this->_init('ceneoplpro/mapping', 'id');
    }

    public function lookupStoreIds($id)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('ceneoplpro/mapping_store'), 'store_id')
            ->where('id = ?', (int)$id);
        return $adapter->fetchCol($select);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }


    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('orba_ceneopro_mapping_store' => $this->getTable('ceneoplpro/mapping_store')),
                $this->getMainTable() . '.id = orba_ceneopro_mapping_store.id',
                array()
            )
                ->where('orba_ceneopro_mapping_store.store_id IN (?)', $storeIds)
                ->order('orba_ceneopro_mapping_store.store_id DESC')
                ->limit(1);
        }
        return $select;
    }



    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('ceneoplpro/mapping_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = array(
                'id = ?' => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );
            $this->_getWriteAdapter()->delete($table, $where);
        }
        if ($insert) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }

}
