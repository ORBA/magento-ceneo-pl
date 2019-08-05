<?php

class Orba_Ceneoplpro_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{

    public function sendPing($version, $upgrade = false)
    {
        return true;
    }
}