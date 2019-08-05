<?php
$this->startSetup();

$table = $this->getConnection()
    ->newTable($this->getTable('ceneoplpro/mapping_store'))
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable'  => false,
            'primary'   => true,
        ),
        'ID'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Store ID'
    )
    ->addIndex(
        $this->getIdxName(
            'ceneoplpro/mapping_store',
            array('store_id')
        ),
        array('store_id')
    )
    ->addForeignKey(
        $this->getFkName(
            'ceneoplpro/mapping_store',
            'id',
            'ceneoplpro/mapping',
            'id'
        ),
        'id',
        $this->getTable('ceneoplpro/mapping'),
        'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $this->getFkName(
            'ceneoplpro/mapping_store',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $this->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Store Table');
$this->getConnection()->createTable($table);


// update ceneopro_category_id attr scope
Mage::getModel('catalog/product')->getResource()->getAttribute('ceneopro_category_id')
    ->setIsGlobal(Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE)
    ->save();

$this->endSetup();