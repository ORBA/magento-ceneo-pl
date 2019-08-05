<?php

$installer = $this;
$installer->startSetup();

Mage::getModel('ceneoplpro/attribute')->addCeneoAttributesToProduct();


$installer->endSetup();