<?php

$installer = $this;
$installer->startSetup();




$catalogInstaller = Mage::getResourceModel("catalog/setup", "core_setup");

// Porduct Allegro shop category
$catalogInstaller->addAttribute(
    "catalog_product", 
     "ceneopro_name_pl", 
     array(
       "type"              => "varchar",
       "input"             => "text",
       "label"             => "Ceneo Polish Name",
       "position"          => 920,
       "global"            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
       "required"          => 0,
       "group"             => "General"
   )
);


$installer->endSetup();