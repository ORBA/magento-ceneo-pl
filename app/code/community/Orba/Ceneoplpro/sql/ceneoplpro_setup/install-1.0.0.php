<?php
$installer = $this;
$installer->startSetup();

Mage::getModel('ceneoplpro/attribute')->addCeneoAttributesToProduct();

$categoryTableName = $this->getTable('ceneoplpro/category');
$categoryTable = $installer->getConnection()
    ->newTable($categoryTableName)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'	=> true,
        'nullable'  => false,
        'primary'   => true,
    ), 'ID')
    ->addColumn('external_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'	=> true,
        'nullable'  => false
    ), 'External ID')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'	=> true,
        'nullable'  => false,
        'default' => 0
    ), 'Parent ID')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false
    ), 'Path')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'	=> true,
        'nullable'  => false,
        'default' => 0
    ), 'Position')
    ->addColumn('level', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'	=> true,
        'nullable'  => false,
        'default' => 0
    ), 'Level')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false
    ), 'Name')
    ->addColumn('group', Varien_Db_Ddl_Table::TYPE_TEXT, 16, array(
        'nullable' => false,
        'default' => 'other'
    ), 'Group')
    ->addColumn('is_deleted', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'default' => 0
    ), 'Is deleted');
$installer->getConnection()->createTable($categoryTable);

$mappingTableName = $this->getTable('ceneoplpro/mapping');
$mappingTable = $installer->getConnection()
    ->newTable($mappingTableName)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'	=> true,
        'nullable'  => false,
        'primary'   => true,
    ), 'ID')
    ->addColumn('ceneopro_category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'	=> true,
        'nullable'  => true
    ), 'Ceneo Category Internal ID')
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'	=> true,
        'nullable'  => false,
        'default' => 0
    ), 'Priority')
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, 4096, array(
        'nullable' => false
    ), 'Serialized conditions')
    ->addIndex($installer->getIdxName('ceneoplpro/mapping', array('ceneopro_category_id')),
        array('ceneopro_category_id')
    )
    ->addForeignKey(
        $installer->getFkName('ceneoplpro/mapping', 'ceneopro_category_id', 'ceneoplpro/category', 'id'),
        'ceneopro_category_id', $installer->getTable('ceneoplpro/category'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($mappingTable);

Mage::getModel('ceneoplpro/config')->saveHash();


$installer->endSetup();