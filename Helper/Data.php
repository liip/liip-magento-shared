<?php

class Liip_Shared_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Changes a store config and reloads it to reflect the change immediately
     *
     * @param   string  $path   Configuration key
     * @param   string  $value  New value
     * @param   Mage_Core_Model_Website $website
     * @param   Mage_Core_Model_Store   $store
     */
    public function setStoreConfig($path, $value, Mage_Core_Model_Website $website = null, Mage_Core_Model_Store $store = null)
    {
        $parts = explode('/', $path);

        if (count($parts) != 3) {
            throw new InvalidArgumentException('Invalid path `'.$path.'\' for config');
        }

        $groups = array();
        $groups[$parts[1]]['fields'][$parts[2]]['value'] = $value;

        Mage::getModel('adminhtml/config_data')
            ->setSection($parts[0])
            ->setWebsite($website)
            ->setStore($store)
            ->setGroups($groups)
            ->save();

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }

    public function translateWildcardsToSql($text)
    {
        return strtr($text, array('*' => '%', '?' => '_'));
    }
}
