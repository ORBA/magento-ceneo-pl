<?php
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$connection->delete(
    $this->getTable('core_config_data'),
    $connection->prepareSqlCondition('path', array(
        'like' => 'ceneoplpro/attr_clothes/Kod_produktu'
    ))
);

$installer->endSetup();