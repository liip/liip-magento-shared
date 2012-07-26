<?php

/**
 * Use in your system.xml to specify source_model
 *
 * <source_model>liip/config_source_target</source_model>
 */
class Liip_Shared_Model_Config_Source_Target
{
    const TEST = 'test';
    const PROD = 'prod';
    const DEV = 'dev';

    public function toOptionArray()
    {
        return array(
            array('value' => self::TEST, 'label' => Mage::helper('liip')->__('Test')),
            array('value' => self::PROD, 'label' => Mage::helper('liip')->__('Production')),
            array('value' => self::DEV, 'label' => Mage::helper('liip')->__('Development')),
        );
    }
}

