<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('orba_ceneopro_mapping')}` CHANGE `ceneopro_category_id` `ceneopro_category_id` INT( 11 ) NULL 
");


$installer->endSetup();