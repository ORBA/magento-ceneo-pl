<?php

$this->startSetup();

Mage::getModel('catalog/product')->getResource()->getAttribute('ceneopro_category_id')
    ->setUsedInProductListing(true)
    ->save();


$this->endSetup();