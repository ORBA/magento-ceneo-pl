<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('orba_ceneopro_category')} ADD `is_deleted` BOOLEAN NOT NULL DEFAULT '0';
    ALTER TABLE {$this->getTable('orba_ceneopro_category')} CHANGE `group` `group` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'other';
");


$installer->endSetup();