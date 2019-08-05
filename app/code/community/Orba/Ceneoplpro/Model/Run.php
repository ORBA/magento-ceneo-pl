<?php

class Orba_Ceneoplpro_Model_Run extends Mage_Core_Model_Abstract
{

    protected $mappedIds = array();

    /**
     * @return array
     */
    public function getMappedIds()
    {
        return $this->mappedIds;
    }

    /**
     * @param array $mappedIds
     */
    public function setMappedIds($mappedIds)
    {
        $this->mappedIds[] = $mappedIds;
    }

    protected function getConfig()
    {
        return Mage::getSingleton('ceneoplpro/config');
    }

    public function runAll()
    {
        // clear old mapping
        $this->clear();

        $collection = Mage::getModel('ceneoplpro/mapping')->getCollection()
            ->addFieldToSelect('*')
            ->setOrder('priority', 'ASC');

        foreach ($collection as $key => $mappingModel) {
            $mappedIds = $mappingModel->run();
            if (is_array($mappedIds)) {
                $this->setMappedIds($mappedIds);
            }
        }

        $this->reindexFlatCatalogIfNeccesary();
        return true;
    }


    protected function clear()
    {
        $clear = Mage::getModel('ceneoplpro/product')->clearAllOldMaping();
        if ($clear) {
            return true;
        }
        return false;
    }


    protected function productIdsByStoreId($productIds, $storeId)
    {
        $array = array();
        foreach ($productIds as $key => $value) {
            if (isset($value[$storeId])) {
                if ($value[$storeId]) {
                    $array[] = $key;
                }
            }
        }

        return $array;
    }


    protected function reindexFlatCatalogIfNeccesary()
    {
        if ($this->getConfig()->isFlatCatalogEnabled()) {
            $process = Mage::getModel('index/process')->getCollection()
                ->addFieldToFilter('indexer_code', 'catalog_product_flat')
                ->getFirstItem();
            if ($process->getId()) {
                $process->reindexAll();
            }

        }
    }
}